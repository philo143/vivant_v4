<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Console\Commands\Redis;

use App\Events\DashboardActualLoadData;
use \Carbon\Carbon;
use App\RTPMActualLoad;

class get_actual_load extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:update_actual_load';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update actual load data';

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
        
        $interval = getIntraIntervalDetails();
        $date = $interval['date'];
        $previous_intrainterval = $interval['prev_intrainterval'];
        $previous_intrainterval_min = explode(':',$previous_intrainterval)[1];
        $hour = $interval['hour'];
        $real_hour = $hour-1;
        $list = RTPMActualLoad::where('date',$date)
            ->where('hour',$hour)
            ->whereRaw('minute(`interval`) = '.$previous_intrainterval_min)
            ->with('resource')
            ->get();

        $data = array();
        $resource_id_list = array();
        foreach ($list as $rec) {
            $resource_id = $rec->resource['resource_id'];
            $data[$resource_id] = array(
                'actual_load' => $rec->actual_load
            );

            if (  !in_array($resource_id, $resource_id_list) ) {
                $resource_id_list[] = $resource_id;
            }
        }

        $previous_intrainterval_min_s = $previous_intrainterval_min - 4;

        $message = array(
            'resource_id_list' => $resource_id_list,
            'data' => $data ,
            'hour' => $hour,
            'min' => $previous_intrainterval_min,
            'intrainterval' => $interval, 
            'interval' => 'Hour '.$hour.' (Prev Interval : '.$real_hour.':'.str_pad($previous_intrainterval_min_s,2,"0",STR_PAD_LEFT).' - '.$real_hour.':'.str_pad($previous_intrainterval_min,2,"0",STR_PAD_LEFT).'H)'
        );

        event(new DashboardActualLoadData($message));

    }
}
