@extends('layouts.app')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-2">
	            <div class="list-group">
	                @include('customer.menu')
	            </div>
	        </div>
	        <div class="col-md-10">
				{!! Breadcrumbs::render('customer.add') !!}
				@if ( count($errors) )
					<div class="alert alert-danger col-md-7">
						<ul>
						@foreach ($errors->all() as $error)
							<li>{{$error}}</li>
						@endforeach
						</ul>
					</div>
				@endif
				<div class="well bs-component col-md-7"> 
				{{ Form::model($customer, ['method' => 'PATCH','route' => ['customers.update', $customer->id], 'class'=>'form-horizontal']) }}
					<legend>Edit Customer</legend>
				<div class="form-group"> 
					{{ Form::label('customer_name', 'Customer Name:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10"> 
						{{ Form::text('customer_name', $customer->customer_name, ['class'=>'form-control input-sm', 'placeholder'=>'Customer Name', 'required'=>'required']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('customer_full_name', 'Customer Full Name:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10"> 
						{{ Form::text('customer_full_name', $customer->customer_full_name, ['class'=>'form-control input-sm', 'placeholder'=>'Customer Full Name', 'required'=>'required']) }}
					</div>
				</div>
				<div class="form-group">
					{{ Form::label('customer_type_id', 'Customer Type:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10">
						{{ Form::select('customer_type_id', array_map('strtoupper',$customer_types), $customer->customer_type_id, ['class'=>'form-control input-sm']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('user[]', 'Users:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10"> 
						{{ Form::select('users[]', $users, $user_customer, ['class'=>'form-control input-sm', 'multiple'=>true, 'size'=>10]) }}
					</div>
				</div>
				<div class="form-group">
					{{ Form::label('participants', 'Participant:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10">
						{{ Form::select('participants[]', array_map('strtoupper', $participants), $customer_participants, ['class'=>'form-control input-sm', 'multiple'=>true, 'size'=>10]) }}
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-10 col-lg-offset-2">
						{{ Form::submit('Update Customer', ['class'=>'btn btn-primary btn-sm']) }}
					</div>
				</div>
				{{ Form::close() }}
				</div>
			</div>
        </div>
	</div>
@endsection