<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\User;
use App\Role;
use App\TradingShiftReport;
use App\PlantShiftReport;
use App\Plant;
use App\IslandMode;
use App\ShiftReports\LogPlantShiftReport;
use App\PlantCapabilityAudit;
use App\PlantCapabilityStatus;
use App;
class PlantShiftReportController extends Controller
{
    

    public function __construct(LogPlantShiftReport $plantShiftReportLogger)
    {
        $this->plantShiftReportLogger = $plantShiftReportLogger;
        $this->middleware('auth');
    } //


    

    ### Plant Operational Shift Report - by plant operator 
    public function plantOpsIndex(){

        $user = User::with('user_plant','user_resource')->where('id',auth()->id())->first();
        $user_plant_obj = $user->user_plant;
        $user_resource_obj = $user->user_resource;

        if( $user_plant_obj == null ) {
            App::abort(403, 'Access denied');
        }

        $user_plant = $user_plant_obj->plants_id;
        $resource = $user_resource_obj->resources_id;
        
        $interval_data = getIntraIntervalDetails();
        $dte = $interval_data['date'];
        $hour = $interval_data['hour'];
        $min = $interval_data['min'];
      
        $plants = Plant::where('id',$user_plant)->get()->pluck('plant_name','id')->toArray();

        return view('plant_shift_reports.list',compact('hour','min','plants','resource','dte'));


    } // eof plantOpIndex


    public function listPlantOperators(Request $request){
        $plant = request('plant');

        $is_all_plant = 0;
        if ( $request->has('is_all_plant') ) {
            $is_all_plant = request('is_all_plant');
        }

        $role_ids = Role::where('has_plant','1')->get()->pluck('id')->toArray();


        if ($is_all_plant == 1) {
            $plant_operators = User::whereHas('role_user', function($q) use ($role_ids){
                        $q->whereIn('role_id', $role_ids);
                    }
                )
            ->get();
        }else {
            $plant_operators = User::whereHas('role_user', function($q) use ($role_ids){
                        $q->whereIn('role_id', $role_ids);
                    }
                )
                ->whereHas('user_plant', function($q) use ($plant){
                        $q->where('plants_id', $plant);
                    }
                )
            ->get();
        }
        

        return $plant_operators;
    }


    // method for saving trading shift report 
    // optional parameters :
    // hour and interval
    public function store(Request $request)
    {

        $type_name = request('type');
        $report = request('report');

        $data = array(
                'type' => $type_name,
                'plant_id' => request('plant'),
                'resource_id' => request('resource'),
                'hour' => request('hour'),
                'min' => request('min'),
                'date' => request('date'),
                'report' => $report ,
                'submitted_by' => auth()->id()
            );

        $this->plantShiftReportLogger->execute($data);

        

        return 'Plant Shift Report submitted successfully';
      
    }

    public function storeIslandMode(Request $request){

        $dte = Carbon::createFromTimestamp(strtotime(request('date')))->format('Y-m-d');
        $hour = request('hour');
        $min = request('min');
        $min = str_pad($min,2,"0",STR_PAD_LEFT);

        if ($min == '00' ) {
            $interval = str_pad($hour,2,"0",STR_PAD_LEFT) . ':'.$min.':00';
        }else {
            $prev_hour = $hour - 1;
            $interval = str_pad($prev_hour,2,"0",STR_PAD_LEFT) . ':'.$min.':00';
        }

        
        $plant_id = request('plant_id');
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

        $data = array(
                'type' => 'audit',
                'plant_id' => $plant_id,
                'resource_id' => 1 ,
                'report' => $report ,
                'submitted_by' => auth()->id()
            );

        $this->plantShiftReportLogger->execute($data);
        return 'Island mode submitted successfully';

    } // eof storeIslandMode

    public function transactions() 
    {
      switch (request('type')) {
        case 'plant_capability':
              $audit_data = PlantCapabilityAudit::find(request('id'))->toArray();
              $data = json_decode($audit_data['data']);
              $ret = array();
              $delivery_date = Carbon::createFromTimestamp(strtotime($data->delivery_date));
              
              foreach($data->status as $d => $int){
                $running_date = $delivery_date->format('m/d/Y');
                $ret[$d]['delivery_date'] = $running_date;
                foreach($int as $int => $val){
                  $status = PlantCapabilityStatus::find($val);
                  $ret[$d][$int]['status'] = $status->status;
                }
                $running_date = $delivery_date->modify('+1 day');
              }
              foreach($data->capability as $d => $int){
                foreach($int as $int => $val){
                  $ret[$d][$int]['capability'] = $val;
                }
              }
              foreach($data->description as $d => $int){
                foreach($int as $int => $val){
                  $ret[$d][$int]['description'] = $val;
                }
              }

              return $ret;
          break;
        
        default:
          return response()->json(['Invalid Report Type'],422);
          break;
      }
  }

}
