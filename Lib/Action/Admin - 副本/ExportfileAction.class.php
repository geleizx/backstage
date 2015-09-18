<?php
class ExportfileAction extends Action
{

    public function __construct()
    {
        parent::__construct();
        $str = file_get_contents("dsm.txt");
        $tvlist = json_decode($str,1);
        $this->expProduct($tvlist);

    }

    function expProduct($array)
    {
        $xlsName = "program";

        $xlsCell = array(
            array('index', '编号'),
            array('station', '子频道'),
            array('logo', '图标'),
            array('link_data', '链接'),
        );

        $tvarray=array();

                foreach($array['ChannelGroupList'] as $key=>$value){

                    foreach($value[channelList] as $k=>$v){
                        array_push($tvarray,$v);
                        $tvarray[$v][index];
                        $tvarray[$v][station];
                        $tvarray[$v][logo];
                        $tvarray[$v][link_data];

                    }
                }
//

        $this->exportExcel($xlsName, $xlsCell, $tvarray);
    }

    public function exportExcel($expTitle,$expCellName,$expTableData){
        ob_end_clean();
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = $_SESSION['user']['nickname'].date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
//        vendor("PHPExcel.PHPExcel");
        import('ORG.PHPExcel.PHPExcel');//
        $objPHPExcel = new PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
        // $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
        }
        // Miscellaneous glyphs, UTF-8
        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
            }
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
}
