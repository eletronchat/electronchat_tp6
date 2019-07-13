<?php
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
use \app\chat\service\Login;
use \app\chat\service\Check;
use \app\chat\service\Chat;
use \app\chat\service\Guest;


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
             if(Guest::letsChat()) {
                //将客户拉入空闲座席
                Guest::chatToWorker(); 
             } else {
                //否则进入排队等候
             }
           break;
           //控制台连接
           case '/service/admin/login' :
                 // ...
           break;
           //客服工作台连接
           case '/service/chat/login' :
                //连接验证
                $data['get']['access-token'] || Gateway::closeClient($client_id);
                $token = $data['get']['access-token'];
                if (!Check::chatConnect($token)) Gateway::closeClient($client_id);
                //更新当前客服连接
                Chat::updateConnectId($token, $client_id);
           break;
           // 断开其余非法连接
           default: 
             
             var_dump($data['server']['REQUEST_URI']);
             Gateway::closeClient($client_id);
      }
        //var_dump(Login::getClientIOS()); 
        //var_export($data);
        //if (!isset($data['get']['token'])) {
        //     Gateway::closeClient($client_id);
        //}
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
        Gateway::sendToClient($client_id, "Hello $client_id\r\n");
        // 向所有人发送
        Gateway::sendToAll("$client_id login\r\n");
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
        Gateway::sendToAll("$client_id said $message\r\n");
   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id)
   {
       // 向所有人发送 
       GateWay::sendToAll("$client_id logout\r\n");
   }
}
