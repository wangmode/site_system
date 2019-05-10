<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/21
 * Time: 17:26
 */

namespace app\common\model;

use PHPExcel_IOFactory;
use PHPExcel;
use think;
use cmf\lib\Upload;
use app\common\model\TelephoneDirectoryModel;
use app\common\model\TelephoneGroupModel;

class PHPExcelTeleModel
{

    /**
     * @param $data
     * @return string
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function explodeCustomerList($data)
    {
        if (empty($data)) {
            $data = ['0'=>['compony'=>'','sex'=>'','email'=>'','address'=>'','group_id'=>'','name'=>'','phone'=>'','group_name'=>'','remarks'=>'']];
        }
//        vendor("PHPoffice.Classes.PHPExcel");
//        vendor("PHPoffice.Classes.Writer.Excel5");
//        vendor("PHPoffice.Classes.Writer.Excel2007");
//        vendor("PHPoffice.Classes.IOFactory");

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '公司名')
            ->setCellValue('B1', '性别')
            ->setCellValue('C1', '邮箱')
            ->setCellValue('D1', '地址')
            ->setCellValue('E1', '组名')
            ->setCellValue('F1', '姓名')
            ->setCellValue('G1', '手机号')
            ->setCellValue('H1', '注释');


        //设置A列水平居中
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置单元格宽度
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(30);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(15);
//        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('I')->setWidth(10);


        //6.循环刚取出来的数组，将数据逐一添加到excel表格。
        for($i=0;$i<count($data);$i++){
//            $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+2),$data[$i]['id']);//ID
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+2),$data[$i]['compony']);//公司名称
            if ($data[$i]['sex'] == 1) {
                $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+2),'男');//性别
            }else if ($data[$i]['sex'] == 2){
                $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+2),'女');//性别
            }
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+2),$data[$i]['email']);//邮箱
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+2),$data[$i]['address']);//地址
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+2),$data[$i]['group_name']);//分组ID
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+2),$data[$i]['name']);//联系人名称
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($i+2),$data[$i]['phone']);//联系方式
            $objPHPExcel->getActiveSheet()->setCellValue('H'.($i+2),$data[$i]['remarks']);//备注
//            $objPHPExcel->getActiveSheet()->setCellValue('I'.($i+2),CustomerInfoModel::getIsContactName($data[$i]['is_contact']));//联系状态
//            $objPHPExcel->getActiveSheet()->setCellValue('J'.($i+2),CustomerInfoModel::getStatusName($data[$i]['status']));//客户状态

        }
        //7.设置保存的Excel表格名称
        $filename = '通讯录列表'.date('YmdHis',time()).'.xlsx';
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('通讯录列表');
        //9.设置浏览器窗口下载表格
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:attachment;filename="'.$filename.'"');
//        //生成excel文件
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//        //下载文件在浏览器窗口
        $objWriter->save('php://output');
        exit;

    }

    /**
     * 导入客户资料
     * @param $filePath
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function importExcel($filePath)
    {
//        vendor("PHPoffice.phpexcel.Classes.PHPExcel");
//        vendor("PHPoffice.phpexcel.Classes.PHPExcel.Writer.Excel5");
//        vendor("PHPoffice.phpexcel.Classes.PHPExcel.Writer.Excel2007");
//        vendor("PHPoffice.phpexcel.Classes.PHPExcel.IOFactory");

//        $PHPExcel = new \PHPExcel();

        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if(!$PHPReader->canRead($filePath)){
            $PHPReader = new \PHPExcel_Reader_Excel5();
                if(!$PHPReader->canRead($filePath)){
                    echo 'no Excel';
                    return;
                }
        }

        $PHPExcel = $PHPReader->load($filePath);
        $currentSheet = $PHPExcel->getSheet(0);    //读取excel中第一个工作表
        $allColumn = $currentSheet->getHighestColumn();   //取得最大序号
        $allRow = $currentSheet->getHighestDataRow();     //一共有多少行

        //从第二行开始  因为第一行是列名
        for ($currentRow = 1; $currentRow <= $allRow; $currentRow ++){
            for($currentColumn = 'A'; $currentColumn <= $allColumn; $currentColumn ++){
                $address = $currentColumn . $currentRow;
                $data[$currentRow][$currentColumn] = $currentSheet->getCell($address)->getValue();
            }
        }
        $key = $data[1];
        for ($j=2;$j<=count($data);$j++) {
            $datas = [
                'compony' => $data[$j]['A'],
                'sex' => $data[$j]['B'],
                'email' => $data[$j]['C'],
                'address' => $data[$j]['D'],
                'group_id' => $data[$j]['E'],
                'name' => $data[$j]['F'],
                'phone' => $data[$j]['G'],
                'remarks' => $data[$j]['H'],
            ];
            $group = TelephoneGroupModel::same($datas['group_id']);
//            dump($group[0]['id']);

            if (empty($group[0])) {
                return ['status'=>1 , 'massage' => '没有此组名'];
            }else {
                $datas['group_id'] = $group[0]['id'];
            }

            if ($datas['sex'] == '男') {
                $datas['sex'] = 1;
            }else if ($datas['sex'] == '女') {
                $datas['sex'] = 2;
            }else {
                return ['status'=>1 , 'massage' => '性别请填男或女'];
            }
            $regex = "/^1[34578]\d{9}$/";
            $email = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/";
            if (!preg_match($regex,$datas['phone'])) {
                return ['status'=>1 , 'massage' => '手机号格式不正确'];
            }
            if (!preg_match($email,$datas['email'])) {
                return ['status'=>1 , 'massage' => '邮箱格式不正确'];
            }
            $result = TelephoneDirectoryModel::add_Directory($datas);
        }
        return ['status'=>'0','massage' => '添加成功'];
    }


    public function excelUpload($file)
    {
        if(!empty($file['file']['name'])){
            $tmp_file = $file['file']['tmp_name'];
            $file_types = explode('.',$file['file']['name']);
            $file_type = $file_types [count ( $file_types ) - 1];

            if (strtolower ( $file_type ) != "xlsx" && strtolower ( $file_type ) != "xls"){
                return ( '不是Excel文件，重新上传' );
            }

//            $savePath = Excel_PATH.'Excel/';
//            $str = date ( 'Ymdhis' );
//            $file_name = $str . "." . $file_type;
//
//            if (! copy ( $tmp_file, $savePath . $file_name )){
//                return ("上传失败");
//            }

            $data = self::importExcel($tmp_file);


            return $data;

        }
    }


}