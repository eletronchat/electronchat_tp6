<?php
/*
 * the base leval for validate
 *
 * @name    wuchuheng
 * @github  github.com/wuchuheng
 * @email   wuchuheng@163.com
 * @data    2019-04-14
 */
namespace app\api\validate;

use think\Validate;
use think\facade\Request;
use app\lib\exception\ParameterException;
    
class Base extends Validate
{
   /**
    *  to validate
    *
    */ 
    public function gocheck()
    {
        $params = Request::param();        
        if (!$this->check($params)) {
            throw new ParameterException([
                'msg' => is_array($this->error) ? implode('', $this->error) : $this->error,
            ]); 
        }
        return true;
    }


    /**
    * 是否有token
    *
    */
    public function hasToken($value)
    {
      if(!isset($_SERVER['HTTP_ACCESS_TOKEN'])) {
          return false;
      } else {
          return true;
      }
    }
}
