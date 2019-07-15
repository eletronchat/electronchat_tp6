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
        //$uid = json_decode($Redis->lPop('chat_waiting_group'), true)['uid'];
    }


    /**
     * 缓存客户信息
     *
     *  @client_id  string  客户连接id
     *  @data       array   $_SEVER和$_GET的http数据 
     */
    public static function cacheGuestData(string $client_id, array $data) 
    {
         $Redis = parent::getRedisInstance();
         $guest_data['ip'] =  $_SERVER['REMOTE_ADDR'];
         $guest_data['fingerprint'] = isset($data['get']['fingerprint']) ? $data['get']['fingerprint'] : '';
         $guest_data['current_url'] = isset($data['server']['HTTP_ORIGIN']) && isset($data['server']['REQUEST_URI'])? $data['server']['HTTP_ORIGIN'] . $data['server']['REQUEST_URI'] : '';
         $guest_data['device']      = array_key_exists('HTTP_USER_AGENT', $data['server']) ? parent::getClientIOS($data['server']['HTTP_USER_AGENT']) : '';
         $guest_data['client_id']   = $client_id;
         $guest_data['create_time'] = time();
         $message = array(
            "from"   => "/service/connect/guest",
            "to"     => "/service/listening/initGuest",
            "method" => "POST",
            "data"   => $guest_data,
         );
         //数据的第三方收集和持久化费时，就交给监听进程处理而不阻塞当前连接。
         $Redis->publish('listening',json_encode($message));
    }
}

