<?php 
namespace app\api\model;

class AuthGroupAccess extends Base
{
    /**
    *  关联分组
    *
    */ 
    public function authGroup()
    {
      return $this->hasOne('AuthGroup', 'id', 'group_id');
    }
}

