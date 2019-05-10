<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/2
 * Time: 11:35
 */

namespace app\common\model;


use think\Model;

class TemplateModel extends Model
{
    const STATUS_AUDITING   = 0;    //  状态 审核中
    const STATUS_ADOPT      = 1;    //  状态 通过
    const STATUS_REJECT     = 2;    //  状态 驳回

    const IS_DEL_NO         = 0;    //  未删除
    const IS_DEL_YES        = 1;    //  已删除


    /**
     * * 获取模板列表
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
    static public function getTemplateListData($keyword=null,$product_id=null,$is_del=null,$status=null,$start_time=null,$end_time=null,$agent_id = null,$page=1,$limit=10)
    {
        $where = [];
        if(empty($keyword) === false){
            $keywords = trim($keyword);
            $where[] =['t.name','like',"%$keywords%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['t.status','=',$status];
        }
        if(empty($is_del) === false || $is_del === 0 || $is_del === '0'){
            $where[] = ['t.is_del','=',$is_del];
        }
        if(empty($product_id) === false){
            $where[] = ['t.product_id','=',$product_id];
        }
        if(empty($agent_id) === false){
            $where[] = ['t.agent_id','=',$agent_id];
        }
        if(empty($start_time) === false && empty($end_time) === false){
            $start_time= formatting_time($start_time);
            $end_time = formatting_time($end_time);
            if(empty($start_time) === false && empty($end_time) === false){
                $where[] = ['t.create_at','between',[$start_time,$end_time]];
            }
        }

        $list = self::alias('t')
            ->leftJoin('product p','t.product_id = p.id')
            ->where($where)
            ->limit(($page-1)*$limit,$limit)
            ->field(['t.id','t.name','t.status','t.create_at','t.is_del','t.agent_id','p.name as product_name'])
            ->order('t.id desc')
            ->select();
        foreach ($list as $key=>$val){
            $list[$key]['company'] = AgentModel::getAgentCompanyById($val['agent_id']);
        }
        return $list;
    }


    /**
     * 获取模板列表总数
     * @param null $keyword         //搜索关键词
     * @param null $product_id      //搜索产品ID
     * @param null $is_del          //筛选是否删除
     * @param null $status          //状态筛选
     * @param null $start_time      //筛选 创建时间 （起始时间筛选）
     * @param null $end_time        //筛选 创建时间 （结束时间筛选）
     * @param null $agent_id        // 代理客户ID
     * @return float|string
     */
    static public function getTemplateListCount($keyword=null,$product_id=null,$is_del=null,$status=null,$start_time=null,$end_time=null,$agent_id = null)
    {
        $where = [];
        if(empty($keyword) === false){
            $keywords = trim($keyword);
            $where[] =['t.name','like',"%$keywords%"];
        }
        if(empty($status) === false || $status === 0 || $status === '0'){
            $where[] = ['t.status','=',$status];
        }
        if(empty($is_del) === false || $is_del === 0 || $is_del === '0'){
            $where[] = ['t.is_del','=',$is_del];
        }
        if(empty($product_id) === false){
            $where[] = ['t.product_id','=',$product_id];
        }
        if(empty($agent_id) === false){
            $where[] = ['t.agent_id','=',$agent_id];
        }
        if(empty($start_time) === false && empty($end_time) === false){
            $start_time= formatting_time($start_time);
            $end_time = formatting_time($end_time);
            if(empty($start_time) === false && empty($end_time) === false){
                $where[] = ['t.create_at','between',[$start_time,$end_time]];
            }
        }
        $count = self::alias('t')
                ->leftJoin('product p','t.product_id = p.id')
                ->where($where)
                ->count();

        return $count;
    }

    /**
     * 添加模板
     * @param $agent_id     //代理客户ID
     * @param $name         //模板名称
     * @param $product_id   //产品ID
     * @param $content      //模板内容
     * @return int|string
     */
    static public function addTemplate($agent_id,$name,$product_id,$content)
    {
        return self::insertGetId([
            'name'          =>$name,
            'content'       =>$content,
            'agent_id'      =>$agent_id,
            'is_del'        =>self::IS_DEL_NO,
            'product_id'    =>$product_id,
            'status'        =>self::STATUS_AUDITING,
            'create_at'     =>date('Y-m-d H:i:s',time()),
        ]);
    }

    static public function newAddTemplate($agent_id,$name,$product_id,$content,$description)
    {
        $template_id = self::addTemplate($agent_id,$name,$product_id,$content);
        $apply_id = ApplyModel::addApply($agent_id,$template_id,ApplyModel::TYPE_TEMPLATE,$description);
        return self::bindApplyId($template_id,$apply_id);
    }

