<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Participant;


class ManualDownloaderController extends Controller
{
    

	public function __construct()
    {
        $this->middleware('auth');
    } //


    public function rtdLmp(){
  
    	$participants = Participant::orderBy('participant_name','asc')->pluck('participant_name','id')->toArray();
    	return view('manual_downloaders.mod_rtd_lmp',compact('participants'));

    } // eof


    public function rtdResourceSpecific(){
  
    	$participants = Participant::orderBy('participant_name','asc')->pluck('participant_name','id')->toArray();
    	return view('manual_downloaders.mod_rtd_resource_specific',compact('participants'));

    } // eof


    public function mpdLmp(){
  
    	$participants = Participant::orderBy('participant_name','asc')->pluck('participant_name','id')->toArray();
    	return view('manual_downloaders.mpd_lmp',compact('participants'));

    } // eof

}
