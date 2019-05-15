<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/14
 * Time: 14:23
 */

namespace app\common\model;


use think\Exception;

class WareModel
{

    protected $header = array();
    protected $project_guid = '';
    protected $url = '';
    protected $resultSetType = 'collection';
    //自定义初始化
    public function __construct()
    {
        $this->header =  ["Content-type:application/x-www-form-urlencoded","Authorization: APIKEY 478A032D810942EA98028A7758980A6B"];
        $this->project_guid = '7f5c2e94-0b08-e911-8db2-c81f66ed8109';
    }

    //更新监控关键词
    public function to_update_keyword($keywords){
        $url = 'http://apis.ciliuti.com/ciliuti/updateword';
        $this->header = ["Content-type:application/x-www-form-urlencoded","Authorization: APIKEY D95F1442A6734C59B705D88F9AC343BC"];
        $data['related_words'] = $keywords;
        $data['project_guid'] = $this->project_guid;
        $result_info = $this->httpRequest($url,$data,$this->header);
        $result = json_decode($result_info,true);
        if(!$result){
            throw new Exception('接口请求失败!');
        }
        if(isset($result['errcode']) && $result['errcode'] == 0){
            return true;
        }else{
            throw new Exception('关键词更新失败!');
        }
    }



    public function httpRequest($url,$header,$data){

        $ch = curl_init();

        /*请求地址*/

        curl_setopt($ch, CURLOPT_URL, $url);

        /*以CURL方式设置http的请求头*/

        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);

        /*文件流形式*/

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        /*发送一个常规的Post请求*/

        curl_setopt($ch, CURLOPT_POST, 1);

        /*Post提交的数据包*/

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        return curl_exec($ch);
    }


    //获取单条数据内容
    function to_get_content($data_id){
        $url = 'http://apis.ciliuti.com/ciliuti/content';
        $header = ["Content-type:application/x-www-form-urlencoded","Authorization: APIKEY 218BD1743C004F7585C6D28E18AC5B2D"];
        $data['data_id'] = $data_id;
        $data['project_guid'] = $this->project_guid;
        $result = json_decode($this->httpRequest($url,$header,http_build_query($data)),true);
        if(!$result){
            throw new Exception('接口请求失败!');

        }
        if(isset($result['errcode']) && $result['errcode'] == 0){
            $content = $result['data'][0]['content'];
            return $content;
        }else{
            throw new Exception('单条数据内容获取失败');
        }
    }

    public function get_title_list($keyword,$page)
    {
        $url = 'http://apis.ciliuti.com/ciliuti/title';
        $header = ["Content-type:application/x-www-form-urlencoded","Authorization: APIKEY E6F4DC8BAFB34C5684792510A7FB5590"];
        $data['page_index'] = $page;
        /*要提交的数据包*/
        $data['project_guid'] = $this->project_guid;
        $data['related_words'] =$keyword;
        $result_info = json_decode($this->httpRequest($url,$header,http_build_query($data)),true);
        if(!$result_info){
            throw new Exception($keyword.'接口请求失败!');
        }
        return $result_info;
    }


//    public function to_export_title($keyword_id,$keyword,$page,$num,$data=[])
//    {
//        try {
//            $result = $this->get_title_list($keyword, $page);
//        } catch (Exception $e) {
//        }
//        if(isset($result['errcode']) && $result['errcode'] == 0){
//            foreach ($result['data']['data'] as $key=>$val){
//                $val['publish_time'] = date('Y-m-d H:i:s',strtotime($val['publish_time']));
//                $val['collect_time'] = date('Y-m-d H:i:s',strtotime($val['add_time']));
//                $val['add_time']  = date('Y-m-d H:i:s',time());
//                $data['warehouse'][]= $val;
//            }
//            $data['keywords']['num'] = $num = $num+count($result['data']['data']);
//            $data['keywords']['current_page'] = $result['data']['page_index'];
//            $data['keywords']['page_num'] = $result['data']['page_size'];
//            $data['keywords']['total'] = $result['data']['total'];
//            if($page < $result['data']['page_count']){
//                $page++;
//                return $this->get_title_list($keyword_id,$keyword,$page,$num,$data);
//            }else{
//                return $data;
//            }
//        }else{
//            //print_r($result);exit;
//            throw new Exception("接口请求错误！$result[errmsg]，关键词：".$keyword.'<br>');
//        }
//    }


    /**
     * @param $keyword_id
     * @param $keyword
     * @param $page
     * @param $num
     * @throws Exception
     */
    public function to_export_title($keyword_id,$keyword,$page,$num)
    {
        do{
            $data = [];
            $result = $this->get_title_list($keyword, $page);
            if(isset($result['errcode']) && $result['errcode'] == 0){
                $result_data = $result['data']['data'];
                $count = count($result_data);
                $key = $num-($page-1)*$result['data']['page_size'];
                for ($i=$key;$i<=$count;$i++){
                    if(isset($result_data[$i]) && empty($result_data[$i]) === false){
                        $info['keyword']        = $keyword;
                        $info['keyword_id']     = $keyword_id;
                        $info['url']            = $result_data[$i]['url'];
                        $info['title']          = $result_data[$i]['title'];
                        $info['data_id']        = $result_data[$i]['data_id'];
                        $info['add_time']       = date('Y-m-d H:i:s',time());
                        $info['author_name']    = $result_data[$i]['author_name'];
                        $info['platform_name']  = $result_data[$i]['platform_name'];
                        $info['collect_time']   = date('Y-m-d H:i:s',strtotime($result_data[$key]['add_time']));
                        $info['publish_time']   = date('Y-m-d H:i:s',strtotime($result_data[$key]['publish_time']));
                        $data[]= $info;
                    }
                }
                $num = $num+count($data);
                WarehouseModel::addWarehouseAll($data);
                WareKeywordModel::updateKeywordInfo($keyword_id,$num, $result['data']['page_index'],$result['data']['total']);
                $page++;
            }else{
                throw new Exception("接口请求错误！$result[errmsg]，关键词：".$keyword.'<br>');
            }
        }while($page <= $result['data']['page_count']);
    }


    /**
     * 抓取文章
     */
    public function get_Warehouse_list()
    {
        set_time_limit(0);
        $ware_keyword_list = WareKeywordModel::getWareKeywordsData();
        foreach ($ware_keyword_list as $key=>$value){
            try{
                $this->to_export_title($value['id'],$value['keyword'],$value['current_page'],$value['num']);
                echo $value['keyword'].'文章抓取成功!<br>';
            }catch (Exception $exception){
                echo date('Y-m-d H:i:s',time()).$exception->getMessage().'<br>';
            }
//            sleep(1);
        }
    }


}