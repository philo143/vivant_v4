@extends('layouts.app')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-2">
				@include('system.menu')
			</div>
			<div class="col-md-10">
				{!! Breadcrumbs::render('ip_tables.add') !!}
            	@if ( count($errors) )
					<div class="alert alert-danger col-md-7">
						<ul>
						@foreach ($errors->all() as $error)
							<li>{{$error}}</li>
						@endforeach
						</ul>
					</div>
				@endif
				<div class="well bs-component col-md-12"> 
				{{ Form::open(['route' => 'ip_tables.save', 'method'=>'post', 'class'=>'form-horizontal']) }}
				<legend>Add New IP Address</legend>
				<div class="form-group"> 
					{{ Form::label('type', 'Type :', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-2"> 
						<select class="form-control input-sm" name="type" id="type">
							<option value="mms">MMS</option>
							<option value="wesm">WESM</option>
							<option value="ngcp">NGCP</option>
						</select>
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('ip_address', 'IP Address :', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-2"> 
						{{ Form::text('ip_address', '', ['class'=>'form-control input-sm', 'placeholder'=>'0.0.0.0', 'required'=>'required']) }}
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-2 col-lg-offset-2">
						{{ Form::submit('Add IP Address', ['class'=>'btn btn-primary btn-sm']) }}
					</div>
				</div>
				{{ Form::close() }}
				</div>
			</div>
		</div>
	</div>
@stop

@section('scripts')
<script>
    
</script>
@endsection
