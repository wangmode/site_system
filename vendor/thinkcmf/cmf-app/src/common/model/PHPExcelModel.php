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

class PHPExcelModel
{


    /**
     * 导出消息记录
     * @param $data
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function explodeSendRecordList($data)
    {
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', '签名名称')
            ->setCellValue('C1', '模板名称')
            ->setCellValue('D1', '产品类别')
            ->setCellValue('E1', '手机号码')
            ->setCellValue('F1', '发送内容')
            ->setCellValue('G1', '状态')
            ->setCellValue('H1', '发送时间');

        //设置A列水平居中
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置单元格宽度
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(35);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(20);

        //6.循环刚取出来的数组，将数据逐一添加到excel表格。
        for($i=0;$i<count($data);$i++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+2),$data[$i]['id']);

            $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+2),$data[$i]['sign']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+2),$data[$i]['name']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+2),$data[$i]['product_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+2),$data[$i]['phone']);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+2),$data[$i]['content']);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($i+2),$data[$i]['status']);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.($i+2),$data[$i]['send_time']);
        }

        //7.设置保存的Excel表格名称
        $filename = '消息记录'.date('YmdHis',time()).'.xlsx';
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('消息记录');
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
     * 导出扣费记录
     * @param $data
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function explodeChargeRecordList($data)
    {
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', '签名名称')
            ->setCellValue('C1', '模板名称')
            ->setCellValue('D1', '产品类别')
            ->setCellValue('E1', '计费条数')
            ->setCellValue('F1', '发送内容')
            ->setCellValue('G1', '计费金额')
            ->setCellValue('H1', '创建时间');

        //设置A列水平居中
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置单元格宽度
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(35);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(20);

        //6.循环刚取出来的数组，将数据逐一添加到excel表格。
        for($i=0;$i<count($data);$i++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+2),$data[$i]['id']);

            $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+2),$data[$i]['sign']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+2),$data[$i]['name']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+2),$data[$i]['product_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+2),$data[$i]['num']);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+2),$data[$i]['content']);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($i+2),$data[$i]['total_price']);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.($i+2),$data[$i]['create_at']);
        }

        //7.设置保存的Excel表格名称
        $filename = '扣费记录'.date('YmdHis',time()).'.xlsx';
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('扣费记录');
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
     * @param $data
     * @return string
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function explodeCustomerList($data)
    {
        vendor("PHPoffice.Classes.PHPExcel");
        vendor("PHPoffice.Classes.Writer.Excel5");
        vendor("PHPoffice.Classes.Writer.Excel2007");
        vendor("PHPoffice.Classes.IOFactory");

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', '公司名称')
            ->setCellValue('C1', '联系人')
            ->setCellValue('D1', '联系方式')
            ->setCellValue('E1', '客户关键字')
            ->setCellValue('F1', 'URL')
            ->setCellValue('G1', '代理商')
            ->setCellValue('H1', '业务员')
            ->setCellValue('I1', '联系状态')
            ->setCellValue('J1', '客户状态')
            ->setCellValue('K1', '创建时间')
            ->setCellValue('L1', '发放时间')
            ->setCellValue('M1', '备注');

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
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('I')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('J')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('K')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('M')->setWidth(40);


        //6.循环刚取出来的数组，将数据逐一添加到excel表格。
        for($i=0;$i<count($data);$i++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+2),$data[$i]['id']);//ID

            $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+2),$data[$i]['title']);//公司名称
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+2),$data[$i]['name']);//联系人
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+2),$data[$i]['tel']);//联系方式
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+2),$data[$i]['keyword']);//客户关键字
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+2),$data[$i]['url']);//URL
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($i+2),$data[$i]['agent_name']);//代理商
            $objPHPExcel->getActiveSheet()->setCellValue('H'.($i+2),$data[$i]['admin_name']);//业务员
            $objPHPExcel->getActiveSheet()->setCellValue('I'.($i+2),CustomerInfoModel::getIsContactName($data[$i]['is_contact']));//联系状态
            $objPHPExcel->getActiveSheet()->setCellValue('J'.($i+2),CustomerInfoModel::getStatusName($data[$i]['status']));//客户状态
            $objPHPExcel->getActiveSheet()->setCellValue('K'.($i+2),date('Y-m-d H:i:s',$data[$i]['create_time']));//创建时间
            $objPHPExcel->getActiveSheet()->setCellValue('L'.($i+2),date('Y-m-d H:i:s',$data[$i]['grant_time']));//发放时间
            $objPHPExcel->getActiveSheet()->setCellValue('M'.($i+2),$data[$i]['remark']);//备注
        }
        //7.设置保存的Excel表格名称
        $filename = '客户资源列表'.date('YmdHis',time()).'.xlsx';
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('客户资源列表');
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

        return $data;
    }


    public function excelUpload()
    {
        if(!empty($_FILES['file_stu']['name'])){
            $tmp_file = $_FILES['file_stu']['tem_name'];
            $file_types = explode('.',$_FILES['file_stu']['name']);
            $file_type = $file_types [count ( $file_types ) - 1];

            if (strtolower ( $file_type ) != "xlsx" && strtolower ( $file_type ) != "xls"){
                return ( '不是Excel文件，重新上传' );
            }

            $savePath = Excel_PATH.'Excel/';
            $str = date ( 'Ymdhis' );
            $file_name = $str . "." . $file_type;

            if (! copy ( $tmp_file, $savePath . $file_name )){
                return ("上传失败");
            }
            $data = self::importExcel( $savePath . $file_name );

            $res = array();

            //循环添加数据库

        }
    }


}