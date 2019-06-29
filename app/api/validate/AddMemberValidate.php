<?php 

/**
 * AddMemberValidate   用户组验证器
 * @author wuchuheng 
 * @email  wuchuheng@163.com
 * @date   2019-06-01
 */
namespace app\api\validate;

use think\facade\Request;
use app\lib\exception\ErrorException;

class  extends Base
{
  protected $rule = [
    'parentId'    => 'require|checkNum',
    'addNodeName' => 'require',
    'editNodeName' => 'require',
    'editNodeName' => 'require',
    'nodeId'       => 'require' //禁止删除
  ];
  protected $message = [
    'parentId.checkNum'  => 'parentId是不小于-1的整数',
  ];
  //场景定义
  protected $scene = [
    'get'    => ['parentId'], //读取
    'post'   => ['addNodeName', 'parentId'], //新增
    'put'    => ['nodeId', 'editNodeName'], //修改
    'delete' => ['nodeId'] //删除
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

}

