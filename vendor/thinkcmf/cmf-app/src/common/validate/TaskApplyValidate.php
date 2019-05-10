<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/3
 * Time: 11:30
 */
namespace app\common\validate;

use think\Validate;

class TaskApplyValidate extends Validate
{
    protected $rule = [
        'agent_id'    => 'require',
        'sign'        => 'require',
        'product'     => 'require',
        'template'    => 'require',
        'phone'       => 'require',
        'send_cycle'  => 'require',
        'start_time'  => 'require',
        'end_time'    => 'require',
        'send_time'   => 'require|check_send_time',
    ];

    protected $message = [
        'agent_id.require'                  => '请指定代理客户',
        'sign.require'                      => '签名不能为空',
        'product.require'                   => '类别不能为空',
        'template.require'                  => '模板不能为空',
        'phone.require'                     => '通讯录不能为空',
        'start_time.require'                => '任务开始时间不能为空',
        'end_time.require'                  => '任务结束时间不能为空',
        'send_cycle.require'                => '循环周期不能为空',
        'send_time.require'                 => '循环周期的发送时间不能为空',
        'send_time.check_send_time'         => '发送时间不能小于当前时间',
    ];


    protected function check_send_time($send_time){
        if(empty($send_time) || strtotime($send_time) >= time()){
            return true;
        }
        return false;
    }
    



    protected $scene = [
        'add'     => ['agent_id','sign', 'product', 'template', 'phone'],
        'loopAdd' => ['agent_id','sign', 'product', 'template', 'phone','start_time','end_time','send_cycle','send_time'],
    ];

}