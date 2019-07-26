<?php

/**
 *  聊天客户系统数据层-redis数据库3业务类
 *
 * File              : Base.php
 * @author           : wuchuheng <wuchuheng@163.com>
 * 
 * Date              : 24.07.2019
 * Last Modified Date: 24.07.2019
 * Last Modified By  : wuchuheng <wuchuheng@163.com>
 */

namespace app\chat\model;

use \app\chat\model\Base;

class Redis3 extends Base
{
    /**
     * 获取redis实例对象
     *
     * @return  redis连接对象
     */
    protected static function getRedisInstance() : object
    {
        self::$Redis = parent::getRedisInstance();
        self::$Redis->select(3);
        return self::$Redis;
    }


    /**
     *  统计当前当值客服量
     *
     *  @return     当值客服量
     */
    public static function countOnlineServers() : int
    {
        $Redis = self::getRedisInstance();
        return $Redis->sSize('chat_servers_online'); 
    }


    /**
     * 添加新的客户连接
     * 
     * @$client_id  客户连接id
     * @$fingerprint    客户设备指纹
     */
    public static function addGuestConnect(string $client_id, string $fingerprint) : bool
    {
        $Redis = self::getRedisInstance();
        if ( $Redis->hSet('guest_connect', $client_id, $fingerprint) ) 
            return true;
        else 
            return false;
        
    }


    /**
     *  获取空闲座席的数量
     *
     */
    public static function getChatWaitingQueueLen() : int
    {
        $Redis = self::getRedisInstance();
        $len = $Redis->lLen('chat_waitting_queue');
        return $len;
    }


    /**
     * 获取客户连接哈希表的值
     *
     * @client_id   客户连接id
     * @return      数据库1的key键 
     */
    public static function getGuestConnectByKey(string $client_id) : string
    {
        $Redis = self::getRedisInstance();
        $key = $Redis->hGet('guest_connect', $client_id);
        return $key;
    }

   /**
     * 取一个空闲坐席
     *
     * @return uid    客服uid
     */ 
    public static function popChatWaittingQueue() : string 
    {
        $Redis = self::getRedisInstance();
        $uid = $Redis->lPop('chat_waitting_queue');
        return $uid;
    }
}
