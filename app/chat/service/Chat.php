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
         $Redis->Lpush('chat_waiting_group',$member_data['uid']);
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
       $Redis->sAdd('chat_servers_online', $member_data['uid']);
       //保存客服连接指针，指向帐户详情
       $Redis->hSet('chat_connect', $client_id, $redis_key);
    }


    /**
     * 是否聊天连接
     * 
     * @$client_id  string  连接id
     * @return boolean
     */
    public static function isChatConnect(string $client_id)
    {
        $Redis = parent::getRedisInstance();
        $Redis->select(3);
        return $Redis->hExists('chat_connect', $client_id);
    }


    /**
     * 删除连接缓存
     *
     * return boolean
     */
    public static function delConnectCacheByCId(string $client_id)
    {
        $Redis = parent::getRedisInstance();
        $Redis->select(3);
        $Redis->hDel('chat_connect', $client_id);
    }


    /**
    * 获取uid
    *
    * @client_id  连接id
    * @return numberic|false
    */
    public static function getUidByCientId(string $client_id)
    {
        $Redis = parent::getRedisInstance(); 
        $Redis->select(3);
        if (!$Redis->hExists('chat_connect', $client_id)) {
            return false;  
        } else {
            $db0_key = $Redis->hGet('chat_connect', $client_id);
            $Redis->select(0);
            $uid = $Redis->hGet($db0_key, 'uid');
            return $uid; 
        }
    }



    /**
     * 撤回挂牌
     *
     */
    public static function withdrawOnlineByUid(string $uid)
    {
        $Redis = self::getRedisInstance();
        $Redis->select(3);  
        $Redis->srem('chat_servers_online', $uid);
    }


    /**
     * 撤回空闲座席
     *
     */
    public static function withdrawChatWaitingGroup($uid)
    {
      $Redis = parent::getRedisInstance(); 
      $Redis->select(3);
      $len = $Redis->lLen('chat_waiting_group');
      if ($len > 0) {
        for ($i = 0; $i < $len; $i++) {
          $q_uid = $Redis->lPop('chat_waiting_group');
          if ($q_uid !== $uid) {
              $q_uid = $Redis->lPush('chat_waiting_group', $q_uid);
          }
        }
      }
    }
}
