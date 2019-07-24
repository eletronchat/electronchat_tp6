<?php

/**
 *  聊天客户系统数据层-数据层基类
 *
 * File              : Base.php
 * @author           : wuchuheng <wuchuheng@163.com>
 * 
 * Date              : 24.07.2019
 * Last Modified Date: 24.07.2019
 * Last Modified By  : wuchuheng <wuchuheng@163.com>
 */

namespace app\chat\model;

class Base 
{
    protected static $Redis; //redis连接实例对象
    protected static $Db;    //数据库连接实例对象


    /**
     * 获取redis实例对象
     *
     * @return  redis连接对象
     */
    protected static function getRedisInstance() : object
    {
        
        if (!is_object(self::$Redis)) {
            $Redis = new \Redis();
            $Redis->connect('127.0.0.1', 6379);
            self::$Redis = $Redis;
        }
        return self::$Redis;
    }


    /**
     * 获取数据库连接实例对象
     *
     *
     */
    protected static function getDbInstance() : object
    {
        if (!is_object(self::$Db)) {
            $Db = new \Workerman\MySQL\Connection(
                '127.0.0.1',
                '3306',
                'root',
                '123qwe',
                'electronchat'
            );
            self::$Db = $Db;
        }
        return self::$Db;
    }

}

