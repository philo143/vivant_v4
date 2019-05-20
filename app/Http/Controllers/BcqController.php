<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customer;

class BcqController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    } //


    public function bcqUploader(){

    	return view('bcq.bcq_uploader');


    } // eof


    public function bcqReport(){

    	$customers = Customer::orderBy('customer_name','asc')->pluck('customer_name','id')->toArray();

    	return view('bcq.bcq_report',compact('customers'));


    } // eof

}
