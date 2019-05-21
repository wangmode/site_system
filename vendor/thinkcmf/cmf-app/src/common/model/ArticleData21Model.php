<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/18
 * Time: 16:09
 */

namespace app\common\model;


use think\Model;

class ArticleData21Model extends Model
{
    protected $pk = 'itemid';


    /**
     * ArticleData21Model constructor.
     * @param $web_id
     * @throws \think\Exception\DbException
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

    /**
     * @param $itemid
     * @param $content
     */
    public function addArticaleData($itemid,$content)
    {
        self::insert([
            'itemid'=>$itemid,
            'content'=>$content
        ]);
    }

}