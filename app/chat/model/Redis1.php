<?php

/**
 *  聊天客户系统数据层-redis数据库1业务类
 *
 * File              : Base.php
 * @author           : wuchuheng <wuchuheng@163.com>
 * 
 * Date              : 24.07.2019
 * Last Modified Date: 24.07.2019
 * Last Modified By  : wuchuheng <wuchuheng@163.com>
 */

namespace app\chat\model;

use \app\chat\model\Base;
use \app\chat\model\{Redis3 as Redis3Model};

class Redis1 extends Base
{
    /**
     * 获取redis实例对象
     *
     * @return  redis连接对象
     */
    protected static function getRedisInstance() : object
    {
        self::$Redis = parent::getRedisInstance();
        self::$Redis->select(1);
        return self::$Redis;
    }


    /**
     *  是否存在一个键
     * 
     *  @key    键名
     *  return  boolean
     */
    public static function isKey(string $key) : bool
    {
        $Redis = self::getRedisInstance();
        return $Redis->exists($key);
    }



    /**
    *  修改或者添加哈希值
    *  
    * @key     键名
    * @field   字段名 
    * @value   值
    * @return boolean;
    */
    public static function setHashByKey(string $key, string $field, int $value) : bool
    {
        $Redis = self::getRedisInstance();
        if ($Redis->hSet($key, $field, $value) ) {
            return true;
        } else {
            return false;
        }
    }


    /**
    *  修改或者添加多个哈希值
    *  
    * @key     键名
    * @field   字段名 
    * @value   值
    * @return boolean;
    */
    public static function setHashMoreByKey(string $key, array $value) : bool
    {
        $Redis = self::getRedisInstance();
        if ($Redis->hMSet($key, $value) ) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 获取客户数据
     * 
     * @key     键名
     * @field   字段名
     * return   客服信息
     */
    public static function getDataByKey(string $key, string $field = '') : array
    {
        $Redis = self::getRedisInstance();
        if ($field === '') {
            return $Redis->hGetAll($key);
        } elseif ($Redis->hExists($key, $field)) {
            $val = $Redis->hGet($key, $field);
            return  [$field => $val];
        } else {
            return [];
        }
    }


    /**
    * 通过客户连接id设置客户信息
    * 
    * @client_id    客户连接id
    * @data         要设置的数据数组
    * @return       boolean
    */
    public static function setHashByClientId(string $client_id, array $data) : bool
    {
        $db3Redis = Redis3Model::getRedisInstance();
        $db1_key  = $db3Redis->hGet('guest_connect', $client_id);
        $db1Redis = self::getRedisInstance();
        if (gettype($db1_key) !== 'string' || strlen($db1_key) == 0) {
           return false; 
        }
        if ($db1Redis->hMSet($db1_key, $data)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     *  通过用户连接id获取用户信息
     *
     * @client_id   string 
     * @return      用户信息
     */
    public static function  getGuestByClientId(string $client_id) 
    {
       $Redis3 = Redis3Model::getRedisInstance();
       $db1_key = $Redis3->hGet('guest_connect', $client_id);
       $Redis1 = self::getRedisInstance();
       $guest_info = $Redis1->hGetAll($db1_key);
       return $guest_info;
    }

}
