<?php
/**
 * .___                            .____          ___.          .__
 * |   | ______ ________ __   ____ |    |   _____ \_ |__   ____ |  |
 * |   |/  ___//  ___/  |  \_/ __ \|    |   \__  \ | __ \_/ __ \|  |
 * |   |\___ \ \___ \|  |  /\  ___/|    |___ / __ \| \_\ \  ___/|  |__
 * |___/____  >____  >____/  \___  >_______ (____  /___  /\___  >____/
 *
 * Author: andyweijin
 * CreateTime: 18/10/30 下午4:11
 *
 */

namespace App\Library\Common;

class MyExcel
{
    public $base_path = '/download/excel/';

    /* 计算列 */
    private function cellIndex($index)
    {
        $mod = $index % 26;
        $count = floor($index / 26);
        $str = chr($mod + 65);
        if ($count > 0) {
            $str = $this->cellIndex($count - 1) . $str;
        }
        return $str;
    }

    // 导入
    public function importExcel($file, $header=[])
    {
        if(!is_file($file)) return false;
        $return = [];
        $fields = array_keys($header);
        $sheet = \PHPExcel_IOFactory::load($file)->getActiveSheet();
        // 列数不一样判断
        $origin_sign = $sheet->getHighestColumn();
        $sign = $this->cellIndex(count($header) - 1);
        if($origin_sign != $sign){
            throw new \Exception('导入数据的列数和模版不符');
        }
        foreach ($sheet->getRowIterator() as $h=>$row) {
            $c = $row->getCellIterator();
            $c->setIterateOnlyExistingCells(false);
            foreach ($c as $k=>$unit) {
                if($h > 1){
                    $return[$h][$fields[$k]] = trim($unit->getValue());
                }
            }
        }
        return $return;
    }

    // 导出
    public function exportExcel(array $data=[], array $header=[], $file_name)
    {
        if(empty($header)) return false;
        $excel = new \PHPExcel();
        $excel->getProperties()
            ->setCreator('andyweijin')
            ->setDescription('数据模板');
        $cell = $excel->setActiveSheetIndex(0);
        for($i=0;$i<=count($data);$i++){
            $j=0;
            foreach($header as $field=>$title){
                $lie = $this->cellIndex($j);
                $cell->getRowDimension($i+1)->setRowHeight(20);
                $cell->getColumnDimension($lie)->setWidth(15);
                $sign = $lie . ($i + 1);
                if($i == 0){
                    $cell->getStyle($sign)->applyFromArray([
                            'font' => ['bold' => true],
                            'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER]]
                    );
                    $cell->setCellValue($sign, $title);
                }else{
                    $v = @$data[$i-1][$field];
                    //可以自定义数据格式
                    //$cell->getStyle($lie)->getNumberFormat()->setFormatCode('#,##');
                    //$cell->getStyle($lie)->getNumberFormat()->setFormatCode('0.00%');
                    $cell->getStyle($lie)->getAlignment()->setWrapText(true); // 自动换行
                    $cell->setCellValue($sign, $v);
                }
                $j++;
            }
        }
        $file_name = $file_name . '_' . date('YmdHis') . '.xlsx';
        $save_path = $this->base_path . date('Ym') . '/';
        //调整到public资源目录
        $path = BASE_PATH . $save_path;
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }
        $writer = new \PHPExcel_Writer_Excel2007($excel);
        $writer->save($path  . $file_name);
        return $save_path  . $file_name;
    }
}