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

class RedisMessage extends Base
{
     /**
      * 初始客户信息
      *
      */
     public static function initGuest(array $message)
		 {
         $guest_data = $message['data'];
				 global $db;
				 $has_guest_data = $db->select('*')
					 ->from('think_guest')
					 ->where('fingerprint= :fingerprint')
					 ->bindValues(array('fingerprint'=>$guest_data['fingerprint']))
					 ->row();
         //是回头客
         if ($has_guest_data) {
             $gust_data = $has_guest_data;
         // 新客户
				 } else {
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
				}
         //缓存用户数据
         global $config;
         $Redis = new \Redis();
         $Redis->connect($config['redis']['host'], $config['redis']['port']);
         $Redis->select(1);
         $expire = $config['redis']['expire'];
         $Redis->hMSet($guest_data['client_id'], $guest_data); 
				 $Redis->expire($guest_data['client_id'], $expire);
         $Redis->select(3);
         // 持久化
           unset($guest_data['client_id']);
           $guest_data['name'] = isset($guest_data['name']) && $guest_data['name'] !== null ? $guest_data['name'] : 'Guest_' . time();
					 $insert_id = $db->insert('think_guest')->cols($guest_data)->query();
		 }

}

