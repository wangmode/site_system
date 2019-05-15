<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/10
 * Time: 14:25
 */

namespace app\common\model;


use think\Exception;
use think\Model;

class WareKeywordModel extends Model
{
    const STATUS_YES = 1;
    const STATUS_NO  = 0;



    static public function getInfo()
    {
        $str = "";

        $str_arr = explode("\r\n",$str);

        $data= [];
        $info['num'] =0;
        $info['current_page'] =1;
        $info['page_num'] =10;
        $info['total'] =0;
        $info['status'] =1;

        foreach ($str_arr as $key=>$value){
            $info['keyword'] = $value;
            $data[] = $info;
        }
        self::insertAll($data);
    }


    /**
     * @param $keyword
     * @param $status
     * @param $page
     * @param $limit
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getWareKeywordListData($keyword,$status,$page,$limit)
    {
        $where = [];
        if(empty($keyword) === false){
            $keyword = trim($keyword);
            $where[] = ['w.keyword','like',"%$keyword%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['w.status','=',$status];
        }
        return self::alias('w')
            ->leftJoin('user u','w.admin_id = u.id')
            ->where($where)
            ->limit(($page-1)*$limit,$limit)
            ->field(['w.id','w.keyword','u.user_nickname','w.num','w.created_at','w.status'])
            ->order('w.id','desc')
            ->select();
    }

    /**
     * 网站关键词列表数据条数
     * @param $keyword
     * @param $status+
     * @return float|string
     */
    static public function getWareKeywordListCount($keyword,$status)
    {
        $where = [];
        if(empty($keyword) === false){
            $keyword = trim($keyword);
            $where[] = ['w.keyword','like',"%$keyword%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['w.status','=',$status];
        }
        return self::alias('w')
            ->leftJoin('user u','w.admin_id = u.id')
            ->where($where)
            ->count();
    }

    static public function getWareKeywordsData()
    {
        return self::alias('w')
            ->where('status',self::STATUS_YES)
            ->field(['w.id','w.keyword','w.num','w.current_page','w.page_num'])
            ->order('w.id','desc')
            ->select();
    }

    /**
     * @return string
     */
    static public function getWareKeywordData()
    {
        return implode("|",self::column('keyword'));
    }

    /**
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function updateKeywordApi()
    {
//        return (new WareModel())->to_update_keyword(self::getWareKeywordData());
    }

    


    /**
     * @param $keyword
     * @return int|string
     */
    static public function addKeyword($keyword)
    {
        return self::insert([
            'num'           =>0,
            'total'         =>0,
            'current_page'  =>1,
            'page_num'      =>10,
            'keyword'       =>$keyword,
            'status'        =>self::STATUS_YES,
            'admin_id'      =>cmf_get_current_admin_id(),
            'created_at'    =>date('Y-m-d H:i:s',time())
        ]);
    }

    /**
     * @return float|string
     */
    static public function getKeywrodCount()
    {
        return self::where('1=1')->count();
    }


    /**
     * @param $keyword
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function newAddKeyword($keyword)
    {
        if(self::getKeywrodCount() >= 200){
            throw new Exception('关键词个数不得大于200个');
        }
        self::addKeyword($keyword);
        return self::updateKeywordApi();
    }


    /**
     * @param $id
     * @return int
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    static public function delKeyword($id)
    {
        return self::where('id',$id)->delete();
    }


    /**
     * @param $id
     * @return mixed
     */
    static public function getKeywrodStatus($id)
    {
        return self::where('id',$id)->value('status');
    }

    /**
     * @param $id
     * @param $status
     * @return WareKeywordModel
     */
    static public function updateKeywrodStatus($id,$status)
    {
        return self::update([
            'id'        =>$id,
            'status'    =>$status
        ]);
    }


    /**
     * @param $id
     * @return mixed
     */
    static public function editKeywordStatus($id)
    {
        $status = self::getKeywrodStatus($id);
        $status = $status == self::STATUS_YES ? self::STATUS_NO : self::STATUS_YES;
        self::updateKeywrodStatus($id,$status);
        return self::getKeywrodStatus($id);
    }


    /**
     * @param $id
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    static public function deleteKeyword($id)
    {
        self::delKeyword($id);
        return self::updateKeywordApi();
    }




}