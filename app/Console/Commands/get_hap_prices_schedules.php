<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Console\Commands\Redis;
use App\MmsHapPriceAndSchedule;
use \Carbon\Carbon;
use App\UserWidget;
use DB;
use App\Events\HapPricesAndSchedules;


class get_hap_prices_schedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:hap_prices_schedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hap Prices and Schedules';

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
        $resorce_ids = UserWidget::whereNotNull('resources_id')->whereIn('widgets_id',['7','8'])->join('resources','user_widgets.resources_id','=','resources.id')->groupBy('resource_id')->pluck('resource_id')->toArray();
        $dt = Carbon::createFromTimestamp(ceil(time() / 300) * 300);
        $current_hr = $dt->hour;
        $date1 = Carbon::now()->format('Y-m-d');
        
        $list = MmsHapPriceAndSchedule::select(
                DB::raw('ANY_VALUE(price_node) as price_node'),
                DB::raw('ANY_VALUE(interval_end) as interval_end'),
                DB::raw('max(run_time) as run_time'),
                DB::raw('ANY_VALUE(lmp) as lmp'),
                DB::raw('ANY_VALUE(mw) as mw') )
            ->whereIn('price_node',$resorce_ids)
            ->whereRaw("date(interval_end) = '".$date1."' ")
            ->whereRaw("hour(interval_end) = ".$current_hr)
            ->groupBy('price_node','interval_end')
            ->get();
        $data = array();
        $total = 0;
        foreach ($list as $row) {
            $hour = date("G",strtotime($row['interval_end']));
            $intra_interval = intval(date("i",strtotime($row['interval_end'])));
            $data[$row->price_node][$hour][$intra_interval] = $row;
            $total++;
        }


        // get data for 24/00 intrainterval
        $next_hour = $current_hr + 1;
        $date2 = Carbon::now()->format('Y-m-d');
        if ($current_hr == 23) {
            $date2 = Carbon::tomorrow()->format('Y-m-d');
            $next_hour = 0;
        }
        $list2 = MmsHapPriceAndSchedule::select(
                DB::raw('ANY_VALUE(price_node) as price_node'),
                DB::raw('ANY_VALUE(interval_end) as interval_end'),
                DB::raw('max(run_time) as run_time'),
                DB::raw('ANY_VALUE(lmp) as lmp'),
                DB::raw('ANY_VALUE(mw) as mw') )
            ->whereIn('price_node',$resorce_ids)
            ->whereRaw("date(interval_end) = '".$date2."' ")
            ->whereRaw("hour(interval_end) = ".$next_hour)
            ->whereRaw("minute(interval_end) = 0 ")
            ->groupBy('price_node','interval_end')
            ->get();
        foreach ($list2 as $row) {
            $hour = date("G",strtotime($row['interval_end']));
            $intra_interval = intval(date("i",strtotime($row['interval_end'])));
            $data[$row->price_node][$hour][$intra_interval] = $row;
            $total++;
        }

        $hours = array($current_hr);
        $return = array(
            'hours' => $hours,
            'data' => $data
        );
        event(new HapPricesAndSchedules($return));
    }
}
