<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\User;
use Hash;

class ChangePasswordController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function form()
    {
        return view('settings.change_password');
    }

    public function change(Request $request)
    {
        $this->validate($request, [
            'current_password' => 'required',
            'new_password' => 'required|min:4',
            'confirm_password' => 'required|same:new_password'
        ]);

        $data = $request->all();

        $user = User::find(auth()->user()->id);
        
        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->with('error','You current password is incorrect.');
        } else {
            $user->password = Hash::make($data['new_password']);
            $user->save();
            return redirect()->route('password.form')->with('success','Password Successfully Changed.');
        }
    }

}
