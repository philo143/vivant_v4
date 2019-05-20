@extends('layouts.app')

@section('content')

<div class="container-fluid">
	<div class="row">
		<div class="col-md-2">
            <div class="list-group">
                @include('nomination.menu')
            </div>
        </div>
        <div class="col-md-10">
        	{!! Breadcrumbs::render('day_ahead_nomination') !!}
			@include ('user.message')
			<h4>Nomination Extraction</h4>
			<hr>
			{{ Form::open(['route' => 'nomination.day_ahead.data', 'id'=>'form_retrieve', 'class'=>'form-horizontal']) }}
			<div class="form-group">
				{{ Form::label('participant', 'Participant:', ['class'=>'col-lg-1 control-label']) }}
				<div class="col-lg-2">
					{{ Form::select('participant', $participants, '', ['class'=>'form-control input-sm']) }}
				</div>
			</div>
			<div class="form-group">
				{{ Form::label('delivery_date', 'Delivery&nbsp;Date:', ['class'=>'col-lg-1 control-label']) }}
				<div class="col-lg-2">
					{{ Form::text('delivery_date', date('m/d/Y'), ['class'=>'form-control input-sm']) }}
				</div>
			</div>
			<div class="form-group">
				<div class="col-lg-1"></div>
				<div class="col-lg-2">
					{{ Form::button('Retrieve', ['class'=>'btn btn-primary btn-sm btn-retrieve']) }}
				</div>
			</div>
			{{ Form::close() }}
			<div class="error"></div> 
			{{ Form::open(['route' => 'nomination.day_ahead.store', 'class'=>'form-horizontal']) }}
			<hr>
			<p><small><b>Delivery Date:&nbsp;&nbsp;</b><span class="date">05/12/2017</span></small></p>
			<input type="hidden" id="date" name="date" value="">
			<input type="hidden" id="participant_id" name="participant_id" value="">
			<div class="form-group">
				{{ Form::label('', 'Populate:', ['class'=>'col-lg-1 control-label']) }}
				<div class="col-lg-5">
					<div class="input-group input-group-sm">
					  <span class="input-group-addon">Hour</span>
					  <input type="text" class="form-control" value="1-24" id="scope">
					  <span class="input-group-addon" >Nomination</span>
					  <input type="text" class="form-control" value="1000" id="nominations">
					  <span class="input-group-btn" >
			  			<button class="btn btn-primary" type="button" id="populate">Populate</button>
					  </span>
					</div>
				</div>	
			</div>
			<hr>
			<div class="table-responsive">
				<table class="table table-striped table-condensed">
					<tr>
						<th class="text-center col-md-1">Hour</th>
						<th class="col-md-2">Nomination&nbsp;(kW)</th>
						<th class="col-md-2">Submitted @&nbsp;</th>
						<th class="col-md-2">Source</th>
						<th>&nbsp;</th>
					</tr>
					@for ($i= 1;$i <= 24; $i++)
						<tr>
							<td class="text-center">{{ $i }}</td>
							<td><input name="nomination[{{$i}}]" class="input-group-xs nomination_items"></td>
							<td><span id="submitted_at_{{$i}}"></span></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							
						</tr>
					@endfor
						<tr>
							<td class="text-center"><b>Total</b></td>
							<td><input readonly class="input-group-xs total"></div></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
				</table>
			</div>
			<hr>
			<div class="form-group">
				{{ Form::label('', 'Remarks:', ['class'=>'col-lg-1 control-label']) }}
				<div class="col-lg-11">
					{{ Form::textarea('remarks', '', ['class'=>'form-control input-sm remarks']) }}
				</div>
			</div>
			<div class="form-group">
				<div class="col-lg-1"></div>
				<div class="col-lg-2">
					{{ Form::submit('Submit Nomination', ['class'=>'btn btn-primary btn-sm']) }}
				</div>
			</div>
			{{ Form::close() }}
			<br><br>
        </div>
    </div>
</div>

@endsection

@section('scripts')

@endsection