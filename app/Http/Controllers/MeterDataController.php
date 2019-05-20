<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Participant;

class MeterDataController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    } //


    public function dailyMqLoad(){
  
    	$participants = Participant::orderBy('participant_name','asc')->pluck('participant_name','id')->toArray();
    	return view('meter_data.trading.daily_mq_load',compact('participants'));

    } // eof


    public function dailyMqGen(){
  
    	$participants = Participant::orderBy('participant_name','asc')->pluck('participant_name','id')->toArray();
    	return view('meter_data.trading.daily_mq_gen',compact('participants'));

    } // eof

    
}
