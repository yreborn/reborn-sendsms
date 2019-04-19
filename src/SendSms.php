<?php
/**
 * Created by PhpStorm.
 * User: Reborn
 * Date: 2019/4/19
 * Time: 9:02
 */

namespace reborn\sendsms;

use think\Exception;
use think\facade\Log;
use think\facade\Config;
use reborn\sendsms\exception\ParamException;

class SendSms
{
    private $time;
    private $appid;
    private $appkey;
    private $code;
    private $url;
    private $phone;
    private $tplid;
    private $param=[];

    function __construct()
    {
        $this->time=time();
        $this->appid=Config::get('sendsms.txy_appid');
        $this->appkey=Config::get('sendsms.txy_appkey');
        $this->url=Config::get('sendsms.txy_url');
        $this->code=mt_rand('100000','999999');
    }

    /**
     * 短信发送
     * Version
     * Creator By River
     * 2018-10-08 18:21:52
     * @param $phone 发送号码
     * @param $tplid 发送短信模板
     * @param $param 发送的短信内容 是一个数组 分别对应配置文件里的模板的位置 ['123456','ddd']
     * @return bool
     * @throws Exception
     * @throws ParamException
     */
    public function send($phone,$tplid,$param)
    {
        if (!$phone){
            throw new ParamException([
                'msg'=>'缺失电话号码!'
            ]);
        }
        if (!$tplid){
            throw new ParamException([
                'msg'=>'缺失模板id'
            ]);
        }
        if (!$param || !is_array($param)){
            throw new ParamException([
                'msg'=>'缺失短信内容'
            ]);
        }

        $this->phone=$phone;
        $this->tplid=$tplid;
        $this->param=$param;


        return self::work();

    }

    /**
     * 短信业务处理
     * Version
     * Creator By River
     * 2018-10-08 18:21:05
     * @return bool
     * @throws Exception
     */
    private function work()
    {

        try{
            //发送短信
            $result=self::doCurl();
            //保存发送内容到数据库
            //self::saveSms($result);

            //判断是否发送成功
            if (!is_array($result)){
                $result=json_decode($result,true);
            }

            if ($result && array_key_exists('result',$result) && $result['result']==0){
                return true;
            }else{
                Log::error($result['errmsg']);
                throw new Exception($result['errmsg']);
            }

        }catch (\Exception $exception){

            Log::error($exception->getMessage());
            throw new Exception($exception->getMessage());
        }
    }


    /**
     * 获取发送的链接地址
     * Version
     * Creator By River
     * 2018-10-08 17:21:06
     * @return string
     */
    private function getUrl()
    {
        $baseParam=[
            'sdkappid'=>$this->appid,
            'random'=>$this->code
        ];

        $url=$this->url.http_build_query($baseParam);

        return $url;
    }

    /**
     * 获取签名
     * Version
     * Creator By River
     * 2018-10-08 17:24:28
     * @return string
     */
    private function getSign()
    {
        $hashParam=[
            'appkey'=>$this->appkey,
            'random'=>$this->code,
            'time'=>$this->time,
            'mobile'=>$this->phone
        ];

        $sign=hash("sha256",http_build_query($hashParam),false);

        return $sign;
    }

    /**
     * 获取准备发送的数据
     * Version
     * Creator By River
     * 2018-10-08 17:25:15
     * @return array
     */
    private function preData()
    {

        $data=[
            'tel'=>[
                'nationcode'=>'86',
                'mobile'=>$this->phone
            ],
            'sign'=>'',
            'tpl_id'=>config::get('sendsms.tpl_'.$this->tplid),
            'params'=>$this->param,
            'sig'=>self::getSign(),
            'time'=>$this->time,
            'extend'=>'',
            'ext'=>''
        ];

        return $data;
    }

    /**
     * 发送短信
     * Version
     * Creator By River
     * 2018-10-08 17:58:12
     * @return mixed
     * @throws Exception
     */
    private function doCurl()
    {
        $url=self::getUrl();
        $data=self::preData();
        if (is_array($data)){
            $data=json_encode($data);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec ($ch);
        if ($result){
            curl_close($ch);
            return $result;
        }else{
            $error=curl_error($ch);
            curl_close($ch);
            throw new Exception("curl出错，错误码:$error");
        }
    }

    /**
     * 保存发送的短信内容到数据库
     * Version
     * Creator By River
     * 2018-10-08 18:14:47
     * @param $result
     */
    private function saveSms($result)
    {
        if (is_array($result)){
            $result=json_encode($result);
        }

        $res=self::preData();
        if (is_array($res)){
            $res=json_encode($res);
        }

        $data=[
            'mobile'=>$this->phone,
            'type'=>1,
            'comment'=>self::getComment(),
            'request'=>$res,
            'response'=>$result
        ];

        (new SmslogModel())->save($data);

    }

    /**
     * 获取发送内容
     * Version
     * Creator By River
     * 2018-10-08 18:13:17
     * @return mixed
     */
    private function getComment()
    {
        $comment=config::get('sendsms.tpl_content_'.$this->tplid);
        $length=count($this->param);
        for ($i=1;$i<=$length;$i++){
            $str=str_replace("{{$i}}",$this->param[$i-1],$comment);
        }

        return $str;
    }
}