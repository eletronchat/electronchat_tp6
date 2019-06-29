<?php 
namespace app\api\controller\V1;

use app\api\controller\V1\Base;
use app\api\model\AuthRule;


class Menu extends Base
{
    /**
     * 获取功能模块列表
     * @url  /aip/v1/moduleList
     * @http get
     * @return  功能模块列表
     */
    public function list()
    {
      $side_menu = (new AuthRule())->getSideMenu();
      return parent::successMessage($side_menu);
    }
}

