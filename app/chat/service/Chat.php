<?php 
/**
 * 聊天业务服务类
 *
 * @auth wuchuheng
 * @email wuchuheng@163.com
 * @date  2019-07-13
 */

namespace app\chat\service;

use \app\chat\service\Base;
use \GatewayWorker\Lib\Gateway;

class Chat extends Base
{
    /**
     * 初始化当前连接
     *
     *  $token  string  用户令牌
     */ 
    public static function initConnect(string $token, string $client_id)
    {
       $redis_key = 'member_' . $token;
       $Redis = parent::getRedisInstance(); 
       $Redis->select(0);
       $member_data = $Redis->hGetAll($redis_key);
       //释放空闲座席入队
       $Redis->select(3);
       for($i = 0 ; $i < (int)$member_data['receives']; $i++) {
         $group_data = [
           'uid'=> $member_data['uid'],
           'create_time'=> microtime(true)
         ];
         $Redis->Lpush('chat_waiting_group',json_encode($group_data));
       }
       $Redis->select(0);
       //检测web_chat连接
       if (!array_key_exists('web_chat_connect_id', $member_data)) {
           $Redis->hset($redis_key, 'web_chat_connect_id', $client_id);
       } elseif($member_data['web_chat_connect_id'] != $client_id) {
           //剔除旧的web_chat连接
           Gateway::closeClient($member_data['web_chat_connect_id']);
       }
       //将所有这个账号的连接绑定到uid
       Gateway::bindUid($client_id, $member_data['uid']);
       $Redis->hset($redis_key, 'web_chat_connect_id', $client_id);
       //将所有这个账号的都连接绑定到uid组
       Gateway::joinGroup($client_id, $member_data['uid']);
       //对外挂牌当前帐号id在线聊天状态
       $Redis->select(3); 
       $Redis->sAdd('chat__servers_online', $member_data['uid']);
       //保存客服连接指针，指向帐户详情
       $Redis->select(4);
       $Redis->set($client_id, $redis_key);
    }
}
