<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/18
 * Time: 16:22
 */

namespace app\common\model;

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
     * @param $web_id
     * @throws DbException
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

    /**
     * @param $keyword
     * @param $page
     * @param $limit
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getNewsListData($keyword,$page,$limit)
    {
        $where= [];
        if(empty($keyword) === false){
            $keyword = trim($keyword);
            $where[]=['title','like',"%$keyword%"];
        }

        return self::where($where)
            ->order('itemid','desc')
            ->limit(($page-1)*$limit,$limit)
            ->field(['username','title','itemid','status','level','addtime','linkurl'])
            ->select();
    }


    /**
     * @param $keyword
     * @return float|string
     */
    public function getNewsListCount($keyword)
    {
        $where= [];
        if(empty($keywrod) === false){
            $keyword = trim($keyword);
            $where[]=['title','like',"%$keyword%"];
        }
        return self::where($where)
            ->count();
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