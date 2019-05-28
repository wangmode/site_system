<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 10:10
 */

namespace app\common\model;


use think\Model;

class WebKeywordModel extends Model
{


    /**
     * @param $web_id
     * @param $keyword
     * @param $status
     * @param int $page
     * @param int $limit
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getKeywordListDataByWebId($web_id,$keyword,$status,$page=1,$limit=10)
    {
        $where = [];
        if(empty($keyword) === false){
            $keyword = trim($keyword);
            $where[]=['k.keyword','like',"%$keyword%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['k.status','=',$status];
        }
        return (new WebKeywordModel)->alias('w')
            ->leftJoin('ware_keyword k','w.keyword_id = k.id')
            ->where('w.web_id',$web_id)
            ->where($where)
            ->limit(($page-1)*$limit,$limit)
            ->field(['k.id','k.keyword','k.created_at','k.status'])
            ->select();
    }


    /**
     * @param $web_id
     * @param $keyword
     * @param $status
     * @return float|string
     */
    static public function getKeywordListCountByWebId($web_id,$keyword,$status)
    {
        $where = [];
        if(empty($keyword) === false){
            $keyword = trim($keyword);
            $where[]=['k.keyword','like',"%$keyword%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['k.status','=',$status];
        }
        return (new WebKeywordModel)->alias('w')
            ->leftJoin('ware_keyword k','w.keyword_id = k.id')
            ->where('w.web_id',$web_id)
            ->where($where)
            ->count();
    }






    /**
     * @param $web_id
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getKeywordByWebId($web_id)
    {
        return self::where('web_id',$web_id)->select();
    }


    /**
     * @param $keyword_id
     * @return array
     */
    static public function getWebByKeywordId($keyword_id)
    {
        return (new WebKeywordModel)->alias('wk')
            ->leftJoin('web_config wc','wk.web_id = wc.id')
            ->where('wc.is_ware',WebConfigModel::IS_WARE_YES)
            ->where('wk.keyword_id',$keyword_id)
            ->column('wc.id');
    }



    /**
     * @param $web_id
     * @return mixed
     */
    static public function getKeywordId($web_id)
    {
        return self::where('web_id',$web_id)->group('web_id')->value('group_concat(keyword_id)');
    }

    /**
     * @param $web_id
     * @param $keyword_id
     */
    static public function add($web_id,$keyword_id)
    {
        foreach ($keyword_id as $key=>$val){
            (new WebKeywordModel)->insert([
                'web_id'=>$web_id,
                'keyword_id'=>$val
            ]);
        }
    }


    /**
     * @param $web_id
     * @param $keyword_id
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    static public function del($web_id,$keyword_id)
    {
        return self::where('web_id',$web_id)->where('keyword_id',$keyword_id)->delete();
    }


    /**
     * @param $keyword_id
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    static public function delByKeywordId($keyword_id)
    {
        return self::where('keyword_id',$keyword_id)->delete();
    }


    /**
     * @param $web_id
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    static public function delByWebId($web_id)
    {
        return self::where('web_id',$web_id)->delete();
    }



}