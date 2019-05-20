<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Resource;
use App\ReserveType;
use App\MmsReserveRtdPrice;
use App\NgcpNominationPrice;
use PHPExcel; 
use PHPExcel_IOFactory;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Helper_HTML;
use Response;
class MmsReserveRtdPricesController extends Controller
{
    

	public function __construct()
    {
        $this->middleware('auth');
    } 



    public function mmsReportIndex(){

    	$regions = array('LUZON' => 'LUZON', 'VISAYAS' => 'VISAYAS', 'MINDANAO' => 'MINDANAO');
    	$reserve_types = ReserveType::orderBy('type','asc')->pluck('description','type')->toArray();

    	return view('mms_data.reserve_prices.list',compact('regions','reserve_types'));

    } // eof

    
    private function generate_data($request){

    	$date = Carbon::createFromTimestamp(strtotime(request('date')))->format('Y-m-d');
        $resource_ids = explode(',',$request['resource_id']);
        $hours = explode(',',$request['hour']);
        $region = $request['region'];
        $reserve_class = $request['reserve_class'];
        $instring_hours = "'" . implode("','", $hours) . "'";
        $instring_resource = "'" . implode("','", $resource_ids) . "'";


        ### MMS DATA
        $query = "select delivery_date, delivery_hour, node_id, reserve_class, resource_id, price
				from mms_reserve_rtd_prices, resource_lookup
				where delivery_date = '$date'
				and node_id like '$region%'
				and reserve_class = '$reserve_class'
				and resource_id in ($instring_resource)";

        $records = DB::select($query);
        $data = array();

        foreach ($records as $row) {
              $resource_id = $row->resource_id;
              $delivery_date = $row->delivery_date;
              $delivery_hour = $row->delivery_hour;
              $node_id = $row->node_id;
              $reserve_class = $row->reserve_class;
              $price = $row->price;

              $rec = array();
              $rec['resource_id'] = $resource_id;
              $rec['delivery_date'] = $delivery_date;
              $rec['delivery_hour'] = $delivery_hour;
              $rec['node_id'] = $node_id;
              $rec['reserve_class'] = $reserve_class;
              $rec['price'] = $price;
              $rec['source'] = 'MMS';
              $key = $delivery_date.'|'.$resource_id . '|' . $reserve_class . '|' . $node_id .'|MMS';
              $data[$key][$delivery_hour] = $rec;
        } // end foreach


        ########################################################
        ### for NGCP Nomination Prices

        ## First, need to get correct plant and unit number
        $query_resources = Resource::query()->with('plant');
        if ( count($resource_ids) > 0 ) {
          $query_resources = $query_resources->whereIn('resource_id', $resource_ids);
        }
        $resources_data = $query_resources->get();
        $resource_list = array();
        foreach ($resources_data as $row) {
              $resource_id = $row->resource_id;

              $plant = $row->plant->plant_name;
              $unit_no = 'Unit ' . $row->unit_no;

              $resource_list[$plant.'_'.$unit_no] = array(
                    'plant' => $plant ,
                    'unit_no' => $unit_no,
                    'resource_id' => $resource_id
              );
        } // end foreach

        if ( count($resource_list) > 0 ) {

        	$query_ngcp = NgcpNominationPrice::query();
	        $query_ngcp = $query_ngcp->where('date', $date);

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

	        $ngcp_prices = $query_ngcp->get();
	        foreach ($ngcp_prices as $row) {
	              $plant = $row->plant;
	              $unit_no = $row->unit_no;
	              $resource_id = $plant.'_'.$unit_no;

	              if ( isset( $resource_list[$plant.'_'.$unit_no] ) ) {
	                  $resource_id = $resource_list[$plant.'_'.$unit_no]['resource_id'];  
	              }

	              $delivery_date = $row->date;
	              $reserve_class = $row->reserve_class;
	              $key = $delivery_date.'|'.$resource_id . '|' . $reserve_class . '|' . $region .'R|NGCP';
	              for ($i=1;$i<=24;$i++){
	                  $price = $row['hour'.$i];
	                  $rec = array('price' => $price);
	                  $rec['source'] = 'NGCP';

	                  $data[$key][$i] = $rec;
	              }
	              //
	        } // end foreach


        }
        

        return $data;
    }

