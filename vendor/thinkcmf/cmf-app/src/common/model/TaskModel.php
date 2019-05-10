<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/2
 * Time: 11:34
 */

namespace app\common\model;


use think\cache\driver\Redis;
use think\Db;
use think\Exception;
use think\facade\Env;
use think\Model;

class TaskModel extends Model
{
    const TYPE_ORDINARY     = 1;    //普通任务
    const TYPE_CYCLE        = 2;    //循环任务

    const STATUS_NO         = 0;    //未执行
    const STATUS_YES        = 1;    //已执行

    const IS_DEL_NO         = 0;    //  未删除
    const IS_DEL_YES        = 1;    //  已删除

    const MB_STR_LEN        = 67;   //单条字符长度

    private $redis;


    public function __construct($data = [])
    {
        $this->redis = new Redis();
        parent::__construct($data);
    }


    /**
     * 新建任务
     * @param $agent_id         //代理客户ID
     * @param $sign_id          //签名ID
     * @param $template_id      //模板ID
     * @param $scheduled_time   //预计发送时间
     * @param $phone            //电话号码
     * @param $content          //发送内容
     * @param $type             //任务类别
     * @param $send_num         //任务发送数量
     * @param $phone_num        //电话数量
     * @param int $cycle_id     //循环任务ID
     */
    static public function addTask($agent_id,$sign_id,$template_id,$scheduled_time,$phone,$content,$type,$send_num,$phone_num,$cycle_id)
    {
        return self::insertGetId([
            'type'              => $type,
            'phone'             => $phone,
            'sign_id'           => $sign_id,
            'content'           => $content,
            'send_num'          => $send_num,
            'cycle_id'          => $cycle_id,
            'agent_id'          => $agent_id,
            'phone_num'         => $phone_num,
            'template_id'       => $template_id,
            'is_del'            => self::IS_DEL_NO,
            'status'            => self::STATUS_NO,
            'create_at'         => date("Y-m-d H:i:s",time()),
            'scheduled_time'    => empty($scheduled_time)?date("Y-m-d H:i:s",time()):$scheduled_time
        ]);
    }


    /**
     * 新建发送任务
     * @param $agent_id         //代理客户ID
     * @param $sign_id          //签名ID
     * @param $template_id      //模板ID
     * @param $scheduled_time   //预定发送时间
     * @param $phone            //电话号码
     * @param $type             //任务类别
     * @param $content          //发送内容
     * @param int $cycle_id     //循环任务ID
     */
    static public function newAddTask($agent_id,$sign_id,$template_id,$scheduled_time,$phone,$type,$content,$cycle_id = 0)
    {
        $phone_num = count(explode(',',$phone));
        $send_num = ceil(mb_strlen($content)/self::MB_STR_LEN);
        return self::addTask($agent_id,$sign_id,$template_id,$scheduled_time,$phone,$content,$type,$send_num,$phone_num,$cycle_id);
    }


    

