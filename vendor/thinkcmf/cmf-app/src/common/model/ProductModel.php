<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/2
 * Time: 11:36
 */

namespace app\common\model;


use think\Model;

class ProductModel extends Model
{

    /**
     * 获取所有产品列表
     * @return mixed
     */
    static public function getProductList()
    {
        return self::select();
    }


    /**
     * 通过ID获取产品信息
     * @param $id
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getProductById($id)
    {
        return self::where($id)->find();
    }

}