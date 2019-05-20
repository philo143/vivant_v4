<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\NodalPriceGrid;
use \Carbon\Carbon;
use App\UserWidget;
use App\Resource;
use App\Console\Commands\Redis;
use App\MmsModLmp;

class get_nodal_prices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'miner:get_nodal_prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nodal Prices Grid Data';

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
        $nodal_prices_data  = array();
        $interval_data = getIntraIntervalDetails();
        $intra_intervals = $interval_data['intra_intervals'];
        
        // $user = auth()->user();
        $resources = Resource::all();
        // get resource id name values
        //$resources = Resource::whereIn('id',$resource_ids)->get();
        foreach ($resources as $row) {
             $resource_id = $row->resource_id;
             foreach ($intra_intervals as $intra) {
                $intra_x = explode(' ',$intra);
                $date = $intra_x[0];
                $interval = $intra_x[1];
                $d = MmsModLmp::where(['date'=>$date,'interval'=>$interval,'price_node'=>$resource_id])->first();
                $nodal_prices_data[$resource_id][$intra] = $d['lmp'] == null ? '--' : number_format($d['lmp'],2);
             }

        } // end foreach

        event(new NodalPriceGrid(array(
                'data' => $nodal_prices_data,
                'intrainterval' => $interval_data
            )));
        // event(new NodalPriceGrid($nodal_prices_data));
    }
}
