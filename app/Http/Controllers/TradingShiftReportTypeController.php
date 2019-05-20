<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\TradingShiftReportType;
class TradingShiftReportTypeController extends Controller
{

    public function listAll(){

        $types = TradingShiftReportType::get();
        return $types;

    }


}
