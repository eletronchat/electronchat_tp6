<?php

/**
 *  聊天客户系统数据层-redis数据库0业务类
 *
 * File              : Redis0.php
 * @author           : wuchuheng <wuchuheng@163.com>
 * 
 * Date              : 24.07.2019
 * Last Modified Date: 24.07.2019
 * Last Modified By  : wuchuheng <wuchuheng@163.com>
 */

namespace app\chat\model;

use \app\chat\model\Base;
use \app\chat\model\Redis3 as Redis3Model;

class Redis0 extends Base
{
    /**
     * 获取redis实例对象
     *
     * @return  redis连接对象
     */
    protected static function getRedisInstance() : object
    {
        self::$Redis = parent::getRedisInstance();
        self::$Redis->select(0);
        return self::$Redis;
    }


    /**
     *  通过用户连接id获取用户信息
     *
     * @client_id   string 
     * @return      用户信息
     */
    public static function  getMemberByClientId(string $client_id) : array
    {
       $Redis3 = Redis3Model::getRedisInstance();
       $db0_key = $Redis3->hGet('chat_connect', $client_id);
       $Redis0 = self::getRedisInstance();
       $member_info = $Redis0->hGetAll($db0_key);
       return $member_info;
    }

}
