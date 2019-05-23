<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/20
 * Time: 11:31
 */

namespace app\admin\service;

use app\common\model\WarehouseModel;
use app\common\model\WareModel;
use app\common\model\WebKeywordModel;
use app\common\model\CompanyModel;
use app\common\model\CategoryModel;
use app\common\model\Article21Model;
use app\common\model\ArticleData21Model;
use app\common\model\NewsModel;
use app\common\model\NewsDataModel;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use app\common\Exception\ArticleException;

class WareService
{
    /**
     * 分发接口
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     * @throws \think\exception\PDOException
     */
    static public function distribute()
    {
        set_time_limit(0);
        $info = WarehouseModel::getWarehouseCount();
        $ware = new WareModel();
        foreach ($info as $key=>$value){
            $web = WebKeywordModel::getWebByKeywordId($value['keyword_id']);
            if(empty($web) === false){
                $num = ceil($value['num']/count($web));
                foreach ($web as $k=>$v){
                    $warehouse= WarehouseModel::getWarehouseDataByKeywordId($value['keyword_id'],$num);
                    if(empty($warehouse) === false){
                        $company = new CompanyModel($v);
                        $username =$company->getUsernameData();
                        $category = new CategoryModel($v);
                        $catid_arr = $category->getArticleCatId();
                        if(empty($username) === false){
                            $article = new Article21Model($v);
                            $article->modelInit($v);
                            $article_data = new ArticleData21Model($v);
                            $article_data->modelInit($v);
                            foreach ($warehouse as $item){
                                try{
                                    shuffle($username);
                                    shuffle($catid_arr);
                                    $article_id = $article->addArticle($item['keyword'],$catid_arr[0],$item['title'],$item['author_name'],$item['url'],$item['keyword'],'',$username[0],$item['platform_name']);
                                    $article->updateLinkUrl($article_id);

                                    $content = $ware->to_get_content($item['data_id']);
                                    usleep(100);
                                    $article_data->addArticaleData($article_id,$content,$item['data_id']);
                                    WarehouseModel::delByDataId($item['data_id']);
                                }catch (ArticleException $exception){
                                    $data = $exception->getArticleId();
                                    if(empty($data) === false){
                                        $article->delArticle($data['itemid']);
                                        WarehouseModel::delByDataId($data['data_id']);
                                    }
                                    echo  $exception->getMessage();
                                    continue;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

}