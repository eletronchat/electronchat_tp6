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
     * 断开当前客服账号其它连接
     *
     *  $token  string  用户令牌
     */ 
    public static function updateConnectId(string $token, string $client_id)
    {
        $redis_key = 'member_' . $token;
        $Redis = parent::getRedisInstance(); 
        $Redis->select(0);
        $member_data = $Redis->hGetAll($redis_key);
        //检测座席
        if (!array_key_exists('group_ids', $member_data)) {
          for($i = 1; $i<= (int)$member_data['receives']; $i++) {
              $member_data['group_ids'][] = uniqid();
          }
          $member_data['group_ids'] = json_encode(array_filter($member_data['group_ids']));
          $Redis->hMSet($redis_key, $member_data);
          //入队待命座席
          $Redis->select(3);
          foreach(json_decode($member_data['group_ids']) as $group_id) {
            $list_data = [
              'uid'=> $member_data['uid'],
              'create_time'=> microtime(true)
            ];
            $Redis->Lpush('chat_waiting_group',json_encode($list_data));
          }
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
    }
}