    /**
     * 绑定申请ID
     * @param $template_id      //模板ID
     * @param $apply_id         //申请ID
     * @return TemplateModel
     */
    static private function bindApplyId($template_id,$apply_id)
    {
        return self::update([
            'id'        => $template_id,
            'apply_id'  => $apply_id
        ]);
    }

    /**
     * 通过模板
     * @param $template_id
     * @return TemplateModel
     */
    static public function adoptTemplate($template_id)
    {
        return self::update([
            'id'        => $template_id,
            'status'    => self::STATUS_ADOPT
        ]);
    }


    /**
     * 驳回模板
     * @param $template_id
     * @return TemplateModel
     */
    static public function rejectTemplate($template_id)
    {
        return self::update([
            'id'        => $template_id,
            'status'    => self::STATUS_REJECT
        ]);
    }


    /**
     * 修改模板重新审核
     * @param $id
     * @param $name
     * @param $content
     * @param $product_id
     * @param $description
     * @return mixed
     */
    static public function editTemplate($id,$name,$content,$product_id,$description)
    {
        $template_info = self::getTemplateStatus($id);
        if(empty($template_info)){
            throw new Exception('查找不到当前模板！');
        }
        if($template_info['status'] == self::STATUS_AUDITING){
            throw new Exception('当前模板正在审核中！');
        }
        $apply_id = ApplyModel::addApply($template_info['agent_id'],$template_info['id'],ApplyModel::TYPE_TEMPLATE,$description);
        return self::updateTemplate($id,$name,$content,$product_id,$apply_id);
    }


    /**
     * 获取模板状态
     * @param $id       //模板ID
     * @return mixed
     */
    static private function getTemplateStatus($id)
    {
        return self::where(['id'=>$id])->where('is_del',self::IS_DEL_NO)->field(['id','status','agent_id'])->find();
    }


    /**
     *  修改模板
     * @param $id               //模板ID
     * @param $name             //模板名称
     * @param $content          //模板内容
     * @param $product_id       //产品ID
     * @param $apply_id         //申请ID
     * @return TemplateModel
     */
    static public function updateTemplate($id,$name,$content,$product_id,$apply_id)
    {
        return self::update([
            'id'            =>$id,
            'name'          =>$name,
            'content'       =>$content,
            'apply_id'      =>$apply_id,
            'product_id'    =>$product_id,
            'status'        =>self::STATUS_AUDITING
        ]);
    }

    /**
     * 删除模板
     * @param $id           //签名ID
     */
    static public function delTemplate($id)
    {
        return self::update([
            'id'=>$id,
            'is_del'=>self::IS_DEL_YES
        ]);
    }

    /**
     * 通过模板ID获取内容
     * @param $agent_id     //代理客户ID
     * @param $id           //模板ID
     * @return mixed
     */
    static public function getTemplateById($agent_id,$id)
    {
        return self::where('id',$id)
                    ->where('agent_id',$agent_id)
                    ->where('status',self::STATUS_ADOPT)
                    ->where('is_del',self::IS_DEL_NO)
                    ->value('content');
    }


    /**
     * 获取模板详细信息
     * @param $id       //模板ID
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getTemplateInfo($id)
    {
        return self::alias('t')
                ->leftJoin('apply a','t.apply_id = a.id')
                ->leftJoin('product p','t.product_id = p.id')
                ->where('t.id',$id)
                ->field(['t.name','t.product_id','t.is_del','t.create_at','t.content','t.status','a.reason','a.apply_time','a.description','a.review_time','p.name as product_name','p.price'])
                ->find();
    }


    /**
     * 获取对应的模板
     * @param $agent_id
     * @param $product_id
     * @param $is_del
     * @param $status
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function getTemplateData($agent_id,$product_id,$is_del,$status)
    {
        return self::where(['agent_id'=>$agent_id,'product_id'=>$product_id,'is_del'=>$is_del,'status'=>$status])
                ->field(['id as template_id','name','content'])
                ->select();
    }

    /**
     * 通过模板ID获取产品价格
     * @param $agent_id     //代理ID
     * @param $template_id  //模板ID
     * @return mixed
     */
    static public function getProductPriceByTemplateId($agent_id,$template_id)
    {
        return self::alias('t')
                    ->leftJoin('product p','t.product_id = p.id')
                    ->where('t.id',$template_id)
                    ->where('t.agent_id',$agent_id)
                    ->where('t.status',self::STATUS_ADOPT)
                    ->where('t.is_del',self::IS_DEL_NO)
                    ->value('p.price');
    }
    

}