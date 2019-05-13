<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/10
 * Time: 15:34
 */

namespace app\admin\controller;


use app\common\model\WebConfigModel;
use cmf\controller\AdminBaseController;
use think\Exception;

class WebController extends AdminBaseController
{

    public function index()
    {
        return $this->fetch();
    }

    public function getWebListData()
    {
        $page       = $this->request->param('page');
        $limit      = $this->request->param('limit');
        $status     = $this->request->param('status');
        $is_ware    = $this->request->param('is_ware');
        $keyword    = $this->request->param('keyword');
        try{
            $data   = WebConfigModel::getWebListData($keyword,$status,$is_ware,$page,$limit);
            $count  = WebConfigModel::getWebListCount($keyword,$status,$is_ware);
            return $this->returnListJson(self::CODE_OK, $count, $data, '获取代理客户列表成功');
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL, null, null, $exception->getMessage());
        }
    }

    public function add()
    {
        return $this->fetch();
    }

    public function addPost()
    {
        $data       = $this->request->param();
        $validate = $this->validate($data,'WebConfig.add');
        try{
            if($validate !== true){
                throw new Exception($validate);
            }
            WebConfigModel::add($data['name'],$data['url'],$data['database'],$data['hostname'],$data['username'],$data['password'],$data['prefix']);
            return $this->returnJson(self::STATUS_OK,null,'添加成功');
        }catch (Exception $exception){
            return $this->returnJson(self::STATUS_FAIL,null,$exception->getMessage());
        }
    }

    public function edit()
    {
        $id       = $this->request->param('id');
        try{
            if(empty($id) === true){
                throw new Exception('非法访问');
            }
            $info = WebConfigModel::getWebConfigInfoById($id);
            $this->assign('info',$info);
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }
    }

    public function editPost()
    {
        $data       = $this->request->param();
        $validate = $this->validate($data,'WebConfig.edit');
        try{
            if($validate !== true){
                throw new Exception($validate);
            }
            WebConfigModel::edit($data['id'],$data['name'],$data['url'],$data['database'],$data['hostname'],$data['username'],$data['password'],$data['prefix']);
            return $this->returnJson(self::STATUS_OK,null,'编辑成功');
        }catch (Exception $exception){
            return $this->returnJson(self::STATUS_FAIL,null,$exception->getMessage());
        }
    }

    public function editStatus()
    {
        $id       = $this->request->param('id');
        try{
            if(empty($id) === true){
                throw new Exception('非法访问');
            }
            $status = WebConfigModel::editStatus($id);
            return $this->returnStatusJson(self::STATUS_OK,$status,'修改配置状态成功！');
        }catch (Exception $exception){
            return $this->returnStatusJson(self::STATUS_FAIL,null,$exception->getMessage());
        }
    }

    public function editIsWare()
    {
        $id       = $this->request->param('id');
        try{
            if(empty($id) === true){
                throw new Exception('非法访问');
            }
            $status = WebConfigModel::editIsWare($id);
            return $this->returnStatusJson(self::STATUS_OK,$status,'修改抓取状态成功！');
        }catch (Exception $exception){
            return $this->returnStatusJson(self::STATUS_FAIL,null,$exception->getMessage());
        }
    }
}