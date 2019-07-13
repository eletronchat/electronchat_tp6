<?php 
/**
 * 客户业务基类 
 *
 * @auth wuchuheng
 * @email wuchuheng@163.com
 * @date  2019-07-12
 */
namespace app\chat\service;

use app\chat\service\Base;

class Guest extends Base
{
  
    /*
     * 是否有空闲的座席
     * 
     * @return  boolean
     */
    public static function hasWaitingGroup()
    {
        $Redis = parent::getRedisInstance(); 
        $Redis->select(3);
        if($Redis->lLen('chat_waiting_group') > 0) 
            return true;
        else
            return false;
    }


    /**
     *  将客户拉入空闲座席
     *
     */ 
    public static function letsChat()
    {
        $Redis = parent::getRedisInstance(); 
        $Redis->select(3);
        $uid = json_decode($Redis->lPop('chat_waiting_group'), true)['uid'];
    }
}

