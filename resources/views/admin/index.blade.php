@extends('layouts.app')

@section('content')
    <div class="container-fluid">
    <div class="row">
        <div class="col-md-2">
            <h5><strong>Admin Tools</strong></h5>
            <hr>
            <div class="list-group">
                <a href="{{ route('users.list') }}" class="list-group-item">Users</a>
                <a href="{{ route('participants.list') }}" class="list-group-item">Participants</a>
                <a href="{{ route('customers.list') }}" class="list-group-item">Customers</a>
                <a href="#" class="list-group-item">Contracts</a>
                <a href="#" class="list-group-item">Fuel Types</a>
                <a href="{{ route('dashboard.manage') }}" class="list-group-item">Dashboard</a>
                <a href="#" class="list-group-item">Settings</a>
            </ul>
            </div>
        </div>
        <div class="col-md-10">
            <h4>Latest Application Activities</h4>
            <hr>
        </div>
    </div>
    </div>

    
  
  

	<div class="columns"></div>
@endsection

