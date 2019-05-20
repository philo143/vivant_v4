<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\User;
use App\Role;
use App\TradingShiftReport;
use App\Plant;
use App\ShiftReports\LogTradingShiftReport;
use App\Resource;
use App\NgcpCapability;
use App\NgcpNomination;
use App\NgcpNominationPrice;
use App\NgcpNominationSubmissionAudit;

class ReserveNominationController extends Controller
{
    
    public function __construct(LogTradingShiftReport $tradingShiftReportLogger)
    {
        $this->tradingShiftReportLogger = $tradingShiftReportLogger;
        $this->middleware('auth');
    }



    ## Method for submission index 
    public function create(){
      
      $user = User::with('user_plant','user_resource')->where('id',auth()->id())->first();
      $user_plant_obj = $user->user_plant;
      $user_resource_obj = $user->user_resource;

      if( $user_plant_obj != null ) {
        $plants = Plant::where('id',$user_plant_obj->plants_id)->where('is_aspa',1)->orderBy('plant_name','asc')->get()->pluck('plant_name','id')->toArray();
      }else {
         $plants = Plant::where('is_aspa',1)->orderBy('plant_name','asc')->get()->pluck('plant_name','id')->toArray();
      }

      
      $date = Carbon::tomorrow()->format('m/d/Y');
      return view('reserve.nomination.create',compact('plants','date'));


    } // eof create


    public function listByDate( Request $request ){
    	
    	$plant_id = request('plant_id');
    	$plant = request('plant');
    	$resource_id = request('resource_id');
    	$resource_name= request('resource_name');
    	$date = Carbon::createFromTimestamp(strtotime(request('date')))->format('Y-m-d');

    	$resources = Resource::query()->where('id', $resource_id)->first();
    	$unit_no = 'Unit ' . $resources['unit_no'];

    	$plants = Plant::with('participant','aspa_types')->where('id', $plant_id)->first();
      	$reserve_type = $plants->aspa_types->type;

    	$data_cap = array();
    	$data_nom = array();
    	$data_price = array();
    	$total_records_cap = 0;
    	$total_records_nom = 0;
    	$total_records_price = 0;


    	## >> get NGCP Capabilities data
    	$query_cap = NgcpCapability::query();
        $query_cap = $query_cap->where('date', $date);
        $query_cap = $query_cap->whereRaw('plant = "'.$plant.'" and unit_no = "'.$unit_no.'"');
        $query_cap = $query_cap->where('reserve_type', $reserve_type);
        $capabilities = $query_cap->get();

        foreach ($capabilities as $row) {
              $plant_row = $row->plant;
              $unit_no = $row->unit_no;
              $delivery_date = $row->date;
              $reserve_type = $row->reserve_type;

              for ($i=1;$i<=24;$i++){
                  $mw = $row['hour'.$i];
                  $rec = array();
                  $rec['date'] = $delivery_date;
                  $rec['plant'] = $plant_row;
                  $rec['unit_no'] = $unit_no;
                  $rec['reserve_type'] = $reserve_type;
                  $rec['mw'] = $mw;
                  $data_cap[$i] = $rec;
              }
              $total_records_cap++;
        } // end foreach


        ## >> get NGCP Nomination data for MW column
        $query_nom = NgcpNomination::query();
        $query_nom = $query_nom->where('date', $date);
        $query_nom = $query_nom->whereRaw('plant = "'.$plant.'" and unit_no = "'.$unit_no.'"');
        $query_nom = $query_nom->where('reserve_type', $reserve_type);
        $nominations = $query_nom->get();

        foreach ($nominations as $row) {
              $plant_row = $row->plant;
              $unit_no = $row->unit_no;
              $delivery_date = $row->date;
              $reserve_type = $row->reserve_type;

              for ($i=1;$i<=24;$i++){
                  $mw = $row['hour'.$i];
                  $rec = array();
                  $rec['date'] = $delivery_date;
                  $rec['plant'] = $plant_row;
                  $rec['unit_no'] = $unit_no;
                  $rec['reserve_type'] = $reserve_type;
                  $rec['mw'] = $mw;
                  $data_nom[$i] = $rec;
              }
              $total_records_nom++;
        } // end foreach


        ## >> get NGCP Nomination Prices for price column
        $query_price = NgcpNominationPrice::query();
        $query_price = $query_price->where('date', $date);
        $query_price = $query_price->whereRaw('plant = "'.$plant.'" and unit_no = "'.$unit_no.'"');
        $query_price = $query_price->where('reserve_class', $reserve_type);
        $prices = $query_price->get();

        foreach ($prices as $row) {
              $plant_row = $row->plant;
              $unit_no = $row->unit_no;
              $delivery_date = $row->date;
              $reserve_type = $row->reserve_type;

              for ($i=1;$i<=24;$i++){
                  $mw = $row['hour'.$i];
                  $rec = array();
                  $rec['date'] = $delivery_date;
                  $rec['plant'] = $plant_row;
                  $rec['unit_no'] = $unit_no;
                  $rec['reserve_type'] = $reserve_type;
                  $rec['mw'] = $mw;
                  $data_price[$i] = $rec;
              }
              $total_records_price++;
        } // end foreach

        $error_message = "";
        $success = 0;

        if ( $total_records_cap > 0 ) {
        	$success = 1;
        }else {
        	$success = 0;
        	$error_message = "Please submit Reserve Capability first before Reserve Nomination";
        }
        $ret  = array(
            'data_cap' => $data_cap,
            'data_nom' => $data_nom,
            'data_price' => $data_price,
            'total_records_cap' => $total_records_cap,
            'total_records_nom' => $total_records_nom,
            'total_records_price' => $total_records_price,
            'success' => $success,
            'error_message' => $error_message
        );
        return $ret;

    } // eof get nomination and prices data



