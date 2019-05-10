<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Released under the MIT License.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
// 请不要随意修改此文件内容
return [
    // 时间字段取出后的默认时间格式
    'datetime_format' => false,
    // 数据集返回类型
    'resultset_type'  => 'collection',
    // 数据库调试模式
    'debug'           => APP_DEBUG,
    // 是否需要进行SQL性能分析
    'sql_explain'     => APP_DEBUG,

    // +----------------------------------------------------------------------
    // | 数据库配置
    // +----------------------------------------------------------------------
    'db_daili'=>[
        // 数据库类型
        'type'     => 'mysql',
        // 服务器地址
        'hostname' => '127.0.0.1',
        // 数据库名
        'database' => 'daili_cmf',
        // 用户名
        'username' => 'root',
        // 密码
        'password' => 'root',
        // 端口
        'hostport' => '3306',
        // 数据库编码默认采用utf8
        'charset'  => 'utf8',
        // 数据库表前缀
        'prefix'   => 'yzt_',
        "authcode" => 'FdW2IGlkQHrDcEbTK5',
        //#COOKIE_PREFIX#
    ],
];