    /**
     * 获取当前循环普通任务
     * @param $cycle_id
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getNowCycleTask($cycle_id)
    {
        return self::where('cycle_id',$cycle_id)
            ->where('type',self::TYPE_CYCLE)
            ->where('to_days ( scheduled_time ) = to_days(now()) ')
            ->field('id')
            ->find();
    }

    /**
     * 验证用户发送返回发送内容
     * @param $agent_id         //代理ID
     * @param $sign_id          //签名ID
     * @param $template_id      //模板ID
     * @param $phone            //电话
     * @return mixed|string
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function checkTaskInfo($agent_id,$sign_id,$template_id,$phone)
    {
        $temCon = TemplateModel::getTemplateById($agent_id,$template_id);
        $varList = self::getVariableNum($temCon);
        if(!empty($varList['data'])){
            foreach ($varList['data'] as $key => $val){
                if(empty($data['variable'][$val])){
                    throw new Exception($val."变量不能为空!");
                }
            }
            $content = self::getContent($agent_id,$sign_id,$template_id,$data['variable']);
        }else{
            $content = $temCon;
        }
        if(ceil(mb_strlen($content,"utf-8")>=60)){
            throw new Exception('模板内容总长度字数不能大于60字！');
        }
        $sensitive = sensitive_filter($content);
        if($sensitive !== true){
            throw new Exception($sensitive.'为敏感词！');
        }
        $phone_num = count(explode(',',$phone));
        if($phone_num > 2000){
            throw new Exception('电话数量不得大于2000个！');
        }
        $price = TemplateModel::getProductPriceByTemplateId($template_id);
        $total_price = bcmul($phone_num,$price);
        $money_bool = AgentModel::getAgentMoney($agent_id,$total_price);
        if($money_bool === false){
            throw new Exception('当前用户余额不足！');
        }
        return $content;
    }

    /**
     * 逻辑处理
     * @param $content
     * @return \think\response\Json
     */
    static public function getVariableNum($content)
    {
        $strPattern = "/(?<={)[^}]+/";
        $arrMatches = [];
        preg_match_all($strPattern, $content, $arrMatches);
        $num = count($arrMatches[0]);
        if($num > 5){
            throw new Exception("模板中最多支持5个变量，请重新选择！");
        }
        return $arrMatches[0];
    }





    /**
     * 组装签名返回内容
     * @param $agent_id     //代理客户ID
     * @param $sign_id      // 签名ID
     * @param $template_id  //模板ID
     * @param $param        //参数
     * @return string
     */
    static public function getContent($agent_id,$sign_id,$template_id,$param)
    {
        $sign = SignModel::getSignById($agent_id,$sign_id);
        $template =TemplateModel::getTemplateById($agent_id,$template_id);
        foreach ($param as $key=>$value){
            $template = str_replace('${'.$key.'}',$value,$template);
        }
        $content = $template.'【'.$sign.'】';
        return $content;
    }


    /**
     * 获取循环发送任务数据
     * @param $cycle_id         //循环ID
     * @param null $start_time  //起始时间
     * @param null $end_time    //结束时间
     * @param int $page         //页码
     * @param int $limit        //每页条数
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getCycleTaskData($cycle_id,$start_time=null,$end_time=null,$page=1,$limit=10)
    {
        $where = [];
        if(empty($start_time) === false && empty($end_time) === false){
            $start_time= formatting_time($start_time);
            $end_time = formatting_time($end_time);
            if(empty($start_time) === false && empty($end_time) === false){
                $where[] = ['ta.create_at','between',[$start_time,$end_time]];
            }
        }
        $list = self::alias('ta')
            ->leftJoin('sign s','ta.sign_id = s.id')
            ->leftJoin('template t','ta.template_id = t.id')
            ->leftJoin('product p','t.product_id = p.id')
            ->where('ta.cycle_id',$cycle_id)
            ->where('ta.type',self::TYPE_CYCLE)
            ->where($where)
            ->limit(($page-1)*$limit,$limit)
            ->field(['ta.id','s.sign','t.name','ta.status','ta.create_at','ta.scheduled_time','ta.is_del','ta.agent_id','p.name as product_name'])
            ->order('ta.id desc')
            ->select();
        return $list;
    }


    /**
     * 获取循环发送任务条数
     * @param $cycle_id         //循环ID
     * @param null $start_time  //起始时间
     * @param null $end_time    //结束时间
     * @return float|string
     */
    static public function getCycleTaskCount($cycle_id,$start_time=null,$end_time=null)
    {
        $where = [];
        if(empty($start_time) === false && empty($end_time) === false){
            $start_time= formatting_time($start_time);
            $end_time = formatting_time($end_time);
            if(empty($start_time) === false && empty($end_time) === false){
                $where[] = ['ta.create_at','between',[$start_time,$end_time]];
            }
        }
        $count = self::alias('ta')
            ->leftJoin('sign s','ta.sign_id = s.id')
            ->leftJoin('template t','ta.template_id = t.id')
            ->leftJoin('product p','t.product_id = p.id')
            ->where('ta.cycle_id',$cycle_id)
            ->where('type',self::TYPE_CYCLE)
            ->where($where)
            ->count();
        return $count;
    }



