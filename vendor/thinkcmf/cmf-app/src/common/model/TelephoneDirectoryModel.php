<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < tasi79810@gmail.com>
// +----------------------------------------------------------------------
namespace app\common\model;

use think\Model;
use think\Db;
class TelephoneDirectoryModel extends Model
{
    const STATUS_DISABLE = 0; //禁用
    const STATUS_NORMAL  = 1; //正常

    /**
     * 获取所有通讯录数据
     * @param null options      //分组筛选
     * @param null agent_id      //分组筛选
     * @param null telephone    //电话号码搜索
     * @param int page          //页码
     * @param int limit         //每页显示数
     */

    static public function getTeleListData($agent_id = null,$options=null,$telephone=null,$page=1,$limit=10)
    {
        $where_tel   = [];
        $where_group = [];
        if (empty($telephone) === false)
        {
            $tele = trim($telephone);
            $where_tel[] = ['td.name' , 'like' , "%$tele%"];
            $where_tel[]  = ['td.phone' , 'like' , "%$tele%" ];

        }

        if (!empty($options))
        {
            $where_group[] = ['group_id','=',"$options"];
        }
        if(!empty($agent_id)) {
            $where_agent['td.agent_id'] = ["$agent_id"];
        }
        $list = self::alias('td')
                     ->leftJoin('sh_telephone_group tg','td.group_id = tg.id')
                     ->where(function ($q) use($where_tel){ $q->whereOr($where_tel);})
                     ->where($where_group)->where(['td.agent_id'=>$agent_id])
                     ->limit(($page-1)*$limit,$limit)
                     ->field(['td.id','compony','sex','email','address','group_id','td.name','phone','tg.name as group_name','td.remarks'])
                     ->order('td.id desc')
                     ->select();
        return $list;
    }


    /**
     *获取通讯录总条数
     * @param null options      分组
     * @param null telephone    电话
     */
    static public function getTeleListCount($agent_id = null,$options = null,$telephone = null)
    {
        $where_tel = [];
        $where_group = [];
        if (empty($telephone) === false)
        {
            $tele = trim($telephone);
            $where_tel[] = ['phone' , 'like' , "%$tele%"];
            $where_tel[] = ['td.name' , 'like' , "%$tele%"];
        }
        if (!empty($options))
        {
            $where_group['group_id'] = [$options];
        }

        $count = self::alias('td')
            ->leftJoin('sh_telephone_group tg','td.group_id = tg.id')
            ->where(function ($q) use($where_tel){ $q->whereOr($where_tel);})
            ->where($where_group)->where(['td.agent_id'=>$agent_id])
            ->field(['td.id','compony','sex','email','address','group_id','td.name','phone','tg.name as group_name','td.remarks'])
            ->order('td.id desc')
            ->count();
        return $count;
    }

    /**
     * @param $data     通讯录数据
     */
    static public function add_Directory($data)
    {
        $datas = [
            'compony'  => $data['compony'],
            'sex'      => $data['sex'],
            'email'    => $data['email'],
            'address'  => $data['address'],
            'agent_id' => cmf_get_current_user_id(),
            'group_id' => $data['group_id'],
            'name'     => $data['name'],
            'phone'    => $data['phone'],
            'remarks'  => $data['remarks']
        ];
        if($data['group_id'] == null){
            return ['status'=>1 , 'massage' => '请选择组'];
        }
        $result = self::insert($datas);
        if ($result) {
            return ['status'=>0 , 'massage' => '添加成功'];
        }else {
            return ['status'=>1 , 'massage' => '数据异常'];
        }
    }

    /**
     * 删除通讯录
     * @param id    通讯录ID
     */
    static public function del_Directory($id)
    {
        if (!strpos($id , ',')) {
            $where['id'] = "$id";
            $result = self::where($where)->delete();
        }else {
            $ids =  substr($id , 0, -1);
            $result = Db::table('sh_telephone_directory')->where("id in ($ids)")->delete();
        }
        if ($result) {
            return ['status' =>0 , 'massage' => '删除成功'];
        } else {
            return ['status' =>1 , 'massage' => '删除失败'];
        }
    }
    /**
     * 根据ID 查询单条
     */
    static public function Directory($id) {
        $data = self::where('id',$id)->select();
        return $data;
    }
    /**
     * 修改通讯录
     */
    static public function do_Update($data){
        $result = self::where('id',$data['id'])->update($data);
        if($result) {
            return ['status'=>0,'massage'=>'修改成功'];
        }else {
            return ['status'=>1,'massage'=>'修改失败'];
        }
    }
}