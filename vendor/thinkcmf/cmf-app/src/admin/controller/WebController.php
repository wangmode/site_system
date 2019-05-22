<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/10
 * Time: 15:34
 */

namespace app\admin\controller;


use app\common\model\Article21Model;
use app\common\model\ArticleData21Model;
use app\common\model\CompanyDataModel;
use app\common\model\CompanyModel;
use app\common\model\MemberGroupModel;
use app\common\model\MemberMiscModel;
use app\common\model\MemberModel;
use app\common\model\NewsDataModel;
use app\common\model\NewsModel;
use app\common\model\WareKeywordModel;
use app\common\model\WebConfigModel;
use app\common\model\WebKeywordModel;
use cmf\controller\AdminBaseController;
use think\Exception;

class WebController extends AdminBaseController
{

    /**
     * @return mixed
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * @return \think\response\Json
     */
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
            return $this->returnListJson(self::CODE_OK, $count, $data, '获取网站配置列表成功');
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL, null, null, $exception->getMessage());
        }
    }

    /**
     * @return mixed
     */
    public function add()
    {
        return $this->fetch();
    }

    /**
     * @return \think\response\Json
     */
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

    /**
     * @return mixed
     */
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

    /**
     * @return \think\response\Json
     */
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

    /**
     * @return \think\response\Json
     */
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


    /**
     * @return \think\response\Json
     */
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

    /**
     * @return \think\response\Json
     */
    public function delWebConfig()
    {

        $id       = $this->request->param('id');
        try{
            if(empty($id) === true){
                throw new Exception('非法访问');
            }
            WebKeywordModel::delByWebId($id);
            WebConfigModel::delWebConfig($id);
            return $this->returnStatusJson(self::STATUS_OK,null,'删除网站配置成功！');
        }catch (Exception $exception){
            return $this->returnStatusJson(self::STATUS_FAIL,null,$exception->getMessage());
        }
    }

    /**
     * @return mixed
     */
    public function keywordList()
    {

        $id       = $this->request->param('id');
        try{
            if(empty($id) === true){
                throw new Exception('非法访问');
            }
            $this->assign('id',$id);
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }
    }

    /**
     * @return \think\response\Json
     */
    public function getKeywordList()
    {
        $id         = $this->request->param('id');
        $page       = $this->request->param('page');
        $limit      = $this->request->param('limit');
        $status     = $this->request->param('status');
        $keyword    = $this->request->param('keyword');
        try{
            if(empty($id)){
                throw new Exception('非法访问');
            }
            $data   = WebKeywordModel::getKeywordListDataByWebId($id,$keyword,$status,$page,$limit);
            $count  = WebKeywordModel::getKeywordListCountByWebId($id,$keyword,$status);
            return $this->returnListJson(self::CODE_OK, $count, $data, '获取网站关键词列表成功');
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL, null, null, $exception->getMessage());
        }
    }


    /**
     * @return \think\response\Json
     */
    public function delWebKeyword()
    {
        $web_id     = $this->request->param('web_id');
        $keyword_id = $this->request->param('keyword_id');
        try{
            if(empty($web_id) || empty($keyword_id) ){
                throw new Exception('非法访问');
            }
            WebKeywordModel::del($web_id,$keyword_id);
            return $this->returnStatusJson(self::STATUS_OK,null,'删除网站关键词配置成功！');
        }catch (Exception $exception){
            return $this->returnStatusJson(self::STATUS_FAIL,null,$exception->getMessage());
        }
    }


    /**
     * @return \think\response\Json
     */
    public function getKeywordListData()
    {
        $page       = $this->request->param('page');
        $limit      = $this->request->param('limit');
        $web_id     = $this->request->param('web_id');
        $keyword    = $this->request->param('keyword');
        try{
            if(empty($web_id)){
                throw new Exception('非法访问');
            }
            $data   = WareKeywordModel::getNotWebKeywrodData($web_id,$keyword,$page,$limit);
            $count  = WareKeywordModel::getNotWebKeywrodCount($web_id,$keyword);
            return $this->returnListJson(self::CODE_OK, $count, $data, '获取网站关键词列表成功');
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL, null, null, $exception->getMessage());
        }
    }

    /**
     * @return \think\response\Json
     */
    public function addKeyword()
    {
        $web_id     = $this->request->param('web_id');
        $keyword_id = $this->request->param('keyword_id');
        try{
            if(empty($web_id) || empty($keyword_id) ){
                throw new Exception('非法访问');
            }
            WebKeywordModel::add($web_id,$keyword_id);
            return $this->returnStatusJson(self::STATUS_OK,null,'添加网站关键词配置成功！');
        }catch (Exception $exception){
            return $this->returnStatusJson(self::STATUS_FAIL,null,$exception->getMessage());
        }
    }

    /**
     * @return mixed
     */
    public function articlaList()
    {
        $id       = $this->request->param('id');
        try{
            if(empty($id) === true){
                throw new Exception('非法访问');
            }
            $this->assign('id',$id);
            $this->assign('url',WebConfigModel::getUrl($id));
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }
    }

    /**
     * @return \think\response\Json
     */
    public function getArticlaListData()
    {
        $page       = $this->request->param('page',1);
        $limit      = $this->request->param('limit',10);
        $web_id     = $this->request->param('web_id',2);
        $keyword    = $this->request->param('keyword');
            try{
            if(empty($web_id)){
                throw new Exception('非法访问');
            }

            $Article = new Article21Model();
            $Article->modelInit($web_id);
            $data   = $Article->getArticleListData($keyword,$page,$limit);
            $count  = $Article->getArticleListCount($keyword);
            return $this->returnListJson(self::CODE_OK, $count, $data, '获取网站资讯列表成功');
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL, null, null, $exception->getMessage());
        }
    }

    /**
     * @return \think\response\Json
     */
    public function delArticla()
    {
        $web_id     = $this->request->param('web_id');
        $itemid     = $this->request->param('itemid');
        try{
            if(empty($web_id) || empty($itemid) ){
                throw new Exception('非法访问');
            }
            $Article = new Article21Model();
            $Article->modelInit($web_id);
            $Article->delArticle($itemid);
            $ArticleData = new ArticleData21Model();
            $ArticleData->modelInit($web_id);
            $ArticleData->delArticle($itemid);
            return $this->returnStatusJson(self::STATUS_OK,null,'删除网站资讯成功！');
        }catch (Exception $exception){
            return $this->returnStatusJson(self::STATUS_FAIL,null,$exception->getMessage());
        }

    }

    /**
     * 加载客户管理页面
     * @return mixed
     */
    public function memberList()
    {
        $id       = $this->request->param('id');
        try{
            if(empty($id) === true){
                throw new Exception('非法访问');
            }
            $group = new MemberGroupModel();
            $group->modelInit($id);
            $groupname = $group->groupName();
            $this->assign('groupname',$groupname);
            $this->assign('id',$id);
            $this->assign('url',WebConfigModel::getUrl($id));
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }
    }


    /**
     * 加载客户列表
     * @return \think\response\Json
     */
    public function getMemberListData()
    {
        $page       = $this->request->param('page',1);
        $limit      = $this->request->param('limit',10);
        $web_id     = $this->request->param('web_id');
        $keyword    = $this->request->param('keyword');
        $status     = $this->request->param('status');
        try{
            if(empty($web_id)){
                throw new Exception('非法访问');
            }
            $member = new MemberModel();
            $member->modelInit($web_id);
            $data   = $member->memberList($keyword,$status,$page,$limit);
            $count  = $member->memberListCount($keyword,$status);
            return $this->returnListJson(self::CODE_OK, $count, $data, '获取客户列表成功');
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL, null, null, $exception->getMessage());
        }
    }

    /**
     * 删除此账户
     * @return \think\response\Json
     */
    public function delMember()
    {
        $web_id     = $this->request->param('web_id');
        $userid     = $this->request->param('userid');
        try{
            if(empty($web_id) || empty($userid) ){
                throw new Exception('非法访问');
            }
            $member      = new MemberModel();
            $company     = new CompanyModel($web_id);
            $misc        = new MemberMiscModel($web_id);
            $companyData = new CompanyDataModel($web_id);
            $member      -> modelInit($web_id);
            $member      -> delMember($userid);
            $company     -> delCompany($userid);
            $misc        -> delMemberMisc($userid);
            $companyData -> delCompanData($userid);
            return $this->returnStatusJson(self::STATUS_OK,null,'删除客户成功！');
        }catch (Exception $exception){
            return $this->returnStatusJson(self::STATUS_FAIL,null,$exception->getMessage());
        }
    }


    public function memberEdit()
    {
        $web_id     = $this->request->param('web_id');
        $userid     = $this->request->param('userid');
        $member = new MemberModel();
        $member->modelInit($web_id);
        $info = $member->memberInfo($userid);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * @return mixed
     */
    public function newsList()
    {
        $id       = $this->request->param('id');
        try{
            if(empty($id) === true){
                throw new Exception('非法访问');
            }
            $this->assign('id',$id);
            return $this->fetch();
        }catch (Exception $exception){
            $this->error($exception->getMessage());
        }
    }


    /**
     * @return \think\response\Json
     */
    public function getNewsListData()
    {
        $page       = $this->request->param('page',1);
        $limit      = $this->request->param('limit',10);
        $web_id     = $this->request->param('web_id',2);
        $keyword    = $this->request->param('keyword');
        try{
            if(empty($web_id)){
                throw new Exception('非法访问');
            }
            $news = new NewsModel();
            $news->modelInit($web_id);
            $data   = $news->getNewsListData($keyword,$page,$limit);
            $count  = $news->getNewsListCount($keyword);
            return $this->returnListJson(self::CODE_OK, $count, $data, '获取网站资讯列表成功');
        }catch (Exception $exception){
            return $this->returnListJson(self::CODE_FAIL, null, null, $exception->getMessage());
        }
    }

    /**
     * @return \think\response\Json
     */
    public function delNews()
    {
        $web_id     = $this->request->param('web_id');
        $itemid     = $this->request->param('itemid');
        try{
            if(empty($web_id) || empty($itemid) ){
                throw new Exception('非法访问');
            }
            $news = new NewsModel();
            $news->modelInit($web_id);
            $news->delNews($itemid);
            $news_data = new NewsDataModel();
            $news_data->modelInit($web_id);
            $news_data->delNews($itemid);
            return $this->returnStatusJson(self::STATUS_OK,null,'删除网站新闻成功！');
        }catch (Exception $exception){
            return $this->returnStatusJson(self::STATUS_FAIL,null,$exception->getMessage());
        }
    }


}