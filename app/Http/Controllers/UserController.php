<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;
use App\Plant;
use App\Resource;
use App\UserPlant;
use App\UserResource;
use DB;
use Hash;
use Yajra\Datatables\Datatables;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
    	return view('user.list');
    }

    public function usersData(Datatables $datatables)
    {
    	$users = User::with('roles')->select(array('id','username','fullname','email','mobile','status','last_login'));

        return Datatables::of($users)
                ->addColumn('action', function($a){
                    return '<a href="/admin/users/edit/'.$a->id.'"><i class="fa fa-pencil" title="edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
                            <a href="#" class="btnDelete" name="'.$a->username.'" id="'.$a->id.'"><i class="fa fa-times" title="delete"></i></a>';
                })
                ->addColumn('role', function(User $user){
                    return $user->roles->map(function ($roles) {
                        return $roles->display_name;
                    });
                })
                ->editColumn('status','@if($status)
                                            <label class="label label-success">Active</label>
                                       @else
                                            <label class="label label-danger">Inactive</label>
                                       @endif')
                ->make(true);
    }
    public function create()
    {
        $roles = Role::pluck('display_name','id');
        $plants = Plant::pluck('plant_name','id');
    	return view('user.add', compact('roles','plants'));
    }
    public function store(Request $request, User $user)
    {   
        $this->validate($request, [
            'username' => 'required|unique:users',
            'fullname' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|same:confirm-password'
        ]);

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $user = User::create($input);
        $user->attachRole($input['role']);

        $priv = Role::find($request->get('role'));
        if($priv->has_plant == 1){
            $users_id = $user->id;
            $plants_id = $request->get('plant');
            $resources_id = $request->get('resource');
            UserPlant::insert(compact('users_id', 'plants_id'));
            UserResource::insert(compact('users_id', 'resources_id'));
        }
        return redirect()->route('users.list')->with('success','User created successfully');
    }
    public function edit($id)
    {
        $user = User::find($id);
        $roles = Role::pluck('display_name','id');
        $userRole = $user->roles->pluck('id','id')->toArray();
        $plants = Plant::pluck('plant_name','id');
        $userPlant = null;
        $resources = array();
        $userResource = null;
        if($user->user_plant){
            $userPlant = $user->user_plant;     
            $resources = Resource::where('plant_id',$userPlant->plants_id)->pluck('resource_id','id')->toArray();
            $userResource = $user->user_resource;    
        }
           
        return view('user.edit',compact('user','roles','userRole','plants','userPlant','resources','userResource'));
    }
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'username' => 'required|unique:users,username,'.$id,
            'fullname' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'min:8|same:confirm-password'
        ]);

        $input = $request->all();
        if(!empty($input['password'])){ 
            $input['password'] = Hash::make($input['password']);
        }else{
            $input = array_except($input,array('password'));    
        }

        $user = User::find($id);
        $user->update($input);
        DB::table('role_user')->where('user_id',$id)->delete();
        $user->attachRole($input['role']);        

        $priv = Role::find($request->get('role'));
        if($priv->has_plant == 1){
            $users_id = $user->id;
            $plants_id = $request->get('plant');
            $resources_id = $request->get('resource');
            UserPlant::where('users_id',$users_id)->delete();
            UserResource::where('users_id',$users_id)->delete();
            UserPlant::insert(compact('users_id', 'plants_id'));            
            UserResource::insert(compact('users_id', 'resources_id'));
        }else{
            $users_id = $user->id;
            $plants_id = $request->get('plant');
            $resources_id = $request->get('resource');
            UserPlant::where('users_id',$users_id)->delete();
            UserResource::where('users_id',$users_id)->delete();
        }
        return redirect()->route('users.list')->with('success','User updated successfully');
    }

    public function delete(Request $request)
    {
        $user = User::find($request->id);
        $user->delete();
    }

    public function change_password ()
    {
        return view('settings.change_password');
    }
}