    public function retrieve(Request $request){

    	$data = $this->generate_data($request->all());

        return $data;

    } // eof 


    public function file(Request $request){

    	$data = $this->generate_data($request->all());
    	$fdate = Carbon::createFromTimestamp(strtotime(request('date')))->format('Ymd');
    	$filename = $fdate . '_Reserve_Prices.xlsx';
    	$hours = explode(',',$request['hour']);
        $sources = array('mms','ngcp');

    	// ### generate excel file here 
        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setShowGridlines(false);
        $sheet->setTitle('Reserve_Schedules');
        $sheet->getDefaultColumnDimension()->setWidth(15);
        $sheet->setCellValue('B1','Realtime Reserve Prices');
        $sheet->setCellValue('B4','Date');
        $sheet->setCellValue('C4','Resource ID');
        $sheet->setCellValue('D4','Source');
        $sheet->setCellValue('E4','Area ID');
        $sheet->setCellValue('F4','Reserve Class');
        
        
        $letter = 'G';
        $last_letter = $letter;
        foreach ($hours as $hour) {
            $sheet->setCellValue($letter.'4','H'.$hour);
            $last_letter = $letter;
            $letter++;

        }

        $sheet->mergeCells('B1:'.$last_letter.'1');

        $sheet->getStyle('B4:'.$last_letter.'4')->applyFromArray(
            array('fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('argb' => 'f4f4f4')
                )
                , 'alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
                , 'font' => array('bold' => true)
        ));


        $sheet->getStyle('B1')->applyFromArray(
            array( 'font' => array('bold' => true, 'size' => '20')
        ));
        


        $row_ctr = 5;
        foreach ($data as $key => $row) {
            
            $tmp = explode('|', $key);

            $date = $tmp[0];
            $resource_id = $tmp[1];
            $reserve_class = $tmp[2];
            $area_id = $tmp[3];
            $source = $tmp[4];

            $date_label = Carbon::createFromTimestamp(strtotime($date))->format('Ymd');

            $sheet->setCellValue('B'.$row_ctr,$date);
            $sheet->setCellValue('C'.$row_ctr,$resource_id);
            $sheet->setCellValue('D'.$row_ctr,$source);
            $sheet->setCellValue('E'.$row_ctr,$area_id);
            $sheet->setCellValue('F'.$row_ctr,$reserve_class);
            

            $letter = 'G';
            foreach ($hours as $hour) {
                $price = '';

                if (  isset($row[$hour]) ) {
                   $price = $row[$hour]['price'];  
                }
                $sheet->setCellValue($letter.$row_ctr,$price);
                $letter++;

            }

            $row_ctr++;

        } // endfor


        $last_row_ctr = $row_ctr -1;

        $sheet->getStyle('B4' . ':'.$last_letter . $last_row_ctr)->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => '000000'),
                )
            )
        ));


        $sheet->getStyle('B4' . ':'.$last_letter . $last_row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'center',
                    'vertical' => 'center'
                )
        ));


        $sheet->getStyle('G5' . ':'.$last_letter . $last_row_ctr)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => 'right',
                    'vertical' => 'center'
                )
        ));

         $sheet->getStyle('G5' . ':'.$last_letter . $last_row_ctr)->getNumberFormat()->setFormatCode('###,###,###,##0.00');




        $writer = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007','HTML');
        $writer->save($filename);


        return Response::download($filename,$filename, 
           [
           'Content-Description' => "File Transfer",
            "Content-Disposition" => "attachment; filename=".$filename]
            )->deleteFileAfterSend(true);


    } // eof file export 



}
