<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/1
 * Time: 16:01
 */

namespace app\common\model;


use think\Exception;
use think\Model;

class ApplyModel extends Model
{
    const TYPE_SIGN     = 1; //类别 签名
    const TYPE_TEMPLATE = 2; //类别 模板

    const STATUS_AUDITING   = 0;    //  状态 审核中
    const STATUS_ADOPT      = 1;    //  状态 通过
    const STATUS_REJECT     = 2;    //  状态 驳回


    /**
     * 添加新的申请
     * @param $agent_id     //申请客户ID
     * @param $info_id      //申请信息ID
     * @param $type         //申请类别
     * @param $description  //申请说明
     * @return int|string
     */
    static public function addApply($agent_id,$info_id,$type,$description)
    {
        return self::insertGetId([
            'type'          =>$type,
            'info_id'       =>$info_id,
            'agent_id'      =>$agent_id,
            'description'   =>$description,
            'status'        =>self::STATUS_AUDITING,
            'apply_time'    =>date("Y-m-d H:i:s",time())
        ]);
    }

    /**
     * 通过申请
     * @param $id
     * @return ApplyModel
     */
    static public function adoptApply($id)
    {
        return self::update([
            'id'            =>$id,
            'status'        =>self::STATUS_ADOPT,
            'admin_id'      =>cmf_get_current_admin_id(),
            'review_time'   =>date("Y-m-d H:i:s",time())
        ]);
    }


    /**
     * 驳回申请
     * @param $id
     * @param $reason
     * @return ApplyModel
     */
    static public function rejectApply($id,$reason)
    {
        return self::update([
            'id'            =>$id,
            'reason'        =>$reason,
            'status'        =>self::STATUS_REJECT,
            'admin_id'      =>cmf_get_current_admin_id(),
            'review_time'   =>date("Y-m-d H:i:s",time())
        ]);
    }


