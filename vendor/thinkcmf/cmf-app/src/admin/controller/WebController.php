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
        $keyword    = $this->request->param('keyword');
        $page       = $this->request->param('page');
        $limit      = $this->request->param('limit');
        try{
            $data   = WebConfigModel::getWebListData($keyword,$page,$limit);
            $count  = WebConfigModel::getWebListCount($keyword);
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

    }
}