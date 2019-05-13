<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/10
 * Time: 17:38
 */

namespace app\common\validate;


use think\Validate;

class WebConfigValidate extends Validate
{
    protected $rule = [
        'id'        => 'require',
        'url'       => 'require',
        'name'      => 'require',
        'database'  => 'require',
        'hostname'  => 'require',
        'username'  => 'require',
        'password'  => 'require'
    ];

    protected $message = [
        'id.require'        => '非法访问',
        'url.require'       => '网站网址不得为空',
        'name.require'      => '网站名称不得为空',
        'database.require'  => '数据库名称不得为空',
        'hostname.require'  => '数据库服务器地址不得为空',
        'username.require'  => '数据库用户名不得为空',
        'password.require'  => '数据库密码不得为空'
    ];


    protected $scene = [
        'add'  => ['url', 'name', 'database','hostname','username','password'],
        'edit' => ['url', 'name', 'database','hostname','username','password','id'],
    ];


}