<?php

/**
 *  聊天客户系统逻辑层-客服工作台处理类
 *
 * File              : Chat.php
 * @author           : wuchuheng <wuchuheng@163.com>
 * 
 * Date              : 24.07.2019
 * Last Modified Date: 24.07.2019
 * Last Modified By  : wuchuheng <wuchuheng@163.com>
 */

namespace app\chat\logic;

use \app\chat\logic\Base;
use \GatewayWorker\Lib\Gateway;
use \app\chat\model\{
    Redis3 as Redis3Model,
    Redis1 as Redis1Model,
    Redis0 as Redis0Model
};
use \app\chat\logic\Guest as GuestLogic;

class Chat extends Base
{

    /**
    *   是否有客服当值
    *
    */
    public static function isServerOnline() : bool
    {
        $count = Redis3Model::countOnlineServers();
        if ($count > 0) 
            return true;
        else 
            return false;
    }


    /**
     * 是否还有空闲座席
     * 
     * @return boolean
     */
    public static function isHasEmptySeat() : bool
    {
        $len = Redis3Model::getChatWaitingQueueLen();
        if ($len > 0) 
            return true;
        else
            return false;
    }


    /**
     * 客服人员同客户建立起连接
     *
     * @client_id 用户连接id
     */
    public static function serverConnectGuest(string $client_id) 
    {
        $guest_uid  = Gateway::getUidByClientId($client_id);
        $server_uid = Redis3Model::popChatWaittingQueue();
        Redis1Model::setHashByClientId($client_id, ['uid'=>$server_uid]);
        Gateway::joinGroup($client_id, $server_uid);
        $server_info = self::getUserInfoByUid($server_uid);
        $guest_info   = GuestLogic::getGuestInfoByUid($guest_uid);
        // 欢迎语新用户和通知客服工作人员
        if (GuestLogic::isNewsGuest($client_id)) {
            Gateway::sendToUid($guest_uid, json_encode([
                'content_type' => 'text',
                'content' => '很高兴为您服务！',
                'microtime' => microtime(true),
                'speaker'  => $server_info['nick_name'],
                'is_me_speaker' => false,
            ]));
            Gateway::sendToUid($server_uid, json_encode([
                'form' => '/service/connect/guest/' . $client_id,
                'to'   => '/local/chat/index/message/onlineList',
                'data' => [
                    'type'           => 'news_guest',
                    'content_tpye'=> 'text',
                    'content'        => '很高兴为您服务！',
                    'speaker'      => $guest_info['name'],
                    'is_me_speaker'  => true,
                ],
                'microtime' => microtime(true)
            ]));
            
        }
    }


    /**
     * 通过用户uid获取用户信息
     *
     * @uid     用户uid
     * @return  用户信息
     */
    public static function getUserInfoByUid(string $uid)
    {
        $client_ids =  Gateway::getClientIdByUid($uid);
        $client_id  =  $client_ids[0];
        $userInfo   =  Redis0Model::getMemberByClientId($client_id);
        return $userInfo;
    }

}
