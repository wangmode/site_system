<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/1
 * Time: 15:13
 */

namespace app\admin\controller;

use app\common\model\AgentModel;
use app\common\model\ApplyModel;
use app\common\model\ProductModel;
use app\common\model\SignModel;
use app\common\model\TemplateModel;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Exception;

class ApplyController extends AdminBaseController
{


    /**
     * 签名申请列表
     * @return mixed
     */
    public function signList()
    {
        return $this->fetch();
    }


    /**
     * 签名申请列表数据
     * @return \think\response\Json
     */
    public function getSignListData()
    {
        $start_time     = $this->request->param('start_time',null);
        $end_time       = $this->request->param('end_time',null);
        $status         = $this->request->param('status',null);
        $source         = $this->request->param('source',null);
        $keyword        = $this->request->param('keyword',null,'string');
        $limit          = $this->request->param('limit',10,'intval');
        $page           = $this->request->param('page',1,'intval');
        try{
            $data = ApplyModel::getSignApplyList($keyword,$status,$source,$start_time,$end_time,$page,$limit);
            $count = ApplyModel::getSignApplyCount($keyword,$status,$source,$start_time,$end_time);
            return $this->returnListJson(self::CODE_OK,$count,$data,'获取签名列表成功！');
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL,null,null,$exception->getMessage());
        }
    }


    /**
     * 添加新的签名申请
     * @return mixed
     */
    public function addSignApply()
    {
        return $this->fetch();
    }

    /**
     * 添加新的签名申请提交
     * @return \think\response\Json
     */
    public function addSignApplyPost()
    {
        $sign        = $this->request->param('sign',null,'string');
        $source      = $this->request->param('source',null,'intval');
        $agent_id    = $this->request->param('agent_id',null,'intval');
        $description = $this->request->param('description',null,'string');
        $data        = $this->request->param();
        $result = $this->validate($data,'signApply.add');
        try{
            if($result !== true){
                throw new Exception($result);
            }
            Db::startTrans();
            SignModel::newAddSign($sign,$agent_id,$source,$description);
            Db::commit();
            return $this->returnJson(self::STATUS_OK,null,'签名申请已提交！');
        }catch (Exception $exception){
            Db::rollback();
            return $this->returnJson(self::STATUS_FAIL,null,$exception->getMessage());
        }
    }


    /**
     * 签名审核
     * @return mixed
     */
    public function signExamine()
    {
        $apply_id        = $this->request->param('apply_id',null,'intval');
        try{
            if(empty($apply_id)){
                throw new Exception('非法访问！');
            }
            $info = ApplyModel::getSignApplyInfo($apply_id);
            $this->assign('info',$info);
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
//            return $this->returnJson(self::STATUS_FAIL,null,$exception->getMessage());
        }
    }

    /**
     * 签名审核提交
     * @return \think\response\Json
     */
    public function signExaminePost()
    {
        $apply_id       = $this->request->param('id',null,'intval');
        $status         = $this->request->param('status',null,'intval');
        $reason         = $this->request->param('reason',null,'string');
        try{
            if(empty($apply_id)){
                throw new Exception('非法访问！');
            }
            Db::startTrans();
            ApplyModel::signExamine($apply_id,$status,$reason);
            Db::commit();
            return $this->returnJson(self::STATUS_OK,null,'审核结果提交成功！');
        }catch (Exception $exception){
            Db::rollback();
            return $this->returnJson(self::STATUS_FAIL,null,$exception->getMessage());
        }
    }

    /**
     * 模板申请列表
     * @return mixed
     */
    public function templateList()
    {
        $product_list = ProductModel::getProductList();
        $this->assign('product_list',$product_list);
        return $this->fetch();
    }

    /**
     * 模板申请列表数据
     * @return \think\response\Json
     */
    public function getTemplateListData()
    {
        $start_time     = $this->request->param('start_time',null);
        $end_time       = $this->request->param('end_time',null);
        $status         = $this->request->param('status',null);
        $keyword        = $this->request->param('keyword',null,'string');
        $limit          = $this->request->param('limit',10,'intval');
        $page           = $this->request->param('page',1,'intval');
        try{
            $data = ApplyModel::getTemplateApplyList($keyword,$status,$start_time,$end_time,$page,$limit);
            $count = ApplyModel::getTemplateApplyCount($keyword,$status,$start_time,$end_time);
            return $this->returnListJson(self::CODE_OK,$count,$data,'获取模板列表成功！');
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL,null,null,$exception->getMessage());
        }
    }

    /**
     * 添加新的模板申请
     * @return mixed
     */
    public function addTemplateApply()
    {
        $product_list = ProductModel::getProductList();
        $this->assign('product_list',$product_list);
        return $this->fetch();
    }

    /**
     * 添加新的模板申请提交
     * @return \think\response\Json
     */
    public function addTemplateApplyPost()
    {
        $name           = $this->request->param('name',null,'string');
        $product_id     = $this->request->param('product_id',null,'intval');
        $agent_id       = $this->request->param('agent_id',null,'intval');
        $content        = $this->request->param('content',null,'string');
        $description    = $this->request->param('description',null,'string');
        $data           = $this->request->param();
        $result = $this->validate($data,'templateApply.add');
        try{
            if($result !== true){
                throw new Exception($result);
            }
            Db::startTrans();
            TemplateModel::newAddTemplate($agent_id,$name,$product_id,$content,$description);
            Db::commit();
            return $this->returnJson(self::STATUS_OK,null,'模板申请已提交！');
        }catch (Exception $exception){
            Db::rollback();
            return $this->returnJson(self::STATUS_FAIL,null,$exception->getMessage());
        }
    }


    /**
     * 模板审核
     * @return mixed
     */
    public function templateExamine()
    {
        $apply_id        = $this->request->param('apply_id',null,'intval');
        try{
            if(empty($apply_id)){
                throw new Exception('非法访问！');
            }
            $info = ApplyModel::getTemplateApplyInfo($apply_id);
            $this->assign('info',$info);
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
//            return $this->returnJson(self::STATUS_FAIL,null,$exception->getMessage());
        }
    }


    /**
     * 模板审核提交
     * @return \think\response\Json
     */
    public function templateExaminePost()
    {
        $apply_id       = $this->request->param('id',null,'intval');
        $status         = $this->request->param('status',null,'intval');
        $reason         = $this->request->param('reason',null,'string');
        try{
            if(empty($apply_id)){
                throw new Exception('非法访问！');
            }
            Db::startTrans();
            ApplyModel::signExamine($apply_id,$status,$reason);
            Db::commit();
            return $this->returnJson(self::STATUS_OK,null,'审核结果提交成功！');
        }catch (Exception $exception){
            Db::rollback();
            return $this->returnJson(self::STATUS_FAIL,null,$exception->getMessage());
        }
    }

    




}