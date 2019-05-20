<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
// use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use Carbon\Carbon;

class submit_xml_bid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mms:submit_xml {ip} {cert} {xml}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Submit xml file.';

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
       $ip = $this->argument('ip');
       $cert = $this->argument('cert');
       $xml = $this->argument('xml');

       $xml = simplexml_load_file($xml);
       $xml = $xml->asXML();
       $xml_string = preg_replace("/<\\?xml.*\\?>/",'',$xml,1);
       $param = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>';
       $param .= $xml_string;
       $param .= '</soap:Body></soap:Envelope>';
       $url = 'https://'.$ip.'/SiemensServices/SAFServiceImpl';
       $guzzleClient = new GuzzleClient([
            'headers' => ['SOAPAction' => 'RawBidSet',
                          'Accept' => 'text/xml',
                          'Content-Length' => strlen($param),
                          'Content-Type' => 'text/xml; charset=UTF-8'
                        ],
            'cert'=>$cert,
            'verify' => false,  
            'http_errors' => false          
       ]);
    
        $response = $guzzleClient->request('POST',$url,array('body'=>$param));
        $res = $response->getBody()->getContents();
        dd($res);
        preg_match('/<Message>(.*?)<\/Message>/', $res, $matches);
        dd($matches[1]);
    }
}
