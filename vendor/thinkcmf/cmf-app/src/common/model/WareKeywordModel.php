<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/10
 * Time: 14:25
 */

namespace app\common\model;


use think\Db;
use think\Model;

class WareKeywordModel extends Model
{

    protected $db;

    public function __construct($web_id=1)
    {

        $config = WebConfigModel::getConfigInfoById($web_id);
        $this->db= Db::connect([
            // 数据库类型
            'type'     => 'mysql',
            // 服务器地址
            'hostname' => $config['hostname'],
            // 数据库名
            'database' => $config['database'],
            // 用户名
            'username' => $config['username'],
            // 密码
            'password' => $config['password'],
            // 端口
            'hostport' => '3306',
            // 数据库编码默认采用utf8
            'charset'  => 'utf8',
            // 数据库表前缀
            'prefix'   => $config['prefix'],
            //#COOKIE_PREFIX#
        ])->name('ware_keyword');
        parent::__construct();
    }



    public function getInfo()
    {
        return $this->db->field(['id','num'])->select();

    }

}