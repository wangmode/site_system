<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/18
 * Time: 16:48
 */

namespace app\common\model;


use think\Model;

class NewsDataModel extends Model
{
    protected $pk = 'itemid';

    /**
     * @param $web_id
     * @throws \think\Exception\DbException
     */
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
    }


    /**
     * @param $itemid
     * @param $content
     */
    public function addNewsData($itemid,$content)
    {
        self::insert([
            'itemid'=>$itemid,
            'content'=>$content
        ]);
    }


    /**
     * @param $itemid
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delNews($itemid)
    {
        return self::where('itemid',$itemid)->delete();
    }


}