    public function save( Request $request ){
        $plant_id = request('plant_id');
        $plant = request('plant');
        $resource_id = request('resource_id');
        $resource_name= request('resource_name');
        $mode_operation = request('mode_operation');

        $dt = Carbon::createFromTimestamp(strtotime(request('date')));
        $date = Carbon::createFromTimestamp(strtotime(request('date')))->format('Y-m-d');
        $dte_today = Carbon::now();

        // get resource data to get unit number
        $resources = Resource::query()->where('id', $resource_id)->first();
        $unit_no = 'Unit ' . $resources['unit_no'];

        // get plant data to get reserve class details
        $plants = Plant::with('participant','aspa_types')->where('id', $plant_id)->first();
        $reserve_type = $plants->aspa_types->type;

        ## saving capability 4 parts
        ## 1. save to ngcp_nominations, ngcp_nomination_prices
        ## 2. save to ngcp_nominations_submission_audit
        ## 3. post to trading shift report
        ## 4. posting to wesm ngcp  ( temporarily off, waiting for miners and test data )

        $message = "";
        $success = 0;
        if ( $dt->lte($dte_today) ) {
            $message = "Invalid Input, Cannot save/post previous or current date";
        } else if ( $dt->isTomorrow() && date("H") >= 14   ){
            $message = "Invalid Input. Cut-off time for day-ahead is 2PM";
        } else {

            ## >>> saving steps start here 
            ## 1. save to ngcp_nominations, ngcp_nomination_prices tables
            $hourly_nom_data = array();
            $hourly_price_data = array();
            for ($i=1;$i<=24;$i++){
              if ( $request->has('mw'.$i) ) {
                 $hourly_nom_data['hour'.$i] = str_replace(',', '', request('mw'.$i)) ;
              }

              if ( $request->has('price'.$i) ) {
                 $hourly_price_data['hour'.$i] = str_replace(',', '', request('price'.$i)) ;
              }
             
            }

            NgcpNomination::updateOrCreate([
                  'date'=>$date,
                  'plant' => $plant,
                  'unit_no'=>$unit_no,
                  'reserve_type'=>$reserve_type
                  ],$hourly_nom_data);


            NgcpNominationPrice::updateOrCreate([
                  'date'=>$date,
                  'plant' => $plant,
                  'unit_no'=>$unit_no,
                  'reserve_class'=>$reserve_type
                  ],$hourly_price_data);



            ## 2. Save to audit table
            $user = str_replace(' ','_',auth()->user()->fullname);
            $audit_data = array(
              'action' => 'insert',
              'data' => json_encode(request()->except(['_token'])),
              'user'  => $user,
            );
            $insert_audit = NgcpNominationSubmissionAudit::create($audit_data);



            ## 3. post / save to trading shift report
            $current_time = date('Hi').'H';
            $user_fullname = User::where('id',auth()->id())->value('fullname');
            $report = ' ASPA Nomination for <b>'.$resource_name.'</b> has been submitted by <b>'.$user_fullname.'</b> at <b>'.$current_time . '</b>';
            $report_data = array(
                'type' => 'audit',
                'plant_id' => $plant_id,
                'resource_id' => $resource_id,
                'report' => $report ,
                'submitted_by' => auth()->id()
            );
            //Save to trading shift reports table
            $this->tradingShiftReportLogger->execute($report_data);



            #$message = "Successfully saved and posted nomination";
            $message = "Successfully saved nomination";
            $success = 1;
        }

        
        $ret = array(
            'message' => $message
           ,'success' => $success 
          );

        return $ret;
    } // end of saving nomination, nomination prices

    ## For Reserve Nomination History page
    public function list(){
      
      $user = User::with('user_plant','user_resource')->where('id',auth()->id())->first();
      $user_plant_obj = $user->user_plant;
      $user_resource_obj = $user->user_resource;

      if( $user_plant_obj != null ) {
        $plants = Plant::where('id',$user_plant_obj->plants_id)->where('is_aspa',1)->orderBy('plant_name','asc')->get()->pluck('plant_name','id')->toArray();
      }else {
         $plants = Plant::where('is_aspa',1)->orderBy('plant_name','asc')->get()->pluck('plant_name','id')->toArray();
      }

      
      $date = Carbon::tomorrow()->format('m/d/Y');
      return view('reserve.nomination.list',compact('plants','date'));


    } // eof list 

