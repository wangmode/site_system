<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/27
 * Time: 9:56
 */

namespace app\admin\controller;


use app\common\model\WarehouseModel;
use app\common\model\WareKeywordModel;
use app\common\model\WareModel;
use cmf\controller\AdminBaseController;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;

class WarehouseController extends AdminBaseController
{
    /**
     *  文章列表
     * @return mixed
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * @return \think\response\Json
     */
    public function getWarehouseListData()
    {
        $page       = $this->request->param('page');
        $limit      = $this->request->param('limit');
        $status     = $this->request->param('status');
        $keyword    = $this->request->param('keyword');
        try{
            $data = WarehouseModel::getWarehouseListData($keyword,$status,$page,$limit);
            $count = WarehouseModel::getWarehouseListCount($keyword,$status);
            return $this->returnListJson(self::CODE_OK, $count, $data, '获取采集文章列表成功');
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL, null, null, $exception->getMessage());
        }
    }

    /**
     * @return \think\response\Json
     */
    public function delWarehouse()
    {

        $id = $this->request->param('data_id');
        try{
            if(empty($id) === true){
                throw new Exception('非法访问');
            }
            WarehouseModel::delByDataId($id);
            return $this->returnJson(self::STATUS_OK,null,'删除文章成功！');
        }catch (Exception $exception){
            return $this->returnJson(self::STATUS_FAIL,null,$exception->getMessage());
        }

    }

    /**
     * @return mixed
     */
    public function edit()
    {
        $id = $this->request->param('data_id');
        try{
            if(empty($id) === true){
                throw new Exception('非法访问');
            }
            $info = WarehouseModel::getWarehouseInfo($id);
            if(empty($info['content'])){
                $info['content'] = (new WareModel())->to_get_content($id);
            }
            $keyword_list = WareKeywordModel::getWareKeywordList();
            $this->assign('keyword_list',$keyword_list);
            $this->assign('info',$info);
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }
    }

    /**
     * @return \think\response\Json
     */
    public function editPost()
    {
        $data           =  $this->request->param();
        $url            =  $this->request->param('url');
        $title          =  $this->request->param('title');
        $status         =  $this->request->param('status');
        $content        =  $this->request->param('content');
        $data_id        =  $this->request->param('data_id');
        $keyword_id     =  $this->request->param('keyword_id');
        $author_name    =  $this->request->param('author_name');
        $platform_name  =  $this->request->param('platform_name');
        try{
            $result = $this->validate($data,'warehouse');
            if($result !== true){
                throw new Exception($result);
            }
            WarehouseModel::editWarehouse($data_id,$url,$title,$status,$content,$keyword_id,$author_name,$platform_name);
            return $this->returnJson(self::STATUS_OK,null,'文章编辑成功！');
        }catch (Exception $exception){
            return $this->returnJson(self::STATUS_FAIL,null,$exception->getMessage());
        }

    }





}