<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Plant;
use App\Resource;
use Carbon\Carbon;
use PHPExcel; 
use PHPExcel_IOFactory;
use Session;
use App\PlantCapabilityStatus;
use App\PlantCapabilityType;
use App\PlantCapabilityAudit;
use App\PlantCapability;
use App\ShiftReports\LogPlantShiftReport;
use App\ShiftReports\LogTradingShiftReport;
use Illuminate\Support\Facades\DB;
use App\User;

class PlantCapabilityController extends Controller
{
    public function __construct(LogPlantShiftReport $plantShiftReportLogger, LogTradingShiftReport $tradingShiftReportLogger)
    {
        $this->plantShiftReportLogger = $plantShiftReportLogger;
        $this->tradingShiftReportLogger = $tradingShiftReportLogger;
        $this->middleware('auth');
    }
    public function realtimeIndex()
    {
    	

      // if users has selected plant access, only the plant he has access should be list
      $user = User::with('user_plant','user_resource')->where('id',auth()->id())->first();
      $user_plant_obj = $user->user_plant;
      $user_resource_obj = $user->user_resource;

      if( $user_plant_obj == null ) {
          $plants = Plant::orderBy('plant_name','asc')->pluck('plant_name','id')->toArray();
      }else {
          $user_plant = $user_plant_obj->plants_id;
          $resource = $user_resource_obj->resources_id;

          $plants = Plant::where('id',$user_plant)->get()->pluck('plant_name','id')->toArray();
      }

      

      $remarks = PlantCapabilityStatus::pluck('status','id')->toArray();
    	return view('plant_capability.realtime.list',compact('plants','remarks'));
    }
    public function store()
    {
      
      $this->validate(request(), [
          'capability.*.*' => 'numeric',
          'source.*.*' => 'in:RT,DAP,WAP',
          'hour.*.*' => 'required',
          'status.*.*' => 'required'
      ]);

      $resource = Resource::find(request('unit'));
      $delivery_date = Carbon::createFromTimestamp(strtotime(request('delivery_date')));
      $hour = request('hour');
      $source = PlantCapabilityType::where('type',request('source.0.1'))->first();
      $action = PlantCapability::where(['delivery_date'=>$delivery_date,'resources_id' => request('unit'),'hour'=>$hour[0][1],'plant_capability_type_id'=>$source->id])->exists() ? 'update' : 'insert' ;
      $i = 0;
      foreach(request('hour') as $day){
        $running_date = $delivery_date->format('Y-m-d');
        foreach($day as $key => $hour){
            $capability = request('capability.'.$i.'.'.$hour) != null ?  request('capability.'.$i.'.'.$hour) : 0 ; 
            $resource->plantCapability()->updateOrCreate(['delivery_date'=>$running_date,'resources_id' => request('unit'),'hour'=>$hour,'plant_capability_type_id'=>$source->id]
              ,['capability'=>$capability,'description'=>request('description.'.$i.'.'.$hour),'plant_capability_type_id'=>$source->id,'plant_capability_status_id'=>request('status.'.$i.'.'.$hour)]);
        }
        $running_date = $delivery_date->modify('+1 day');
        $i++;
      }

      $current_date = Carbon::now();
      $counter = PlantCapabilityAudit::select(DB::raw('where date(created_at) = '.$current_date->format('Y-m-d').''))->count();
      $counter = str_pad(($counter + 1),3,'0',STR_PAD_LEFT);
      $user = str_replace(' ','_',auth()->user()->fullname);
      $generate_trans_id = $source->type.'_'.$user.'_'.$resource->resource_id.'_'.$current_date->format('Ymd').'_'.$counter;
      
      $audit_data = array(
        'transaction_id' => $generate_trans_id,
        'action' => $action,
        'data' => json_encode(request()->except(['_token','hour'])),
        'user'  => $user,
        );

      //Save to audit table
      $insert_audit = PlantCapabilityAudit::create($audit_data);

      $report = $source->description.' Plant Availability submission for '.$resource->resource_id.'<br \>
                Transaction ID: <a href="#" class="trans_link" id="'.$insert_audit->id.'">'.$generate_trans_id.'</a>';
      $report_data = array(
          'type' => 'audit',
          'plant_id' => $resource->plant_id,
          'resource_id' => $resource->id,
          'report' => $report ,
          'submitted_by' => auth()->id()
      );
      //Save to trading shift reports table
      $this->tradingShiftReportLogger->execute($report_data);
      //Save to plant shift report table
      $this->plantShiftReportLogger->execute($report_data);

      return 'Plant Capability submitted successfully';
    }

