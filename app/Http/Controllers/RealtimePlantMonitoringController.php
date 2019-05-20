<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Plant;
use App\Resource;
use App\MmsModRtd;
use App\IslandMode;
use App\ASPANomination;
use App\RTPMActualLoad;
use App\PlantShiftReport;
use App\TradingShiftReport;
use App\User;
use App\ShiftReports\LogPlantShiftReport;
use App\ShiftReports\LogTradingShiftReport;
use App\Events\DashboardActualLoadData;
use App;

class RealtimePlantMonitoringController extends Controller
{
    public function __construct(LogPlantShiftReport $plantShiftReportLogger, LogTradingShiftReport $tradingShiftReportLogger)
    {
        $this->plantShiftReportLogger = $plantShiftReportLogger;
        $this->tradingShiftReportLogger = $tradingShiftReportLogger;
        $this->middleware('auth');
    }


    /*
    Index function for Plant / Realtime Plant Monitoring page

    */
    public function plantIndex(){

        // get user plants plants only
        $user = User::with('user_plant','user_resource')->where('id',auth()->id())->first();
        $user_plant_obj = $user->user_plant;
        $user_resource_obj = $user->user_resource;

        if( $user_plant_obj == null ) {
            App::abort(403, 'Access denied');
        }

        $user_plant = $user_plant_obj->plants_id;
        $resource = $user_resource_obj->resources_id;

      	$plants = Plant::where('id',$user_plant)->get()->pluck('plant_name','id')->toArray();

        // get default values for resources
        $first_plant = Plant::where('id',$user_plant)->orderBy('plant_name', 'asc')->first();
        $plant_id_selected = $first_plant['id'];
        $resources = Resource::where('plant_id',$plant_id_selected)->get()->pluck('resource_id','id')->toArray();

        return view('realtime_plant_monitoring.plant.view'
        	,compact('plants',
        			'resources'
        	));

    } // eof plantIndex


    public function getResourceMmsModRtdSchedule($resource_id){
        
        $interval_data = getIntraIntervalDetails();
        $dte = $interval_data['date'];
        $hour = $interval_data['hour'];
        $min = $interval_data['min'];
        $interval = $interval_data['intra_interval'];
        $previous_hour = $interval_data['prev_hour'];
        $previous_interval = $interval_data['prev_intrainterval'];

        $previous_rtd_data = MmsModRtd::where(
            [
             'price_node'=>$resource_id,
             'date'=>$dte,
             'interval'=>$previous_interval
            ])->first();

        $current_rtd_data = MmsModRtd::where(
            [
             'price_node'=>$resource_id,
             'date'=>$dte,
             'interval'=>$interval
            ])->first();


        $current_rtd = $current_rtd_data['mw'];
        $current_actual = 0.00;
        $previous_rtd = $previous_rtd_data['mw'];
        $previous_actual = 0.00;
        
        return array(
            'current_rtd' => $current_rtd,
            'current_actual' => $current_actual,
            'previous_rtd' => $previous_rtd,
            'previous_actual' => $previous_actual,
            'current_hour' => $hour,
            'current_interval' => $interval,
            'previous_hour' => $previous_hour,
            'previous_interval' => $previous_interval,
            'current_time' => $interval,
            'previous_time' => $previous_interval

        );

    }

