<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MmsSystemMessage;
use Carbon\Carbon;
use PHPExcel; 
use PHPExcel_IOFactory;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Helper_HTML;
use PHPExcel_Style_Alignment;
use Response;
use Yajra\Datatables\Datatables;

class MmsSystemMessagesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    } //


    public function mmsReportIndex(){

    	return view('mms_data.system_messages.list');


    } // eof


    public function data(Request $request){
    	    $s_date = Carbon::createFromTimestamp(strtotime(request('sdate')))->format('Y-m-d');
          $e_date = Carbon::createFromTimestamp(strtotime(request('edate')))->format('Y-m-d');
          $query = MmsSystemMessage::query();
          $query = $query->whereRaw('date(date) between "'.$s_date.'" and "'.$e_date.'"');


          $urgency_list = explode(',',$request['urgency']);
          if ( count($urgency_list) > 0 ) {
            $query = $query->whereIn('urgency', $urgency_list);
          }

          if ( $request->has('content') ) {
            $content = request('content');
            $query = $query->where('message', 'like', '%'.$content.'%');
          }

         
          return Datatables::of($query)
                ->make(true);

    } // eof data


    public function file(Request $request){
        $s_date = Carbon::createFromTimestamp(strtotime(request('sdate')))->format('Y-m-d');
        $e_date = Carbon::createFromTimestamp(strtotime(request('edate')))->format('Y-m-d');
        $query = MmsSystemMessage::query();
        $query = $query->whereRaw('date(date) between "'.$s_date.'" and "'.$e_date.'"');


        $urgency_list = explode(',',$request['urgency']);
        if ( count($urgency_list) > 0 ) {
          $query = $query->whereIn('urgency', $urgency_list);
        }

        if ( $request->has('content') ) {
          $content = request('content');
          $query = $query->where('message', 'like', '%'.$content.'%');
        }

        $data = $query->get();

        // Excel File
        $file_s_date = Carbon::createFromTimestamp(strtotime(request('sdate')))->format('Ymd');
        $file_e_date = Carbon::createFromTimestamp(strtotime(request('edate')))->format('Ymd');

        $filename = 'REP_SYSTEM_MESSAGES'.$file_s_date . '_' . $file_e_date . '.xlsx';

        $formatted_sdate = Carbon::createFromTimestamp(strtotime(request('sdate')))->format('F d, Y');
        $formatted_edate = Carbon::createFromTimestamp(strtotime(request('edate')))->format('F d, Y');

        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setShowGridlines(false);
        $sheet->setTitle('SYSTEM_MESSAGES');
        $sheet->getDefaultColumnDimension()->setWidth(15);
        $sheet->setCellValue('A2','System Messages');
        $sheet->setCellValue('A3',$formatted_sdate . ' to ' . $formatted_edate);

        $sheet->setCellValue('A5','Date');
        $sheet->setCellValue('B5','Message');
       
        $sheet->mergeCells('A2:B2');
        $sheet->mergeCells('A3:B3');

        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(100);
        
        $sheet->getStyle('A5:B5')->applyFromArray(
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


        $sheet->getStyle('A2')->applyFromArray(
            array( 
                'font' => array('bold' => true, 'size' => '20')
                , 'alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                ) 
            )
            );

        $sheet->getStyle('A3')->applyFromArray(
            array( 
                'font' => array('bold' => true, 'size' => '13')
                , 'alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                ) 
            ));


        $red = array(
          'font'  => array(
              'color' => array('rgb' => 'ff0000')
          ));

        $blue = array(
          'font'  => array(
              'color' => array('rgb' => '1000ff')
          ));

        $green = array(
          'font'  => array(
              'color' => array('rgb' => '0d771b')
          ));

        $row_ctr = 6;
        foreach ($data as $row) {

            $date = $row->date;
            $message = $row->message;
            $urgency = $row->urgency;

            $sheet->setCellValue('A'.$row_ctr,$date);
            $sheet->setCellValue('B'.$row_ctr,$message);


            if ($urgency == 'RED') {
                $sheet->getStyle('A'.$row_ctr . ':B'.$row_ctr)->applyFromArray($red);
            } else if ($urgency == 'BLUE') {
                $sheet->getStyle('A'.$row_ctr . ':B'.$row_ctr)->applyFromArray($blue);
            } else if ($urgency == 'GREEN') {
                $sheet->getStyle('A'.$row_ctr . ':B'.$row_ctr)->applyFromArray($green);
            }


            if ($row_ctr % 2 == 0) {
               $sheet->getStyle('A'.$row_ctr . ':B'.$row_ctr)->applyFromArray(
                  array(
                     'fill' => array(
                       'type' => PHPExcel_Style_Fill::FILL_SOLID,
                       'color' => array('rgb' => 'f4f4f4')
                     )
                  )
               );
            }
            $row_ctr++;
        }


        $last_row_ctr = $row_ctr -1;

        $sheet->getStyle('A5' . ':B' . $last_row_ctr)->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => '000000'),
                )
            )
        ));


        $sheet->getStyle('A5' . ':A' . $last_row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
        ));


        $sheet->getStyle('B6' . ':B' . $last_row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'left',
                    'vertical' => 'center'
                )
        ));
        $sheet->getStyle('B6:B'.$last_row_ctr)->getAlignment()->setWrapText(true); 
        $writer = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007','HTML');
        $writer->save($filename);




        return Response::download($filename,$filename, 
           [
           'Content-Description' => "File Transfer",
            "Content-Disposition" => "attachment; filename=".$filename]
            )->deleteFileAfterSend(true);

    } //
    
}