    private function generate_data( $request ){
        

        $plant_id = $request['plant_id'];
        $plant = $request['plant'];
        $unit_nos = explode(',',$request['unit_nos']);

        $sdate = Carbon::createFromTimestamp(strtotime($request['sdate']))->format('Y-m-d');
        $edate = Carbon::createFromTimestamp(strtotime($request['edate']))->format('Y-m-d');

        // get plant data to get reserve class details
        $plants = Plant::with('participant','aspa_types')->where('id', $plant_id)->first();
        $reserve_type = $plants->aspa_types->type;


       
        $query = NgcpNomination::query();
        $query = $query->whereBetween('date', [$sdate,$edate]);
        $query = $query->whereIn('unit_no', $unit_nos);
        $query = $query->where('reserve_type', $reserve_type);
        $records = $query->get();

        $data = array();
        $total_rows = 0;
        foreach ($records as $row) {
            $date = $row['date'];
            $unit_no = $row['unit_no'];
            $reserve_type = $row['reserve_type'];

            for ( $i=1;$i<=24;$i++ ) {
                $data[$date][$unit_no][$i] = $row['hour'.$i];
            }

            $total_rows++;

        } // end foreach

        return array(
            'data' => $data 
           ,'total_rows' => $total_rows 
          );

    }
    public function generateFileLink( Request $request ) {
        $return = $this->generate_data($request->all());

        $data = $return['data'];
        $total_rows = $return['total_rows'];

        $message = "";
        $success = 0;

        if (  $total_rows > 0 ){
            $plant = request('plant');
            $plant_id = request('plant_id');
            $sdate = Carbon::createFromTimestamp(strtotime(request('sdate')))->format('mdY');
            $edate = Carbon::createFromTimestamp(strtotime(request('edate')))->format('mdY');

            // get plant data to get reserve class details
            $plants = Plant::with('participant','aspa_types')->where('id', $plant_id)->first();
            $reserve_type = $plants->aspa_types->type;

            $message = '<a id="filelink">'. $plant .'_NOM_'.$reserve_type .'_'.$sdate . '_' . $edate . '.csv </a>' ;
            $success = 1;
        }else {
            $message = "No available data";
            $success = 0;
        }

        return array(
            'message' => $message ,
            'success' => $success
          );

    } // eof generate file link


    public function fileCsv( Request $request ){
        $return = $this->generate_data($request->all());

        $data = $return['data'];
        $total_rows = $return['total_rows'];

        $plant = request('plant');
        $plant_id = request('plant_id');
        $sdate = Carbon::createFromTimestamp(strtotime(request('sdate')))->format('mdY');
        $edate = Carbon::createFromTimestamp(strtotime(request('edate')))->format('mdY');
        $unit_nos = explode(',',request('unit_nos'));

        // get plant data to get reserve class details
        $plants = Plant::with('participant','aspa_types')->where('id', $plant_id)->first();
        $reserve_type = $plants->aspa_types->type;

        $filename = $plant .'_CAP_'.$reserve_type .'_'.$sdate . '_' . $edate . '.csv';


        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=".$filename);
        header("Pragma: no-cache");
        header("Expires: 0");
        $file = fopen('php://output', 'w');   


        $date_list = array();
        $headers1 = array("DATE");
        $headers2 = array("PLANT");
        $headers3 = array("UNIT No.");
        $headers4 = array("RES. TYPE");

        foreach ($data as $date_key => $value) {
            $date_list[] = $date_key;
            $date_label = Carbon::createFromTimestamp(strtotime($date_key))->format('m/d/Y');

            foreach ($unit_nos as $unit_no) {
                
                $headers1[] = $date_label;
                $headers2[] = $plant;
                $headers3[] = $unit_no;
                $headers4[] = $reserve_type;

            } // unit for

        }         

        fputcsv($file,$headers1);  
        fputcsv($file,$headers2);  
        fputcsv($file,$headers3);  
        fputcsv($file,$headers4); 


        // iterate data values for csv generation
        for ( $i=1; $i<=24;$i++){
            $records = array($i);

            foreach ($data as $date_key => $unit_values) {
                
                foreach ($unit_nos as $unit_no) {
                    $mw = '';  
                    if ( isset( $unit_values[$unit_no]  ) ) {

                        if ( isset( $unit_values[$unit_no][$i]  ) ) {
                            $mw = $unit_values[$unit_no][$i];  
                        }
                    }

                    $records[] = $mw;
                } // unit for


            }  // for data

            fputcsv($file,$records); 

        } // end for i

        exit();
    }


}