    public function retrieve(){
    	
       
        $resource_id = Resource::where('id',request('resource_id'))->value('resource_id');
        $rtd_schedules = $this->getResourceMmsModRtdSchedule($resource_id);

        $current_rtd = $rtd_schedules['current_rtd'];
        $current_actual = $rtd_schedules['current_actual'];
        $previous_rtd = $rtd_schedules['previous_rtd'];
        $previous_actual = $rtd_schedules['previous_actual'];
        $current_hour = $rtd_schedules['current_hour'];
        $current_interval = $rtd_schedules['current_interval'];
        $previous_hour = $rtd_schedules['previous_hour'];
        $previous_interval = $rtd_schedules['previous_interval'];
        $current_time = $rtd_schedules['current_time'];
        $previous_time = $rtd_schedules['previous_time'];

        $dte = Carbon::now()->format('Y-m-d');
        $list = MmsModRtd::where(
            [
             'price_node'=>$resource_id,
             'date'=>$dte
            ])->get();


        // fix the collection to be returned
        $data = array();
        foreach ($list as $rec) {
            
            $tmp_time = explode(':',$rec->interval);
            $hr = Carbon::createFromTime($tmp_time[0],$tmp_time[1],$tmp_time[2],'Asia/Manila')->hour;
            if ( $rec->interval == '00:00:00' ) {
                $hr = 24;
            }else {
                if ($tmp_time[1] != '00') {
                    $hr = $hr+1;
                }
            }
            

            $interval = $rec->interval;
            $data[$hr][$interval] = $rec;
        }


        // island mode
        $im = IslandMode::where(
            [
             'plant_id'=>request('plant_id'),
             'date'=>$dte,
             'hour'=>$current_hour,
             'interval'=>$current_time
            ])->value('im');


        // aspa nomination
         $aspa = ASPANomination::where(
            [
             'plant_id'=>request('plant_id'),
             'resource_id'=>request('resource_id'),
             'date'=>$dte,
             'hour'=>$current_hour,
             'interval'=>$current_time
            ])->first();


        // actual load
        $cur_actual_load = RTPMActualLoad::where(
            [
             'plant_id'=>request('plant_id'),
             'resource_id'=>request('resource_id'),
             'date'=>$dte,
             'interval'=>$current_time
            ])->first();

        $prev_actual_load = RTPMActualLoad::where(
            [
             'plant_id'=>request('plant_id'),
             'resource_id'=>request('resource_id'),
             'date'=>$dte,
             'interval'=>$previous_time
            ])->first();

        $actual_load_raw = RTPMActualLoad::where(
            [
             'plant_id'=>request('plant_id'),
             'resource_id'=>request('resource_id'),
             'date'=>$dte
            ])->get();

        $actual_load_list = array();
        foreach ($actual_load_raw as $rec) {
            $hr = $rec->hour;
            $interval = $rec->interval;
            $actual_load_list[$hr][$interval] = $rec;
        }



        $ret = array(
        	'current_rtd' => $current_rtd,
        	'current_actual' => $current_actual,
        	'previous_rtd' => $previous_rtd,
        	'previous_actual' => $previous_actual,
            'current_hour' => $current_hour,
            'current_interval' => $current_interval,
            'previous_hour' => $previous_hour,
            'previous_interval' => $previous_interval,
            'list' => $data,
            'island_mode' => $im,
            'aspa' => $aspa,
            'cur_actual_load' => $cur_actual_load,
            'prev_actual_load' => $prev_actual_load,
            'actual_load_list' => $actual_load_list,
            'date' => $dte
        );

        return $ret;
    } // eof getRealtimeData


    public function storeIslandMode(Request $request){

        // ### get current date, hour and interval values
        $interval_data = getIntraIntervalDetails();
        $dte = $interval_data['date'];
        $hour = $interval_data['hour'];
        $min = $interval_data['min'];
        $interval = $interval_data['intra_interval'];
        
        // ## get other passed parameters
        $plant_id = request('plant_id');
        $resource_id = request('resource_id');
        $im = request('im');
        $im_remarks = request('im_remarks');
        
        IslandMode::updateOrCreate([
            'date'=>$dte,
            'hour' => $hour,
            'interval'=>$interval,
            'plant_id'=>$plant_id
            ],[
            'im'=>$im,
            'submitted_by' =>  auth()->id()
            ]);

        // ### insert to plant shift report
        $im_text = $im == 1 ? 'ON' : 'OFF';
        $current_time = date('Hi').'H';
        $user_fullname = User::where('id',auth()->id())->value('fullname');
        $report = 'Island mode triggered <b>'. $im_text.'</b> due to <b>"'.$im_remarks.'"</b> by  <b>'.$user_fullname.'</b> at ' .$current_time;
        $this->storePlantShiftReport($plant_id,$resource_id,$report,'audit');

        return 'Island mode submitted successfully';

    } // eof storeIslandMode


    public function storeASPANomination(Request $request){

        // ### get current date, hour and interval values
        $interval_data = getIntraIntervalDetails();
        $dte = $interval_data['date'];
        $hour = $interval_data['hour'];
        $min = $interval_data['min'];
        $interval = $interval_data['intra_interval'];
        

        $this->validate(request(), [
          'dispatch_capacity' => 'required',
          'remarks' => 'required'
        ]);


        // ## get other passed parameters
        $plant_id = request('plant_id');
        $resource_id = request('resource_id');
        $dispatch_capacity = request('dispatch_capacity');
        $remarks = request('remarks');


        
        ASPANomination::updateOrCreate([
            'date'=>$dte,
            'hour' => $hour,
            'interval'=>$interval,
            'plant_id'=>$plant_id,
            'resource_id'=>$resource_id
            ],[
            'dispatch_capacity'=>$dispatch_capacity,
            'remarks'=>$remarks,
            'submitted_by' =>  auth()->id()
            ]);

        $resource_name = Resource::where('id',$resource_id)->value('resource_id');

        $user_fullname = User::where('id',auth()->id())->value('fullname');
        $current_time = date('Hi').'H';
        $report = $dispatch_capacity . ' MW ASPA dispatched advised by “<b>'.$remarks.'</b>” for <i>'.$resource_name.'</i> by <b>'.$user_fullname.'</b> at ' .$current_time;
        $this->storePlantShiftReport($plant_id,$resource_id,$report,'rtd');

    

       return 'ASPA Nomination submitted successfully';

    } // eof storeASPANomination


