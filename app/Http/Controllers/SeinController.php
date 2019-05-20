<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Sein;
use App\Customer;
use App\Resource;
use Yajra\Datatables\Datatables;

class SeinController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function r_index()
    {
    	return view('sein.r_list');	
    }
    public function r_data()
    {
        $sein = Sein::with('resource')->where('type','GEN');
        
        return Datatables::of($sein)
                ->addColumn('action', function($a){
                    return '<a href="/admin/sein/r_edit/'.$a->id.'"><i class="fa fa-pencil" title="edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
                            <a href="#" class="btnDelete" name="'.$a->sein.'" id="'.$a->id.'"><i class="fa fa-times" title="delete"></i></a>';
                })
                ->make(true);
    }
    public function r_create()
    {
        $resources = Resource::pluck('resource_id','id')->toArray();
    	return view('sein.r_add', compact('resources'));
    }

    public function r_store(Request $request)
    {
    	$this->validate($request, [
            'sein' => 'max:45,required|unique:sein'
        ]);

        $input = $request->all();
        $sein = Sein::create($input);
        $sein->type = 'GEN';
        $sein->save();
        
        return redirect()->route('resource_sein.list')->with('success','SEIN created successfully');
    }

    public function r_edit($id)
    {
    	$sein = Sein::find($id);
    	$resources = Resource::pluck('resource_id','id')->toArray();

    	return view('sein.r_edit', compact('sein','resources'));
    }

    public function c_index() 
    {
    	return view('sein.c_list');
    }

    public function c_data()
    {
        $sein = Sein::with('customer')->where('type','LD');
        
        return Datatables::of($sein)
                ->addColumn('action', function($a){
                    return '<a href="/admin/sein/c_edit/'.$a->id.'"><i class="fa fa-pencil" title="edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
                            <a href="#" class="btnDelete" name="'.$a->sein.'" id="'.$a->id.'"><i class="fa fa-times" title="delete"></i></a>';
                })
                ->make(true);
    }

    public function c_create()
    {
        $customers = Customer::pluck('customer_name','id')->toArray();
    	return view('sein.c_add', compact('customers'));
    }

    public function c_store(Request $request)
    {
    	$this->validate($request, [
            'sein' => 'max:45,required|unique:sein'
        ]);

        $input = $request->all();
        $sein = Sein::create($input);
        $sein->type = 'LD';
        $sein->save();
        
        return redirect()->route('customer_sein.list')->with('success','SEIN created successfully');
    }

    public function c_edit($id)
    {
    	$sein = Sein::find($id);
    	$customers = Customer::pluck('customer_name','id')->toArray();

    	return view('sein.c_edit', compact('sein','customers'));
    }

    public function update(Request $request, $id)
    {
    	$this->validate($request, [
            'sein' => 'max:45,required|unique:sein,sein,'.$id
        ]);

        $input = $request->all();

        $sein = Sein::find($id);
        $sein->update($input);

        if ($sein->type == 'LD') {
        	return redirect()->route('customer_sein.list')->with('success','Sein updated successfully');
        } else {
        	return redirect()->route('resource_sein.list')->with('success','Sein updated successfully');
        }
    }

    public function delete(Request $request)
    {
    	$sein = Sein::find($request->id)->delete();
    }
}
