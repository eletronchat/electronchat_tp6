<?php
namespace app\api\service;

use app\api\model\Member;
use think\facade\Request;
use app\api\service\Cache as CatchService;
use think\facade\Db;
use app\lib\exception\ErrorException;

class Token extends Base
{

    /**
    * 获取token
    * @return boolean
    */
    public function getToken()
    {
      $account = Request::param('account/s');
      $passwd  = get_hash(Request::param('passwd/s'));
      $member = (new Member())->where('account', $account)
        ->find();
      Db::startTrans();
      try{
        $member->web_last_ip = $member->web_current_ip;
        $member->web_current_ip = $_SERVER['REMOTE_ADDR'];
        $member->count = $member->count + 1;
        $member->web_login_time = time();
        $member->web_token = md5 ( uniqid ( rand (), true ) );
        $member->save();
        $db_prefix = config('database.prefix');
        $rules = Db::name('auth_rule')->alias('ar')
          ->where('ar.name', '<>', '')
          ->where('aga.uid', $member->uid)
          ->join("{$db_prefix}auth_group ag", "find_in_set(ar.id, ag.rules)")
          ->join("{$db_prefix}auth_group_access aga", "aga.group_id = ag.id")
          ->field('ar.name')
          ->select()->toArray();
        $rules = array_column($rules, 'name');
        $member_arr = $member->toArray();
        $member_arr['rules'] = json_encode($rules);
        $is_cache = (new CatchService())->cacheMember($member_arr);
        Db::commit();
      } catch(\Exception $e) {
        Db::rollback();
        throw new ErrorException();
      }
      if (!$is_cache) {
        throw new ErrorException(['msg'=>'缓存出错请联系管理员!']);
        Db::commit();
      }
      return $member->web_token;
    }

    
    /**
    * 登出
    *
    */
    public function logout()
    {
        $token = $_SERVER['HTTP_ACCESS_TOKEN'];
        Db::startTrans();
        try{
            $member = (new Member())->where('web_token', $token)->find();
            $member->web_logout_time = time();
            $member->web_token = '';
            $member->save();
            (new CatchService())->del('member_'.$token);
            Db::commit();
        } catch(\Exception $e) {
            Db::rollback();
            return false;
        }
        return true;
    }



}

