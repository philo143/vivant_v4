<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\MmsModLmp;
use PHPExcel; 
use PHPExcel_IOFactory;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Helper_HTML;
use PHPExcel_Style_Alignment;
use Response;
class MmsRTDPricesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    } //


    public function mmsReportIndex(){

    	$regions = array('LUZON' => 'LUZON', 'VISAYAS' => 'VISAYAS', 'MINDANAO' => 'MINDANAO');

    	$types = array('GEN' => 'GEN', 'LOAD' => 'LOAD');


    	return view('mms_data.lmp.list',compact('regions','types'));


    } // eof


    public function retrieve( Request $request ){
    	$s_date = Carbon::createFromTimestamp(strtotime(request('sdate')))->format('Ymd');
        $e_date = Carbon::createFromTimestamp(strtotime(request('edate')))->format('Ymd');

        $resource_ids = explode(',',$request['resource_id']);
        $hours = explode(',',$request['hour']);
        $interval_l = explode(',',$request['interval']);
        $intervals = array();
        foreach ($interval_l as $value) {
          // $tme = '00:'. str_pad($value,2,"0",STR_PAD_LEFT) . ':00';
          $intervals[] = $value;
        }

        $query = MmsModLmp::query();
        $query = $query->whereBetween('date', [$s_date,$e_date]);

        if ( count($hours) > 0 ) {
            $instring_hours = "'" . implode("','", $hours) . "'";
            $query = $query->whereRaw("( if ( `interval` = '00:00:00', ( 24 ) , ( if(minute(`interval`) = 0 , hour(`interval`) , hour(`interval`) + 1) ) )   ) in (".$instring_hours.")");
        }

        if ( count($intervals) > 0 ) {
            $instring_intervals = "'" . implode("','", $intervals) . "'";
            $query = $query->whereRaw("minute(`interval`) in (".$instring_intervals.")");
            //$query = $query->whereIn('interval', $intervals);
        }

        if ( count($resource_ids) > 0 ) {
          $query = $query->whereIn('price_node', $resource_ids);
        }

        $records = $query->get();
        $data = array();
        foreach ($records as $record) {
              $resource_id = $record->price_node;
              $delivery_date = $record->date;
              
              $tmp_time = explode(':',$record->interval);
              $delivery_hour = Carbon::createFromTime($tmp_time[0],$tmp_time[1],$tmp_time[2],'Asia/Manila')->hour;
              if ( $record->interval == '00:00:00' ) {
                    $delivery_hour = 24;
              }else {
                if ($tmp_time[1] != '00') {
                    $delivery_hour = $delivery_hour+1;
                }
              }
              

              $interval = $record->interval;
             
              $data[$delivery_date][$delivery_hour][$interval][$resource_id] = $record;
          }

        return $data;
    } // eof



    public function file( Request $request ){
    	$s_date = Carbon::createFromTimestamp(strtotime(request('sdate')));
        $e_date = Carbon::createFromTimestamp(strtotime(request('edate')));

        $resource_ids = explode(',',$request['resource_id']);
        $hours = explode(',',$request['hour']);
        $interval_l = explode(',',$request['interval']);
        $intervals = array();
        foreach ($interval_l as $value) {
          $intervals[] = $value;
        }

        $query = MmsModLmp::query();
        $query = $query->whereBetween('date', [$s_date,$e_date]);

        if ( count($hours) > 0 ) {
            $instring_hours = "'" . implode("','", $hours) . "'";
            $query = $query->whereRaw("( if ( `interval` = '00:00:00', ( 24 ) , ( if(minute(`interval`) = 0 , hour(`interval`) , hour(`interval`) + 1) ) )   ) in (".$instring_hours.")");
        }

        if ( count($intervals) > 0 ) {
            $instring_intervals = "'" . implode("','", $intervals) . "'";
            $query = $query->whereRaw("minute(`interval`) in (".$instring_intervals.")");
            //$query = $query->whereIn('interval', $intervals);
        }

        if ( count($resource_ids) > 0 ) {
          $query = $query->whereIn('price_node', $resource_ids);
        }

        $records = $query->get();
        $data = array();
        $resource_list = array();
        foreach ($records as $record) {
              $resource_id = $record->price_node;
              $delivery_date = $record->date;
              
              $tmp_time = explode(':',$record->interval);
              $delivery_hour = Carbon::createFromTime($tmp_time[0],$tmp_time[1],$tmp_time[2],'Asia/Manila')->hour;
              if ( $record->interval == '00:00:00' ) {
                    $delivery_hour = 24;
              }else {
                if ($tmp_time[1] != '00') {
                    $delivery_hour = $delivery_hour+1;
                }
              }

              $interval = $record->interval;
              
              if (!in_array($resource_id, $resource_list)) {
                $resource_list[] = $resource_id;
              }

              $data[$delivery_date][$delivery_hour][$interval][$resource_id] = $record;
          }

        
        $file_format = request('file_format');
        $file_s_date = Carbon::createFromTimestamp(strtotime(request('sdate')))->format('Ymd');
        $file_e_date = Carbon::createFromTimestamp(strtotime(request('edate')))->format('Ymd');

        $file = 'REP_LMP_'.$file_s_date . '_' . $file_e_date;
        $file_ext = $file_format == 'excel' ? '.xlsx' : '.csv';
        $filename = $file . $file_ext;

        if ( $file_format == 'csv' ) {
            $this->exportCSVFile($filename,$s_date,$e_date,$data,$resource_list,$hours,$intervals);
            

        } else {
            $this->exportExcelFile($filename,$s_date,$e_date,$data,$resource_list,$hours,$intervals);

           return Response::download($filename,$filename, 
           [
           'Content-Description' => "File Transfer",
            "Content-Disposition" => "attachment; filename=".$filename]
            )->deleteFileAfterSend(true);
        }

         


    } // eof


    private function exportExcelFile( $filename,$sdate,$edate,$data,$resource_list,$hour_list,$interval_list ) {

        $formatted_sdate = Carbon::createFromTimestamp(strtotime($sdate))->format('F d, Y');
        $formatted_edate = Carbon::createFromTimestamp(strtotime($edate))->format('F d, Y');

        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setShowGridlines(false);
        $sheet->setTitle('LOCATIONAL_MARGINAL_PRICES');
        $sheet->getDefaultColumnDimension()->setWidth(15);
        $sheet->setCellValue('B2','Locational Marginal Prices');
        $sheet->setCellValue('B3',$formatted_sdate . ' to ' . $formatted_edate);

        $sheet->setCellValue('B5','Date');
        $sheet->mergeCells('B5:C5');

        // first header
        $letter = 'D';
        $date_list = array();
        foreach ($data as $dte => $list) {
        	$dte_formatted = Carbon::createFromTimestamp(strtotime($dte))->format('Ymd');
        	$date_list[] = $dte;
        	foreach ($resource_list as $resource_id) {
        		$sheet->setCellValue($letter . '5', $dte_formatted );
        		$last_letter = $letter;
        		$letter++;
        	}

        }
        

        // second header
        $sheet->setCellValue('B6','Hour');
        $sheet->setCellValue('C6','Interval');
        $letter = 'D';
        foreach ($data as $dte => $list) {
        	foreach ($resource_list as $resource_id) {
        		$sheet->setCellValue($letter . '6', $resource_id );
        		$letter++;
        	}

        }


        $sheet->mergeCells('B2:'.$last_letter.'2');
        $sheet->mergeCells('B3:'.$last_letter.'3');

        $sheet->getDefaultColumnDimension()->setWidth(15);

        
        $sheet->getStyle('B5:'.$last_letter.'6')->applyFromArray(
            array( 'alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
                , 'font' => array('bold' => true)
        ));


        $sheet->getStyle('B2')->applyFromArray(
            array( 
                'font' => array('bold' => true, 'size' => '20')
                , 'alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                ) 
            )
            );

        $sheet->getStyle('B3')->applyFromArray(
            array( 
                'font' => array('bold' => true, 'size' => '13')
                , 'alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                ) 
            ));
            

        $row_ctr = 7;

        foreach ($hour_list as $hr) {
        	foreach ($interval_list as $int_) {
				// $tmp = explode(':',$int)[1];
				// $interval_formatted = '00:'.$tmp . 'H';

                $prev_hr = $hr - 1;

                if ($int_ == '00') {
                    $int = str_pad($hr,2,"0",STR_PAD_LEFT) . ':' . str_pad($int_,2,"0",STR_PAD_LEFT) . ':00';
                    $interval_formatted = str_pad($hr,2,"0",STR_PAD_LEFT) . ':'.str_pad($int_,2,"0",STR_PAD_LEFT) . 'H';
                }else {
                    $int = str_pad($prev_hr,2,"0",STR_PAD_LEFT) . ':' . str_pad($int_,2,"0",STR_PAD_LEFT) . ':00';
                    $interval_formatted = str_pad($prev_hr,2,"0",STR_PAD_LEFT) . ':'.str_pad($int_,2,"0",STR_PAD_LEFT) . 'H';
                }

        		$sheet->setCellValue('B'.$row_ctr,$hr);
        		$sheet->setCellValue('C'.$row_ctr,$interval_formatted);

        		$letter = 'D';
				foreach ($date_list as $dte) {

					foreach ($resource_list as $resource_id) {
						$price = '';

						if ( isset($data[$dte]) ) {
							

							if ( isset($data[$dte][$hr]) ) {

								if ( isset($data[$dte][$hr][$int]) ) {
									

									if ( isset($data[$dte][$hr][$int][$resource_id]) ) {

										$price = $data[$dte][$hr][$int][$resource_id]['lmp'];

									}

								}

							}

						}

		        		$sheet->setCellValue($letter . $row_ctr, $price );
		        		$letter++;
		        	}

        		} // foreach date_list

        		$row_ctr++;
        	} // foreach interval
        } //

        
        $last_row_ctr = $row_ctr -1;

        $sheet->getStyle('B5' . ':'.$last_letter . $last_row_ctr)->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => '000000'),
                )
            )
        ));


        $sheet->getStyle('A5' . ':'.$last_letter . $last_row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
        ));

        $sheet->getStyle('D7' . ':'.$last_letter . $last_row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'right',
                    'vertical' => 'center'
                )
        ));

         $sheet->getStyle('D7:' .$last_letter. $last_row_ctr)->getNumberFormat()->setFormatCode('###,###,###,##0.00');

        $writer = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007','HTML');
        $writer->save($filename);

    } // eof exportExcelFile


    private function exportCSVFile( $filename,$sdate,$edate,$data,$resource_list,$hour_list,$interval_list ) {

        $formatted_sdate = Carbon::createFromTimestamp(strtotime($sdate))->format('F d Y');
        $formatted_edate = Carbon::createFromTimestamp(strtotime($edate))->format('F d Y');


        $date_list = array();
        

        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=".$filename);
        header("Pragma: no-cache");
        header("Expires: 0");
        $file = fopen('php://output', 'w');                             
        fputcsv($file, array('Locational Marginal Prices'));
        fputcsv($file, array($formatted_sdate . ' to ' . $formatted_edate));           

        $first_header = array();
        $first_header[] = 'Date';
        $first_header[] = '';
        
        foreach ($data as $dte => $list) {
        	$dte_formatted = Carbon::createFromTimestamp(strtotime($dte))->format('Ymd');
        	$date_list[] = $dte;
        	foreach ($resource_list as $resource_id) {
        		$first_header[] =  $dte_formatted;
        	}
        }
        fputcsv($file, $first_header);    


        // second header
        $second_header = array();
        $second_header[] = 'Hour';
        $second_header[] = 'Interval';
        
        foreach ($data as $dte => $list) {
        	foreach ($resource_list as $resource_id) {
        		$second_header[] = $resource_id;
        	}
        }
        fputcsv($file, $second_header);    

        
        $records = array();
        
        foreach ($hour_list as $hr) {
        	foreach ($interval_list as $int_) {
				$prev_hr = $hr - 1;

                if ($int_ == '00') {
                    $int = str_pad($hr,2,"0",STR_PAD_LEFT) . ':' . str_pad($int_,2,"0",STR_PAD_LEFT) . ':00';
                    $interval_formatted = str_pad($hr,2,"0",STR_PAD_LEFT) . ':'.str_pad($int_,2,"0",STR_PAD_LEFT) . 'H';
                }else {
                    $int = str_pad($prev_hr,2,"0",STR_PAD_LEFT) . ':' . str_pad($int_,2,"0",STR_PAD_LEFT) . ':00';
                    $interval_formatted = str_pad($prev_hr,2,"0",STR_PAD_LEFT) . ':'.str_pad($int_,2,"0",STR_PAD_LEFT) . 'H';
                }
                
				$record = array();
				$record[] = $hr;
				$record[] = $interval_formatted;
        		
        		foreach ($date_list as $dte) {

					foreach ($resource_list as $resource_id) {
						$price = '';

						if ( isset($data[$dte]) ) {
							

							if ( isset($data[$dte][$hr]) ) {

								if ( isset($data[$dte][$hr][$int]) ) {
									

									if ( isset($data[$dte][$hr][$int][$resource_id]) ) {

										$price = $data[$dte][$hr][$int][$resource_id]['lmp'];

									}

								}

							}

						}

		        		$record[] = $price;
		        	}

        		} // foreach date_list

        		fputcsv($file, $record);    


        	} // foreach interval
        } //

        exit();

    } // eof exportCSVFile

}
