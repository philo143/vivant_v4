@extends('layouts.app')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-2">
				@include('system.menu')
			</div>
			<div class="col-md-10">
				{!! Breadcrumbs::render('resource_lookup.add') !!}
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
				{{ Form::open(['route' => 'resource_lookup.admin.store', 'method'=>'post', 'class'=>'form-horizontal']) }}
				<legend>Add Resource</legend>
				
				<div class="form-group"> 
					{{ Form::label('resource_id', 'Resource ID:', ['class'=>'col-lg-3 control-label']) }}
					<div class="col-lg-8"> 
						{{ Form::text('resource_id', '', ['class'=>'form-control input-sm', 'placeholder'=>'Resource ID', 'required'=>'required']) }}
					</div>
				</div>

				<div class="form-group"> 
					{{ Form::label('region', 'Region :', ['class'=>'col-lg-3 control-label']) }}
					<div class="col-lg-4"> 
						<select class="form-control input-sm" name="region" id="region">
							<option value="LUZON">LUZON</option>
							<option value="VISAYAS">VISAYAS</option>
							<option value="MINDANAO">MINDANAO</option>
						</select>
					</div>
				</div>

				<div class="form-group"> 
					{{ Form::label('type', 'Type :', ['class'=>'col-lg-3 control-label']) }}
					<div class="col-lg-2"> 
						<select class="form-control input-sm" name="type" id="type">
							<option value="GEN">GEN</option>
							<option value="LD">LOAD</option>
						</select>
					</div>
				</div>

				<div class="form-group"> 
					{{ Form::label('is_mms_reserve', 'Is MMS Reserve :', ['class'=>'col-lg-3 control-label']) }}
					<div class="col-lg-2"> 
						<select class="form-control input-sm" name="is_mms_reserve" id="is_mms_reserve">
							<option value="1">Yes</option>
							<option value="0">No</option>
						</select>
					</div>
				</div>

				<div class="form-group"> 
					{{ Form::label('reserve_types_display', 'Reserve Type:', ['class'=>'col-lg-3 control-label']) }}
					<div class="col-lg-7"> 
						{{ Form::select('reserve_types_display', $reserve_types, [], ['class'=>'form-control input-sm', 'multiple'=>true, 'size'=>15]) }}


						<input type="hidden" name="reserve_classes" id="reserve_classes" value="">
					</div>
				</div>

				

				<div class="form-group">
					<div class="col-lg-8 col-lg-offset-3">
						{{ Form::submit('Add Resource', ['class'=>'btn btn-primary btn-sm']) }}
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
    $(document).ready(function(){
    	$('#is_mms_reserve').unbind().bind('change',function(){
    		$("#reserve_types_display").val([]);
    		$('#reserve_classes').val('');
    		var is_mms_reserve = parseInt( $('#is_mms_reserve').val(), 10 ); 
    		if ( is_mms_reserve == 1 ) {
    			$('#reserve_types_display').prop('disabled',false);
    		}else {
    			$('#reserve_types_display').prop('disabled',true);
    		}
    	})
        
        $('#reserve_types_display').unbind().bind('change',function(){
        	$('#reserve_classes').val($("#reserve_types_display").val().join(','));
        });

    });
    
</script>
@endsection
