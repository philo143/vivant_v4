@extends('layouts.app')

@section('content')

<div class="container-fluid">
	<div class="row">
	    <div class="col-md-10 col-md-offset-1">
	    	@include ('user.message')
	    	@if ( count($errors) )
				<div class="alert alert-danger">
					<ul>
					@foreach ($errors->all() as $error)
						<li>{{$error}}</li>
					@endforeach
					</ul>
				</div>
			@endif
	        <div class="panel panel-default">
	            <div class="panel-heading">Change Password</div>
				 
	            <div class="panel-body">
	                {{ Form::open(['route' => 'password.submit', 'class'=>'form-horizontal']) }}
	                	<div class="form-group"> 
						{{ Form::label('current_password', 'Current Password:', ['class'=>'col-lg-2 control-label']) }}
							<div class="col-lg-4"> 
								{{ Form::password('current_password', ['class'=>'form-control input-sm', 'placeholder'=>'old password', 'required'=>'required']) }}
							</div>
						</div>
						<hr>
						<div class="form-group"> 
						{{ Form::label('new_password', 'New Password:', ['class'=>'col-lg-2 control-label']) }}
							<div class="col-lg-4"> 
								{{ Form::password('new_password', ['class'=>'form-control input-sm', 'placeholder'=>'new password', 'required'=>'required']) }}
							</div>
						</div>
						<div class="form-group"> 
						{{ Form::label('confirm_password', 'Confirm Password:', ['class'=>'col-lg-2 control-label']) }}
							<div class="col-lg-4"> 
								{{ Form::password('confirm_password', ['class'=>'form-control input-sm', 'placeholder'=>'confirm password', 'required'=>'required']) }}
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-10 col-lg-offset-2">
								{{ Form::submit('Change Password', ['class'=>'btn btn-primary btn-sm']) }}
							</div>
						</div>
	                {{ Form::close() }}
	            </div>
	        </div>
	    </div>
	</div>
</div>

@stop