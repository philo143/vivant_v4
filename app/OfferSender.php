<?php

namespace App;
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;
use App\OfferSubmissionUnits;
use App\IpTable;

class OfferSender {
    public function sendParams($id,$cert_user,$cert_pass,$cert_file,$cert_loc){ // TO FOLLOW (NEED TO MOVE THIS TO A DIFFERENT FOLDER OR NEW LARAVEL PROJECT AND ADD API TOKEN)
        $param = array();
        $param['xml'] = OfferSubmissionUnits::where('id',$id)->pluck('generated_xml')->first();
        $param['xml'] = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>'.$param['xml'].'</soap:Body></soap:Envelope>';
        $param['cert_user'] = $cert_user;
        $param['cert_pass'] = $cert_pass;
        $param['cert_file'] = $cert_file;
        $param['cert_loc'] = $cert_loc;
        $return = $this->submitXml($param);
        return $return;
    }
    private function submitXml($p){
        $path = base_path($p['cert_loc']).'/'.$p['cert_file'].'.pem';
        $nmms_ip = IpTable::where(['status'=>'1','type'=>'mms'])->first()->toArray();
        $ip = $nmms_ip['ip_address'];
        $url = 'https://'.$ip.'/SiemensServices/SAFServiceImpl'; // to follow ip changer
        $guzzleClient = new GuzzleClient([
            'headers' => ['SOAPAction' => 'RawBidSet',
                          'Accept' => 'text/xml',
                          'Content-Length' => strlen($p['xml']),
                          'Content-Type' => 'text/xml; charset=UTF-8'
                        ],
            'cert'=> base_path($p['cert_loc']).'/'.$p['cert_file'].'.pem',
            'verify' => false,  
            'http_errors' => false          
        ]);
        
        $response = $guzzleClient->request('POST',$url,array('body'=>$p['xml']));
        $res = $response->getBody()->getContents();
        dd($res);
        preg_match('/<Message>(.*?)<\/Message>/', $res, $matches);
        return $matches[1];
    }
}
