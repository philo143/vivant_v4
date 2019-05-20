<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function twofa()
    {
    	return view('settings.2fa');
    }
}