    public function storeActualLoad(Request $request){

        $interval_data = getIntraIntervalDetails($request->all());
        $dte = $interval_data['date'];
        $hour = $interval_data['hour'];
        $min = $interval_data['min'];
        $interval = $interval_data['intra_interval'];
        
        $this->validate(request(), [
          'actual_load' => 'required'
        ]);


        // ## get other passed parameters
        $plant_id = request('plant_id');
        $resource_id = request('resource_id');
        $actual_load = request('actual_load');
        
        RTPMActualLoad::updateOrCreate([
            'date'=>$dte,
            'hour' => $hour,
            'interval'=>$interval,
            'plant_id'=>$plant_id,
            'resource_id'=>$resource_id
            ],[
            'actual_load'=>$actual_load,
            'submitted_by' =>  auth()->id()
            ]);


        // check if the inputted interval is previous interval
        $current_interval_data = getIntraIntervalDetails();
        $submitted_interval = $interval;
        $previous_interval = $current_interval_data['prev_intrainterval'];
        if ($submitted_interval == $previous_interval ) {

            $date = $current_interval_data['date'];
            $previous_intrainterval = $current_interval_data['prev_intrainterval'];
            $previous_intrainterval_min = explode(':',$previous_intrainterval)[1];
            $hour = $current_interval_data['hour'];
            $real_hour = $hour-1;
            $list = RTPMActualLoad::where('date',$date)
                ->where('hour',$hour)
                ->whereRaw('minute(`interval`) = '.$previous_intrainterval_min)
                ->with('resource')
                ->get();

            $data = array();
            $resource_id_list = array();
            foreach ($list as $rec) {
                $resource_id = $rec->resource['resource_id'];
                $data[$resource_id] = array(
                    'actual_load' => $rec->actual_load
                );

                if (  !in_array($resource_id, $resource_id_list) ) {
                    $resource_id_list[] = $resource_id;
                }
            }

            $previous_intrainterval_min_s = $previous_intrainterval_min - 4;

            $message = array(
                'resource_id_list' => $resource_id_list,
                'data' => $data ,
                'hour' => $hour,
                'min' => $previous_intrainterval_min,
                'intrainterval' => $current_interval_data, 
                'interval' => 'Hour '.$hour.' (Prev Interval : '.$real_hour.':'.str_pad($previous_intrainterval_min_s,2,"0",STR_PAD_LEFT).' - '.$real_hour.':'.str_pad($previous_intrainterval_min,2,"0",STR_PAD_LEFT).'H)'
            );

            event(new DashboardActualLoadData($message));
        }

       return 'Actual Load submitted successfully';

    } // eof storeActualLoad


    public function retrievePlantShiftReport(){
        
        $interval_data = getIntraIntervalDetails();
        $dte = $interval_data['date'];
        $hour = $interval_data['hour'];
        $min = $interval_data['min'];
        $interval = $interval_data['intra_interval'];


        $shift_report = PlantShiftReport::where(
            [
             'plant_id'=>request('plant_id'),
             'resource_id'=>request('resource_id'),
             'date'=>$dte,
             'hour'=>$hour,
             'interval'=>$interval
            ])
            ->with('shift_report_type','user')
            ->get();

        return $shift_report;
    } //


    public function storePlantShiftReport($plant,$resource,$report,$type_name){
        $data = array(
                'type' => $type_name,
                'plant_id' => $plant,
                'resource_id' => $resource ,
                'report' => $report ,
                'submitted_by' => auth()->id()
            );

        $this->plantShiftReportLogger->execute($data);
        

    } // eof


