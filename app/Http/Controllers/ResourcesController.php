<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Resource;
use App\Plant;
use Yajra\Datatables\Datatables;

class ResourcesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
    	return view('resource.list');
    }
    public function data()
    {
    	$resources = Resource::all();

    	return Datatables::of($resources)
    			->addColumn('action', function($a){
    				return '<a href="/admin/resources/edit/'.$a->id.'"><i class="fa fa-pencil" title="edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
                            <a href="#" class="btnDelete" name="'.$a->resource_id.'" id="'.$a->id.'"><i class="fa fa-times" title="delete"></i></a>';
    			})
    			->make(true);
    }
    public function create()
    {
    	$plants = Plant::pluck('plant_name','id');
    	return view('resource.add', compact('plants'));
    }
    public function store(Request $request, Resource $resource)
    {
    	$this->validate($request, [
            'resource_id' => 'required|unique:resources',
            'unit_no' => 'required|unique_with:resources,plant_id'
        ]);
        $input = $request->all();
        
        $resources = Resource::create($input);

        return redirect()->route('resources.list')->with('success','Resource ID created successfully');
    }
    public function edit($id)
    {
    	$resource = Resource::find($id);
     	$plants = Plant::pluck('plant_name','id');

        $resourcePlant = $resource->plant_id;
     	return view('resource.edit',compact('resource','plants','resourcePlant'));
    }
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'resource_id' => 'required|unique:resources,resource_id,'.$id,
            'unit_no' => 'required|unique_with:resources,plant_id'
        ]);

        $input = $request->all();
		$resource = Resource::find($id);
        $resource->update($input);

        return redirect()->route('resources.list')->with('success','Resource updated successfully');
    }

    public function delete(Request $request)
    {
        $resource = Resource::find($request->id);
        $resource->delete();
    }
    
    public function list_resources_by_plant_id(){
        $resource = Resource::where('plant_id',request(['plant_id']))->get();
        return $resource;
    }
}
