<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\IpTable;

class IpTablesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    } //


    public function index(){

		$query = IpTable::query();
		$records = $query->orderBy('type','asc')->distinct()->get();

		$data = array();
		foreach ($records as $row) {
              $type = $row->type;
			  $data[$type][] = $row;
        } // end foreach

    	return view('ip_tables.index',compact('data'));


    } //


    public function store(Request $request){

    	$mms = request('mms');
    	$wesm = request('wesm');

    	IpTable::query()->update(['status' => 0]);

    	IpTable::where('id', $mms)
          ->update(['status' => 1]);

        IpTable::where('id', $wesm)
          ->update(['status' => 1]); 


        return redirect()->route('ip_tables.index')->with('success','Save successfully');

    }

    public function create(){
        return view('ip_tables.add');
    }

    public function save(Request $request){

        $type = request('type');
        $ip = request('ip_address');
        $this->validate( request(), [
                'ip_address' => 'required|unique:ip_tables'
        ]);
        $success = IpTable::query()->insert(['type' => $type,'ip_address'=>$ip,'status'=>0]);

        $ret = array();
        if($success){
            $ret['status'] = 'success';
            $ret['message'] = 'New IP Address has been created.';
        }else{
            $ret['status'] = 'error';
            $ret['message'] = 'An error occurred while saving. Please try again.';
        }

        return redirect()->route('ip_tables.index')->with('success','New IP Address has been created.');

    }
}
