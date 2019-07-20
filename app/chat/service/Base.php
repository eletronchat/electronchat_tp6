<?php 
/**
 * 聊天业务基类 
 *
 * @auth wuchuheng
 * @email wuchuheng@163.com
 * @date  2019-07-12
 */
namespace app\chat\service;

class Base
{
    public static $redis;

    /**
     * redis 连接实例
     *
     * return obj 连接对象
     */
    public static function getRedisInstance()
    {
        if (!is_object(self::$redis)) {
            $redis = new \Redis();
            $redis->connect('127.0.0.1', 6379);
            self::$redis = $redis;
        }
        return self::$redis;
    }

    /**
     *  获取用户设备连接设备
     *  
     *  @warning 这个方法只能在首次连接才能调用
     *  @retrun string  设备操作系统名 
     */
    public static function getClientIOS($agent)
    {
        $agent = strtolower($agent);
        if(strpos($agent, 'windows nt')) {
            $platform = 'windows';
        } elseif(strpos($agent, 'macintosh')) {
            $platform = 'mac';
        } elseif(strpos($agent, 'ipod')) {
            $platform = 'ipod';
        } elseif(strpos($agent, 'ipad')) {
            $platform = 'ipad';
        } elseif(strpos($agent, 'iphone')) {
            $platform = 'iphone';
        } elseif (strpos($agent, 'android')) {
            $platform = 'android';
        } elseif(strpos($agent, 'unix')) {
            $platform = 'unix';
        } elseif(strpos($agent, 'linux')) {
            $platform = 'linux';
        } else {
            $platform = 'other';
        }
        return $platform;
    }


    /**
     * 是否客户连接
     *
     * @return boolean
     */
    public static function isGuestConnect(string $client_id) : bool
    {
        $Redis = self::getRedisInstance();
        $Redis->select(1);
        if ($Redis->exists($client_id)) 
            return true;
        else
            return false;
    }


    /**
     * 客服是否在线
     *
     */
    public static function isServerOnline() : bool
    {
        $Redis = self::getRedisInstance();
        $Redis->select(3);
        if ($Redis->scard('chat_servers_online') > 0) {
            return true;
        } else {
            return false;
        }
    }
}

