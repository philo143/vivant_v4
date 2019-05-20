<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\TickerData;
use App\ResourcesLookup;
use App\Zones;
use Illuminate\Support\Facades\DB;
use App\MmsModLmp;

class get_ticker_data extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:get_ticker_data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nodal Prices Ticker';

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
        $zones = Zones::all();
        $data = array();
        $data = array();
        $di = explode(' ',$interval['intra_intervals'][0]);
        $date = $di[0];
        $int = $di[1];
        foreach($zones as $zone){
            $resources = ResourcesLookup::orWhereRaw('SUBSTRING(resource_id,2) like "'. $zone->zone_prefix.'%"' )->get();            
            foreach ($resources as $resource) {
                 $type = $resource->type;
                 $resource_id = $resource->resource_id;
                 $mw = MmsModLmp::where(['date'=>$date,'interval'=>$int,'price_node'=>$resource_id])->pluck('lmp')->first();
                 $data[$type][$zone->zone_prefix][$resource_id] = $mw !== null ? number_format($mw,2) : '--';
            }
        }
        event(new TickerData($data));
    }
}
