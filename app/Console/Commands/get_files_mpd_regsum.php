<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\MmsRegionalSummaryHap;
use App\MmsRegionalSummaryDap;
use PHPExcel; 
use App\Overrides\Override_IOFactory as PHPExcel_IOFactory;
use PHPExcel_Style_NumberFormat;
use PHPExcel_Shared_Date;
use PHPExcel_CachedObjectStorageFactory;
use PHPExcel_Settings;

class get_files_mpd_regsum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'miner:get_files_mpd_regsum';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse files from mms (MPD Regional Summary)';

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
            preg_match('/MPD_(.*?)_(.*?)_/',$file,$match);
            switch ($match[1].'_'.$match[2]){
                case 'REGSUM_HAP' : // Market Projections Displays -> Regional Summary HAP
                    $objReader = PHPExcel_IOFactory::createReader('Excel2003XML');
                    $objReader->setReadDataOnly(true);
                    $workbook = $objReader->load($file);                    
                    $sheet = $workbook->getActiveSheet();
                    $row = 4;

                    while($sheet->getCell('A'.$row)->getValue()){            
                        $run_time = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A'.$row)->getValue());
                        $interval_end = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('B'.$row)->getValue());
                        $data = array(
                            'run_time' => Date('Y-m-d H:i:s',$run_time),
                            'interval_end' => Date('Y-m-d H:i:s',$interval_end),
                            'region' => $sheet->getCell('C'.$row)->getValue(),
                            'commodity' => $sheet->getCell('D'.$row)->getValue(),
                            'scenario' => $sheet->getCell('E'.$row)->getValue(),
                            'commodity_req' => $sheet->getCell('F'.$row)->getCalculatedValue() !== '' ? $sheet->getCell('F'.$row)->getCalculatedValue() : null,
                            'bid_in_demand' => $sheet->getCell('G'.$row)->getCalculatedValue() !== '' ? $sheet->getCell('G'.$row)->getCalculatedValue() : null,
                            'curtailed_load' => $sheet->getCell('H'.$row)->getCalculatedValue() !== '' ? $sheet->getCell('H'.$row)->getCalculatedValue() : null,
                            'energy_loss' => $sheet->getCell('I'.$row)->getCalculatedValue() !== '' ? $sheet->getCell('I'.$row)->getCalculatedValue() : null,
                            'generation' => $sheet->getCell('J'.$row)->getCalculatedValue() !== '' ? $sheet->getCell('J'.$row)->getCalculatedValue() : null,
                            'import' => $sheet->getCell('K'.$row)->getCalculatedValue() !== '' ? $sheet->getCell('K'.$row)->getCalculatedValue() : null,
                            'export' => $sheet->getCell('L'.$row)->getCalculatedValue() !== '' ? $sheet->getCell('L'.$row)->getCalculatedValue() : null,
                        );
                        $data_dup = array(
                            'run_time' => Date('Y-m-d H:i:s',$run_time),
                            'interval_end' => Date('Y-m-d H:i:s',$interval_end),
                            'region' => $sheet->getCell('C'.$row)->getValue(),
                            'commodity' => $sheet->getCell('D'.$row)->getValue()
                        );
                        $success = MmsRegionalSummaryHap::updateOrCreate($data_dup,$data);
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
                        echo "MMS MPD REGSUM HAP data has been saved\n";
                        // unlink($filename);
                    }                                        
                break;
                case 'REGSUM_DAP' : // Market Projections Displays -> Regional Summary DAP
                    $objReader = PHPExcel_IOFactory::createReader('Excel2003XML');
                    $objReader->setReadDataOnly(true);
                    $workbook = $objReader->load($file);                    
                    $sheet = $workbook->getActiveSheet();
                    $row = 4;

                    while($sheet->getCell('A'.$row)->getValue()){            
                        $run_time = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A'.$row)->getValue());
                        $interval_end = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('B'.$row)->getValue());
                        $data = array(
                            'run_time' => Date('Y-m-d H:i:s',$run_time),
                            'interval_end' => Date('Y-m-d H:i:s',$interval_end),
                            'region' => $sheet->getCell('C'.$row)->getValue(),
                            'commodity' => $sheet->getCell('D'.$row)->getValue(),
                            'scenario' => $sheet->getCell('E'.$row)->getValue(),
                            'commodity_req' => $sheet->getCell('F'.$row)->getCalculatedValue() !== '' ? $sheet->getCell('F'.$row)->getCalculatedValue() : null,
                            'bid_in_demand' => $sheet->getCell('G'.$row)->getCalculatedValue() !== '' ? $sheet->getCell('G'.$row)->getCalculatedValue() : null,
                            'curtailed_load' => $sheet->getCell('H'.$row)->getCalculatedValue() !== '' ? $sheet->getCell('H'.$row)->getCalculatedValue() : null,
                            'energy_loss' => $sheet->getCell('I'.$row)->getCalculatedValue() !== '' ? $sheet->getCell('I'.$row)->getCalculatedValue() : null,
                            'generation' => $sheet->getCell('J'.$row)->getCalculatedValue() !== '' ? $sheet->getCell('J'.$row)->getCalculatedValue() : null,
                            'import' => $sheet->getCell('K'.$row)->getCalculatedValue() !== '' ? $sheet->getCell('K'.$row)->getCalculatedValue() : null,
                            'export' => $sheet->getCell('L'.$row)->getCalculatedValue() !== '' ? $sheet->getCell('L'.$row)->getCalculatedValue() : null,
                        );
                        $data_dup = array(
                            'run_time' => Date('Y-m-d H:i:s',$run_time),
                            'interval_end' => Date('Y-m-d H:i:s',$interval_end),
                            'region' => $sheet->getCell('C'.$row)->getValue(),
                            'commodity' => $sheet->getCell('D'.$row)->getValue()
                        );
                        $success = MmsRegionalSummaryDap::updateOrCreate($data_dup,$data);
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
                        echo "MMS MPD REGSUM DAP data has been saved\n";
                        // unlink($filename);
                    }                                        
                break;
                // case 'REGSUM_WAP' : // Market Projections Displays -> Regional Summary WAP
                //     $objReader = PHPExcel_IOFactory::createReader('Excel2003XML');
                //     $objReader->setReadDataOnly(true);
                //     $workbook = $objReader->load($file);                    
                //     $sheet = $workbook->getActiveSheet();
                //     $row = 3;

                //     while($sheet->getCell('A'.$row)->getValue()){            
                //         $run_time = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A'.$row)->getValue());
                //         $interval_end = PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('B'.$row)->getValue());
                //         $data = array(
                        //     'run_time' => Date('Y-m-d H:i:s',$run_time),
                        //     'interval_end' => Date('Y-m-d H:i:s',$interval_end),
                        //     'region' => $sheet->getCell('C'.$row)->getValue(),
                        //     'commodity' => $sheet->getCell('D'.$row)->getValue(),
                        //     'scenario' => $sheet->getCell('E'.$row)->getValue(),
                        //     'commodity_req' => $sheet->getCell('F'.$row)->getValue(),
                        //     'bid_in_demand' => $sheet->getCell('G'.$row)->getValue(),
                        //     'curtailed_load' => $sheet->getCell('H'.$row)->getValue(),
                        //     'energy_loss' => $sheet->getCell('I'.$row)->getValue(),
                        //     'generation' => $sheet->getCell('J'.$row)->getValue(),
                        //     'import' => $sheet->getCell('K'.$row)->getValue(),
                        //     'export' => $sheet->getCell('L'.$row)->getValue(),
                        // );
                        // $data_dup = array(
                        //     'run_time' => Date('Y-m-d H:i:s',$run_time),
                        //     'interval_end' => Date('Y-m-d H:i:s',$interval_end),
                        //     'region' => $sheet->getCell('C'.$row)->getValue(),
                        //     'commodity' => $sheet->getCell('D'.$row)->getValue()
                        // );
                //         $success = MmsRegionalSummaryWap::updateOrCreate($data_dup,$data);
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
                //         echo "MMS MPD REGSUM WAP data has been saved\n";
                //         // unlink($filename);
                //     }                                        
                // break;
            };
            
        }        
    }
}
