@extends('layouts.app')

@section('content')
<style type="text/css">
	.nomination_items , .total {
		text-align: right;
	}
</style>

<link href=" {{ asset('css/bootstrap-datepicker.standalone.min.css') }} " rel="stylesheet">

@php
$is_with_uploading = 0;
$remarks = '';
$nomination_data = array();
$date_list = array();
@endphp

@if(Session::has('message_uploading'))
    @php
    $nomination = Session::get('nomination');
    $nomination_data = $nomination['nomination_data'];
    $remarks = $nomination['remarks'];
    $date_list = $nomination['date_list'];
    $is_with_uploading = 1;
    $date = $nomination['date'];
    $end_date = $nomination['end_date'];
    @endphp
@endif

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
				<h4>Week Ahead Nomination</h4>
	            	             
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
                        {{ Form::label('delivery_date', 'Delivery Date:', ['class'=>'col-lg-2 control-label']) }}
                        <div class="col-lg-4 input-group" style="padding-left:15px;"> 
                            {{ Form::text('delivery_date', $date, ['class'=>'form-control input-sm', 'placeholder'=>'Delivery Date', 'required'=>'required' , 'id' => 'delivery_date']) }}
                             <span class="input-group-addon" id="basic-addon1"> - </span>

                             {{ Form::text('end_date', $end_date, ['class'=>'form-control input-sm', 'id'=>'end_date', 'required'=>'required', 'readonly' => true, 'style' => 'background-color:#ffffff;']) }}
                              <span class="input-group-btn">
                              </span>
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
	            	@if(Session::has('message_uploading'))
	                     <p id="info_box" class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message_uploading') }}</p>
	                @else 
	                   <div id="info_box"></div>         
	                @endif
	            </div>
	            <div class="well col-md-12">
			      	<h5>Upload Nomination Template &nbsp;&nbsp;
			      		<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne" class="collapsed">
			        		<i class="glyphicon glyphicon-collapse-down"></i>
			        	</a>
			        </h5>					        					      
					<div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne" style="height: 0px;">
					      <div class="panel-body">
					        {{ Form::open(array('route'=>'nomination.wan.upload','files'=>true,'method'=>'POST'),['class'=>'form-horizontal']) }}
					        <input type="hidden" name="upload_sdate" id="upload_sdate" value="">
					        <input type="hidden" name="upload_edate" id="upload_edate" value="">
					        <div class="form-group col-lg-12">
		                        <div class="col-lg-6">
			                        <div class="fileinput fileinput-new input-group" data-provides="fileinput">
										<div class="form-control input-sm" data-trigger="fileinput" style="overflow:hidden;white-space: nowrap"><i class="glyphicon glyphicon-file fileinput-exists"></i><span class="fileinput-filename"></span></div>
										<span class="input-group-addon btn btn-default btn-file"><span class="fileinput-new btn-sm">Select file</span><span class="fileinput-exists btn-sm">Change</span>
										<input type="file" name="filename" required="required" accept=".xlsx"></span>
										<a href="#" class="input-group-addon btn btn-default fileinput-exists input-sm" data-dismiss="fileinput">Remove</a>
									</div>									
		                        </div>
		                        <div class="col-lg-6"> 
		                        	{{ Form::submit('Upload File', ['class'=>'btn btn-primary btn-sm','id' => 'btn_upload_file']) }}
		                        </div>   
		                    </div>		                    
		                    {{ Form::close() }}
					    </div>
					</div>
					<hr>
					<h5>Populate Values &nbsp;&nbsp;
			      		<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapsePopulate" aria-expanded="true" aria-controls="collapsePopulate" class="collapsed">
			        		<i class="glyphicon glyphicon-collapse-down"></i>
			        	</a>
			        </h5>					        					      
					<div id="collapsePopulate" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne" style="height: 0px;">
						{{ Form::open(['id'=>'form_populate', 'class'=>'form-horizontal']) }}
					      <div class="panel-body">
					        <div class="form-group">
								{{ Form::label('', 'Populate:', ['class'=>'col-lg-2 control-label']) }}
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

							<div class="form-group">
		                        <div class="col-lg-2" style="text-align:right;" >
		                            <input type="hidden" name="hid_check_uncheck" id="hid_check_uncheck" value="1">
		                            <button type="button" class="btn btn-default btn-sm" id="btn_check_uncheck" ><span class="glyphicon glyphicon-unchecked"></span> Uncheck All</button>
		                        </div>
		                        <div class="col-lg-9" id="checkbox_container" style="margin-top:10px;">
		                        	@if ( $is_with_uploading == 1 )

		                        		@foreach ($date_list as $date)
										    <label><input type="checkbox" class="week_date" name="week_date"  value="{{$loop->index}}" checked=true>&nbsp;{{$date}}&nbsp;&nbsp;</label>&nbsp; 
										@endforeach
		                        	@endif
		                        </div>
		                    </div>
					    </div>

					     {{ Form::close() }}
					</div>							
				</div>	            	

				<div class="error col-md-12"></div> 
	            <div class="well col-md-12">
	            	{{ Form::open(['route' => 'nomination.week_ahead.store', 'class'=>'form-horizontal']) }}

						<input type="hidden" id="date" name="date" value="">
						<input type="hidden" id="participant_id" name="participant_id" value="">
						<input type="hidden" id="customer_id" name="customer_id" value="">
						

						<div class="tabpanel">

		                    <!-- Nav tabs -->
		                    <ul class="nav nav-tabs" role="tablist" id="tab_list">
		                    	@if ( $is_with_uploading == 1 )
	                        		@foreach ($date_list as $date)
	                        			@php
	                        			$active = ''; 
	                        			if ($loop->index == 0) {
	                        				$active = 'active'; 
	                        			}
	                        			@endphp
									    <li class="{{$active}}"><a href="#t{{$loop->index}}" data-toggle="tab">{{$date}}</a></li>
									@endforeach
	                        	@endif
		                    </ul>

		                    <!-- Tab panes -->
		                    <div class="tab-content" id="tab_content">
		                        @if ( $is_with_uploading == 1 )
	                        		@foreach ($date_list as $date)
									    @php
	                        			$active = ''; 
	                        			if ($loop->index == 0) {
	                        				$active = 'active'; 
	                        			}
	                        			@endphp


	                        			<div class="tab-pane {{$active}}" id="t{{$loop->index}}"><div class="table-responsive">
										  <table class="table table-striped table-condensed" id="table_t{{$loop->index}}">
										  	<tr>
										  	<th class="text-center col-md-1" style="text-align: center;">Hour</th>
										  	<th class="col-md-2" style="text-align: center;">Nomination&nbsp;(kW)</th>
										  	<th class="col-md-2" style="text-align: center;">Submitted @&nbsp;</th>
										  	<th class="col-md-2" style="text-align: center;">Source</th>
										  	</tr>

										  	@for ($h = 1; $h <= 24; $h++)
										  		@php 
										  		$nomination = '';
										  		if ( isset( $nomination_data[$loop->index]) ) {
										  			if ( isset( $nomination_data[$loop->index][$h]) ) {
										  				$nomination = $nomination_data[$loop->index][$h]['nomination'];
										  			}
										  		}
										  		@endphp
											    <tr>
											    	<td class="text-center">{{$h}}</td>
													<td>
														<input name="nomination[{{$loop->index}}][{{$h}}]" 
															class="input-group-xs nomination_items form-control input-sm" 
															value="{{number_format($nomination, 2)}}">
													</td>
													<td style="text-align:center; vertical-align: middle;">
														<span id="submitted_at_{{$loop->index}}_{{$h}}"></span>
													</td>
													<td style="text-align:center; vertical-align: middle;">
														<span id="source_{{$loop->index}}_{{$h}}"></span>
													</td>
												</tr>
											@endfor

										  	<tr>
										  	<td class="text-center"><b>Total</b></td>
										  	<td><input readonly 
										  		class="input-group-xs total form-control input-sm" id="total_{{$loop->index}}" 
										  		value=""></td>
										  	<td>&nbsp;</td>
										  	<td>&nbsp;</td>
										  	</tr>
										  	</table>
										  </div>
										  </div>

									@endforeach
	                        	@endif
		                    </div>
		                    
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

	    -

	
