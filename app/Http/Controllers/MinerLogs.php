<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MinerLogs extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
    } //
    public function rtdLog(){
    	$file = file_get_contents(base_path()."/miner/mms_mod/errors_rtd.log"); 

    	dd($file);
    }
    public function lmpLog(){
    	$file = file_get_contents(base_path()."/miner/mms_mod/errors_lmp.log");
    	dd($file);
    }
    public function laraLog(){
    	$file = file_get_contents(base_path()."/storage/logs/laravel.log");
    	dd($file);
    }
}
