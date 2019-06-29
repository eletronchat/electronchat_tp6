<?php 
/* 
 *  Role 控制器服务层
 *  @author wuchuheng
 *  @data 2019/05/16
 *  @email wuchuheng@163.com
 *  @blog  www.wuchuheng.com
 */
namespace app\api\service;

use app\api\model\Member; 
use app\api\service\Base;
use think\facade\Db;
use think\facade\Request;
use app\api\model\MemberGroup;
use app\api\model\AuthGroup;
use think\facade\Config;
use think\facade\Env;
use MemberGroupAccess;
use app\api\model\Image;
use app\api\model\AuthRule;
use app\api\service\Cache as CacheService;

class Role extends Base
{
    /**
     * 获取客服组数据树
     * :xxx  请求的数据用于添加客服分类用的:xxx这设计不符合rest规范,先将就下
     * @return obj
     * 
     */
    public function getAllUser($forAddMember = '')
    {
        $count           = Member::count();
        $count_no_count  = (new Member())->countNotBelong();
        $MemberGroup     = (new MemberGroup())->getGroup();
        $otherNode       = [
            'title'     => "未分组({$count_no_count})",
            'id'        => -1,
            'parentId'  => 0-1
          ];
        $subNode = $this->_arrToTree($MemberGroup->toArray());
        //用于添加会员的表单用
        if (Request::get('addMember'))  return $subNode;
        //返回，更新子节点
        if (Request::get('nodeId/d') > 0) return $subNode;
        $subNode[] = $otherNode; 
        //返回一级节点
        if (Request::get('nodeId/d') === 0 ) return $subNode;
        $data[] = [
          'title'    => "所有({$count})",
          'id'       => 0,
          'spread'   => true,
          'parentId' => 0,
          'children' => $subNode
        ];
        return $data;
    } 


    /**
     * 修改节点
     * @return    boolean
     */
     public function editGroup()
     {
        $id = Request::put('nodeId/d');
        $handle = MemberGroup::get($id);
        $handle->name = Request::put('editNodeName/s');
        $isSave = $handle->save();
        return $isSave;
     }


    /**
     * 添加客服组
     * @return  boolean    处理结果
     */
    public function AddGroup() 
    {
        $data['name'] = Request::param('nodeName');
        $pid = Request::param('parentId');
        if ($pid) {
          $parentNode =(new MemberGroup())->where("id = {$pid}")->field('id,path')->find();
          $data['path'] = $parentNode->path . '-' . $parentNode->id;
          $data['pid']  = $parentNode->id;
        }
        $data['name'] = Request::param('addNodeName');
        $isSave = (new MemberGroup())->create($data);
        return $isSave;
    }


    /**
      * 删除节点
      * @access public
      * @return boolean
      *
      */
    public function delGroup()
    {
        $id = Request::delete('nodeId/d');
        $isDel = MemberGroup::where('id', '=', $id)  
          ->whereOr('path', 'like', "%-{$id}%") 
          ->delete();
        return $isDel;
    }


    /**
     * 将数组遍历为数组树 
     * @arr     有子节点的目录树
     * @tree    遍历赋值的树
     * @return  array   
     *
     */ 
    protected function _arrToTree($items, $pid = 'parentId')
    {
         $map  = [];
         $tree = [];   
         foreach ($items as &$it){
           $el = &$it; 
           $el['title'] = $el['title'] . "(" .$el['count']. ")";
           unset($el['path']);
           unset($el['name']);
           unset($el['count']);
           unset($el['pid']);
           unset($el['fullpath']);
           $map[$it['id']] = &$it; 
         }  //数据的ID名生成新的引用索引树
         foreach ($items as &$it){
           $parent = &$map[$it[$pid]];
           if($parent) {
             $parent['children'][] = &$it;
           }else{
             $tree[] = &$it;
           }
         }
         return $tree;
    }


    /**
     * 获取权限角色列表
     *
     */
    public function getRoleList()
    {
      if (Request::has('limit', 'get') && Request::has('page', 'get')) {
      $list = (new AuthGroup())
        ->field('id,title,descript')
        ->append(['parentId'])
        ->order('id desc')
        ->paginate(Request::get('limit/d'));
        $total = $list->total();
        $list = $list->toArray();
        $list = $list['data'];
        return ['data'=>$list, 'count'=>$total];
      }
      $hasData = (new AuthGroup())
        ->field('id,title,descript')
        ->append(['parentId'])
        ->order('id desc')
        ->select();
      return $hasData;
    }


  /**
   *  添加新的成员
   *  @return   boolean 
   */
   public function addMember()
   {
     Db::startTrans();
     try {
       $img_id = $this->_saveIcon();
       if (Request::has('receives', 'post') && Request::post('receives/d') > 0 ) {
         $receives = Request::post('receives/d');
       } else {
         $result = (Db::name('config')->where('name', '=', 'receives')->field('value')->find());
         $receives = (int)$result['value'];
       }
       $uid = Db::name('member')->insertGetId([
         'username'      => Request::post('username/s'),
           'passwd'      => get_hash(Request::post('passwd/s')),
           'img_id'      => $img_id,
           'account'     => Request::post('account/s'),
           'receives'    => $receives,
           'nick_name'   => Request::post('nick_name/s'),
           'email'       => Request::post('email/s'),
           'phone'       => Request::post('phone/s'),
           'create_time' => time()
         ]);
       Db::name('authGroupAccess')->insert([
         'uid'      => $uid,
         'group_id' => Request::post('group_id/d')
       ]);
       Db::name('memberGroupAccess')->insert([
         'uid'             => $uid,
         'member_group_id' => Request::post('member_group_id/d')
       ]);
       Db::commit();
     } catch (\Exception $e) {
       return false;
       Db::rollback();
     }
     return true;
   }


