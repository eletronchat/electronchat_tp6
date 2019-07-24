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
}
