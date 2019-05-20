<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Resource;
use App\MmsReserveRtdSchedule;
use App\NgcpSchedule;
use PHPExcel; 
use PHPExcel_IOFactory;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Helper_HTML;
use Response;

class MmsReserveRtdSchedulesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    } //


    public function mmsReportIndex(){

    	return view('mms_data.reserve_schedules.list');

    } // eof


    public function resourceList(Request $request){

        $resources = Resource::query()->whereHas('plant', function($q){
            $q->where('is_aspa', '=', 1);
        })->get();
       return $resources;
    } //


    private function generate_data($date,$resource_ids,$hours,$source){

        // get plant and unit number by resource id
        $query_resources = Resource::query()->with('plant');
        if ( count($resource_ids) > 0 ) {
          $query_resources = $query_resources->whereIn('resource_id', $resource_ids);
        }
        $resources_data = $query_resources->get();
        $resource_list = array();
        foreach ($resources_data as $row) {
              $resource_id = $row->resource_id;
              $plant = $row->plant->plant_name;
              $unit_no = 'Unit ' . $row->unit_no;

              $resource_list[$plant.'_'.$unit_no] = array(
                    'plant' => $plant ,
                    'unit_no' => $unit_no,
                    'resource_id' => $resource_id
                );
        } // end foreach


        $data = array();

        ############################################
        // ##### for mms reserve
        if ( in_array('mms', $source) ) {

            $query_res = MmsReserveRtdSchedule::query();
            $query_res = $query_res->where('delivery_date', $date);

            if ( count($hours) > 0 ) {
              $query_res = $query_res->whereIn('delivery_hour', $hours);
            }

            if ( count($resource_ids) > 0 ) {
              $query_res = $query_res->whereIn('resource_id', $resource_ids);
            }

            $mms_res_schedules = $query_res->get();
            foreach ($mms_res_schedules as $row) {
                  $resource_id = $row->resource_id;
                  $delivery_date = $row->delivery_date;
                  $delivery_hour = $row->delivery_hour;
                  $interval = $row->interval;
                  $reserve_class = $row->reserve_class;
                  
                  $row['source'] = 'MMS';
                  $key = $delivery_date.'|'.$resource_id . '|' . $reserve_class .'|MMS';

                  $data[$key][$delivery_hour] = $row;
            } // end foreach

        }
        



        ############################################
        ##### for ngcp

        if ( in_array('ngcp', $source) ) {

            $query_ngcp = NgcpSchedule::query();
            $query_ngcp = $query_ngcp->where('date', $date);

            $query_ngcp = $query_ngcp->where(function($query) use($resource_list){

                 $ctr = 1; 
                 foreach ($resource_list as $resource) {
                      $plant = $resource['plant'];
                      $unit_no = $resource['unit_no'];


                      if($ctr==1){
                            $query->whereRaw('plant = "'.$plant.'" and unit_no = "'.$unit_no.'"');
                        }else {
                            $query->orWhereRaw('plant = "'.$plant.'" and unit_no = "'.$unit_no.'"');
                        }
                    $ctr++;
                } // end foreach
            });

            $ngcp_schedules = $query_ngcp->get();
            foreach ($ngcp_schedules as $row) {
                  $plant = $row->plant;
                  $unit_no = $row->unit_no;
                  $resource_id = $plant.'_'.$unit_no;

                  if ( isset( $resource_list[$plant.'_'.$unit_no] ) ) {
                      $resource_id = $resource_list[$plant.'_'.$unit_no]['resource_id'];  
                  }

                  $delivery_date = $row->date;
                  $reserve_class = $row->reserve_class;
                  $key = $delivery_date.'|'.$resource_id . '|' . $reserve_class .'|NGCP';

                  for ($i=1;$i<=24;$i++){
                      $mw = $row['hour'.$i];
                      $rec = array('mw' => $mw);
                      $rec['source'] = 'NGCP';

                      $data[$key][$i] = $rec;
                  }
                  //
            } // end foreach

        }
        return $data;


    } // eof

    public function retrieve(Request $request){
        $date = Carbon::createFromTimestamp(strtotime(request('date')))->format('Y-m-d');
        $resource_ids = explode(',',$request['resource_id']);
        $hours = explode(',',$request['hour']);
        $source = explode(',',$request['source']);

        $data = $this->generate_data($date,$resource_ids,$hours,$source);
        return $data;

    } // eof retrieve function



    public function file(Request $request){

        $date = Carbon::createFromTimestamp(strtotime(request('date')))->format('Y-m-d');
        $fdate = Carbon::createFromTimestamp(strtotime(request('date')))->format('Ymd');
        $resource_ids = explode(',',$request['resource_id']);
        $hours = explode(',',$request['hour']);
        $sources = explode(',',$request['source']);

        $data = $this->generate_data($date,$resource_ids,$hours,$sources);
        $filename = $fdate . '_Reserve_Schedules.xlsx';

        // ### generate excel file here 
        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setShowGridlines(false);
        $sheet->setTitle('Reserve_Schedules');
        $sheet->getDefaultColumnDimension()->setWidth(15);
        $sheet->setCellValue('B1','Realtime Reserve Schedules');
        $sheet->setCellValue('B4','Delivery Date');
        $sheet->setCellValue('C4','Resource ID');
        $sheet->setCellValue('D4','Reserve Class');
        $sheet->setCellValue('E4','Source');
        
        $letter = 'F';
        $last_letter = $letter;
        foreach ($hours as $hour) {
            $sheet->setCellValue($letter.'4','H'.$hour);
            $last_letter = $letter;
            $letter++;

        }

        $sheet->mergeCells('B1:'.$last_letter.'1');

        $sheet->getStyle('B4:'.$last_letter.'4')->applyFromArray(
            array('fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('argb' => 'f4f4f4')
                )
                , 'alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
                , 'font' => array('bold' => true)
        ));


        $sheet->getStyle('B1')->applyFromArray(
            array( 'font' => array('bold' => true, 'size' => '20')
        ));
        


        $row_ctr = 5;
        foreach ($data as $key => $row) {
            
            $tmp = explode('|', $key);

            $date = $tmp[0];
            $resource_id = $tmp[1];
            $reserve_class = $tmp[2];
            $source = $tmp[3];

            $date_label = Carbon::createFromTimestamp(strtotime($date))->format('Ymd');

            $sheet->setCellValue('B'.$row_ctr,$date);
            $sheet->setCellValue('C'.$row_ctr,$resource_id);
            $sheet->setCellValue('D'.$row_ctr,$reserve_class);
            $sheet->setCellValue('E'.$row_ctr,$source);

            $letter = 'F';
            foreach ($hours as $hour) {
                $sched = '';

                if (  isset($row[$hour]) ) {
                   $sched = $row[$hour]['mw'];  
                }
                $sheet->setCellValue($letter.$row_ctr,$sched);
                $letter++;

            }

            $row_ctr++;

        } // endfor


        // ### total row
        $end_row = $row_ctr -1;
        foreach ($sources as $source_val) {
          
            $source_val = strtoupper($source_val);
            $sheet->setCellValue('B'.$row_ctr,'Total');
            $sheet->mergeCells('B'.$row_ctr.':C'.$row_ctr);
            $sheet->setCellValue('D'.$row_ctr,'-');
            $sheet->setCellValue('E'.$row_ctr,$source_val);


            $letter = 'F';
            foreach ($hours as $hour) {
               
                $sheet->setCellValue($letter.$row_ctr,'=SUMIF(E5:E'.$end_row.',"'.$source_val.'",'.$letter.'5:'.$letter.$end_row.')');
                $letter++;

            }

            $row_ctr++;
        }

        $last_row_ctr = $row_ctr -1;

        $sheet->getStyle('B4' . ':'.$last_letter . $last_row_ctr)->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => '000000'),
                )
            )
        ));


        $sheet->getStyle('B4' . ':'.$last_letter . $last_row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
        ));


        $sheet->getStyle('F5' . ':'.$last_letter . $last_row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'right',
                    'vertical' => 'center'
                )
        ));

         $sheet->getStyle('F5' . ':'.$last_letter . $last_row_ctr)->getNumberFormat()->setFormatCode('###,###,###,##0.00');

         $x = $end_row +1;
         $sheet->getStyle('B'.$x.':'.$last_letter.$last_row_ctr)->applyFromArray(
            array('font' => array('bold' => true)
        ));



        $writer = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007','HTML');
        $writer->save($filename);


        return Response::download($filename,$filename, 
           [
           'Content-Description' => "File Transfer",
            "Content-Disposition" => "attachment; filename=".$filename]
            )->deleteFileAfterSend(true);


    } // eof file function
}
