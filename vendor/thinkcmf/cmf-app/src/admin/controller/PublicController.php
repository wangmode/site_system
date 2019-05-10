<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use think\Exception;

class PublicController extends AdminBaseController
{
    public function initialize()
    {
    }

    /**
     * 后台登陆界面
     */
    public function login()
    {
        $loginAllowed = session("__LOGIN_BY_CMF_ADMIN_PW__");
        if (empty($loginAllowed)) {
            //$this->error('非法登录!', cmf_get_root() . '/');
            return redirect(cmf_get_root() . "/");
        }

        $admin_id = session('ADMIN_ID');
        if (!empty($admin_id)) {//已经登录
            return redirect(url("admin/Index/index"));
        } else {
            session("__SP_ADMIN_LOGIN_PAGE_SHOWED_SUCCESS__", true);
            $result = hook_one('admin_login');
            if (!empty($result)) {
                return $result;
            }
            return $this->fetch(":login");
        }
    }

    /**
     * 登录验证
     */
    public function doLogin()
    {
        try{
            if (hook_one('admin_custom_login_open')) {
                throw new Exception('您已经通过插件自定义后台登录！');
//                $this->error('您已经通过插件自定义后台登录！');
            }

            $loginAllowed = session("__LOGIN_BY_CMF_ADMIN_PW__");
            if (empty($loginAllowed)) {
                throw new Exception('非法登录!');
//                $this->error('非法登录!', cmf_get_root() . '/');
            }

            $captcha = $this->request->param('captcha');
            if (empty($captcha)) {
                throw new Exception(lang('CAPTCHA_REQUIRED'));
//                $this->error(lang('CAPTCHA_REQUIRED'));
            }
            //验证码
            if (!cmf_captcha_check($captcha)) {
                throw new Exception(lang('CAPTCHA_NOT_RIGHT'));
//                $this->error(lang('CAPTCHA_NOT_RIGHT'));
            }

            $name = $this->request->param("username");
            if (empty($name)) {
                throw new Exception(lang('USERNAME_OR_EMAIL_EMPTY'));
//                $this->error(lang('USERNAME_OR_EMAIL_EMPTY'));
            }
            $pass = $this->request->param("password");
            if (empty($pass)) {
                throw new Exception(lang('PASSWORD_REQUIRED'));
//                $this->error(lang('PASSWORD_REQUIRED'));
            }
            if (strpos($name, "@") > 0) {//邮箱登陆
                $where['user_email'] = $name;
            } else {
                $where['user_login'] = $name;
            }

            $result = Db::name('user')->where($where)->find();

            if (!empty($result) && $result['user_type'] == 1) {
                if (cmf_compare_password($pass, $result['user_pass'])) {
                    $groups = Db::name('RoleUser')
                        ->alias("a")
                        ->join('__ROLE__ b', 'a.role_id =b.id')
                        ->where(["user_id" => $result["id"], "status" => 1])
                        ->value("role_id");
                    if ($result["id"] != 1 && (empty($groups) || empty($result['user_status']))) {
                        throw new Exception(lang('USE_DISABLED'));
//                        $this->error(lang('USE_DISABLED'));
                    }
                    //登入成功页面跳转
                    session('ADMIN_ID', $result["id"]);
                    session('name', $result["user_login"]);
                    $result['last_login_ip']   = get_client_ip(0, true);
                    $result['last_login_time'] = time();
                    $token                     = cmf_generate_user_token($result["id"], 'web');
                    if (!empty($token)) {
                        session('token', $token);
                    }
                    Db::name('user')->update($result);
                    cookie("admin_username", $name, 3600 * 24 * 30);
                    session("__LOGIN_BY_CMF_ADMIN_PW__", null);
                    return $this->returnJson(self::STATUS_OK,url("admin/Index/index"),lang('LOGIN_SUCCESS'));
//                $this->success(lang('LOGIN_SUCCESS'), url("admin/Index/index"));
                } else {
                    throw new Exception(lang('PASSWORD_NOT_RIGHT'));
//                $this->error(lang('PASSWORD_NOT_RIGHT'));
                }
            } else {
                throw new Exception(lang('USERNAME_NOT_EXIST'));
//            $this->error(lang('USERNAME_NOT_EXIST'));
            }
        }catch (Exception $exception){
            return $this->returnJson(self::STATUS_FAIL,null,$exception->getMessage());
        }
    }

    
}