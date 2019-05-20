<?php

namespace App;
use App\OfferSubmissionUnits;
use App\OfferType;
use Carbon\Carbon;

class OfferParams{
        
    public $isTest = '';
    public $delivery_date = '';
    public $pq_pairs = '';
    public $intervals = '';
    public $ramp_rates = '';
    public $other_reasons = '';
    public $bid_action = '';
    public $day_type = '';
    public $standing_flag = '';
    public $expiry_date = '';
    public $opres_ramp_rate = '';
    public $reserve_class = '';
        
    function OfferParams($bid_arr){
        $this->delivery_date = date('Ymd',strtotime($bid_arr['delivery_date']));        
        $this->resource_id = isset($bid_arr['resource_id']) ? $bid_arr['resource_id'] : null;;
        $this->setBody($bid_arr['intervals']);
        $this->bid_action = isset($bid_arr['action']) ? $bid_arr['action'] : null;
        $this->day_type = isset($bid_arr['day_type']) ? $bid_arr['day_type'] : null;
        $this->expiry_date = isset($bid_arr['expiry_date']) ? $bid_arr['expiry_date'] : null;
        $this->opres_ramp_rate = isset($bid_arr['opres_ramp_rate']) ? $bid_arr['opres_ramp_rate'] : null;
        $this->reserve_class = isset($bid_arr['reserve_class']) ? $bid_arr['reserve_class'] : null;;
        $this->standing_flag = isset($bid_arr['standing_flag']) ? $bid_arr['standing_flag'] : null;;
    }
    
    function setBody($int_arr){
        $pq_pairs = '';
        $intervals = '';
        $ramp_rates = '';
        $other_reasons = '';
        $reason_code = '';
        $zero_price = '';
        $quantity = '';
        $resv_ld_pnt = '';

        foreach($int_arr as $int){
            $int_remarks = isset($int['remarks']) ? urlencode($int['remarks']) : '';
            $int_ramp_rate = isset($int['ramp_rate']) ? $int['ramp_rate'] : '';
            for($i=$int['start'];$i<=$int['end'];$i++){
                if($pq_pairs != '') $pq_pairs .= '&';
                $pq_pairs .= 'pq_pair_'.$i.'='.$int['price_quantity'];
                
                if($intervals != '') $intervals .= '&';
                $intervals .= 'interval_'.$i.'=Y';
                
                if($ramp_rates != '') $ramp_rates .= '&';
                $ramp_rates .= 'ramp_rate_'.$i.'='.$int_ramp_rate;
                
                if($other_reasons != '') $other_reasons .= '&';
                $other_reasons .= 'other_reason_'.$i.'='.$int_remarks;

                if($reason_code != '') $reason_code .= '&';
                $reason_code .= 'reason_code_'.$i.'=OTHER';

                if($resv_ld_pnt != '') $resv_ld_pnt .= '&';
                $resv_ld_pnt .= 'resv_ld_pnt_'.$i.'=0';

                if($zero_price != '') $zero_price .='&';
                $zero_price .= 'zero_price_'.$i.'=0';

                if($quantity != '') $quantity .= '&';
                $quantity .= 'quantity_'.$i.'='.$int['price_quantity'];
            }
        }
        $this->pq_pairs = $pq_pairs;
        $this->intervals = $intervals;
        $this->ramp_rates = $ramp_rates;       
        $this->other_reasons = $other_reasons;
        $this->reason_code = $reason_code;
        $this->zero_price = $zero_price;
        $this->quantity = $quantity;
        $this->resv_ld_pnt = $resv_ld_pnt;
    }
    
    function generate(){
        $bid_class = $this->reserve_class !== null ? 'OPER_RESV' : 'RTEM';        
        $opres_ramp_rate = $this->opres_ramp_rate !== null ? $this->opres_ramp_rate : '999.9' ;
        $standing_flag = $this->standing_flag !== null ? 'Y' : 'N' ;
        $day_type = $this->day_type !== null ? $this->day_type : 'ALL';

        $param = "FORMAT=HTML&CATEGORY=BID&REQ_TYPE=CREATE&ADMIN_MP=&ADMIN_USER=";
        $param .= "&MARKET=PM&CLASS=".$bid_class."&SIGNATURE=&transaction_id=";
        $param .= "&delivery_date=".$this->delivery_date."&participant_id=GPCO";
        $param .= "&user_id=John&day_type=".$day_type."&daily_engy_limit=&opres_ramp_rate=".$opres_ramp_rate;
        $param .= "&bid_type=GEN&standing_flag=".$standing_flag."&mode=NORMAL&bid_action=".$this->bid_action;
        if($this->expiry_date !== null) { $param .= $this->expiry_date !== '' ? '&expiry_date='.$this->expiry_date : ''; }
        $param .= "&resource_id=".$this->resource_id."&version=1.0";
        $param .= "&" . $this->intervals;
        $param .= "&" . $this->pq_pairs;

        if(!$this->reserve_class) $param .= "&" . $this->ramp_rates;
        if($this->reserve_class) { 
            $param .= "&" . $this->resv_ld_pnt; 
            $param .= "&" . $this->reason_code; 
        }
        if(!$this->standing_flag || $this->reserve_class) $param .= "&" . $this->other_reasons;

        return $param;
    }

