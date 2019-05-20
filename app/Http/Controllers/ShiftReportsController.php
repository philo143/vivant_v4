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
use App\ShiftReports\LogTradingShiftReport;
use App\PlantCapabilityAudit;
use App\PlantCapabilityStatus;
use App\OfferAudit;
use PHPExcel; 
use PHPExcel_IOFactory;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Helper_HTML;
use Response;
use PDF;
use App;
class ShiftReportsController extends Controller
{
    

    public function __construct(LogPlantShiftReport $plantShiftReportLogger, LogTradingShiftReport $tradingShiftReportLogger)
    {
        $this->plantShiftReportLogger = $plantShiftReportLogger;
        $this->tradingShiftReportLogger = $tradingShiftReportLogger;
        $this->middleware('auth');
    }

    public function plantList(){
      
       // get role_id of trader role
  	   $trader_role_id = Role::where('name','plant_operator')->value('id');

  	   $plant_operators = User::whereHas('roles', function($q) use ($trader_role_id) {
	        $q->where('role_id', $trader_role_id);
	        })->pluck('fullname','id')->toArray();


      $user = User::with('user_plant','user_resource')->where('id',auth()->id())->first();
      $user_plant_obj = $user->user_plant;
      $user_resource_obj = $user->user_resource;

      if( $user_plant_obj != null ) {
           $plants = Plant::where('id',$user_plant_obj->plants_id)->orderBy('plant_name','asc')->get()->pluck('plant_name','id')->toArray();
      }else {
         $plants = Plant::orderBy('plant_name','asc')->get()->pluck('plant_name','id')->toArray();
      }

     

      $interval_data = getIntraIntervalDetails();
      $dte = $interval_data['date'];
      $hour = $interval_data['hour'];
      $min = $interval_data['min'];

      return view('trading_shift_reports.plant.list',compact('plant_operators','hour','min','plants'));


    } // eof plantList



    // method for retrieval
    public function plantRetrieve(Request $request){
          $s_date = Carbon::createFromTimestamp(strtotime(request('s_date')));
          $e_date = Carbon::createFromTimestamp(strtotime(request('e_date')));

          $query = PlantShiftReport::query();
          $query = $query->whereBetween('plant_shift_report.date', [$s_date,$e_date]);



          if ( $request->has('hour') ) {
            $hour = request('hour');
            $query = $query->where('plant_shift_report.hour', $hour);
          }


          if ( $request->has('plant') ) {
            $plant = request('plant');
            $query = $query->where('plant_shift_report.plant_id', $plant);
          }


          if ( $request->has('submitted_by') ) {
            $submitted_by = request('submitted_by');
            $query = $query->where('submitted_by', $submitted_by);
          }

          $query->leftJoin('island_mode', function($join)
                 {
                     $join->on('island_mode.date', '=', 'plant_shift_report.date');
                     $join->on('island_mode.hour', '=', 'plant_shift_report.hour');
                     $join->on('island_mode.interval', '=', 'plant_shift_report.interval');
                     $join->on('island_mode.plant_id', '=', 'plant_shift_report.plant_id');
                 });
          $query->select('plant_shift_report.*','island_mode.im');

          $query->with('shift_report_type','user');
          $reports = $query->get(array('plant_shift_report.*'));

          // fix the collection to be returned
          $return = [];
          foreach ($reports as $report) {
                $hr = $report->hour;
                $interval = $report->interval;
                $dte = $report->date;
                $type_id = $report->type_id;
                $return[$dte][$hr][$interval][$type_id][] = $report;
            }


          ## get current intra interval value
         $interval_data = getIntraIntervalDetails();
         $dte = $interval_data['date'];
         $hour = $interval_data['hour'];
         $min = $interval_data['min'];

          $data = array(
              'list' => $return 
             ,'date' => $dte 
             ,'hour' => $hour
             ,'min' => $min 
            );
          return $data;
    } ///


    



