<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Plant;
use App\Resource;
use App\Participant;
use App\AspaType;
use Yajra\Datatables\Datatables;

class PlantsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
    	return view('plant.list');
    }
    public function data()
    {
    	$plants = Plant::with('resource');

        return Datatables::of($plants)
                ->addColumn('action', function($a){
                    return '<a href="/admin/plant/edit/'.$a->id.'"><i class="fa fa-pencil" title="edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
                            <a href="#" class="btnDelete" name="'.$a->plant_name.'" id="'.$a->id.'"><i class="fa fa-times" title="delete"></i></a>';
                })
                ->addColumn('resources', function(Plant $plant){
                    return $plant->resource->map(function ($resource) {
                        return $resource->resource_id;
                    });
                })
                ->make(true);
    }
    public function create()
    {
        $participants = Participant::pluck('participant_name','id');
        $aspa_types = AspaType::orderBy('type','asc')->pluck('description','type');
    	return view('plant.add', compact('participants','aspa_types'));
    }
    public function store(Request $request, Plant $plant)
    {
    	$this->validate($request, [
            'plant_name' => 'required|unique:plants',
        ]);
        $input = $request->all();

        // added by akel 6/16/2017
        // check if plant aspa capable is 0, aspa_type should be null
        if (request('is_aspa') == 0 ){
            $input['aspa_type'] = null;
        }

        $plant = Plant::create($input);

        return redirect()->route('plants.list')->with('success','Plant created successfully');
    }
    public function edit($id)
    {
    	$plant = Plant::find($id);
        $participants = Participant::pluck('participant_name','id');
        $plantParticipant = $plant->participant_id;
        $aspa_types = AspaType::orderBy('type','asc')->pluck('description','type');
        return view('plant.edit',compact('plant','participants','plantParticipant','aspa_types'));
    }
    public function update(Request $request, $id)
    {
    	$this->validate($request, [
            'plant_name' => 'required|unique:plants,plant_name,'.$id
        ]);

        $input = $request->all();

        // added by akel 6/16/2017
        // check if plant aspa capable is 0, aspa_type should be null
        if (request('is_aspa') == 0 ){
            $input['aspa_type'] = null;
        }
        
        $plant = Plant::find($id);
        $plant->update($input);

        return redirect()->route('plants.list')->with('success','Plant updated successfully');
    }
    public function delete(Request $request)
    {
    	$plant = Plant::find($request->id);
        $plant->delete();
    }
}
