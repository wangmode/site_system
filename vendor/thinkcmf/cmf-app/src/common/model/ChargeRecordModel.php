<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/11
 * Time: 9:30
 */

namespace app\common\model;


use think\Model;

class ChargeRecordModel extends Model
{

    /**
     * 获取消息记录列表数据
     * @param $create_at            // 筛选日期
     * @param $agent_id             //代理ID
     * @param null $product_id      //产品ID
     * @param null $keywords        //索引关键词
     * @param int $page             //页码
     * @param int $limit            //每页条数
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getSendRecordData($create_at,$agent_id,$product_id=null,$keywords=null,$page=1,$limit=10)
    {
        $where = [];
        if(empty($keyword) === false){
            $keywords = trim($keyword);
            $where[] =['t.name|s.sign','like',"%$keywords%"];
        }
        if(empty($product_id) === false){
            $where[] = ['t.product_id','=',$product_id];
        }
        $list = self::alias('cr')
            ->leftJoin('task ta','cr.task_id = ta.id')
            ->leftJoin('sign s','ta.sign_id = s.id')
            ->leftJoin('template t','ta.template_id = t.id')
            ->leftJoin('product p','cr.product_id = p.id')
            ->where($where)
            ->where('cr.agent_id',$agent_id)
            ->where('cr.create_at','between',["$create_at 00:00:00","$create_at 23:59:59"])
            ->limit(($page-1)*$limit,$limit)
            ->field(['s.sign','t.name','cr.create_at','cr.num','cr.num','cr.total_price','cr.task_id','ta.content','p.name as product_name'])
            ->order('cr.id desc')
            ->select();
        return $list;
    }

    /**
     * 获取消息记录列表数据
     * @param $create_at            // 筛选日期
     * @param $agent_id             //代理ID
     * @param null $product_id      //产品ID
     * @param null $keywords        //索引关键词
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getSendRecordCount($create_at,$agent_id,$product_id=null,$keywords=null)
    {
        $where = [];
        if(empty($keyword) === false){
            $keywords = trim($keywords);
            $where[] =['t.name|s.sign','like',"%$keywords%"];
        }
        if(empty($product_id) === false){
            $where[] = ['t.product_id','=',$product_id];
        }
        $create_at = date("Y-m-d H:i:s",strtotime($create_at));
        $count = self::alias('cr')
            ->leftJoin('task ta','cr.task_id = ta.id')
            ->leftJoin('sign s','ta.sign_id = s.id')
            ->leftJoin('template t','ta.template_id = t.id')
            ->leftJoin('product p','cr.product_id = p.id')
            ->where($where)
            ->where('cr.create_at','between',["$create_at 00:00:00","$create_at 23:59:59"])
            ->where('cr.agent_id',$agent_id)
            ->count();
        return $count;
    }

    /**
     * 获取导出发送记录数据
     * @param $keyword          //索引关键词
     * @param $product_id       //产品ID
     * @param $send_time        //发送日期
     * @param $start_time       //筛选开始时间
     * @param $end_time         //筛选结束时间
     * @param $agent_id         //代理客户ID
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getExplodeChargeRecordList($keyword=null,$product_id=null,$send_time=null,$start_time=null,$end_time=null,$agent_id=null)
    {
        $where = [];
        if(empty($keyword) === false){
            $keywords = trim($keyword);
            $where[] =['t.name|s.sign','like',"%$keywords%"];
        }
        if(empty($product_id) === false){
            $where[] = ['t.product_id','=',$product_id];
        }
        if(empty($agent_id) === false){
            $where[] = ['cr.agent_id','=',$agent_id];
        }
        if(empty($send_time) === false){
            $start_time = "$send_time 00:00:00";
            $end_time   = "$send_time 23:59:59";
        }
        if(empty($start_time) === false && empty($end_time) === false){
            $start_time= formatting_time($start_time);
            $end_time = formatting_time($end_time);
            if(empty($start_time) === false && empty($end_time) === false){
                $where[] = ['cr.create_at','between',[$start_time,$end_time]];
            }
        }
        $list = self::alias('cr')
            ->leftJoin('task ta','cr.task_id = ta.id')
            ->leftJoin('sign s','ta.sign_id = s.id')
            ->leftJoin('template t','ta.template_id = t.id')
            ->leftJoin('product p','cr.product_id = p.id')
            ->where($where)
            ->where('cr.agent_id',$agent_id)
            ->field(['cr.id','s.sign','t.name','cr.create_at','cr.num','cr.total_price','ta.content','p.name as product_name'])
            ->select();
        return $list;
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
    static public function getSendRecordListData($start_time=null,$end_time=null,$agent_id = null,$page=1,$limit=10)
    {
        $where = [];
        if(empty($agent_id) === false){
            $where[] = ['agent_id','=',$agent_id];
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
            ->field([" DATE_FORMAT (create_at,'%Y-%m-%d') as date",'sum(num) as num','sum(total_price) as money'])
            ->order('date desc')
            ->group('date')
            ->select();
        return $list;
    }


    /**
     * 获取消息记录列表数据条数
     * @param null $start_time      //筛选 创建时间 （起始时间筛选）
     * @param null $end_time        //筛选 创建时间 （结束时间筛选）
     * @param null $agent_id        //代理客户ID
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getSendRecordListCount($start_time=null,$end_time=null,$agent_id = null,$page=1,$limit=10)
    {
        $where = [];
        if(empty($agent_id) === false){
            $where[] = ['agent_id','=',$agent_id];
        }
        if(empty($start_time) === false && empty($end_time) === false){
            $start_time= formatting_time($start_time);
            $end_time = formatting_time($end_time);
            if(empty($start_time) === false && empty($end_time) === false){
                $where[] = ['create_at','between',[$start_time,$end_time]];
            }
        }
        $list = self::where($where)
            ->field(["DATE_FORMAT (create_at,'%Y-%m-%d') as date"])
            ->group('date')
            ->select();
        return $list;
    }



    /**
     * 获取代理商总扣费金额
     * @param $agent_list   //代理商列表
     * @return mixed
     */
    static public function getAgentAmount($agent_list)
    {
        foreach ($agent_list as $key=>$value){
            $agent_list[$key]['amount'] =  self::getSumAmountByAgentId($value['id']);
        }
        return $agent_list;
    }

    /**
     * 获取代理商总扣费金额
     * @param $agent_id     //代理客户ID
     * @return mixed
     */
    static public function getSumAmountByAgentId($agent_id)
    {
        return self::where('agent_id',$agent_id)->value('convert(ifnull(sum(total_price),0), decimal(12,2))');
    }


    /**
     * 添加扣费记录
     * @param $task_id      //任务ID
     * @param $record_id    //消息记录ID
     * @param $num          // 信息数量
     * @param $price        // 信息单价
     * @param $agent_id     //代理客户ID
     * @param $product_id   //产品ID
     * @return int|string
     */
    static public function addChargeRecord($task_id,$num,$price,$agent_id,$product_id)
    {
        return self::insert([
            'num'           => $num,
            'price'         => $price,
            'task_id'       => $task_id,
            'agent_id'      => $agent_id,
            'product_id'    => $product_id,
            'total_price'   => bcmul($num,$price,2),
            'create_at'     => date('Y-m-d H:i:s',time())
        ]);
    }


}