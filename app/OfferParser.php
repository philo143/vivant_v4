<?php

namespace App;
use Carbon\Carbon;
use PHPExcel; 
use PHPExcel_IOFactory;
use PHPExcel_Style_NumberFormat;

class OfferParser {

	public function readEnergyTemplate($file,$type)
	{
		$uploaded_filename = $file->getClientOriginalName();
        $dtetime = Carbon::now()->format('Ymd_His');
        $workbook = PHPExcel_IOFactory::load($file);
        $sheet = $workbook->getActiveSheet();
		$resource_id = $sheet->getCell('D5')->getValue();
        $date = $sheet->getCell('D6')->getValue();
        $date = PHPExcel_Style_NumberFormat::toFormattedString($date, "M/D/YYYY");
        $bid = array();
        $row = 12;
        if($type == "EN"){
            if($sheet->getTitle() != "Energy Offer Form" || $sheet->getCell('C2')->getValue() != 'ENERGY OFFER FORM' 
                || $sheet->getCell('AM9')->getValue() != 'Remarks'){
                return null;
            }
        }
        if($type == "DAR"){
            if($sheet->getTitle() != "Day Ahead Reserve Offer" || $sheet->getCell('C2')->getValue() != 'DAY AHEAD RESERVE OFFER FORM'
                || $sheet->getCell('X9')->getValue() != 'Remarks' || $sheet->getCell('U5')->getValue() != 'Reserve Class'){
                return null;
            }
        }
        if($type == "SO"){
            if($sheet->getTitle() != "Standing Offer" || $sheet->getCell('C2')->getValue() != 'STANDING OFFER FORM'
                || $sheet->getCell('A7')->getValue() != 'Expiry Date:' || $sheet->getCell('V7')->getValue() != 'Day Type'){
                return null;
            }
        }
        if($type == "SOR"){
            if($sheet->getTitle() != "Standing Reserve Offer" || $sheet->getCell('C2')->getValue() != 'STANDING RESERVE OFFER FORM'
                || $sheet->getCell('X9')->getValue() != 'Remarks' || $sheet->getCell('U5')->getValue() != 'Reserve Class'
                || $sheet->getCell('A7')->getValue() != 'Expiry Date:' || $sheet->getCell('U7')->getValue() != 'Day Type'){
                return null;
            }
        }
        
        for ($x=1;$x<=24;$x++) {
            $j = 1;
            $price_quantity = '';
            $pair_complete = false;

            foreach(range('B','W') as $letter){
                if( strlen($sheet->getCell($letter.$row)->getFormattedValue()) > 0 ){
                    if($pair_complete)
                        $price_quantity .= ',';

                    if($j%2!=0){
                        $price_quantity .= '(';
                        $price_quantity .= $sheet->getCell($letter.$row)->getValue();
                        $price_quantity .= ',';
                        $pair_complete = false;
                    }else{
                        $price_quantity .= $sheet->getCell($letter.$row)->getValue();
                        $price_quantity .= ')';
                        $pair_complete = true;
                    }
                }
                $j++;
            }
            if($sheet->getCell('X9')->getValue() === "Remarks"){
                $remarks = '';
                if($sheet->getCell('X'.$row)->getValue() != null){
                    $remarks = $sheet->getCell('X'.$row)->getValue();
                }
            }else{
                $ramp_rate = '';
                if($type == 'EN'){
                    // FOR ENERGY OFFER WITH MULTIPLE RAMP RATE BLOCKS
                    $rr_complete = false;
                    $n = 1;
                    // foreach(range('X','AL') as $rr_letter){
                    // for($rr_letter='X';$rr_letter!='AL';$rr_letter++){
                    $rr_letter = 'X';
                    while($rr_letter != 'AL'){
                        if( strlen($sheet->getCell($rr_letter.$row)->getFormattedValue()) > 0 ){
                            if($rr_complete){
                                $ramp_rate .= ',';
                            }
                            $ramp_rate .= '('; 
                            $ramp_rate .= $sheet   ->getCell($rr_letter.$row)->getValue();
                            $ramp_rate .= ',';

                            $rr_letter++;
                            $ramp_rate .= $sheet   ->getCell($rr_letter.$row)->getValue();
                            $ramp_rate .= ',';

                            $rr_letter++;
                            $ramp_rate .= $sheet->getCell($rr_letter.$row)->getValue();
                            $ramp_rate .= ')';
                            $rr_complete = true;

                            $rr_letter++;
                        }else{
                            break;
                        }
                        
                    }
                    $remarks = '';
                    if($sheet->getCell('AM'.$row)->getValue() != null){
                        $remarks = $sheet->getCell('AM'.$row)->getValue();
                    }
                }else{
                    if($sheet->getCell('X'.$row)->getValue() != null){
                        $ramp_rate = '(';
                        $ramp_rate .= $sheet->getCell('X'.$row)->getValue() . ',';
                        $ramp_rate .= $sheet->getCell('Y'.$row)->getValue() . ',';
                        $ramp_rate .= $sheet->getCell('Z'.$row)->getValue() . ')';
                    } 
                    $remarks = '';
                    if($sheet->getCell('AA'.$row)->getValue() != null){
                        $remarks = $sheet->getCell('AA'.$row)->getValue();
                    }
                }
                     
            }            
            $row++;
            $bid[$x]['price_quantity'] = $price_quantity;
            $bid[$x]['ramp_rate'] = $sheet->getCell('X9')->getValue() !== 'Remarks' ? $ramp_rate : ''; 
            $bid[$x]['remarks'] = $remarks;
        }
        $cells = array(
            'delivery_date' =>$date,
            'resource_id'   =>$resource_id,
            'intervals'   =>$bid,
        );
        if($type == 'SO' || $type == 'SOR') {
            $cells['day_type'] = $sheet->getCell('X7')->getValue();
            $exp_date = $sheet->getCell('D7')->getValue();
            $cells['expiry_date'] = PHPExcel_Style_NumberFormat::toFormattedString($exp_date, "M/D/YYYY");
        }
        if($type == 'DAR' || $type == "SOR"){
            $reserve_class = array('DISPATCH'=>'DIS','REGULATION'=>'REG','REGULATING'=>'REG','CONTINGENCY'=>'CON','INTERRUPTIBLE LOAD'=>'ILD');
            $cells['reserve_class'] = isset($reserve_class[$sheet->getCell('X5')->getValue()]) ? $reserve_class[$sheet->getCell('X5')->getValue()] : '';
            $cells['opres_ramp_rate'] = $sheet->getCell('X6')->getValue();
        }
        return $cells;
	}

