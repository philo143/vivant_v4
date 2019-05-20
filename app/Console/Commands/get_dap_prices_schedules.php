<?php

namespace App\Console\Commands;
use App\Console\Commands\Redis;
use Illuminate\Console\Command;
use App\MmsDapPriceAndSchedule;
use \Carbon\Carbon;
use App\UserWidget;
use DB;
use App\Events\DapPricesAndSchedules;

class get_dap_prices_schedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:dap_prices_schedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dashboard Dap Prices and Schedules';

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
        $resorce_ids = UserWidget::whereNotNull('resources_id')->whereIn('widgets_id',['9','10','11'])->join('resources','user_widgets.resources_id','=','resources.id')->groupBy('resource_id')->pluck('resource_id')->toArray();
        $date_today = Carbon::now()->format('Y-m-d');

        $list = MmsDapPriceAndSchedule::whereIn('price_node',$resorce_ids)
            // ->whereRaw("date(interval_end) = '".$date_today."' ")
            ->whereRaw("hour(interval_end) != 0 ")
            ->where('run_time',function($query) use ($date_today,$resorce_ids){

                $query->select(DB::raw('max(run_time)'))
                      ->from('mms_mpd_dap_sched')
                      ->whereIn('price_node',$resorce_ids)
                      ->whereRaw("date(interval_end) = '".$date_today."' ")
                      ->whereRaw("hour(interval_end) != 0 ");
            })
            ->get();

        $data = array();
        $total = 0;
        foreach ($list as $row) {
            $hour = date("G",strtotime($row['interval_end']));
            $data[$row->price_node][$hour] = $row;
            $total++;
        }


        ## get next day for interval 24
        $date_tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $list2 = MmsDapPriceAndSchedule::whereIn('price_node',$resorce_ids)
            ->whereRaw("date(interval_end) = '".$date_tomorrow."' ")
            ->whereRaw("hour(interval_end) = 0 ")
            ->where('run_time',function($query) use ($date_tomorrow,$resorce_ids){

                $query->select(DB::raw('max(run_time)'))
                      ->from('mms_mpd_dap_sched')
                      ->whereIn('price_node',$resorce_ids)
                      ->whereRaw("date(interval_end) = '".$date_tomorrow."' ")
                      ->whereRaw("hour(interval_end) = 0 ");
            })
            ->get();
        foreach ($list2 as $row) {
            $hour = date("G",strtotime($row['interval_end']));
            $data[$row->price_node][24] = $row;
            $total++;
        }
        event(new DapPricesAndSchedules($data));
    }
}
