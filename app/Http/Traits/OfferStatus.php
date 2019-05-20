<?php 

namespace App\Http\Traits;

use App\Http\Traits\OfferStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Participant;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\IpTable;


trait OfferStatus
{
	public static function retrieve($participant,$bid_id,$resource){
        $path = PHP_OS_FAMILY == "Windows" ? 'C:/var/miner/' : '/var/miner/';
        $nmms_ip = IpTable::where(['status'=>'1','type'=>'mms'])->first()->toArray();
        $r = array(
            'participant' => $participant,
            'bid_id' => $bid_id,
            'resource' => $resource,
            'ip' => $nmms_ip['ip_address'],
            // 'server_url' => 'http://localhost:8000' // FOR LOCAL
            'server_url' => request()->root()
        );
        $p = Participant::where(['participant_name'=>$r['participant']])
                          ->first()->toArray();
        $cert_pem   =   base_path().$p['cert_loc']."/".$p['cert_file'].'.pem';
        $cert_key   =   base_path().$p['cert_loc']."/".$p['cert_file'].'.crt';
        $cert_user  =   $p['cert_user'];
        $cert_pass  =   $p['cert_pass'];

        //FOR WINDOWS MACHINES // start /b cmd /c
        //FOR UNIX/LINUX // nohup
        $process = new Process(base_path().'/node_modules/phantomjs/bin/phantomjs --config='.base_path().'/miner/config.json --ssl-client-certificate-file='.$cert_key.' --ssl-client-key-file='.$cert_pem.' --ssl-client-key-passphrase='.$cert_user.'  '.base_path().'/miner/offer_status.js '.$r['ip'].' '.$r['participant'].' '.$cert_user.':'.$cert_pass.' '.$r['bid_id'].' '.$r['resource'].' '.$r['server_url'].' &> '.$path.'offers/logs/'.$r['participant'].'_'.$r['bid_id'].'.log');

        $process->setTimeout(3*60);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        file_put_contents($path.'offers/logs/'.$r['participant'].'_'.$r['bid_id'].'.log',$process->getOutput());
        // $handle = shell_exec();
        return true;
    }
}