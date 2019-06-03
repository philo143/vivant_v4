<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\RtdGrid;
use \Carbon\Carbon;
use App\UserWidget;
use App\MmsModRtd;

class get_rtd_sched extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'miner:get_rtd_sched';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'insert dummy data for RTD Schedules';

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
        //TEST FOR RTD GRID DASHBOARD WIDGET
        $dt = Carbon::createFromTimestamp(ceil(time() / 300) * 300); // added subMinutes to match nmms late data. remove this when nmms fixed their data
        $dt_from = Carbon::createFromTimestamp(ceil(time() / 300) * 300)->subMinutes(4); // subtract 4 minutes only. change after nmms fixed their data         
        $resources = UserWidget::whereNotNull('resources_id')->with('resources')->get();
        foreach($resources as $resource){
            $rtd = MmsModRtd::where(['date'=>Date('Y-m-d'),'interval'=>$dt,'price_node'=>$resource->resources->resource_id])->first();
            $mw = $rtd !== null ? $rtd->mw : null;
            // $mw = round(rand(1,500).'.'.rand(1,99),1);
            $data[$resource->resources->resource_id] = array(
                'hour' => ($dt->hour+1),
                'interval' => 'Hour '.($dt->hour+1).' (Interval: '.$dt_from->hour .':'.Date('i',strtotime($dt_from)).' - '.$dt->hour.':'.Date('i',strtotime($dt)).'H)', //Used php Date, Carbon has no leading zeros for minutes below 10
                'mw' => $mw == null ? '--': round($mw, 1),
                'plus' => $mw == null ? '--': round($mw + ($mw * 1.5/100),1),
                'minus' => $mw == null ? '--': round($mw - ($mw * 3/100),1),
                'current' => Carbon::now()
            );
        }
        event(new RtdGrid($data));
    }
}
