<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Resource;
use Validator;
use Carbon\Carbon;
use PHPExcel; 
use PHPExcel_IOFactory;
use PHPExcel_Style_NumberFormat;
use App\OfferParser;
use App\OfferSubmissionUnits;
use App\OfferType;
use App\OfferParams;
use App\MmsRtem;
use App\MmsOpres;
use App\User;
use App\Plant;
use App\Participant;
use App\TradingShiftReport;
use App\TradingShiftReportType;
use Illuminate\Support\Facades\Artisan;
use App\OfferSender;
use App\OfferAudit;
use Yajra\Datatables\Datatables;
use App\Http\Traits\OfferStatus;
use App\Jobs\OfferStatusQueue;


class OffersController extends Controller
{
    use OfferStatus;
    public function __construct()
    {
        $this->middleware('auth');
    } //
    public function scheduledOfferindex()
    {
        $resources = Resource::pluck('resource_id','id');

        $start_date = strtotime('-7 days');
        $end_date = strtotime('+7 days');
        for ($x=$start_date;$x<=$end_date;$x=$x+86400) {
            $ctr = $x - strtotime('+0 day');
            $ctr = $ctr / 86400;
            $date_val = date('Ymd',$x);
            $arr_date[$date_val] = date('D, M d',$x)." ($ctr)";
        }
        $cur_time = Date('H:i');
        return view('offers.scheduled_offer', compact('resources','arr_date','cur_time'));
    }
    public function energyOfferIndex()
    {
    	$resources = Resource::pluck('resource_id','id');

        $start_date = strtotime('-7 days');
        $end_date = strtotime('+7 days');
        for ($x=$start_date;$x<=$end_date;$x=$x+86400) {
            $ctr = $x - strtotime('+0 day');
            $ctr = $ctr / 86400;
            $date_val = date('Ymd',$x);
            $arr_date[$date_val] = date('D, M d',$x)." ($ctr)";
        }
        $cur_time = Date('H');
    	return view('offers.energy_offer', compact('resources','arr_date','cur_time'));
    }
    public function uploadTemplate(Request $request)
    {

        $parser = new OfferParser;
	    $this->validate( request(), [
	            'file' => 'required|max:10000|mimes:xlsx,xls'
	    ]);
        $file = request()->file('file');
        $action = strtoupper(request('upload_action'));
        $type = request('template_type');
        if(!isset($type)) return redirect()->back()->with('errors', ['Invalid File.']);
        $template_type = array('standing'=> "SO",'energy'=>'EN','so_reserve'=>'SOR','da_reserve'=>'DAR');
        $tmp_type = $template_type[$type];
             
        $data = $parser->readEnergyTemplate($file,$tmp_type);
        if($data === null) return redirect()->back()->with('errors', ['Invalid Template.']);
        $data['delivery_date'] = $data['delivery_date'] != null ? Carbon::createFromTimestamp(strtotime($data['delivery_date'])) : '';
        $data['action'] = strtolower($action);

        session()->flash('message_uploading', 'Uploading successful');
        session()->flash('energy_offer_template_data', $data);
	   return redirect()->back();
    }
    public function dayAheadReserveindex()
    {
        $resources = Resource::pluck('resource_id','id');

        $start_date = strtotime('-7 days');
        $end_date = strtotime('+7 days');
        for ($x=$start_date;$x<=$end_date;$x=$x+86400) {
            $ctr = $x - strtotime('+0 day');
            $ctr = $ctr / 86400;
            $date_val = date('Ymd',$x);
            $arr_date[$date_val] = date('D, M d',$x)." ($ctr)";
        }
        return view('offers.day_ahead_reserve', compact('resources','arr_date'));
    }
    public function standingIndex()
    {
        $resources = Resource::pluck('resource_id','id');

        $start_date = strtotime('-7 days');
        $end_date = strtotime('+7 days');
        for ($x=$start_date;$x<=$end_date;$x=$x+86400) {
            $ctr = $x - strtotime('+0 day');
            $ctr = $ctr / 86400;
            $date_val = date('Ymd',$x);
            $arr_date[$date_val] = date('D, M d',$x)." ($ctr)";
        }
        return view('offers.standing_offer', compact('resources','arr_date'));
    }
    public function standingReserveindex()
    {
        $resources = Resource::pluck('resource_id','id');

        $start_date = strtotime('-7 days');
        $end_date = strtotime('+7 days');
        for ($x=$start_date;$x<=$end_date;$x=$x+86400) {
            $ctr = $x - strtotime('+0 day');
            $ctr = $ctr / 86400;
            $date_val = date('Ymd',$x);
            $arr_date[$date_val] = date('D, M d',$x)." ($ctr)";
        }
        return view('offers.standing_reserve', compact('resources','arr_date'));
    }
    public function convert()
    {
        $parser = new OfferParser;
        if(request('view') === 'web'){
            $xml = simplexml_load_string(request('xml'));
            $tmp = array();
            $tmp = $parser->convertXmlObjToArr($xml, $tmp);
            $ret = array();
            if($tmp){
                $ret['web'] = request('flag') == 'YES' ? $parser->convertSOToWeb($tmp) : $parser->convertToWeb($tmp) ; // convert to web values
                foreach($ret['web']["intervals"] as $k => $val){
                    $g = (strtotime(request('delivery_date')) > strtotime(date('Ymd'))) ? 'open' : 'close';
                    $ret['web']["intervals"][$k]["gate_closure"] = $g;
                }
            }
            return $ret;
        }

        if(request('view') === 'xml'){
            $data_arr = array();
            for($i=1;$i<=24;$i++){
                if(request('go-'.$i) == 1){
                    $data_arr['intervals'][$i]['price_quantity'] = request('web-price_qty-'.$i);
                    if(request('reserve_class') === null) $data_arr['intervals'][$i]['ramp_rate'] = request('web-ramp_rate-'.$i);                    
                    // $data_arr['intervals'][$i]['remarks'] = request('web-remarks-'.$i);
                }
            }
            $data_arr['resource_id'] = request('unit') ? request('unit') : 'DEFAULT';
            $plant_unit = Resource::where('resource_id',request('unit'))->first();
            if($plant_unit === null){
                return response()->json(['Invalid Unit'],400);
            }            
            $plant = Plant::with('participant')->find($plant_unit->plant_id);
            $data_arr['cert_user'] = Participant::where('id',$plant->participant_id)->pluck('cert_user')->first();
            $data_arr['delivery_date'] = request('delivery_date');
            $data_arr['action'] = strtoupper(request('action'));
            if(request('reserve_class') !== null) $data_arr['reserve_class'] = request('reserve_class');
            if(request('flag') === 'YES'){
                $data_arr['standing_flag'] = request('flag');
                $expiry_date = Carbon::createFromTimestamp(strtotime(request('expiry_date')));
                $data_arr['expiry_date'] = $expiry_date->format('Ymd');
                $data_arr['day_type'] = strtoupper(request('day_type'));
            }
            $compressed = $parser->compressFormat($data_arr);                                   
            $xml = $parser->convertToXML('DEFAULT', 'DEFAULT', $compressed);

            return htmlentities($xml);
        }        
    }
    public function submitOffer()
    {
        $offer_parser = new OfferParser();
        $plant_unit = Resource::where('resource_id',request('unit'))->first();
        if($plant_unit === null){
            return response()->json(['Invalid Unit'],400);
        } 
        $plant = Plant::with('participant')->find($plant_unit->plant_id);
        $participant_arr = Participant::where('id',$plant->participant_id)->first();
        $cert_user = $participant_arr['cert_user'];
        $participant = $participant_arr['id'];
        $delivery_date = date('Ymd', strtotime(request('delivery_date')));
        $resource_id = ($plant_unit->resource_id) ? $plant_unit->resource_id : 'DEFAULT';
        $offer_type = ($delivery_date == date('Ymd')) ? 'RTEM' : 'DAP';
        if(request('flag') !== null) $offer_type = 'SO';
        if(request('reserve_class') !== null) $offer_type = $offer_type.'R';
        $offer_type = OfferType::where('offer_type',$offer_type)->first();

        $xml = request('xml');

        //if web view convert to xml
        if(request('view') == 'web')
        {
            $b_arr = array();
            for($i=1;$i<=24;$i++){
                if(request('go-'.$i) == 1){                    
                    $b_arr['intervals'][$i]['price_quantity'] = request('web-price_qty-'.$i);
                    $b_arr['intervals'][$i]['ramp_rate'] = request('reserve_class') === null ? request('web-ramp_rate-'.$i) : '';
                    $b_arr['intervals'][$i]['remarks'] = request('flag') === null || request('reserve_class') !== null ? request('web-remarks-'.$i) : '';
                }
            }
            if(!$b_arr){
                return response()->json(['Nothing to submit'],400);
            }
            $required = Validator::make($b_arr['intervals'],[
                '*.price_quantity' => 'required',
                // '*.ramp_rate' => request('reserve_class') === null ? 'required' : '',
                // '*.remarks' => request('flag') === null ? 'max:50' : '',
            ]); 

            if($required->fails()){
                return response()->json($required->messages(),400);
            }          
            $b_arr['resource_id'] = $resource_id;
            $b_arr['delivery_date'] = $delivery_date;
            $b_arr['action'] = strtoupper(request('action'));
            $b_arr['cert_user'] = $cert_user;

            if(request('flag') === 'YES'){
                $b_arr['standing_flag'] = request('flag');
                $b_arr['expiry_date'] = request('expiry_date');
                $b_arr['day_type'] = strtoupper(request('day_type'));
            }
            if(request('reserve_class') !== null) $b_arr['reserve_class'] = request('reserve_class');            
            $b_compressed = $offer_parser->compressFormat($b_arr);
            $bid_arr = $b_compressed;
            $xml = $offer_parser->convertToXML('DEFAULT', 'DEFAULT', $bid_arr);

        }
        //Always process with xml format     
        $xml_ref = $xml;
        $xml = simplexml_load_string($xml);
        $tmp = array();
        $offer_parser->convertXmlObjToArr($xml, $tmp);

        $b = request('flag') == 'YES' ? $offer_parser->convertSOToWeb($tmp) : $offer_parser->convertToWeb($tmp) ;

        $audit_data = array();
        //insert data to table
        $xmlContent[0] = $tmp;
        foreach($xmlContent as $bid_xml)
        {
            $b = request('flag') == 'YES' ? $offer_parser->convertSOToWeb($bid_xml) : $offer_parser->convertToWeb($bid_xml) ;           
            $data = array();
            for($i=1; $i<=24; $i++){                                               
                if(!isset($b['intervals'][$i])){
                    $b['intervals'][$i]['price_quantity'] = null;
                    $b['intervals'][$i]['ramp_rate'] = null;
                    // $b['intervals'][$i]['remarks'] = null;
                }

                // PRICE AND QUANTITY 
                $price_quantity = preg_replace('/\),\(/','|', $b['intervals'][$i]['price_quantity']);
                $price_quantity = preg_replace('/\(|\)/', '', $price_quantity);
                $price_quantity = str_replace(';', '', $price_quantity);
                $price_quantity = explode('|', $price_quantity);

                foreach($price_quantity as $y => $pq){
                    $pq = explode(',', $pq);                    
                    $for_validation[$i]['price'][$y] = $pq[0] != null ? $pq[0] : null;
                    $for_validation[$i]['quantity'][$y] = (isset($pq[1])) ? $pq[1]: null;
                    $data[$i]['b_p'.$y] = $for_validation[$i]['price'][$y];
                    $data[$i]['b_v'.$y] = $for_validation[$i]['quantity'][$y];
                }
                // RAMP RATE
                if(request('reserve_class') === null){
                    $rr_tmp = preg_replace('/\),\(/','|', $b['intervals'][$i]['ramp_rate']);
                    $rr_tmp = preg_replace('/\(|\)/', '', $rr_tmp);     
                    $rr_tmp = str_replace(';', '', $rr_tmp);         
                    $rr_tmp = explode('|', $rr_tmp);
                    
                    $rr_tmp_cnt = count($rr_tmp);                
                    if($rr_tmp_cnt > 5){
                        return response()->json(['Error: (Interval '.$i.') Ramp rate pairs exceeded.'],422);
                    }
                    foreach($rr_tmp as $y => $rr){
                        $rr_tmp = explode(',', $rr);
                        $for_validation[$i]['breakpoint'][$y] = $rr_tmp[0] != null ? $rr_tmp[0] : null;
                        $for_validation[$i]['ramp_up'][$y] = (isset($rr_tmp[1])) ? $rr_tmp[1] : null;
                        $for_validation[$i]['ramp_down'][$y] = (isset($rr_tmp[2])) ? $rr_tmp[2] : null;
                        $data[$i]['breakpoint'.$y] = $for_validation[$i]['breakpoint'][$y];                                  
                        $data[$i]['ramp_up'.$y] = $for_validation[$i]['ramp_up'][$y];                                  
                        $data[$i]['ramp_down'.$y] = $for_validation[$i]['ramp_down'][$y];                                
                    }
                }
                // if(request('flag') === null || request('reserve_class') !== null) $data[$i]['remarks'] = $b['intervals'][$i]['remarks'];
            }
            // OFFER VALIDATION
            // if(request('reserve_class') === null){
            //     $offer_validations = Validator::make($for_validation, [
            //       '*.price' => 'match_block|offer_price',
            //       '*.quantity' => 'offer_qty',
            //       '*.breakpoint.*' => request('reserve_class') === null ? 'match_digits:'.$plant_unit->pmax : '',
            //       '*.ramp_up.*' => request('reserve_class') === null ? 'match_digits:'.$plant_unit->ramp_rate : '',
            //       '*.ramp_down.*' => request('reserve_class') === null ? 'match_digits:'.$plant_unit->ramp_rate : '',
            //     ]);
            //     if($offer_validations->fails()){
            //         return response()->json($offer_validations->messages(),422);
            //     }
            // }
            $units_data = array();
            $units_data['delivery_date'] = $delivery_date;
            $units_data['resources_id'] = $plant_unit->id;
            $units_data['participants_id'] = $participant;
            $units_data['status'] = 'Waiting';
            $units_data['submitted_by'] = auth()->user()->id;
            $units_data['offer_type_id'] = $offer_type->id;
            $units_data['action'] = strtoupper(request('action'));
            if(request('flag') == 'YES'){
                $units_data['expiry_date'] = Carbon::createFromTimestamp(strtotime(request('expiry_date')))->format('Ymd');;
                $units_data['day_type'] = request('day_type');                
            }
            if(request('reserve_class') !== null) {
                $reserve_class = array('DIS'=>'DISPATCH','REG'=>'REGULATION','REG'=>'REGULATING','CON'=>'CONTINGENCY','ILD'=>'INTERRUPTIBLE LOAD');
                $units_data['reserve_class'] = $reserve_class[request('reserve_class')];
                $units_data['opres_ramp_rate'] = request('opres_ramp_rate');
            };
            $offer_unit_save = OfferSubmissionUnits::create($units_data); // SAVE TO OFFER UNITS
            $offer_id = $offer_unit_save->id; 

            $offer_unit = OfferSubmissionUnits::find($offer_id);
            for($i=1;$i<=24;$i++){
                $data[$i]['delivery_date'] = $delivery_date;
                $data[$i]['hour'] = $i;
                $data[$i]['interval'] = null;
                $data[$i]['offer_submission_units_id'] = $offer_id;
                $data[$i]['submitted_by'] = auth()->user()->id;
                
                $offer_unit->offer_data()->insert($data[$i]); // SAVE TO OFFER DATA

                $audit_data['intervals'][$i] = $data[$i];
            } 
        } //end
        $b['resource_id'] = $resource_id;
        $b['delivery_date'] = $delivery_date;
        $bid_type = 'energy';
        if(request('flag') !== null) $bid_type = 'standing';
        if(request('reserve_class') !== null) $bid_type = 'reserve';
        $data = $offer_parser->prepareOfferParams($b, $bid_type);
        $data['action'] = strtoupper(request('action'));
        $data['participant'] = "participant";
        $data['user'] = $cert_user;
        if(request('flag') == 'YES'){
            $data['standing_flag'] = 'YES';
            $data['expiry_date'] = Carbon::createFromTimestamp(strtotime(request('expiry_date')))->format('Ymd');
            $data['day_type'] = request('day_type');
        }
        if(request('reserve_class') !== null) {
            $data['opres_ramp_rate'] = request('opres_ramp_rate') !== null ? request('opres_ramp_rate') : '';
            $data['reserve_class'] = request('reserve_class');
        }

        $offer_params = new OfferParams;
        $offer_params->OfferParams($data);
        $params_content = $offer_params->generate();
        // PARTICIPANT NAME LARAVEL $plant->participant->participant_name //
        //save params to table
        $offer_unit->generated_xml = $xml_ref;
        $offer_unit->offer_params = $params_content;

        if($offer_unit->save())
        {
            
            //$participant_info = $this->participant_ins()->getFull(DEF_PARTICIPANT_ID);
            // $participant_id = $this->participant_ins()->getIdByName($plant->participant->participant_name);
            $participant_info = Participant::find($plant->participant->id);
            $args = ' '
                    .$offer_id.' '
                    .$participant_info->cert_user.' '
                    .$participant_info->cert_pass.' '
                    .$participant_info->cert_file.' '
                    .$participant_info->cert_loc;
            $sender = new OfferSender;
            $return = $sender->sendParams($offer_id,$participant_info->cert_user,$participant_info->cert_pass,$participant_info->cert_file,$participant_info->cert_loc);

                      
            // return $return;
            // $return = shell_exec(PHP_PATH.'/php '.MMS_SCRAPER_PATH.'/engine/rtem.php '.$args);
            // FOR TESTING ONLY
            // $return = "Success: 123456 for 01RESOURCE_U01 and 20180719 TESTING ONLY";
           
            preg_match('/Success: (.*) for/' ,$return ,$response_trans_id);
            $response_trans_id = (trim($response_trans_id[1])!=='') ? $response_trans_id[1] : 'N/A - '.date("His");
            //SAVE GENERATED XML OLD
            if($offer_unit->update(['generated_xml' => $xml_ref,'response_str' => $return,'response_trans_id' => $response_trans_id,'status'=>"Waiting"])){
                //html result from wesm
                // $pretty_result = $offer_params->get_pretty_return($return); OLD RESULT FORMATTER
                $pretty_result = $return;
                echo '<div id="wesm_response"><pre>'.$pretty_result.'</pre></div>';
                
                ## OLD RETURN HAS INTERVAL/HOUR RETURN ## THIS CANNOT BE USED BECAUSE NEW RETURN DOESNT HAVE INTERVALS/HOURS 
                // $out_arr = $offer_params->getReturnInArray($pretty_result);
                // if(count($out_arr) > 0){
                //     foreach($out_arr as $key => $val){
                //         $offer_unit->offer_data()->where('hour', $key)->update(['return_code'=>$val]);
                //     }
                // }
            
            }else{
                return response()->json(["Err: Response from WESM: " . $return],422);
            }
        
        }
        else
        {
            return response()->json(['Err: Params not saved.'],422);
        }

        $offer_unit = OfferSubmissionUnits::with('offer_type')->find($offer_id);
        $transaction_id_type = $offer_unit->offer_type->offer_type;
        $formatted_transaction_id = $offer_params->generateTransID($transaction_id_type,$resource_id);
        
        $audit_data['transaction_id_wesm'] = $response_trans_id;
        // insert offer audit log old
        // $log_data = array();
        // $log_data['data'] = json_encode($audit_data);
        // $log_data['delivery_date'] = $delivery_date;
        // $log_data['resource_id'] = $resource_id;
        // $log_data['type'] = $offer_type;
        // $log_type_description = $offer_unit->offer_type->description;
        //NEW OFFER AUDIT
        $offer_audit = new OfferAudit;
        $offer_audit->transaction_id = $formatted_transaction_id;
        $offer_audit->data = json_encode($audit_data);
        $offer_audit->delivery_date = $delivery_date;
        $offer_audit->resource_id = $resource_id;
        $offer_audit->type = $offer_unit->offer_type;
        $offer_audit->submitted_by = auth()->user()->id;
        $offer_audit->save();

        ## trading shift report
        $cur_datetime = Carbon::now();
        $dte = $cur_datetime->format('Y-m-d');
        $cur_hour = $cur_datetime->hour;
        $cur_int = $cur_datetime->addHour(1)->hour;
        $cur_min = $cur_datetime->minute;
        $min = $cur_min;
        if($cur_min %5 != 0) {
          $rem = ($cur_min % 5);
          $min = $cur_min - $rem;
        }
        $min = str_pad($min,2,"0",STR_PAD_LEFT);
        $time = $cur_hour.':'.$min.':00';
        $current_user_displayname = auth()->user()->fullname;
        $offer_action = $offer_unit->action == 'submit' ? 'submitted' : 'cancelled';
        $shift_report_type = TradingShiftReportType::where('type','audit')->first();
        $sr_data['type_id'] = $shift_report_type->id;
        $sr_data['date'] = $dte;
        $sr_data['hour'] = $cur_int;
        $sr_data['interval'] = $time;
        $sr_data['submitted_by'] = auth()->user()->id;
        $sr_data['report'] = '<b>'.$offer_unit->offer_type->description .'</b> '.$offer_action.' by <b>'. $current_user_displayname . '</b>'.
            ' for resource <b>'. $resource_id .'</b> and delivery date <i>'. date('Ymd',strtotime($delivery_date)) .'</i>'.
            '. Transaction ID : <a href="#" class="trans_link" id="'.$formatted_transaction_id.'">'.$formatted_transaction_id.'</a>';

        $trading_shift_report_save = TradingShiftReport::create($sr_data);

        $p_dispatch = array(
                'participant' => $participant_info->participant_name,
                'bid_id'      => $response_trans_id,
                'resource_id' => $resource_id);
        dispatch(new OfferStatusQueue($p_dispatch)); // NEEDS QUEUE WORKER/LISTENER RUNNING

        // end of insert to offer audit
        //Added July 2016 Email to Trading Manager// - Bry                      FOR FINALIZATION V4
        // $email_offer_description = 'Dear Trading Manager, <br \><br \><b>'.Date('[M d, Y] [H:i:s]').' '.base64_decode($_SESSION["username"]).'<b><br \>
        //     '.$log_type_description . ' '.$offer_action.' by <b>'. $current_user_displayname . '</b>'.
        //     ' for resource <b>'. $resource_id .'</b> and delivery date <i>'. date('Ymd',strtotime($delivery_date)) .'</i>'.
        //     '. Transaction ID : '.$formatted_transaction_id.' ';

        // $email_details = array(
        //     'offer_details' => $email_offer_description,
        //     'subject'      => '[PHINMA] '.$offer_type.' - '.$resource_id.' '.$delivery_date,
        //     'transaction_id' => $formatted_transaction_id
        // );
        // $this->email_offer_to_tm($email_details);
        
        //end email data//
    }
    public function retrieveOffer(Request $request)
    {
        $parser = new OfferParser;
        $delivery_date = Date('Y-m-d',strtotime($request->delivery_date));
        $unit = $request->unit;
        $resource_id = Resource::where('resource_id',$unit)->pluck('id')->first();
        $data_xml = OfferSubmissionUnits::where(['delivery_date' => $delivery_date,'resources_id'=>$resource_id])->pluck('generated_xml')->last();
        $xml = simplexml_load_string($data_xml);
        $tmp = array();
        $tmp = $parser->convertXmlObjToArr($xml, $tmp);
        $ret = array();
        if($tmp){
            $ret['web'] = request('flag') == 'YES' ? $parser->convertSOToWeb($tmp) : $parser->convertToWeb($tmp) ; // convert to web values
            foreach($ret['web']["intervals"] as $k => $val){
                $g = (strtotime(request('delivery_date')) > strtotime(date('Ymd'))) ? 'open' : 'close';
                $ret['web']["intervals"][$k]["gate_closure"] = $g;
            }
        }
        return $ret;

        /// OLD  RETRIEVE///
        // $date = Carbon::createFromTimestamp(strtotime(request('delivery_date')));
        // $resource = Resource::where('resource_id',request('unit'))->first();
        // if($resource === null){
        //     return response()->json(['Invalid Unit'],400);
        // } 
        // // $retrieve = $this->rep_latest_bids($date,$r['resource_id']); // WAITING FOR MINERS
        // // if(trim($retrieve) != ''){ // WAITING FOR MINERS
        // // for($i=1;$i<=24;$i++){
        // //     $make = new MmsOpres;
        // //     $make->reserve_class = 'DIS';
        // //     $make->date = '2017-05-25';
        // //     $make->hour = $i;
        // //     $make->price1 = '1000.0';
        // //     $make->price2 = '2000.0';
        // //     $make->qty1 = '100.0';
        // //     $make->qty2 = '200.0';
        // //     $make->opres_ramp_rate = '123.0';
        // //     $make->reason = 'MAN';

        // //     $resource->mmsOpres()->save($make);
        // // }
        //     if(request('reserve_class') === null){
        //         $data = MmsRtem::where(['resources_id'=>$resource->id,'date'=>$date])->get();
        //     }else{
        //         $data = MmsOpres::where(['resources_id'=>$resource->id,'date'=>$date,'reserve_class'=>request('reserve_class')])->get();
        //     }
        //     //dd($data);
        //     if(count($data) == 0){
        //         return response()->json(['No data to retrieve'],422);
        //     }
        //     $data_compressed = array();
        //     $opres_ramp_rate = '';
        //     foreach($data as $unit){
        //         $k = $unit['hour'];
        //         $price_qty = '';
        //         for($j=1;$j<=11;$j++){
        //             if(isset($unit['price'.$j]) && isset($unit['qty'.$j])){
        //                 if($price_qty != '') $price_qty .= ',';
        //                 $price_qty .= '('.round($unit['price'.$j],2).','.round($unit['qty'.$j],2).')';
        //             }
        //         }
                
        //         $ramp_rate = '';
        //         for($j=1;$j<=5;$j++){
        //             if($unit['breakpoint'.$j] || $unit['ramp_up'.$j] || $unit['ramp_down'.$j]){
        //                 if($ramp_rate != '') $ramp_rate .= ',';
        //                 $ramp_rate .= '('.round($unit['breakpoint'.$j],2).','.round($unit['ramp_up'.$j],2).','.round($unit['ramp_down'.$j],2).')';
        //             }
        //         }
                
        //         $data_compressed['intervals'][$k]['price_quantity'] = $price_qty;
        //         $data_compressed['intervals'][$k]['ramp_rate'] =  $ramp_rate;
        //         $data_compressed['intervals'][$k]['remarks'] = $unit['reason'];
        //         if(request('reserve_class') !== null) $opres_ramp_rate = $unit['opres_ramp_rate'];
        //     }
        
        //     // FOR XML //
        //     $parser = new OfferParser;
        //     $data_compressed['resource_id'] = $resource->id;
        //     $data_compressed['delivery_date'] = $date;
        //     $data_compressed['action'] = strtoupper(request('action'));
        //     $b_compressed = $parser->compressFormat($data_compressed);

        //     $xml = $parser->convertToXML('DEFAULT', 'DEFAULT', $b_compressed);
        //     if(request('view') == 'web')
        //     {
        //         $xml = simplexml_load_string($xml);
        //         $tmp = array();
        //         $parser->convertXmlObjToArr($xml, $tmp);
        //         $ret = array();
        //         if($tmp){
        //             $ret['web'] = $parser->convertToWeb($tmp); // convert to web values
                    
        //             foreach($ret['web']["intervals"] as $k => $val){
        //                 if(request('reserve_class') !== null) {
        //                     unset($ret['web']['intervals'][$k]['ramp_rate']);
        //                     $ret['web']['opres_ramp_rate'] = round($opres_ramp_rate,2);
        //                 }
        //                 $g = (strtotime(request('delivery_date')) > strtotime(date('Ymd'))) ? 'open' : 'close';
        //                 $ret['web']["intervals"][$k]["gate_closure"] = $g;
        //             }
        //         }
                
        //         return $ret;         
        //     }else if(request('view') == 'xml'){            
        //         return htmlentities($xml);
        //     }
        // // }
    }
    // OFFER SUMMARY //
    public function summaryIndex() 
    {
        $offer_types = OfferType::all()->pluck('offer_type','id')->toArray();
        return view('offers.offer_summary',compact('offer_types'));
    }
    public function summaryData() 
    {
        $delivery_date = Carbon::createFromTimestamp(strtotime(request('delivery_date')))->format('Y-m-d');
        $offer_type_params = request('offer_type') == 0 ? array(1,2,3,4,5,6) : array(request('offer_type'));

        $data = OfferSubmissionUnits::with(array('resource'=>function($query){
                $query->select('id','resource_id');
                },'offer_type','user'))->whereIn('offer_type_id',$offer_type_params)->where('delivery_date',$delivery_date)->select(array('id','response_trans_id','resources_id','delivery_date','created_at','offer_type_id','submitted_by','action','status'));

        return Datatables::of($data)
                ->addColumn('download', function($a){
                    return '<button class="btn btn-success btn-sm download" id="'.$a->id.'"> Download &nbsp;&nbsp;<i class="glyphicon glyphicon-download-alt"></i> </button>';                    
                })
                ->edit_column('response_trans_id','@if($response_trans_id)
                                             <a href="#" id="{{$id}}" class="offer_info"><strong>{{$response_trans_id}}</strong></a>
                                       @else
                                            <a href="#" id="{{$id}}" class="offer_info"><strong>None</strong></a>
                                        @endif')
                ->edit_column('resource.resource_id','<strong>{{$resource["resource_id"]}}</strong>')
                
                ->edit_column('action', '{{ strtoupper($action) }}')
                ->edit_column('status', '@if($status == "Valid")
                                            <label id="s_{{$id}}" class="label label-success"><strong>{{strtoupper($status)}}<strong></label>
                                         @elseif($status == "Invalid")
                                            <label id="s_{{$id}}" class="label label-danger"><strong>{{strtoupper($status)}}<strong></label>
                                         @else
                                            <label id="s_{{$id}}"class="label label-info"><strong>{{strtoupper($status)}}<strong></label>
                                         @endif')

        ->make(true);
    }
    public function offerInfo() 
    {
        $data = OfferSubmissionUnits::with('resource','user')->find(request('id'));

        return $data;
    }
    public function downloadOfferInfo()
    {
        $offer = OfferSubmissionUnits::with('offer_data','resource','user','offer_type')->find(request('id'));
        $offer_type = $offer->offer_type->offer_type;
        $delivery_date = Carbon::createFromTimestamp(strtotime($offer->delivery_date))->format('d/m/Y');
        $filename_arr = array('RTEM'=>'Energy_Offer','DAP'=>'Energy_Offer','SO'=>'Standing_Offer','DAPR'=>'Day_Ahead_Reserve','SOR'=>'Standing_Offer_Reserve');
        $filename = $filename_arr[$offer_type].'.xlsx';
        $file = storage_path().'/templates/'.$filename;
        $workbook = PHPExcel_IOFactory::load($file);
        $sheet = $workbook->getActiveSheet();

        $sheet->setCellValue('D5',$offer->resource->resource_id);
        $sheet->setCellValue('D6',$delivery_date);
        if($offer_type == "SOR" || $offer_type == "SO"){
            $sheet->setCellValue('D7',Carbon::createFromTimestamp(strtotime($offer->expiry_date))->format('d/m/Y'));
            $sheet->setCellValue('X7',$offer->day_type);
        }

        if($offer_type == "SOR" || $offer_type == "DAPR"){
            $sheet->setCellValue('X6',$offer->opres_ramp_rate);
            $sheet->setCellValue('X5',$offer->reserve_class);
        }
        $row = 12;
        foreach($offer->offer_data as $i => $val){    
            for($x=0;$x<=21;$x++){
                if($x % 2 == 0){
                    $sheet->setCellValue(chr($x+66).($row),$val['b_p'.(($x)/2)]);
                }else{
                    $sheet->setCellValue(chr($x+66).($row),$val['b_v'.(($x-1)/2)]);
                }
            }

            if($offer_type == "RTEM" || $offer_type == "DAP" || $offer_type == "SO"){
                $rr_letter = 23;
                for($y=0;$y<=5;$y++){
                    $sheet->getCellByColumnAndRow($rr_letter,$row)->setValue($val['breakpoint'.$y]);
                    $rr_letter++;
                    $sheet->getCellByColumnAndRow($rr_letter,$row)->setValue($val['ramp_up'.$y]);
                    $rr_letter++;
                    $sheet->getCellByColumnAndRow($rr_letter,$row)->setValue($val['ramp_down'.$y]);
                    $rr_letter++;
                }
            }
            
            if($offer_type == "SOR" || $offer_type == "DAPR"){
                $sheet->setCellValue('X'.$row,$val['remarks']);
            } 
            if ($offer_type == "EN"){
                $sheet->setCellValue('AA'.$row,$val['remarks']);
            }
            $row++;
        }
        $filename = $offer->delivery_date.'-'.$filename;
        $writer = PHPExcel_IOFactory::createWriter($workbook,'Excel2007');
        $writer->save($filename);

        return response()->download($filename)->deleteFileAfterSend(true);
    }
    // OFFER TEMPLATES //
    public function templatesIndex() 
    {        
        return view('offers.offer_templates');
    }
    public function downloadTemplate() 
    {
        $template = request('template');
        $filename_arr = array('energy'=>'Energy_Offer','standing'=>'Standing_Offer_Reserve','da_reserve'=>'Day_Ahead_Reserve','so_reserve'=>'Standing_Offer_Reserve');
        $file = $filename_arr[$template].'.xlsx';

        return response()->download(storage_path().'/templates/'.$file);
    }
    public function getServerTime(){
        return Artisan::call('app:time');
    }
}
