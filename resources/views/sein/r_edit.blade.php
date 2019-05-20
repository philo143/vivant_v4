@extends('layouts.app')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-2">
	            <div class="list-group">
	                @include('participant.menu')
	            </div>
	        </div>
	        <div class="col-md-10">
				{!! Breadcrumbs::render('resource_sein.edit') !!}
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
				{{ Form::model($sein, ['method' => 'PATCH','route' => ['sein.update', $sein->id], 'class'=>'form-horizontal']) }}
					<legend>Edit SEIN</legend>
				<div class="form-group"> 
					{{ Form::label('sein', 'Sein:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10"> 
						{{ Form::text('sein', $sein->sein, ['class'=>'form-control input-sm', 'placeholder'=>'Sein', 'required'=>'required']) }}
					</div>
				</div>
				<div class="form-group">
					{{ Form::label('resources_id', 'Resource ID:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10">
						{{ Form::select('resources_id', $resources, $sein->resources_id, ['class'=>'form-control input-sm']) }}
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-10 col-lg-offset-2">
						{{ Form::submit('Update SEIN', ['class'=>'btn btn-primary btn-sm']) }}
					</div>
				</div>
				{{ Form::close() }}
				</div>
			</div>
        </div>
	</div>
@endsection