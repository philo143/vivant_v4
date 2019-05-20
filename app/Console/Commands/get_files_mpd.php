<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\MmsRtd;
use App\MmsMpdHapLmp;
use App\MmsMpdDapLmp;
use App\MmsMpdWapLmp;
use App\MmsHapPriceAndSchedule;
use App\MmsDapPriceAndSchedule;
use PHPExcel; 
use App\Overrides\Override_IOFactory as PHPExcel_IOFactory;
use PHPExcel_Style_NumberFormat;
use PHPExcel_Shared_Date;
use PHPExcel_CachedObjectStorageFactory;
use PHPExcel_Settings;
use Illuminate\Support\Facades\Artisan;

class get_files_mpd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'miner:get_files_mpd';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Files from MMS Market Projections Displays';

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
        $files = glob(base_path().'/miner/mms_mpd/MPD_*');
        foreach($files as $file){
            $filename = $file;         
            echo "filename " . $filename . "\n";   
            preg_match('/MPD_(.*?)_(.*?)_/',$file,$match);
            switch ($match[1].'_'.$match[2]){
                case 'LMPS_HAP' : // Market Projections Displays -> LMPs HAP
                    if($sheet->getCell('A7')->getValue() != ''){
                        $objReader = PHPExcel_IOFactory::createReader('Excel2003XML');
                        $objReader->setReadDataOnly(true);
                        $workbook = $objReader->load($file);                    
                        $sheet = $workbook->getActiveSheet();
                        $row = 3;
                        while($sheet->getCell('A'.$row)->getValue()){            
                            // $run_time = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A'.$row)->getValue());
                            // $interval_end = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('B'.$row)->getValue());

                            $run_time_cell = $sheet->getCell('A'.$row);
                            if(PHPExcel_Shared_Date::isDateTime($run_time_cell)){
                                $run_time = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A'.$row)->getValue());
                            }  else {
                                $run_time = strtotime($sheet->getCell('A'.$row)->getValue());
                            }

                            $interval_end_cell = $sheet->getCell('B'.$row);
                            if(PHPExcel_Shared_Date::isDateTime($interval_end_cell)){
                                $interval_end = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('B'.$row)->getValue());
                            }  else {
                                $interval_end = strtotime($sheet->getCell('B'.$row)->getValue());
                            }

                            $data = array(
                                'run_time' => Date('Y-m-d H:i:s',$run_time),
                                'interval_end' => Date('Y-m-d H:i:s',$interval_end),
                                'price_node' => $sheet->getCell('C'.$row)->getValue(),
                                'lmp' => $sheet->getCell('D'.$row)->getValue(),
                            );
                            $data_dup = array(
                                'run_time' => Date('Y-m-d H:i:s',$run_time),
                                'interval_end' => Date('Y-m-d H:i:s',$interval_end),
                                'price_node' => $sheet->getCell('C'.$row)->getValue()
                            );
                            $success = MmsMpdHapLmp::updateOrCreate($data_dup,$data);
                            if(!$success){
                                echo "There has been an error upon parsing the data";
                                die();
                            }
                            $row++;
                        }
                        $workbook->disconnectWorksheets(); 
                        unset($workbook);                         
                        // print_r(memory_get_peak_usage());                                   
                        if($success){
                            echo $filename.'\n';
                            echo "MMS MPD LMP HAP data has been saved\n";
                            unlink($filename);
                        }
                    }                                        
                break;

                case 'LMPS_DAP' : // Market Projections Displays -> LMPs DAP

                    $objReader = PHPExcel_IOFactory::createReader('Excel2003XML');
                    $objReader->setReadDataOnly(true);
                    $workbook = $objReader->load($file);                    
                    $sheet = $workbook->getActiveSheet();
                    $row = 3;
                    if($sheet->getCell('A7')->getValue() != ''){
                        while($sheet->getCell('A'.$row)->getValue()){            
                            // $run_time = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A'.$row)->getValue());
                            // $interval_end = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('B'.$row)->getValue());

                            $run_time_cell = $sheet->getCell('A'.$row);
                            if(PHPExcel_Shared_Date::isDateTime($run_time_cell)){
                                $run_time = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A'.$row)->getValue());
                            }  else {
                                $run_time = strtotime($sheet->getCell('A'.$row)->getValue());
                            }

                            $interval_end_cell = $sheet->getCell('B'.$row);
                            if(PHPExcel_Shared_Date::isDateTime($interval_end_cell)){
                                $interval_end = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('B'.$row)->getValue());
                            }  else {
                                $interval_end = strtotime($sheet->getCell('B'.$row)->getValue());
                            }

                            $data = array(
                                'run_time' => Date('Y-m-d H:i:s',$run_time),
                                'interval_end' => Date('Y-m-d H:i:s',$interval_end),
                                'price_node' => $sheet->getCell('C'.$row)->getValue(),
                                'lmp' => $sheet->getCell('D'.$row)->getValue(),
                            );
                            $data_dup = array(
                                'run_time' => Date('Y-m-d H:i:s',$run_time),
                                'interval_end' => Date('Y-m-d H:i:s',$interval_end),
                                'price_node' => $sheet->getCell('C'.$row)->getValue()
                            );
                            $success = MmsMpdDapLmp::updateOrCreate($data_dup,$data);
                            if(!$success){
                                echo "There has been an error upon parsing the data";
                                die();
                            }
                            $row++;
                        }
                        $workbook->disconnectWorksheets(); 
                        unset($workbook);                         
                        // print_r(memory_get_peak_usage());
                        if($success){
                            echo $filename.'\n';
                            echo "MMS MPD LMP DAP data has been saved\n";
                            unlink($filename);
                        }
                    }                                        
                break;
                case 'LMPS_WAP' : // Market Projections Displays -> LMPs WAP
                    $objReader = PHPExcel_IOFactory::createReader('Excel2003XML');
                    $objReader->setReadDataOnly(true);
                    $workbook = $objReader->load($file);                    
                    $sheet = $workbook->getActiveSheet();
                    $row = 3;
                    if($sheet->getCell('A7')->getValue() != ''){
                        while($sheet->getCell('A'.$row)->getValue()){            
                            // $run_time = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A'.$row)->getValue());
                            // $interval_end = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('B'.$row)->getValue());

                            $run_time_cell = $sheet->getCell('A'.$row);
                            if(PHPExcel_Shared_Date::isDateTime($run_time_cell)){
                                $run_time = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A'.$row)->getValue());
                            }  else {
                                $run_time = strtotime($sheet->getCell('A'.$row)->getValue());
                            }

                            $interval_end_cell = $sheet->getCell('B'.$row);
                            if(PHPExcel_Shared_Date::isDateTime($interval_end_cell)){
                                $interval_end = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('B'.$row)->getValue());
                            }  else {
                                $interval_end = strtotime($sheet->getCell('B'.$row)->getValue());
                            }
                            
                            $data = array(
                                'run_time' => Date('Y-m-d H:i:s',$run_time),
                                'interval_end' => Date('Y-m-d H:i:s',$interval_end),
                                'price_node' => $sheet->getCell('C'.$row)->getValue(),
                                'lmp' => $sheet->getCell('D'.$row)->getValue(),
                            );
                            $data_dup = array(
                                'run_time' => Date('Y-m-d H:i:s',$run_time),
                                'interval_end' => Date('Y-m-d H:i:s',$interval_end),
                                'price_node' => $sheet->getCell('C'.$row)->getValue()
                            );
                            $success = MmsMpdWapLmp::updateOrCreate($data_dup,$data);
                            if(!$success){
                                echo "There has been an error upon parsing the data";
                                die();
                            }
                            $row++;
                            // print_r(memory_get_peak_usage()."\n");
                        }
                        $workbook->disconnectWorksheets(); 
                        unset($workbook);                         
                        // print_r(memory_get_peak_usage());
                        if($success){
                            echo $filename.'\n';
                            echo "MMS MPD LMP WAP data has been saved\n";
                            unlink($filename);
                        }
                    }                                        
                break;
                case 'SCHED_HAP' : // Market Projections Displays -> SCHEDULES HAP
                    $objReader = PHPExcel_IOFactory::createReader('Excel2003XML');
                    $objReader->setReadDataOnly(true);
                    $workbook = $objReader->load($file);                    
                    $sheet = $workbook->getActiveSheet();
                    $row = 4;
                    if($sheet->getCell('A7')->getValue() != ''){
                        while($sheet->getCell('A'.$row)->getValue()){            
                            // $run_time = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A'.$row)->getValue());
                            // $interval_end = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('B'.$row)->getValue());

                            $run_time_cell = $sheet->getCell('A'.$row);
                            if(PHPExcel_Shared_Date::isDateTime($run_time_cell)){
                                $run_time = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A'.$row)->getValue());
                            }  else {
                                $run_time = strtotime($sheet->getCell('A'.$row)->getValue());
                            }

                            $interval_end_cell = $sheet->getCell('B'.$row);
                            if(PHPExcel_Shared_Date::isDateTime($interval_end_cell)){
                                $interval_end = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('B'.$row)->getValue());
                            }  else {
                                $interval_end = strtotime($sheet->getCell('B'.$row)->getValue());
                            }

                            $data = array(
                                'run_time' => Date('Y-m-d H:i:s',$run_time),
                                'interval_end' => Date('Y-m-d H:i:s',$interval_end),
                                'price_node' => $sheet->getCell('C'.$row)->getValue(),
                                'mw' => $sheet->getCell('D'.$row)->getValue(),
                                'lmp' => $sheet->getCell('E'.$row)->getValue(),
                                'loss_factor' => $sheet->getCell('F'.$row)->getValue(),
                                'energy' => $sheet->getCell('G'.$row)->getValue(),
                                'loss' => $sheet->getCell('H'.$row)->getValue(),
                                'congestion' => $sheet->getCell('I'.$row)->getValue(),
                            );
                            $data_dup = array(
                                'run_time' => Date('Y-m-d H:i:s',$run_time),
                                'interval_end' => Date('Y-m-d H:i:s',$interval_end),
                                'price_node' => $sheet->getCell('C'.$row)->getValue()
                            );
                            $success = MmsHapPriceAndSchedule::updateOrCreate($data_dup,$data);
                            if(!$success){
                                echo "There has been an error upon parsing the data";
                                die();
                            }
                            $row++;
                            // print_r(memory_get_peak_usage()."\n");
                        }
                        $workbook->disconnectWorksheets(); 
                        unset($workbook);                         
                        // print_r(memory_get_peak_usage());
                        if($success){
                            Artisan::call('dashboard:hap_prices_schedules');
                            echo $filename.'\n';
                            echo "MMS MPD SCHED HAP data has been saved\n";
                            unlink($filename);
                        }
                    }                                        
                break;
                case 'SCHED_DAP' : // Market Projections Displays -> SCHEDULES DAP
                    $objReader = PHPExcel_IOFactory::createReader('Excel2003XML');
                    $objReader->setReadDataOnly(true);
                    $workbook = $objReader->load($file);                    
                    $sheet = $workbook->getActiveSheet();
                    $row = 4;
                    if($sheet->getCell('A7')->getValue() != ''){

                        while($sheet->getCell('A'.$row)->getValue()){     
                            
                            $run_time_cell = $sheet->getCell('A'.$row);
                            if(PHPExcel_Shared_Date::isDateTime($run_time_cell)){
                                $run_time = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A'.$row)->getValue());
                            }  else {
                                $run_time = strtotime($sheet->getCell('A'.$row)->getValue());
                            }

                            $interval_end_cell = $sheet->getCell('B'.$row);
                            if(PHPExcel_Shared_Date::isDateTime($interval_end_cell)){
                                $interval_end = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('B'.$row)->getValue());
                            }  else {
                                $interval_end = strtotime($sheet->getCell('B'.$row)->getValue());
                            }
                            
                            $data = array(
                                'run_time' => Date('Y-m-d H:i:s',$run_time),
                                'interval_end' => Date('Y-m-d H:i:s',$interval_end),
                                'price_node' => $sheet->getCell('C'.$row)->getValue(),
                                'mw' => $sheet->getCell('D'.$row)->getValue(),
                                'lmp' => $sheet->getCell('E'.$row)->getValue(),
                                'loss_factor' => $sheet->getCell('F'.$row)->getValue(),
                                'energy' => $sheet->getCell('G'.$row)->getValue(),
                                'loss' => $sheet->getCell('H'.$row)->getValue(),
                                'congestion' => $sheet->getCell('I'.$row)->getValue(),
                            );

                            echo "#######  : " . $run_time . "\n";
                            $data_dup = array(
                                'run_time' => Date('Y-m-d H:i:s',$run_time),
                                'interval_end' => Date('Y-m-d H:i:s',$interval_end),
                                'price_node' => $sheet->getCell('C'.$row)->getValue()
                            );

                            

                            $success = MmsDapPriceAndSchedule::updateOrCreate($data_dup,$data);
                            if(!$success){
                                echo "There has been an error upon parsing the data";
                                die();
                            }
                            $row++;
                            // print_r(memory_get_peak_usage()."\n");
                        }
                        $workbook->disconnectWorksheets(); 
                        unset($workbook);                         
                        // print_r(memory_get_peak_usage());
                        if($success){
                            // Artisan::call('dashboard:dap_prices');
                            Artisan::call('dashboard:dap_prices_schedules');
                            // Artisan::call('dashboard:dap_prices_schedules');
                            echo $filename.'\n';
                            echo "MMS MPD SCHED DAP data has been saved\n";
                            unlink($filename);
                        }   
                    }                                     
                break;
                // case 'SCHED_WAP' : // Market Projections Displays -> SCHEDULES WAP
                //     $objReader = PHPExcel_IOFactory::createReader('Excel2003XML');
                //     $objReader->setReadDataOnly(true);
                //     $workbook = $objReader->load($file);                    
                //     $sheet = $workbook->getActiveSheet();
                //     $row = 3;

                //     while($sheet->getCell('A'.$row)->getValue()){            
                //         $run_time = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A'.$row)->getValue());
                //         $interval_end = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('B'.$row)->getValue());
                //         $data = array(
                //             'run_time' => Date('Y-m-d H:i:s',$run_time),
                //             'interval_end' => Date('Y-m-d H:i:s',$interval_end),
                //             'price_node' => $sheet->getCell('C'.$row)->getValue(),
                //             'mw' => $sheet->getCell('D'.$row)->getValue(),
                //             'lmp' => $sheet->getCell('E'.$row)->getValue(),
                //             'loss_factor' => $sheet->getCell('F'.$row)->getValue(),
                //             'energy' => $sheet->getCell('G'.$row)->getValue(),
                //             'loss' => $sheet->getCell('H'.$row)->getValue(),
                //             'congestion' => $sheet->getCell('I'.$row)->getValue(),
                //         );
                //         $data_dup = array(
                //             'run_time' => Date('Y-m-d H:i:s',$run_time),
                //             'interval_end' => Date('Y-m-d H:i:s',$interval_end),
                //             'price_node' => $sheet->getCell('C'.$row)->getValue()
                //         );
                //         $success = MmsMpdWapLmp::updateOrCreate($data_dup,$data);
                //         if(!$success){
                //             echo "There has been an error upon parsing the data";
                //             die();
                //         }
                //         $row++;
                //         // print_r(memory_get_peak_usage()."\n");
                //     }
                //     $workbook->disconnectWorksheets(); 
                //     unset($workbook);                         
                //     // print_r(memory_get_peak_usage());
                //     if($success){
                //         echo $filename.'\n';
                //         echo "MMS MPD SCHED WAP data has been saved\n";
                //         // unlink($filename);
                //     }                                        
                // break;
            };
            
        }        
    }
}
