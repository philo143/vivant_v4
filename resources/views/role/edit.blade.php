@extends('layouts.app')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-2">
				@include('user.menu')
			</div>
			<div class="col-md-10">
				{!! Breadcrumbs::render('priv.edit') !!}
            	@if ( count($errors) )
					<div class="alert alert-danger col-md-7">
						<ul>
						@foreach ($errors->all() as $error)
							<li>{{$error}}</li>
						@endforeach
						</ul>
					</div>
				@endif
				{{-- <div class="col-lg-6">  --}}
				<div class="well bs-component col-md-7"> 
				{{ Form::model($role, ['method' => 'PATCH','route' => ['priv.update', $role->id], 'class'=>'form-horizontal']) }}
				<legend>Edit Privilege</legend>
				<div class="form-group"> 
					{{ Form::label('name', 'Name:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10"> 
						{{ Form::text('name', $role->name, ['class'=>'form-control input-sm', 'placeholder'=>'name', 'required'=>'required']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('display_name', 'Display Name:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10"> 
						{{ Form::text('display_name', $role->display_name, ['class'=>'form-control input-sm', 'placeholder'=>'display name', 'required'=>'required']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('description', 'Description:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10"> 
						{{ Form::textarea('description', $role->description, ['class'=>'form-control input-sm', 'placeholder'=>'description']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('permission', 'Permission:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10"> 
						{{ Form::select('permission[]', $permission, $rolePermissions, ['class'=>'form-control input-sm', 'multiple'=>true, 'size'=>15]) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('has_plant', 'Has Plant?', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10"> 
						{{ Form::select('has_plant',['0'=>'NO','1'=>'YES'], $role->has_plant, ['class'=>'form-control input-sm']) }}
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-10 col-lg-offset-2">
						{{ Form::submit('Update Privilege', ['class'=>'btn btn-primary btn-sm']) }}
					</div>
				</div>
				{{ Form::close() }}
				</div>
				{{-- </div> --}}
			</div>
		</div>
	</div>
@stop