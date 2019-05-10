<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/3
 * Time: 17:15
 */

namespace app\common\validate;


use think\Validate;

class TemplateApplyValidate extends Validate
{
    protected $rule = [
        'id'            => 'require',
        'name'          =>['require','name_length','chsAlphaNum','regex'=>'/^.*[^\d].*$/'] ,
        'product_id'    => 'require',
        'agent_id'      => 'require',
        'content'       => 'require|content_length',
        'description'   => 'require|description_length',
    ];

    protected $message = [
        'id.require'                        => '非法访问',
        'name.sign_length'                  => '模板名称需为2-12个字',
        'name.chsAlphaNum'                  => '模板名称需为中英文或数字组成,支持全英文,不支持空格、符号',
        'name.regex'                        => '模板名称不能为全数字',
        'product_id.require'                => '产品类别不能为空',
        'agent_id.require'                  => '请指定代理客户',
        'content.require'                   => '模板内容不能为空',
        'content.content_length'            => '模板内容不得多于50个字',
        'description.require'               => '业务说明不能为空',
        'description.description_length'    => '说明不得多于300个字',
    ];

    protected function name_length($name){
        if(mb_strlen($name)<=12 && mb_strlen($name)>=2){
            return true;
        }
        return false;
    }

    protected function content_length($content){
        if(mb_strlen($content)<=50){
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
        'add'  => ['name', 'product_id','contnet', 'agent_id', 'description'],
        'edit' => ['name', 'product_id','contnet', 'agent_id', 'description','id'],
    ];

}