<?php

/**
 * redis 缓存封装
 * @name    wuchuheng
 * @email   wuchuehng@163.com
 * @data    2019/06/23
 * @blog    www.wuchuheng.com
 */
namespace  app\api\service;

use think\facade\Db;

class Cache extends Base
{
    public static $_redis;

    /**
    * 获取redis连接实例 
    * return obj  redis 连接对象
    */
    public function getRedisInstance()
    {
      if(!isset(self::$_redis)) {
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        self::$_redis = $redis;
      }
      return self::$_redis;
    }


    /**
     * 获取缓存的用户权限规则
     * @param  string   $token    用户token
     * @return mix      用户规则|false
     *
     */
    public function getRulesByToken(string $token)
    {
        $key = 'member_' . $token;
        $has_data = $this->getRedisInstance()->hGet($key, 'rules');
        if ($has_data) {
            $has_data = json_decode($has_data);
        }
        return $has_data;
    }


    /**
     * 更新这个权限组下所有用户的缓存
     * @param   $id   ini  权限组id
     * @return  boolean
     */
    public function updateByAuthGroupId(int $id)
    {
        $prefix = config('database.prefix');
        $rules = Db::name('auth_rule')
          ->where('AG.id', $id)
          ->where('AR.name','<>', '')
          ->alias('AR')
          ->join("{$prefix}auth_group AG", "find_in_set(AR.id, AG.rules)")
          ->field("AR.name")
          ->select()
          ->toArray();
        if (!isset($rules[0]['name'])) return true;
        $rules = json_encode(array_column($rules, 'name'));
        // 更新web_token
        $has_tokens = Db::name('member')
          ->alias('M')
          ->where('AG.id', $id)
          ->where('M.web_token', '<>', '')
          ->field('concat("member_", M.web_token) as web_token')
          ->join("{$prefix}auth_group_access AGA", "AGA.uid = M.uid")
          ->join("{$prefix}auth_group AG", "AG.id = AGA.group_id")
          ->select()
          ->toArray();
        if (count($has_tokens) === 0 ) return true;
        $tokens = array_column($has_tokens, 'web_token');
        $count_fail = 0;
        foreach($tokens as $key) {
          if ($this->getRedisInstance()->hGetAll($key) ) {
              $is_cache  = $this->getRedisInstance()->hSet($key, 'rules', $rules);
              if (!$is_cache) $count_fail++;
          }
        }
        if ($count_fail !== 0 ) {
            return false;
        } else {
            return true;
        }
    }


    /**
    * 删除缓存
    * @param  string  key  键名
    * @return  boolean
    */
    public function del(string $key)
    {
      return $this->getRedisInstance()->del($key);
    }
    

    /**
    * 更新用户缓存
    * @param  $uid  int   用户id
    * @return boolean
    */
    public function updataByMemberUid(int $uid)
    {
        $prefix  = config('database.prefix]');
        $member = Db::name('member')
          ->where('uid', $uid)
          ->find();
        $is_in_cache = $this->getRedisInstance()
          ->hGetAll('member_' . $member['web_token']);
        if (!$is_in_cache) return true;

        $rules = Db::name('auth_rule')
          ->field('AR.name')
          ->where('AR.name', '<>', '')
          ->where('AGA.uid', $uid)
          ->alias('AR')
          ->join("{$prefix}auth_group AG", "find_in_set(AR.id, AG.rules)")
          ->join('auth_group_access AGA', 'AGA.group_id = AG.id')
          ->select()
          ->toArray();
        if(isset($rules[0]['name'])) {
            $rules  =  array_column($rules, 'name');
        }
        $member['rules'] = json_encode($rules);
        return $this->getRedisInstance()
          ->hMSet('member_' . $member['web_token'], $member);
    }



    /**
     * 缓存用户数据
     * @param    $member   用户数据
     *
     */
    public function cacheMember(array $member)
    {
        
        $key = 'member_' . $member['web_token'];
        $is_cache = $this->getRedisInstance()
          ->hMSet($key, $member);
        $has_config = Db::name('config')
          ->where('name', 'expire')
          ->field('value')
          ->find();
        if ($is_cache && isset($has_config['value']) && (int)$has_config['value'] > 0) {
          //添加过期时间
          $time = (int) $has_config['value'];
          $this->getRedisInstance()
              ->expire($key, $time);
          return true;
        } else {
          return false;
        }
    }
}

