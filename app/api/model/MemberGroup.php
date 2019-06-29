<?php 
namespace app\api\model;

use app\api\model\Member;
use think\facade\Request;

class MemberGroup extends Base
{
  /**
   * 获取客服组目录树
   * @return    obj  
   */
  public function getGroup()
  {
    $request = Request::instance();
    $result = self::withCount(['memberGroupAccess'=>'count'])
      ->where(function($query) use ($request) {
        if ($request->param('nodeId/d')) {
          $id = $request->param('nodeId');
          $query->where('path', 'like', "%-$id%");
        }
      })
      ->field(['name'=>'title', 'pid'=>'parentId', 'id', 'concat(path,"-",id)'=>'fullpath'])
      ->order('fullpath') 
      ->select();
      return $result;
  }
  
  /**
   * 关联户分组-用户中间表
   *
   */ 
  public function memberGroupAccess()
  {
      return $this->hasMany('MemberGroupAccess', 'member_group_id', 'id');
  }

}

