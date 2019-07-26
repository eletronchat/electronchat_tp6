<?php 
/**
 * Redis消息处理类 
 *
 * @auth wuchuheng
 * @email wuchuheng@163.com
 * @date  2019-07-14
 */
namespace app\chat\service;

use \app\chat\service\Base;
use \app\chat\model\{Redis3 as Redis3Model, Redis1 as Redis1Model};

class RedisMessage extends Base
{
    /**
     * 完善客户信息
     *
     */
    public static function initGuest(array $message)
    {
        $guest_data = $message['data'];
        // 是否广域网
        if (!filter_var($guest_data['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            $guest_data['is_wan'] = 0;
        } else {
            //广域网ip来源 	
            $guest_data['is_wan'] = 1;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,'http://ip.taobao.com/service/getIpInfo.php?ip=' . $guest_data['ip']);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 5); 
            $result = json_decode(curl_exec($ch), true);
            curl_close($ch);
            if ($result['code'] == 0) {
                $guest_data['ip_referer_country'] = $result['data']['country'];
                $guest_data['ip_referer_region']  = $result['data']['region'];
                $guest_data['ip_referer_city']    = $result['data']['city']     === 'XX' ? '' : $result['data']['city'];
                $guest_data['ip_referer_county']  = $result['data']['county']   === 'XX' ? '' : $result['data']['couty'];
                $guest_data['ip_referer_area']    = $result['data']['area']     === 'XX' ? '' : $result['data']['area'];
            }
        }
        //缓存用户数据
        Redis1Model::setHashMoreByKey($guest_data['fingerprint'], $guest_data);
        // 持久化
        /* unset($guest_data['client_id']); */
        /* if ($has_guest_data) { */
        /*     $update_id = $db->update('think_guest')->cols($guest_data)->where('fingerprint="' . $guest_data['fingerprint'] . "\"")->query(); */
        /* } else { */
        /*     $insert_id = $db->insert('think_guest')->cols($guest_data)->query(); */
        /* } */
    }

}

