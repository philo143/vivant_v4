@extends('layouts.app')

@section('content')
<style type="text/css">
	.nomination_items , .total {
		text-align: right;
	}
</style>
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
				<h4>Override Nomination</h4>
	            	             
	            <div class="well bs-component col-md-12">
		            {{ Form::open(['route' => 'nomination.day_ahead.data', 'id'=>'form_retrieve', 'class'=>'form-horizontal']) }}
					<div class="form-group">
						{{ Form::label('customer', 'Customer :', ['class'=>'col-lg-2 control-label']) }}
						<div class="col-lg-2">
							{{ Form::select('customer', $customers, '', ['class'=>'form-control input-sm']) }}
						</div>
					</div>

					<div class="form-group">
						{{ Form::label('participant', 'Participant:', ['class'=>'col-lg-2 control-label']) }}
						<div class="col-lg-2">
							{{ Form::select('participant', $participants, '', ['class'=>'form-control input-sm']) }}
						</div>
					</div>
					<div class="form-group">
						{{ Form::label('delivery_date', 'Delivery&nbsp;Date:', ['class'=>'col-lg-2 control-label']) }}
						<div class="col-lg-2">
							{{ Form::text('delivery_date', $default_date, ['class'=>'form-control input-sm','id'=>'delivery_date']) }}
						</div>
					</div>
					<div class="form-group">
						<div class="col-lg-2"></div>
						<div class="col-lg-2">
							{{ Form::button('Retrieve', ['class'=>'btn btn-primary btn-sm btn-retrieve']) }}
						</div>
					</div>
					{{ Form::close() }}
	            </div>

	            <div class="col-md-12" id="msg_uploading">
	            	@php
	                ## generate default values for form inputs
	                $is_with_uploading = 0;
	                $remarks = '';
	                $form_data = array();
	                for($i=1;$i<=24;$i++){
	                    $form_data[$i] = array(
	                        'nomination' => ''
	                    );
	                }
	                @endphp

	                @if(Session::has('message_uploading'))
	                     <p id="info_box" class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message_uploading') }}</p>
	                    @php
	                    $nomination = Session::get('nomination');
	                    $form_data = $nomination['nomination_data'];
	                    $remarks = $nomination['remarks'];
	                    $is_with_uploading = 1;
	                    @endphp
	                @else 
	                   <div id="info_box"></div>         
	                @endif
	            </div>
	            <div class="well col-md-12">
			      	
					<h5>Populate Values &nbsp;&nbsp;
			      		<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapsePopulate" aria-expanded="true" aria-controls="collapsePopulate" class="collapsed">
			        		<i class="glyphicon glyphicon-collapse-down"></i>
			        	</a>
			        </h5>					        					      
					<div id="collapsePopulate" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne" style="height: 0px;">
					      <div class="panel-body">
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
					    </div>
					</div>							
				</div>	            	

				<div class="error col-md-12"></div> 
	            <div class="well col-md-12">
	            	{{ Form::open(['route' => 'nomination.day_ahead.store', 'class'=>'form-horizontal']) }}
						<p><small><b>Delivery Date:&nbsp;&nbsp;</b><span class="date">05/12/2017</span></small></p>
						<input type="hidden" id="date" name="date" value="">
						<input type="hidden" id="participant_id" name="participant_id" value="">
						<input type="hidden" id="customer_id" name="customer_id" value="">
						
						<hr>
						<div class="table-responsive">
							<table class="table table-striped table-condensed">
								<tr>
									<th class="text-center col-md-1" style="text-align: center;">Hour</th>
									<th class="col-md-3" style="text-align: center;">Nomination&nbsp;(kW)</th>
									<th class="col-md-3" style="text-align: center;">Submitted @&nbsp;</th>
									<th class="col-md-3" style="text-align: center;">Source</th>
								</tr>
								@for ($i= 1;$i <= 24; $i++)
									<tr>
										<td class="text-center">{{ $i }}</td>
										<td><input name="nomination[{{$i}}]" 
											class="input-group-xs nomination_items form-control input-sm" value="{{ $form_data[$i]['nomination']}} "></td>
										<td style="text-align:center; vertical-align: middle;"><span id="submitted_at_{{$i}}"></span></td>
										<td style="text-align:center;vertical-align: middle;"><span id="source_{{$i}}"></span></td>
										
									</tr>
								@endfor
									<tr>
										<td class="text-center"><b>Total</b></td>
										<td><input readonly class="input-group-xs total form-control input-sm"></div></td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
									</tr>
							</table>
						</div>
						<hr>
						<div class="form-group">
							{{ Form::label('', 'Remarks:', ['class'=>'col-lg-1 control-label']) }}
							<div class="col-lg-11">
								{{ Form::textarea('remarks', $remarks, ['class'=>'form-control input-sm remarks','id'=>'remarks']) }}
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-1"></div>
							<div class="col-lg-2">
								{{ Form::submit('Submit Nomination', ['class'=>'btn btn-primary btn-sm','id'=>'btn_submit']) }}
							</div>
						</div>
						{{ Form::close() }}
		        </div>
	        </div>
	    </div>
	
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function(){

	$.extend({
		retrieve : function(){
			$('#msg_uploading').html('');
            $.ajax({
                url : "/nomination/day_ahead/data",                 
                data : $('#form_retrieve').serialize(),
                type : "POST",
                error : function(XMLHttpRequest, status, error){
                	console.log(error)
                	if (XMLHttpRequest.status === 401) {
				      location.href = '/';
				    } 
                },
                success : function(data){
                	$('.error').html('').removeClass('alert alert-warning')
                    if (data.length > 0) {
                    	var source = data[0].type;
                    	$.each(data[0].nomination_items,function(i,val){
                    		$('input[name="nomination['+val.hour+']"]').val($.formatNumberToSpecificDecimalPlaces(val.nomination,2));
                    		$('#submitted_at_'+i).html(val.updated_at);
                    		$('#source_'+i).html(source);
                    	})
                    	$.total_nominations();
                    	$('.remarks').text(data[0].remarks);
                    } else {
                    	$('.error').html('No data to be retrieved').addClass('alert alert-warning');
                    	$('input[name*=nomination]').val('');
                    	$('span[id*=submitted_at_]').html('');
                    	$('span[id*=source_]').html('')
                    } 

                    // check if date selected less than or equal to current date, disabled all textboxes and submit button
                    var current_date = '<?php echo $current_date;?>';
                    var current_date_obj = new Date(current_date);
                    var selected_date = $('#delivery_date').val();
                    var selected_date_obj = new Date(selected_date);

                    if ( selected_date_obj > current_date_obj) {
                    	$('input[name*=nomination]').removeAttr('disabled');
                    	$('#remarks').removeAttr('disabled');
                    	$('#btn_submit').removeAttr('disabled');
                    	$('#btn_upload_file').removeAttr('disabled');
                    }else {
                    	$('input[name*=nomination]').prop('disabled',true);
                    	$('#remarks').prop('disabled',true);
                    	$('#btn_submit').prop('disabled',true);
                    	$('#btn_upload_file').prop('disabled',true);
                    }
                }
            })
        },
        total_nominations : function(){
        	var sum = 0;
	    	$('.nomination_items').each(function(){
	    		sum += +parseFloat($(this).val().replace(/,/gi, ""))
	    	});
	    	$('.total').val($.formatNumberToSpecificDecimalPlaces(sum,2));
        }
	})

    $(function() {
        $('input[name="delivery_date"]').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true
        });

        var is_with_uploading = parseInt($.trim('<? echo $is_with_uploading;?>'),10);
        if (is_with_uploading == 0 ) {
        	$.retrieve();
        }else {
        	$.total_nominations();
        }
 


        $('.nomination_items').autoNumeric('init',{
            mDec: '2'
            ,vMin : -9999999999      
        });
    });

    $('.btn-retrieve').on('click', function(e){
    	e.preventDefault();
    	$.retrieve();
    })

    $('input[name="delivery_date"]').change(function(){
    	$('.date').text($(this).val())
    	$('#date').val($(this).val())
    });

    $('#participant_id').val($('#participant').val())
    $('#participant').change(function(){
    	$('#participant_id').val($(this).val())
    });


    $('#customer_id').val($('#customer').val())
    $('#customer').change(function(){
    	$('#customer_id').val($(this).val())
    });

    $('#populate').on('click', function(){
    	var scope = $('#scope').val().split('-')
    	for(i = scope[0]; i<= scope[1]; i++) {
    		$('input[name="nomination['+i+']"]').val($.formatNumberToSpecificDecimalPlaces($('#nominations').val(),2))
    	}
    	$.total_nominations();
    })

    $('.nomination_items').on('change', function(){
    	$.total_nominations();
    })

});
</script>
@endsection