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
      //模式选择
      switch(Request::param('mode')) {
          case 'service' : 
            echo  '客服模式';exit;
            break;
          case 'manage':
            echo '管理模式'; exit;
            break;
      }
      //自动选择，优先管理模式
      $hasData = self::where('pid = 2')
        ->where('is_side_menu = 1')
        ->field('jump,title,icon,data_name as name')
        ->field('title,icon,data_name as name,jump')
        ->select();
      if($hasData->isEmpty()) {
        $hasData = self::where('pid = 20')
          ->where('is_side_menu = 1')
          ->field('jump,title,icon,data_name as name')
          ->field('title,icon,data_name as name,jump')
          ->select();
      }
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

