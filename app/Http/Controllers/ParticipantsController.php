<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Participant;
use Yajra\Datatables\Datatables;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ParticipantsController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
    	return view('participant.list');
    }
    public function data()
    {
    	$participants = Participant::with('plant');

    	return Datatables::of($participants)
    			->addColumn('action', function($a){
    				return '<a href="/admin/participant/edit/'.$a->id.'"><i class="fa fa-pencil" title="edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
                            <a href="#" class="btnDelete" name="'.$a->participant_name.'" id="'.$a->id.'"><i class="fa fa-times" title="delete"></i></a>';
    			})
    			->addColumn('plants', function(Participant $participant){
                    return $participant->plant->map(function ($plant) {
                        return $plant->plant_name;
                    });
                })
    			->edit_column('cert_file', '@if($cert_file)
    									     <label class="label label-success">Installed</label>
                                       @else
                                            <label class="label label-danger">No Certificate</label>
                                       @endif')
                ->edit_column('status', '@if($status == "active")
                                             <label class="label label-success">Active</label>
                                       @else
                                            <label class="label label-danger">Inactive</label>
                                       @endif')
    			->make(true);
    }
    public function create()
    {
    	return view('participant.add');
    }
    public function store(Request $request, Participant $participant)
    {
    	$this->validate($request, [
            'participant_name' => 'required|unique:participants'
        ]);
        
        $input = $request->all();
        $input['cert_file'] = null;
        $input['cert_loc'] = null;
        if(count($request->files) > 0){
            if($request->file('cert_file')->getClientMimeType() != "application/x-pkcs12") {
                return redirect()->back()->withInput()->withErrors(['participant.list' => 'The cert file must be a file of type: pfx.']);
            }
            $file = $request->file('cert_file');
            $filename = date('YmdHis').'_'.$file->getClientOriginalName();
            $request->file('cert_file')->move(base_path().'/storage/certs/cert_tmp/', $filename);
            $input['cert_file'] = $input['cert_file'] = str_replace('.pfx', '', $filename);
            $input['cert_loc'] = '/storage/certs/cert_live';
        }
        
        $participant = Participant::create($input);
        $process = new Process('touch '.base_path().'/storage/certs/cert_tmp/passtxt');
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
            return redirect()->back()->withInput()->withErrors(['participant.list' => $process->getOutput()]);
        }else{
            file_put_contents(base_path().'/storage/certs/cert_tmp/passtxt', trim($_POST['dc_pass']));
            $this->convert_cert(base_path()."/storage/certs/cert_tmp/".($input['cert_file']));
            return redirect()->route('participants.list')->with('success','Participant created successfully');
        }              
        
    }
    public function edit($id)
    {
    	$participant = Participant::find($id);
        return view('participant.edit',compact('participant'));
    }
    public function update(Request $request, $id)
    {
    	$this->validate($request, [
            'participant_name' => 'required|unique:participants,participant_name,'.$id
        ]);

        $input = $request->all();
        if(!empty($input['cert_pass'])){ 
            $input['cert_pass'] = $input['cert_pass'];
        }else{
            $input = array_except($input,array('cert_pass'));    
        }

        if(count($request->files) > 0){
            if($request->file('cert_file')->getClientMimeType() != "application/x-pkcs12") {
                return redirect()->back()->withInput()->withErrors(['participant.list' => 'The cert file must be a file of type: pfx.']);
            }
            $file = $request->file('cert_file');
            $filename = date('YmdHis').'_'.$file->getClientOriginalName();
            $request->file('cert_file')->move(base_path().'/storage/certs/cert_tmp/', $filename);
            $input['cert_file'] = str_replace('.pfx', '', $filename);
            $input['cert_loc'] = '/storage/certs/cert_live';
            $process = new Process('touch '.base_path().'/storage/certs/cert_tmp/passtxt');
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
                return redirect()->back()->withInput()->withErrors(['participant.list' => $process->getOutput()]);
            }else{
                file_put_contents(base_path().'/storage/certs/cert_tmp/passtxt', trim($_POST['dc_pass']));
                $this->convert_cert(base_path()."/storage/certs/cert_tmp/".($input['cert_file']));
            }
        }

        $participant = Participant::find($id);
        $participant->update($input);
           
        return redirect()->route('participants.list')->with('success','Participant updated successfully');        

    }
    public function delete(Request $request)
    {
    	$participant = Participant::find($request->id);
        $participant->delete();
    }
    private function convert_cert(){
        $tmp_dir    = base_path().'/storage/certs/cert_tmp/';
        $live_dir   = base_path().'/storage/certs/cert_live/';
        $backup_dir = base_path().'/storage/certs/cert_backup/';

        if ($h = opendir($tmp_dir)) {
            while (false !== ($file = readdir($h))) {
                if ($file != "." && $file != ".." && $file != 'passtxt') {
                    $filename = explode('.',$file);
                    if ($filename[1] === 'pfx') {
                        copy($tmp_dir.$file,$backup_dir.$file);
                        exec('openssl pkcs12 -in '.$tmp_dir.$file.' -out '.$live_dir.$filename[0].'.pem -nodes -passin file:'.$tmp_dir.'passtxt');
                        exec('openssl pkcs12 -in '.$tmp_dir.$file.' -clcerts -nokeys -out '.$live_dir.$filename[0].'.crt -passin file:'.$tmp_dir.'passtxt');
                        exec('chmod 777 '.$live_dir.$filename[0].'.key');
                        exec('chmod 777 '.$live_dir.$filename[0].'.pem');
                        exec('rm '.$tmp_dir.'passtxt');
                        exec('rm '.$tmp_dir.$file);
                    }
                }
            }
            
            closedir($h);
        }
    }
}
