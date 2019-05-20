<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ResourcesLookup;
use Yajra\Datatables\Datatables;
use App\ReserveType;

class ResourcesLookupController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth');
    } //

    public function list(Request $request){

    	  $query = ResourcesLookup::query();
    	  $query->select('resource_lookup.resource_id', 'resource_lookup.region', 'resource_lookup.type');

    	  if ($request->has('is_own_resources') ) {
	      	$is_own_resources = request('is_own_resources');
	      	if ($is_own_resources == 1) {
	      		$query = $query->join('resources', 'resources.resource_id', '=', 'resource_lookup.resource_id');
	      	}
	      }


	      if ( $request->has('type') ) {
	        $type = request('type');
	        $query = $query->where('resource_lookup.type', $type);
	      }


	      if ( $request->has('region') ) {
	        $region = request('region');
	        $query = $query->where('resource_lookup.region', $region);
	      }


	      if ( $request->has('reserve_class') ) {
	        $reserve_class = request('reserve_class');
	        $query = $query->whereRaw('FIND_IN_SET(?,resource_lookup.reserve_classes)', [$reserve_class]);
	      }


	      if ( $request->has('is_mms_reserve') ) {
	        $is_mms_reserve = request('is_mms_reserve');
	        $query = $query->where('resource_lookup.is_mms_reserve', $is_mms_reserve);
	      }


	      


	      $reports = $query->orderBy('resource_id','asc')->distinct()->get();

	      return $reports;

    } //


    ### For admin page
    public function resourceLookupAdmin(){
  
    	return view('resource_lookup.list');

    } // eof


    public function data()
    {
    	$resources = ResourcesLookup::query();

        return Datatables::of($resources)
        		->edit_column('is_mms_reserve', '@if($is_mms_reserve == 1)
    									     <label class="label label-success">Reserve</label>
                                       @else
                                            
                                       @endif')
                ->addColumn('action', function($a){
                    return '<a href="/admin/resource_lookup/edit/'.$a->id.'"><i class="fa fa-pencil" title="edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
                            <a href="#" class="btnDelete" name="'.$a->resource_id.'" id="'.$a->id.'"><i class="fa fa-times" title="delete"></i></a>';
                })
                ->make(true);
    }

    public function create()
    {
        $reserve_types = ReserveType::pluck('type','type');
    	return view('resource_lookup.add', compact('reserve_types'));
    }

    public function store(Request $request, ResourcesLookup $resourceLookup)
    {
    	$this->validate($request, [
            'resource_id' => 'required|unique:resource_lookup',
        ]);
        $input = $request->all();
        $rl = ResourcesLookup::create($input);

        return redirect()->route('resource_lookup.admin.list')->with('success','Resource created successfully');
    }

    public function edit($id)
    {
    	$resource_lookup = ResourcesLookup::find($id);
        $reserve_types = ReserveType::pluck('type','type');
        return view('resource_lookup.edit',compact('resource_lookup','reserve_types'));
    }


    public function update(Request $request, $id)
    {
    	$this->validate($request, [
            'resource_id' => 'required'
        ]);

        $input = $request->all();

        $rl = ResourcesLookup::find($id);
        $rl->update($input);

        return redirect()->route('resource_lookup.admin.list')->with('success','Resource updated successfully');
    }

    public function delete(Request $request)
    {
    	$rl = ResourcesLookup::find($request->id);
        $rl->delete();
    }

}
