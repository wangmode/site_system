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
        return (new WarehouseModel)->group('keyword_id')->field(['keyword_id','count(*) as num'])->select();
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
     * @param $page
     * @param $limit
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    static public function getWarehouseListData($keyword,$page,$limit)
    {
        $where = [];
        if(empty($keywrod) === false){
            $keyword = trim($keyword);
            $where[]=['keyword','like',"%$keyword%"];
        }
        self::where($where)
            ->limit(($page - 1) * $limit, $limit)
            ->order('data_id', 'asc')
            ->select();
    }

    /**
     * @param $keyword
     */
    static public function getWarehouseListCount($keyword)
    {
        $where = [];
        if(empty($keywrod) === false){
            $keyword = trim($keyword);
            $where[]=['keyword','like',"%$keyword%"];
        }
        self::where($where)
            ->count();
    }
}