    /**
     * 获取普通任务列表数据
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
    static public function getTaskListData($keyword=null,$product_id=null,$is_del=null,$status=null,$start_time=null,$end_time=null,$agent_id = null,$page=1,$limit=10)
    {
        $where = [];
        if(empty($keyword) === false){
            $keywords = trim($keyword);
            $where[] =['t.name|s.sign','like',"%$keywords%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['ta.status','=',$status];
        }
        if(empty($is_del) === false || $is_del === 0 || $is_del === '0'){
            $where[] = ['ta.is_del','=',$is_del];
        }
        if(empty($product_id) === false){
            $where[] = ['t.product_id','=',$product_id];
        }
        if(empty($agent_id) === false){
            $where[] = ['ta.agent_id','=',$agent_id];
        }
        if(empty($start_time) === false && empty($end_time) === false){
            $start_time= formatting_time($start_time);
            $end_time = formatting_time($end_time);
            if(empty($start_time) === false && empty($end_time) === false){
                $where[] = ['ta.create_at','between',[$start_time,$end_time]];
            }
        }
        $list = self::alias('ta')
            ->leftJoin('sign s','ta.sign_id = s.id')
            ->leftJoin('template t','ta.template_id = t.id')
            ->leftJoin('product p','t.product_id = p.id')
            ->where($where)
            ->where('type',self::TYPE_ORDINARY)
            ->limit(($page-1)*$limit,$limit)
            ->field(['ta.id','s.sign','t.name','ta.status','ta.create_at','ta.scheduled_time','ta.is_del','ta.agent_id','p.name as product_name','ta.send_num'])
            ->order('ta.id desc')
            ->select();
        return $list;
    }


    /**
     * 获取普通任务列表总条数
     * @param null $keyword         //搜索关键词
     * @param null $product_id      //搜索产品ID
     * @param null $is_del          //筛选是否删除
     * @param null $status          //状态筛选
     * @param null $start_time      //筛选 创建时间 （起始时间筛选）
     * @param null $end_time        //筛选 创建时间 （结束时间筛选）
     * @param null $agent_id        // 代理客户ID
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getTaskListCount($keyword=null,$product_id=null,$is_del=null,$status=null,$start_time=null,$end_time=null,$agent_id = null)
    {
        $where = [];
        if(empty($keyword) === false){
            $keywords = trim($keyword);
            $where[] =['t.name|s.sign','like',"%$keywords%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['ta.status','=',$status];
        }
        if(empty($is_del) === false || $is_del === 0 || $is_del === '0'){
            $where[] = ['ta.is_del','=',$is_del];
        }
        if(empty($product_id) === false){
            $where[] = ['t.product_id','=',$product_id];
        }
        if(empty($agent_id) === false){
            $where[] = ['ta.agent_id','=',$agent_id];
        }
        if(empty($start_time) === false && empty($end_time) === false){
            $start_time= formatting_time($start_time);
            $end_time = formatting_time($end_time);
            if(empty($start_time) === false && empty($end_time) === false){
                $where[] = ['ta.create_at','between',[$start_time,$end_time]];
            }
        }
        $count = self::alias('ta')
            ->leftJoin('sign s','ta.sign_id = s.id')
            ->leftJoin('template t','ta.template_id = t.id')
            ->leftJoin('product p','t.product_id = p.id')
            ->where($where)
            ->where('type',self::TYPE_ORDINARY)
            ->count();
        return $count;
    }

    /**
     * 获取任务详情
     * @param $task_id
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getTaskInfo($task_id)
    {
        $info = self::alias('ta')
            ->leftJoin('sign s','ta.sign_id = s.id')
            ->leftJoin('template t','ta.template_id = t.id')
            ->leftJoin('product p','t.product_id = p.id')
            ->where('ta.id',$task_id)
            ->field(['ta.id','s.sign','t.name','ta.status','ta.create_at','ta.scheduled_time','ta.is_del','ta.content','ta.agent_id','ta.phone','p.name as product_name'])
            ->order('ta.id desc')
            ->find();
        $info['company'] = AgentModel::getAgentCompanyById($info['agent_id']);
        return $info;
    }

    /**
     * 删除任务
     * @param $task_id
     */
    static public function delTask($task_id)
    {
        return self::update(['id'=>$task_id,'is_del'=>self::IS_DEL_YES]);
    }


