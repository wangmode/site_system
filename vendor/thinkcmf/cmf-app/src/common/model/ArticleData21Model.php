<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/18
 * Time: 16:09
 */

namespace app\common\model;


use app\common\Exception\ArticleException;
use think\Exception;
use think\Model;

class ArticleData21Model extends Model
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
        $this->table = "$config[prefix]article_data_21";
    }


    /**
     * @param $itemid
     * @param $content
     * @param $data_id
     * @throws ArticleException
     */
    public function addArticaleData($itemid,$content,$data_id)
    {
        try{
            self::insert([
                'itemid'=>$itemid,
                'content'=>$content
            ]);
        }catch (Exception $exception){
            throw new ArticleException($exception->getMessage(),$itemid,$data_id);
        }
    }


    /**
     * @param $itemid
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delArticle($itemid)
    {
        return self::where('itemid',$itemid)->delete();
    }

}