    /**
     *  保存头像图片
     *  @return  $img_id  int  相册id 
     */
     protected function _saveIcon()
     {
       if (Request::has('file', 'post') && !base64_decode(Request::post('file/s'))) {
         $result = Db::name('config')->where('name','=', 'member_img_id')->field('value')->find();
         return (int)$result['value'];
       }
       $base64 = Request::post('file/s');
       preg_match("/^data:image\/(\w+);base64,/", $base64, $file_type);
       $base64 = substr($base64, strpos($base64, ',') + 1);
       $file_type = $file_type[1];
       $source = base64_decode($base64);
       $dir = Env::get('root_path') . "public/static/img/" . date('Y-m-d');
       if (!is_dir($dir)) mkdir($dir, 0766, true);
       $filename = $dir . '/' . uniqid() . '.' . $file_type;
       file_put_contents($filename, $source);
       $filename = str_replace(Env::get('root_path') . 'public', '', $filename);
       $img_id = Db::name('image')->insertGetId(['url'=>$filename, 'from'=>1]);
       return $img_id;
     }


    /**
     * 获取成员
     * :xxx  要采用远程关联并返回collection数据集
     */
     public function getMembers()
     {
       $mga_uid_s =  Db::name('member_group_access')->field('uid')->select()->toArray();
       $mga_str = implode(',', array_column($mga_uid_s, 'uid'));
       $limit  = Request::get('limit/d');
       $db_prefix = Config::get('database.prefix');
       $query = Db::name('member')->alias('M');
       if (Request::has('current_id', 'get')) {
         $current_id = Request::get('current_id/d');
         if ($current_id > 0) {
           $query = $query->whereOr("MG.path", "like", "%{$current_id}%")
             ->whereOr('MG.id', $current_id);
         } elseif ($current_id === -1) {
           $query = $query->whereNotIn("M.uid", $mga_str);
         } 
       }
       $result = $query
         ->field("M.uid,M.account,M.nick_name,M.phone,M.receives,M.username,I.url as img,AG.title as role,M.is_lock,M.email")
         ->join("{$db_prefix}image I","M.img_id = I.id")
         ->join("{$db_prefix}auth_group_access AGA", "M.uid = AGA.uid" )
         ->join("{$db_prefix}auth_group AG", "AG.id = AGA.group_id")
         ->leftJoin("{$db_prefix}member_group_access MGA", "MGA.uid = M.uid")
         ->leftJoin("{$db_prefix}member_group MG", "MG.id = MGA.member_group_id")
         ->paginate($limit);
       return (object) array('count'=>$result->total(), 'data'=>$result->items());
     }


     /**
     *  修改成员
     *  @param  int   $uid    用户id
     *  @return boolean
     *
     */
     public function editMember(int $uid)
     {
        parse_str(file_get_contents('php://input'), $put_params);
       // 启动事务
       Db::startTrans();
       try {
         $member = Member::where('uid', '=', $uid)->find();
         //更新用户组
         if (Request::has('member_group_id')){
           $member->memberGroupAccess->member_group_id = Request::param('member_group_id/d', 'put');
           $member->memberGroupAccess
             ->allowField(true)
             ->save($params);
         } 
         //更新权限组
         if (Request::has('group_id')) {
           $member->authGroupAccess->group_id = Request::param('group_id/d', 'put');
           $member->memberGroupAccess
             ->allowField(true)
             ->save(Request::param());
         }
         //更新成员信息
         $member->save($put_params);
         Db::commit();
       } catch (\Exception $e) {
         // 回滚事务
         Db::rollback();
         return false;
       }
       //更新成员缓存
       (new CacheService())->updataByMemberUid($member->uid);
       return true;

     }
      
    
    /**
     * 删除成员
     * @param    $uid    init    成员uid 
     * @return   boolean
     */
     public function delMember(int $uid) 
		 {
          Db::startTrans();
          try{
         	  $user = Member::where('uid', '=', $uid)->find();
						Db::name('member_group_access')->where('uid','=', $user->uid)->delete();
						Db::name('auth_group_access')->where('uid','=', $user->uid)->delete();
            $Image = Image::get($user->img_id);
					  $Image->delete_time=date('Y-m-d H:i:s', time());
						$Image->save();
            $user->delete();
            Db::commit();
          } catch(\Exception $e){
            Db::rollback();
            return false;
          }
 					return true;
     }


    /**
    * 单个角色权限目录树
    *
    */
    public function getRoleById()
    {
        $roleList = (new AuthGroup())->getRulesById();
        $checkedIds = explode(',', $roleList);
        $data = (new AuthRule())->AllToTree()->toArray();         
         foreach ($data as &$it){
           $el = &$it; 
           $map[$it['value']] = &$it;
         }
         foreach ($data as &$it){
           $parent = &$map[$it['pid']];
           unset($it['pid']);
           unset($it['fullpath']);
           if(in_array($it['value'], $checkedIds)) {
              $it['checked'] = true;
           } else {
              $it['checked'] = false;
           }
           if($parent) {
             $parent['list'][] = &$it;
           }else{
             $tree[] = &$it;
           }
         }
        return $tree;
    }


    /**
    * 更新角色
    *
    */
    public function  uploadRoleById()
    {
        $rules = implode(',', Request::param('rules', 'put'));
        $id = Request::param('id/d', 'put');
        $is_upload = (new AuthGroup())->where('id = '. $id)->save(['rules'=>$rules]);
        //更新缓存
        (new Cache())->updateByAuthGroupId($id);
        if ($is_upload) {
          return true;
        } else {
          return false;
        }
   }
}

