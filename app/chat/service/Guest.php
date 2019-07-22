<?php /**
* File              : Guest.php
* @author           : wuchuheng <wuchuheng@163.com>
* Date              : 20.07.2019
* Last Modified Date: 20.07.2019
* Last Modified By  : wuchuheng <wuchuheng@163.com>
 */
/**
 * 客户业务基类 
 *
 * @auth wuchuheng
 * @email wuchuheng@163.com
 * @date  2019-07-12
 */
namespace app\chat\service;

use app\chat\service\Base;
use \GatewayWorker\Lib\Gateway;

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


    /**
    * 没有客服当值，缓存数据
    * 
    * @client_id    客户连接id
    * @data         客户信息
    */
    public static function cacheToMissingSet(string $client_id, array $data)
    {
        //客服不在线，进入错过队列
        $Redis = parent::getRedisInstance();
        $Redis->select(3);
            $Redis->lPush('missing_guest', json_encode(array(
                'client_id' => $client_id,
                'time'      => microtime(true)
            )));
    }


    /**
     *  清理客户连接缓存
     *
     *
     */
    public static function delConnectCacheByCId(string $client_id)
    {
        $Redis = self::getRedisInstance();
        $Redis->select(1);
        $Redis->del($client_id);
    }


    /**
     *  招待客户
     *
     *
     */
    public static function welcome(string $client_id) 
    {
        $Redis = parent::getRedisInstance();
        $Redis->select(3); 
        //进入空闲座席
        $uid = $Redis->lPop('chat_waiting_group');
        Gateway::joinGroup($client_id, $uid);  
        //通知客服人员有新客服
        $Redis->select(1);
        $guest_name = $Redis->hGet($client_id, 'name');
        Gateway::sendToUid($uid, json_encode([
            'from'  => '/service/connect/guest/' . $client_id, 
            'to'    => '/local/chat/index/message/onlineList',
            'data'  =>  [
                'speaker'    => 'me',
                'guest_name' => $guest_name,
                'content'    => '您好，有什么可以帮助吗？'
            ],
            'microtime'  => microtime(true)
        ]));
        // 给用户发送消息
        $chat_client_ids = Gateway::getClientIdByUid($uid);
        $chat_client_id  = reset($chat_client_ids);
        $Redis->select(3);
        $db0_key         = $Redis->hGet('chat_connect', $chat_client_id);
        $Redis->select(0);
        $server_member_info = $Redis->hGetAll($db0_key);
        Gateway::sendToClient($client_id, json_encode([
            'type' => 'welcome',
            'data' => ['name' => $server_member_info['nick_name'], 
            'content' => '很高兴为你服务'
        ],
        'microtime' => microtime(true)
    ]));
        // 缓存这个客户连接指向给它服务的工作人员uid
        $Redis->select(1);
        $Redis->hSet($client_id, 'uid', $uid);
    }
}

