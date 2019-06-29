<?php

namespace app\lib\exception;

class ParameterException extends BaseException
{
    protected $code      = 400;
    protected $errorCode = 10000;
    protected $msg       = "invalid parameters";
}
