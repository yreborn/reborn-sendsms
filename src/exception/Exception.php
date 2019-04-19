<?php
/**
 * Created by PhpStorm.
 * User: Reborn
 * Date: 2019/4/19
 * Time: 9:54
 */

namespace reborn\sendsms\exception;

class Exception extends \Exception
{
    private $code=200;
    private $msg='获取成功';

    public function __construct()
    {
        $result=[
            'code'=>$this->code,
            'msg'=>$this->msg,
        ];
        return json($result,200);
    }
}