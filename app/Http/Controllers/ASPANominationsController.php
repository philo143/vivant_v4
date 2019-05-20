<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Plant;
use App\Resource;
use App\ASPANomination;
use App\ASPANominationAudit;
use PHPExcel; 
use PHPExcel_IOFactory;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Helper_HTML;
use PHPExcel_Cell;
use PHPExcel_Style_NumberFormat;
use Response;
use Auth;

class ASPANominationsController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth');
    } //


    public function input(){
    	$plants = Plant::orderBy('plant_name','asc')->get()->pluck('plant_name','id');
    	$current_date = date('Y-m-d');
        $default_date = Carbon::now()->format('m/d/Y');
    	return view('aspa.input', compact('plants','current_date','default_date'));
    } // eof


    public function upload(Request $request){
    	   $file = request()->file('filename');
          $this->validate( request(), [
                'filename' => 'required|max:10000|mimes:xlsx' 
          ]);

          $ext = $file->getClientOriginalExtension();
          $uploaded_filename = $file->getClientOriginalName();

          $dtetime = Carbon::now()->format('Ymd_His');
          $filename = $dtetime . '_' . $uploaded_filename ;

          $file = request()->file('filename');
          $file->storeAs('aspa_nomination',$filename);
          $excel_obj = PHPExcel_IOFactory::load($file);
          $sheet = $excel_obj->setActiveSheetIndex(0);

          $excel_date = PHPExcel_Style_NumberFormat::toFormattedString($sheet->getCell('D5')->getFormattedValue(), 'YYYY-MM-DD');
          $date = date('Y-m-d',strtotime($excel_date));
          $date2 = date('m/d/Y',strtotime($excel_date));
          // check the unit number if exists
          $plant_name = $sheet->getCell('C8')->getValue();
          $plant = Plant::where('plant_name',$plant_name)->first();
          if ( !$plant ) {
              return redirect()->back()->with('error','Invalid Plant Name at cell C8 ');
          }
          $plant_id = $plant->id;
          $resource_ids = array();
          $letter_indx = 1;
          $total_invalid_unit = 0;
          while( $sheet->getCell(PHPExcel_Cell::stringFromColumnIndex($letter_indx).'9')->getValue() != '' ){
                $unit_no_label = strtoupper($sheet->getCell(PHPExcel_Cell::stringFromColumnIndex($letter_indx).'9')->getValue());
                $unit_no = trim(str_replace("UNIT","", $unit_no_label));

                $resource = Resource::where('plant_id',$plant_id)
                  ->where('unit_no',$unit_no)
                  ->first();

                if ( $resource ) {
                    $resource_id = $resource->id;
                    $resource_ids[] = $resource_id;
                }else {
                    $total_invalid_unit++;
                    return redirect()->back()->with('error','Invalid Unit No '. $unit_no_label);
                    break;
                }
                $letter_indx+=7;          
          } //

          $user_id = Auth::user()->id;
          $r = 0;
          $audit_nominations = array();
          if ($total_invalid_unit == 0) {
              $letter_indx = 1;
              while( $sheet->getCell(PHPExcel_Cell::stringFromColumnIndex($letter_indx).'9')->getValue() != '' ){
                    $resource_id = $resource_ids[$r];

                    for($i=11;$i<=34;$i++){

                        $interval = $sheet->getCell('A'.$i)->getCalculatedValue();
                        $available_capacity = $sheet->getCell(PHPExcel_Cell::stringFromColumnIndex($letter_indx).$i)->getValue();
                        $pump = $sheet->getCell(PHPExcel_Cell::stringFromColumnIndex($letter_indx+1).$i)->getValue();
                        $rr = $sheet->getCell(PHPExcel_Cell::stringFromColumnIndex($letter_indx+2).$i)->getValue();
                        $cr = $sheet->getCell(PHPExcel_Cell::stringFromColumnIndex($letter_indx+3).$i)->getValue();
                        $dr = $sheet->getCell(PHPExcel_Cell::stringFromColumnIndex($letter_indx+4).$i)->getValue();
                        $rps = $sheet->getCell(PHPExcel_Cell::stringFromColumnIndex($letter_indx+5).$i)->getValue();
                        $nominated_price = $sheet->getCell(PHPExcel_Cell::stringFromColumnIndex($letter_indx+6).$i)->getValue();
                        $data_key = array(
                            'date' => $date,
                            'hour' => $interval,
                            'interval' => null,
                            'plant_id' => $plant_id,
                            'resource_id' => $resource_id
                        );
                        $data = array(
                            'available_capacity' => $available_capacity,
                            'pump' => $pump,
                            'rr' => $rr,
                            'cr' => $cr,
                            'dr' => $dr,
                            'rps' => $rps,
                            'nominated_price' => $nominated_price,
                            'filename' => $filename,
                            'submitted_by' => $user_id
                        );
                        
                        $aspa_nomination = ASPANomination::updateOrCreate($data_key,$data);

                        // audit
                        $audit_row = array(
                            'date' => $date,
                            'hour' => $interval,
                            'interval' => null,
                            'plant_id' => $plant_id,
                            'resource_id' => $resource_id,
                            'available_capacity' => $available_capacity,
                            'pump' => $pump,
                            'rr' => $rr,
                            'cr' => $cr,
                            'dr' => $dr,
                            'rps' => $rps,
                            'nominated_price' => $nominated_price,
                            'filename' => $filename,
                            'submitted_by' => $user_id
                        );
                        $audit_nominations[] = $audit_row;
                    }    

                    $letter_indx+=7;
                    $r++;          
              } //

              $audit_data = array(
                  'action' => 'insert',
                  'data' => json_encode($audit_nominations),
                  'user' => $user_id
              );
              $insert_audit = ASPANominationAudit::create($audit_data);
          }
          return redirect()->back()->with('success','Uploading successful')
            ->with('success_upload',1)
            ->with('plant_id',$plant_id)
            ->with('delivery_date',$date2);;
    } // eof

    
    public function data(Request $request)
    {
      $user_id = Auth::user()->id;
      $plant_id = $request->plant_id;
      $date = date('Ymd', strtotime($request->delivery_date));

      $nominations = ASPANomination::with('resource')
            ->where('date',$date)
            ->where('plant_id',$plant_id)
            ->get();

      $data = array();
      $resource_ids = array();
      $total_records = 0;
      foreach ($nominations as $row) {
         $data[$row->date][$row->hour][$row->resource_id] = $row;

         if ( !isset($resource_ids[$row->resource_id])) {
              $resource_ids[$row->resource_id] = $row->resource;
         }
         $total_records++;
      }

      $return = array(
        'data' => $data ,
        'date' => date('Y-m-d', strtotime($request->delivery_date)),
        'resource_ids' => $resource_ids,
        'total_records' => $total_records
      );
      return response()->json($return);
    } // eof data 


    public function store(Request $request){

      $user_id = Auth::user()->id;
      $user_name = Auth::user()->username;
      $plant_id = $request->plant;
      $resource_ids = $request->resource_ids;
      $resource_ids_list = explode(',',$resource_ids);
      $date = date('Y-m-d', strtotime($request->date));
      $date2 = date('m/d/Y', strtotime($request->date));
      $audit_nominations = array();
      foreach ($resource_ids_list as $resource_key) {
          $resource_id = trim(str_replace("resource_", "", $resource_key));

          for($i=1;$i<=24;$i++){
              $id = $request['id_resource-'.$resource_id.'_int-'.$i];

              $aspa_nomination = ASPANomination::find($id);
              if ($aspa_nomination ) {
                  $scheduled_capacity = $request['scheduled_capacity_resource-'.$resource_id.'_int-'.$i];
                  $dispatch_capacity = $request['dispatch_capacity_resource-'.$resource_id.'_int-'.$i];
                  $remarks = $request['remarks_resource-'.$resource_id.'_int-'.$i];

                  // remove commas
                  $scheduled_capacity= str_replace(',','',$scheduled_capacity);
                  $dispatch_capacity= str_replace(',','',$dispatch_capacity);

                  $input = array();
                  $input['scheduled_capacity'] =strlen($scheduled_capacity) ==0 ? null : $scheduled_capacity;
                  $input['dispatch_capacity'] =strlen($dispatch_capacity) == 0 ? null : $dispatch_capacity;
                  $input['remarks'] =strlen($remarks) ==0 ? null : $remarks;
                  $x = $aspa_nomination->update($input);

                  $audit_row = array(
                      'date' => $aspa_nomination->date,
                      'hour' => $aspa_nomination->hour,
                      'interval' => null,
                      'plant_id' => $aspa_nomination->plant_id,
                      'resource_id' => $aspa_nomination->resource_id,
                      'scheduled_capacity' => $scheduled_capacity,
                      'dispatch_capacity' => $dispatch_capacity,
                      'remarks' => $remarks,
                      'submitted_by' => $user_id
                  );
                  $audit_nominations[] = $audit_row;
              }
              
          }
      }


      $audit_data = array(
          'action' => 'update',
          'data' => json_encode($audit_nominations),
          'user' => $user_id
      );
      $insert_audit = ASPANominationAudit::create($audit_data);
      
       return back()->withInput()->with('success','ASPA Nomination submitted successfully')
        ->with('plant_id',$plant_id)
        ->with('delivery_date',$date2);
    } // eof store


    public function template() 
    {
        $file = 'ASPA_Nomination_Template.xlsx';
        return response()->download(storage_path().'/templates/'.$file);
    } // template


    public function view(){
      $plants = Plant::orderBy('plant_name','asc')->get()->pluck('plant_name','id');
      $current_date = date('Y-m-d');
      $default_date = Carbon::now()->format('m/d/Y');
      return view('aspa.view', compact('plants','current_date','default_date'));
    } // eof


    public function data_bydaterange(Request $request)
    {
      $user_id = Auth::user()->id;
      $plant_id = $request->plant_id;
      $sdate = date('Ymd', strtotime($request->sdate));
      $edate = date('Ymd', strtotime($request->edate));

      $nominations = ASPANomination::with('resource')
            ->whereBetween('date',[$sdate,$edate])
            ->where('plant_id',$plant_id)
            ->get();

      $data = array();
      $resource_ids = array();
      $total_records = 0;
      foreach ($nominations as $row) {
         $data[$row->date][$row->hour][$row->resource_id] = $row;

         if ( !isset($resource_ids[$row->resource_id])) {
              $resource_ids[$row->resource_id] = $row->resource;
         }
         $total_records++;
      }

      $return = array(
        'data' => $data ,
        'resource_ids' => $resource_ids,
        'total_records' => $total_records
      );
      return response()->json($return);
    } // eof data 



    public function file(Request $request){

        $plant_id = $request->plant_id;
        $sdate = date('Ymd', strtotime($request->sdate));
        $edate = date('Ymd', strtotime($request->edate));

        $nominations = ASPANomination::with('resource')
              ->whereBetween('date',[$sdate,$edate])
              ->where('plant_id',$plant_id)
              ->get();

        $data = array();
        $resource_ids = array();
        $total_records = 0;
        foreach ($nominations as $row) {
           $data[$row->date][$row->hour][$row->resource_id] = $row;

           if ( !isset($resource_ids[$row->resource_id])) {
                $resource_ids[$row->resource_id] = $row->resource;
           }
           $total_records++;
        }


        $plant_details = Plant::where('id',$plant_id)->first();
        $plant_name = $plant_details->plant_name;

        $filename = 'ASPANomination_'. $sdate. '_' . $edate. '.xlsx';

        ## EXCEL 
        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setShowGridlines(false);
        $sheet->getDefaultColumnDimension()->setWidth(15);
        
        ##  Header
        $sheet->setCellValue('A1','National Grid Corporation of the Philippines');
        $sheet->setCellValue('A2','Luzon System Operations');
        $sheet->setCellValue('A4','Ancillary Services Nomination/Renomination  (For Hydro/ non-Hydro Electric Plants)');
        $sheet->setCellValue('A6','Revision: 00');
        $sheet->setCellValue('A8','Generating Plant Name: ');
        $sheet->setCellValue('C8',$plant_name);
        $sheet->mergeCells('C8:L8');

        $letter = 'C';
        $last_letter = $letter;
        foreach ($resource_ids as $resource_id => $resource) {
            $unit_no = $resource['unit_no'];
            $unit_no_label = 'Unit ' . $unit_no;
            $letter_start = $letter;
            
            $sheet->setCellValue($letter .'9',$unit_no_label);
            $letter++;
            $letter++;
            $letter++;
            $letter++;
            $letter++;
            $letter++;
            $letter++;
            $letter++;
            $letter++;
            $last_letter = $letter;
            $sheet->mergeCells($letter_start.'9:'.$last_letter.'9');
        }
        
        ## Column Header
        $sheet->setCellValue('A9','Date');
        $sheet->mergeCells('A9:A10');

        $sheet->setCellValue('B9','Interval');
        $sheet->mergeCells('B9:B10');

        $letter = 'C';
        foreach ($resource_ids as $resource_id => $resource) {
            $sheet->setCellValue($letter .'10','Available Capacity (MW)');
            $letter++;
            $sheet->setCellValue($letter .'10','Pump (MW)');
            $letter++;
            $sheet->setCellValue($letter .'10','RR (MW)');
            $letter++;
            $sheet->setCellValue($letter .'10','CR (MW)');
            $letter++;
            $sheet->setCellValue($letter .'10','DR (MW)');
            $letter++;
            $sheet->setCellValue($letter .'10','RPS (Mvar)');
            $letter++;
            $sheet->setCellValue($letter .'10','Nominated Price (Pesos)');
            $letter++;
            $sheet->setCellValue($letter .'10','Scheduled Capacity');
            $letter++;
            $sheet->setCellValue($letter .'10','Dispatch Capacity');
            $letter++;
            $sheet->setCellValue($letter .'10','Remarks');
        }

        $row_ctr = 11;
        foreach ($data as $date => $date_data) {
            
            for ($i = 1;$i<=24; $i++){

              if ( isset($date_data[$i]) ) {
                  $hour_data = $date_data[$i];

                  $sheet->setCellValue('A'.$row_ctr,$date);
                  $sheet->setCellValue('B'.$row_ctr,$i);

                  // per resource 
                  $letter = 'C';
                  foreach ($resource_ids as $resource_id => $resource) {
                      $available_capacity = '';
                      $pump = '';
                      $rr = '';
                      $cr = '';
                      $dr = '';
                      $rps = '';
                      $nominated_price = '';
                      $scheduled_capacity = '';
                      $dispatch_capacity = '';
                      $remarks = '';

                      if ( isset($hour_data[$resource_id]) ) {
                          $available_capacity = $hour_data[$resource_id]['available_capacity'];
                          $pump = $hour_data[$resource_id]['available_capacity'];
                          $rr = $hour_data[$resource_id]['pump'];
                          $cr = $hour_data[$resource_id]['cr'];
                          $dr = $hour_data[$resource_id]['dr'];
                          $rps =$hour_data[$resource_id]['rps'];
                          $nominated_price = $hour_data[$resource_id]['nominated_price'];
                          $scheduled_capacity =$hour_data[$resource_id]['scheduled_capacity'];
                          $dispatch_capacity =$hour_data[$resource_id]['dispatch_capacity'];
                          $remarks = $hour_data[$resource_id]['remarks'];
                      }
                      $sheet->setCellValue($letter . $row_ctr,$available_capacity);
                      $letter++;
                      $sheet->setCellValue($letter . $row_ctr,$pump);
                      $letter++;
                      $sheet->setCellValue($letter . $row_ctr,$rr);
                      $letter++;
                      $sheet->setCellValue($letter . $row_ctr,$cr);
                      $letter++;
                      $sheet->setCellValue($letter . $row_ctr,$dr);
                      $letter++;
                      $sheet->setCellValue($letter . $row_ctr,$rps);
                      $letter++;
                      $sheet->setCellValue($letter . $row_ctr,$nominated_price);
                      $letter++;
                      $sheet->setCellValue($letter . $row_ctr,$scheduled_capacity);
                      $letter++;
                      $sheet->setCellValue($letter . $row_ctr,$dispatch_capacity);
                      $letter++;
                      $sheet->setCellValue($letter . $row_ctr,$remarks);
                  }
                  $row_ctr++;
              } // 
              
            } // for i

        }
       

        ## header formatting 
        $sheet->getStyle('A1:A4')->applyFromArray(
            array( 
                'font' => array('bold' => true, 'size' => '12')
                , 'alignment' => array(
                    'horizontal' => 'left',
                    'vertical' => 'center'
                ) 
            )
        );

        $sheet->getStyle('A9:'.$last_letter.'10')->applyFromArray(
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
        $sheet->getStyle('A9:'.$last_letter.'10')->getAlignment()->setWrapText(true); 



        ## border
        $last_row_ctr = $row_ctr -1;
        $sheet->getStyle('A9:'.$last_letter . $last_row_ctr)->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => '000000'),
                )
            )
        ));


        $sheet->getStyle('A11' . ':'.$last_letter.$last_row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
        ));

        
        // $sheet->getStyle('B6:' .$last_letter. $last_row_ctr)->getNumberFormat()->setFormatCode('###,###,###,##0.00');


        $writer = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007','HTML');
        $writer->save($filename);
        ## END OF EXCEL

        return Response::download($filename,$filename, 
               [
               'Content-Description' => "File Transfer",
                "Content-Disposition" => "attachment; filename=".$filename]
                )->deleteFileAfterSend(true);

    } // eof
}
