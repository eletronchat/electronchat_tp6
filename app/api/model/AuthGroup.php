<?php 
namespace app\api\model;

class AuthGroup extends Base
{
     /**
     *  添加dtree字段 parentId
     *
     */ 
    public function getParentIdAttr($value, $data)
    {
        return 0;
    }


    /**
    * 获取角色现在的权限
    * 
    */
    public function getRulesById()
    {
        $id = input('id');
        $result = self::where('id', $id)->field('rules')->find();
        return $result['rules'];
    }

}