    ### For Trading Shift Reports
    public function tradingList(){
    	$trader_role_id = Role::where('name','trader')->value('id');

    	$trader_users = User::whereHas('roles', function($q) use ($trader_role_id) {
		    $q->where('role_id', $trader_role_id);
		})->pluck('fullname','id')->toArray();

      $interval_data = getIntraIntervalDetails();
      $dte = $interval_data['date'];
      $hour = $interval_data['hour'];
      $min = $interval_data['min'];
      


      $user = User::with('user_plant','user_resource')->where('id',auth()->id())->first();
      $user_plant_obj = $user->user_plant;
      $user_resource_obj = $user->user_resource;

      if( $user_plant_obj != null ) {
           App::abort(403, 'Access denied');
      }



        return view('trading_shift_reports.trading.list',compact('trader_users','hour','min'));
    } // eof tradingList



    public function tradingStore(Request $request){
    	  $type_name = request('type');
        $report = request('report');
        $data = array(
            'hour' => request('hour'),
            'min' => request('interval'),
            'type' => $type_name,
            'report' => $report ,
            'submitted_by' => auth()->id()
        );

        $this->tradingShiftReportLogger->execute($data);


        return 'Trading Shift Report submitted successfully';
    } // eof tradingStore


    public function tradingRetrieve(Request $request){
          $s_date = Carbon::createFromTimestamp(strtotime(request('s_date')));
          $e_date = Carbon::createFromTimestamp(strtotime(request('e_date')));


          $query = TradingShiftReport::select('trading_shift_report.*','users.username','users.fullname');
          $query = $query->whereBetween('date', [$s_date,$e_date]);


          if ( $request->has('hour') ) {
            $hour = request('hour');
            $query = $query->where('hour', $hour);
          }


          if ( $request->has('submitted_by') ) {
            $submitted_by = request('submitted_by');
            $query = $query->where('submitted_by', $submitted_by);
          }

          
          $query->leftJoin('users', 'users.id', '=', 'trading_shift_report.submitted_by');
          $reports = $query->get();


          $return = [];

          // fix the collection to be returned
          foreach ($reports as $report) {
                $hr = $report->hour;
                $interval = $report->interval;
                $dte = $report->date;
                $type_id = $report->type_id;

                $return[$dte][$hr][$interval][$type_id][] = $report;
            }

          return $return;
    } //


    // #### Extraction 
    public function extractionIndex(){
        $plant_role_id = Role::where('name','plant_operator')->value('id');

         $plant_operators = User::whereHas('roles', function($q) use ($plant_role_id) {
            $q->where('role_id', $plant_role_id);
            })->pluck('fullname','id')->toArray();


        $trader_role_id = Role::where('name','trader')->value('id');

        $trader_users = User::whereHas('roles', function($q) use ($trader_role_id) {
            $q->where('role_id', $trader_role_id);
        })->pluck('fullname','id')->toArray();
         
        $plants = Plant::orderBy('plant_name','asc')->get()->pluck('plant_name','id')->toArray();


        $interval_data = getIntraIntervalDetails();
        $dte = $interval_data['date'];
        $hour = $interval_data['hour'];
        $min = $interval_data['min'];

        $user = User::with('user_plant','user_resource')->where('id',auth()->id())->first();
        $user_plant_obj = $user->user_plant;
        $user_resource_obj = $user->user_resource;

        if( $user_plant_obj != null ) {
             App::abort(403, 'Access denied');
        }


        return view('trading_shift_reports.extraction_list',compact('plant_operators','trader_users','hour','min','plants'));


    } // eof extractionIndex


