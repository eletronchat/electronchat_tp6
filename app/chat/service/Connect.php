<?php 

/**
 * 连接服务封装类
 *
 * File              : Connect.php
 * @author           : wuchuheng <wuchuheng@163.com>
 * Date              : 20.07.2019
 * Last Modified Date: 20.07.2019
 * Last Modified By  : wuchuheng <wuchuheng@163.com>
 */

namespace app\chat\service;

use \app\chat\service\Base;
use \GatewayWorker\Lib\Gateway;
use \app\chat\logic\{Guest as GuestLogic, Chat as ChatLogic};

class Connect extends Base 
{

    /**
    *   客户连接服务
    *   
    *   @client_id  连接id 
    *   @data       连接信息
    *
    */
    public static function guest(string $client_id, array $data) 
    {
        // 客户连接初始化
        GuestLogic::initConnect($client_id, $data); 
        // 有客服人员当值，进行接待
        if (ChatLogic::isServerOnline()) {
            if (ChatLogic::isHasEmptySeat()) {
            
            } 
        } else {
        // 没有客服当值,进入
        }
        //if(Guest::isServerOnline()) {
        //    Guest::cacheGuestData($client_id, $data); //缓存客户信息
        //    Guest::welcome($client_id);                //将客户拉入空闲座席
        //} else {
        //    // 客服不在线处理 
        //    Guest::cacheToMissingSet($client_id, $data);
        //    Gateway::sendToClient($client_id, json_encode(array(
        //        'type' => 'miss',
        //        'time' => microtime(true)
        //    ))); 
        //}
    }
}