    /**
     * 发送完成修改任务状态
     * @param $task_id      //任务ID
     * @return TaskModel
     */
    static public function updateTaskStatus($task_id)
    {
        return self::update([
            'id'        =>$task_id,
            'status'    =>self::STATUS_YES,
            'send_time' =>date('Y-m-d H:i:s',time())
        ]);
    }






    /**
     * 通过cycleID 获取循环任务详情
     * @param $cycle_id
     * @param $page
     * @param $limit
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getCycleIdData($cycle_id,$send_time,$page,$limit)
    {
        $where = [];
        if(empty($send_time) === false){
            $end_time = $send_time." 23:59:59";
            $where[] = ['ta.send_time','between',[$send_time,$end_time]];
        }
        $list = self::alias('ta')
            ->leftJoin('sign s','ta.sign_id = s.id')
            ->leftJoin('template t','ta.template_id = t.id')
            ->leftJoin('product p','t.product_id = p.id')
            ->where('ta.cycle_id',$cycle_id)
            ->where('ta.type',self::TYPE_CYCLE)
            ->where($where)
            ->limit(($page-1)*$limit,$limit)
            ->field(['ta.id','ta.send_num','s.sign','t.name','ta.status','ta.create_at','ta.send_time','ta.is_del','ta.agent_id','p.name as product_name'])
            ->order('ta.id desc')
            ->select();
        return $list;
    }

    /**
     * 通过cycle_id 获取循环任务详情条数
     * @param $cycle_id
     * @param $send_time
     * @return float|string
     */
    static public function getCycleIdCount($cycle_id,$send_time)
    {
        $where = [];
        if(empty($cycle_id) === false){
            $where[] =['cycle_id','=',$cycle_id];
        }
        if(empty($send_time) === false){
            $end_time = $send_time." 23:59:59";
            $where[] = ['send_time','between',[$send_time,$end_time]];
        }
        return self::where($where)->count();
    }


