@extends('layouts.app')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-2">
				@include('participant.menu')
			</div>
			<div class="col-md-10">
				{!! Breadcrumbs::render('plant.add') !!}
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
				{{ Form::open(['route' => 'plants.store', 'method'=>'post', 'class'=>'form-horizontal']) }}
				<legend>Add Plant</legend>
				<div class="form-group"> 
					{{ Form::label('participant_id', 'Participant:', ['class'=>'col-lg-3 control-label']) }}
					<div class="col-lg-8"> 
						{{ Form::select('participant_id', $participants, '', ['class'=>'form-control input-sm']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('plant_name', 'Plant Name:', ['class'=>'col-lg-3 control-label']) }}
					<div class="col-lg-8"> 
						{{ Form::text('plant_name', '', ['class'=>'form-control input-sm', 'placeholder'=>'Plant Name', 'required'=>'required']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('long_name', 'Long Name:', ['class'=>'col-lg-3 control-label']) }}
					<div class="col-lg-8"> 
						{{ Form::text('long_name', '', ['class'=>'form-control input-sm', 'placeholder'=>'Long Name']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('location', 'Location:', ['class'=>'col-lg-3 control-label']) }}
					<div class="col-lg-8"> 
						{{ Form::text('location', '', ['class'=>'form-control input-sm', 'placeholder'=>'Location']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('description', 'Description:', ['class'=>'col-lg-3 control-label']) }}
					<div class="col-lg-8"> 
						{{ Form::textarea('description', '', ['class'=>'form-control input-sm', 'placeholder'=>'Description']) }}
					</div>
				</div>


				<div class="form-group"> 
					{{ Form::label('is_aspa', 'ASPA Capable :', ['class'=>'col-lg-3 control-label']) }}
					<div class="col-lg-8"> 
						<select class="form-control input-sm" name="is_aspa" id="is_aspa">
							<option value="0">No</option>
							<option value="1">Yes</option>
						</select>
					</div>
				</div>

				<div class="form-group" id="aspa_type_row" style="display:none;"> 
					{{ Form::label('aspa_type', 'Type of ASPA:', ['class'=>'col-lg-3 control-label']) }}
					<div class="col-lg-8"> 
						{{ Form::select('aspa_type', $aspa_types, '', ['class'=>'form-control input-sm']) }}
					</div>
				</div>

				<div class="form-group"> 
					{{ Form::label('is_island_mode', 'Island Mode :', ['class'=>'col-lg-3 control-label']) }}
					<div class="col-lg-8"> 
						<select class="form-control input-sm" name="is_island_mode" id="is_island_mode">
							<option value="0">No</option>
							<option value="1">Yes</option>
						</select>
					</div>
				</div>


				<div class="form-group"> 
					{{ Form::label('engines', 'Number of Engines :', ['class'=>'col-lg-3 control-label']) }}
					<div class="col-lg-8"> 
						<select class="form-control input-sm" name="engines" id="engines">
							@for ($i = 1; $i <= 10; $i++)
							<option value="{{$i}}">{{$i}}</option>
                            @endfor
						</select>
					</div>
				</div>

				<div class="form-group">
					<div class="col-lg-8 col-lg-offset-3">
						{{ Form::submit('Add Plant', ['class'=>'btn btn-primary btn-sm']) }}
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
    	$('#is_aspa').unbind().bind('change',function(){
    		$('#aspa_type_row').hide();
    		$('#aspa_type').find('option:eq(0)').prop('selected', true);
    		var is_aspa = parseInt( $('#is_aspa').val(), 10 ); 
    		if ( is_aspa == 1 ) {
    			$('#aspa_type_row').show();
    		}
    	})
        

    });
    
</script>
@endsection