    public function acknowledgeRTD(Request $request){

        $cur_datetime = Carbon::now();
        $dte = $cur_datetime->format('Y-m-d');
        
        // ## get other passed parameters
        $plant_id = request('plant_id');
        $resource_id = request('resource_id');
        $rtd = request('rtd');
        $hour = request('hour');
        // $t_interval = explode(':',request('interval'));
        // $interval_val = $t_interval[1];
        $interval = request('interval');
        $p_interval = request('interval');

        // need to check if with already record
        // if none, add submitted by to values
        $values_for_saving = [
            'rtd'=>$rtd,
            'rtd_acknowledged'=>1,
            'rtd_acknowledged_by' => auth()->id(),
            'rtd_acknowledged_dt' =>  date('Y-m-d H:i:s')
            ];


        $check_data = RTPMActualLoad::where(
            [
                 'date'=>$dte,
                'hour' => $hour,
                'interval'=>$interval,
                'plant_id'=>$plant_id,
                'resource_id'=>$resource_id
            ])->first();
        

        if ($check_data == null ) {
            $values_for_saving = [
                'rtd'=>$rtd,
                'rtd_acknowledged'=>1,
                'rtd_acknowledged_by' => auth()->id(),
                'rtd_acknowledged_dt' =>  date('Y-m-d H:i:s'),
                'submitted_by' => auth()->id()
                ];

        }

        RTPMActualLoad::updateOrCreate([
            'date'=>$dte,
            'hour' => $hour,
            'interval'=>$interval,
            'plant_id'=>$plant_id,
            'resource_id'=>$resource_id
            ],$values_for_saving);


        $resource_name = Resource::where('id',$resource_id)->value('resource_id');
        $plant_name = Plant::where('id',$plant_id)->value('plant_name');
        $user_fullname = User::where('id',auth()->id())->value('fullname');
        $current_time = date('Hi').'H';
        $report = '<b>['.$plant_name.']['.$resource_name.']</b> Interval '.$p_interval.'H RTD Acknowledged by <b>'.$user_fullname.'</b> at ' .$current_time;
        $this->storePlantShiftReport($plant_id,$resource_id,$report,'rtd');

       return 'Successfully acknowledged RTD';

    } // eof acknowledgeRTD




    ### FOR TRADING REALTIME PLANT MONITORING 
    public function tradingIndex(){


        $user = User::with('user_plant','user_resource')->where('id',auth()->id())->first();
        $user_plant_obj = $user->user_plant;
        $user_resource_obj = $user->user_resource;

        if( $user_plant_obj != null ) {
            App::abort(403, 'Access denied');
        }


        $plants = Plant::orderBy('plant_name', 'asc')->pluck('plant_name','id')->toArray();

        // get default values for resources
        $first_plant = Plant::orderBy('plant_name', 'asc')->first();
        $plant_id_selected = $first_plant['id'];
        $resources = Resource::where('plant_id',$plant_id_selected)->get()->pluck('resource_id','id')->toArray();


        return view('realtime_plant_monitoring.trading.view'
            ,compact('plants',
                    'resources'
            ));

    } // eof index


    public function retrieveTradingShiftReport(){
        
        $interval_data = getIntraIntervalDetails();
        $dte = $interval_data['date'];
        $hour = $interval_data['hour'];
        $min = $interval_data['min'];
        $interval = $interval_data['intra_interval'];


        $shift_report = TradingShiftReport::where(
            [
             'date'=>$dte,
             'hour'=>$hour,
             'interval'=>$interval
            ])
            ->with('shift_report_type','user')
            ->get();

        return $shift_report;
    } //


    public function acknowledgeAL(Request $request){

        $cur_datetime = Carbon::now();
        $date = $cur_datetime->format('Y-m-d');
        
        // ## get other passed parameters
        $plant_id = request('plant_id');
        $resource_id = request('resource_id');
        $rtd = request('rtd');
        $hour = request('hour');
        $interval = request('interval');
        $p_interval = request('interval');


        $resource_name = Resource::where('id',$resource_id)->value('resource_id');
        $plant_name = Plant::where('id',$plant_id)->value('plant_name');
        $user_fullname = User::where('id',auth()->id())->value('fullname');
        $current_time = date('Hi').'H';

        


        $query = RTPMActualLoad::query();
        $query = $query->where('date', $date);
        $query = $query->where('plant_id', $plant_id);
        $query = $query->where('resource_id', $resource_id);
        $query = $query->whereRaw('`interval` <= "'.$interval .'"');
        $list = $query->get();

        $reports = array();
        foreach ($list as $row) {
              $id = $row->id;
              $row_interval = $row->interval;

              $values_for_saving = [
                'actual_load_acknowledged'=>1,
                'actual_load_acknowledged_by' => auth()->id(),
                'actual_load_acknowledged_dt' =>  date('Y-m-d H:i:s')
              ];


              RTPMActualLoad::whereId($id)->update($values_for_saving);

               $report = '<b>['.$plant_name.']['.$resource_name.']</b> Interval '.$row_interval.'H Actual Load Acknowledged by <b>'.$user_fullname.'</b> at ' .$current_time;
               
               $reports[] = $report;
               
        } // end foreach

        $report_data = array(
          'type' => 'activity',
          'plant_id' => $plant_id,
          'resource_id' => $resource_id,
          'report' => '<br>' . implode('<br>', $reports) ,
          'submitted_by' => auth()->id()
        );

        $this->tradingShiftReportLogger->execute($report_data);
        
       return 'Successfully acknowledged Actual Load';

    } // eof acknowledge al
}
