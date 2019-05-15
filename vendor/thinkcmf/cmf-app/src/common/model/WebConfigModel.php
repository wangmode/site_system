<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/10
 * Time: 13:34
 */

namespace app\common\model;


use think\Model;

class WebConfigModel extends Model
{

    const STATUS_NO     = 0;
    const STATUS_YES    = 1;

    const IS_WARE_NO    = 0;
    const IS_WARE_YES   = 1;




    /**
     * 通过网站获取网站数据库配置
     * @param $id
     * @return \think\db\Query|null
     * @throws \think\Exception\DbException
     */
    static public function getConfigInfoById($id)
    {
        return self::field(['id','database','hostname','username','password','prefix'])->get($id);
    }

    /**
     * 网站配置列表数据
     * @param $keyword
     * @param $status
     * @param $is_ware
     * @param int $page
     * @param int $limit
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getWebListData($keyword,$status,$is_ware,$page=1,$limit=10)
    {
        $where = [];
        if(empty($keyword) === false){
            $keyword = trim($keyword);
            $where[] = ['w.name','like',"%$keyword%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['w.status','=',$status];
        }
        if(empty($is_ware) === false || $is_ware === 0 || $is_ware === '0'){
            $where[] = ['w.is_ware','=',$is_ware];
        }

        return self::alias('w')
            ->leftJoin('user u','w.admin_id = u.id')
            ->where($where)
            ->limit(($page-1)*$limit,$limit)
            ->field(['w.id','w.name','u.user_nickname','w.url','w.created_at','w.status','w.is_ware'])
            ->order('w.id','desc')
            ->select();
    }


    /**
     * 网站配置列表数据条数
     * @param $keyword
     * @param $status
     * @param $is_ware
     * @return float|string
     */
    static public function getWebListCount($keyword,$status,$is_ware)
    {
        $where = [];
        if(empty($keyword) === false){
            $keyword = trim($keyword);
            $where[] = ['w.name','like',"%$keyword%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['w.status','=',$status];
        }
        if(empty($is_ware) === false || $is_ware === 0 || $is_ware === '0'){
            $where[] = ['w.is_ware','=',$is_ware];
        }
        return self::alias('w')
            ->leftJoin('user u','w.admin_id = u.id')
            ->where($where)
            ->count();
    }

    /**
     *  添加新的网站配置
     * @param $name
     * @param $url
     * @param $database
     * @param $hostname
     * @param $username
     * @param $password
     * @param $prefix
     * @return int|string
     */
    static public function add($name,$url,$database,$hostname,$username,$password,$prefix)
    {
        return self::insert([
            'url'           =>$url,
            'name'          =>$name,
            'prefix'        =>$prefix,
            'hostname'      =>$hostname,
            'username'      =>$username,
            'password'      =>$password,
            'database'      =>$database,
            'status'        =>self::STATUS_YES,
            'is_ware'       =>self::IS_WARE_YES,
            'admin_id'      =>cmf_get_current_admin_id(),
            'created_at'    =>date('Y-m-d H:i:s',time()),
        ]);
    }


    /**
     * 修改网站配置
     * @param $id
     * @param $name
     * @param $url
     * @param $database
     * @param $hostname
     * @param $username
     * @param $password
     * @param $prefix
     * @return WebConfigModel
     */
    static public function edit($id,$name,$url,$database,$hostname,$username,$password,$prefix)
    {
        return self::update([
            'id'            =>$id,
            'url'           =>$url,
            'name'          =>$name,
            'prefix'        =>$prefix,
            'hostname'      =>$hostname,
            'username'      =>$username,
            'password'      =>$password,
            'database'      =>$database,
            'update_at'     =>date('Y-m-d H:i:s',time()),
        ]);
    }


    /**
     * 修改网站状态
     * @param $id
     * @param $status
     * @return WebConfigModel
     */
    static public function updateStatus($id,$status)
    {
        return self::update([
            'id'        =>$id,
            'status'    =>$status,
            'update_at' =>date('Y-m-d H:i:s',time())
        ]);
    }

    static public function editStatus($id)
    {
        $status = self::getStatusById($id);
        $status = $status == self::STATUS_YES ? self::STATUS_NO : self::STATUS_YES;
        self::updateStatus($id,$status);
        return self::getStatusById($id);
    }

    /**
     * 修改抓取状态
     * @param $id
     * @param $is_ware
     * @return WebConfigModel
     */
    static public function updateIsWare($id,$is_ware)
    {
        return self::update([
            'id'        =>$id,
            'is_ware'   =>$is_ware,
            'update_at' =>date('Y-m-d H:i:s',time())
        ]);
    }

    static public function editIsWare($id)
    {
        $is_ware = self::getIsWareById($id);
        $is_ware = $is_ware == self::IS_WARE_YES ? self::IS_WARE_NO : self::IS_WARE_YES;
        self::updateIsWare($id,$is_ware);
        return self::getIsWareById($id);
    }

    /**
     * 获取网站状态
     * @param $id
     * @return mixed
     */
    static public function getStatusById($id)
    {
        return self::where('id',$id)->value('status');
    }

    /**
     * 获取网站抓取状态
     * @param $id
     * @return mixed
     */
    static public function getIsWareById($id)
    {
        return self::where('id',$id)->value('is_ware');
    }

    /**
     * 删除网站配置
     * @param $id
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    static public function delWebConfig($id)
    {
        return self::where('id',$id)->delete();
    }

    /**
     * 获取网站配置详细信息
     * @param $id
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getWebConfigInfoById($id)
    {
        return self::alias('w')
            ->leftJoin('user u','w.admin_id = u.id')
            ->where('w.id',$id)
            ->field(['w.*','u.user_nickname'])
            ->find();
    }


}