    /**
     * 每分钟查询符合发送时间的taskId
     * @param $time
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getSendTime($time)
    {
        $where = [];
        if(!empty($time)){
            $end_time = $time.":59";
            $where[] = ['scheduled_time','between',[$time,$end_time]];
        }

        return self::where($where)
            ->where(['is_del'=>self::IS_DEL_NO])
            ->where(['status'=>self::STATUS_NO])
            ->field(['id'])
            ->select();
    }

    static public function getSendTimeData($time)
    {
        $where = [];
        if(!empty($time)){
            $end_time = $time.":59";
            $where[] = ['scheduled_time','between',[$time,$end_time]];
        }
        return self::alias('ta')
            ->leftJoin('template t','ta.template_id=t.id')
            ->leftJoin('product p','t.product_id=p.id')
            ->where(['ta.is_del'=>self::IS_DEL_NO])
            ->where(['ta.status'=>self::STATUS_NO])
            ->where($where)
            ->field(['ta.id','ta.phone','ta.content','p.code'])
            ->select();
    }

    /**
     * 根据task_id 查询手机号和发送内容
     * @param $task_id
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getSendContent($task_id)
    {
//        return self::where(['id'=>$task_id])->field(['phone','content'])->find();
        return self::alias('ta')
            ->leftJoin('template t','ta.template_id=t.id')
            ->leftJoin('product p','t.product_id=p.id')
            ->where(['ta.id'=>$task_id])
            ->field(['ta.phone','ta.content','p.code','p.id as product_id','ta.send_num','p.price','ta.agent_id'])
            ->find();
    }


    /**
     * 获取当前任务添加redis
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSendTimeTask()
    {
        $taskIdList = self::getSendTime(date('Y-m-d H:i'));
        if($taskIdList->isEmpty() === false){
            //加入消息队列
            foreach ($taskIdList as $key => $val){
                $this->redis->Lpush('task_id',$val['id']);
            }
        }
    }


    /**
     *执行发送任务
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTaskId()
    {
        $task_id = $this->redis->lpop('task_id');
        while (empty($task_id) === false){
            $this->redis->lpush('task_id',$task_id);
            $info = TaskModel::getSendContent($task_id);
            if($info->isEmpty()){
                $this->redis->Lrem('task_id',$task_id,0);
                continue;
            }
            $phoneNum   = $this->redis->Llen('task_id:'.$task_id);
            $msgDataNum = $this->redis->Llen("msgData:".$task_id);
            if($phoneNum == 0 && $msgDataNum == 0){
                $phoneList = explode(',',$info['phone']);
                foreach ($phoneList as $key => $val){
                    $this->redis->lpush('task_id:'.$task_id,$val);
                }
                foreach ($phoneList as $key => $val){
                    $this->sendRedis($info['content'],$val,$info['send_num'],$info['code'],$task_id,$info['agent_id'],$info['product_id'],$info['price']);
                    $this->redis->Lrem('task_id:'.$task_id,$val,0);
                }
            }else if($phoneNum != 0 && $msgDataNum == 0){
                for($i = 1; $i <= $phoneNum; $i++){
                    $phone = $this->redis->lpop('task_id:'.$task_id);
                    $this->redis->lpush('task_id:'.$task_id,$phone);
                    $this->sendRedis($info['content'],$phone,$info['send_num'],$info['code'],$task_id,$info['agent_id'],$info['product_id'],$info['price']);
                    $this->redis->Lrem('task_id:'.$task_id,$phone,0);
                }
            }
            self::updateTaskStatus($task_id);
            $this->agentCharge($task_id, $info['agent_id'], $info['price'], $info['product_id']);
            $this->redis->Lrem('task_id',$task_id,0);
            $task_id = $this->redis->lpop('task_id');
        }
    }


    /**
     * 发送消息
     * @param $content      // 发送内容
     * @param $phone        //电话号码
     * @param $num          //发送数量
     * @param $code         //产品代码
     * @param $task_id      //任务ID
     * @param $agent_id     //客户ID
     * @param $product_id   //产品ID
     * @param $price        //产品单价
     */
    public function sendRedis($content,$phone,$num,$code,$task_id,$agent_id,$product_id,$price)
    {
//        $res = $send->sendMsg($content,$phone,$code);
        $res = "1,20190419";
        $statusList = explode(',',$res);
        $this->redis->lpush("msgData:".$task_id,['phone'=>$phone,'status'=>$statusList[0],'num'=>$num,'reason'=>"",'task_id'=>$task_id,'agent_id'=>$agent_id,'order_no'=>isset($statusList[1])?$statusList[1]:'','send_time'=>date('Y-m-d H:i:s',time()),'product_id'=>$product_id,'money'=>$price]);
        if($statusList[0] == 1){
            $this->redis->incr("SuccessMsgDataNum:".$task_id);
        }
    }


    /**
     * 扣费
     * @param $task_id      //任务ID
     * @param $agent_id     //客户ID
     * @param $price        //单价
     * @param $product_id   //产品ID
     * @throws Exception
     */
    public function agentCharge($task_id,$agent_id,$price,$product_id)
    {
        $num = ceil($this->redis->Llen("msgData:".$task_id)/1000);
        for($i=1;$i<=$num;$i++){
            $msgData = $this->redis->lrange("msgData:".$task_id,0,999);
            SendRecordModel::addSendRecordAll($msgData);
            $this->redis->ltrim("msgData:".$task_id,1000,-1);
        }
        $successNum = $this->redis->get("SuccessMsgDataNum:".$task_id);
        Db::startTrans();

        $priceRes = AgentModel::setDecMoney($agent_id,$successNum*$price);     //成功扣费
        if($priceRes == 1){
            ChargeRecordModel::addChargeRecord($task_id,$successNum,$price,$agent_id,$product_id);
            $this->redis->del("SuccessMsgDataNum:".$task_id);
        }
        Db::commit();
    }

}