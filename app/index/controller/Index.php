<?php
namespace app\index\controller;

class Index
{
    public function index()
    {
      return  <<<EOF
                <html>
                      <body>
                          <p>登录</p>
                          <p>后台</p>
                          <p><a href="/admin">adminin</a></p>
                      </body>
                </html>
EOF;
    }
}
