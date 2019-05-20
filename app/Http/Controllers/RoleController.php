<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Role;
use App\Permission;
use DB;
use Yajra\Datatables\Datatables;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
    	return view('role.list');
    }
    public function privData(Datatables $datatables)
    {
		$roles = Role::with('permissions')->select(array('id','name','display_name','description'));

        return Datatables::of($roles)
                ->addColumn('action', function($a){
                    return '<a href="/admin/privilege/edit/'.$a->id.'"><i class="fa fa-pencil" title="edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
                            <a href="#" class="btnDelete" name="'.$a->name.'" id="'.$a->id.'"><i class="fa fa-times" title="delete"></i></a>';
                })
                ->addColumn('permission', function(Role $role){
                	return $role->permissions->map(function ($p){
                		return $p->name;
                		
                	});
                })
                ->make(true);
    }
    public function create()
    {
    	$permission = Permission::pluck('display_name','id');
        return view('role.add',compact('permission'));
    }
    public function store(Request $request, Role $role)
    {
    	$this->validate($request, [
            'name' => 'required|unique:roles',
            'display_name' => 'required|unique:roles',
            'permission' => 'required'
        ]);

        $role = new Role();
        $role->name = $request->input('name');
        $role->display_name = $request->input('display_name');
        $role->description = $request->input('description');
        $role->has_plant = $request->input('has_plant');
        $role->save();
        
        foreach ($request->input('permission') as $key => $value) {
            $role->attachPermission($value);
        }

        return redirect()->route('priv.list')->with('success','Privilege created successfully');
    }
    public function edit($id)
    {
    	$role = Role::find($id);
    	$permission = Permission::pluck('display_name','id');
    	$rolePermissions = DB::table("permission_role")->where("permission_role.role_id",$id)
            ->pluck('permission_role.permission_id','permission_role.permission_id')->toArray();

        return view('role.edit',compact('role','permission','rolePermissions'));
    }
    public function update(Request $request, $id)
    {
    	$this->validate($request, [
            'display_name' => 'required',
            'description' => 'required',
            'permission' => 'required',
        ]);

        $role = Role::find($id);
        $role->display_name = $request->input('display_name');
        $role->description = $request->input('description');
        $role->save();

        DB::table("permission_role")->where("permission_role.role_id",$id)
            ->delete();

        foreach ($request->input('permission') as $key => $value) {
            $role->attachPermission($value);
        }

        return redirect()->route('priv.list')
                ->with('success','Role updated successfully');
    }
    public function delete(Request $request)
    {
    	$role = Role::find($request->id);
        $role->delete();
    }
    public function getPrivPlant() 
    {
        $role = Role::find(request('id'));
        return $role->has_plant;
    }
}
