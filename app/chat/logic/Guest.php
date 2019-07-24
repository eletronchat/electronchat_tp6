<?php

/**
 *  聊天客户系统逻辑层-客户处理类
 *
 * File              : Base.php
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
    Redis1 as Redis1Model
};

class Guest extends Base
{

    /**
    *   客户连接初始化
    *
    */
    public static function initConnect(string $client_id, array $data) 
    {
        //用于后期id关闭识别类型
        Redis3Model:: addGuestConnect($client_id, $data['get']['fingerprint']);
        //把客户的所有连接绑定到唯一uid, 用于后期找加客户的全部连接
        Gateway::bindUid($client_id, $data['get']['fingerprint']);
        //缓存数据并打是否新客户标记
        if (Redis1Model::isKey($data['get']['fingerprint'])) {
            Redis1Model::setHashByKey($data['get']['fingerprint'], 'is_news_guest', 0);
        } else {
            $guest_data['fingerprint'] = $data['get']['fingerprint'];
            $guest_data['ip'] =  $_SERVER['REMOTE_ADDR'];
            $guest_data['current_url'] = isset($data['server']['HTTP_ORIGIN']) && isset($data['server']['REQUEST_URI'])? $data['server']['HTTP_ORIGIN'] . $data['server']['REQUEST_URI'] : '';
            $guest_data['device']      = array_key_exists('HTTP_USER_AGENT', $data['server']) ? parent::getClientIOS($data['server']['HTTP_USER_AGENT']) : '';
            $guest_data['client_id']   = $client_id;
            $guest_data['create_time'] = time();
            $guest_data['name'] =  'Guest_' . time();
            $guest_data['is_news_guest'] = 1;
            Redis1Model::setHashMoreByKey($data['get']['fingerprint'], $guest_data);
        }
    
    }

}