	public function convertToXML($participant,$user,$bid_arr){
        $xml_body = '';               
        foreach($bid_arr as $offer){
            $delivery_date = date('Y-m-d', strtotime($bid_arr['delivery_date']));
            $cur_time = date('H:i:s');
            $resource_id = strtoupper($bid_arr['resource_id']);
            $action = strtoupper($bid_arr['action']);
            $sdate = $delivery_date;
            $edate = !isset($bid_arr['expiry_date']) ? $delivery_date : $bid_arr['expiry_date']; 
            $cert_user = strtoupper($bid_arr['user']);
        } 
        foreach($bid_arr['intervals'] as $int){
            if(isset($int)) {
                $int['price_quantity'] = str_replace('),(','|', $int['price_quantity']);
                $int['price_quantity'] = str_replace('(', '', $int['price_quantity']);
                $int['price_quantity'] = str_replace(')', '', $int['price_quantity']);
                $int['price_quantity'] = str_replace(';', '', $int['price_quantity']);
                $int['price_quantity'] = explode('|', $int['price_quantity']);
                
                $xml_pq = '';
                foreach($int['price_quantity'] as $pq_raw){
                    preg_match_all('/,/',$pq_raw,$matches);
                    if(count($matches[0]) == 2){
                        $pq = preg_replace('/,/','',$pq_raw,1);
                        $price_quantity = $pq; 
                    }else{
                        $price_quantity = $pq_raw;
                    }
                    $pq_pair = explode(',',$price_quantity);               
                    $price = $pq_pair[0];
                    $qty = $pq_pair[1];
                    // PQ CURVE
                    $xml_pq .='
                                <m:CurveSchedData>
                                  <m:xAxisData>'.$qty.'</m:xAxisData>
                                  <m:y1AxisData>'.$price.'</m:y1AxisData>
                                </m:CurveSchedData>';
                    // 
                }
                $int['start'] = $int['start'] < 10 ? '0'.($int['start'] - 1) : ($int['start'] - 1);
                $xml_body .='
                        <m:BidSchedule>
                            <m:timeIntervalStart>'.$sdate.'T'.$int['start'].':00:00.000+08:00</m:timeIntervalStart>
                            <m:timeIntervalEnd>'.$edate.'T'.$int['end'].':00:00.000+08:00</m:timeIntervalEnd>
                            <m:BidPriceCurve>';
                //add PQ Curve to xml body
                $xml_body .= $xml_pq;
                //
                $xml_body .='
                            </m:BidPriceCurve>
                        </m:BidSchedule>'; 
            }
                          
        }
        $xml_body .='
                </m:ProductBid>';

        if(!isset($bid_arr['reserve_class']) && isset($int)) { 
            $int['ramp_rate'] = str_replace('),(','|', $int['ramp_rate']);
            $int['ramp_rate'] = str_replace('(', '', $int['ramp_rate']);
            $int['ramp_rate'] = str_replace(')', '', $int['ramp_rate']);
            $int['ramp_rate'] = str_replace(';', '', $int['ramp_rate']);
            $int['ramp_rate'] = explode('|', $int['ramp_rate']);
            $xml_body .= '
                <m:RampRateCurve>';
            foreach($int['ramp_rate'] as $rr_raw){
                preg_match_all('/,/',$rr_raw,$matches);
                if(count($matches[0]) == 3){
                    $rr = preg_replace('/,/','',$rr_raw,1);
                    $ramp_rate = $rr; 
                }else{
                    $ramp_rate = $rr_raw;
                }
                $ramp_rate = explode(',',$ramp_rate);
                $xml_body.='    
                    <m:CurveData>
                        <m:xAxisData>'.$ramp_rate[0].'</m:xAxisData>
                        <m:y1AxisData>'.$ramp_rate[1].'</m:y1AxisData>
                        <m:y2AxisData>'.$ramp_rate[2].'</m:y2AxisData>
                    </m:CurveData>'; 
            }
            $xml_body .= '
                </m:RampRateCurve>';
        }                           
        //if(!isset($bid_arr['standing_flag']) || isset($bid_arr['reserve_class'])) { $xml_body.='    
        //             <reason>'.htmlentities($int['remarks']).'</reason>'; }     
     	if(isset($bid_arr['standing_flag']) && $bid_arr['standing_flag'] == 'YES') $so_xml = '
                                <standing expiry_date = "'.str_replace('/','',$bid_arr["expiry_date"]).'"
                                    type = "'.$bid_arr["day_type"].'"/>';

        $xml = '<m:RawBidSet xmlns:m="http://pemc/soa/RawBidSet.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pemc/soa/RawBidSet.xsd RawBidSet.xsd">';
                                        
                    if(trim($xml_body) != ''){
                        $xml .='
            <m:MessageHeader>
                <m:TimeDate>'.date('Y-m-d').'T'.$cur_time.'Z</m:TimeDate>
                <m:Source>Default</m:Source>
            </m:MessageHeader>
            <m:MessagePayload>
                <m:GeneratingBid>
                    <m:startTime>'.$sdate.'T00:00:00.000+08:00</m:startTime>
                    <m:stopTime>'.$edate.'T24:00:00.000+08:00</m:stopTime>
                <m:RegisteredGenerator>
                    <m:mrid>'.$resource_id.'</m:mrid>
                </m:RegisteredGenerator>
                <m:MarketParticipant>
                    <m:mrid>'.$cert_user.'</m:mrid>
                </m:MarketParticipant>
                <m:ProductBid>
                    <m:MarketProduct>
                        <m:marketProductType>EN</m:marketProductType>
                    </m:MarketProduct>';
                                $xml .= $xml_body;
                        $xml .= '
                </m:GeneratingBid>
            </m:MessagePayload>
        </m:RawBidSet>';
                    }
            return $xml;
    }

