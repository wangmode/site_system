<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/10
 * Time: 10:34
 */

namespace app\common\model;


use think\Model;

class SendRecordModel extends Model
{
    const STATUS_NO     = 0; //发送失败
    const STATUS_YES    = 1; //发送成功

    /**
     * 获取消息记录列表数据
     * @param null $keyword         //搜索关键词
     * @param null $product_id      //搜索产品ID
     * @param null $status          //状态筛选
     * @param null $start_time      //筛选 创建时间 （起始时间筛选）
     * @param null $end_time        //筛选 创建时间 （结束时间筛选）
     * @param null $agent_id        // 代理客户ID
     * @param null $task_id        // 任务ID
     * @param int $page             //页码
     * @param int $limit            //每页条数
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getSendRecordListData($keyword=null,$product_id=null,$status=null,$start_time=null,$end_time=null,$agent_id = null,$task_id = null,$page=1,$limit=10)
    {
        $where = [];
        if(empty($keyword) === false){
            $keywords = trim($keyword);
            $where[] =['t.name|s.sign|sr.phone','like',"%$keywords%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['sr.status','=',$status];
        }
        if(empty($product_id) === false){
            $where[] = ['t.product_id','=',$product_id];
        }
        if(empty($agent_id) === false){
            $where[] = ['sr.agent_id','=',$agent_id];
        }
        if(empty($task_id) === false){
            $where[] = ['sr.task_id','=',$task_id];
        }
        if(empty($start_time) === false && empty($end_time) === false){
            $start_time= formatting_time($start_time);
            $end_time = formatting_time($end_time);
            if(empty($start_time) === false && empty($end_time) === false){
                $where[] = ['sr.send_time','between',[$start_time,$end_time]];
            }
        }
        $list = self::alias('sr')
            ->leftJoin('task ta','sr.task_id = ta.id')
            ->leftJoin('sign s','ta.sign_id = s.id')
            ->leftJoin('template t','ta.template_id = t.id')
            ->leftJoin('product p','t.product_id = p.id')
            ->where($where)
            ->limit(($page-1)*$limit,$limit)
            ->field(['sr.id','sr.phone','ta.content','sr.reason','s.sign','t.name','sr.status','sr.send_time','p.name as product_name'])
            ->order('sr.id desc')
            ->select();
        return $list;
    }


    /**
     * 获取消息记录列表数据
     * @param null $keyword         //搜索关键词
     * @param null $product_id      //搜索产品ID
     * @param null $status          //状态筛选
     * @param $send_time            //发送日期
     * @param null $agent_id        // 代理客户ID
     * @param int $page             //页码
     * @param int $limit            //每页条数
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getSendRecordList($keyword,$product_id,$status,$send_time,$agent_id,$page,$limit)
    {
        $start_time = null;
        $end_time = null;
        if(empty($send_time) === false){
            $start_time = "$send_time 00:00:00";
            $end_time   = "$send_time 23:59:59";
        }
        return self::getSendRecordListData($keyword,$product_id,$status,$start_time,$end_time,$agent_id ,$task_id = null,$page,$limit);
    }

    /**
     * 获取导出发送记录数据
     * @param $keyword          //索引关键词
     * @param $product_id       //产品ID
     * @param $status           //发送状态
     * @param $send_time        //发送日期
     * @param $start_time       //筛选开始时间
     * @param $end_time         //筛选结束时间
     * @param $agent_id         //代理客户ID
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getExplodeSendRecordList($keyword=null,$product_id=null,$status=null,$send_time=null,$start_time=null,$end_time=null,$agent_id=null)
    {
        $where = [];
        if(empty($keyword) === false){
            $keywords = trim($keyword);
            $where[] =['t.name|s.sign|sr.phone','like',"%$keywords%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['sr.status','=',$status];
        }
        if(empty($product_id) === false){
            $where[] = ['t.product_id','=',$product_id];
        }
        if(empty($agent_id) === false){
            $where[] = ['sr.agent_id','=',$agent_id];
        }
        if(empty($send_time) === false){
            $start_time = "$send_time 00:00:00";
            $end_time   = "$send_time 23:59:59";
        }

        if(empty($start_time) === false && empty($end_time) === false){
            $start_time= formatting_time($start_time);
            $end_time = formatting_time($end_time);
            if(empty($start_time) === false && empty($end_time) === false){
                $where[] = ['sr.send_time','between',[$start_time,$end_time]];
            }
        }
        $list = self::alias('sr')
            ->leftJoin('task ta','sr.task_id = ta.id')
            ->leftJoin('sign s','ta.sign_id = s.id')
            ->leftJoin('template t','ta.template_id = t.id')
            ->leftJoin('product p','t.product_id = p.id')
            ->where($where)
            ->field(['sr.id','sr.phone','ta.content','sr.reason','s.sign','t.name',"if(sr.status=1,'成功','失败') as status",'sr.send_time','p.name as product_name'])
            ->order('sr.id desc')
            ->select();
        return $list;
    }


    /**
     * 获取消息记录列表数据
     * @param null $keyword         //搜索关键词
     * @param null $product_id      //搜索产品ID
     * @param null $status          //状态筛选
     * @param $send_time            //发送日期
     * @param null $agent_id        // 代理客户ID
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getSendCount($keyword,$product_id,$status,$send_time,$agent_id)
    {
        $start_time = null;
        $end_time = null;
        if(empty($send_time) === false){
            $start_time = "$send_time 00:00:00";
            $end_time   = "$send_time 23:59:59";
        }
        return self::getSendRecordListCount($keyword,$product_id,$status,$start_time,$end_time,$agent_id ,$task_id = null);
    }





    /**
     * 获取消息记录列表数据
     * @param null $keyword         //搜索关键词
     * @param null $product_id      //搜索产品ID
     * @param null $status          //状态筛选
     * @param null $start_time      //筛选 创建时间 （起始时间筛选）
     * @param null $end_time        //筛选 创建时间 （结束时间筛选）
     * @param null $agent_id        // 代理客户ID
     * @param null $task_id        // 任务ID
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getSendRecordListCount($keyword=null,$product_id=null,$status=null,$start_time=null,$end_time=null,$agent_id = null,$task_id = null)
    {
        $where = [];
        if(empty($keyword) === false){
            $keywords = trim($keyword);
            $where[] =['t.name|s.sign|sr.phone','like',"%$keywords%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['sr.status','=',$status];
        }
        if(empty($product_id) === false){
            $where[] = ['t.product_id','=',$product_id];
        }
        if(empty($agent_id) === false){
            $where[] = ['sr.agent_id','=',$agent_id];
        }
        if(empty($start_time) === false && empty($end_time) === false){
            $start_time= formatting_time($start_time);
            $end_time = formatting_time($end_time);
            if(empty($start_time) === false && empty($end_time) === false){
                $where[] = ['sr.send_time','between',[$start_time,$end_time]];
            }
        }
        $count = self::alias('sr')
            ->leftJoin('task ta','sr.task_id = ta.id')
            ->leftJoin('sign s','ta.sign_id = s.id')
            ->leftJoin('template t','ta.template_id = t.id')
            ->leftJoin('product p','t.product_id = p.id')
            ->where($where)
            ->count();
        return $count;
    }

    /**
     * 获取消息记录列表数据
     * @param null $start_time      //筛选 创建时间 （起始时间筛选）
     * @param null $end_time        //筛选 创建时间 （结束时间筛选）
     * @param null $agent_id        // 代理客户ID
     * @param int $page             //页码
     * @param int $limit            //每页条数
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getSendRecordData($start_time=null,$end_time=null,$agent_id = null,$page=1,$limit=10)
    {
        $where = [];
        if(empty($agent_id) === false){
            $where[] = ['agent_id','=',$agent_id];
        }
        if(empty($start_time) === false && empty($end_time) === false){
            $start_time= formatting_time($start_time);
            $end_time = formatting_time($end_time);
            if(empty($start_time) === false && empty($end_time) === false){
                $where[] = ['send_time','between',[$start_time,$end_time]];
            }
        }
        $list = self::where($where)
            ->limit(($page-1)*$limit,$limit)
            ->field(["DATE_FORMAT (send_time,'%Y-%m-%d') as date",'count(*) as num'])
            ->order('date desc')
            ->group('date')
            ->select();
        return $list;
    }

    /**
     * 获取消息记录列表数据
     * @param null $start_time      //筛选 创建时间 （起始时间筛选）
     * @param null $end_time        //筛选 创建时间 （结束时间筛选）
     * @param null $agent_id        // 代理客户ID
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getSendRecordCount($start_time=null,$end_time=null,$agent_id = null)
    {
        $where = [];
        if(empty($agent_id) === false){
            $where[] = ['sr.agent_id','=',$agent_id];
        }
        if(empty($start_time) === false && empty($end_time) === false){
            $start_time= formatting_time($start_time);
            $end_time = formatting_time($end_time);
            if(empty($start_time) === false && empty($end_time) === false){
                $where[] = ['sr.send_time','between',[$start_time,$end_time]];
            }
        }
        $count = self::alias('sr')
            ->where($where)
            ->field(["DATE_FORMAT (send_time,'%Y-%m-%d') as date"])
            ->order('date desc')
            ->group('date')
            ->count();
        return $count;
    }

    /**
     * 通过task_id 获取普通任务的发送记录
     * @param $task_id
     * @param $page
     * @param $limit
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getTaskIdSendRecordData($task_id,$status,$page,$limit)
    {
        if(empty($task_id) === false){
            $where[] =['task_id','=',$task_id];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['sr.status','=',$status];
        }
        $list = self::alias('sr')
            ->leftJoin('task ta','sr.task_id = ta.id')
            ->leftJoin('sign s','ta.sign_id = s.id')
            ->leftJoin('template t','ta.template_id = t.id')
            ->leftJoin('product p','t.product_id = p.id')
            ->where($where)
            ->limit(($page-1)*$limit,$limit)
            ->field(['sr.id','sr.phone','ta.content','sr.reason','s.sign','t.name','sr.status','sr.send_time','p.name as product_name'])
            ->order('sr.id desc')
            ->select();
        return $list;
    }

    static public function getTaskIdSendRecordCount($task_id,$status)
    {
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['status','=',$status];
        }
        if(empty($task_id) === false){
            $where[] =['task_id','=',$task_id];
        }
        self::where($where)->count();
    }
/*
     * 添加短信发送记录
     * @param $phone        //电话号码
     * @param $status       //发送状态
     * @param $task_id      //任务ID
     * @param $agent_id     //代理客户ID
     * @param $order_no     // 发送订单号
     * @param $reason       //发送失败原因
     * @return int|string
     */
    static public function addSendRecord($phone,$status,$task_id,$agent_id,$order_no,$reason)
    {
        return self::insertGetId([
            'num'       => 1,
            'phone'     => $phone,
            'status'    => $status,
            'reason'    => $reason,
            'task_id'   => $task_id,
            'agent_id'  => $agent_id,
            'order_no'  => $order_no,
            'send_time'   => date('Y-m-d H:i:s',time())
        ]);
    }

    /**
     * 按月统计发送数量
     * @param $month
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getSendRecordCountByMonth($month,$agent_id)
    {
        $where = [];
        if(empty($agent_id) === false){
            $where[] = ['agent_id','=',$agent_id];
        }
        return self::where(" date_format(send_time,'%Y%m') = date_format(DATE_SUB(curdate(), INTERVAL $month MONTH), '%Y%m') ")
            ->where($where)
            ->field(['count(*) as num',"date_format(DATE_SUB(curdate(), INTERVAL $month MONTH), '%Y-%m') as month"])
            ->find();
    }

    /**
     * 获取最近12个月统计数据
     * @param null $agent_id //代理客户ID
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getSendRecordDataCount($agent_id = null)
    {
        $data = [];
        for ($i=12;$i>=1;$i--){
            $info = self::getSendRecordCountByMonth($i,$agent_id);
            $data['month'][] = $info['month'];
            $data['num'][] =  $info['num'];
        }
        return $data;
    }

    static public function addSendRecordAll($data)
    {
        return self::insertAll($data);
    }

}