<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/14
 * Time: 16:07
 */

namespace app\common\validate;


use think\Validate;

class WareKeywordValidate extends Validate
{
    protected $rule = [
        'keyword'        => 'require',
    ];

    protected $message = [
        'keyword.require'        => '关键词不得为空！',
    ];
}