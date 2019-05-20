<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Resource;
use App\PlantCapabilityStatus;
use App\PlantCapabilityType;
use App\PlantCapability;
use Carbon\Carbon;
use PHPExcel; 
use PHPExcel_IOFactory;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Helper_HTML;
use Response;
use PDF;
use Yajra\Datatables\Datatables;

class PlantCapabilityReportsController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth');
    }



    public function tradingIndex(){

    	$resources = Resource::orderBy('resource_id','asc')->pluck('resource_id','id')->toArray();

    	$types = PlantCapabilityType::orderBy('id','asc')->pluck('type','id')->toArray();

    	return view('plant_capability_reports.trading_list',compact('resources','types'));


    } // 



    public function data(Request $request){
    	  $s_date = Carbon::createFromTimestamp(strtotime(request('sdate')));
          $e_date = Carbon::createFromTimestamp(strtotime(request('edate')));

          $query = PlantCapability::query();
          $query = $query->whereBetween('delivery_date', [$s_date,$e_date]);


          if ( $request->has('resource_id') ) {
            $resource_id = request('resource_id');
            $query = $query->where('resources_id', $resource_id);
          }


          if ( $request->has('type_id') ) {
            $type_id = request('type_id');
            $query = $query->where('plant_capability_type_id', $type_id);
          }

          $query->with('plantCapabilityType','plantCapabilityStatus')->get();

          return Datatables::of($query)
                ->make(true);


    } // 


    private function excelFile($list,$resource_id,$filename){
        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setShowGridlines(false);
        $sheet->setTitle('Availability_Reports');
        $sheet->getDefaultColumnDimension()->setWidth(15);
        $sheet->setCellValue('B2','AVAILABILITY REPORTS');
        $resource_name = Resource::where('id',request('resource_id'))->value('resource_id');

        $sheet->setCellValue('B3',$resource_name);
        $sheet->setCellValue('B5','Date');
        $sheet->setCellValue('C5','Interval');
        $sheet->setCellValue('D5','Hour');
        $sheet->setCellValue('E5','Net Energy (MWH)');
        $sheet->setCellValue('F5','Remarks');
        $sheet->setCellValue('G5','Description');
        $sheet->setCellValue('H5','Source');

        
        $sheet->mergeCells('B2:H2');
        $sheet->mergeCells('B3:H3');
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(40);

        $sheet->getStyle('B2:H2')->applyFromArray(
            array('alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
                , 'font' => array('bold' => true, 'size'  => '20')
        ));

        $sheet->getStyle('B3:H3')->applyFromArray(
            array('alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
                , 'font' => array('bold' => true, 'size' => '11')
        ));


        $row_ctr = 6;
        foreach ($list as $report) {

        	$delivery_date = $report->delivery_date;
        	$hour = $report->hour;
        	$capability = $report->capability;

        	$remarks = $report->plantCapabilityStatus->status;
        	$description = $report->description;
        	$type = $report->plantCapabilityType->type;

        	$dte = Carbon::createFromTimestamp(strtotime($delivery_date))->format('m/d/Y');
        	$prev_hour = $hour - 1;
        	$time = str_pad($prev_hour,2,"0",STR_PAD_LEFT) .':01 - ' . str_pad($hour,2,"0",STR_PAD_LEFT) .':00';

        	$sheet->setCellValue('B'.$row_ctr,$dte);
	        $sheet->setCellValue('C'.$row_ctr,$hour);
	        $sheet->setCellValue('D'.$row_ctr,$time);
	        $sheet->setCellValue('E'.$row_ctr,$capability);
	        $sheet->setCellValue('F'.$row_ctr,$remarks);
	        $sheet->setCellValue('G'.$row_ctr,$description);
	        $sheet->setCellValue('H'.$row_ctr,$type);

	        $row_ctr++;

        }

        
        $last_row_ctr = $row_ctr -1;

        $sheet->getStyle('B5' . ':H' . $last_row_ctr)->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => '000000'),
                )
            )
        ));


        $sheet->getStyle('B5' . ':H' . $last_row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
        ));


        $sheet->getStyle('H6:H' . $last_row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'left',
                    'vertical' => 'top'
                )
        ));

        $writer = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007','HTML');
        $writer->save($filename);
    } // excelFile


    private function pdfFile($list,$resource_id,$filename){

    	$resource_name = Resource::where('id',request('resource_id'))->value('resource_id');

    	$html = '';
        $html .= '<style>';
        $html .= '@page { margin: 30px; }';
		$html .= 'body { margin: 30px; margin-top:60px;}';
        $html .= 'table td, table th, center { ';
        $html .= 'font-family:sans-serif; font-size:10px; text-align:center;';
        $html .= 'padding:4px;';
        $html .= '} ';
        $html .= ' .header, .footer {
                width: 100%;
                text-align: center;
                position: fixed;
            }
            .header {
                top: 0px;
            }
            .footer {
                bottom: 0px;
            }
            .pagenum:before {
                content: counter(page);
            } ';
        $html .= '</style>';

        $html .='<body>';
        $html .='<div class="header">';
        $html .='<center style="font-weight:bold; font-size:16px;  padding:4px;">CAPABILITY REPORTS</center>' ;
        $html .='<center style="font-weight:bold; font-size:13px; padding:4px;">'.$resource_name.'</center><br>';
        $html .='</div>';

        $html .='<div id="content">';
        $html .= '<table width="100%" border="1" cellspacing="0" cellpadding="0" page-break-inside: auto;>';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th style="font-weight:bold; font-size:11px; background-color:#b4c6e7; ">Date</th>';
        $html .= '<th style="font-weight:bold; font-size:11px; background-color:#b4c6e7;">Interval</th>';
        $html .= '<th style="font-weight:bold; font-size:11px; background-color:#b4c6e7;">Hour</th>';
        $html .= '<th style="font-weight:bold; font-size:11px; background-color:#b4c6e7;">Net Energy (MWh)</th>';
        $html .= '<th style="font-weight:bold; font-size:11px; background-color:#b4c6e7; width:150px;"">Remarks</th>';
        $html .= '<th style="font-weight:bold; font-size:11px; background-color:#b4c6e7; width:200px;">Description</th>';
        $html .= '<th style="font-weight:bold; font-size:11px; background-color:#b4c6e7;">Source</th>';
        $html .= '</tr>';
        $html .= '</thead>';


        
        foreach ($list as $report) {

        	$delivery_date = $report->delivery_date;
        	$hour = $report->hour;
        	$capability = $report->capability;

        	$remarks = $report->plantCapabilityStatus->status;
        	$description = $report->description;
        	$type = $report->plantCapabilityType->type;

        	$dte = Carbon::createFromTimestamp(strtotime($delivery_date))->format('m/d/Y');
        	$prev_hour = $hour - 1;
        	$time = str_pad($prev_hour,2,"0",STR_PAD_LEFT) .':01 - ' . str_pad($hour,2,"0",STR_PAD_LEFT) .':00';


        	$html .= '<tr>';
	        $html .= '<td>'.$dte.'</td>';
	        $html .= '<td>'.$hour.'</td>';
	        $html .= '<td>'.$time.'</td>';
	        $html .= '<td>'.$capability.'</td>';
	        $html .= '<td>'.$remarks.'</td>';
	        $html .= '<td>'.$description.'</td>';
	        $html .= '<td>'.$type.'</td>';
	        
	        $html .= '</tr>';

        	

        }

        $html .= '</table>';
        $html .= '</div></body></html>';

         PDF::loadHTML($html)->setPaper('letter', 'landscape')->setWarnings(false)->save($filename);
        
    } // pdf

    public function file(Request $request){
    	  $s_date = Carbon::createFromTimestamp(strtotime(request('sdate')));
	      $e_date = Carbon::createFromTimestamp(strtotime(request('edate')));

	      $query = PlantCapability::query();
	      $query = $query->whereBetween('delivery_date', [$s_date,$e_date]);


	      if ( $request->has('resource_id') ) {
	        $resource_id = request('resource_id');
	        $query = $query->where('resources_id', $resource_id);
	      }


	      if ( $request->has('type_id') ) {
	        $type_id = request('type_id');
	        $query = $query->where('plant_capability_type_id', $type_id);
	      }

	      $data = $query->with('plantCapabilityType','plantCapabilityStatus')->get();


	      $file_format = request('file_format');

	      if ($file_format === 'pdf') {
	      		$filename = 'Availability_Reports.pdf';
	      		$this->pdfFile($data,$resource_id,$filename);	
	      }else {
	      		$filename = 'Availability_Reports.xlsx';
	      		$this->excelFile($data,$resource_id,$filename);	
	      }

	      return Response::download($filename,$filename, 
           [
           'Content-Description' => "File Transfer",
            "Content-Disposition" => "attachment; filename=".$filename]
            )->deleteFileAfterSend(true);

    } // 

}
