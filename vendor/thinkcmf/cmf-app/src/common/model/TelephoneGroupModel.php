<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/8
 * Time: 18:08
 */

namespace app\common\model;


use think\Model;

class TelephoneGroupModel extends Model
{

    static public function getGroupList($agent_id)
    {
        return self::where('agent_id',$agent_id)->select();
    }
    /**
     * 添加分组数据
     * @param null group       新建分组数据
     */
    static public function addGroup($group)
    {
        if (self::match($group['name'])) {
            return ['status'=>1 , 'massage' => '禁止使用非法字符'];
        }

        if (count(self::same($group['name'])) !== 0) {
            return ['status' => 1, 'massage' => '组名已存在'];
        }

        $data = [
            'name' => $group['name'],
            'agent_id' =>$group['id']
        ];
        $result = self::insert($data);
        if ($result) {
            return ['status' => 0, 'massage' => '添加成功'];
        }
    }
    /**
     * 判断非法字符
     */
    static public function match($group)
    {
        $regex = "/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
        return(preg_match($regex,$group));
    }
    /**
     * @param $group 组名
     * 判断是否已存在
     */
    static public function same($group){
        return self::where('name' , $group)->select();
    }
    static public function name($id){
        return self::where('id' , $id)->select();
    }
}