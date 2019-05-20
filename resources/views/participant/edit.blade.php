@extends('layouts.app')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-2">
				@include('participant.menu')
			</div>
			<div class="col-md-10">
				{!! Breadcrumbs::render('participant.edit') !!}
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
				{{ Form::model($participant, ['method' => 'PATCH','route' => ['participants.update', $participant->id], 'class'=>'form-horizontal', 'files'=>'true']) }}
				<legend>Edit Participant</legend>
				<div class="form-group"> 
					{{ Form::label('participant_name', 'Participant Name:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10"> 
						{{ Form::text('participant_name', $participant->participant_name, ['class'=>'form-control input-sm', 'placeholder'=>'Participant Name', 'required'=>'required']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('description', 'Description:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10"> 
						{{ Form::textarea('description', $participant->description, ['class'=>'form-control input-sm', 'placeholder'=>'Description']) }}
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-2"></div>
					<div class="col-lg-10">Market Participant User Access Information (for Web Login)</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('cert_user', 'Cert Username:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-4"> 
						{{ Form::text('cert_user', $participant->cert_user, ['class'=>'form-control input-sm', 'placeholder'=>'Cert Username']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('cert_pass', 'Cert Password:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-4"> 
						{{ Form::password('cert_pass', ['class'=>'form-control input-sm', 'placeholder'=>'Cert Password']) }}
					</div>
					<div class="col-lg-4"><p class="small"><br>(Example : XXxXXXxX)</p></div>
				</div>
				<div class="form-group">
					<div class="col-lg-2"></div>
					<div class="col-lg-10">Digital Certificate (DC) Information</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('dc_pass', 'DC Password:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-4"> 
						{{ Form::password('dc_pass', ['class'=>'form-control input-sm', 'placeholder'=>'DC Password']) }}
					</div>
					<div class="col-lg-4"><p class="small"><br>(Example : xxx_01)</p></div>
				</div>
				<div class="form-group"> 
					{{ Form::label('cert_file', 'Upload Certificate:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-4"> 
						{{ Form::file('cert_file', ['class'=>'form-control input-sm file']) }}
					</div>
					<div class="col-lg-4"><p class="small"><br>(.pfx file)</p></div>
				</div>
				<div class="form-group"> 
					{{ Form::label('status', 'Status :', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-4"> 
						<select class="form-control input-sm" name="status" id="status">
							<option value="active" {{ $participant->status == 'active' ? "selected=true" : "" }}>Active</option>
							<option value="inactive" {{ $participant->status == 'inactive' ? "selected=true" : "" }}>Inactive</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-10 col-lg-offset-2">
						{{ Form::submit('Update Participant', ['class'=>'btn btn-primary btn-sm']) }}
					</div>
				</div>
				{{ Form::close() }}
				</div>
				{{-- </div> --}}
			</div>
		</div>
	</div>
@stop