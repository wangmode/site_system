<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/22 0022
 * Time: 14:16
 */

namespace app\common\model;


use think\Model;

class MemberMiscModel extends Model
{
    protected $pk = 'userid';

    /**
     * CompanyModel constructor.
     * @param $web_id
     * @throws DbException
     */
    public function __construct($web_id)
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
        parent::__construct();
    }

    public function delMemberMisc($userid)
    {
        return self::where(['userid'=>$userid])->delete();
    }
}