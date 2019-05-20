@extends('layouts.app')

@section('content')
<style type="text/css">
	.nomination_items , .total {
		text-align: right;
	}
</style>

@php
$is_with_uploading = 0;
if ($success_upload = Session::get('success_upload')) {
	$is_with_uploading = 1;
}

if (Session::get('delivery_date')) {
	$default_date = Session::get('delivery_date');
}
$default_plant = '';
if (Session::get('plant_id')) {
	$default_plant = Session::get('plant_id');
}
@endphp
<div class="container-fluid">

	<div class="row">
	        <div class="col-md-2">
	            <div class="list-group">
	                @include('aspa.menu')
	            </div>
	        </div>

	        <div class="col-md-10">
	            {!! Breadcrumbs::render('aspa_nomination_input') !!}
				@include ('user.message')
				<h4>ASPA Nomination Input</h4>
	            	             
	            <div class="well bs-component col-md-12">
		            {{ Form::open([ 'id'=>'form_retrieve', 'class'=>'form-horizontal']) }}
					
					<div class="form-group">
						{{ Form::label('plant_id', 'Unit :', ['class'=>'col-lg-2 control-label']) }}
						<div class="col-lg-2">
							{{ Form::select('plant_id', $plants, $default_plant, ['class'=>'form-control input-sm', 'id' => 'plant_id']) }}
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

	            <div class="well col-md-12">
			      	<h5>Upload ASPA Template &nbsp;&nbsp;
			      		<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne" class="collapsed">
			        		<i class="glyphicon glyphicon-collapse-down"></i>
			        	</a>
			        </h5>					        					      
					<div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne" style="height: 0px;">
					      <div class="panel-body">
					        {{ Form::open(array('route'=>'aspa_nomination.upload','files'=>true,'method'=>'POST'),['class'=>'form-horizontal']) }}
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

		                    <div class="form-group">
								<div class="form-group col-lg-12"> 
								    <div class="col-lg-6">
								    	<a id="download_template" style="cursor: pointer;">Download Template</a>
								    </div>
								</div>						
							</div>		                    
		                    {{ Form::close() }}
					    </div>
					</div>
											
				</div>	            	

				<div class="error col-md-12"></div> 
	            <div class="col-md-12" style="padding:0px;">
	            	{{ Form::open(['class'=>'form-horizontal', 'route'=>'aspa_nomination.store']) }}
						<input type="hidden" id="date" name="date" value="">
						<input type="hidden" id="plant" name="plant" value="">
						<input type="hidden" id="resource_ids" name="resource_ids" value="">
						
						<div class="table-responsive col-md-12" id="tablepanel">
						</div>
						
						<div class="form-group" id="btn_holder" style="display:none;">
							<div class="col-lg-1"></div>
							<div class="col-lg-2">
								{{ Form::submit('Submit', ['class'=>'btn btn-primary btn-sm','id'=>'btn_submit']) }}
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
			$('#btn_holder').hide();
			$('#tablepanel').html('').removeAttr('class');
            $('#resource_ids').val('');
			$.ajax({
				url : "/aspa_nomination/data",                 
                data : $('#form_retrieve').serialize(),
                type : "POST",
                error : function(XMLHttpRequest, status, error){
                	console.log(error)
                	if (XMLHttpRequest.status === 401) {
				      location.href = '/';
				    } 
                },
                success : function(ret) {
                	var data = ret.data;
                	var date = ret.date;
                	var resource_ids = ret.resource_ids;
                	var total_records = parseInt(ret.total_records);
                	$('#tablepanel').html('').removeAttr('class');
                	$('#resource_ids').val('');
                	var resource_id_list = [];
                    if (total_records > 0) {
                    	var html = '<table class="table table-striped table-condensed table-bordered" id="list">'
                    	html+='<thead>';
                    	html+='<tr>';
                    	html+='<th rowspan="2" style="vertical-align:middle; text-align:center;">Interval</th>';
                    	$.each(resource_ids,function(key,resource){
                    		resource_id_list.push('resource_'+key);
                            html+='<th colspan="10" style="text-align:center;">Unit '+resource.unit_no+'</th>';
                        });
                    	html+='</tr>';
                    	$('#resource_ids').val(resource_id_list.join(','));

                    	html+='<tr>';
                    	$.each(resource_ids,function(key,resource){
                            html+='<th style="text-align:center; width:100px;">Available Capacity (MW)</th>';
                            html+='<th style="text-align:center;width:100px;">Pump (MW)</th>';
                            html+='<th style="text-align:center;width:100px;">RR (MW)</th>';
                            html+='<th style="text-align:center;width:100px;">CR (MW)</th>';
                            html+='<th style="text-align:center;width:100px;">DR (MW)</th>';
                            html+='<th style="text-align:center;width:100px;">RPS (Mvar)</th>';
                            html+='<th style="text-align:center;width:100px;">Nominated Price (Pesos)</th>';
                            html+='<th style="text-align:center;width:100px;">Scheduled Capacity</th>';
                            html+='<th style="text-align:center;width:100px;">Dispatched Capacity</th>';
                            html+='<th style="text-align:center;width:100px;">Remarks</th>';
                        });
                    	html+='</tr>';
						html+='</thead>';
                    	
						html+='<tbody>';
						for (var i=1;i<=24;i++){
							html+='<tr>';
							html+='<td style="text-align:center;">'+i+'</td>';

							$.each(resource_ids,function(key,resource){
								var available_capacity = '';
								var pump = '';
								var rr = '';
								var cr = '';
								var dr = '';
								var rps = '';
								var nominated_price = '';
								var scheduled_capacity = '';
								var dispatch_capacity = '';
								var remarks = '';
								var id = '';
								if ( typeof data[date] != 'undefined' ) {
									if ( typeof data[date][i] != 'undefined' ) {
										if ( typeof data[date][i][key] != 'undefined' ) {
											var object = data[date][i][key];
											id = object['id'];
											available_capacity = $.formatNumberToSpecificDecimalPlaces(object['available_capacity'],2);
											pump = $.formatNumberToSpecificDecimalPlaces(object['pump'],2);
											rr = $.formatNumberToSpecificDecimalPlaces(object['rr'],2);
											cr = $.formatNumberToSpecificDecimalPlaces(object['cr'],2);
											dr = $.formatNumberToSpecificDecimalPlaces(object['dr'],2);
											rps = $.formatNumberToSpecificDecimalPlaces(object['rps'],2);
											nominated_price = object['nominated_price'] == null ? '' : $.formatNumberToSpecificDecimalPlaces(object['nominated_price'],2);
											scheduled_capacity = object['scheduled_capacity'] == null ? '' : $.formatNumberToSpecificDecimalPlaces(object['scheduled_capacity'],2);
											dispatch_capacity = object['dispatch_capacity'] == null ? '' : $.formatNumberToSpecificDecimalPlaces(object['dispatch_capacity'],2);
											remarks = object['remarks']= object['remarks'] == null ? '' : object['remarks'];

										}
									}
								}

								html+='<td style="text-align:right;">'+available_capacity+'</td>';
	                            html+='<td style="text-align:right;">'+pump+'</td>';
	                            html+='<td style="text-align:right;">'+rr+'</td>';
	                            html+='<td style="text-align:right;">'+cr+'</td>';
	                            html+='<td style="text-align:right;">'+dr+'</td>';
	                            html+='<td style="text-align:right;">'+rps+'</td>';
	                            html+='<td style="text-align:right;">'+nominated_price+'</td>';
	                            html+='<td style="text-align:right;"><input type="text" class="form-control input-sm numeric" value="'+scheduled_capacity+'" name="scheduled_capacity_resource-'+key+'_int-'+i+'"/></td>';
	                            html+='<td style="text-align:right;"><input type="text" class="form-control input-sm numeric" value="'+dispatch_capacity+'"  name="dispatch_capacity_resource-'+key+'_int-'+i+'"/></td>';
	                            html+='<td style="text-align:right;"><input type="text" class="form-control input-sm" value="'+remarks+'"  name="remarks_resource-'+key+'_int-'+i+'"/></td>';
	                            html+='<input type="hidden" class="form-control input-sm" value="'+id+'"  name="id_resource-'+key+'_int-'+i+'"/>';

	                            
	                        });
	                        html+='</tr>';

						}
                    	html+='</tbody>';		
						html+='</table>'
						$('#tablepanel').html(html).attr('class','table-responsive');

						$('.numeric').autoNumeric('init',{
				            mDec: '2'
				            ,vMin : -9999999999      
				        });
						$('#btn_holder').show();
                    } else {
                    	$('#tablepanel').html('No record available').attr('class','alert alert-info');
                    } 
                }
			});
            $
        }
	})

    $('input[name="delivery_date"]').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true
    });


    $('.btn-retrieve').on('click', function(e){
    	e.preventDefault();
    	$.retrieve();
    })

    $('#plant').val($('#participant').val())
    $('#plant_id').change(function(){
    	$('#plant').val($(this).val())
    });

    $('input[name="delivery_date"]').change(function(){
    	$('.date').text($(this).val())
    	$('#date').val($(this).val())
    });

    $('#download_template').unbind().bind('click',function(){
    	window.location.href = '{{ route('aspa_nomination.template') }}';
    });
    $.retrieve();

});
</script>
@endsection