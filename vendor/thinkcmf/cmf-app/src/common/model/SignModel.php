<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/1
 * Time: 17:45
 */

namespace app\common\model;


use think\Exception;
use think\Model;

class SignModel extends Model
{
    const STATUS_AUDITING   = 0;    //  状态 审核中
    const STATUS_ADOPT      = 1;    //  状态 通过
    const STATUS_REJECT     = 2;    //  状态 驳回

    const IS_DEL_NO         = 0;    //  未删除
    const IS_DEL_YES        = 1;    //  已删除

    const SOURCE_COMPANY    = 1;    //企事业单位的全称或简称
    const SOURCE_WEB        = 2;    //工信部备案网站的全称或简称
    const SOURCE_APP        = 3;    //APP应用的全称或简称
    const SOURCE_ACCOUNTS   = 4;    //公众号或小程序的全称或简称


    /**
     * 获取签名列表
     * @param null $keyword         //索引关键词
     * @param null $status          //索引状态
     * @param null $source          //查询来源
     * @param null $start_time      // 筛选开始时间
     * @param null $end_time        // 筛选结束时间
     * @param null $is_del          //是否已删除
     * @param null $agent_id        //代理客户ID
     * @param int $page             // 页码
     * @param int $limit            // 每页条数
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getSignListData($keyword=null,$is_del=null,$status=null,$source=null,$start_time=null,$end_time=null,$agent_id=null,$page=1,$limit=10)
    {
        $where = [];
        if(empty($keyword) === false){
            $keywords = trim($keyword);
            $where[] =['sign','like',"%$keywords%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['status','=',$status];
        }
        if(empty($is_del) === false || $is_del === 0 || $is_del === '0'){
            $where[] = ['is_del','=',$is_del];
        }
        if(empty($agent_id) === false){
            $where[] = ['agent_id','=',$agent_id];
        }
        if(empty($source) === false){
            $where[] = ['source','=',$source];
        }
        if(empty($start_time) === false && empty($end_time) === false){
            $start_time= formatting_time($start_time);
            $end_time = formatting_time($end_time);
            if(empty($start_time) === false && empty($end_time) === false){
                $where[] = ['create_at','between',[$start_time,$end_time]];
            }
        }
        $list = self::where($where)
                ->limit(($page-1)*$limit,$limit)
                ->field(['id','sign','source','status','create_at','is_del'])
                ->order('id desc')
                ->select();
        return $list;
    }

    /**
     * 获取签名列表数量
     * @param null $keyword         //索引关键词
     * @param null $status          //索引状态
     * @param null $source          //查询来源
     * @param null $start_time      // 筛选开始时间
     * @param null $end_time        // 筛选结束时间
     * @param null $is_del          //是否已删除
     * @param null $agent_id        //代理客户ID
     * @return float|string
     */
    static public function getSignListCount($keyword=null,$is_del=null,$status=null,$source=null,$start_time=null,$end_time=null,$agent_id=null)
    {
        $where = [];
        if(empty($keyword) === false){
            $keywords = trim($keyword);
            $where[] =['sign','like',"%$keywords%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['status','=',$status];
        }
        if(empty($is_del) === false || $is_del === 0 || $is_del === '0'){
            $where[] = ['is_del','=',$is_del];
        }
        if(empty($agent_id) === false){
            $where[] = ['agent_id','=',$agent_id];
        }
        if(empty($source) === false){
            $where[] = ['source','=',$source];
        }
        if(empty($start_time) === false && empty($end_time) === false){
            $start_time= formatting_time($start_time);
            $end_time = formatting_time($end_time);
            if(empty($start_time) === false && empty($end_time) === false){
                $where[] = ['create_at','between',[$start_time,$end_time]];
            }
        }
        $count = self::where($where)->count();
        return $count;
    }


    /**
     * 获取签名详细信息
     * @param $id       //签名ID
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getSignInfo($id)
    {
        $info = self::alias('s')
                ->leftJoin('apply a','s.apply_id = a.id')
                ->where('s.id',$id)
                ->field(['s.id','s.sign','s.agent_id','s.create_at','s.status','s.is_del','s.source','a.reason','a.description','a.review_time','a.apply_time'])
                ->find();
        $info['company'] = AgentModel::getAgentCompanyById($info['agent_id']);
        return $info;
    }

    /**
     * 添加新的签名
     * @param $sign         //签名
     * @param $agent_id     //代理客户ID
     * @param $source       //签名来源
     * @return int|string
     */
    static private function addSign($sign,$agent_id,$source)
    {
        return self::insertGetId([
                    'sign'      =>$sign,
                    'source'    =>$source,
                    'agent_id'  =>$agent_id,
                    'is_del'    =>self::IS_DEL_NO,
                    'status'    =>self::STATUS_AUDITING,
                    'create_at' =>date('Y-m-d H:i:s',time())
                ]);
    }

    /**
     * 创建新的签名
     * @param $sign         //签名
     * @param $agent_id     //代理客户ID
     * @param $source       //签名来源
     * @param $description  //申请说明
     * @return SignModel
     */
    static public function newAddSign($sign,$agent_id,$source,$description)
    {
        $sign_id = self::addSign($sign,$agent_id,$source);
        $apply_id = ApplyModel::addApply($agent_id,$sign_id,ApplyModel::TYPE_SIGN,$description);
        return self::bindApplyId($sign_id,$apply_id);
    }

    /**
     * 绑定申请ID
     * @param $sign_id      //签名ID
     * @param $apply_id     //申请ID
     * @return SignModel
     */
    static private function bindApplyId($sign_id,$apply_id)
    {
        return self::update([
                'id'        => $sign_id,
                'apply_id'  => $apply_id
            ]);
    }


    /**
     * 通过签名
     * @param $sign_id
     * @return SignModel
     */
    static public function adoptSign($sign_id)
    {
        return self::update([
            'id'        => $sign_id,
            'status'    => self::STATUS_ADOPT
        ]);
    }

    /**
     * 驳回签名
     * @param $sign_id
     * @return SignModel
     */
    static public function rejectSign($sign_id)
    {
        return self::update([
            'id'        => $sign_id,
            'status'    => self::STATUS_REJECT
        ]);
    }

    /**
     * 修改签名重新审核
     * @param $id           //签名ID
     * @param $sign         //签名
     * @param $source       //签名来源
     * @param $description  //申请说明
     * @return SignModel
     * @throws Exception
     */
    static public function editSign($id,$sign,$source,$description)
    {
        $sign_info = self::getSignStatus($id);
        if(empty($sign_info)){
            throw new Exception('查找不到当前签名！');
        }
        if($sign_info['status'] == self::STATUS_AUDITING){
            throw new Exception('当前签名正在审核中！');
        }
        $apply_id = ApplyModel::addApply($sign_info['agent_id'],$sign_info['id'],ApplyModel::TYPE_SIGN,$description);
        return self::updateSign($id,$sign,$source,$apply_id);
    }


    /**
     * 获取签名状态
     * @param $id       //签名ID
     * @return mixed
     */
    static public function getSignStatus($id)
    {
        return self::where(['id'=>$id,'is_del'=>self::IS_DEL_NO])->field(['id,status,agent_id'])->find();
    }


    /**
     * 修改签名
     * @param $id           //签名ID
     * @param $sign         //签名
     * @param $source       //签名来源
     * @param $apply_id     //申请ID
     * @return SignModel
     */
    static public function updateSign($id,$sign,$source,$apply_id)
    {
        return self::update([
            'id'        =>$id,
            'sign'      =>$sign,
            'source'    =>$source,
            'status'    =>self::STATUS_AUDITING,
            'apply_id'  =>$apply_id
        ]);
    }

    /**
     * 删除签名
     * @param $id           //签名ID
     * @return SignModel
     */
    static public function delSign($id)
    {
        return self::update([
            'id'=>$id,
            'is_del'=>self::IS_DEL_YES
        ]);
    }

    /**
     * 获取有效签名
     * @param $id           //签名ID
     * @param $agent_id     //代理客户ID
     * @return mixed
     */
    static public function getSignById($agent_id,$id)
    {
        return self::where('id',$id)
                        ->where('agent_id',$agent_id)
                        ->where('is_del',self::IS_DEL_NO)
                        ->where('status',self::STATUS_ADOPT)
                        ->value('sign');
    }


    /**
     * 根据用户ID 获取签名名称
     * @param $agent_id
     * @param $is_del
     * @return array
     */
    static public function getSignName($agent_id,$is_del)
    {
        return self::where(['agent_id'=>$agent_id,'is_del'=>$is_del,'status'=>self::STATUS_ADOPT])->field(['sign','id as sign_id'])->select()->toArray();
    }
}