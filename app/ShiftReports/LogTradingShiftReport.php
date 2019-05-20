<?php

namespace App\ShiftReports;

use Carbon\Carbon;
use App\TradingShiftReport;
use App\TradingShiftReportType;

class LogTradingShiftReport {

	public function execute(array $data) {
        // temporarily solution as of now nmms data for dashboard is -5 minutes, so to avoid conflict we need to pass the date, hour, min to the getIntraintervaldetails 

        if ( !isset($data['min']) ) {
            $cur_datetime = Carbon::createFromTimestamp(ceil(time() / 300) * 300);
            // if the current minute is 55 to 00 , hour should equal to the current hour

            $cur_hour = $cur_datetime->hour + 1;
            if ( Carbon::now()->minute >= 55 || Carbon::now()->minute == 0 ) {
                $cur_hour = $cur_datetime->hour;
            }
            $cur_min = $cur_datetime->minute;
            $cur_date = $cur_datetime->format('Y-m-d');
            $data['date'] = $cur_date;
            $data['min'] = $cur_min;
            $data['hour'] = $cur_hour;
        }
        

        $interval_data = getIntraIntervalDetails($data);
        
        $dte = $interval_data['date'];
        $hour = $interval_data['hour'];
        $min = $interval_data['min'];
        $interval = $interval_data['intra_interval'];

		$type_name = $data['type'];
		$report = $data['report'];
		$submitted_by = $data['submitted_by'];

		$type_id = TradingShiftReportType::where('type',$type_name)->value('id');


		TradingShiftReport::create([
            'date' => $dte,
            'hour' => $hour,
            'interval' => $interval,
            'type_id' => $type_id,
            'report' => $report,
            'submitted_by' =>  $submitted_by
        ]);
	}
}