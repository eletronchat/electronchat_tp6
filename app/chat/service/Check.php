<?php 
/**
 * 连接验证类
 *
 * @auth wuchuheng
 * @email wuchuheng@163.com
 * @date  2019-07-13
 */
namespace app\chat\service;

use \app\chat\service\Base;

class Check extends Base 
{


    /**
    * 客服工作台连接验证
    *
    * @token    string  连接令牌
    * @return   boolean 
    */
    public static function chatConnect(string $token)
    {
        $Redis = parent::getRedisInstance(); 
        $Redis->select(0);
        $has_data = $Redis->hGetAll('member_' . $token);
        if (count($has_data) === 0)
          return false;
        else 
          return true;
        
    }

}