    public function extractionData(array $request){

        // ### parameters
        $sdate = $request['sdate'];
        $edate = $request['edate'];
        $hours = explode(',',$request['hour']);
        $interval_l = explode(',',$request['interval']);

        $intervals = array();
        foreach ($interval_l as $value) {
          $intervals[] = $value;
        }

        $traders = $request['traders'];
        $plant_id = $request['plant_id'];
        $resource_id = $request['resource_id'];
        $plant_operator = $request['plant_operator']; 
        $report_type = $request['report_type']; 
        $island_mode = $request['island_mode']; 
        $content = $request['content']; 
        $shift_report_type = isset($request['shift_report_type']) ? $request['shift_report_type'] : 'all'; 

        $s_date = Carbon::createFromTimestamp(strtotime($sdate));
        $e_date = Carbon::createFromTimestamp(strtotime($edate));

        $is_with_plant = false;
        $is_with_trading = false;
        if ($shift_report_type == 'all') {
            $is_with_plant = true;
            $is_with_trading = true;
        } else if ($shift_report_type == 'trading') {
            $is_with_trading = true;
        } else if ($shift_report_type == 'plant') {
            $is_with_plant = true;
        }
        $data = array();


        ##########################################
        ## Trading Shift Report 
        ##########################################

        if ( $is_with_trading ) {

            $query_tsr = TradingShiftReport::query();
            $query_tsr = $query_tsr->whereBetween('date', [$s_date,$e_date]);


            if ( count($hours) > 0 ) {
              $query_tsr = $query_tsr->whereIn('trading_shift_report.hour', $hours);
            }


            if ( count($intervals) > 0 ) {
                $instring_intervals = "'" . implode("','", $intervals) . "'";
                $query_tsr = $query_tsr->whereRaw("minute(`interval`) in (".$instring_intervals.")");
                // $query_tsr = $query_tsr->whereIn('trading_shift_report.interval', $intervals);
            }

            
            if ( strlen($traders) >0 ) {
              $query_tsr = $query_tsr->where('trading_shift_report.submitted_by', $plant_operator);
            }

            if ( strlen($content) >0 ) {
              $query_tsr = $query_tsr->where('trading_shift_report.report','like', '%'.$content.'%');
            }


            
            $query_tsr->with('shift_report_type','user');
            $trading_shift_reports = $query_tsr->get();

            // fix the collection to be returned
            foreach ($trading_shift_reports as $report) {
                  $hr = $report->hour;
                  $interval = $report->interval;
                  $dte = $report->date;
                  $type_id = $report->type_id;
                  $report['field'] = 'Trading';

                  $plant = array('plant_name' => 'N/A');
                  $resource = array('resource_id' => 'N/A');
                  $report['plant'] = $plant;
                  $report['resource'] = $resource;
                  $report['im'] = 'N/A';
                  $data[$dte][$hr][$interval][] = $report;
              }

        } // tsr


        ##########################################
        ## Plant Shift Report 
        ##########################################
        if ($is_with_plant){

            $query_psr = PlantShiftReport::query();
            $query_psr = $query_psr->whereBetween('plant_shift_report.date', [$s_date,$e_date]);


            if ( count($hours) > 0 ) {
              $query_psr = $query_psr->whereIn('plant_shift_report.hour', $hours);
            }

            if ( count($intervals) > 0 ) {
                $instring_intervals = "'" . implode("','", $intervals) . "'";
                $query_psr = $query_psr->whereRaw("minute(plant_shift_report.`interval`) in (".$instring_intervals.")");
                // $query_psr = $query_psr->whereIn('plant_shift_report.interval', $intervals);
            }
            

            if ( strlen($plant_id) >0 ) {
              $query_psr = $query_psr->where('plant_shift_report.plant_id', $plant_id);
            }

            if ( strlen($resource_id) >0 ) {
              $query_psr = $query_psr->where('plant_shift_report.resource_id', $plant_id);
            }

            if ( strlen($plant_operator) >0 ) {
              $query_psr = $query_psr->where('plant_shift_report.submitted_by', $plant_operator);
            }


            if ( strlen($content) >0 ) {
              $query_psr = $query_psr->where('plant_shift_report.report','like', '%'.$content.'%');
            }

           
            if ( strlen($island_mode) > 0 ) {
                 $query_psr->leftJoin('island_mode', function($join)
                   {
                       $join->on('island_mode.date', '=', 'plant_shift_report.date');
                       $join->on('island_mode.hour', '=', 'plant_shift_report.hour');
                       $join->on('island_mode.interval', '=', 'plant_shift_report.interval');
                       $join->on('island_mode.plant_id', '=', 'plant_shift_report.plant_id');
                   })
                   ->where('island_mode.im', '=', $island_mode );
            }else {
                $query_psr->leftJoin('island_mode', function($join)
                   {
                       $join->on('island_mode.date', '=', 'plant_shift_report.date');
                       $join->on('island_mode.hour', '=', 'plant_shift_report.hour');
                       $join->on('island_mode.interval', '=', 'plant_shift_report.interval');
                       $join->on('island_mode.plant_id', '=', 'plant_shift_report.plant_id');
                   });
            }
           
            $query_psr->select('plant_shift_report.*','island_mode.im');

            $query_psr->with('shift_report_type','user','plant','resource');
            $plant_shift_report_list = $query_psr->get();

            foreach ($plant_shift_report_list as $report) {
                $hr = $report->hour;
                $interval = $report->interval;
                $dte = $report->date;
                $type_id = $report->type_id;
                $report['field'] = 'Plant';
                $data[$dte][$hr][$interval][] = $report;
            }
        } // end psr


        
        

        return $data;

    } // eof