    /// FOR REVIEW
    function generate_sb(){
        
        $param = "FORMAT=HTML&CATEGORY=BID&REQ_TYPE=CREATE&ADMIN_MP=&ADMIN_USER=";
        $param .= "&MARKET=PM&CLASS=RTEM&SIGNATURE=&transaction_id=";
        $param .= "&delivery_date=" .$this->delivery_date."&participant_id=GPCO";
        $param .= "&user_id=John&day_type=".$this->day_type."&daily_engy_limit=&opres_ramp_rate=999.9";
        $param .= "&bid_type=GEN&standing_flag=Y&mode=NORMAL&bid_action=".$this->bid_action."&expiry_date=".$this->expiry_date;
        $param .= "&resource_id=".$this->resource_id."&version=1.0";
        $param .= "&" . $this->getIntervals();
        $param .= "&" . $this->getPQPairs();
        $param .= "&" . $this->getRampRates();
        $param .= "&" . $this->getReasonCode();
        $param .= "&" . $this->getOtherReasons();
        
        return $param;
    }
    function generate_reserve(){
        $param = "FORMAT=HTML&CATEGORY=BID&REQ_TYPE=CREATE&ADMIN_MP=&ADMIN_USER=";
        $param .= "&MARKET=PM&CLASS=OPER_RESV&SIGNATURE=&transaction_id=";
        $param .= "&delivery_date=" .$this->delivery_date."&participant_id=GPCO";
        $param .= "&user_id=John&day_type=ALL&bid_type=GEN";
        $param .= "&standing_flag=N&reserve_class=".$this->reserve_class."&mode=NORMAL&bid_action=".$this->bid_action;
        $param .= "&opres_ramp_rate=".$this->opres_ramp_rate."&resource_id=".$this->resource_id."&version=1.0";
        $param .= "&" . $this->getIntervals();
        $param .= "&" . $this->getPQPairs();
        $param .= "&" . $this->getResvLdPnt();
        $param .= "&" . $this->getReasonCode();
        $param .= "&" . $this->getOtherReasons();
        
        return $param;
    }
    function generate_sb_reserve(){
        
        $param = "FORMAT=HTML&CATEGORY=BID&REQ_TYPE=CREATE&ADMIN_MP=&ADMIN_USER=";
        $param .= "&MARKET=PM&CLASS=OPER_RESV&SIGNATURE=&transaction_id=";
        $param .= "&delivery_date=" .$this->delivery_date."&participant_id=GPCO";
        $param .= "&user_id=John&day_type=".$this->day_type."&bid_type=GEN";
        $param .= "&standing_flag=Y&reserve_class=".$this->reserve_class."&mode=NORMAL&bid_action=".$this->bid_action."&expiry_date=".$this->expiry_date;
        $param .= "&opres_ramp_rate=".$this->opres_ramp_rate."&resource_id=".$this->resource_id."&version=1.0";
        $param .= "&" . $this->getIntervals();
        $param .= "&" . $this->getPQPairs();
        $param .= "&" . $this->getResvLdPnt();
        $param .= "&" . $this->getReasonCode();
        $param .= "&" . $this->getOtherReasons();
        
        return $param;
    }

