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
use App\NgcpSchedule;
use App\MmsReserveRtdSchedule;


class ReserveScheduleController extends Controller
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
      return view('reserve.schedule.create',compact('plants','date'));


    } // eof create


    private function generate_data( $request ){


      $plant_id = $request['plant_id'];
      $plant = $request['plant'];
      $resource_ids = explode(',',$request['resource_ids']);
      $source= $request['source'];
      $sdate = Carbon::createFromTimestamp(strtotime($request['sdate']))->format('Y-m-d');
      $edate = Carbon::createFromTimestamp(strtotime($request['edate']))->format('Y-m-d');


      $plants = Plant::with('participant','aspa_types')->where('id', $plant_id)->first();
      $reserve_type = $plants->aspa_types->type;

      $query_resources = Resource::query()->with('plant');
      if ( count($resource_ids) > 0 ) {
        $query_resources = $query_resources->whereIn('resource_id', $resource_ids);
      }
      $resources_data = $query_resources->get();
      $resource_list = array();
      $unit_no_list = array();
      foreach ($resources_data as $row) {
            $resource_id = $row->resource_id;
            $plant = $row->plant->plant_name;
            $unit_no = 'Unit ' . $row->unit_no;

            $resource_list[$plant.'_'.$unit_no] = array(
                  'plant' => $plant ,
                  'unit_no' => $unit_no,
                  'resource_id' => $resource_id
              );


            $unit_no_list[$resource_id] = $unit_no;
      } // end foreach

      $data = array();
      $total_records = 0;
    
      if ( $source == 'ngcp') {
          $query_ngcp = NgcpSchedule::query();
          $query_ngcp = $query_ngcp->whereBetween('date', [$sdate,$edate]);
          $query_ngcp = $query_ngcp->where('reserve_class', $reserve_type);

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
                $date = $row->date;
                if ( isset( $resource_list[$plant.'_'.$unit_no] ) ) {
                    $resource_id = $resource_list[$plant.'_'.$unit_no]['resource_id'];  
                }

                for ($i=1;$i<=24;$i++){
                    $mw = $row['hour'.$i];
                    $data[$date][$resource_id][$i] = $mw;
                }
                //
                $total_records++;
          } // end foreach
      } else {
          $query_mms = MmsReserveRtdSchedule::query();
          $query_mms = $query_mms->whereBetween('delivery_date', [$sdate,$edate]);

          if ( count($resource_ids) > 0 ) {
            $query_mms = $query_mms->whereIn('resource_id', $resource_ids);
          }

          $mms_res_schedules = $query_mms->get();
          foreach ($mms_res_schedules as $row) {
                $resource_id = $row->resource_id;
                $mw = $row->mw;
                $date = $row->delivery_date;
                $delivery_hour = $row->delivery_hour;
                $data[$date][$resource_id][$delivery_hour] = $mw;
                $total_records++;
          } // end foreach
      }

      return array(
          'data' => $data ,
          'total_records' => $total_records,
          'resource_list' => $resource_list,
          'unit_no_list' => $unit_no_list
        );
    } // generate data 

    public function listByDate( Request $request ){
      
      $return = $this->generate_data($request->all());
      $data = $return['data'];
      $resource_list = $return['resource_list'];

      $total_records = $return['total_records'];

      $ret  = array(
          'data' => $data
         ,'total' => $total_records
         ,'resource_list' => $resource_list
      );
      return $ret;

    } // eof get capability data


    ## For Reserve Schedule History page
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
      return view('reserve.schedule.list',compact('plants','date'));


    } // eof list 


    public function generateFileLink( Request $request ) {
        $return = $this->generate_data($request->all());

        $data = $return['data'];
        $total_rows = $return['total_records'];

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

            $message = '<a id="filelink">'. $plant .'_SCHED_'.$reserve_type .'_'.$sdate . '_' . $edate . '.csv </a>' ;
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

    private function fileNgcpCsv( $data, $unit_nos, $resource_list, $plant ,$reserve_type , $filename){
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

            foreach ($data as $date_key => $per_resource_values) {
                
                foreach ($per_resource_values as $resource_id => $value) {
                    $unit_no = $unit_nos[$resource_id];
                    $mw = '';
                    if ( isset( $value[$i]  ) ) {
                        $mw = $value[$i];  
                    }
                    $records[] = $mw;
                }
                
            }  // for data

            fputcsv($file,$records); 

        } // end for i

        exit();
    } // generate ngcp file
    public function fileCsv( Request $request ){
        $return = $this->generate_data($request->all());

        $data = $return['data'];
        $total_rows = $return['total_records'];
        $unit_no_list = $return['unit_no_list'];
        $resource_list = $return['resource_list'];

        $plant = request('plant');
        $plant_id = request('plant_id');
        $sdate = Carbon::createFromTimestamp(strtotime(request('sdate')))->format('mdY');
        $edate = Carbon::createFromTimestamp(strtotime(request('edate')))->format('mdY');
        $source = request('source');


        // get plant data to get reserve class details
        $plants = Plant::with('participant','aspa_types')->where('id', $plant_id)->first();
        $reserve_type = $plants->aspa_types->type;

        $filename = $plant .'_SCHED_'.$reserve_type .'_'.$sdate . '_' . $edate . '.csv';


        if ( $source == 'ngcp' ) {
            $this->fileNgcpCsv( $data, $unit_no_list , $resource_list, $plant ,$reserve_type , $filename);
        }

        
    }


}
