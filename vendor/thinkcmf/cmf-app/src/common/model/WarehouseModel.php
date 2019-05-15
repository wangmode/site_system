<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/10
 * Time: 13:38
 */

namespace app\common\model;


use think\Model;

class WarehouseModel extends Model
{



    protected $pk = 'data_id';

//    public function __construct($web_id=1)
//    {
//
//        $config = WebConfigModel::getConfigInfoById($web_id);
//        $this->connection = [
//            // 数据库类型
//            'type'     => 'mysql',
//            // 服务器地址
//            'hostname' => '127.0.0.1',
//            // 数据库名
//            'database' => 'destoon',
//            // 用户名
//            'username' => 'root',
//            // 密码
//            'password' => 'root',
//            // 端口
//            'hostport' => '3306',
//            // 数据库编码默认采用utf8
//            'charset'  => 'utf8',
//            // 数据库表前缀
//            'prefix'   => 'destoon_',
////            "authcode" => 'FdW2IGlkQHrDcEbTK5',
//            //#COOKIE_PREFIX#
//        ];
//        parent::__construct();
//    }

    static public function addWarehouseAll($warehouse_list)
    {
        return self::insertAll($warehouse_list);
    }


    public function getInfoByDataId()
    {
        return self::all();
//        return $this->where('data_id','19344752')->find();
//        return $this->where('data_id',$data_id)->find();
    }

}