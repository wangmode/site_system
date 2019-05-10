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
     * @param $keyword  //
     * @param int $page
     * @param int $limit
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getWebListData($keyword,$page=1,$limit=10)
    {
        $where = [];
        if(empty($keyword) === false){
            $keyword = trim($keyword);
            $where[] = ['w.name','like',"%$keyword%"];
        }
        return self::alias('w')
            ->leftJoin('user u','w.admin_id = u.id')
            ->where($where)
            ->limit(($page-1)*$limit,$limit)
            ->field(['w.id','w.name','u.user_nickname','w.url','w.created.at','w.status','w.is_ware'])
            ->order('w.id','desc')
            ->select();
    }

    static public function getWebListCount($keyword)
    {
        $where = [];
        if(empty($keyword) === false){
            $keyword = trim($keyword);
            $where[] = ['w.name','like',"%$keyword%"];
        }
        return self::alias('w')
            ->leftJoin('user u','w.admin_id = u.id')
            ->where($where)
            ->count();
    }

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

    static public function updateStatus($id,$status)
    {
        return self::update([
            'id'        =>$id,
            'status'    =>$status
        ]);
    }

    static public function updateIsWare($id,$is_ware)
    {
        return self::update([
            'id'        =>$id,
            'is_ware'    =>$is_ware
        ]);
    }

    static public function getStatusById($id)
    {
        return self::where('id',$id)->value('status');
    }

    static public function getIsWareById($id)
    {
        return self::where('id',$id)->value('is_ware');
    }

    static public function delWebConfig($id)
    {
        return self::where('id',$id)->delete();
    }

    static public function getWebConfigInfoById($id)
    {
        return self::alias('w')
            ->leftJoin('user u','w.admin_id = u.id')
            ->where('w.id',$id)
            ->field(['w.*','u.user_nickname'])
            ->find();
    }


}