<?php

/**
*异常接管处理类
*/

namespace app\lib\exception;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\facade\Request;
use app\lib\exception\BaseException;
use think\facade\Log;
use think\facade\App;
use think\facade\Env;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{

    private $code;      //the statu code for http response
    private $msg;
    private $errorCode; //the custom code
    private $url;       //the request url
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 添加自定义异常处理机制
         if ($e instanceof BaseException){
            //to assign the custom exctpion data;
            $this->code      = $e->code;
            $this->msg       = $e->msg;
            $this->errorCode = $e->errorCode;
        }else{
            //return the frendly exception when the debug mode is true  
            if (Env::get('app_debug')) {
              return parent::render($request, $e);
            } else {
                $this->code      = 500;
                $this->msg       = 'sorry，we make a mistake. (^o^)Y';
                $this->errorCode = 999;
               Log::record($this->msg, 'error');
            }
        }
        return json([
            'msg'       => $this->msg,
            'url'       => Request::url(),
            'errorCode' => $this->errorCode
        ], $this->code);

    }

}
