@extends('layouts.app')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-2">
				@include('participant.menu')
			</div>
			<div class="col-md-10">
				{!! Breadcrumbs::render('resource.add') !!}
            	@if ( count($errors) )
					<div class="alert alert-danger col-md-7">
						<ul>
						@foreach ($errors->all() as $error)
							<li>{{$error}}</li>
						@endforeach
						</ul>
					</div>
				@endif
				<div class="well bs-component col-md-9"> 
				{{ Form::open(['route' => 'resources.store', 'method'=>'post', 'class'=>'form-horizontal']) }}
				<legend>Add Resource</legend>
				<div class="form-group"> 
					{{ Form::label('plant_id', 'Plant:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-3"> 
						{{ Form::select('plant_id', $plants, '', ['class'=>'form-control input-sm']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('resource_id', 'Resource ID:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-3"> 
						{{ Form::text('resource_id', '', ['class'=>'form-control input-sm', 'placeholder'=>'Resource ID', 'required'=>'required']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('region', 'Region:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-3"> 
						{{ Form::select('region', ['luzon'=>'Luzon','visayas'=>'Visayas'], '', ['class'=>'form-control input-sm']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('pmin', 'Pmin:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-2"> 
						{{ Form::text('pmin', 0, ['class'=>'form-control input-sm', 'placeholder'=>'Pmin']) }}
					</div>
					{{ Form::label('pmax', 'Pmax:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-2"> 
						{{ Form::text('pmax', 0, ['class'=>'form-control input-sm', 'placeholder'=>'Pmax']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('ramp_rate', 'Ramp Rate:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-2"> 
						{{ Form::text('ramp_rate', 0, ['class'=>'form-control input-sm', 'placeholder'=>'Ramp Rate']) }}
					</div>
					{{ Form::label('ramp_up', 'Ramp&nbsp;Up:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-2"> 
						{{ Form::text('ramp_up', 0, ['class'=>'form-control input-sm', 'placeholder'=>'Ramp Up']) }}
					</div>
					{{ Form::label('ramp_down', 'Ramp&nbsp;Down:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-2"> 
						{{ Form::text('ramp_down', 0, ['class'=>'form-control input-sm', 'placeholder'=>'Ramp Down']) }}
					</div>
				</div>

				<div class="form-group"> 
					{{ Form::label('unit_no', 'Unit Number:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-2"> 
						<select name="unit_no" id="unit_no" class="form-control input-sm">
							@for ($i = 1; $i <= 10; $i++)
								<option value="{{ $i }}">{{ $i }}</option>
							@endfor

						</select>
					</div>
				</div>



				<div class="form-group">
					<div class="col-lg-10 col-lg-offset-2">
						{{ Form::submit('Add Resource', ['class'=>'btn btn-primary btn-sm']) }}
					</div>
				</div>
				{{ Form::close() }}
				</div>
			</div>
		</div>
	</div>
@stop