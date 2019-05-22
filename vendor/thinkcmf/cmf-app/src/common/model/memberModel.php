<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/22 0022
 * Time: 11:09
 */

namespace app\common\model;


use think\Model;

class memberModel extends Model
{

    public function modelInit($web_id)
    {
        $config = WebConfigModel::getConfigInfoById($web_id);
        $this->connection = [
            // 数据库类型
            'type'     => 'mysql',
            // 服务器地址
            'hostname' => '127.0.0.1',
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
//            "authcode" => 'FdW2IGlkQHrDcEbTK5',
            //#COOKIE_PREFIX#
        ];
        $this->table = "$config[prefix]article_21";
    }

    public function memberList($page,$limit)
    {
        return self::alias("m")
            ->leftJoin("member_group g","m.groupid = g.groupid")
            ->leftJoin("company c","m.userid = c.userid")
            ->limit(($page-1)*$limit,$limit)
            ->order('m.userid','desc')
            ->field(['m.username','m.passport','c.company','g.groupname','m.truename','m.mobile','c.level','c.vip',''])
            ->select();
    }
}