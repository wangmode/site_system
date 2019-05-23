<?php
namespace  app\common\Exception;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/23
 * Time: 17:05
 */

class ArticleException extends \think\Exception
{

    function __construct($message, $itemid ,$data_id)
    {
        $this->setData('Article_error_data',[
            'itemid'      => $itemid,
            'data_id'   => $data_id,
        ]);
        parent::__construct($message);
    }


    function getArticleId()
    {
        return $this->getData()['Article_error_data'];
    }



}