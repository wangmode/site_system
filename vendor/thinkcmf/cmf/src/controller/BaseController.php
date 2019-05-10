<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace cmf\controller;

use think\Container;
use think\Controller;
use think\Db;
use think\facade\View;
use think\facade\Config;

class BaseController extends Controller
{
    const CODE_OK   = 0;
    const CODE_FAIL = 404;

    const STATUS_OK     = 1;
    const STATUS_FAIL   = 0;


    /**
     * BaseController constructor.
     */
    public function __construct()
    {
        $this->app     = Container::get('app');
        $this->request = $this->app['request'];

        if (!cmf_is_installed() && $this->request->module() != 'install') {
            return $this->redirect(cmf_get_root() . '/?s=install');
        }

        $this->_initializeView();
        $this->view = View::init(Config::get('template.'));

        // 控制器初始化
        $this->initialize();

        // 前置操作方法 即将废弃
        foreach ((array)$this->beforeActionList as $method => $options) {
            is_numeric($method) ?
                $this->beforeAction($options) :
                $this->beforeAction($method, $options);
        }

    }


    // 初始化视图配置
    protected function _initializeView()
    {
    }

    /**
     *  排序 排序字段为list_orders数组 POST 排序字段为：list_order
     */
    protected function listOrders($model)
    {
        $modelName = '';
        if (is_object($model)) {
            $modelName = $model->getName();
        } else {
            $modelName = $model;
        }

        $pk  = Db::name($modelName)->getPk(); //获取主键名称
        $ids = $this->request->post("list_orders/a");

        if (!empty($ids)) {
            foreach ($ids as $key => $r) {
                $data['list_order'] = $r;
                Db::name($modelName)->where($pk, $key)->update($data);
            }
        }

        return true;
    }

    /**
     * @param int $code
     * @param int $count
     * @param null $data
     * @param string $message
     * @return \think\response\Json
     */
    protected function returnListJson($code = self::CODE_FAIL,$count = 0,$data= null, $message = '')
    {
        return json(['code'=>$code,'count'=>$count,'data'=>$data,'message'=>$message]);
    }


    /**
     * @param int $status
     * @param null $data
     * @param string $message
     * @return \think\response\Json
     */
    protected function returnJson($status = self::STATUS_FAIL,$data= null, $message = '')
    {
        return json(['status'=>$status,'data'=>$data,'message'=>$message]);
    }


    /**
     * @param int $status
     * @param null $result_status
     * @param string $message
     * @return \think\response\Json
     */
    protected function returnStatusJson($status = self::STATUS_FAIL,$result_status= null, $message = '')
    {
        return json(['status'=>$status,'result_status'=>$result_status,'message'=>$message]);
    }

}