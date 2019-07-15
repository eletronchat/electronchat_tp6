<?php 
/**
 * 连接验证类
 *
 * @auth wuchuheng
 * @email wuchuheng@163.com
 * @date  2019-07-13
 */
namespace app\chat\service;

use \app\chat\service\Base;
use \GatewayWorker\Lib\Gateway;

class Check extends Base 
{
  public static $error_message = array(
       'from'=> '/service/chat/login',
       'to'  => '/local/chat',
       'data'  => ['type'=>'warning'],
  );

    /**
    * 客服工作台连接验证
    *
    * @token    string  连接令牌
    * @data     array   首次连接的http数据
    * @return   mix     错误信息或true 
    */
    public static function chatConnect(array $data)
    {
      self::$error_message['time'] = microtime();
      if (!isset($data['get']['access-token'])) {
        self::$error_message['data']['message'] = '请提交令牌';
        return false;
      }
      $token = $data['get']['access-token'];
        $Redis = parent::getRedisInstance(); 
        $Redis->select(0);
        $has_data = $Redis->hGetAll('member_' . $token);
        if (count($has_data) === 0) {
          self::$error_message['data']['message'] = '令牌过期';
          return false;
        } else  {
          return true;
        }
    }


     /**
      * 发送错误并关闭连接
      *
      * $client_id   string  连接id
      */ 
    public static function sendMessageAndCloseByClientId(string $client_id) {
         Gateway::sendToClient($client_id, json_decode(self::$error_message));
         Gateway::closeClient($client_id);
    }

}
