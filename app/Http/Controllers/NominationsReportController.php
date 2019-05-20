<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CustomerParticipant;
use App\UserCustomer;
use Auth;
use App\Nomination;
use App\NominationItem;
use App\Customer;
use App\CustomerNomination;
use App\NominationAudit;
use Carbon\Carbon;
use PHPExcel; 
use PHPExcel_IOFactory;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Helper_HTML;
use Response;
use DB;
use App\Participant;

class NominationsReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    private function get_participants()
    {
    	$customer_id = UserCustomer::where('users_id', Auth::user()->id)->pluck('customers_id');

        if (count($customer_id)) {
            $participants = CustomerParticipant::with('participants')->where('customers_id',$customer_id[0])->get()->pluck('participants.participant_name','participants.id');
        } else {
            $participants = [];
        }
    	

    	return $participants;
    }


    private function get_customers()
    {
        $customer_ids = Auth::user()->customers()->pluck('customers_id');
        if (count($customer_ids)) {
            $customers = Customer::whereIn('id',$customer_ids)->orderBy('customer_name','asc')->get()->pluck('customer_name','id');
        } else {
            $customers = [];
        }
        return $customers;
    }


    public function index()
    {
    	return view('nomination.list');	
    }
    public function transactions()
    {
    	$participants = $this->get_participants();
        $customers = $this->get_customers();
        $current_date = date('Y-m-d');
        $default_date = Carbon::now()->format('m/d/Y');
    	return view('nomination.reports.transactions', compact('participants','customers','current_date','default_date'));
    } // eof

    public function transactions_data(Request $request)
    {
    	$participant = $request->participant_id;
    	$customer = $request->customer_id;
    	$date = date('Y-m-d',strtotime($request->date));

    	$transactions = NominationAudit::with('user')
            ->where('customers_id',$customer)
            ->where('participants_id',$participant)
            ->where(function ($query) use ($date) {
				    $query->where('sdate', '<=', $date);
				    $query->where('edate', '>=', $date);
				})
			->orderBy('transaction_id','asc')
            ->get();

         return response()->json($transactions);
    } // eof


    ### RUNNING NOMINATIONS REPORTS ####
    public function running_report()
    {
        $participants = $this->get_participants();
        $customers = $this->get_customers();
        $current_date = date('Y-m-d');
        $default_date = Carbon::now()->format('m/d/Y');
        $types = array('DAN' => 'DAN', 'WAN' => 'WAN', 'MAN' => 'MAN');
        return view('nomination.reports.running_report', compact('participants','customers','current_date','default_date','types'));
    } // eof


    public function running_report_data(Request $request)
    {
        $participant = $request->participant_id;
        $customer = $request->customer_id;
        $type = $request->type;
        $billing_month = $request->billing_month;
        $billing_year = $request->billing_year;

        $tmp_billing_dte = date('Y-m-d', strtotime($billing_year . '-' . $billing_month . '-05'));
        $start_date = date("Y-m-26", strtotime("previous month " . $tmp_billing_dte));
        $end_date = date("Y-m-25", strtotime($tmp_billing_dte));

        $list = NominationItem::select('nomination_items.*')
                ->whereBetween('nomination_items.date',[$start_date,$end_date])
                ->join('nominations', function($join) use ($participant,$customer,$type,$start_date,$end_date)
                   {
                       $join->on('nominations.type', '=', DB::raw("'". $type. "'") );
                       $join->on('nominations.participants_id', '=', DB::raw($participant) );
                       $join->on('nominations.customers_id', '=', DB::raw($customer));
                       $join->on('nominations.id', '=', 'nomination_items.nominations_id');
                   })
            ->orderBy('nomination_items.date','asc')
            ->get();

        $data = array();
        $total_records = 0;
        foreach ($list as $row) {
            $hr = $row->hour;
            $dte = $row->date;
            $data[$dte][$hr] = $row;
            $total_records++;
        }

        $return = array(
            'list' => $data,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_records' => $total_records
        );

        return response()->json($return);
    } // eof


    public function running_report_excel(Request $request){

        $participant = $request->participant_id;
        $customer = $request->customer_id;
        $type = $request->type;
        $billing_month = $request->billing_month;
        $billing_year = $request->billing_year;

        $tmp_billing_dte = date('Y-m-d', strtotime($billing_year . '-' . $billing_month . '-05'));
        $start_date = date("Y-m-26", strtotime("previous month " . $tmp_billing_dte));
        $end_date = date("Y-m-25", strtotime($tmp_billing_dte));


        $list = NominationItem::select('nomination_items.*')
                ->whereBetween('nomination_items.date',[$start_date,$end_date])
                ->join('nominations', function($join) use ($participant,$customer,$type,$start_date,$end_date)
                   {
                       $join->on('nominations.type', '=', DB::raw("'". $type. "'") );
                       $join->on('nominations.participants_id', '=', DB::raw($participant) );
                       $join->on('nominations.customers_id', '=', DB::raw($customer));
                       $join->on('nominations.id', '=', 'nomination_items.nominations_id');
                   })
            ->orderBy('nomination_items.date','asc')
            ->get();

        $data = array();
        $total_records = 0;
        foreach ($list as $row) {
            $hr = $row->hour;
            $dte = $row->date;
            $data[$dte][$hr] = $row;
            $total_records++;
        }

        $customer_details = Customer::where('id',$customer)->first();
        $customer_name = $customer_details->customer_name;
        $customer_full_name = $customer_details->customer_full_name;

        $participant_details = Participant::where('id',$participant)->first();
        $participant_name = $participant_details->participant_name;

        $file_billing = date("F Y", strtotime($tmp_billing_dte));
        $filename = 'Nominations_'.$type.'_'. $participant_name. '_' . $customer_name . '_'. $file_billing . '.xlsx';

        ## EXCEL 
        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setShowGridlines(false);
        $sheet->setTitle('Buyer Nominations');
        $sheet->getDefaultColumnDimension()->setWidth(15);
        
        ##  Header
        $sheet->setCellValue('A1','Buyer');
        $sheet->setCellValue('B1',$customer_full_name);
        $sheet->setCellValue('A2','Type');
        $sheet->setCellValue('B2',$type);
        $sheet->setCellValue('A3','Participant');
        $sheet->setCellValue('B3',$participant_name);
        $sheet->setCellValue('A4','Billing Period');
        $sheet->setCellValue('B4',$file_billing);

        
        ## Column Header
        $sheet->setCellValue('A6','Interval');
        $sdate = Carbon::parse($start_date);
        $edate = Carbon::parse($end_date);
        $letter = 'B';
        $last_letter = $letter;
        for($date = $sdate; $date->lte($edate); $date->addDay()) {
            $loop_date = $date->format('Y-m-d');
            $last_letter = $letter;
            $sheet->setCellValue($letter.'6',$loop_date);
            $letter++;
        }

        $row = 7;

        for($i=1;$i<=24;$i++){
            $sheet->setCellValue('A'.$row,$i);

            $letter = 'B';
            $sdate = Carbon::parse($start_date);
            $edate = Carbon::parse($end_date);
            for($date = $sdate; $date->lte($edate); $date->addDay()) {
                $loop_date = $date->format('Y-m-d');
                $nomination = '';
                if ( isset($data[$loop_date]) ) {
                    if ( isset($data[$loop_date][$i]) ) {
                        $nomination = $data[$loop_date][$i]['nomination'];
                    }
                }
                $sheet->setCellValue($letter.$row,$nomination);
                $letter++;
            }

            $row++;
        }

        ## header formatting 
        $sheet->getStyle('B1:B4')->applyFromArray(
            array( 
                'font' => array('bold' => true, 'size' => '11')
                , 'alignment' => array(
                    'horizontal' => 'left',
                    'vertical' => 'center'
                ) 
            )
        );

        $sheet->getStyle('A6:'.$last_letter.'6')->applyFromArray(
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


        ## border
        $last_row_ctr = $row -1;
        $sheet->getStyle('A6' . ':'.$last_letter . $last_row_ctr)->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => '000000'),
                )
            )
        ));


        $sheet->getStyle('A6' . ':A'.$last_row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
        ));

        $sheet->getStyle('B7' . ':'.$last_letter . $last_row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'right',
                    'vertical' => 'center'
                )
        ));

        $sheet->getStyle('B6:' .$last_letter. $last_row_ctr)->getNumberFormat()->setFormatCode('###,###,###,##0.00');


        $writer = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007','HTML');
        $writer->save($filename);
        ## END OF EXCEL

        return Response::download($filename,$filename, 
               [
               'Content-Description' => "File Transfer",
                "Content-Disposition" => "attachment; filename=".$filename]
                )->deleteFileAfterSend(true);

    } // eof



    ### NOMINATION EXTRACTION REPORTS ####
    public function extraction_report()
    {
        $customers = $this->get_customers();
        $current_date = date('Y-m-d');
        $default_date = Carbon::now()->format('m/d/Y');
        $types = array('DAN' => 'DAN', 'WAN' => 'WAN', 'MAN' => 'MAN');
        return view('nomination.reports.extraction_report', compact('customers','current_date','default_date','types'));
    } // eof

    private function data_for_extraction_report($customer,$type,$billing_month,$billing_year ){
        $tmp_billing_dte = date('Y-m-d', strtotime($billing_year . '-' . $billing_month . '-05'));
        $start_date = date("Y-m-26", strtotime("previous month " . $tmp_billing_dte));
        $end_date = date("Y-m-25", strtotime($tmp_billing_dte));

        $list = NominationItem::selectRaw('nomination_items.date,nomination_items.hour, 
                sum(nomination_items.nomination) as nomination')
                ->whereBetween('nomination_items.date',[$start_date,$end_date])
                ->join('nominations', function($join) use ($customer,$type,$start_date,$end_date)
                   {
                       $join->on('nominations.id', '=', 'nomination_items.nominations_id');
                       $join->on('nominations.type', '=', DB::raw("'". $type. "'") );
                       if ( strlen(trim($customer)) > 0 ) {
                            $join->on('nominations.customers_id', '=', DB::raw($customer));
                       }
                       
                   })
            ->orderBy('nomination_items.date','asc')
            ->groupBy('nomination_items.date','nomination_items.hour')
            ->get();

        $data = array();
        $total_records = 0;
        foreach ($list as $row) {
            $hr = $row->hour;
            $dte = $row->date;
            $data[$dte][$hr] = $row;
            $total_records++;
        }

        return array(
            'data' => $data,
            'total_records' => $total_records
        );
    } // eof 

    public function extraction_report_data(Request $request)
    {
        
        $customer = $request->customer_id;
        $type = $request->type;
        $billing_month = $request->billing_month;
        $billing_year = $request->billing_year;

        $tmp_billing_dte = date('Y-m-d', strtotime($billing_year . '-' . $billing_month . '-05'));
        $start_date = date("Y-m-26", strtotime("previous month " . $tmp_billing_dte));
        $end_date = date("Y-m-25", strtotime($tmp_billing_dte));


        $report_data  = $this->data_for_extraction_report($customer,$type,$billing_month,$billing_year);
        $data = $report_data['data'];
        $total_records = $report_data['total_records'];
        $return = array(
            'list' => $data,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_records' => $total_records
        );

        return response()->json($return);
    } // eof


    private function extraction_report_excel($data,$customer,$type, $billing_month,$billing_year,$filename){

        $tmp_billing_dte = date('Y-m-d', strtotime($billing_year . '-' . $billing_month . '-05'));
        $start_date = date("Y-m-26", strtotime("previous month " . $tmp_billing_dte));
        $end_date = date("Y-m-25", strtotime($tmp_billing_dte));


        $customer_name = '';
        $customer_full_name = '';
        $file_billing = date("F Y", strtotime($tmp_billing_dte));
        if ( strlen(trim($customer)) > 0 ) {
            $customer_details = Customer::where('id',$customer)->first();
            $customer_name = $customer_details->customer_name;
            $customer_full_name = $customer_details->customer_full_name;
        }
       

        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setShowGridlines(false);
        $sheet->setTitle('Buyer Nominations');
        $sheet->getDefaultColumnDimension()->setWidth(15);
        
        ##  Header
        if ( strlen(trim($customer_name)) > 0 ) {
            $sheet->setCellValue('A1','Buyer');
            $sheet->setCellValue('B1',$customer_full_name);
        }
        $sheet->setCellValue('A2','Type');
        $sheet->setCellValue('B2',$type);
        $sheet->setCellValue('A3','Billing Period');
        $sheet->setCellValue('B3',$file_billing);

        
        ## Column Header
        $sheet->setCellValue('A6','Interval');
        $sdate = Carbon::parse($start_date);
        $edate = Carbon::parse($end_date);
        $letter = 'B';
        $last_letter = $letter;
        for($date = $sdate; $date->lte($edate); $date->addDay()) {
            $loop_date = $date->format('Y-m-d');
            $last_letter = $letter;
            $sheet->setCellValue($letter.'6',$loop_date);
            $letter++;
        }

        $row = 7;

        for($i=1;$i<=24;$i++){
            $sheet->setCellValue('A'.$row,$i);

            $letter = 'B';
            $sdate = Carbon::parse($start_date);
            $edate = Carbon::parse($end_date);
            for($date = $sdate; $date->lte($edate); $date->addDay()) {
                $loop_date = $date->format('Y-m-d');
                $nomination = '';
                if ( isset($data[$loop_date]) ) {
                    if ( isset($data[$loop_date][$i]) ) {
                        $nomination = $data[$loop_date][$i]['nomination'];
                    }
                }
                $sheet->setCellValue($letter.$row,$nomination);
                $letter++;
            }

            $row++;
        }

        ## header formatting 
        $sheet->getStyle('B1:B4')->applyFromArray(
            array( 
                'font' => array('bold' => true, 'size' => '11')
                , 'alignment' => array(
                    'horizontal' => 'left',
                    'vertical' => 'center'
                ) 
            )
        );

        $sheet->getStyle('A6:'.$last_letter.'6')->applyFromArray(
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


        ## border
        $last_row_ctr = $row -1;
        $sheet->getStyle('A6' . ':'.$last_letter . $last_row_ctr)->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => '000000'),
                )
            )
        ));


        $sheet->getStyle('A6' . ':A'.$last_row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
        ));

        $sheet->getStyle('B7' . ':'.$last_letter . $last_row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'right',
                    'vertical' => 'center'
                )
        ));

        $sheet->getStyle('B6:' .$last_letter. $last_row_ctr)->getNumberFormat()->setFormatCode('###,###,###,##0.00');


        $writer = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007','HTML');
        $writer->save($filename);


    } // eof 


    private function extraction_report_csv($data,$customer,$type, $billing_month,$billing_year,$filename){

        $tmp_billing_dte = date('Y-m-d', strtotime($billing_year . '-' . $billing_month . '-05'));
        $start_date = date("Y-m-26", strtotime("previous month " . $tmp_billing_dte));
        $end_date = date("Y-m-25", strtotime($tmp_billing_dte));


        $customer_name = '';
        $customer_full_name = '';
        $file_billing = date("F Y", strtotime($tmp_billing_dte));
        if ( strlen(trim($customer)) > 0 ) {
            $customer_details = Customer::where('id',$customer)->first();
            $customer_name = $customer_details->customer_name;
            $customer_full_name = $customer_details->customer_full_name;
        }
        
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=".$filename);
        header("Pragma: no-cache");
        header("Expires: 0");
        $file = fopen('php://output', 'w');                             
        
        ##  Header
        $headers = array();
        if ( strlen(trim($customer_name)) > 0 ) {
            $headers[] = 'Buyer';
            $headers[] = $customer_full_name;
            fputcsv($file,$headers); 
        }
        $headers = array();
        $headers[] = 'Type';
        $headers[] = $type;
        fputcsv($file,$headers); 
        $headers = array();
        $headers[] = 'Billing Period';
        $headers[] = $file_billing;
        fputcsv($file,$headers); 
        
        fputcsv($file,array()); 

        ## Column Header
        $headers = array();
        $headers[] = 'Interval';
        $sdate = Carbon::parse($start_date);
        $edate = Carbon::parse($end_date);
        for($date = $sdate; $date->lte($edate); $date->addDay()) {
            $loop_date = $date->format('Y-m-d');
            $headers[] = $loop_date;
        }
        fputcsv($file,$headers); 
        
        
        for($i=1;$i<=24;$i++){
            $rec = array();
            $rec[] = $i;
            $sdate = Carbon::parse($start_date);
            $edate = Carbon::parse($end_date);
            for($date = $sdate; $date->lte($edate); $date->addDay()) {
                $loop_date = $date->format('Y-m-d');
                $nomination = '';
                if ( isset($data[$loop_date]) ) {
                    if ( isset($data[$loop_date][$i]) ) {
                        $nomination = $data[$loop_date][$i]['nomination'];
                    }
                }
                $rec[] = $nomination;
            }
            fputcsv($file,$rec); 
        }
        exit();
    
    } // eof 

    public function extraction_report_file( Request $request ){
        $customer = $request->customer_id;
        $type = $request->type;
        $billing_month = $request->billing_month;
        $billing_year = $request->billing_year;
        $file_format = $request->file_format;

        $tmp_billing_dte = date('Y-m-d', strtotime($billing_year . '-' . $billing_month . '-05'));
        $start_date = date("Y-m-26", strtotime("previous month " . $tmp_billing_dte));
        $end_date = date("Y-m-25", strtotime($tmp_billing_dte));


        $report_data  = $this->data_for_extraction_report($customer,$type,$billing_month,$billing_year);
        $data = $report_data['data'];
        $total_records = $report_data['total_records'];

        $file_billing = date("F Y", strtotime($tmp_billing_dte));
        
        $filename = 'Nominations_'.$type.'_ALL CUSTOMER_'. $file_billing;
        if ( strlen(trim($customer)) > 0 ) {
            $customer_details = Customer::where('id',$customer)->first();
            $customer_name = $customer_details->customer_name;
            $filename = 'Nominations_'.$type.'_'. $customer_name . '_'. $file_billing;

        }

        if ( $file_format == 'csv' ) {
            $filename .= '.csv';
            $this->extraction_report_csv($data,$customer,$type, $billing_month,$billing_year,$filename);
            

        } else {
            $filename .= '.xlsx';
            $this->extraction_report_excel($data,$customer,$type, $billing_month,$billing_year,$filename);
            return Response::download($filename,$filename, 
               [
               'Content-Description' => "File Transfer",
                "Content-Disposition" => "attachment; filename=".$filename]
                )->deleteFileAfterSend(true);
        }

         
        

        
    }// eof file 


}
