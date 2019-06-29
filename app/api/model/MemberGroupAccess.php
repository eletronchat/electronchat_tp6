<?php 
namespace app\api\model;

class MemberGroupAccess extends Base
{
    /**
     * 关联分组表
     *
     */
    public function memberGroup()
    {
        return $this->hasOne('MemberGroup', 'id', 'member_group_id');
    }
}
