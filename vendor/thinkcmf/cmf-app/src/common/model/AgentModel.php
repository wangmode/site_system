<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/1
 * Time: 15:49
 */


namespace app\common\model;

use think\Model;

class AgentModel extends Model
{
    protected $connection= 'db_daili';

    const STATUS_DISABLE    = 0; //禁用
    const STATUS_NORMAL     = 1; //正常


    /**
     * 通过ID获取代理商名称
     * @param $agent_id
     * @return mixed
     */
    static public function getAgentCompanyById($agent_id)
    {
        return self::where($agent_id)->value('company');
    }


    /**
     * 获取所有代理商列表数据
     * @param null $keyword         //搜索关键词
     * @param null $status          //搜索状态
     * @param int $page             //页码
     * @param int $limit            //每页条数
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getAgentListData($keyword=null,$status=null,$page=1,$limit=10)
    {
        $where = [];
        if(empty($keyword) === false){
            $keywords = trim($keyword);
            $where[] =['company|account','like',"%$keywords%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['status','=',$status];
        }
        $list = self::where($where)
                    ->limit(($page-1)*$limit,$limit)
                    ->field(['id','company','status','convert(money, decimal(12,2)) as money','account'])
                    ->select();
        return $list;
    }


    /**
     * 获取代理列表总条数
     * @param null $keyword         //搜索关键词
     * @param null $status          //搜索状态
     * @return float|string
     */
    static public function getAgentListCount($keyword=null,$status=null)
    {
        $where = [];
        if(empty($keyword) === false){
            $keywords = trim($keyword);
            $where[] =['company','like',"%$keywords%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['status','=',$status];
        }
        $count = self::where($where)->count();
        return $count;
    }

    /**
     * 获取代理客户详细信息
     * @param $agent_id     //代理客户ID
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getAgentInfo($agent_id)
    {
        $info = self::alias('a')
            ->where('a.id',$agent_id)
            ->leftJoin('city c','a.province = c.id')
            ->leftJoin('city ci','a.city = ci.id')
            ->field(['a.id','c.name as province_name','ci.name as city_name','a.company','a.account','a.linkman','a.linkphone','a.qq','a.wechat','a.status','a.type','convert(a.money, decimal(12,2)) as money','a.level'])
            ->find();
        return $info;
    }

    /**
     * 登录判断
     * @param $user
     * @return int
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function doName($user)
    {
        $result = $this->where('account', $user['user_login'])->find();
        if (!empty($result)) {
            $comparePasswordResult = yzt_compare_password($user['user_pass'], $result['password']);
            $hookParam             = [
                'user'                    => $user,
                'compare_password_result' => $comparePasswordResult
            ];
            hook_one("user_login_start", $hookParam);
            if ($comparePasswordResult) {
                //拉黑判断。
                if ($result['status'] == 0) {
                    return ['status'=>3,'massage'=>'账号被禁止访问系统'];
                }
                session('user', $result->toArray());
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip'   => get_client_ip(0, true),
                ];
                $result->where('id', $result["id"])->update($data);
                $token = cmf_generate_user_token($result["id"], 'web');
                if (!empty($token)) {
                    session('token', $token);
                }
                return ['status'=>1,'massage'=>lang('LOGIN_SUCCESS')];
            }
            return ['status'=>0,'massage'=>lang('PASSWORD_NOT_RIGHT')];
        }
        $hookParam = [
            'user'                    => $user,
            'compare_password_result' => false
        ];
        hook_one("user_login_start", $hookParam);
        return ['status'=>2,'massage'=>'账户不存在'];
    }

    /**
     * 获取用户密码
     * @param $id
     * @return mixed
     */
    public function getPassword($id)
    {
        $data = self::where(['id'=>$id])->find();
        return $data['password'];
    }


    /**
     * 短信扣费
     * @param $agent_id     //客户ID
     * @param $money        //扣费金额
     * @return int|true
     * @throws \think\Exception
     */
    static public function setDecMoney($agent_id,$money)
    {
        return self::where('id',$agent_id)
                    ->where('money','>=',$money)
                    ->setDec('money',$money);
    }

    /**
     * 获取客户余额
     * @param $agent_id     //客户ID
     * @param $money        //扣费金额
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getAgentMoney($agent_id,$money)
    {
        $agent = self::where('id',$agent_id)
            ->where('money','>=',$money)
            ->field('id')
            ->find();
        if($agent->isEmpty()){
            return false;
        }
        return true;
    }

    /**
     * 修改用户密码
     * @param $id
     * @param $pwd
     */
    public function updatePwd($id,$pwd)
    {
        self::where(['id'=>$id])->update(['password'=>md5($pwd)]);
    }
}