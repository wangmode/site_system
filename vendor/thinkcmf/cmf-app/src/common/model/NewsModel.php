<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/18
 * Time: 16:22
 */

namespace app\common\model;


use think\Exception;
use think\Exception\DbException;
use think\Model;

class NewsModel extends Model
{

    const LEVEL_ONE     = 1; //中间显示位置
    const LEVEL_TWO     = 2; //幻灯片 （必须有图）
    const LEVEL_THREE   = 3; //推荐图文
    const LEVEL_FOUR    = 4;
    const LEVEL_FIVE    = 5; //头条推挤
    const LEVEL_SIX     = 6;
    const LEVEL_SEVEN   = 7;
    const LEVEL_EIGHT   = 8;
    const LEVEL_NINE    = 9; //普通文章

    const STATUS_THREE  = 3; //正常

    protected $pk = 'itemid';


    /**
     * NewsModel constructor.
     * @param $web_id
     * @throws Exception
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
     * @param $title
     * @param $thumb
     * @param $username
     * @return int|string
     */
    public function addNews($title,$thumb,$username)
    {
        return self::insertGetId([
            'title'=>$title,
            'thumb'=>$thumb,
            'addtime'=>time(),
            'username'=>$username,
            'level'=>self::LEVEL_NINE,
            'status'=>self::STATUS_THREE,
        ]);
    }

    /**
     * @param $itemids
     * @return NewsModel
     */
    public function updateLinkUrl($itemids)
    {
        return self::whereIn('itemid',$itemids)->exp('linkurl',"concat('show.php?itemid=',`itemid`)")->update();
    }

}