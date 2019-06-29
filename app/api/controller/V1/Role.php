<?php 
/**
 * 客服用户管理
 * @name    wuchuheng
 * @email   wuchuehng@163.com
 * @data    2019/05/13
 * @blog    www.wuchuheng.com
 */
namespace app\api\controller\v1;

use app\api\service\Role as RoleService;
use think\facade\Request;
use app\api\validate\DtreeNode;
use app\lib\exception\ErrorException;
use app\api\controller\V1\Base;

 
class Role extends Base
{
    /**
     * 获取客服所有分类
     * @url     /api/v1/group
     * @http    get
     * @return  json
     **/ 
    public function getAllGroup()
    { 
      if (Request::get('addMember')) {
        $hasdata = (new RoleService())->getAllUser('forAddMember');   
      } else {
        (new DtreeNode())->scene('get')->gocheck(); 
      }
        $hasdata = (new RoleService())->getAllUser();   
        if (!$hasdata) throw new ErrorException(); 
        return  parent::successMessage(['style'=>'dtree', 'data'=>$hasdata]);
    }


    /**
     * 增加节点
     * @url     /api/v1/group
     * @http    post
     * @return  json 
     */
    public function addNode()
    {
      (new DtreeNode())->scene('post')->gocheck(); 
       //添加子节点
       $result = (new RoleService())->addGroup();
       if ($result->id) {
         $id = $result->id;
         return  parent::successMessage(['style'=>'dtree', 'data'=>['nodeId'=>$id]]);
       }
    }

    
    /**
     * 修改节点  
     * @url    /api/v1/group
     * @http   put 
     * @return json
     */
     public function editNode()
     {
        (new DtreeNode())->scene('put')->gocheck(); 
        $isSave= (new RoleService())->editGroup();
        if (!$isSave) throw new ErrorException(['msg' => 'to update data faild']); 
        return  parent::successMessage();
     }

    
    /**
      * 删除节点
     * @url    /api/v1/group
     * @http   delele
     * @return json  
     */
    public function delNode()
    {
        (new DtreeNode())->scene('delete')->gocheck(); 
        $isDel = (new RoleService)->delGroup();
        if (!$isDel) {
            throw new ErrorException('删除失败，内部错误');
        } else {
            return parent::successMessage();
        }
    }
    

    /**
    *  获取权限角色列表
    *
    */
    public function getRoleList()
    {
      (new DtreeNode())->scene('getRoleList')->gocheck();
        $hasData = (new RoleService())->getRoleList();
        if(!$hasData) {
          throw new ErrorException([
            'msg'=>'没有权限角色分组，请先添加',
            'code' => 500
          ]);
        } else {
            return parent::successMessage($hasData);
        }
    }


    /**
     *  添加客服
     *
     * @http  post 
     * @url   api/v1/members
     * @return json
     */
     public function addMember()
     {
         (new DtreeNode())->scene('addMember')->gocheck(); 
         $is_add = (new RoleService())->addMember();
         if (!$is_add) {
           throw new ErrorException([
             'msg'=>'添加失败，服务器内部错误，请联系管理员',
           ]);
         } else {
             return parent::successMessage();
         }
     }


    /**
     * 读取成员
     * @http   get 
     * @url    api/v1/members
     * @return json   成员信息 
     */
     public function getMembers()
     {
        (new DtreeNode())->scene('getMembers')->gocheck();
         $hasData = (new RoleService())->getMembers();
        if (!$hasData) {
           throw new ErrorException([
             'msg'=>'查询数据失败，内部错误',
           ]);
        } else {
          return parent::successMessage(['data'=>$hasData->data, 'count'=>$hasData->count]);
        }
     }


     /**
     * 修改成员
     * @http    put 
     * @param   init    $uid    用户id
     * @url     api/v1/member/:id
     * @return  json    修改结果 
     */
    public function editMember($uid)
    {
        (new DtreeNode())->scene('editMember')->gocheck();
        $is_edit = (new RoleService())->editMember($uid);
        if ($is_edit) {
            return parent::successMessage();
        } else {
           throw new ErrorException(['msg'=>'内部错误修改失败']);
        }
        
    }


    /**
    * 删除成员
    * @http   DELETE
    * @param  $uid    init    用户id 
    * @return json
    */
     public function del(int $uid) 
     {
        (new DtreeNode())->scene('delMember')->gocheck();
        $is_del = (new RoleService())->delMember($uid);
        if ($is_del) {
            return parent::successMessage();
        } else {
            new ErrorException(['msg'=>'服务器内部错误，数据删除失败']);
        }
     }


    /**
    * 单个角色权限目录树
    * @http /api/v1/roleList/:id
    * @param init   @id   角色id
    */
    public function getRoleById()
    {
        (new DtreeNode())->scene('getRoleById')->gocheck();
        $has_data = (new RoleService())->getRoleById();
        if ($has_data) {
            return json(['data'=>['trees'=>$has_data]]);
        } else {
            new ErrorException(['msg'=>'服务器内部错误，数据删除失败']);
        }
    }


     /**
     * 更新角色
     *
     */
     public function uploadRoleById()
     {
        (new DtreeNode())->scene('uploadRoleById')->gocheck();
        $is_upload = (new RoleService())->uploadRoleById();
        if (!$is_upload) {
            throw new ErrorException();
        } else {
            return parent::successMessage();
        }
     }
}
