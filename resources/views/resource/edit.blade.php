@extends('layouts.app')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-2">
				@include('participant.menu')
			</div>
			<div class="col-md-10">
				{!! Breadcrumbs::render('resource.edit') !!}
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
				{{ Form::model($resource, ['method' => 'PATCH','route' => ['resources.update', $resource->id], 'class'=>'form-horizontal']) }}
				<legend>Add Resource</legend>
				<div class="form-group"> 
					{{ Form::label('plant_id', 'Plant:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10"> 
						{{ Form::select('plant_id', $plants, $resourcePlant, ['class'=>'form-control input-sm']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('resource_id', 'Resource ID:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10"> 
						{{ Form::text('resource_id', $resource->resource_id, ['class'=>'form-control input-sm', 'placeholder'=>'Resource ID', 'required'=>'required']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('region', 'Region:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10"> 
						{{ Form::select('region', ['luzon'=>'Luzon','visayas'=>'Visayas'], $resource->region, ['class'=>'form-control input-sm']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('pmin', 'Pmin:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-2"> 
						{{ Form::text('pmin', $resource->pmin, ['class'=>'form-control input-sm', 'placeholder'=>'Pmin']) }}
					</div>
					{{ Form::label('pmax', 'Pmax:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-2"> 
						{{ Form::text('pmax', $resource->pmax, ['class'=>'form-control input-sm', 'placeholder'=>'Pmax']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('ramp_rate', 'Ramp Rate:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-2"> 
						{{ Form::text('ramp_rate', $resource->ramp_rate, ['class'=>'form-control input-sm', 'placeholder'=>'Ramp Rate']) }}
					</div>
					{{ Form::label('ramp_up', 'Ramp&nbsp;Up:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-2"> 
						{{ Form::text('ramp_up', $resource->ramp_up, ['class'=>'form-control input-sm', 'placeholder'=>'Ramp Up']) }}
					</div>
					{{ Form::label('ramp_down', 'Ramp&nbsp;Down:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-2"> 
						{{ Form::text('ramp_down', $resource->ramp_down, ['class'=>'form-control input-sm', 'placeholder'=>'Ramp Down']) }}
					</div>
				</div>

				<div class="form-group"> 
					{{ Form::label('unit_no', 'Unit Number:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-2"> 
						<select name="unit_no" id="unit_no" class="form-control input-sm">
							@for ($i = 1; $i <= 10; $i++)
								@if ($resource->unit_no === $i)
									<option selected="true" value="{{ $i }}">{{ $i }}</option>
    							@else
    								<option value="{{ $i }}">{{ $i }}</option>
    							@endif
								
							@endfor

						</select>
					</div>
				</div>

				<div class="form-group">
					<div class="col-lg-10 col-lg-offset-2">
						{{ Form::submit('Update Resource', ['class'=>'btn btn-primary btn-sm']) }}
					</div>
				</div>
				{{ Form::close() }}
				</div>
			</div>
		</div>
	</div>
@stop