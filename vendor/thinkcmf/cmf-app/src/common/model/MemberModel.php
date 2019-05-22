<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/22 0022
 * Time: 14:32
 */

namespace app\common\model;


use think\Model;

class MemberModel extends Model
{
    protected $pk = 'userid';

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
     * 获取客户列表
     * @param $page
     * @param $limit
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function memberList($keyword,$status,$page,$limit)
    {
        $where = [];
        if(empty($keyword) === false){
            $keyword = trim($keyword);
            $where[]=['m.username|c.company','like',"%$keyword%"];
        }
        if(empty($status) === false){
            $where[] = ['m.groupid','=',$status];
        }
        return self::alias("m")
            ->where($where)
            ->leftJoin("member_group g","m.groupid = g.groupid")
            ->leftJoin("company c","m.userid = c.userid")
            ->limit(($page-1)*$limit,$limit)
            ->order('m.userid','desc')
            ->field(['m.userid','m.username','m.passport','c.company','g.groupname','m.truename','m.mobile','c.level','c.vip','c.linkurl'])
            ->select();
    }

    /**
     * 获取客户列表
     * @return \think\db\Query
     */
    public function memberListCount($keyword,$status)
    {
        $where = [];
        if(empty($keyword) === false){
            $keyword = trim($keyword);
            $where[]=['m.username|c.company','like',"%$keyword%"];
        }
        if(empty($status) === false){
            $where[] = ['m.groupid','=',$status];
        }
        return self::alias("m")
            ->where($where)
            ->leftJoin("member_group g","m.groupid = g.groupid")
            ->leftJoin("company c","m.userid = c.userid")
            ->count();
    }


    /**
     * 删除会员
     * @param $userid
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delMember($userid)
    {
        return self::where(['userid'=>$userid])->delete();
    }

    public function memberInfo($userid)
    {
        return self::alias('m')
            ->leftJoin("member_group g","m.groupid = g.groupid")
            ->leftJoin("company c","m.userid = c.userid")
            ->leftJoin("company_data d","m.userid = d.userid")
            ->where(['m.userid'=>$userid])
            ->field(['m.userid','m.username','g.groupname','c.company','c.level','m.passport','m.truename','m.mobile','m.regtime','m.money','c.type','c.linkurl','d.content','c.vip'])
            ->find();
    }
}