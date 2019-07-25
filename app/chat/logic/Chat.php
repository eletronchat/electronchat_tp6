<?php

/**
 *  聊天客户系统逻辑层-客服工作台处理类
 *
 * File              : Base.php
 * @author           : wuchuheng <wuchuheng@163.com>
 * 
 * Date              : 24.07.2019
 * Last Modified Date: 24.07.2019
 * Last Modified By  : wuchuheng <wuchuheng@163.com>
 */

namespace app\chat\logic;

use \app\chat\logic\Base;
sue \app\chat\model\{Redis3 as Redis3Model};

class Chat extends Base
{

    /**
    *   是否有客服当值
    *
    */
    public static function isServerOnline() : bool
    {
        $count = Redis3Model::countOnlineServers();
        if ($count > 0) 
            return true;
        else 
            return false;
    }


    /**
     * 是否还有空闲座席
     * 
     */
    public static function isHasEmptySeat() : bool
    {
        $len = Redis3Model::getChataWitingQueueLen();
        if ($len > 0) 
            return true;
        else
            return false;
    }
     
}
