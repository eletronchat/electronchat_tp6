<?php 
/**
 * 数据库IO 消息接收 并处理 
 * 
 * @author   wuchuehng
 * @email    wuchuheng@163.com
 * @date     2019-07-14
 */

use \Workerman\Worker;
use \app\chat\service\Base;
use \Workerman\Lib\Timer;

require_once __DIR__ . '/../../vendor/autoload.php';

$worker = new Worker('websocket://0.0.0.0:8484');
$worker->name = 'listening message progress'; //消息监听进程
$worker->count  = 1;
$worker->onWorkerStart = function($worker)
{
     define('CAHCE_MEMBER_DB', 0); //工作成员缓存数据库 
     define('CAHCE_GUEST_CONNECT_DB', 1); //客户人员缓存数据库
     define('CAHCE_CHAT_LOG_DB', 2);   //客户聊天缓存数据库 
     define('CAHCE_INTERATION_DB', 3); //各种数据交互缓存数据库
     //配置
     global $config; 
		 $config = [
       'redis' => [
           'expire' => 60*60*24,
           'host'   => '127.0.0.1',
           'port'   => 6379,
				]
		];
     global $RedisMessage;
     $RedisMessage = new \app\chat\service\RedisMessage();
     global $db;
     $db = new \Workerman\MySQL\Connection('127.0.0.1', '3306', 'root', '123qwe', 'electronchat');
     global $Redis;
     $Redis = Base::getRedisInstance();
     $Redis->setOption(Redis::OPT_READ_TIMEOUT, -1);
     $Redis->subscribe(['listening'], function($instance, $channelName, $message) {
         global $RedisMessage; 
         $message = json_decode($message, true);
          switch($message['to']) {
              //初始化用户信息
              case '/service/listening/initGuest' :
								$is_ok = $RedisMessage::initGuest($message);
              break;
          }
     });
};