    public function retrieve() 
    {
      $delivery_date = Carbon::createFromTimestamp(strtotime(request('delivery_date')));
      $plant_capability = PlantCapability::where(['resources_id'=>request('unit'),'delivery_date'=>$delivery_date])
                          ->join('plant_capability_type', 'plant_capability_type.id', '=', 'plant_capability_type_id')
                          ->select('*','plant_capability.description as desc')
                          ->get()
                          ->groupBy('type');
      return $plant_capability;
    }

    public function dayaheadIndex(){
        $remarks = PlantCapabilityStatus::pluck('status','id')->take(2)->toArray();

         // if users has selected plant access, only the plant he has access should be list
        $user = User::with('user_plant','user_resource')->where('id',auth()->id())->first();
        $user_plant_obj = $user->user_plant;
        $user_resource_obj = $user->user_resource;

        if( $user_plant_obj == null ) {
            $plants = Plant::orderBy('plant_name','asc')->pluck('plant_name','id')->toArray();
        }else {
            $user_plant = $user_plant_obj->plants_id;
            $resource = $user_resource_obj->resources_id;

            $plants = Plant::where('id',$user_plant)->get()->pluck('plant_name','id')->toArray();
        }


    	  return view('plant_capability.dayahead.list', compact('plants','remarks'));
    }


    public function dayAheadUploadTemplate(Request $request){
          $file = request()->file('filename');
          


          $this->validate( request(), [
                'filename' => 'required|max:10000|mimes:xlsx' 
          ]);

          $ext = $file->getClientOriginalExtension();
          $uploaded_filename = $file->getClientOriginalName();


          $dtetime = Carbon::now()->format('Ymd_His');
          $filename = $dtetime . '_' . $uploaded_filename ;

          $file = request()->file('filename');
          $file->storeAs('day_ahead_uploaded_files',$filename);
          $excel_obj = PHPExcel_IOFactory::load($file);
          $sheet = $excel_obj->setActiveSheetIndex(0);
          $return_data = array();
          for ($row = 10; $row <= 33; ++$row) {
                $interval       = $sheet->getCell('B' . $row)->getValue();
                $net_energy     = $sheet->getCell('D' . $row)->getValue();
                $remarks        = $sheet->getCell('E' . $row)->getValue();
                $description    = $sheet->getCell('F' . $row)->getValue();

                $return_data[$interval] = ['interval' => $interval, 'net_energy' => $net_energy, 'remarks' => $remarks, 'description' => $description];


          }

          session()->flash('message_uploading', 'Uploading successful');
          session()->flash('day_ahead_template_data', $return_data);
          return redirect()->back();  
    }


    ### Week Ahead Plant Capability
    public function weakaheadIndex(){

        $date = Carbon::parse('next saturday')->format('m/d/Y');
        $end_date = Carbon::parse('next saturday')->addDays(6)->format('m/d/Y');

        // need create initial date list
        $date_list = array();
        for($i=0;$i<=6;$i++){
            $x = Carbon::parse($date)->addDays($i)->format('Y-m-d');
            $date_list[] = $x;
        }

        $remarks = PlantCapabilityStatus::pluck('status','id')->take(2)->toArray();

        
        // if users has selected plant access, only the plant he has access should be list
        $user = User::with('user_plant','user_resource')->where('id',auth()->id())->first();
        $user_plant_obj = $user->user_plant;
        $user_resource_obj = $user->user_resource;

        if( $user_plant_obj == null ) {
            $plants = Plant::orderBy('plant_name','asc')->pluck('plant_name','id')->toArray();
        }else {
            $user_plant = $user_plant_obj->plants_id;
            $resource = $user_resource_obj->resources_id;

            $plants = Plant::where('id',$user_plant)->get()->pluck('plant_name','id')->toArray();
        }




        return view('plant_capability.weekahead.list', compact('plants','remarks','date','end_date','date_list'));
    }


