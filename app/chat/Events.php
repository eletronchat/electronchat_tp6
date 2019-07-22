<?php
/**
 * File              : Events.php
 * @author           : wuchuheng <wuchuheng@163.com>
 * Date              : 20.07.2019
 * Last Modified Date: 20.07.2019
 * Last Modified By  : wuchuheng <wuchuheng@163.com>
 */
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;
use \app\chat\service\{Login, Check, Guest, Chat};

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    public static function onWebSocketConnect($client_id, $data)
    {
      //根据路由进行连接验证 
      switch(parse_url($data['server']['REQUEST_URI'])['path']) {
           //客户连接 
           case '/service/guest/login':
             if(Guest::isServerOnline()) {
                 //缓存客户信息
                 Guest::cacheGuestData($client_id, $data);
                //将客户拉入空闲座席
                Guest::welcome($client_id);
             } else {
                // 客服不在线处理 
                Guest::cacheToMissingSet($client_id, $data);
                Gateway::sendToClient($client_id, json_encode(array(
                    'type' => 'miss',
                    'time' => microtime(true)
                ))); 
             }
           break;

           //控制台连接
           case '/service/admin/login' :
                 // ...
           break;

           //客服工作台连接
           case '/service/chat/login' :
             //连接验证
             if (!Check::chatConnect($data)) {
               Check::sendMessageAndCloseByClientId($client_id);
             } else {
              //初始化连接
              Chat::initConnect($data['get']['access-token'], $client_id);
             }
           break;
           // 断开其余非法连接
           default: 
             var_dump($data['server']['REQUEST_URI']);
             Gateway::closeClient($client_id);
      }
    }

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {
        // 向当前client_id发送数据 
        //Gateway::sendToClient($client_id, "Hello $client_id\r\n");
        // 向所有人发送
        //Gateway::sendToAll("$client_id login\r\n");
    }
    
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $message)
   {
        //var_dump($client_id);
        // 向所有人发送 
        //Gateway::sendToAll("$client_id said $message\r\n");
   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id)
   {
     // 客服设备中断处理
     if (Chat::isChatConnect($client_id)) {
        $uid = Chat::getUidByCientId($client_id);
        Chat::delConnectCacheByCId($client_id);
        if (count(Gateway::getClientIdByUid($uid)) === 0) {
          //当前账号所有设备都下线了，撤回挂牌在线
          Chat::withdrawOnlineByUid($uid); 
          // 撤回空闲座席
          Chat::withdrawChatWaitingGroup($uid);
          // .. 其它处理， 当前客户转移，或者等待10s,还是不能转移就向客户道歉原因
        }
     }
     //客服连接中断处理 
     if (Guest::isGuestConnect($client_id)) {
         Guest::delConnectCacheByCId($client_id);
     }

   }
}
