<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/16 0016
 * Time: 14:09
 */

namespace app\admin\controller;


use app\common\model\AgentModel;
use app\common\model\ChargeRecordModel;
use app\common\model\CycleTaskModel;
use app\common\model\SendRecordModel;
use app\common\model\TaskModel;

use cmf\controller\BaseController;
use cmf\controller\HomeBaseController;
use think\cache\driver\Redis;
use think\Db;
use think\Exception;

class TimeController extends BaseController
{

    

//
//    public function getSendTime()
//    {
//        $time = date('Y-m-d H:i');
//        try{
//            $taskIdList = TaskModel::getSendTime($time);
//            if(!$taskIdList->isEmpty()){
//                //加入消息队列
//                $redis = new Redis();
//                foreach ($taskIdList as $key => $val){
//                    $redis->Lpush('task_id',$val['id']);
//                }
//                echo "添加成功";
//            }else{
//                echo "没有符合的数据";
//            }
//        }catch (Exception $exception){
//            $this->error($exception->getMessage());
//        }
//
//    }

//    public function getTaskId()
//    {
//        $t1 = microtime(true);
//        set_time_limit(0);
//        $redis = new Redis();
//        try{
//            while (true){
//                $task_id = $redis->lpop('task_id');
//                $redis->lpush('task_id',$task_id);
//                if (!empty($task_id)) {
//                    $info = TaskModel::getSendContent($task_id);
//                    if($info->isEmpty()){
//                        break;
//                    }
//                    $phoneNum   = $redis->Llen('task_id:'.$task_id);
//                    $msgDataNum = $redis->Llen("msgData:".$task_id);
//                    if($phoneNum == 0 && $msgDataNum == 0){
//                        $phoneList = explode(',',$info['phone']);
//                        foreach ($phoneList as $key => $val){
//                            $redis->lpush('task_id:'.$task_id,$val);
//                        }
//                        foreach ($phoneList as $key => $val){
////                            $res = $send->sendMsg($info['content'],$val,$info['code']);
//                            $res = "1,20190419";
//                            $statusList = explode(',',$res);
//                            $redis->lpush("msgData:".$task_id,['phone'=>$val,'status'=>$statusList[0],'num'=>1,'reason'=>"",'task_id'=>$task_id,'agent_id'=>$info['agent_id'],'order_no'=>isset($statusList[1])?$statusList[1]:'','send_time'=>date('Y-m-d H:i:s',time()),'product_id'=>$info['product_id'],'money'=>self::PRICE]);
//                            if($statusList[0] == 1){
//                                $redis->incr("SuccessMsgDataNum:".$task_id);
//                            }
//                            $redis->Lrem('task_id:'.$task_id,$val,0);
//                        }
//                    }else if($phoneNum != 0 && $msgDataNum == 0){
//                        for($i = 1; $i <= $phoneNum; $i++){
//                            $phone = $redis->lpop('task_id:'.$task_id);
//                            $redis->lpush('task_id:'.$task_id,$phone);
////                            $res = $send->sendMsg($info['content'],$phone,$info['code']);
//                            $res = "1,20190419";
//                            $statusList = explode(',',$res);
//                            $redis->lpush("msgData:".$task_id,['phone'=>$phone,'status'=>$statusList[0],'num'=>1,'reason'=>"",'task_id'=>$task_id,'agent_id'=>$info['agent_id'],'order_no'=>isset($statusList[1])?$statusList[1]:'','send_time'=>date('Y-m-d H:i:s',time()),'product_id'=>$info['product_id'],'money'=>self::PRICE]);
//                            if($statusList[0] == 1){
//                                $redis->incr("SuccessMsgDataNum:".$task_id);
//                            }
//                            $redis->Lrem('task_id:'.$task_id,$phone,0);
//                        }
//                    }
//
//
//                    $num = ceil($redis->Llen("msgData:".$task_id)/1000);
//                    for($i=1;$i<=$num;$i++){
//                        $msgData = $redis->lrange("msgData:".$task_id,0,999);   //3.32
//                        SendRecordModel::addSendRecordAll($msgData);
//                        $redis->ltrim("msgData:".$task_id,1000,-1);
//                    }
//                    $successNum = $redis->get("SuccessMsgDataNum:".$task_id);
//                    Db::startTrans();
//                    $priceRes = AgentModel::setDecMoney($info['agent_id'],$successNum*$info['price']);     //成功扣费
//                    if($priceRes == 1){
//                        ChargeRecordModel::addChargeRecord($task_id,$successNum,$info['price'],$info['agent_id'],$info['product_id']);
//                        $redis->del("SuccessMsgDataNum:".$task_id);
//                    }
//                    Db::commit();
//                    $redis->Lrem('task_id',$task_id,0);
//
//
//                }else{
//                    echo "队列里面已经没有数据了";
//                    break;
//                }
//            }
//        }catch (Exception $exception){
//            Db::rollback();
//            $this->error($exception->getMessage());
//        }
//    }


    public function sendTaskMassage()
    {
        $t1 = microtime(true);
        set_time_limit(0);
        try{
            $task = new TaskModel();
            $task->getSendTimeTask();
            $task->getTaskId();
            $t2 = microtime(true);
            echo '耗时'.round($t2-$t1,3).'秒<br>';
        }catch (Exception $exception){
            Db::rollback();
        }
    }

    public function addCycleTask()
    {
        try{
            CycleTaskModel::addTask();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }
    }
}