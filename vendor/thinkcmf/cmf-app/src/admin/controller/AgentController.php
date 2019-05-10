<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/1
 * Time: 15:12
 */

namespace app\admin\controller;


use app\common\model\ChargeRecordModel;
use app\common\model\CycleTaskModel;
use app\common\model\ProductModel;
use app\common\model\SendRecordModel;
use app\common\model\SignModel;
use app\common\model\TaskModel;
use app\common\model\TemplateModel;
use think\Db;
use app\common\model\AgentModel;
use cmf\controller\AdminBaseController;
use think\Exception;

class AgentController extends AdminBaseController
{

    /**
     * 获取生效代理客户数据
     * @return \think\response\Json
     */
    public function getAgentData()
    {
        $account = $this->request->param('account', null, 'string');
        $limit = $this->request->param('limit', 10, 'intval');
        $page = $this->request->param('page', 1, 'intval');
        try {
            $list   = AgentModel::getAgentListData($account,AgentModel::STATUS_NORMAL,$page,$limit);
            $count  = AgentModel::getAgentListCount($account,AgentModel::STATUS_NORMAL);
            return $this->returnListJson(self::CODE_OK, $count, $list, '获取代理客户列表成功');
        } catch (Exception $exception) {
            return $this->returnListJson(self::CODE_FAIL, null, null, $exception->getMessage());
        }
    }

