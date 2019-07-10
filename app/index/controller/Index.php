<?php

namespace app\index\controller;

use think\facade\View;

class Index
{
    public function index()
    {
      return  <<<EOF
                <html>
                      <body>
                          <p>登录</p>
                          <p><a href="/admin">后台</a></p>
                          <p><a href="http://electronchat_tp6.com/admin/views/chat/test/">聊天测试页</a></p>
                      </body>
                </html>
EOF;
    }


     /**
       * 测试页面
       *
       */ 
    public function test() {
         return View::fetch('test'); 
    }
}
