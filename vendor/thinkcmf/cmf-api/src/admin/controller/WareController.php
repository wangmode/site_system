<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/24
 * Time: 10:13
 */

namespace api\admin\controller;


use app\admin\service\WareService;
use app\common\model\WareModel;
use cmf\controller\RestBaseController;
use think\Exception;

class WareController extends RestBaseController
{
    /**
     * 挖掘关键词文章
     */
    public function warehouse()
    {
        try{
            $ware = new WareModel();
            $ware->get_Warehouse_list();
        }catch (Exception $exception){
            echo date('Y-m-d H:i:s',time()).$exception->getMessage().'<br>';
        }
    }

    /**
     * 分发文章
     */
    public function distribute()
    {
        try{
            WareService::distribute();
        }catch (Exception $exception){
            echo date('Y-m-d H:i:s',time()).$exception->getMessage().'<br>';
        }
    }

}