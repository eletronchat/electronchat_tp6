<?php

namespace app\api\validate;

class IdMustBeInt extends Base
{
    protected $rule = [
        'id' => 'require'
    ];
    
}
