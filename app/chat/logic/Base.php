<?php 

/**
 *  聊天客户系统逻辑层基类
 *
 * File              : Base.php
 * @author           : wuchuheng <wuchuheng@163.com>
 * @warning          : 在chat这个所有有数据数据库连接和redis连接只能交给给model来处理，
 * 换句来说，涉及数据的读写统一交给model类来完成。而其它的地方就不要擅自创建数据
 * 读写连接和操作。
 * 
 * Date              : 20.07.2019
 * Last Modified Date: 20.07.2019
 * Last Modified By  : wuchuheng <wuchuheng@163.com>
 */

namespace app\chat\logic;

class Base 
{

    /**
     *  获取用户设备连接设备
     *  
     *  @warning 这个方法只能在首次连接才能调用
     *  @retrun string  设备操作系统名 
     */
    public static function getClientIOS($agent)
    {
        $agent = strtolower($agent);
        if(strpos($agent, 'windows nt')) {
            $platform = 'windows';
        } elseif(strpos($agent, 'macintosh')) {
            $platform = 'mac';
        } elseif(strpos($agent, 'ipod')) {
            $platform = 'ipod';
        } elseif(strpos($agent, 'ipad')) {
            $platform = 'ipad';
        } elseif(strpos($agent, 'iphone')) {
            $platform = 'iphone';
        } elseif (strpos($agent, 'android')) {
            $platform = 'android';
        } elseif(strpos($agent, 'unix')) {
            $platform = 'unix';
        } elseif(strpos($agent, 'linux')) {
            $platform = 'linux';
        } else {
            $platform = 'other';
        }
        return $platform;
    }
}

