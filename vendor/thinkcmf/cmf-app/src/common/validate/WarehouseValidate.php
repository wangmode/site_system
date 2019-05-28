<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/27
 * Time: 16:22
 */

namespace app\common\validate;


use think\Validate;

class WarehouseValidate extends Validate
{

    protected $rule = [
        'title'         => 'require',
        'data_id'       => 'require',
        'content'       => 'require',
        'keyword_id'    => 'require'
    ];

    protected $message = [
        'title.require'         => '标题不能为空',
        'data_id.require'       => '非法访问',
        'content.require'       => '内容不能为空',
        'keyword_id.require'    => '请指定关键词'
    ];

}