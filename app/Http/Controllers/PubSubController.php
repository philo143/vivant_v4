<?php

namespace App\Http\Controllers;

use App\Http\Traits\OfferStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Participant;
use App\OfferSubmissionUnits;
use App\Events\OfferStatus as OfferStatusEvent;

class PubSubController extends Controller
{
    //
    use OfferStatus;

    public function parseStatus($participant,$bid_id){
    	$path = PHP_OS_FAMILY == "Windows" ? 'C:/var/miner/' : '/var/miner/';
	    $file = file_get_contents($path.'offers/status_results/'.$participant.'_'.$bid_id.'.json');
	    if($file){
	        $f = json_decode($file);
	        $d = array(
                        'status' => $f->status,
                        'date' => Date('Y-m-d',strtotime($f->date)),
                        'resource_id' => $f->resource,
                        'bid_id' => $f->bid_id,
                        'participant' => $f->participant
	                    );
	        $participant_id = Participant::where('participant_name',$d['participant'])->pluck('id')->first();
	        OfferSubmissionUnits::where(['status'=>'Waiting',
	        											'participants_id'=>$participant_id,
	        											'response_trans_id'=>$d['bid_id'],
	        											'delivery_date'=>$d['date']
	        										 ])
	        ->update(['status'=>$d['status']]);
	    }

	    event(new OfferStatusEvent($d));
	    unlink($path.'offers/status_results/'.$participant.'_'.$bid_id.'.json');
	}

	public function retrieveOfferStatus($participant,$bid_id,$resource){
       OfferStatus::retrieve($participant,$bid_id,$resource);
    }
}