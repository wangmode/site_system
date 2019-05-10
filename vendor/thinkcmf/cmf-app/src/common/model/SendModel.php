<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/15
 * Time: 14:08
 */

namespace app\common\model;

use think\Exception;
use app\common\model\SendAPI;
use think\cache\driver\Redis;


class SendModel
{

    private $apiUrl ="http://www.ztsms.cn/sendNSms.do";    //发送地址
//    private $apiUrl ="http://www.ztsms.cn/sendManyNSms.do";    //发送地址
    private $username ="Heyou";    //用户名
    private $password ="NZVI1WmX";    //密码
    private $autograph = "鹤友网络";    //签名

    static private $verify_code_product_id  = 676767;
    static private $notice_product_id       = 887362;
    static private $marketing_product_id    = 435227;

    static private $lifeTime   = 300;

    public $send;

    public function __construct()
    {
        $this->send = new SendAPI($this->apiUrl,$this->username,$this->password);
    }


    /**
     * 获取阻断验证KEY
     * @param $mobile
     * @return string
     */
    static private function getBlockKey($mobile)
    {
        return 'Block_key:'.md5($mobile);
    }

    /**
     * 获取短信验证码KEY
     * @param $mobile
     * @return string
     */
    static private function getVerifyCodeKey($mobile)
    {
        return 'Verify_code_key:'.md5($mobile);
    }


    /**
     * 发送短信
     * @param $content
     * @param $mobile
     * @param $product_id
     * @return mixed
     */
    private function send($content,$mobile,$product_id)
    {
        $this->send->setData($content,$mobile,$product_id);
        return $this->send->sendSMS('POST');
    }

    public function sendMsg($content,$mobile,$product_id)
    {
        $this->send->setData($content,$mobile,$product_id);
        return $this->send->sendSMS('POST');
    }

    /**
     * 获取短信验证码
     * @return int
     */
    private function getRandCode()
    {
        return rand(1000,9999);
    }

    /**
     * 获取短信验证码语句
     * @param $code
     * @return string
     */
    static private function getVerifyCodeInfo($code)
    {
        return "您的短信验证码为$code ，有效期".(floor(self::$lifeTime/60))."分钟";
    }

    /**
     * 发送短信验证码
     * @param $mobile
     * @throws Exception
     */
    public function sendVerifyCode($mobile)
    {
        $check = self::checkBlock($mobile);
        if($check === false){
            throw new Exception('请不要重复发送');
        }
        $code = $this->getRandCode();
        $result = $this->send(self::getVerifyCodeInfo($code),$mobile,self::$verify_code_product_id);
        (new Redis())->set(self::getVerifyCodeKey($mobile),$code,self::$lifeTime);
    }


    /**
     * 验证短信验证码
     * @param $code
     * @param $mobile
     * @return bool
     * @throws Exception
     */
    public function verifySmsCode($code,$mobile)
    {
        $redis_code = (new Redis())->get(self::getVerifyCodeKey($mobile));
        if(empty($redis_code)){
            throw new Exception('验证码已过期，请重新发送');
        }
        if(strcmp($redis_code,$code) === 0){
            return true;
        }
        return false;
    }

    /**
     * 设置短信阻断
     * @param $mobile
     * @return bool
     */
    static private function checkBlock($mobile)
    {
        $redis = new Redis();
        $block_key = self::getBlockKey($mobile);
        $block = $redis->get($block_key);
        if(empty($block)){
            $redis->set($block_key,1,30);
            return true;
        }else{
            return false;
        }
    }


}