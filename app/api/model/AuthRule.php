<?php  
namespace app\api\model;

use think\facade\Request;

class AuthRule extends Base
{
    /**
     * 获取右则菜单列表
     *
     */
    public function getSideMenu()
    {
      $hasData = self::where('pid = 2')
        ->where('is_side_menu = 1')
        ->field('jump,title,icon,data_name as name')
        ->field('title,icon,data_name as name,jump')
        ->select();
      return $hasData;
    }



    /**
     * 获取客服模式右侧菜单列表
     *
     */
     public function getChatMenu()
     {
       $hasData = self::where('pid = 23')
         ->where('is_side_menu = 1')
         ->field('jump,title,icon,data_name as name')
         ->field('title,icon,data_name as name,jump')
         ->select();
       return $hasData;
     }


    /**
     * 规则树
     *
     */
    public function allToTree()
    {
        $result = self::
          field(['title'=>'name','id'=>'value', 'pid', 'concat(path,"-",id)'=>'fullpath'])
          ->order('fullpath') 
          ->select();
      return $result;
    }


    
}

