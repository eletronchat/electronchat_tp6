<?php 
namespace app\api\model;

use think\model\concern\SoftDelete;

class Image extends Base
{
		use SoftDelete;
		protected $deleteTime = 'delete_time';


    /**
    * 图片路径修改
    *
    *
    */
  public function getUrlAttr($value, $data)
  {
      return $value;
  }
}

