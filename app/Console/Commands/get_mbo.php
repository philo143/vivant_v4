<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use Carbon\Carbon;
use App\MarketBidsAndOffers;

class get_mbo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'miner:mbo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download MBO';

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
        //  Create a new Goutte client instance


        $url = "http://ngcp.ph/operations#situation";
        $client = new Client();
        $guzzleClient = new GuzzleClient();
        $client->setClient($guzzleClient);
        
        $crawler = $client->getAsync($url);
        $output = $crawler->filter('#carousel-situation-body')->extract('table');

        dd($output);
        // $url = 'http://wesm.ph/inner.php/downloads/market_bids_&_offers';
        // $css_selector = 'a.link';
        // $thing_to_scrape = 'href';

        // $client = new Client();
        // $guzzleClient = new GuzzleClient();
        // // $client->setClient($guzzleClient);

        // $report = 'RTD';
        // $url = "http://wesm.ph/include/downloads_hour_function.php?id=44&base_url=http://www.wesm.ph/&type=$report&resource=OG&region=LUZ&radiogroup=all&dateoption=latest";

        // $crawler = $client->request('GET', $url);
        // $output = $crawler->filter($css_selector)->extract(array('href','_text'));

        // $filename = $output[0][1];
        // $url = $output[0][0];

        // $guzzleClient->request('GET', $url, ['sink' => '/tmp/' . $filename]);
        // $resource = fopen('/tmp/'.$filename, 'r');

        // $now = Carbon::now('utc')->toDateTimeString();
        // $data = array();
        // $x=0;
        // while (!feof($resource)) 
        // {
        //     $x++;
        //     $csv = fgetcsv($resource);
        //     if (8 <= $x && 'EOF' !== $csv[0]) {
        //         $data[] = array(
        //             'report' => $report,
        //             'date' => date('Ymd',strtotime($csv[0])),
        //             'hour' => $csv[1],
        //             'region' => $csv[2],
        //             'type' => $csv[3],
        //             'participant' => $csv[4],
        //             'resource_id' => $csv[5],
        //             'p1' => empty($csv[6]) ? NULL : $csv[6],
        //             'q1' => empty($csv[7]) ? NULL : $csv[7],
        //             'p2' => empty($csv[8]) ? NULL : $csv[8],
        //             'q2' => empty($csv[9]) ? NULL : $csv[9],
        //             'p3' => empty($csv[10]) ? NULL : $csv[10],
        //             'q3' => empty($csv[11]) ? NULL : $csv[11],
        //             'p4' => empty($csv[12]) ? NULL : $csv[12],
        //             'q4' => empty($csv[13]) ? NULL : $csv[13],
        //             'p5' => empty($csv[14]) ? NULL : $csv[14],
        //             'q5' => empty($csv[15]) ? NULL : $csv[15],
        //             'p6' => empty($csv[16]) ? NULL : $csv[16],
        //             'q6' => empty($csv[17]) ? NULL : $csv[17],
        //             'p7' => empty($csv[18]) ? NULL : $csv[18],
        //             'q7' => empty($csv[19]) ? NULL : $csv[19],
        //             'p8' => empty($csv[20]) ? NULL : $csv[20],
        //             'q8' => empty($csv[21]) ? NULL : $csv[21],
        //             'p9' => empty($csv[22]) ? NULL : $csv[22],
        //             'q9' => empty($csv[23]) ? NULL : $csv[23],
        //             'p10' => empty($csv[24]) ? NULL : $csv[24],
        //             'q10' => empty($csv[25]) ? NULL : $csv[25],
        //             'p11' => empty($csv[26]) ? NULL : $csv[26],
        //             'q11' => empty($csv[27]) ? NULL : $csv[27],
        //             'rr_break_qty1' => empty($csv[28]) ? NULL : $csv[28],
        //             'rr_up1'=> empty($csv[29]) ? NULL : $csv[29],
        //             'rr_down1'=> empty($csv[30]) ? NULL : $csv[30],
        //             'rr_break_qty2' => empty($csv[31]) ? NULL : $csv[31],
        //             'rr_up2'=> empty($csv[32]) ? NULL : $csv[32],
        //             'rr_down2'=> empty($csv[33]) ? NULL : $csv[33],
        //             'rr_break_qty3' => empty($csv[34]) ? NULL : $csv[34],
        //             'rr_up3'=> empty($csv[35]) ? NULL : $csv[35],
        //             'rr_down3'=> empty($csv[36]) ? NULL : $csv[36],
        //             'rr_break_qty4' => empty($csv[37]) ? NULL : $csv[37],
        //             'rr_up4'=> empty($csv[38]) ? NULL : $csv[38],
        //             'rr_down4'=> empty($csv[39]) ? NULL : $csv[39],
        //             'rr_break_qty5' => empty($csv[40]) ? NULL : $csv[40],
        //             'rr_up5'=> empty($csv[41]) ? NULL : $csv[41],
        //             'rr_down5'=> empty($csv[42]) ? NULL : $csv[42],
        //             'created_at' => $now,
        //             'updated_at' => $now
        //         );    
        //     }

        // }
        // fclose($resource);
        
        // foreach(array_chunk($data, 1000) as $d) {
        //     $response = MarketBidsandOffers::insert($d);    
        //     echo $response . "\n";
        // }
        
        // echo 'Finished Inserting MBO data';
        
    }
}
