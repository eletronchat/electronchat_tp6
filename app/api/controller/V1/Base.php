<?php 
/**
 * api 接口基类
 * @name    wuchuheng
 * @email   wuchuehng@163.com
 * @data    2019/05/13
 * @blog    www.wuchuheng.com
 */
namespace app\api\controller\V1;

use think\Controller;
use think\Db;
use think\facade\Env;
use app\api\service\Cache;
use app\api\validate\Token as TokenValidate;
use think\facade\Request;
use think\facade\App;
use think\facade\Log;
use app\lib\exception\ErrorException;

class Base 
{
    public function __construct()
    {
      if (!isset($_SERVER['HTTP_ACCESS_TOKEN'])) {
        throw new ErrorException(['msg'=>'请登录!', 'errorCode' => 1001, 'code'=>200]);
      }
      //过期验证
      $has_rules = (new Cache())->getRulesByToken($_SERVER['HTTP_ACCESS_TOKEN']);
      if (!$has_rules) {
        throw new ErrorException(['msg'=>'很抱歉！由于您的登录信息已过期，请重新登录', 'errorCode' => 1001, 'code'=>200]);
      }
      //权限验证
      $rule = Request::app() . '/' . request()->controller() . '/' . request()->action();
      if (!in_array($rule, $has_rules)) {
        throw new ErrorException(['msg'=>'您的权限不足，操作无效', 'code'=>200, 'errorCode'=>500000]);
      } 

      if (Env::get('status', 'null') === 'dev') {
        $class = __NAMESPACE__ . '\\' . substr(request()->controller(), 3);
        $reflector = new \reflectionClass($class);
        foreach ($reflector->getMethods() as $v){
          if (strtolower($v->name) === request()->action()) {
              $commend = explode("* ", $v->getDocComment())[1];
              $rule = request()->module() . '/' . request()->controller() . '/' . request()->action();
              $hasData = Db::name('auth_rule')->where('name', $rule)->find();
              $isSave = Db::name('tmp')->where('title', $commend)->where('name', $rule)->find();
              if (!$hasData && !$isSave) {
                Db::name('tmp')->insert(['title'=>$commend, 'name'=>$rule]);
              }
          }
        }
        $authName = Db::name('tmp')->select();
        foreach($authName as $v) {
          if (Db::name('auth_rule')->where('name', $v['name'])->find()) {
              Db::name('tmp')->where('name', $v['name'])->delete();
          }
        }
      }

    }


    /**
     * 返回成功消息
     * @style   array  数据返回风格格式，默认layui风格
     */  
    public function successMessage($mix = [])
    {
      if (gettype($mix) === 'object') $mix = $mix->toArray();
      //dtree 风格
      if (array_key_exists('style', $mix) && $mix['style'] === 'dtree') 
      {
        return json(['status'=>['code' => 200, 'massage' => 'success', ], 'data' => $mix['data'] ]);
      }
      //默认layui风格
      $data = array_key_exists('data', $mix) ? $mix['data'] : $mix;
      $result =  [
        'errorCode' => 0,
        'msg'  => 'sucess',
        'data' => $data
      ]; 
      //自定义添加自定义字段
      if (array_key_exists('data', $mix)) {
          unset($mix['data']);
          $result = array_merge($mix, $result);
      }
      return json($result);
    }
}



