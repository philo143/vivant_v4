<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PlantShiftReportType;

class PlantShiftReportTypeController extends Controller
{
    public function listAll(){

        $types = PlantShiftReportType::get();
        return $types;

    }
}
