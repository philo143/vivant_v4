<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Participant;
use App\IpTable;
use Request;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\get_mbo::class,
        Commands\get_rtd_sched::class,
        Commands\get_ticker_data::class,
        Commands\get_nodal_prices::class,
        Commands\get_files_mod::class,
        Commands\get_files_mpd::class,
        Commands\get_files_mpd_regsum::class,
        Commands\get_realtime_monitoring_data::class,
        Commands\submit_xml_bid::class,
        Commands\get_time::class,
        Commands\get_actual_load::class,
        Commands\get_dap_prices_schedules::class,
        Commands\get_hap_prices_schedules::class,

    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */

    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();        
        
        
        $schedule->command('miner:get_rtd_sched')
                 ->cron('1,6,11,16,21,26,31,36,41,46,51,56 * * * *');
        $schedule->command('dashboard:get_ticker_data')
                ->cron('1,6,11,16,21,26,31,36,41,46,51,56 * * * *');
        $schedule->command('miner:get_nodal_prices')
                 ->cron('1,6,11,16,21,26,31,36,41,46,51,56 * * * *');
        $schedule->command('dashboard:update_actual_load')
                 ->cron('1,6,11,16,21,26,31,36,41,46,51,56 * * * *');
                 
        $schedule->command('app:realtime_monitoring_data_updater');
        $schedule->command('app:time')->everyMinute();

        //FAILED STATUS RETRIEVAL CHECKER//
        // $schedule->exec('phantomjs --debug=true miner/status_checker.js '.Request::root())->everyMinute()->withoutOverlapping()->sendOutputTo(base_path()."/miner/status_checker.log");
        // server side //
        $schedule->exec('/usr/local/bin/phantomjs --debug=true miner/status_checker.js '.Request::root())->everyMinute()->withoutOverlapping()->sendOutputTo(base_path()."/miner/status_checker.log");
        // MINERS //
        $participants = Participant::where('status','active')->whereNotNull('cert_file')->get()->toArray();
        $nmms_ip = IpTable::where(['status'=>'1','type'=>'mms'])->first()->toArray();
        foreach($participants as $p){
            $name = $p['participant_name'];
            $cert_pem   =   base_path().$p['cert_loc']."/".$p['cert_file'].'.pem';
            $cert_key   =   base_path().$p['cert_loc']."/".$p['cert_file'].'.crt';
            $cert_pass  =   $p['cert_user'].':'.$p['cert_pass'];
            // ALL IN ONE MINER (RTD,LMP,SCHED DAP,SCHED HAP) args [IP] [participant] [cert_user:cert_pass]
            // $schedule->exec('phantomjs --config=miner/config.json --ssl-client-certificate-file='.$cert_key.' --ssl-client-key-file='.$cert_pem.'  miner/nmms_miner.js '.$nmms_ip['ip_address'].' '.$name.' '.$cert_pass)->everyMinute()->withoutOverlapping()->sendOutputTo(base_path()."/miner/miner_".$name."_log.log");

            // server side //
            $schedule->exec('/usr/local/bin/phantomjs --config=miner/config.json --ssl-client-certificate-file='.$cert_key.' --ssl-client-key-file='.$cert_pem.'  miner/nmms_miner.js '.$nmms_ip['ip_address'].' '.$name.' '.$cert_pass)->everyMinute()->withoutOverlapping()->sendOutputTo(base_path()."/miner/miner_".$name."_log.log");
        }
        // PARSERS FOR NMMS
        $schedule->command('miner:get_files_mod')
                  ->everyMinute()
                  ->withOutOverlapping();
        $schedule->command('miner:get_files_mpd')
                  ->everyMinute()
                  ->withOutOverlapping();       
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