    public function get_fake_wesm_return()
    {
    
        $trans_id = md5(date('His'));
        $trans_id = ucfirst(substr($trans_id, 0, 8));
        
        $res = "<HTML><HEAD> 
                <META HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=UTF-8'> 
                <TITLE>MIRP Status</TITLE>
                 <STYLE TYPE='TEXT/CSS'>
                 BODY {background-image: url(/mos/msg/powerbg4.jpg);
                       text-align:center;       font-family:sans-serif;       font-size:100%;       font-weight:bold;       color:#004080}
                 .MI {font-family:sans-serif;      font-size:large;      font-weight:bold;      text-align:center;      color:#004080}
                 .SOK {font-family:sans-serif;       font-size:medium;       font-weight:normal;       text-align:center;       color:#004080}
                 .SER {font-family:sans-serif;       font-size:medium;       font-weight:bold;       text-align:center;       color:#FF0000}
                 .SM1 {font-family:sans-serif;       font-size:medium;       font-weight:normal;       color:#004080}
                 .SM2 {font-family:sans-serif;       font-size:medium;       font-weight:normal;       color:#004080}
                 .SM3 {font-family:sans-serif;       font-size:medium;       font-weight:normal;       color:#004080}
                 </STYLE></HEAD><BODY><BR><P CLASS=MI>WARNING ::: THIS IS A FAKE WESM RETURN ::: WARNING</P>
                <BR><P CLASS=SER>VALIDATION Transaction Id is ".$trans_id." <BR><BR></P>
                <CENTER><IMG SRC=\"/mos/msg/backgrnd.gif\" WIDTH=\"50%\" HEIGHT=5></CENTER>
                <BR><P CLASS=SOK>PM_INFO_MSG: Normal bid submitted for delivery_date ".date("Ymd")." </P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 1 successfully inserted/updated with all validations</P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 2 successfully inserted/updated with all validations</P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 3 successfully inserted/updated with all validations</P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 4 successfully inserted/updated with all validations</P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 5 successfully inserted/updated with all validations</P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 6 successfully inserted/updated with all validations</P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 7 successfully inserted/updated with all validations</P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 8 successfully inserted/updated with all validations</P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 9 successfully inserted/updated with all validations</P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 10 successfully inserted/updated with all validations</P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 11 successfully inserted/updated with all validations</P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 12 successfully inserted/updated with all validations</P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 13 successfully inserted/updated with all validations</P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 14 successfully inserted/updated with all validations</P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 15 successfully inserted/updated with all validations</P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 16 successfully inserted/updated with all validations</P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 17 successfully inserted/updated with all validations</P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 18 successfully inserted/updated with all validations</P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 19 successfully inserted/updated with all validations</P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 20 successfully inserted/updated with all validations</P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 21 successfully inserted/updated with all validations</P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 22 successfully inserted/updated with all validations</P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 23 successfully inserted/updated with all validations</P>
                <BR><P CLASS=SOK>PM_SUCCESS: Interval 24 successfully inserted/updated with all validations</P>
                </BODY></HTML>
                ";
                
        return $res;
    }
    public function get_pretty_return($string)
    {
        $start = '<BODY>';
        $end = '</BODY>';
        $string = str_replace('<BR><P CLASS=SER>',"<P><B><span style='background-color:yellow'>",$string);
        $string = str_replace('<BR><BR></P>',"</span></B></P>",$string);
        $string = str_replace('<CENTER><IMG SRC="/mos/msg/backgrnd.gif" WIDTH="50%" HEIGHT=5></CENTER>',"",$string);
        #$string = str_replace("PM_INFO_MSG:", '<span class="ui-icon ui-icon-info" style="float:left;"></span>', $string);
        #$string = str_replace("PM_SUCCESS:", '<span class="ui-icon ui-icon-check" style="float:left;"></span>', $string);
        #$string = str_replace("PM_BID_CNCELD:", '<span class="ui-icon ui-icon-check" style="float:left;"></span>', $string);
        $string = str_replace("<BR>","", $string);
        $string = " ".$string;
        $ini = strpos($string,$start);
        if ($ini == 0) return "";
        $ini += strlen($start);
        $len = strpos($string,$end,$ini) - $ini;
        return substr($string,$ini,$len);
    }
    function getReturnInArray($string)
    {
        $t = str_replace('</P>', '|', strtoupper($string));
        $t_arr = explode('|', strip_tags($t));
        $o_arr = array();
        
        foreach($t_arr as $k => $v){
            $val = trim($v);
            if(strlen($val) > 0) {
                if(strpos($val,'INTERVAL')){
                    $val_arr = explode(':',  $val);
                    $key = preg_replace("/[^0-9]/", "",$val_arr[1]); 
                    $o_arr[$key] = $val_arr[0];
                }
            }
        }
        #print_r($o_arr);
        return $o_arr;
    }
    private function getUnitByResourceId($p_resource_id=''){
        $resource_resource_id = trim($p_resource_id);
        if ( strlen($resource_resource_id) > 2 ) {
            $unit = intval(substr($resource_resource_id,-2));
        }

        return $unit;
    }
    public function generateTransID($type="",$identifier_code=""){
        $generated_transaction_id = "";
        /*
        * For types WAO, DAO, RTO, BCQ format should be :
        * - •  WAO_UNIT_YYYYMMDD_HHHH_XX eg. WAO_ USERNAME_1_20121214_01 (for unit 1)
        * - •  DAO_UNIT _YYYYMMDD_HHHH_XX eg. DAO_ USERNAME_1_20121214_01 (for unit 1)
        * - •  RTO_UNIT _YYYYMMDD_HHHH_XX eg. RTO_ USERNAME_1_20121214_01 (for unit 1)
        * - •  BCQ_ USERNAME_YYYYMMDD_HHHH_XX eg. BCQ_20120512_1639_01
        *
        */
        $date_string = date('Ymd');
        $hour_string = date('gi');
        $type = trim(strtoupper($type));
        $resource_unit = $this->getUnitByResourceId($identifier_code);
        $total_count = 0;

         // **** WAITING FOR AUDIT TABLE // SWITCHED TO submission units for now
        $current_date = date('Y-m-d');
        $offer_type = OfferType::where('offer_type',$type)->first();
        $total_count = OfferSubmissionUnits::whereDate('created_at',$current_date)->where('action','submit')->count();
        
        $total_count = $total_count + 1;
        $total_count_str = str_pad($total_count,2,'0',STR_PAD_LEFT);
        $generated_transaction_id = $type . '_' . $resource_unit. '_' . $date_string . '_'  . $hour_string . '_'. $total_count_str;


        return $generated_transaction_id;

    }
}