    public function compressFormat($b) // merge intervals with same pq, rr, remarks
	{
		$start = 1;
		$end = 0;
		$unique = 0;
		$offer_arr = array();
		
		$price_quantity_tmp = '';
		$ramp_rate_tmp = '';
		// $remarks_tmp = '';
		
		for($i=0; $i<=23; $i++){
			if(isset($b['intervals'][($i+1)])){
				$price_quantity = $b['intervals'][($i+1)]['price_quantity'];
				$ramp_rate = $b['intervals'][($i+1)]['ramp_rate'];
				// $remarks = $b['intervals'][($i+1)]'remarks'];
			}else{
				$price_quantity = '';
				$ramp_rate = '';
				$remarks = '';
			}
			if(($price_quantity_tmp != $price_quantity) || 
				($ramp_rate_tmp != $ramp_rate)){
				
				$price_quantity_tmp = $price_quantity;
				$ramp_rate_tmp = $ramp_rate;
				// $remarks_tmp = $remarks;
				
				$end = ($i+1);
				$start = $end;
				$unique++;
			}else{
				$end++;
			}
			
			if($price_quantity_tmp != ""){
				$offer_arr[$unique]['start'] = $start;
				$offer_arr[$unique]['end'] = $end;
				$offer_arr[$unique]['price_quantity'] = $price_quantity_tmp ;
				$offer_arr[$unique]['ramp_rate'] = $ramp_rate_tmp ;
				// $offer_arr[$unique]['remarks'] = $remarks_tmp ;
			}            
		}
		$data = array();        
		$data['participant'] = 'DEFAULT_PARTICIPANT';
		$data['user'] = $b['cert_user'];
		$data['delivery_date'] = $b['delivery_date'];
		$data['resource_id'] = $b['resource_id'];
		$data['intervals'] = $offer_arr;
		$data['action'] = $b['action'];
        if(isset($b['standing_flag']) && $b['standing_flag'] == 'YES'){
            $data['standing_flag'] = $b['standing_flag'];
            $data['expiry_date'] = $b['expiry_date'];
            $data['day_type'] = $b['day_type'];
        }
        if(isset($b['reserve_class'])){
            $data['reserve_class'] = $b['reserve_class'];
        }
		return $data;
	}
	public function convertToWeb($xml){ // previous function name = getChildren offer_xml

        $children = $xml ? $xml : array();
        $b_raw = array();

        foreach($children as $child){
            for($i=($child['start']+1); $i<=$child['end']; $i++){
                $b_raw[$i]['interval'] = $i;
                $b_raw[$i]['price_quantity'] = $child['price_quantity'];
                $b_raw[$i]['ramp_rate'] = $child['ramp_rate']; 	
            }
        }
        $bid_arr = array();
        $bid_arr['intervals'] = $b_raw;
        return $bid_arr;
    }
    public function convertSOToWeb($xml){ // previous function name = getChildren offer_xml
        $children = $xml[0]['@children'][0]['@children'];
        $b_raw = array();

        // dd($children);
        foreach($children as $k => $child){
                if($k > 0){
                    for($i=$child['@attributes']['start']; $i<=$child['@attributes']['end']; $i++){
                        foreach($child['@children'] as $val){
                            if($val['@name'] == 'reason') { $val['@name'] = 'remarks';}
                            if(!isset($val['@text'])) { $val['@text'] = '';}
                            $b_raw[$i][$val['@name']] = $val['@text'];
                        }    
                    }
                }
        }
        $bid_arr = array();
        $bid_arr['intervals'] = $b_raw;        
        return $bid_arr;
    }
    //utility - converts xml to object
    public function convertXmlObjToArr($obj, &$arr)
    {
        $children = $obj->children('m',true)->MessagePayload->children('m',true);
        $bid_data = array();
        foreach ($children as $elementName => $node)
        {
            $nextIdx = 0;
            $arr[$nextIdx] = array();
            foreach($node->ProductBid->BidSchedule as $bid_sched){                
                $arr[$nextIdx]['start'] = preg_replace('/\.(.*)/','',(string) $bid_sched->timeIntervalStart);
                $arr[$nextIdx]['end'] = preg_replace('/\.(.*)/','',(string) $bid_sched->timeIntervalEnd);
                $arr[$nextIdx]['start'] = Carbon::parse($arr[$nextIdx]['start']);
                $arr[$nextIdx]['end'] = Carbon::parse($arr[$nextIdx]['end']);
                $arr[$nextIdx]['start'] = $arr[$nextIdx]['start']->hour /*== 00 ? 24 : $arr[$nextIdx]['start']->hour*/;
                $arr[$nextIdx]['end'] = $arr[$nextIdx]['end']->hour == 00 ? 24 : $arr[$nextIdx]['end']->hour;
                $pq = array();
                $rr = array();
                foreach($bid_sched->BidPriceCurve->CurveSchedData as $bid_pq){
                    array_push($pq,$bid_pq->y1AxisData.','.$bid_pq->xAxisData);                    
                }
                foreach($node->RampRateCurve->CurveData as $bid_rr){
                    // array_push($rr,$bid_rr->xAxisData.','.$bid_rr->y1AxisData.','.$bid_rr->y2AxisData);
                    array_push($rr,$bid_rr->xAxisData.','.$bid_rr->y1AxisData.','.$bid_rr->y2AxisData);
                }
                $price_quantity = implode('),(',$pq);
                $ramp_rate = implode('),(',$rr);
                $arr[$nextIdx]['price_quantity'] = '('.$price_quantity.')';
                $arr[$nextIdx]['ramp_rate'] = '('.$ramp_rate.')';

                $nextIdx++;
            }

            // $nextIdx = count($arr);
            // $arr[$nextIdx] = array();
            // $arr[$nextIdx]['@name'] = strtolower((string)$elementName);
            // $arr[$nextIdx]['@attributes'] = array();
            // $attributes = $node->attributes();
            // foreach ($attributes as $attributeName => $attributeValue)
            // {
            //     $attribName = strtolower(trim((string)$attributeName));
            //     $attribVal = trim((string)$attributeValue);
            //     $arr[$nextIdx]['@attributes'][$attribName] = $attribVal;
            // }
            // $text = (string)$node;
            // $text = trim($text);
            // if (strlen($text) > 0)
            // {
            //     $arr[$nextIdx]['@text'] = $text;
            // }
            // $arr[$nextIdx]['@children'] = array();
            // $this->convertXmlObjToArr($node, $arr[$nextIdx]['@children']);

        }
        return $arr;
    }
    public function prepareOfferParams($bid_arr,$bid_type=""){
        //$go_status = ($offer_action=='cancel') ? BID_SUBMITTED : BID_TO_SUBMIT;
        
        //$b = $this->listAllOfferData($offer_id);
        $start = 1;
        $end = 0;
        $unique = 0;
        $offer_arr = array();
        
        $price_quantity_tmp = '';
        $ramp_rate_tmp = '';
        $remarks_tmp = '';
        
        for($i=0; $i<=23; $i++){
            $price_quantity = str_replace(';','',$bid_arr['intervals'][$i+1]['price_quantity']);
            $ramp_rate = $bid_type !== 'reserve' ? str_replace(';','',$bid_arr['intervals'][$i+1]['ramp_rate']) : '';
            // $remarks = $bid_type !== 'standing' ? str_replace(';','',$bid_arr['intervals'][$i+1]['remarks']) : '';
            
            if(($price_quantity_tmp != $price_quantity) || 
                ($ramp_rate_tmp != $ramp_rate)){
                
                $price_quantity_tmp = $price_quantity;
                $ramp_rate_tmp = $ramp_rate;
                // $remarks_tmp = $remarks;
                
                $end = ($i+1);
                $start = $end;
                $unique++;
                
            }else{
                $end++;
            }
            
            if($price_quantity_tmp != ""){
                $offer_arr[$unique]['start'] = $start;
                $offer_arr[$unique]['end'] = $end;
                $offer_arr[$unique]['price_quantity'] = $price_quantity_tmp ;
                if($bid_type !== 'reserve') $offer_arr[$unique]['ramp_rate'] = $ramp_rate_tmp ;
                // if($bid_type !== 'standing') $offer_arr[$unique]['remarks'] = $remarks_tmp ;
            }
        }

        $data = array();
        $data['participant'] = 'DEFAULT_PARTICIPANT';
        $data['user'] = @$bid_arr['cert_user'];
        $data['delivery_date'] = @$bid_arr['delivery_date'];
        $data['resource_id'] = @$bid_arr['resource_id'];
        $data['intervals'] = $offer_arr;
        return $data;

    }
}
