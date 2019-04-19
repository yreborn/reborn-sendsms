# reborn-sendsms
发送短信

1.在config目录下新增sendsms.php
return [
    'txy_appid' =>'xxxx',//短信appid
    'txy_appkey' =>'xxxx',//短信key
    'txy_url' =>'xxxxx',//短信联系
    'checkurl' =>'xxxx',//网站链接
    'tpl_1' =>'xxxx',//模板id
    'tpl_content_1' =>'xxxx',//模板内容
];

2、demo
<?php

namespace app\index\controller;

use reborn\sendsms\SendSms;

class Index
{

  public function sendsms()
    {
        $phone='158xxx';//手机号
        $data=mt_rand('100000','999999');//短信1的内容
        $param=[$data];
        $res=(new SendSms())->send($phone,1,$param);
        return $res;
    }
}