<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\RealtimeMonitoringData;
use \Carbon\Carbon;
use App\Resource;
use App\RTPMActualLoad;
use App\IslandMode;
use App\Events\RtdGrid;
class get_realtime_monitoring_data extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:realtime_monitoring_data_updater';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For updating data on realtime monitoring pages';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $interval_data = getIntraIntervalDetails();
        $date = $interval_data['date'];
        $hour = $interval_data['hour'];
        $min = $interval_data['min'];
        $interval = $interval_data['intra_interval'];
        $previous_hour = $interval_data['prev_hour'];
        $previous_interval = $interval_data['prev_intrainterval'];

        $data_return = array();
        $data_return['current_hour'] = $hour;
        $data_return['current_intrainterval'] = $interval;

        // get actual load rtpm data for each resources
        $resources = Resource::get();
        $current_actual_load_data = array();
        foreach ($resources as $row) {
             $resource_id = $row->resource_id;
             $res_id = $row->id;
             $plant_id = $row->plant_id;

             $actual_load_data = RTPMActualLoad::where(
                ['resource_id'=>$res_id,
                 'date'=>$date,
                 'hour'=>$hour,
                 'interval'=>$interval
                ])->first();

            if($actual_load_data === null){
                 $current_actual_load_val = null;
                 $current_actual_load_is_acknowledged = 0;
                 $current_actual_load_rtd_is_acknowledged = 0;
            } else {
                $current_actual_load_val = $actual_load_data->actual_load;
                $current_actual_load_is_acknowledged = $actual_load_data->actual_load_acknowledged;
                $current_actual_load_rtd_is_acknowledged = $actual_load_data->rtd_acknowledged;
            }


            // island mode
            $im = IslandMode::where(
                    [
                     'plant_id'=>$plant_id,
                     'date'=>$date,
                     'hour'=>$hour,
                     'interval'=>$interval
                    ])->value('im');

            $current_actual_load_data[$resource_id] = array(
                'actual_load' => $current_actual_load_val,
                'is_rtd_acknowledged' => $current_actual_load_rtd_is_acknowledged,
                'is_actual_load_acknowledged' => $current_actual_load_rtd_is_acknowledged,
                'im' => $im
            );

        } // end foreach

        $data_return['resource_actual_load_data'] = $current_actual_load_data;

        event(new RealtimeMonitoringData($data_return));

        // event(new RtdGrid($data_return));
    }
}
