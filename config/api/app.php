<?php 

return [
  // 异常处理handle类 
  'exception_handle'       => '\app\lib\exception\ExceptionHandler',
  //日志异常日志配置
  'log' => [
    'path' => \think\facade\App::getRootPath(). 'log/'
  ]
];
