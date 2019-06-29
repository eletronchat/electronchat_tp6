<?php 
namespace app\api\model;

use think\Db;

class Member extends Base
{
    /**
     * 获取客服目录树的未分组数据
     * @return obj  collection
     */
    public function countNotBelong()
    {
      $count = self::where('uid', 'not in', function($query) {
        $query->name('member_group_access')->field('uid');
      }) ->count();
      return $count;
    }


    /**
     * 关联权限分组模型
     *
     */
     public function authGroupAccess()
     {
        return $this->hasManyThrough('MemberGroupAccess', 'MemberGroup', 'uid', 'member_group_id', 'id');
     }


     /**
     * 关联成员成员分组模型
     *
     */
     public function memberGroupAccess()
     {
         return $this->belongsTo('MemberGroupAccess', 'uid', 'uid');
     }


     /**
     * 关联相册
     * 
     */
    public function image()
    {
      return $this->hasOne('Image', 'id', 'img_id');
    }


    /**
      *  关联角色中间表
      *
      */
    public function authAccess()
    {
        return $this->hasOne('AuthGroupAccess', 'uid', 'uid');
    }
   
     
}

