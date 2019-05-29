<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/10
 * Time: 13:38
 */

namespace app\common\model;


use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Model;

class WarehouseModel extends Model
{

    const STATUS_AUDITING   = 0;    //  状态 审核中
    const STATUS_ADOPT      = 1;    //  状态 通过
    const STATUS_REJECT     = 2;    //  状态 驳回

    /**
     * @param $warehouse_list
     * @return int|string
     */
    static public function addWarehouseAll($warehouse_list)
    {
        return (new WarehouseModel)->insertAll($warehouse_list);
    }


    /**
     * @return array|\PDOStatement|string|\think\Collection
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    static public function getWarehouseCount()
    {
        return (new WarehouseModel)->where('status',self::STATUS_ADOPT)->where('content','<>','')->group('keyword_id')->field(['keyword_id','count(*) as num'])->select();
    }


    /**
     * @param $keyword_id
     * @param $limit
     * @return array|\PDOStatement|string|\think\Collection
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    static public function getWarehouseDataByKeywordId($keyword_id,$limit)
    {
        return self::where('keyword_id',$keyword_id)
                    ->where('status',self::STATUS_ADOPT)
                    ->where('content','<>','')
                    ->limit(0,$limit)
                    ->select();
    }

    /**
     * @param $data_id
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    static public function delByDataId($data_id)
    {
        self::where('data_id',$data_id)->delete();
    }


    /**
     * @param $keyword
     * @param $status
     * @param $page
     * @param $limit
     * @return array|\PDOStatement|string|\think\Collection
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    static public function getWarehouseListData($keyword,$status,$page,$limit)
    {
        $where = [];
        if(empty($keyword) === false){
            $keyword = trim($keyword);
            $where[]=['title|keyword','like',"%$keyword%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['status','=',$status];
        }
        return self::where($where)
            ->limit(($page - 1) * $limit, $limit)
            ->order('data_id', 'asc')
            ->select();
    }

    /**
     * @param $keyword
     * @param $status
     * @return float|string
     */
    static public function getWarehouseListCount($keyword,$status)
    {
        $where = [];
        if(empty($keywrod) === false){
            $keyword = trim($keyword);
            $where[]=['title|keyword','like',"%$keyword%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['status','=',$status];
        }
        return self::where($where)
            ->count();
    }

    /**
     * @param $data_id
     * @return array|\PDOStatement|string|Model|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    static public function getWarehouseInfo($data_id)
    {
        return self::where('data_id',$data_id)->find();
    }


    /**
     * @param $data_id
     * @param $url
     * @param $title
     * @param $status
     * @param $content
     * @param $keyword_id
     * @param $author_name
     * @param $platform_name
     * @return int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    static public function editWarehouse($data_id,$url,$title,$status,$content,$keyword_id,$author_name,$platform_name)
    {
        $keyword = WareKeywordModel::getKeyword($keyword_id);
        return self::where('data_id',$data_id)
            ->update([
                'url'           => $url,
                'title'         => $title,
                'status'        => $status,
                'content'       => $content,
                'keyword'       => $keyword,
                'keyword_id'    => $keyword_id,
                'author_name'   => $author_name,
                'platform_name' => $platform_name
            ]);

    }

}