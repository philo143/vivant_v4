<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use PHPExcel; 
use PHPExcel_IOFactory;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Helper_HTML;
use PHPExcel_Style_Alignment;
use Response;
use App\MmsModRtd;
use App\MmsModLmp;

class MmsRtdsSchedulesController extends Controller
{
    

	public function __construct()
    {
        $this->middleware('auth');
    } //


    public function mmsReportIndex(){

    	return view('mms_data.rtd_schedules_and_prices.list');


    } // eof


    private function data( $sdate, $edate, $resource_id, $hour, $interval, $is_show_schedule, $is_show_price ){

        $s_date = Carbon::createFromTimestamp(strtotime($sdate))->format('Y-m-d');
        $e_date = Carbon::createFromTimestamp(strtotime($edate))->format('Y-m-d');

        $resource_ids = explode(',',$resource_id);
        $hours = explode(',',$hour);
        $interval_l = explode(',',$interval);
        $intervals = array();
        foreach ($interval_l as $value) {
          $intervals[] = $value;
        }

        ## Get RTD Schedules data 
        $rtd_data = array();
        $resource_id_list = array();
        $delivery_date_list = array();
        
        if ( $is_show_schedule == 1 ) {
            $query = MmsModRtd::query();
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

            $rtds = $query->get();

            foreach ($rtds as $rtd) {
                  $resource_id = $rtd->price_node;
                  $delivery_date = $rtd->date;

                  $tmp_time = explode(':',$rtd->interval);
                  $delivery_hour = Carbon::createFromTime($tmp_time[0],$tmp_time[1],$tmp_time[2],'Asia/Manila')->hour;
                  if ( $rtd->interval == '00:00:00' ) {
                        $delivery_hour = 24;
                  }else {
                    if ($tmp_time[1] != '00') {
                        $delivery_hour = $delivery_hour+1;
                    }
                  }

                  $interval = $rtd->interval;
                  $rtd_data[$delivery_date][$delivery_hour][$interval][$resource_id] = $rtd;

                  if (!in_array($resource_id, $resource_id_list)) {
                      $resource_id_list[] = $resource_id;
                  }

                  if (!in_array($delivery_date, $delivery_date_list)) {
                      $delivery_date_list[] = $delivery_date;
                  }
              }
        }
        


        ## Get RTD Prices data 
        $price_data = array();
        if ( $is_show_price == 1) {
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

            $prices = $query->get();
            foreach ($prices as $price) {
                  $resource_id = $price->price_node;
                  $delivery_date = $price->date;

                  $tmp_time = explode(':',$price->interval);
                  $delivery_hour = Carbon::createFromTime($tmp_time[0],$tmp_time[1],$tmp_time[2],'Asia/Manila')->hour;
                  if ( $price->interval == '00:00:00' ) {
                        $delivery_hour = 24;
                  }else {
                    if ($tmp_time[1] != '00') {
                        $delivery_hour = $delivery_hour+1;
                    }
                  }

                  $interval = $price->interval;
                  $price_data[$delivery_date][$delivery_hour][$interval][$resource_id] = $price;

                  if (!in_array($resource_id, $resource_id_list)) {
                      $resource_id_list[] = $resource_id;
                  }

                  if (!in_array($delivery_date, $delivery_date_list)) {
                      $delivery_date_list[] = $delivery_date;
                  }
              }
        }
        

          sort($delivery_date_list);
          sort($resource_id_list);
          $data = array(
            'rtd_data' => $rtd_data,
            'price_data' => $price_data,
            'delivery_date_list' => $delivery_date_list,
            'resource_id_list' => $resource_id_list
          );


        return $data;
    }

    public function retrieve( Request $request ){
    	$sdate = request('sdate');
        $edate = request('edate');
        $resource_id = request('resource_id');
        $hour = request('hour');
        $interval = request('interval');
        $is_show_schedule = request('is_show_schedule');
        $is_show_price = request('is_show_price');

        $data = $this->data( $sdate, $edate, $resource_id, $hour, $interval , $is_show_schedule, $is_show_price);

        return $data;
    } // eof


