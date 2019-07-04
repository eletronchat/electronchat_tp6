<?php 
namespace app\api\controller\V1;

use app\api\controller\V1\Base;
use app\api\model\AuthRule;


class Menu extends Base
{
    /**
     * 获取功能模块列表
     * @url  /aip/v1/menu/list
     * @http get
     * @return  功能模块列表
     */
    public function list()
    {
      $side_menu = (new AuthRule())->getSideMenu();
      return parent::successMessage($side_menu);
    }


    /**
     * 获取客服模式模块列表
     * 
     * @url  /api/v1/menu/chat
     * @http GET
     * @return  json
     */
     public function chat()
     {
         $chatMenu = (new AuthRule())->getChatMenu();
         return parent::successMessage($chatMenu);
     }
}