</div>

@endsection
@section('scripts')

<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('js/jquery.autoNumeric.js') }}"></script>
<script>
// $(document).ready(function(){
	$.extend({
		retrieve : function(){
            $.ajax({
                url : "/nomination/week_ahead/data",                 
                data : $('#form_retrieve').serialize(),
                type : "POST",
                error : function(XMLHttpRequest, status, error){
                    if (XMLHttpRequest.status === 401) {
				      location.href = '/';
				    }
                },
                success : function(data){
                	
                	var date_checkbox = '';
                	var date_tab = '';
                	var tab_content = '';
                	var active = '';
                	var i = 0;

                	var nominations = data.nominations;
                	var nomination = nominations.nomination !== null ? nominations.nomination : {};
                	var source = typeof nomination['type'] != 'undefined' ? nomination['type'] : '';
                	var remarks = typeof nomination['remarks'] != 'undefined' ? nomination['remarks'] : '';
                	var nomination_items = nominations.nomination_items;
                	var is_with_data = typeof nomination['type'] != 'undefined' ? true : false;

                	$.each(data.date, function(k, cur_date){
                		
                		active = (i === 0) ? 'active' : '';
                		date_checkbox+='<label><input type="checkbox" class="week_date" name="week_date"  value="'+i+'" checked=true>&nbsp;'+cur_date+'&nbsp;&nbsp;</label>&nbsp;'; 
                		date_tab+= '<li class="'+active+'"><a href="#t'+i+'" data-toggle="tab">'+cur_date+'</a></li>';

                		var table_content = '', total_nomination = 0;
                		for (tr=1;tr<=24;tr++) {

                			var nomination = '', updated_at = '';
                			if (typeof nomination_items[cur_date] !='undefined' ) {
                				if (typeof nomination_items[cur_date][tr] !='undefined' ) {
                					nomination = $.formatNumberToSpecificDecimalPlaces(nomination_items[cur_date][tr]['nomination']);
                					updated_at = moment(nomination_items[cur_date][tr]['updated_at']['date']).format('MM/DD/YYYY h:mm:ss A');
                					total_nomination = total_nomination + parseFloat(nomination_items[cur_date][tr]['nomination']);
	                			}
                			}

                			table_content+=	'<tr><td class="text-center">'+tr+'</td>'+
											'<td><input name="nomination['+i+']['+tr+']" class="input-group-xs nomination_items form-control input-sm" value="'+nomination+'"></td>'+
											'<td style="text-align:center; vertical-align: middle;"><span id="submitted_at_'+i+'_'+tr+'">'+updated_at+'</span></td>'+
											'<td style="text-align:center; vertical-align: middle;"><span id="source_'+i+'_'+tr+'">'+source+'</span></td>'+
											'</tr>'

                		}

                		tab_content+='<div class="tab-pane '+active+'" id="t'+i+'"><div class="table-responsive">' +
									  '<table class="table table-striped table-condensed" id="table_t'+i+'">'+
									  '<tr>'+
									  '<th class="text-center col-md-1" style="text-align: center;">Hour</th>'+
									  '<th class="col-md-2" style="text-align: center;">Nomination&nbsp;(kW)</th>'+
									  '<th class="col-md-2" style="text-align: center;">Submitted @&nbsp;</th>'+
									  '<th class="col-md-2" style="text-align: center;">Source</th>'+
									  table_content+
									  '</tr><tr>'+
									  '<td class="text-center"><b>Total</b></td>'+
									  '<td><input readonly class="input-group-xs total form-control input-sm" id="total_'+i+'" value="'+$.formatNumberToSpecificDecimalPlaces(total_nomination)+'"></div></td>'+
									  '<td>&nbsp;</td>'+
									  '<td>&nbsp;</td>'+
									  '</tr>'+
									  '</table>'+
									  '</div>'+
									  '</div>';
		    			i++;
				    });

                	$('#checkbox_container').html(date_checkbox)
                	$('#tab_list').html(date_tab)
                	$('#tab_content').html(tab_content);
                	$('#remarks').val(remarks);
                	
                	$('.error').html('').removeClass('alert alert-warning')
                    if ( !is_with_data ) {
                    	$('.error').html('No data to be retrieved').addClass('alert alert-warning')
                    } 

                	$('.nomination_items').autoNumeric('init',{
			            mDec: '2'
			            ,vMin : -9999999999      
			        });
                	// return false;
                	
                }
            })
        },
        total_nominations : function(){
        	for(var w=0;w<=6;w++){
        		var sum = 0;

        		$( 'input[name^=nomination\\['+w+'\\]]' ).each(function( index ) {
				  	sum += +$(this).val().replace(/,/gi, "")
				});
		    	$('#total_'+w).val($.formatNumberToSpecificDecimalPlaces(sum,2))
        	}


        	
        }
    });

	$('.btn-retrieve').on('click', function(e){
		e.preventDefault();
		$.retrieve();
	})

	
	$('#checkall').on('click', function(e){
		e.preventDefault();

		$('.week_date').prop('checked',!$('.week_date').prop('checked'));
	});

	$('#populate').on('click', function(){
    	var scope = $('#scope').val().split('-');
    	var week_data_list = $('[name=week_date]:checked').map(function() {return this.value;}).get();
    	var week_date_index = '';
    	var nom = $.formatNumberToSpecificDecimalPlaces($('#nominations').val(),2);
    	for(var w=0;w<week_data_list.length;w++){
    		week_date_index = week_data_list[w];
    		for(i = scope[0]; i<= scope[1]; i++) {
    			$('input[name="nomination['+week_date_index+']['+i+']"]').val(nom);
    		}
    	}
    	$.total_nominations();
    })


	$('#date').val($('#delivery_date').val())
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


    $('#upload_sdate').val($('#delivery_date').val())
    $('#delivery_date').change(function(){
    	$('#upload_sdate').val($(this).val())
    });


    $('#upload_edate').val($('#end_date').val())
    


    $('#btn_check_uncheck').unbind().bind('click',function(){
        var hid_check_uncheck = parseInt($('#hid_check_uncheck').val());
        if (hid_check_uncheck === 1) {
            $('#btn_check_uncheck').html('<span class="glyphicon glyphicon-check"></span> Check All');
            $('#hid_check_uncheck').val('0');
            $('input[name=week_date]').prop('checked',false)
        }else {
             $('#btn_check_uncheck').html('<span class="glyphicon glyphicon-unchecked"></span> Uncheck All');
            $('#hid_check_uncheck').val('1');
            $('input[name=week_date]').prop('checked',true)
        }
    });

	$('input[name="delivery_date"]').datepicker({
        daysOfWeekDisabled: "0,1,2,3,4,5,",
        daysOfWeekHighlighted: "6",
        autoclose: true
    })
    .on('changeDate', function(e) {
        var x = new Date($('#delivery_date').val());
        var end_date = moment(x).add(6, 'days').format('MM/DD/YYYY');
        $('#end_date').val(end_date);
        $('#upload_edate').val($('#end_date').val())
        if($('#delivery_date').val() <= moment().format('MM/DD/YYYY')){
            $('#btn_submit').prop('disabled',true);
            $('#btn_upload_file').prop('disabled',true);
            $('input[name*=nomination]').prop('disabled',true);
            $('#remarks').prop('disabled',true);
        }else{
            $('input[name*=nomination]').removeAttr('disabled');
        	$('#remarks').removeAttr('disabled');
        	$('#btn_submit').removeAttr('disabled');
        	$('#btn_upload_file').removeAttr('disabled');
        }
    });
    var is_with_uploading = parseInt($.trim('<? echo $is_with_uploading;?>'),10);
    if (is_with_uploading == 0 ) {
    	$.retrieve();
    }

// });
</script>
@endsection