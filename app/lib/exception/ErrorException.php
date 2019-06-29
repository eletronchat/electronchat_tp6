<?php

namespace app\lib\exception;

use app\lib\exception\BaseException;

class ErrorException extends BaseException
{
     public $code      = 500;
     public $errorCode = 50000;
     public $msg       = "something has gone wrong on the web site's server";
}
