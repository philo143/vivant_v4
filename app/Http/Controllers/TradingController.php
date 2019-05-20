<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TradingController extends Controller
{
    public function index()
    {
    	return view('nomination.list');	
    }
}
