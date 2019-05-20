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
use App\Participant;
use Carbon\Carbon;
use PHPExcel; 
use PHPExcel_IOFactory;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Helper_HTML;
use Response;

class NominationsController extends Controller
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
    public function day_ahead()
    {
    	$participants = $this->get_participants();
        $customers = $this->get_customers();
        $current_date = date('Y-m-d');
        $default_date = Carbon::now()->addDay()->format('m/d/Y');
    	return view('nomination.day_ahead', compact('participants','customers','current_date','default_date'));
    }
    public function day_ahead_data(Request $request)
    {
    	$user_id = Auth::user()->id;
    	$customer_id = $request->customer;
        $participant_id = $request->participant;

    	$date = date('Ymd', strtotime($request->delivery_date));

    	$nominations = Nomination::with('nomination_items')
            ->where('date',$date)
            ->where('customers_id',$customer_id)
            ->where('participants_id',$participant_id)
            ->where('type','DAN')
            ->get();

        return response()->json($nominations);
    } // eof day_ahead_data

    public function day_ahead_store(Request $request)
    {

    	$user_id = Auth::user()->id;
        $user_name = Auth::user()->username;
    	$customer_id = $request->customer_id;
        $participant_id = $request->participant_id;
        $date = date('Y-m-d', strtotime($request->date));
        $data_key = array(
            'date' => $date,
            'type' => 'DAN',
            'participants_id' => $participant_id,
            'customers_id' => $customer_id,
        );
        $data = array(
            'remarks' => $request->remarks,
            'sdate' => $date,
            'edate' => $date,
            'submitted_by' => $user_id
        );
        
        $nomination = Nomination::updateOrCreate($data_key,$data);
        $nomination_id = $nomination->id;


        // ### attached nomination to customer 
        $relations = CustomerNomination::where('customers_id',$customer_id)
            ->where('nominations_id',$nomination_id)
            ->get();
        if ( count($relations) <= 0) {
            $relations_data = array();
            $relations_data[] = array( 
                'customers_id' => $customer_id,
                'nominations_id' => $nomination_id
            );
            CustomerNomination::insert($relations_data);
        }   
        

    	$nomination_items = NominationItem::where('nominations_id', $nomination->id)->get();
    	$nomination_items_cnt = count($nomination_items);
        $audit_nominations = array();
    	foreach ($request->nomination as $hour=>$nom) {
    		$nomination_item = NominationItem::updateOrCreate(
    			[
                    'nominations_id' => $nomination_id,
    			    'hour' => $hour,
                    'date' => $date
                ],
    			['nomination' => $nom ? str_replace(',','',$nom) : 0]
			);


            $audit_nominations[$date][$hour] = $nom;
    	}


        // ### add audit 
        $customer_name = Customer::find($customer_id)->customer_name;
        $transaction_id = $customer_name .'_'.$user_name.'_DAN_'.date('Ymd', strtotime($request->date));

        $audit_data = array(
            'transaction_id' => $transaction_id,
            'nominations_id' => $nomination_id,
            'sdate' => $date,
            'edate' => $date,
            'type' => 'DAN',
            'participants_id'  => $participant_id,
            'customers_id' => $customer_id,
            'data' => json_encode($audit_nominations),
            'remarks' => $request->remarks,
            'submitted_by' => $user_id
        );
        $insert_audit = NominationAudit::create($audit_data);

    	
    	return back()->withInput()->with('success','Nomination submitted successfully');
    } // eof day_ahead_store


    public function day_ahead_upload(Request $request){
          $file = request()->file('filename');
          $this->validate( request(), [
                'filename' => 'required|max:10000|mimes:xlsx' 
          ]);

          $ext = $file->getClientOriginalExtension();
          $uploaded_filename = $file->getClientOriginalName();


          $dtetime = Carbon::now()->format('Ymd_His');
          $filename = $dtetime . '_' . $uploaded_filename ;

          $file = request()->file('filename');
          $file->storeAs('nomination/DAN',$filename);
          $excel_obj = PHPExcel_IOFactory::load($file);
          $sheet = $excel_obj->setActiveSheetIndex(0);
          $return_data = array();
          $interval = 1;
          for ($row = 6; $row <= 29; ++$row) {
                $nomination     = $sheet->getCell('C' . $row)->getValue();
                $return_data[$interval] = ['nomination' => $nomination];
                $interval++;
          }

          $remarks = $sheet->getCell('C31')->getValue();
          session()->flash('message_uploading', 'Uploading successful');
          $nomination = array(
            'nomination_data' => $return_data,
            'remarks' => $remarks
          );
          session()->flash('nomination', $nomination);
          return redirect()->back();  
    }
    
    public function week_ahead()
    {
    	$participants = $this->get_participants();
        $customers = $this->get_customers();
        
        $date = Carbon::parse('next saturday')->format('m/d/Y');
        $end_date = Carbon::parse('next saturday')->addDays(6)->format('m/d/Y');
        $date_list = array();
        for($i=0;$i<=6;$i++){
            $x = Carbon::parse($date)->addDays($i)->format('Y-m-d');
            $date_list[] = $x;
        }

    	return view('nomination.week_ahead', 
            compact('participants','weekly_array','customers','date','end_date','date_list'));
    }

    private function data_by_date_range($user_id,$customer_id,$participant_id,$sdate,$edate,$type){
        $sdate = date('Y-m-d',strtotime($sdate));
        $edate = date('Y-m-d',strtotime($edate));

        $nomination = Nomination::whereBetween('date', [$sdate, $edate])
            ->where('customers_id',$customer_id)
            ->where('participants_id',$participant_id)
            ->where('type',$type)
            ->first();
        $nomination_items = array();
        if ( count($nomination) > 0 ) {
            $nomination_id = $nomination->id;

            $records = NominationItem::where('nominations_id',$nomination_id)
                ->get();
            foreach ($records as $row) {
              
              $rec = array(
                'date' => $row->date,
                'hour' => $row->hour,
                'interval' => $row->interval,
                'nomination' => $row->nomination,
                'nominations_id' => $row->nominations_id,
                'updated_at' => $row->updated_at
              );
              $nomination_items[$row->date][$row->hour] = $rec;
            } // end foreach
        }
        
        $nominations = array(
            'nomination' => $nomination,
            'nomination_items' => $nomination_items
        );

        return $nominations;

    } // eof data_by_date_range 

    public function week_ahead_data(Request $request)
    {
    	$delivery_date = request('delivery_date');
        $date = Carbon::parse($delivery_date)->format('m/d/Y');
        $end_date = Carbon::parse($delivery_date)->addDays(6)->format('m/d/Y');
        $date_list = array();
        for($i=0;$i<=6;$i++){
            $x = Carbon::parse($date)->addDays($i)->format('Y-m-d');
            $date_list[] = $x;
        }


        $user_id = Auth::user()->id;
        $customer_id = $request->customer;
        $participant_id = $request->participant;

        $date = date('Ymd', strtotime($request->delivery_date));
        $nominations = $this->data_by_date_range($user_id,$customer_id,$participant_id,$date,$end_date,'WAN');

        

    	$ret = array(
			'date' => $date_list,
            'nominations' => $nominations
		);

    	return response()->json($ret);
    } // eof week_ahead_data


    public function week_ahead_store(Request $request){
        $user_id = Auth::user()->id;
        $user_name = Auth::user()->username;
        $customer_id = $request->customer_id;
        $participant_id = $request->participant_id;

        $delivery_date = $request->date;
        $date = Carbon::parse($delivery_date)->format('Y-m-d');
        $end_date = Carbon::parse($delivery_date)->addDays(6)->format('Y-m-d');
        $sdate_label = Carbon::parse($delivery_date)->format('Ymd');
        $edate_label = Carbon::parse($delivery_date)->addDays(6)->format('Ymd');

        // ### ===> save to nomination table
        $data_key = array(
            'date' => $date,
            'type' => 'WAN',
            'participants_id' => $participant_id,
            'customers_id' => $customer_id,
        );
        $data = array(
            'remarks' => $request->remarks,
            'sdate' => $date,
            'edate' => $end_date,
            'submitted_by' => $user_id
        );
        
        $nomination = Nomination::updateOrCreate($data_key,$data);
        $nomination_id = $nomination->id;


        // ### ===> save to relations table
        $relations = CustomerNomination::where('customers_id',$customer_id)
            ->where('nominations_id',$nomination_id)
            ->get();

        if ( count($relations) <= 0) {
            $relations_data = array();
            $relations_data[] = array( 
                'customers_id' => $customer_id,
                'nominations_id' => $nomination_id
            );
            CustomerNomination::insert($relations_data);
        }   
        
        // ### ===> save nominations items
        $nominations = $request->nomination;
        $audit_nominations = array();
        foreach ($nominations as $i => $per_week_nominations) {
            $cur_date = Carbon::parse($date)->addDays($i)->format('Y-m-d');
            foreach ($per_week_nominations as $hour => $nom) {
                $nom_cleaned = str_replace(',','',$nom);
                $nomination_item = NominationItem::updateOrCreate(
                    [
                        'nominations_id' => $nomination_id,
                        'hour' => $hour,
                        'date' => $cur_date
                    ],
                    ['nomination' => $nom_cleaned ]
                );


                $audit_nominations[$cur_date][$hour] = $nom;
            }
        }

       
        // ###===> add audit 
        $customer_name = Customer::find($customer_id)->customer_name;
        $transaction_id = $customer_name .'_'.$user_name.'_WAN_'.$sdate_label.'_'.$edate_label;

        $audit_data = array(
            'transaction_id' => $transaction_id,
            'nominations_id' => $nomination_id,
            'sdate' => $date,
            'edate' => $end_date,
            'type' => 'WAN',
            'participants_id'  => $participant_id,
            'customers_id' => $customer_id,
            'data' => json_encode($audit_nominations),
            'remarks' => $request->remarks,
            'submitted_by' => $user_id
        );
        $insert_audit = NominationAudit::create($audit_data);

        return back()->withInput()->with('success','Nomination submitted successfully');

    } // eof week_ahead_store


    public function week_ahead_upload(Request $request){
          $file = request()->file('filename');
          $this->validate( request(), [
                'filename' => 'required|max:10000|mimes:xlsx' 
          ]);

          $ext = $file->getClientOriginalExtension();
          $uploaded_filename = $file->getClientOriginalName();


          $dtetime = Carbon::now()->format('Ymd_His');
          $filename = $dtetime . '_' . $uploaded_filename ;

          $file = request()->file('filename');
          $file->storeAs('nomination/WAN',$filename);
          $excel_obj = PHPExcel_IOFactory::load($file);
          $sheet = $excel_obj->setActiveSheetIndex(0);
          $return_data = array();
          $interval = 1;
          for ($row = 7; $row <= 30; ++$row) {

                $letter = 'B';
                for ($d=0;$d<=6;$d++){
                    $nomination = $sheet->getCell( $letter . $row)->getValue();
                    $return_data[$d][$interval] = array('nomination' => $nomination);
                    $letter++;
                }
                
                $interval++;
          }

          $upload_sdate = request('upload_sdate');
          $date = Carbon::parse($upload_sdate)->format('m/d/Y');
          $end_date = Carbon::parse($upload_sdate)->addDays(6)->format('m/d/Y');
          $date_list = array();
          for($i=0;$i<=6;$i++){
              $x = Carbon::parse($date)->addDays($i)->format('Y-m-d');
              $date_list[] = $x;
          }



          $remarks = $sheet->getCell('C32')->getValue();
          session()->flash('message_uploading', 'Uploading successful');
          $nomination = array(
            'nomination_data' => $return_data,
            'remarks' => $remarks,
            'date_list' => $date_list,
            'date' => $date,
            'end_date' => $end_date
          );
          session()->flash('nomination', $nomination);
          return redirect()->back();  
    } // eof


    public function month_ahead()
    {
        $participants = $this->get_participants();
        $customers = $this->get_customers();
        
        $next_month = Carbon::parse('next month');
        $billing_month = $next_month->format('m');
        $billing_year = $next_month->format('Y');

        $months = array();
        for ($i = 1; $i <= 12; $i++) {
            $month_name = date('F',strtotime('2018-'.$i.'-01'));
            $months[$i] = $month_name;
        }

        $start_year = $billing_year - 5;
        $end_year = $billing_year+1;
        $years = array();
        for($y=$start_year;$y<=$end_year;$y++){
            $years[$y] = $y;
        }


        return view('nomination.month_ahead', 
            compact('participants','weekly_array','customers',
                'billing_month' , 'billing_year','months','years'));
    }
    public function month_ahead_data(Request $request)
    {
        $delivery_date = request('delivery_date');
        $date = Carbon::parse($delivery_date)->format('m/d/Y');


        $billing_month = request('billing_month');
        $billing_year = request('billing_year');
        $tmp_billing_dte = date('Y-m-d', strtotime($billing_year . '-' . $billing_month . '-05'));
        $sdate = date("m/26/Y", strtotime("previous month " . $tmp_billing_dte));
        $edate = date("m/25/Y", strtotime($tmp_billing_dte));

        $start_date = Carbon::parse($sdate);
        $end_date = Carbon::parse($edate);

        $date_list = array();
        for($date = $start_date; $date->lte($end_date); $date->addDay()) {
            $date_list[] = $date->format('Y-m-d');
        }

        $user_id = Auth::user()->id;
        $customer_id = $request->customer;
        $participant_id = $request->participant;

        $date = date('Ymd', strtotime($request->delivery_date));
        $nominations = $this->data_by_date_range($user_id,$customer_id,$participant_id,$date,$end_date,'MAN');

        

        $ret = array(
            'date' => $date_list,
            'nominations' => $nominations
        );

        return response()->json($ret);
    }
    public function month_ahead_store(Request $request)
    {
        $user_id = Auth::user()->id;
        $user_name = Auth::user()->username;
        $customer_id = $request->customer_id;
        $participant_id = $request->participant_id;

        $billing_month = request('month');
        $billing_year = request('year');
        $tmp_billing_dte = date('Y-m-d', strtotime($billing_year . '-' . $billing_month . '-05'));
        $sdate = date("Y-m-26", strtotime("previous month " . $tmp_billing_dte));
        $edate = date("Y-m-25", strtotime($tmp_billing_dte));
        $date = Carbon::parse($sdate)->format('Y-m-d');

        $sdate_label = Carbon::parse($sdate)->format('Ymd');
        $edate_label = Carbon::parse($edate)->addDays(6)->format('Ymd');


        // ### ===> save to nomination table
        $data_key = array(
            'date' => $date,
            'type' => 'MAN',
            'participants_id' => $participant_id,
            'customers_id' => $customer_id,
        );
        $data = array(
            'remarks' => $request->remarks,
            'sdate' => $sdate,
            'edate' => $edate,
            'submitted_by' => $user_id
        );
        
        $nomination = Nomination::updateOrCreate($data_key,$data);
        $nomination_id = $nomination->id;


        // ### ===> save to relations table
        $relations = CustomerNomination::where('customers_id',$customer_id)
            ->where('nominations_id',$nomination_id)
            ->get();

        if ( count($relations) <= 0) {
            $relations_data = array();
            $relations_data[] = array( 
                'customers_id' => $customer_id,
                'nominations_id' => $nomination_id
            );
            CustomerNomination::insert($relations_data);
        }   
        
        // ### ===> save nominations items
        $nominations = $request->nomination;
        $audit_nominations = array();
        foreach ($nominations as $i => $per_month_nominations) {
            $cur_date = Carbon::parse($date)->addDays($i)->format('Y-m-d');
            foreach ($per_month_nominations as $hour => $nom) {
                $nom_cleaned = str_replace(',','',$nom);
                $nomination_item = NominationItem::updateOrCreate(
                    [
                        'nominations_id' => $nomination_id,
                        'hour' => $hour,
                        'date' => $cur_date
                    ],
                    ['nomination' => $nom_cleaned ]
                );


                $audit_nominations[$cur_date][$hour] = $nom;
            }
        }

       
        // ###===> add audit 
        $customer_name = Customer::find($customer_id)->customer_name;
        $transaction_id = $customer_name .'_'.$user_name.'_MAN_'.$sdate_label.'_'.$edate_label;

        $audit_data = array(
            'transaction_id' => $transaction_id,
            'nominations_id' => $nomination_id,
            'sdate' => $sdate,
            'edate' => $edate,
            'type' => 'WAN',
            'participants_id'  => $participant_id,
            'customers_id' => $customer_id,
            'data' => json_encode($audit_nominations),
            'remarks' => $request->remarks,
            'submitted_by' => $user_id
        );
        $insert_audit = NominationAudit::create($audit_data);

        return back()->withInput()->with('success','Nomination submitted successfully');;
    } //eof


    public function month_ahead_upload(Request $request){
          $file = request()->file('filename');
          $this->validate( request(), [
                'filename' => 'required|max:10000|mimes:xlsx' 
          ]);

          $ext = $file->getClientOriginalExtension();
          $uploaded_filename = $file->getClientOriginalName();

          $upload_sdate = request('upload_month');


          $billing_month = request('upload_month');
          $billing_year = request('upload_year');
          $tmp_billing_dte = date('Y-m-d', strtotime($billing_year . '-' . $billing_month . '-05'));
          $start_date = date("Y-m-26", strtotime("previous month " . $tmp_billing_dte));
          $end_date = date("Y-m-25", strtotime($tmp_billing_dte));
          $date = Carbon::parse($start_date )->format('Y-m-d');

          $max = 0;
          $date_list = array();

          $date = $start_date;
          $my_end_date = $end_date;
          while (strtotime($date) <= strtotime($my_end_date)) {
            $date_index = date('Y-m-d', strtotime($date));
            $date_list[] = $date_index;
            $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
            $max++;
          }

        
          $dtetime = Carbon::now()->format('Ymd_His');
          $filename = $dtetime . '_' . $uploaded_filename ;

          $file = request()->file('filename');
          $file->storeAs('nomination/WAN',$filename);
          $excel_obj = PHPExcel_IOFactory::load($file);
          $sheet = $excel_obj->setActiveSheetIndex(0);
          $return_data = array();
          $interval = 1;
          for ($row = 7; $row <= 30; ++$row) {

                $letter = 'B';
                for ($d=0;$d<=$max;$d++){
                    $nomination = $sheet->getCell( $letter . $row)->getValue();
                    $return_data[$d][$interval] = array('nomination' => $nomination);
                    $letter++;
                }
                
                $interval++;
          }

          $remarks = $sheet->getCell('C32')->getValue();
          session()->flash('message_uploading', 'Uploading successful');
          $nomination = array(
            'nomination_data' => $return_data,
            'remarks' => $remarks,
            'date_list' => $date_list,
            'date' => $date,
            'end_date' => $end_date
          );
          session()->flash('nomination', $nomination);
          return redirect()->back();  
    } // eof

    public function template(){    
        $templates = array();

        ## DAN
        $tomorrow = Carbon::parse('tomorrow');
        $filename = 'Nomination_DAN_'.$tomorrow->format('mdY').'.xlsx';
        $templates[] = array(
            'label' => 'Day Ahead Nomination',
            'value' => 'DAN',
            'daterange' => $tomorrow->format('m/d/Y'),
            'filename' => $filename
        );

        ## WAN
        $w_sdate = Carbon::parse('next saturday');
        $w_edate = Carbon::parse('next saturday')->addDays(6);
        $filename = 'Nomination_WAN_'.$w_sdate->format('mdY').'-'.$w_edate->format('mdY').'.xlsx';
        $templates[] = array(
            'label' => 'Week Ahead Nomination',
            'value' => 'WAN',
            'daterange' => $w_sdate->format('m/d/Y') . ' - '. $w_edate->format('m/d/Y'),
            'filename' => $filename
        );


        ### MAN
        $now = Carbon::now();
        $mn = $now->month;
        $yr = $now->year;
        $m_sdate = Carbon::parse($yr.'-'.$mn.'-26');

        $next_month = Carbon::parse('first day of next month');
        $next_mn = $next_month->month;
        $next_yr = $next_month->year;
        $m_edate = Carbon::parse($next_yr.'-'.$next_mn.'-25');

        $filename = 'Nomination_MAN_'.$m_sdate->format('mdY').'-'.$m_edate->format('mdY').'.xlsx';
        $templates[] = array(
            'label' => 'Month Ahead Nomination',
            'value' => 'MAN',
            'daterange' => $m_sdate->format('m/d/Y') . ' - '. $m_edate->format('m/d/Y'),
            'filename' => $filename
        );


        $customers = $this->get_customers();
        return view('nomination.template', compact('customers','templates'));
    } // eof template



    private function day_ahead_template($date,$filename){
        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setShowGridlines(false);
        $sheet->setTitle('Day Ahead Nomination');
        $sheet->getDefaultColumnDimension()->setWidth(5);
        $sheet->setCellValue('B2','DAY-AHEAD NOMINATION');
        $sheet->mergeCells('B2:C2');

        $formatted = Carbon::parse($date)->format('F d, Y');
        $sheet->setCellValue('B3','Trading Date: '.$formatted);
        $sheet->mergeCells('B3:C3');

        $sheet->setCellValue('B5','Interval');
        $sheet->setCellValue('C5','Nomination (kW)');
        
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(25);

        $sheet->getStyle('B2:C2')->applyFromArray(
            array('alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
                , 'font' => array('bold' => true, 'size'  => '12')
        ));

        $sheet->getStyle('B3:C3')->applyFromArray(
            array('alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
                , 'font' => array('bold' => true, 'size' => '10')
        ));

        $sheet->getRowDimension('5')->setRowHeight(20);
        $sheet->getStyle('B5:C5')->applyFromArray(
            array('fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('argb' => 'CCCCCC')
                )
                , 'alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
                , 'font' => array('bold' => true, 'size' => '10')
        ));


        $row_ctr = 6;
        $s = 0;
        for ($i=1;$i<=24;$i++){
            $interval_label = $i . '   ('. str_pad($s,2,"0",STR_PAD_LEFT) .':01 - '. str_pad($i,2,"0",STR_PAD_LEFT) .':00)';
            $sheet->setCellValue('B'.$row_ctr,$interval_label);
            $s++;
            $row_ctr++;
        }


        $last_row_ctr = $row_ctr -1;
        $sheet->getStyle('B5' . ':C' . $last_row_ctr)->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => '000000'),
                ),
                'outline' => array(
                  'style' => PHPExcel_Style_Border::BORDER_MEDIUM
                )
            )
        ));


        $sheet->getStyle('B6' . ':B' . $last_row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
        ));


        $sheet->getStyle('C6:C' . $last_row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'right',
                    'vertical' => 'top'
                )
        ));


        ## remarks
        $row_ctr++;
        $sheet->setCellValue('B'.$row_ctr,'Remarks');
        $sheet->setCellValue('C'.$row_ctr,'Sample Remarks');
        $sheet->getStyle('B'.$row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
                , 'font' => array('bold' => true, 'size' => '10')
        ));


        $writer = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007','HTML');
        $writer->save($filename);
    } // eof day_ahead_template


    private function daterange_ahead_template($sdate,$edate,$filename,$title){
        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setShowGridlines(false);
        $sheet->setTitle($title);
        $sheet->getDefaultColumnDimension()->setWidth(20);
        $sheet->setCellValue('A2',$title);
        $sheet->getStyle('A2')->applyFromArray(
            array('alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
                , 'font' => array('bold' => true, 'size'  => '14')
        ));

        $sheet->setCellValue('A4','Interval');
        $sheet->mergeCells('A4:A6');
        $sheet->setCellValue('B4','Nominations (kW)');
        $sheet->setCellValue('B5','Delivery Date');

        // interval
        $row_ctr = 7;
        $s = 0;
        for ($i=1;$i<=24;$i++){
            $interval_label = $i . '   ('. str_pad($s,2,"0",STR_PAD_LEFT) .':01 - '. str_pad($i,2,"0",STR_PAD_LEFT) .':00)';
            $sheet->setCellValue('A'.$row_ctr,$interval_label);
            $s++;
            $row_ctr++;
        }

        $start_date = date("Y-m-d", strtotime($sdate));
        $end_date = date("Y-m-d", strtotime($edate));
        $date = $start_date;
        $my_end_date = $end_date;
        $days_ctr = 1;
        $letter = 'B';
        $letter_start = $letter;
        $letter_end = $letter;
        while (strtotime($date) <= strtotime($my_end_date)) {
            $date_index = date('d-M-Y', strtotime($date));
            $sheet->setCellValue($letter.'6',$date_index);
            $letter_end = $letter;
            
            $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
            $days_ctr++;
            $letter++;
        }
        $sheet->mergeCells('A2:'.$letter_end.'2');
        $sheet->mergeCells('B3:'.$letter_end.'3');
        $sheet->mergeCells('B4:'.$letter_end.'4');
        $sheet->mergeCells('B5:'.$letter_end.'5');
        $sheet->getRowDimension('4')->setRowHeight(20);
        $sheet->getRowDimension('5')->setRowHeight(20);
        $sheet->getStyle('A4:'.$letter_end.'6')->applyFromArray(
            array('alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
                , 'font' => array('bold' => true, 'size' => '10')
        ));

        $last_row_ctr = $row_ctr -1;
        $sheet->getStyle('A4' . ':' .$letter_end . $last_row_ctr)->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => '000000'),
                ),
                'outline' => array(
                  'style' => PHPExcel_Style_Border::BORDER_MEDIUM
                )
            )
        ));


        $sheet->getStyle('A4:'.$letter_end.'6')->applyFromArray(
            array('fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('argb' => 'CCCCCC')
                )
                , 'alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
                , 'font' => array('bold' => true, 'size' => '10')
        ));


        $sheet->getStyle('A6' . ':A' . $last_row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
        ));


        $sheet->getStyle('B7:' .$letter_end . $last_row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'right',
                    'vertical' => 'top'
                )
        ));


        ## remarks
        $row_ctr++;
        $sheet->setCellValue('B'.$row_ctr,'Remarks');
        $sheet->setCellValue('C'.$row_ctr,'Sample Remarks');
        $sheet->getStyle('B'.$row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
                , 'font' => array('bold' => true, 'size' => '10')
        ));


        $writer = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007','HTML');
        $writer->save($filename);
    } // eof day_ahead_template



    public function file_template(Request $request){
        $template = request('template');
        $daterange = request('daterange');
        $filename = request('filename');

          if ($template === 'DAN') {
            $this->day_ahead_template($daterange,$filename);   
          }else {
            $tmp = explode('-',$daterange);
            $sdate = trim($tmp[0]);
            $edate = trim($tmp[1]);

            $title = 'WEEK AHEAD NOMINATION';
            if ( $template != 'WAN') {
                 $title = 'MONTH AHEAD NOMINATION';
            }
            $this->daterange_ahead_template($sdate,$edate,$filename,$title);   
          }

          return Response::download($filename,$filename, 
           [
           'Content-Description' => "File Transfer",
            "Content-Disposition" => "attachment; filename=".$filename]
            )->deleteFileAfterSend(true);


    } // eof file_template


    /* Override Nomination */
    public function override()
    {
        $participants = Participant::orderBy('participant_name','asc')->get()->pluck('participant_name','id');
        $customers = Customer::orderBy('customer_name','asc')->get()->pluck('customer_name','id');
        $current_date = date('Y-m-d');
        $default_date = Carbon::now()->addDay()->format('m/d/Y');
        return view('nomination.override', compact('participants','customers','current_date','default_date'));
    }



}