    /**
     *
     * * 获取签名申请列表
     * @param null $keyword     //查询关键词
     * @param null $status      //查询状态
     * @param null $source      //查询来源
     * @param null $start_time  //查询开始时间
     * @param null $end_time    //查询结束时间
     * @param int $page         // 页码
     * @param int $limit        //每页条数
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getSignApplyList($keyword=null,$status=null,$source=null,$start_time=null,$end_time=null,$page=1,$limit=10)
    {
        $where = [];
        if(empty($keyword) === false){
            $keywords = trim($keyword);
            $where[] = ['s.sign','like',"%$keywords%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['a.status','=',$status];
        }
        if(empty($source) === false){
            $where[] = ['s.source','=',$source];
        }
        if(empty($start_time) === false && empty($end_time) === false){
            $start_time= formatting_time($start_time);
            $end_time = formatting_time($end_time);
            if(empty($start_time) === false && empty($end_time) === false){
                $where[] = ['a.apply_time','between',[$start_time,$end_time]];
            }
        }
        $list = self::alias('a')
                ->leftJoin('sign s','a.info_id = s.id')
                ->where('a.type',self::TYPE_SIGN)
                ->where($where)
                ->limit(($page-1)*$limit,$limit)
                ->field(['a.id','s.sign','a.status','s.source','a.apply_time','a.agent_id'])
                ->order('a.id desc')
                ->select();
        foreach ($list as $key=>$value){
            $list[$key]['company'] = AgentModel::getAgentCompanyById($value['agent_id']);
        }
        return $list;
    }


    /**
     * 获取签名申请列表数量
     * @param null $keyword     //查询关键词
     * @param null $status      //查询状态
     * @param null $source      //查询来源
     * @param null $start_time  //查询开始时间
     * @param null $end_time    //查询结束时间
     * @return float|string
     */
    static public function getSignApplyCount($keyword=null,$status=null,$source=null,$start_time=null,$end_time=null)
    {
        $where = [];
        if(empty($keyword) === false){
            $keywords = trim($keyword);
            $where[] = ['s.sign','like',"%$keywords%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['a.status','=',$status];
        }
        if(empty($source) === false){
            $where[] = ['s.source','=',$source];
        }
        if(empty($start_time) === false && empty($end_time) === false){
            $start_time= formatting_time($start_time);
            $end_time = formatting_time($end_time);
            if(empty($start_time) === false && empty($end_time) === false){
                $where[] = ['a.apply_time','between',[$start_time,$end_time]];
            }
        }
        $count = self::alias('a')
                ->leftJoin('sign s','a.info_id = s.id')
                ->where('a.type',self::TYPE_SIGN)
                ->where($where)
                ->count();
        return $count;
    }


    /**
     * 获取模板申请列表
     * @param null $keyword     //查询关键词
     * @param null $status      //查询状态
     * @param null $start_time  //查询开始时间
     * @param null $end_time    //查询结束时间
     * @param int $page         // 页码
     * @param int $limit        //每页条数
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getTemplateApplyList($keyword=null,$status=null,$start_time=null,$end_time=null,$page=1,$limit=10)
    {
        $where = [];
        if(empty($keyword) === false){
            $keywords = trim($keyword);
            $where[] =['t.name','like',"%$keywords%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['a.status','=',$status];
        }
        if(empty($start_time) === false && empty($end_time) === false){
            $start_time= formatting_time($start_time);
            $end_time = formatting_time($end_time);
            if(empty($start_time) === false && empty($end_time) === false){
                $where[] = ['a.apply_time','between',[$start_time,$end_time]];
            }
        }
        $list = self::alias('a')
            ->leftJoin('template t','a.info_id = t.id')
            ->leftJoin('product p','t.product_id = p.id')
            ->where('a.type',self::TYPE_TEMPLATE)
            ->where($where)
            ->limit(($page-1)*$limit,$limit)
            ->field(['a.id','t.name','a.status','a.apply_time','a.agent_id','p.name as product_name'])
            ->order('a.id desc')
            ->select();
        foreach ($list as $key=>$value){
            $list[$key]['company'] = AgentModel::getAgentCompanyById($value['agent_id']);
        }
        return $list;
    }

    /**
     * 获取模板申请列表条数
     * @param null $keyword     //查询关键词
     * @param null $status      //查询状态
     * @param null $start_time  //查询开始时间
     * @param null $end_time    //查询结束时间
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getTemplateApplyCount($keyword=null,$status=null,$start_time=null,$end_time=null)
    {
        $where = [];
        if(empty($keyword) === false){
            $keywords = trim($keyword);
            $where[] =['t.name','like',"%$keywords%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['a.status','=',$status];
        }
        if(empty($start_time) === false && empty($end_time) === false){
            $start_time= formatting_time($start_time);
            $end_time = formatting_time($end_time);
            if(empty($start_time) === false && empty($end_time) === false){
                $where[] = ['a.apply_time','between',[$start_time,$end_time]];
            }
        }
        $count = self::alias('a')
            ->leftJoin('template t','a.info_id = t.id')
            ->leftJoin('product p','t.product_id = p.id')
            ->where('a.type',self::TYPE_TEMPLATE)
            ->where($where)
            ->count();
        return $count;
    }


    /**
     * 签名审核
     * @param $apply_id     //申请ID
     * @param $status       //审核结果
     * @param $reason       //驳回原因
     * @throws Exception
     */
    static public function signExamine($apply_id,$status,$reason)
    {
        $sign_id = self::getInfoId($apply_id);
        if(empty($sign_id)){
            throw new Exception('查找不到申请数据！');
        }
        switch ($status){
            case self::STATUS_ADOPT:
                self::adoptApply($apply_id);
                SignModel::adoptSign($sign_id);
                break;
            case self::STATUS_REJECT:
                if(empty($reason)){
                    throw new Exception('请填写驳回原因！');
                }
                self::rejectApply($apply_id,$reason);
                SignModel::rejectSign($sign_id);
                break;
            default:
                throw new Exception('提交的审核状态不正确！');
        }
    }


    /**
     * 模板审核
     * @param $apply_id     //申请ID
     * @param $status       //审核结果
     * @param $reason       //驳回原因
     * @throws Exception
     */
    static public function templateExamine($apply_id,$status,$reason)
    {
        $template_id = self::getInfoId($apply_id);
        if(empty($template_id)){
            throw new Exception('查找不到申请数据！');
        }
        switch ($status){
            case self::STATUS_ADOPT:
                self::adoptApply($apply_id);
                TemplateModel::adoptTemplate($template_id);
                break;
            case self::STATUS_REJECT:
                if(empty($reason)){
                    throw new Exception('请填写驳回原因！');
                }
                self::rejectApply($apply_id,$reason);
                TemplateModel::rejectTemplate($template_id);
                break;
            default:
                throw new Exception('提交的审核状态不正确！');
        }
    }

    /**
     * 获取信息ID
     * @param $apply_id
     * @return mixed
     */
    static public function getInfoId($apply_id)
    {
        $info_id = self::where('id',$apply_id)->value('info_id');
        return $info_id;
    }

    /**
     * 获取签名申请详细信息
     * @param $id
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getSignApplyInfo($id)
    {
        $info = self::alias('a')
                ->leftJoin('sign s','a.info_id = s.id')
                ->where('a.id',$id)
                ->where('a.type',self::TYPE_SIGN)
                ->field(['s.sign','s.source','a.status','a.apply_time','a.agent_id','a.description','a.review_time','a.reason','a.id'])
                ->find();
        if(empty($info)){
            throw new Exception('数据不存在！');
        }
        $info['company'] = AgentModel::getAgentCompanyById($info['agent_id']);
        return $info;
    }

    /**
     *获取模板申请详细信息
     * @param $id
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getTemplateApplyInfo($id)
    {
        $info = self::alias('a')
            ->leftJoin('template t','a.info_id = t.id')
            ->leftJoin('product p','t.product_id = p.id')
            ->where('a.id',$id)
            ->where('a.type',self::TYPE_TEMPLATE)
            ->field(['t.name','t.content','a.id','a.status','a.apply_time','a.agent_id','a.description','a.review_time','a.review_time','a.reason','p.name as product_name','p.price'])
            ->find();
        $info['company'] = AgentModel::getAgentCompanyById($info['agent_id']);
        return $info;
    }



}