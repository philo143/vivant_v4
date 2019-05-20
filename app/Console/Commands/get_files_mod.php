<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\MmsModRtd;
use App\MmsModLmp;
use App\UserWidget;
use PHPExcel; 
use App\Overrides\Override_IOFactory as PHPExcel_IOFactory;
use PHPExcel_Style_NumberFormat;
use PHPExcel_Shared_Date;
use App\Resource;
use App\Events\NodalPriceGrid;
use App\Events\RtdGrid;
use \Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;


class get_files_mod extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'miner:get_files_mod';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse files from mms and input to database';

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
    // private function match_array($list){
    //     // if(preg_match('/RTD/',$list)){
    //     //     return true;
    //     // }else{
    //     //     return false;
    //     // }
    // }
    
    public function handle()
    {
        $files = glob(base_path().'/miner/mms_mod/RTD_*');
        foreach($files as $file){
            $filename = $file;
            preg_match('/RTD_(.*?)_/',$file,$match);
            switch ($match[1]){
                case 'ResSpec' : // RTD OUTPUT DISPLAY -> Resource Specific                    
                    $ret = array();          
                    
                    $file = file_get_contents($file); 
                    $f = json_decode($file,true);
                    $pn_count = count($f) - 1;
                    for($n=0;$n<=$pn_count;$n++){                             
                        $d = $f[$n];
                        $dt = explode(" ",$d[0]);
                        $data = array(
                            'date' => date('Y-m-d',strtotime($dt[0])),
                            'interval' => $dt[1],
                            'price_node' => $d[1],
                            'mw' => str_replace(',','',$d[2]),
                            'lmp' => str_replace(',','',$d[3]),
                            'loss_factor' => str_replace(',','',$d[4]),
                            'energy' => str_replace(',','',$d[5]),
                            'loss' => str_replace(',','',$d[6]),
                            'congestion' => str_replace(',','',$d[7])
                        );
                        $data_dup = array(
                           'date' => date('Y-m-d',strtotime($dt[0])),
                            'interval' => $dt[1],
                            'price_node' => $d[1]
                        );             
                        $success = MmsModRtd::updateOrCreate($data_dup,$data); 
                        if(!$success){
                            echo "There has been an error upon parsing the data";
                            die();
                        }else{
                            //IF Artisan doesnt work
                            // $date = $data['date'];
                            // $int = $data['interval'];
                            // // dd($data['interval']);
                            // $res_id = $data['price_node'];
                            // $hr_min = explode(':',$int);
                            // $mw = $data['mw'] !== null ? $data['mw'] : 0;
                            // $dt_from = Carbon::createFromTime($hr_min[0], $hr_min[1], 00, 'Asia/Manila')->subMinutes(4)->format('H:i');
                            // $hour = explode(':',$dt_from);
                            // $ret[$data['price_node']] = array(
                            //     'hour' => $hour[0]+1,
                            //     'interval' => 'Hour '.($hour[0]+1).' (Interval: '.$dt_from.' - '.$int.'H)', //Used php Date, Carbon has no leading zeros for minutes below 10
                            //     'mw' => $mw == 0 ? '0.0': round($mw, 1),
                            //     'plus' => $mw == 0 ? '0.0': round($mw + ($mw * 1.5/100), 1),
                            //     'minus' => $mw == 0 ? '0.0': round($mw - ($mw * 3/100), 1),
                            //     'current' => Carbon::now()
                            // );
                        }                          
                    }     
                    if($success){
                        Artisan::call('miner:get_rtd_sched'); 
                        // event(new RtdGrid($ret)); IF Artisan doesnt work
                        echo "RTD Resource Specific data has been saved";
                        unlink($filename);                        
                    }
                          
                break;

                case 'LMP' : // RTD OUTPUT DISPLAY -> LMPs

                    $objReader = PHPExcel_IOFactory::createReader('Excel2003XML');
                    $objReader->setReadDataOnly(true);
                    $workbook = $objReader->load($file);                    
                    $sheet = $workbook->getActiveSheet();
                    $row = 3;
                    $resources = Resource::pluck('resource_id')->toArray();
                    $intra_interval = getIntraIntervalDetails();
                    if($sheet->getCell('A7')->getValue() != ''){
                        while($sheet->getCell('A'.$row)->getValue()){                                    
                            // $interval_end = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A'.$row)->getValue());

                            $interval_end_cell = $sheet->getCell('A'.$row);
                            if(PHPExcel_Shared_Date::isDateTime($interval_end_cell)){
                                $interval_end = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A'.$row)->getValue());
                            }  else {
                                $interval_end = strtotime($sheet->getCell('A'.$row)->getValue());
                            }


                            $interval_end = Date('Y-m-d H:i:s',$interval_end);
                            $dt = explode(" ",$interval_end);
                            $data = array(
                                'date' => $dt[0],
                                'interval' => $dt[1],
                                'price_node' => $sheet->getCell('B'.$row)->getValue(),
                                'lmp' => $sheet->getCell('C'.$row)->getValue(),
                            );
                            $data_dup = array(
                                'date' => $dt[0],
                                'interval' => $dt[1],
                                'price_node' => $sheet->getCell('B'.$row)->getValue()
                            );
                            $success = MmsModLmp::updateOrCreate($data_dup,$data);
                            if(!$success){
                                echo "There has been an error upon parsing the data";
                                die();
                            }else{
                                // IF Artisan doesnt work
                                // if(in_array($data['price_node'],$resources)){
                                //     $date = $data['date'];
                                //     $int = $data['interval'];
                                //     $res_id = $data['price_node'];
                                //     $lmp[$res_id][$date.' '.$int] = $data['lmp'] == null ? '--' : round($data['lmp'],1);
                                // }
                            }
                            $row++;                        
                        }
                        $workbook->disconnectWorksheets(); 
                        unset($workbook);                         
                        if($success){
                            Artisan::call('miner:get_nodal_prices');
                            Artisan::call('dashboard:get_ticker_data');
                            // IF Artisan doesnt work
                            // $ret = array(
                            //         'intrainterval' => $date.' '.$int,
                            //         'data' => $lmp,
                            //         'hour' => $intra_interval['hour']
                            //     );
                            // event(new NodalPriceGrid($ret));
                            echo $filename.'\n';
                            echo "MMS RTD LMP data has been saved\n";
                            unlink($filename);
                        }      
                    }
                                                      
                break;
            }
        }        
    }
}
