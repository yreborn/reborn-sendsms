<?php
/**
 * Created by PhpStorm.
 * User: Reborn
 * Date: 2019/4/19
 * Time: 9:55
 */

namespace reborn\sendsms\exception;

class ParamException extends Exception
{
    public $code=400;

    public $msg='参数错误';
}