    private function exportExcelFile( $filename,$sdate,$edate,$data,$resource_list
        ,$hour_list,$interval_list, $is_show_schedule, $is_show_price ) {


        $formatted_sdate = Carbon::createFromTimestamp(strtotime($sdate))->format('F d, Y');
        $formatted_edate = Carbon::createFromTimestamp(strtotime($edate))->format('F d, Y');

        $show_all_columns = 0;
        if ( $is_show_schedule == 1 && $is_show_price == 1) {
           $show_all_columns = 1;
        }

        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setShowGridlines(false);
        $sheet->setTitle('RTD_SCHEDULES_AND_PRICES');
        $sheet->getDefaultColumnDimension()->setWidth(15);
        $sheet->setCellValue('B2','RTD SCHEDULES AND PRICES');
        $sheet->setCellValue('B3',$formatted_sdate . ' to ' . $formatted_edate);
        

        ## First Header
        $sheet->setCellValue('B5','Date');
        $sheet->mergeCells('B5:B6');
        $sheet->setCellValue('C5','Hour');
        $sheet->mergeCells('C5:C6');
        $sheet->setCellValue('D5','Interval');
        $sheet->mergeCells('D5:D6');
        
        $letter = 'E';
        foreach ($resource_list as $resource_id) {
           $sheet->setCellValue($letter.'5',$resource_id);

           if ($show_all_columns == 1) {
                $s_letter = $letter;
                $letter++;
                $sheet->mergeCells($s_letter. '5:'.$letter.'5');
           }

           $letter++;
        }

        $sheet->setCellValue($letter.'5','Total');
        if ($show_all_columns == 1) {
            $s_letter = $letter;
            $letter++;
            $sheet->mergeCells($s_letter. '5:'.$letter.'5');
        }

        $last_letter = $letter;
        $sheet->mergeCells('B2:'.$last_letter.'2');
        $sheet->mergeCells('B3:'.$last_letter.'3');

        ## Second Header
        $letter = 'E';
        foreach ($resource_list as $resource_id) {

           if ( $is_show_schedule == 1) {
                $sheet->setCellValue($letter.'6','Sched');
           } 

           if ($show_all_columns == 1) {
                $letter++;
           }
           
           if ( $is_show_price == 1) {
                $sheet->setCellValue($letter.'6','Price');
           } 

           $letter++;
        }

       if ( $is_show_schedule == 1) {
            $sheet->setCellValue($letter.'6','Sched');
       } 

       if ($show_all_columns == 1) {
            $letter++;
       }
       
       if ( $is_show_price == 1) {
            $sheet->setCellValue($letter.'6','Price');
       } 
        

        $sheet->getDefaultColumnDimension()->setWidth(20);

        
        $sheet->getStyle('B5:'.$last_letter.'6')->applyFromArray(
            array('fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('argb' => 'b4c6e7')
                )
                , 'alignment' => array(
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
        $rtd_data = $data['rtd_data'];
        $price_data = $data['price_data'];
        $delivery_date_list = $data['delivery_date_list'];

        foreach ($delivery_date_list as $date) {
            foreach ($hour_list as $hr) {
                foreach ($interval_list as $int_) {

                    $prev_hr = $hr - 1;
                    if ($int_ == '00') {
                        $int = str_pad($hr,2,"0",STR_PAD_LEFT) . ':' . str_pad($int_,2,"0",STR_PAD_LEFT) . ':00';
                    }else {
                        $int = str_pad($prev_hr,2,"0",STR_PAD_LEFT) . ':' . str_pad($int_,2,"0",STR_PAD_LEFT) . ':00';
                    }
                    
                    $dte = Carbon::createFromTimestamp(strtotime($date))->format('m/d/Y');

                    $sheet->setCellValue('B'.$row_ctr,$dte);
                    $sheet->setCellValue('C'.$row_ctr,$hr);
                    $sheet->setCellValue('D'.$row_ctr,$int);

                    $total_mw = 0;
                    $total_price = 0;
                    $mw = '';
                    $price = '';

                    $letter = 'E';
                    foreach ($resource_list as $resource_id) {

                        $mw = '';
                        $price = '';


                        ## check schedule data
                        if ( isset($rtd_data[$date]) ) {
                            if ( isset($rtd_data[$date][$hr]) ) {
                                if ( isset($rtd_data[$date][$hr][$int]) ) {
                                    if ( isset($rtd_data[$date][$hr][$int][$resource_id]) ) {
                                        $rtd_resource_data = $rtd_data[$date][$hr][$int][$resource_id];
                                        $mw = $rtd_resource_data['mw'];
                                        $total_mw = $total_mw + $mw;
                                    }
                                }
                            }
                        }


                        ## check price data
                        if ( isset($price_data[$date]) ) {
                            if ( isset($price_data[$date][$hr]) ) {
                                if ( isset($price_data[$date][$hr][$int]) ) {
                                    if ( isset($price_data[$date][$hr][$int][$resource_id]) ) {
                                        $price_resource_data = $price_data[$date][$hr][$int][$resource_id];
                                        $price = $price_resource_data['lmp'];
                                        $total_price = $total_price + $price;
                                    }
                                }
                            }
                        }
                        

                        

                       if ( $is_show_schedule == 1) {
                            $sheet->setCellValue($letter.$row_ctr,$mw);
                       } 

                       if ($show_all_columns == 1) {
                            $letter++;
                       }
                       
                       if ( $is_show_price == 1) {
                            $sheet->setCellValue($letter.$row_ctr,$price);
                       } 
                        $letter++;
                    } // loop resources


                   if ( $is_show_schedule == 1) {
                        $sheet->setCellValue($letter.$row_ctr,$total_mw);
                   } 

                   if ($show_all_columns == 1) {
                        $letter++;
                   }
                   
                   if ( $is_show_price == 1) {
                        $sheet->setCellValue($letter.$row_ctr,$total_price);
                   } 
                   $row_ctr++;
                } // endforeach

                
            } // endforeach
        }

        $last_row_ctr = $row_ctr -1;

        $sheet->getStyle('B5' . ':'.$last_letter . $last_row_ctr)->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => '000000'),
                )
            )
        ));


        $sheet->getStyle('B5' . ':'.$last_letter . $last_row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
        ));

        $sheet->getStyle('E7' . ':'.$last_letter . $last_row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'right',
                    'vertical' => 'center'
                )
        ));

        $sheet->getStyle('E7:' .$last_letter. $last_row_ctr)->getNumberFormat()->setFormatCode('###,###,###,##0.00');

        $writer = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007','HTML');
        $writer->save($filename);

    } // eof exportExcelFile



    private function exportCSVFile( $filename,$sdate,$edate,$data,$resource_list,$hour_list,$interval_list , $is_show_schedule, $is_show_price) {

        $formatted_sdate = Carbon::createFromTimestamp(strtotime($sdate))->format('F d, Y');
        $formatted_edate = Carbon::createFromTimestamp(strtotime($edate))->format('F d, Y');


        $show_all_columns = 0;
        if ( $is_show_schedule == 1 && $is_show_price == 1) {
           $show_all_columns = 1;
        }

        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=".$filename);
        header("Pragma: no-cache");
        header("Expires: 0");
        $file = fopen('php://output', 'w');                             
        fputcsv($file, array('RTD Schedules and Prices'));
        fputcsv($file, array($formatted_sdate . ' to ' . $formatted_edate));  

        ### First header
        $headers = ['Date','Hour','Interval'];
        foreach ($resource_list as $resource_id) {
            $headers[] = $resource_id;
            if ( $show_all_columns == 1 ) {
                $headers[] = '';
            }
        }

        $headers[] = 'Total';
        if ( $show_all_columns == 1 ) {
            $headers[] = '';
        }       
        fputcsv($file,$headers); 


        #### Second Header
        $headers2 = ['','',''];
        foreach ($resource_list as $resource_id) {
            if ( $is_show_schedule == 1) {
                $headers2[] = 'Sched';
            }

            if ( $is_show_price == 1) {
                $headers2[] = 'Price';
            }
        }
        if ( $is_show_schedule == 1) {
            $headers2[] = 'Sched';
        }

        if ( $is_show_price == 1) {
            $headers2[] = 'Price';
        }
        fputcsv($file,$headers2); 


        $records = array();
        $rtd_data = $data['rtd_data'];
        $price_data = $data['price_data'];
        $delivery_date_list = $data['delivery_date_list'];

        foreach ($delivery_date_list as $date) {
            foreach ($hour_list as $hr) {
                foreach ($interval_list as $int_) {

                    $prev_hr = $hr - 1;
                    if ($int_ == '00') {
                        $int = str_pad($hr,2,"0",STR_PAD_LEFT) . ':' . str_pad($int_,2,"0",STR_PAD_LEFT) . ':00';
                    }else {
                        $int = str_pad($prev_hr,2,"0",STR_PAD_LEFT) . ':' . str_pad($int_,2,"0",STR_PAD_LEFT) . ':00';
                    }
                    
                    $dte = Carbon::createFromTimestamp(strtotime($date))->format('m/d/Y');

                    $record = array();
                    $record[] = $dte;
                    $record[] = $hr;
                    $record[] = $int;

                    $total_mw = 0;
                    $total_price = 0;
                    $mw = '';
                    $price = '';

                    foreach ($resource_list as $resource_id) {

                        $mw = '';
                        $price = '';


                        ## check schedule data
                        if ( isset($rtd_data[$date]) ) {
                            if ( isset($rtd_data[$date][$hr]) ) {
                                if ( isset($rtd_data[$date][$hr][$int]) ) {
                                    if ( isset($rtd_data[$date][$hr][$int][$resource_id]) ) {
                                        $rtd_resource_data = $rtd_data[$date][$hr][$int][$resource_id];
                                        $mw = $rtd_resource_data['mw'];
                                        $total_mw = $total_mw + $mw;
                                    }
                                }
                            }
                        }


                        ## check price data
                        if ( isset($price_data[$date]) ) {
                            if ( isset($price_data[$date][$hr]) ) {
                                if ( isset($price_data[$date][$hr][$int]) ) {
                                    if ( isset($price_data[$date][$hr][$int][$resource_id]) ) {
                                        $price_resource_data = $price_data[$date][$hr][$int][$resource_id];
                                        $price = $price_resource_data['lmp'];
                                        $total_price = $total_price + $price;
                                    }
                                }
                            }
                        }
                        
                        if ( $is_show_schedule == 1) {
                            $record[] = $mw;
                        }

                        if ( $is_show_price == 1) {
                            $record[] = $price;
                        }
                        
                    }

                    if ( $is_show_schedule == 1) {
                       $record[] = $total_mw;
                    }

                    if ( $is_show_price == 1) {
                       $record[] = $total_price;
                    }
                    
                    fputcsv($file,$record);  
                } // endforeach
            } // endforeach
        }
        
        exit();
    } // eof exportCSVFile


    public function file( Request $request ){
        $sdate = request('sdate');
        $edate = request('edate');
        $resource_id = request('resource_id');
        $hour = request('hour');
        $interval = request('interval');
        $is_show_schedule = request('is_show_schedule');
        $is_show_price = request('is_show_price');

        $hours = explode(',',$request['hour']);
        $interval_l = explode(',',$request['interval']);
        $intervals = array();
        foreach ($interval_l as $value) {
          $intervals[] = $value;
        }
        $s_date = Carbon::createFromTimestamp(strtotime($sdate));
        $e_date = Carbon::createFromTimestamp(strtotime($edate));


        $data = $this->data( $sdate, $edate, $resource_id, $hour, $interval , $is_show_schedule, $is_show_price);
        $resource_list = $data['resource_id_list'];

        $file_format = request('file_format');
        $file_s_date = Carbon::createFromTimestamp(strtotime($sdate))->format('Ymd');
        $file_e_date = Carbon::createFromTimestamp(strtotime($edate))->format('Ymd');

        $file = 'REP_RTD_SCHEDULES_AND_PRICES_'.$file_s_date . '_' . $file_e_date;
        $file_ext = $file_format == 'excel' ? '.xlsx' : '.csv';
        $filename = $file . $file_ext;

        if ( $file_format == 'csv' ) {
            $this->exportCSVFile($filename,$s_date,$e_date,$data,$resource_list,$hours,$intervals, $is_show_schedule, $is_show_price);
            

        } else {
            $this->exportExcelFile($filename,$s_date,$e_date,$data,$resource_list,$hours,$intervals, $is_show_schedule, $is_show_price);
            return Response::download($filename,$filename, 
               [
               'Content-Description' => "File Transfer",
                "Content-Disposition" => "attachment; filename=".$filename]
                )->deleteFileAfterSend(true);
        }

         
        

        
    }// eof file 

}
