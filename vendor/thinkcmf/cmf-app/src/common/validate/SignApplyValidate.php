<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/3
 * Time: 11:30
 */
namespace app\common\validate;

use think\Validate;

class SignApplyValidate extends Validate
{
    protected $rule = [
        'id'            => 'require',
        'sign'          =>['require','sign_length','chsAlphaNum','regex'=>'/^.*[^\d].*$/'] ,
        'source'        => 'require',
        'agent_id'      => 'require',
        'description'   => 'require|description_length',
    ];

    protected $message = [
        'id.require'                        => '非法访问',
        'sign.require'                      => '签名不能为空',
        'sign.sign_length'                  => '签名需为2-12个字',
        'sign.chsAlphaNum'                  => '签名需为中英文或数字组成,支持全英文,不支持空格、符号',
        'sign.regex'                        => '签名不能为全数字',
        'source.require'                    => '签名来源不能为空',
        'agent_id.require'                  => '请指定代理客户',
        'description.require'               => '业务说明不能为空',
        'description.description_length'    => '说明不得多于300个字',
    ];

    protected function sign_length($sign){
        if(mb_strlen($sign)<=12 && mb_strlen($sign)>=2){
            return true;
        }
        return false;
    }

    protected function description_length($description){
        if(mb_strlen($description)<=300){
            return true;
        }
        return false;
    }



    protected $scene = [
        'add'  => ['sign', 'source', 'agent_id', 'description'],
        'edit' => ['sign', 'source', 'agent_id', 'description','id'],
    ];

}