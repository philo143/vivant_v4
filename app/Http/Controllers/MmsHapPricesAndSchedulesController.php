<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MmsHapPriceAndSchedule;
use Carbon\Carbon;
use PHPExcel; 
use PHPExcel_IOFactory;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Helper_HTML;
use PHPExcel_Style_Alignment;
use Response;
class MmsHapPricesAndSchedulesController extends Controller
{
    

	public function __construct()
    {
        $this->middleware('auth');
    } //


    public function mmsReportIndex(){

    	$regions = array('LUZON' => 'LUZON', 'VISAYAS' => 'VISAYAS', 'MINDANAO' => 'MINDANAO');

    	$types = array('GEN' => 'GEN', 'LOAD' => 'LOAD');

    	return view('mms_data.hap.list',compact('regions','types'));


    } // eof



    private function generate_data($request){
    	$date = Carbon::createFromTimestamp(strtotime($request['date']))->format('Ymd');

        $resource_ids = explode(',',$request['resource_id']);
        $hours = explode(',',$request['hour']);
        $interval_l = explode(',',$request['interval']);
        $intervals = array();
        foreach ($interval_l as $value) {
          $intervals[] = $value;
        }

        $query = MmsHapPriceAndSchedule::query();
        $query = $query->whereDate('interval_end', $date);

        if ( count($hours) > 0 ) {
          $hours = "'".implode("','",$hours)."'";
          $query = $query->whereRaw('hour(interval_end) IN ('.$hours.')');
        }

        if ( count($intervals) > 0 ) {
            $instring_intervals = "'" . implode("','", $intervals) . "'";
            $query = $query->whereRaw("minute(interval_end) in (".$instring_intervals.")");
        }

        if ( count($resource_ids) > 0 ) {
          $query = $query->whereIn('price_node', $resource_ids);
        }
        $query->orderBy('run_time','ASC')->get();
        $records = $query->get();
        $data = array();
        $resource_list = array();
        foreach ($records as $record) {
              $resource_id = $record->price_node;
              $delivery_date = Carbon::createFromTimestamp(strtotime($record->interval_end))->format('Y-m-d');
              $delivery_hour = Carbon::createFromTimestamp(strtotime($record->interval_end))->format('H');
              $interval = Carbon::createFromTimestamp(strtotime($record->interval_end))->format('H:i:s');
             
              $data[$delivery_date][$delivery_hour][$interval][$resource_id] = $record;

              if (  !in_array($resource_id, $resource_list)  ) {
              		$resource_list[] = $resource_id;	
              }
         }


         $return = array(
     		'list' => $data,
     		'resource_list' => $resource_list
     	);

        return $return;
    }

    public function retrieve( Request $request ){
    	$data = $this->generate_data($request->all());
        return $data;
    } // eof


    public function file( Request $request ){
    	$ret = $this->generate_data($request->all());

    	$data = $ret['list'];
    	$resource_list = $ret['resource_list'];

    	$hours = explode(',',$request['hour']);
        $interval_l = explode(',',$request['interval']);
        $intervals = array();
        foreach ($interval_l as $value) {
          $intervals[] = $value;
        }

        $type = request('type');
        $file_date = Carbon::createFromTimestamp(strtotime(request('date')))->format('Ymd');
        $dte = Carbon::createFromTimestamp(strtotime(request('date')))->format('Y-m-d');
        $filename = 'HAP_'. $type. '_'.$file_date .  '.xlsx';


        // GENERATE EXCEL FILE 
        $formatted_date = Carbon::createFromTimestamp(strtotime(request('date')))->format('F d, Y');
        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setShowGridlines(false);
        $sheet->setTitle('HAP');
        $sheet->getDefaultColumnDimension()->setWidth(15);
        $sheet->setCellValue('B2','Hour Ahead Projections');
        $sheet->setCellValue('B3',$formatted_date );

        
        $sheet->setCellValue('B5','Hour');
        $sheet->setCellValue('C5','Interval');
        $letter = 'D';
        $start_letter = $letter;
        $last_letter = $letter;
        foreach ($resource_list as $resource_id) {
        	$start_letter = $letter;
    		$sheet->setCellValue($letter . '5', $resource_id );
    		$letter++;
    		$last_letter = $letter;
    		$sheet->mergeCells($start_letter . '5:'.$last_letter.'5');

    		$sheet->setCellValue($start_letter . '6', 'Price' );
    		$sheet->setCellValue($last_letter . '6', 'MW' );

    		$letter++;
    	}
    	$sheet->mergeCells('B5:B6');
    	$sheet->mergeCells('C5:C6');
    	$sheet->mergeCells('B2:'.$last_letter.'2');
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
                'font' => array('bold' => true, 'size' => '14')
                , 'alignment' => array(
                    'horizontal' => 'left',
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

        foreach ($hours as $hr) {
        	foreach ($intervals as $int_) {
                $prev_hr = $hr ;

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
				foreach ($resource_list as $resource_id) {
					$price = '';
					$mw = '';
					if ( isset($data[$dte]) ) {
						

						if ( isset($data[$dte][$hr]) ) {

							if ( isset($data[$dte][$hr][$int]) ) {
								

								if ( isset($data[$dte][$hr][$int][$resource_id]) ) {

									$price = $data[$dte][$hr][$int][$resource_id]['lmp'];
									$mw = $data[$dte][$hr][$int][$resource_id]['mw'];

								}

							}

						}

					}

	        		$sheet->setCellValue($letter . $row_ctr, $price );
	        		$letter++;
	        		$sheet->setCellValue($letter . $row_ctr, $mw );
	        		$letter++;
	        	}
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

        // END FILE GENERATION


        return Response::download($filename,$filename, 
           [
           'Content-Description' => "File Transfer",
            "Content-Disposition" => "attachment; filename=".$filename]
            )->deleteFileAfterSend(true);

         


    } // eof


}
