<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/2
 * Time: 17:11
 */

namespace app\common\model;

use think\Db;
use think\Model;
use think\Exception;

class CycleTaskModel extends Model
{
    const STATUS_DISABLE    = 0; //停用
    const STATUS_NORMAL     = 1; //启用

    const IS_DEL_NO         = 0;    //  未删除
    const IS_DEL_YES        = 1;    //  已删除

//    const SEND_CYCLE_YEAR   = 1;    //  每年
    const SEND_CYCLE_MOTNTH = 1;    //  每月
    const SEND_CYCLE_WEEK   = 2;    //  每周
    const SEND_CYCLE_DAY    = 3;    //  每日


    /**
     * 获取循环任务列表数据
     * @param null $keyword         //搜索关键词
     * @param null $product_id      //搜索产品ID
     * @param null $is_del          //筛选是否删除
     * @param null $status          //状态筛选
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
    static public function getCycleTaskListData($keyword=null,$product_id=null,$is_del=null,$status=null,$start_time=null,$end_time=null,$agent_id = null,$page=1,$limit=10)
    {
        $where = [];
        if(empty($keyword) === false){
            $keywords = trim($keyword);
            $where[] =['t.name|s.sign','like',"%$keywords%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['c.status','=',$status];
        }
        if(empty($is_del) === false || $is_del === 0 || $is_del === '0'){
            $where[] = ['c.is_del','=',$is_del];
        }
        if(empty($product_id) === false){
            $where[] = ['t.product_id','=',$product_id];
        }
        if(empty($agent_id) === false){
            $where[] = ['c.agent_id','=',$agent_id];
        }
        if(empty($start_time) === false && empty($end_time) === false){
            $start_time= formatting_time($start_time);
            $end_time = formatting_time($end_time);
            if(empty($start_time) === false && empty($end_time) === false){
                $where[] = ['c.create_at','between',[$start_time,$end_time]];
            }
        }
        $list = self::alias('c')
            ->leftJoin('sign s','c.sign_id = s.id')
            ->leftJoin('template t','c.template_id = t.id')
            ->leftJoin('product p','t.product_id = p.id')
            ->where($where)
            ->limit(($page-1)*$limit,$limit)
            ->field(['c.id','s.sign','t.name','c.status','c.create_at','c.start_time','c.end_time','c.send_time','c.send_cycle','c.is_del','c.agent_id','p.name as product_name','c.send_num'])
            ->order('c.id desc')
            ->select();
        foreach ($list as $key=>$value){
            $list[$key]['send_time'] = self::cycleSendTime($value['send_cycle'],$value['send_time']);
        }
        return $list;
    }

    /**
     * 获取循环任务详情
     * @param $cycle_id
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getCycleInfo($cycle_id)
    {
        $info = self::alias('c')
            ->leftJoin('sign s','c.sign_id = s.id')
            ->leftJoin('template t','c.template_id = t.id')
            ->leftJoin('product p','t.product_id = p.id')
            ->where('c.id',$cycle_id)
            ->field(['c.id','s.sign','t.name','c.status','c.create_at','c.start_time','c.end_time','c.send_time','c.send_cycle','c.is_del','c.content','c.agent_id','c.phone','p.name as product_name'])
            ->order('c.id desc')
            ->find();
        $info['company'] = AgentModel::getAgentCompanyById($info['agent_id']);
        $info['send_time'] = self::cycleSendTime($info['send_cycle'],$info['send_time']);
        return $info;
    }

    static public function cycleSendTime($cycle,$date)
    {
        $weekarray=array("日","一","二","三","四","五","六");
        switch ($cycle){
//            case self::SEND_CYCLE_YEAR:
//                return date('m-d H:i:s',strtotime($date));
            case self::SEND_CYCLE_MOTNTH:
                return date('d日 H:i:s',strtotime($date));
            case self::SEND_CYCLE_WEEK:
                $time = strtotime($date);
                return "星期".$weekarray[date("w",$time)].' '.date("H:i:s",$time);
            case self::SEND_CYCLE_DAY:
                return date('H:i:s',strtotime($date));
            default:
                return '错误';
        }
    }

    /**
     * 获取循环任务条数
     * @param null $keyword         //搜索关键词
     * @param null $product_id      //搜索产品ID
     * @param null $is_del          //筛选是否删除
     * @param null $status          //状态筛选
     * @param null $start_time      //筛选 创建时间 （起始时间筛选）
     * @param null $end_time        //筛选 创建时间 （结束时间筛选）
     * @param null $agent_id        // 代理客户ID
     * @return float|string
     */
    static public function getCycleTaskListCount($keyword=null,$product_id=null,$is_del=null,$status=null,$start_time=null,$end_time=null,$agent_id = null)
    {
        $where = [];
        if(empty($keyword) === false){
            $keywords = trim($keyword);
            $where[] =['t.name|s.sign','like',"%$keywords%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['c.status','=',$status];
        }
        if(empty($is_del) === false || $is_del === 0 || $is_del === '0'){
            $where[] = ['c.is_del','=',$is_del];
        }
        if(empty($product_id) === false){
            $where[] = ['t.product_id','=',$product_id];
        }
        if(empty($agent_id) === false){
            $where[] = ['c.agent_id','=',$agent_id];
        }
        if(empty($start_time) === false && empty($end_time) === false){
            $start_time= formatting_time($start_time);
            $end_time = formatting_time($end_time);
            if(empty($start_time) === false && empty($end_time) === false){
                $where[] = ['c.create_at','between',[$start_time,$end_time]];
            }
        }

        $count = self::alias('c')
            ->leftJoin('sign s','c.sign_id = s.id')
            ->leftJoin('template t','c.template_id = t.id')
            ->leftJoin('product p','t.product_id = p.id')
            ->where($where)
            ->count();
        return $count;
    }


    /**
     * 变更循环任务状态
     * @param $id       //循环任务ID
     * @return mixed
     * @throws Exception
     */
    static public function toDisable($id)
    {
        $status = self::getCycleStatusById($id);
        $new_status = $status === 1?0:1;
        $update = self::updateCycleStatus($id,$new_status);
        return self::getCycleStatusById($id);
    }




    /**
     * 获取循环任务状态
     * @param $id       // 循环任务ID
     * @return mixed
     */
    static public function getCycleStatusById($id)
    {
        return self::where('id',$id)->value('status');
    }


    /**
     * 变更循环任务状态
     * @param $id       //任务ID
     * @param $status   //任务状态
     * @return CycleTaskModel
     */
    static public function updateCycleStatus($id,$status)
    {
        return self::update(['id'=>$id,'status'=>$status]);
    }
    
    /**
     * 添加循环任务
     * @param $agent_id         //代理客户ID
     * @param $sign_id          //签名ID
     * @param $template_id      //模板ID
     * @param $send_cycle       //循环周期
     * @param $send_time        //发送时间
     * @param $start_time       //循环开始时间
     * @param $end_time         //循环结束时间
     * @param $phone            //电话
     * @param $content          //发送内容
     * @return int|string
     */
    static public function addCycleData($agent_id,$sign_id,$template_id,$send_cycle,$send_time,$start_time,$end_time,$phone,$content)
    {
        return self::insertGetId([
            'phone'         => $phone,
            'content'       => $content,
            'sign_id'       => $sign_id,
            'agent_id'      => $agent_id,
            'end_time'      => $end_time,
            'send_time'     => $send_time,
            'start_time'    => $start_time,
            'send_cycle'    => $send_cycle,
            'template_id'   => $template_id,
            'is_del'        => self::IS_DEL_NO,
            'status'        => self::STATUS_NORMAL,
            'create_at'     => date("Y-m-d H:i:s",time()),
            'phone_num'     => count(explode(',',$phone)),
            'send_num'      => ceil(mb_strlen($content)/TaskModel::MB_STR_LEN),
        ]);
    }

    /**
     * 检查时间循环范围
     * @param $send_time
     * @param $start_time
     * @param $end_time
     * @throws Exception
     */
    static public function checkCycleTime($send_time,$start_time,$end_time)
    {
        if(strtotime($send_time) < strtotime($start_time) || strtotime($send_time) > strtotime($end_time) ){
            throw new Exception('当前发送时间不在任务生效范围内！');
        }
    }


    /**
     * 获取当天生效循环任务
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getNowCycleTask()
    {
        $sql = "( send_cycle = ".self::SEND_CYCLE_MOTNTH." and ( ( DATE_FORMAT ( send_time,'%d') >= DATE_FORMAT(LAST_DAY(now()) ,'%d') ) or ( DATE_FORMAT ( send_time,'%d' ) = DATE_FORMAT ( now(),'%d' ) ) ) ) or ( send_cycle = ".self::SEND_CYCLE_WEEK." and DATE_FORMAT ( send_time,'%w' ) = DATE_FORMAT( now(),'%w' ) ) or (send_cycle = ".self::SEND_CYCLE_DAY." )";
        return self::where('is_del',self::IS_DEL_NO)
            ->where('status',self::STATUS_NORMAL)
            ->where('start_time','exp',Db::raw('<= now()'))
            ->where('end_time','exp',Db::raw('>= now()'))
            ->where($sql)
            ->field(['id','agent_id','sign_id','template_id','send_time','phone','content','send_num','phone_num'])
            ->select();
    }

    /**
     * 添加普通任务
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function addTask()
    {
        $cycle_list = self::getNowCycleTask();
        foreach ($cycle_list as $key=>$value){
            $task = TaskModel::getNowCycleTask($value['id']);
            $price_info = self::getTotalPriceByTaskId($value['id']);
            $money_bool = AgentModel::getAgentMoney($price_info['agent_id'],$price_info['total_price']);
            if(empty($task) && $money_bool===true){
                $send_time = date("H:i:s",strtotime($value['send_time']));
                TaskModel::newAddTask($value['agent_id'],$value['sign_id'],$value['template_id'],date("Y-m-d $send_time",time()),$value['phone'],TaskModel::TYPE_CYCLE,$value['content'],$value['id']);
            }
        }
    }

    static public function delTask($id)
    {
        return self::update(['id'=>$id,'is_del'=>self::IS_DEL_YES]);
    }


    /**
     * 通过任务ID获取总价格
     * @param $task_id      //任务ID
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getTotalPriceByTaskId($task_id)
    {
        $price = self::alias('c')
            ->leftJoin('template t','c.template_id = t.id')
            ->leftJoin('product p','t.product_id = p.id')
            ->where('c.id',$task_id)
            ->field(['c.send_num','c.phone_num','p.price','c.agent_id'])
            ->find();
        $total_price = bcmul($price['send_num']*$price['phone_num'],$price['price']);
        return ['total_price'=>$total_price,'agent_id'=>$price['agent_id']];
    }


    /**
     * 判断当前时间是否生成任务
     * @param $send_cycle       //任务周期
     * @param $send_time        //预定发送时间
     * @param $start_time       //任务开始时间
     * @param $end_time         //任务结束时间
     * @return bool
     */
    static public function getNowDate($send_cycle,$send_time,$start_time,$end_time)
    {
        $time = date('H:i:s',strtotime($send_time));
        if(strtotime($start_time) <= strtotime(date("Y-m-d $time",time())) && strtotime($end_time) > strtotime(date("Y-m-d $time",time()))){
            switch ($send_cycle){
                case self::SEND_CYCLE_MOTNTH:
                    if(date('d',time()) === date('d',strtotime($send_time)) && strtotime(date('H:i:s',time())) <= strtotime($time)  ){
                        return true;
                    }
                    return false;
                case self::SEND_CYCLE_WEEK:
                    if(date('w',time()) === date('w',strtotime($send_time)) && strtotime(date('H:i:s',time())) <= strtotime($time)  ){
                        return true;
                    }
                    return false;
                case self::SEND_CYCLE_DAY:
                    if(strtotime(date('H:i:s',time())) <= strtotime($time)){
                        return true;
                    }
                    return false;
                default:
                    return false;
            }

        }
        return false;
    }
    
}