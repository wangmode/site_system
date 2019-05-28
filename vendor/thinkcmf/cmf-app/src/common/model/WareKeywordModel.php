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

    const IS_WARE_NO    = 0;
    const IS_WARE_YES   = 1;


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
        (new WareKeywordModel)->insertAll($data);
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
        return (new WareKeywordModel)->alias('w')
            ->leftJoin('user u','w.admin_id = u.id')
            ->where($where)
            ->limit(($page-1)*$limit,$limit)
            ->field(['w.id','w.keyword','u.user_nickname','w.num','w.created_at','w.status','w.is_ware'])
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
        return (new WareKeywordModel)->alias('w')
            ->leftJoin('user u','w.admin_id = u.id')
            ->where($where)
            ->count();
    }

    /**
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getWareKeywordsData()
    {
        return self::where('status',self::STATUS_YES)
            ->field(['id','keyword','num','current_page','is_ware'])
            ->limit(0,5)
            ->order(['order'=>'asc','id'=>'asc'])
            ->select();
    }

    /**
     * @return string
     */
    static public function getWareKeywordData()
    {
        return implode("|",self::where('is_ware',self::IS_WARE_YES)->column('keyword'));
    }


    /**
     * @return bool
     * @throws Exception
     */
    static public function updateKeywordApi()
    {
        return (new WareModel())->to_update_keyword(self::getWareKeywordData());
    }


    /**
     * @param $keyword
     * @param $order
     * @param $is_ware
     * @return int|string
     */
    static public function addKeyword($keyword,$order,$is_ware)
    {
        return (new WareKeywordModel)->insert([
            'num'           =>0,
            'total'         =>0,
            'current_page'  =>1,
            'page_num'      =>10,
            'order'         =>$order,
            'keyword'       =>$keyword,
            'is_ware'       =>$is_ware,
            'status'        =>self::STATUS_YES,
            'admin_id'      =>cmf_get_current_admin_id(),
            'created_at'    =>date('Y-m-d H:i:s',time())
        ]);
    }

    /**
     * @return float|string
     */
    static public function getKeywordCount()
    {
        return self::where('is_ware',self::IS_WARE_YES)->count();
    }


    /**
     * @param $keyword
     * @return bool
     * @throws Exception
     */
    static public function newAddKeyword($keyword)
    {
        $is_ware = self::getKeywordCount() >= 10?self::IS_WARE_NO:self::IS_WARE_YES;
        $order = self::getOrderByDesc();
        self::addKeyword($keyword,$order,$is_ware);
        return self::updateKeywordApi();
    }

    /**
     * @return mixed
     */
    static public function getOrderByDesc()
    {
        return (int)self::order('order','desc')->value('order');
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
    static public function getKeywordStatus($id)
    {
        return self::where('id',$id)->value('status');
    }

    /**
     * @param $id
     * @return mixed
     */
    static public function getKeywordIsWare($id)
    {
        return self::where('id',$id)->value('is_ware');
    }

    /**
     * @param $id
     * @return mixed
     */
    static public function getKeyword($id)
    {
        return self::where('id',$id)->value('keyword');
    }

    /**
     * @param $id
     * @param $status
     * @return WareKeywordModel
     */
    static public function updateKeywordStatus($id,$status)
    {
        $info = [
            'id'        =>$id,
            'status'    =>$status
        ];
        if($status === self::STATUS_YES){
            $info['order'] = self::getOrderByDesc();
        }
        return self::update($info);
    }

    /**
     * @param $web_id
     * @param $keyword
     * @param $page
     * @param $limit
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getNotWebKeywordData($web_id,$keyword,$page,$limit)
    {
        $where = [];
        if(empty($keyword) === false){
            $keyword = trim($keyword);
            $where[] = ['keyword','like',"%$keyword%"];
        }
        return (new WareKeywordModel)->whereNotIn('id',WebKeywordModel::getKeywordId($web_id))
            ->where($where)
            ->limit(($page-1)*$limit,$limit)
            ->field(['id','keyword'])
            ->select();
    }

    /**
     * @param $web_id
     * @param $keyword
     * @return float|string
     */
    static public function getNotWebKeywordCount($web_id,$keyword)
    {
        $where = [];
        if(empty($keyword) === false){
            $keyword = trim($keyword);
            $where[] = ['keyword','like',"%$keyword%"];
        }
        return (new WareKeywordModel)->whereNotIn('id',WebKeywordModel::getKeywordId($web_id))
            ->where($where)
            ->field(['id','keyword'])
            ->count();
    }


    /**
     * @param $id
     * @return mixed
     */
    static public function editKeywordStatus($id)
    {
        $status = self::getKeywordStatus($id);
        $status = $status == self::STATUS_YES ? self::STATUS_NO : self::STATUS_YES;
        self::updateKeywordStatus($id,$status);
        return self::getKeywordStatus($id);
    }

    /**
     * @param $id
     * @return mixed
     * @throws Exception
     */
    static public function editKeywordIsWare($id)
    {
        $is_ware = self::getKeywordIsWare($id);
        $is_ware = $is_ware == self::IS_WARE_YES ? self::IS_WARE_NO : self::IS_WARE_YES;
        if($is_ware === self::IS_WARE_YES && self::getKeywordCount() >= 10){
            throw new Exception('采集关键词个数不得大于10个');
        }
        self::updateIsWare($id,$is_ware);
        self::updateKeywordApi();
        return self::getKeywordIsWare($id);
    }

    /**
     * @param $id
     * @return bool
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    static public function deleteKeyword($id)
    {
        self::delKeyword($id);
        WebKeywordModel::delByKeywordId($id);
        return self::updateKeywordApi();
    }

    /**
     * @param $keyword_id
     * @return int|true
     * @throws Exception
     */
    static public function keywordInc($keyword_id)
    {
        return self::where('id',$keyword_id)->setInc('order');
    }

    /**
     * @param $keyword_id
     * @param $is_ware
     * @return WareKeywordModel
     */
    static public function updateIsWare($keyword_id,$is_ware)
    {
        return self::update(['id'=>$keyword_id,'is_ware'=>$is_ware]);
    }

    /**
     * @param $keyword_id
     * @return WareKeywordModel
     */
    static public function updateIsWareYes($keyword_id)
    {
        return self::updateIsWare($keyword_id,self::IS_WARE_YES);
    }

    /**
     * @param $keyword_id
     * @return WareKeywordModel
     */
    static public function updateIsWareNo($keyword_id)
    {
        return self::updateIsWare($keyword_id,self::IS_WARE_NO);
    }

    /**
     * @param $keyword_id
     * @param $num
     * @param $current_page
     * @param $total
     * @return WareKeywordModel
     */
    static public function updateKeywordInfo($keyword_id,$num,$current_page,$total)
    {
        return self::update([
            'id'            =>$keyword_id,
            'num'           =>$num,
            'total'         =>$total,
            'current_page'  =>$current_page
        ]);
    }

    /**
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getWareKeywordList()
    {
        return self::field('id,keyword')->select();
    }




}