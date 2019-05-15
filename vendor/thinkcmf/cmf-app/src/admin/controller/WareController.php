<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/13
 * Time: 16:49
 */

namespace app\admin\controller;


use app\common\model\WareKeywordModel;
use cmf\controller\AdminBaseController;
use think\Exception;

class WareController extends AdminBaseController
{

    /**
     * 关键词列表
     * @return mixed
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 关键词列表数据
     * @return \think\response\Json
     */
    public function getWareKeywordData()
    {
        $page       = $this->request->param('page');
        $limit      = $this->request->param('limit');
        $status     = $this->request->param('status');
        $keyword    = $this->request->param('keyword');
        try{
            $data = WareKeywordModel::getWareKeywordListData($keyword,$status,$page,$limit);
            $count = WareKeywordModel::getWareKeywordListCount($keyword,$status);
            return $this->returnListJson(self::CODE_OK, $count, $data, '获取代理客户列表成功');
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL, null, null, $exception->getMessage());
        }
    }

    /**
     * 添加新的关键词
     * @return \think\response\Json
     */
    public function addPost()
    {
        $data = $this->request->param();
        $validate = $this->validate($data,'WareKeyword');
        try{
            if($validate !== true){
                throw new Exception($validate);
            }
            WareKeywordModel::newAddKeyword($data['keyword']);
            return $this->returnJson(self::STATUS_OK,null,'添加新的关键词成功！');
        }catch (Exception $exception){
            return $this->returnJson(self::STATUS_FAIL,null,$exception->getMessage());
        }
    }

    /**
     * 变更关键词状态
     * @return \think\response\Json
     */
    public function editStatus()
    {
        $id = $this->request->param('id');
        try{
            if(empty($id) === true){
                throw new Exception('非法访问');
            }
            $status = WareKeywordModel::editKeywordStatus($id);
            return $this->returnStatusJson(self::STATUS_OK,$status,'变更关键词状态成功！');
        }catch (Exception $exception){
            return $this->returnStatusJson(self::STATUS_FAIL,null,$exception->getMessage());
        }
    }

    /**
     * 删除关键词
     * @return \think\response\Json
     */
    public function delKeyword()
    {
        $id = $this->request->param('id');
        try{
            if(empty($id) === true){
                throw new Exception('非法访问');
            }
            WareKeywordModel::deleteKeyword($id);
            return $this->returnJson(self::STATUS_OK,null,'删除关键词状成功！');
        }catch (Exception $exception){
            return $this->returnJson(self::STATUS_FAIL,null,$exception->getMessage());
        }
    }
}