    /**
     * 所有代理客户列表
     * @return mixed
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 获取所有代理客户列表数据
     * @return \think\response\Json
     */
    public function getAgentListData()
    {
        $status         = $this->request->param('status',null);
        $keyword        = $this->request->param('keyword',null,'string');
        $limit          = $this->request->param('limit',10,'intval');
        $page           = $this->request->param('page',1,'intval');
        try{
            $list   = AgentModel::getAgentListData($keyword,$status,$page,$limit);
            $count  = AgentModel::getAgentListCount($keyword,$status);
            return $this->returnListJson(self::CODE_OK,$count,$list,'获取代理客户列表成功！');
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL,null,null,$exception->getMessage());
        }
    }


    /**
     * 客户详细信息
     * @return mixed
     */
    public function agentInfo()
    {
        $agent_id        = $this->request->param('agent_id',null,'intval');
        try{
            if(empty($agent_id)){
                throw new Exception('非法访问！');
            }
            $this->assign('agent_id',$agent_id);
            $info = AgentModel::getAgentInfo($agent_id);
            $this->assign('info',$info);
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }
    }

    /**
     * 客户签名列表
     * @return mixed
     */
    public function signList()
    {
        $agent_id        = $this->request->param('agent_id',null,'intval');
        try{
            if(empty($agent_id)){
                throw new Exception('非法访问！');
            }
            $this->assign('agent_id',$agent_id);
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }
    }


    /**
     *客户签名列表
     * @return \think\response\Json
     */
    public function getSignListData()
    {
        $status         = $this->request->param('status',null);
        $is_del         = $this->request->param('is_del',null);
        $source         = $this->request->param('source',null);
        $start_time     = $this->request->param('start_time',null);
        $end_time       = $this->request->param('end_time',null);
        $keyword        = $this->request->param('keyword',null,'string');
        $limit          = $this->request->param('limit',10,'intval');
        $page           = $this->request->param('page',1,'intval');
        $agent_id        = $this->request->param('agent_id',null,'intval');
        try{
            if(empty($agent_id)){
                throw new Exception('非法访问！');
            }
            $sign_list =  SignModel::getSignListData($keyword,$is_del,$status,$source,$start_time,$end_time,$agent_id,$page,$limit);
            $count =SignModel::getSignListCount($keyword,$is_del,$status,$source,$start_time,$end_time,$agent_id);
            return $this->returnListJson(self::CODE_OK,$count,$sign_list,'获取代理客户签名列表成功！');
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL,null,null,$exception->getMessage());
        }
    }

    /**
     * 签名详情
     * @return mixed
     */
    public function signInfo()
    {
        $sign_id        = $this->request->param('sign_id',null,'intval');
        try{
            if(empty($sign_id)){
                throw new Exception('非法访问！');
            }
            $info = SignModel::getSignInfo($sign_id);
            $this->assign('info',$info);
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }
    }

    /**
     * 模板列表
     * @return mixed
     */
    public function templateList()
    {
        $agent_id        = $this->request->param('agent_id',null,'intval');
        try{
            if(empty($agent_id)){
                throw new Exception('非法访问！');
            }
            $product_list = ProductModel::getProductList();
            $this->assign('product_list',$product_list);
            $this->assign('agent_id',$agent_id);
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }
    }

    /**
     *客户模板列表数据
     * @return \think\response\Json
     */
    public function getTemplateListData()
    {
        $status         = $this->request->param('status',null);
        $is_del         = $this->request->param('is_del',null);
        $product_id     = $this->request->param('product_id',null);
        $start_time     = $this->request->param('start_time',null);
        $end_time       = $this->request->param('end_time',null);
        $keyword        = $this->request->param('keyword',null,'string');
        $limit          = $this->request->param('limit',10,'intval');
        $page           = $this->request->param('page',1,'intval');
        $agent_id       = $this->request->param('agent_id',null,'intval');
        try{
            if(empty($agent_id)){
                throw new Exception('非法访问！');
            }
            $template_list = TemplateModel::getTemplateListData($keyword,$product_id,$is_del,$status,$start_time,$end_time,$agent_id,$page,$limit);
            $count =TemplateModel::getTemplateListCount($keyword,$product_id,$is_del,$status,$start_time,$end_time,$agent_id);
            return $this->returnListJson(self::CODE_OK,$count,$template_list,'获取代理客户模板列表成功！');
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL,null,null,$exception->getMessage());
        }
    }


    /**
     * 模板详情
     * @return mixed
     */
    public function templateInfo()
    {
        $template_id        = $this->request->param('template_id',null,'intval');
        try{
            if(empty($template_id)){
                throw new Exception('非法访问！');
            }
            $info = TemplateModel::getTemplateInfo($template_id);
            $this->assign('info',$info);
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }

    }

    /**
     * 循环任务列表
     * @return mixed
     */
    public function cycleTaskList()
    {
        $agent_id        = $this->request->param('agent_id',null,'intval');
        try{
            if(empty($agent_id)){
                throw new Exception('非法访问！');
            }
            $product_list = ProductModel::getProductList();
            $this->assign('product_list',$product_list);
            $this->assign('agent_id',$agent_id);
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }

    }

    /**
     * 获取循环任务列表数据
     * @return \think\response\Json
     */
    public function getCycleTaskListData()
    {
        $status         = $this->request->param('status',null);
        $is_del         = $this->request->param('is_del',null);
        $product_id     = $this->request->param('product_id',null);
        $start_time     = $this->request->param('start_time',null);
        $end_time       = $this->request->param('end_time',null);
        $keyword        = $this->request->param('keyword',null,'string');
        $limit          = $this->request->param('limit',10,'intval');
        $page           = $this->request->param('page',1,'intval');
        $agent_id       = $this->request->param('agent_id',null,'intval');
        try{
            if(empty($agent_id)){
                throw new Exception('非法访问！');
            }
            $cycle_task_list = CycleTaskModel::getCycleTaskListData($keyword,$product_id,$is_del,$status,$start_time,$end_time,$agent_id,$page,$limit);
            $count =CycleTaskModel::getCycleTaskListCount($keyword,$product_id,$is_del,$status,$start_time,$end_time,$agent_id);
            return $this->returnListJson(self::CODE_OK,$count,$cycle_task_list,'获取循环任务列表数据成功！');
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL,null,null,$exception->getMessage());
        }
    }

    /**
     * 获取循环任务详情
     * @return mixed
     */
    public function cycleInfo()
    {
        $cycle_id =  $this->request->param('cycle_id',null,'intval');
        try{
            if(empty($cycle_id)){
                throw new Exception('非法访问！');
            }
            $info = CycleTaskModel::getCycleInfo($cycle_id);
            $this->assign('info',$info);
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }
    }


    /**
     * 变更循环任务状态
     * @return \think\response\Json
     */
    public function cycleTaskToDisable()
    {
        $cycle_id       = $this->request->param('id',null,'intval');
        try{
            if(empty($cycle_id)){
                throw new Exception('非法访问！');
            }
            $cycle_status = CycleTaskModel::toDisable($cycle_id);
            return $this->returnStatusJson(self::STATUS_OK,$cycle_status,'状态变更成功！');
        }catch (Exception $exception){
            return $this->returnStatusJson(self::STATUS_FAIL,null,null,$exception->getMessage());
        }

    }

    /**
     * 循环发送任务列表
     * @return mixed
     */
    public function cycleTask()
    {
        $cycle_id        = $this->request->param('cycle_id',null,'intval');
        try{
            if(empty($cycle_id)){
                throw new Exception('非法访问！');
            }
            $this->assign('cycle_id',$cycle_id);
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }
    }


    /**
     * 获取循环发送任务列表数据
     * @return \think\response\Json
     */
    public function getCycleTaskData()
    {
        $start_time     = $this->request->param('start_time',null);
        $end_time       = $this->request->param('end_time',null);
        $keyword        = $this->request->param('keyword',null,'string');
        $limit          = $this->request->param('limit',10,'intval');
        $page           = $this->request->param('page',1,'intval');
        $cycle_id       = $this->request->param('cycle_id',null,'intval');
        try{
            if(empty($cycle_id)){
                throw new Exception('非法访问！');
            }
            $task_list = TaskModel::getCycleTaskData($cycle_id,$start_time,$end_time,$page,$limit);
            $count =TaskModel::getCycleTaskCount($cycle_id,$start_time,$end_time);
            return $this->returnListJson(self::CODE_OK,$count,$task_list,'获取循环发送任务列表数据成功！');
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL,null,null,$exception->getMessage());
        }
    }


    /**
     * 发送任务列表
     * @return mixed
     */
    public function taskList()
    {
        $agent_id        = $this->request->param('agent_id',null,'intval');
        try{
            if(empty($agent_id)){
                throw new Exception('非法访问！');
            }
            $product_list = ProductModel::getProductList();
            $this->assign('product_list',$product_list);
            $this->assign('agent_id',$agent_id);
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }
    }

    /**
     * 获取发送任务列表数据
     * @return \think\response\Json
     */
    public function getTaskListData()
    {
        $status         = $this->request->param('status',null);
        $is_del         = $this->request->param('is_del',null);
        $product_id     = $this->request->param('product_id',null);
        $start_time     = $this->request->param('start_time',null);
        $end_time       = $this->request->param('end_time',null);
        $keyword        = $this->request->param('keyword',null,'string');
        $limit          = $this->request->param('limit',10,'intval');
        $page           = $this->request->param('page',1,'intval');
        $agent_id       = $this->request->param('agent_id',null,'intval');
        try{
            if(empty($agent_id)){
                throw new Exception('非法访问！');
            }
            $task_list = TaskModel::getTaskListData($keyword,$product_id,$is_del,$status,$start_time,$end_time,$agent_id,$page,$limit);
            $count =TaskModel::getTaskListCount($keyword,$product_id,$is_del,$status,$start_time,$end_time,$agent_id);
            return $this->returnListJson(self::CODE_OK,$count,$task_list,'获取发送任务列表数据成功！');
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL,null,null,$exception->getMessage());
        }
    }


    /**
     * 获取循环任务详情
     * @return mixed
     */
    public function taskInfo()
    {
        $task_id =  $this->request->param('task_id',null,'intval');
        try{
            if(empty($task_id)){
                throw new Exception('非法访问！');
            }
            $info = TaskModel::getTaskInfo($task_id);
            $this->assign('info',$info);
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }
    }


    /**
     * 发送任务列表
     * @return mixed
     */
    public function sendRecordList()
    {
        $agent_id        = $this->request->param('agent_id',null,'intval');
        try{
            if(empty($agent_id)){
                throw new Exception('非法访问！');
            }
            $product_list = ProductModel::getProductList();
            $this->assign('product_list',$product_list);
            $this->assign('agent_id',$agent_id);
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }
    }


    /**
     * 获取发送任务列表数据
     * @return \think\response\Json
     */
    public function getSendRecordListData()
    {
        $status         = $this->request->param('status',null);
        $product_id     = $this->request->param('product_id',null);
        $start_time     = $this->request->param('start_time',null);
        $end_time       = $this->request->param('end_time',null);
        $keyword        = $this->request->param('keyword',null,'string');
        $limit          = $this->request->param('limit',10,'intval');
        $page           = $this->request->param('page',1,'intval');
        $agent_id       = $this->request->param('agent_id',null,'intval');
        $task_id       = $this->request->param('task_id',null,'intval');
        try{
            if(empty($agent_id) && empty($task_id)){
                throw new Exception('非法访问！');
            }
            $task_list = SendRecordModel::getSendRecordListData($keyword,$product_id,$status,$start_time,$end_time,$agent_id,$task_id,$page,$limit);
            $count =SendRecordModel::getSendRecordListCount($keyword,$product_id,$status,$start_time,$end_time,$agent_id,$task_id);
            return $this->returnListJson(self::CODE_OK,$count,$task_list,'获取发送任务列表数据成功！');
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL,null,null,$exception->getMessage());
        }
    }

    /**
     * 发送任务列表
     * @return mixed
     */
    public function sendRecord()
    {
        $task_id        = $this->request->param('task_id',null,'intval');
        try{
            if(empty($task_id)){
                throw new Exception('非法访问！');
            }
            $product_list = ProductModel::getProductList();
            $this->assign('product_list',$product_list);
            $this->assign('task_id',$task_id);
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }
    }

    /**
     * 所有代理客户消费列表
     * @return mixed
     */
    public function agentChargeList()
    {
        return $this->fetch();
    }

    /**
     * 获取生效代理客户数据
     * @return \think\response\Json
     */
    public function getAgentChargeListData()
    {
        $status     = $this->request->param('status',null);
        $keyword    = $this->request->param('keyword',null,'string');
        $limit      = $this->request->param('limit', 10, 'intval');
        $page       = $this->request->param('page', 1, 'intval');
        try {
            $agent_list = AgentModel::getAgentListData($keyword,$status,$page,$limit);
            $list       = ChargeRecordModel::getAgentAmount($agent_list);
            $count      = AgentModel::getAgentListCount($keyword,$status);
            return $this->returnListJson(self::CODE_OK, $count, $list, '获取代理客户扣费列表成功');
        } catch (Exception $exception) {
            return $this->returnListJson(self::CODE_FAIL, null, null, $exception->getMessage());
        }
    }

    /**
     * 代理客户消费列表
     * @return mixed
     */
    public function chargeRecordList()
    {
        $agent_id        = $this->request->param('agent_id',null,'intval');
        try{
            if(empty($agent_id)){
                throw new Exception('非法访问！');
            }
            $this->assign('agent_id',$agent_id);
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }
    }


    /**
     * 获取生效消费数据
     * @return \think\response\Json
     */
    public function getChargeRecordListData()
    {
        $start_time     = $this->request->param('start_time',null);
        $end_time       = $this->request->param('end_time',null);
        $agent_id       = $this->request->param('agent_id',null,'intval');
        $limit          = $this->request->param('limit', 10, 'intval');
        $page           = $this->request->param('page', 1, 'intval');
        try {
            $list       = ChargeRecordModel::getSendRecordListData($start_time,$end_time,$agent_id,$page,$limit);
            $count      = ChargeRecordModel::getSendRecordListCount($start_time,$end_time,$agent_id);
            return $this->returnListJson(self::CODE_OK, $count, $list, '获取代理客户扣费列表成功');
        } catch (Exception $exception) {
            return $this->returnListJson(self::CODE_FAIL, null, null, $exception->getMessage());
        }
    }

    /**
     * 代理客户消费列表
     * @return mixed
     */
    public function chargeInfo()
    {
        $agent_id        = $this->request->param('agent_id',null,'intval');
        $create_at       = $this->request->param('create_at',null);
        try{
            if(empty($agent_id) || empty($create_at)){
                throw new Exception('非法访问！');
            }
            $product_list = ProductModel::getProductList();
            $this->assign('product_list',$product_list);
            $this->assign('agent_id',$agent_id);
            $this->assign('create_at',$create_at);
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }
    }


    /**
     * 获取生效消费数据
     * @return \think\response\Json
     */
    public function getChargeRecordData()
    {
        $create_at      = $this->request->param('create_at',null);
        $product_id     = $this->request->param('product_id',null);
        $keywords       = $this->request->param('keyword',null,'string');
        $agent_id       = $this->request->param('agent_id',null,'intval');
        $limit          = $this->request->param('limit', 10, 'intval');
        $page           = $this->request->param('page', 1, 'intval');
        try {
            $list       = ChargeRecordModel::getSendRecordData($create_at,$agent_id,$product_id,$keywords,$page,$limit);
            $count      = ChargeRecordModel::getSendRecordCount($create_at,$agent_id,$product_id,$keywords);;
            return $this->returnListJson(self::CODE_OK, $count, $list, '获取代理客户扣费列表成功');
        } catch (Exception $exception) {
            return $this->returnListJson(self::CODE_FAIL, null, null, $exception->getMessage());
        }
    }




}