    public function extractionCheckData(Request $request){
      
      $data = $this->extractionData($request->all());
      $file_format = request('file_format');
      $shift_report_type = request('shift_report_type');
      $sdate = request('sdate');
      $edate = request('edate');

      $s_date = Carbon::createFromTimestamp(strtotime($sdate))->format('Ymd');
      $e_date = Carbon::createFromTimestamp(strtotime($edate))->format('Ymd');

      $file = 'Trading_Plant_Shift_Report_'.$s_date . '_' . $e_date;
      if ($shift_report_type == 'trading') {
        $file = 'Trading_Shift_Report_'.$s_date . '_' . $e_date;
      }else if ($shift_report_type == 'plant') {
        $file = 'Plant_Shift_Report_'.$s_date . '_' . $e_date;
      } 
      $message = '';
      $success = 0;
      if ( count($data) > 0 ) {
          $file_ext = $file_format == 'excel' ? '.xlsx' : '.pdf';
          $message = $file . $file_ext;
          $success = 1;
      }else {
          $message = 'No available record';
          $success = 0;
      }

      $return = array(
          'success' => $success,
          'message' => $message,
          'data' => $data
      );
      return $return;
    } // eof



    private function extractionExcelFile(array $parameters, array $data,string $filename){

        $shift_report_type = isset($parameters['shift_report_type']) ? $parameters['shift_report_type'] : 'all'; 

        $is_with_plant = false;
        if ($shift_report_type == 'all') {
            $is_with_plant = true;
        } else if ($shift_report_type == 'plant') {
            $is_with_plant = true;
        }


        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setShowGridlines(false);
        $sheet->setTitle('Shift_Report');
        $sheet->getDefaultColumnDimension()->setWidth(15);
        $sheet->setCellValue('A1','OPERATIONS SHIFT REPORT');
        $sheet->setCellValue('A2','Date');
        $sheet->setCellValue('B2','Hour');
        $sheet->setCellValue('C2','Interval');
        $sheet->setCellValue('D2','Field');

        $last_letter = 'F';
        if ( $is_with_plant ) {

            $sheet->setCellValue('E2','Plant');
            $sheet->setCellValue('F2','Resource ID');
            $sheet->setCellValue('G2','Report Type');
            $sheet->setCellValue('H2','Shift Report');
            $last_letter = 'H';

        }else {

            $sheet->setCellValue('E2','Report Type');
            $sheet->setCellValue('F2','Shift Report');
        }
        $sheet->mergeCells('A1:'.$last_letter.'1');
        $sheet->getColumnDimension($last_letter)->setWidth(80);

        $sheet->getStyle('A1:'.$last_letter.'2')->applyFromArray(
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


        $sheet->getStyle('A1')->applyFromArray(
            array( 'font' => array('bold' => true, 'size' => '20')
        ));
        

        $row_ctr = 3;
        foreach ($data as $date => $by_date_list) {
          
            foreach ($by_date_list as $hr => $by_hour_list) {
              
                    foreach ($by_hour_list as $int => $by_int_list) {
                      
                        foreach ($by_int_list as $row) {
                            
                            $dte = Carbon::createFromTimestamp(strtotime($row['date']))->format('F d Y');
                            $time_arr = explode(':',$row['interval']);
                            $interval = str_pad($row['hour'],2,"0",STR_PAD_LEFT) . ':' . $time_arr[1] . 'H';

                            $sheet->setCellValue('A'.$row_ctr,$dte);
                            $sheet->setCellValue('B'.$row_ctr,$row['hour']);
                            $sheet->setCellValue('C'.$row_ctr,$interval);
                            $sheet->setCellValue('D'.$row_ctr,$row['field']);


                            if ( $is_with_plant ) {

                                $html = strip_tags($row['report']);
                                $sheet->setCellValue('E'.$row_ctr,$row['plant']['plant_name']);
                                $sheet->setCellValue('F'.$row_ctr,$row['resource']['resource_id']);
                                $sheet->setCellValue('G'.$row_ctr,$row['shift_report_type']['description']);
                                $sheet->setCellValue('H'.$row_ctr,$html);

                            }else {
                                $html = strip_tags($row['report']);
                                $sheet->setCellValue('E'.$row_ctr,$row['shift_report_type']['description']);
                                $sheet->setCellValue('F'.$row_ctr,$html);
                            }  

                            

                            $row_ctr++;

                        }


                  } // end for


            } // end for

        } // endfor
        $last_row_ctr = $row_ctr -1;

        $sheet->getStyle('A1' . ':'.$last_letter . $last_row_ctr)->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => '000000'),
                )
            )
        ));


        $sheet->getStyle('A1' . ':'.$last_letter . $last_row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
        ));


        $sheet->getStyle($last_letter . '3' . ':'.$last_letter . $last_row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'left',
                    'vertical' => 'top'
                )
        ));

       



        $writer = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007','HTML');
        $writer->save($filename);
        


    } //



     private function extractionPDFFile(array $parameters, array $data,string $filename){

        $shift_report_type = isset($parameters['shift_report_type']) ? $parameters['shift_report_type'] : 'all'; 

        $is_with_plant = false;
        
        if ($shift_report_type == 'all') {
            $is_with_plant = true;
        } else if ($shift_report_type == 'plant') {
            $is_with_plant = true;
        }

        $total_columns = 6;
        if (  $is_with_plant ){
            $total_columns = 8;
        }
        $html = '';
        $html .= '<style>';
        $html .= 'table td { ';
        $html .= 'font-family:sans-serif; font-size:10px; text-align:center;';
        $html .= '} ';
        $html .= '</style>';
        $html .= '<table width="100%" border="1" cellspacing="0" cellpadding="0">';
        $html .= '<tr><td style="font-weight:bold; font-size:20px; padding:10px; background-color:#b4c6e7;" colspan="'.$total_columns.'">OPERATIONS SHIFT REPORT</td></tr>';

        $html .= '<tr>';
        $html .= '<td style="font-weight:bold; font-size:12px; background-color:#b4c6e7;">Date</td>';
        $html .= '<td style="font-weight:bold; font-size:12px; background-color:#b4c6e7;">Hour</td>';
        $html .= '<td style="font-weight:bold; font-size:12px; background-color:#b4c6e7;">Interval</td>';
        $html .= '<td style="font-weight:bold; font-size:12px; background-color:#b4c6e7;">Field</td>';

        if ( $is_with_plant ) {
          $html .= '<td style="font-weight:bold; font-size:12px; background-color:#b4c6e7;">Plant</td>';
          $html .= '<td style="font-weight:bold; font-size:12px; background-color:#b4c6e7;">Resource ID</td>';
        }  
        $html .= '<td style="font-weight:bold; font-size:12px; background-color:#b4c6e7;">Report Type</td>';
        $html .= '<td  style="font-weight:bold; font-size:12px; background-color:#b4c6e7;">Shift Report</td>';
        $html .= '</tr>';

        foreach ($data as $date => $by_date_list) {
          
            foreach ($by_date_list as $hr => $by_hour_list) {
              
                    foreach ($by_hour_list as $int => $by_int_list) {
                      
                        foreach ($by_int_list as $row) {
                            
                            $dte = Carbon::createFromTimestamp(strtotime($row['date']))->format('F d Y');
                            $time_arr = explode(':',$row['interval']);
                            $interval = str_pad($row['hour'],2,"0",STR_PAD_LEFT) . ':' . $time_arr[1] . 'H';
                            $report = strip_tags($row['report']);

                            $html .= '<tr>';
                            $html .= '<td>'.$dte.'</td>';
                            $html .= '<td>'.$row['hour'].'</td>';
                            $html .= '<td>'.$interval.'</td>';
                            $html .= '<td>'.$row['field'].'</td>';

                            if ( $is_with_plant ) {
                              $html .= '<td>'.$row['plant']['plant_name'].'</td>';
                              $html .= '<td>'.$row['resource']['resource_id'].'</td>';
                            }  
                            $html .= '<td>'.$row['shift_report_type']['description'].'</td>';
                            $html .= '<td style="text-align:left;">'.$report.'</td>';
                            $html .= '</tr>';
                        }


                  } // end for


            } // end for

        } // endfor
        $html .= '</table>';

         PDF::loadHTML($html)->setPaper('legal', 'landscape')->setWarnings(false)->save($filename);
    } //

    public function extractionFile(Request $request){
        $data = $this->extractionData($request->all());

        $file_format = request('file_format');
        $shift_report_type = request('shift_report_type');
        $sdate = request('sdate');
        $edate = request('edate');

        $s_date = Carbon::createFromTimestamp(strtotime($sdate))->format('Ymd');
        $e_date = Carbon::createFromTimestamp(strtotime($edate))->format('Ymd');

        $file = 'Trading_Plant_Shift_Report_'.$s_date . '_' . $e_date;
        if ($shift_report_type == 'trading') {
          $file = 'Trading_Shift_Report_'.$s_date . '_' . $e_date;
        }else if ($shift_report_type == 'plant') {
          $file = 'Plant_Shift_Report_'.$s_date . '_' . $e_date;
        } 


        $file_ext = $file_format == 'excel' ? '.xlsx' : '.pdf';
        $filename = $file . $file_ext;


        if ( $file_format == 'pdf' ) {
            
            $this->extractionPDFFile($request->all(),$data,$filename);

        } else {
            $this->extractionExcelFile($request->all(),$data,$filename);
        }
        


         return Response::download($filename,$filename, 
           [
           'Content-Description' => "File Transfer",
            "Content-Disposition" => "attachment; filename=".$filename]
            )->deleteFileAfterSend(true);

    } ///

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
        case 'offer':
              $offer_log = OfferAudit::where('transaction_id',request('id'))->first()->toArray();
  
              $ret = $offer_log;
              return $ret;
        break;
        
        default:
          return response()->json(['Invalid Report Type'],422);
          break;
      }
          
      
    }

    
}
