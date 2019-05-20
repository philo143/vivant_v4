<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CustomerType;
use App\User;
use App\UserCustomer;
use App\Participant;
use App\Customer;
use App\CustomerParticipant;
use Yajra\Datatables\Datatables;

class CustomersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index() 
    {
        // $customers = Customer::with('customer_type','customer_participant.participants','sein','user_customer.user')->get();
        // dd($customers);
    	return view('customer.list');
    }
    public function data()
    {
        $customers = Customer::with('customer_type','customer_participant.participants','sein','user_customer.user');
        
        return Datatables::of($customers)
                ->addColumn('action', function($a){
                    return '<a href="/admin/customers/edit/'.$a->id.'"><i class="fa fa-pencil" title="edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
                            <a href="#" class="btnDelete" name="'.$a->customer_name.'" id="'.$a->id.'"><i class="fa fa-times" title="delete"></i></a>';
                })
                ->addColumn('sein', function(Customer $customer){
                    return $customer->sein->map(function ($sein) {
                        return $sein->sein;
                    });
                })
                ->addColumn('participants', function(Customer $customer){
                    return $customer->customer_participant->map(function ($participant) {
                        return $participant->participants->participant_name;
                    });
                })
                ->addColumn('users', function(Customer $customer){
                    return $customer->user_customer->map(function ($customer_user) {
                        return $customer_user->user->username;
                    });
                })

                ->make(true);
    }

    public function create()
    {
        $customer_types = CustomerType::pluck('customer_type','id')->toArray();
        $participants = Participant::pluck('participant_name','id')->toArray();
        $users = User::orderBy('updated_at', 'DESC')->pluck('username','id');
    	return view('customer.add', compact('participants','customer_types','users'));
    }
    public function store(Request $request, Customer $customer)
    {
    	$this->validate($request, [
            'customer_name' => 'max:15,required|unique:customers',
            'customer_full_name' => 'max:50,required',
            'participants' => 'required'
        ]);

        $input = $request->all();
        $customer = Customer::create($input);

        $this->add_customer_participant($customer->id, $request->get('participants'));

        $this->add_user_customer($customer->id, $request->get('users'));
        
        return redirect()->route('customers.list')->with('success','Customer created successfully');
    }
    private function add_user_customer($customers_id, $users)
    {
        $user_customer_array = array();
        foreach ($users as $user) {
            $user_customer_array[] = array(
                'users_id' => $user,
                'customers_id' => $customers_id
            );
        }
        UserCustomer::insert($user_customer_array);
    }
    private function add_customer_participant($customers_id, $participant_array)
    {
        $customer_participant_array = array();
        foreach ($participant_array as $participant) {
            $customer_participant_array[] = array(
                'participants_id' => $participant,
                'customers_id' => $customers_id
            );
        }
        CustomerParticipant::insert($customer_participant_array);
    }
    public function edit($id)
    {
        $customer = Customer::find($id);
        $participants = Participant::pluck('participant_name','id')->toArray();
        $customer_types = CustomerType::pluck('customer_type','id')->toArray();
        $customer_participants = CustomerParticipant::where('customers_id',$customer->id)->pluck('participants_id')->toArray();
        $users = User::orderBy('updated_at','DESC')->pluck('username','id')->toArray();
        $user_customer = UserCustomer::where('customers_id',$customer->id)->pluck('users_id')->toArray();

        return view('customer.edit',compact('customer','participants','customer_types','customer_participants','users','user_customer'));
    }
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'customer_name' => 'max:15,required|unique:customers,customer_name,'.$id,
            'customer_full_name' => 'max:50,required,'.$id,
            'participants' => 'required'
        ]); 

        $input = $request->all();

        $customer = Customer::find($id);
        $customer->update($input);
        CustomerParticipant::where('customers_id', $customer->id)->delete();
        $this->add_customer_participant($customer->id, $request->get('participants'));
        UserCustomer::where('customers_id', $customer->id)->delete();
        $this->add_user_customer($customer->id, $request->get('users'));

        return redirect()->route('customers.list')->with('success','Customer updated successfully');
    }

    public function delete(Request $request)
    {
        CustomerParticipant::where('customers_id', $request->id)->delete();
        UserCustomer::where('customers_id', $request->id)->delete();
        Customer::find($request->id)->delete();
    }
}
