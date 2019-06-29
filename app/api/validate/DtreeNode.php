<?php 
/**
 * 用户组管理场景验证器
 * @author wuchuheng 
 * @email  wuchuheng@163.com
 * @date   2019-05-17
 */
namespace app\api\validate;

use think\facade\Request;
use app\api\model\Member;
use app\lib\exception\ErrorException;

class DtreeNode extends Base
{
  protected $rule   = [
    'parentId'     => 'require|checkNum',
    'addNodeName'  => 'require',
    'editNodeName' => 'require',
    'receives'     => 'checkReceives',
    'nodeId'       => 'require', //禁止删除
    'account'      => 'require|length:6,20|accountIsUnique', 
		'passwd'       => 'require|length:6,20',
		'repasswd'     => 'require|confirm:passwd',
	  'username'     => 'require|isChinese',
	  'nick_name'    => 'require|nickNameIsUnique',
	  'phone'        => 'mobile',
		'email'        => 'email',
		'select_role'  => 'integer',
		'select_role'  => 'integer',
    'limit'        => 'has_limit',
    'page'        => 'has_page',
    'uid'         => 'require|number|gt0|hasUid',
    'id'          => 'require|number|gt0',
    'rules'       => 'require|isArray'
  ];

  protected $message       = [
    'limit.has_limit' => 'page和limit必须大于0的数字',
    'page.has_page' => 'page和limit为大于0的数字',
    'receives.checkReceives'     => '接待量必需是整数',
    'parentId.checkNum'          => 'parentId是不小于-1的整数',
		'account.lenght'             => '请输入6-20位的账户名',
		'account.accountIsUnique'    => '该帐号已存在，请换个别的',
  	'passwd.length'              => '请输入6-20位的密码',
  	'checkpasswd.confirm'        => '2次密码不一致',
		'username.require'           => '请输入用户名',
		'username.isChinese'         => '请输中文姓名',
    'nick_name.require'          => '请输昵称',
    'nick_name.nickNameIsUnique' => '该昵称已存在，请换个别的',
		'phone.mobile'               => '请输正确的手机号码',
		'email.email'                => '请输入正确的邮箱',
		'select_role'                => '请选择权限角色',
    'account.require'            => '请添加账号account',
    'uid.gt0'                    => '用户uid必须大于0',
    'uid.hasUid'                 => '没有这个成员',
  ];

  //场景定义
  protected $scene  = [
     'get'          => ['parentId'], //读取组
     'post'         => ['addNodeName', 'parentId'], //新增组
     'put'          => ['nodeId', 'editNodeName'], //修改组
     'delete'       => ['nodeId'], //删除组
     'addMember'    => ['account', 'passwd', 'repasswd', 'username', 'nick_name', 'receives', 'phone', 'email', 'select_role'], //添加用户
     'getMemberByAccount' => ['account'],  //以帐户名查询查询单个用户信息
     'getMembers'  => ['limit', 'page'],  //获取成员场景
     'editMember'  => ['uid'],  //:xxx 修改成员有多个字段要对应验证
     'delMember'   => ['uid'], //删除成员
     'getRoleList' => ['page', 'limit'], //获取权限
     'getRoleById' => ['id'], //单个角色权限目录树
     'uploadRoleById' => ['id', 'rules'] //更新角色
  ];

    
  /**
   *  get 场景规则修正
   *  @note get场景涉及全部节点和子节点读取，
   *  而子节点读取是有参数的要验证，全部节点则不用
   *  @note nodeId参数涉及到修改和删除场景，而删除
   *  场景需要禁止删了一些节点，需要附加一个验证条
   *  件
   */
  public function __construct()
  {
      if (Request::method() === 'GET' ) {
        if ( !Request::has('parentId', 'get')) {
            $this->rule['parentId'] = '';
        } else {
            $this->rule['parentId'] = 'require|checkNum';
        }
      } 
      if (Request::method() === 'DELETE') {
            $this->rule['nodeId'] = 'require|forbiden';
      } 
  }


   /**
    * 验证整数范围不小于-1
    * @access protected
    * @return boolean
    */
  protected function checkNum($value)
  {
    if (!is_numeric($value)) return false;
    if ($value !== 0 OR $value !== -1) return true;
    if (!is_int($value)) return false;
    return true;
  }


   /**
    * 禁止删除根节点和未分组节点
    * @access protected
    * @value  numeric    不小于-1的节点id
    * @return boolean
    */
    protected function forbiden($value)
    {
      if ($value == -1 || $value == 0) {
        throw new ErrorException(['msg' => '禁止删除该节点', 'errorCode' => '40310', 'code'=>403.1]);
      } else {
        return true;
      }
    }
 	

		/**
			*  是否汉字
			*  @return boolean
			*/
			protected function isChinese($value)
			{		
        return true;
        if (preg_match("/^[\u4e00-\u9fa5]+$/", $value )) {
							return true;
        } else {
							return false;
        }
			}


     /**
       * account 是否唯一
       * return  boolean
       */
       protected function accountIsUnique($value, $data) 
       {
           $hasData = (new Member())->where('account', '=', $value)->find();          if ($hasData) return false;
           else return true;
       }

       
     /**
     * nick_name 是否唯一
     * return  boolean
     */
     protected function nickNameIsUnique($value, $data) 
     {
         $hasData = (new Member())->where('nick_name', '=', $value)->find();
         if ($hasData) return false;
         else return true;
     }


       /**
       *   验证receives
       */
     public function checkReceives($value, $data)
     {
         if (strlen($value) === 0) return true;
         if (!is_numeric($value)) {
             return false;
         } else {
             return true;
         }
     }


     /**
     *  大于0
     */
    public function gt0($value, $data)
    {
      if ($value <= 0 ) return false;
      else return  true;
    }


    /**
     * uid是否在member表
     * @return   booleean
     */
    public function hasUid($value, $data)
    {
      $hasData = Member::where('uid', '=', $value)->field('uid')->find();
      if (!$hasData) {
          return false;
      } else {
          return true;
      }
    }


    /**
    * 分页数字验证
    *
    */
    public function has_page($value, $data)
    {
      if (!Request::has('limit', 'get')) {
        return false;
      } 
      if (!$value) return true;
      if (!is_numeric($value) ) return false;
      if ($value <= 0 ) return false;
      else return  true;
    }


    /**
    * 分页数量验证
    *
    */
    public function has_limit($value, $data)
    {
      if (!Request::has('page', 'get')) {
        return false;
      }
      if (!$value) return true;
      if (!is_numeric($value) ) return false;
      if ($value <= 0 ) return false;
      else return  true;
    }

  /**
   * 是否数组 
   *
   */
   public function isArray($value)
   {
     if (is_array($value)) {
         return true;
     } else {
         return false;
     }
   }
}


