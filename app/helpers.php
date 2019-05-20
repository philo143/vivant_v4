<?php

// ############## Common Functions ####################

use Carbon\Carbon;
## function for getting the current intrainterval
function getIntraIntervalDetails($params = array()) {
	$number = range(5, 60, 5);
	$min = 0;

	// get date value
	$is_midnight = false;
	$midnight_date = '';
	$midnight_interval = '';

	if (isset($params['date'])) {
		$cur_datetime = Carbon::createFromTimestamp(strtotime($params['date']));
		$dte = $cur_datetime->format('Y-m-d');
	} else {
		$cur_datetime = Carbon::now()->subMinutes(5);
		$dte = $cur_datetime->format('Y-m-d');

		$est_hour_value = $cur_datetime->hour + 1;
		if ($est_hour_value == 24) {
			$midnight_date = Carbon::now()->format('Y-m-d');
			$midnight_interval = '00:00:00';
			$is_midnight = true;
		}

	}

	// get hour value
	if (!isset($params['hour'])) {
		$cur_hour = $cur_datetime->hour;
		$hour = $cur_hour + 1;
	} else {
		$hour = $params['hour'];
	}

	// get min value
	if (!isset($params['min'])) {
		$cur_min = $cur_datetime->minute;
		$min_value = 1;
		foreach ($number as $max_value) {
			if ($cur_min >= $min_value && $cur_min <= $max_value) {
				$min = $max_value;
				break;
			}
			$min_value = $max_value + 1;
		}
	} else {
		$min = $params['min'];
	}

	if ($min == 60) {
		$min = 0;
	}

	$prev_hour = $hour - 1;
	$prev_date = $dte;
	if ($min != 0) {
		$min = str_pad($min, 2, "0", STR_PAD_LEFT);
		$interval = str_pad($prev_hour, 2, "0", STR_PAD_LEFT) . ':' . $min . ':00';

		$prev_min = $min - 5;
	} else {
		$min = str_pad($min, 2, "0", STR_PAD_LEFT);
		$interval = str_pad($hour, 2, "0", STR_PAD_LEFT) . ':' . $min . ':00';
		$prev_min = 55;
		$prev_date = $cur_datetime::yesterday()->format('Y-m-d');
	}
	$prev_intrainterval = str_pad($prev_hour, 2, "0", STR_PAD_LEFT) . ':' . str_pad($prev_min, 2, "0", STR_PAD_LEFT) . ':00';

	## for previous previous intra interval
	$prev_prev_hour = $prev_hour;
	$prev_prev_date = $prev_date;

	if ($prev_min != 0) {
		$prev_prev_min = $prev_min - 5;
	} else {
		$prev_prev_hour = $prev_hour - 1;
		$prev_prev_min = 55;
	}
	$prev_prev_intrainterval = str_pad($prev_prev_hour, 2, "0", STR_PAD_LEFT) . ':' . str_pad($prev_prev_min, 2, "0", STR_PAD_LEFT) . ':00';

	// create prev, prev, current intra interval values
	// if the current date time is midnight, return the real date and 00:00:00 interval
	$intra_intervals = array();
	if ($is_midnight) {
		$intra_intervals[] = $midnight_date . ' ' . $midnight_interval;
	} else {
		$intra_intervals[] = $dte . ' ' . $interval;
	}

	$intra_intervals[] = $prev_date . ' ' . $prev_intrainterval;
	$intra_intervals[] = $prev_prev_date . ' ' . $prev_prev_intrainterval;

	return array(
		'date' => $dte,
		'min' => $min,
		'hour' => $hour,
		'intra_interval' => $interval,
		'prev_hour' => $prev_hour,
		'prev_intrainterval' => $prev_intrainterval,
		'intra_intervals' => $intra_intervals,
	);

}
?>