    public function weekAheadUploadTemplate(Request $request){
          $file = request()->file('filename');
          
          $this->validate( request(), [
                'filename' => 'required|max:10000|mimes:xlsx' 
          ]);

          $ext = $file->getClientOriginalExtension();
          $uploaded_filename = $file->getClientOriginalName();


          $dtetime = Carbon::now()->format('Ymd_His');
          $filename = $dtetime . '_' . $uploaded_filename ;

          $file = request()->file('filename');
          $file->storeAs('day_ahead_uploaded_files',$filename);
          $excel_obj = PHPExcel_IOFactory::load($file);
          $sheet = $excel_obj->setActiveSheetIndex(0);
          
          $letter_sets = array();
          $letter_sets[] = array('B','F');
          $letter_sets[] = array('H','L');
          $letter_sets[] = array('N','R');
          $letter_sets[] = array('T','X');
          $letter_sets[] = array('Z','AD');
          $letter_sets[] = array('AF','AJ');
          $letter_sets[] = array('AL','AP');

          $return_data = array();
          $excel_date_list = array();
          $resource = $sheet->getCell('B2' )->getValue();
          foreach ($letter_sets as $key => $set) {
              $start_letter = $set[0];
              $end_letter = $set[1];
              
              $date_string      = trim(explode(':',$sheet->getCell($start_letter . '6' )->getValue())[1]);
              $dte              = Carbon::parse($date_string)->format('Y-m-d');
              

              $interval_data_set = array();
              for ($row = 10; $row <= 33; ++$row) {
                $letter = $start_letter;
                $interval       = $sheet->getCell($letter . $row)->getValue();
                $letter++;
                $letter++;

                $net_energy     = $sheet->getCell($letter . $row)->getValue();
                $letter++;
                
                $remarks        = $sheet->getCell($letter . $row)->getValue();
                $letter++;

                $description    = $sheet->getCell($end_letter . $row)->getValue();

                $return_data[$key][$interval] = ['interval' => $interval, 'net_energy' => $net_energy, 'remarks' => $remarks, 'description' => $description];



              } // end for row

              $excel_date_list[] = $dte;
              // $return_data[$key] = array(
              //     'date' => $dte
              //    ,'data' => $interval_data_set
              //   );
          }
          
          
          session()->flash('message_uploading', 'Uploading successful');
          session()->flash('week_ahead_template_data', $return_data);
          session()->flash('excel_date_list', $excel_date_list);
          return redirect()->back();  
    } //

    public function templates() 
    {
        // if users has selected plant access, only the plant he has access should be list
        $user = User::with('user_plant','user_resource')->where('id',auth()->id())->first();
        $user_plant_obj = $user->user_plant;
        $user_resource_obj = $user->user_resource;

        if( $user_plant_obj == null ) {
            $plants = Plant::orderBy('plant_name','asc')->pluck('plant_name','id')->toArray();
        }else {
            $user_plant = $user_plant_obj->plants_id;
            $resource = $user_resource_obj->resources_id;

            $plants = Plant::where('id',$user_plant)->get()->pluck('plant_name','id')->toArray();
        }


        return view('plant_capability.templates.list',compact('plants'));
    }
    public function downloadTemplate() 
    {
        // dd(request()->all());
        $plant = Plant::find(request('plant'));
        $resource = Resource::find(request('resource'));
        $delivery_date = Carbon::createFromTimestamp(strtotime(request('delivery_date')))->format('F d, Y');

        if(request('template_type') == 'day_ahead'){
          $filename = "Plant_Capability_Day_Ahead.xlsx";
        }elseif(request('template_type') == 'week_ahead'){
          $filename = "Plant_Capability_Week_Ahead.xlsx";
        }

        $file = storage_path().'/templates/'.$filename;
        $workbook = PHPExcel_IOFactory::load($file);
        $sheet = $workbook->getActiveSheet();

        if(request('template_type') == 'day_ahead'){
          $sheet->setCellValue('B6','Relevant Trading Day: '.$delivery_date);
          $sheet->setCellValue('B2',$resource->resource_id);
          $sheet->setCellValue('D8',$resource->resource_id);
        }elseif(request('template_type') == 'week_ahead'){
          $start_date = Carbon::createFromTimestamp(strtotime(request('delivery_date')))->format('Ymd');
          $end_date = Carbon::createFromTimestamp(strtotime(request('delivery_date')))->addDays(7)->format('Ymd');
          $letter = array();
          for($l = 'A';$l != 'BG';$l++){
            $letter[] = $l;
          }
          $n = 1;
          for($i=$start_date;$i<=$end_date;$i++){
            $sheet->setCellValue($letter[$n].'6','Relevant Trading Day: '.Carbon::createFromTimestamp(strtotime($i))->format('F d, Y'));
            $sheet->setCellValue($letter[$n].'2',$resource->resource_id);
            $sheet->setCellValue($letter[$n+2].'8',$resource->resource_id);
            $n = $n+6;
          }
        }
        
        $filename = 'PLANT_CAPABILITY'.strtoupper(request('template_type')).'_'.$delivery_date.'.xlsx';
        $writer = PHPExcel_IOFactory::createWriter($workbook,'Excel2007');
        $writer->save($filename);

        return response()->download($filename)->deleteFileAfterSend(true);
    }
}
