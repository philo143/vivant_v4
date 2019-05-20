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
	                @include('aspa.menu')
	            </div>
	        </div>

	        <div class="col-md-10">
	            {!! Breadcrumbs::render('aspa_nomination_view') !!}
				@include ('user.message')
				<h4>ASPA Nomination View Page</h4>
	            	             
	            <div class="well bs-component col-md-12">
		            {{ Form::open([ 'id'=>'form_retrieve', 'class'=>'form-horizontal']) }}
					
					<div class="form-group">
						{{ Form::label('plant_id', 'Unit :', ['class'=>'col-lg-2 control-label']) }}
						<div class="col-lg-2">
							{{ Form::select('plant_id', $plants, '', ['class'=>'form-control input-sm', 'id' => 'plant_id']) }}
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
						<div class="col-lg-1" style="width:6%;">
							{{ Form::button('Retrieve', ['class'=>'btn btn-primary btn-sm btn-retrieve']) }}
						</div>
						<div class="col-lg-1" id="result_button"></div>
					</div>
					{{ Form::close() }}
	            </div>

	            	

				<div class="error col-md-12"></div> 
	            <div class="col-md-12" style="padding:0px;">
	            	<div class="table-responsive" id="tablepanel">
					</div>
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
			$('#tablepanel').html('').removeAttr('class');
			$('#result_button').html('');
			var tmp_ = $('#delivery_date').val().split('-');
            var sdate = $.trim(tmp_[0]);
            var edate = $.trim(tmp_[1]);
            var plant_id = $('#plant_id').val();

			var params = {};
			params['sdate'] = sdate;
			params['edate'] = edate;
			params['plant_id'] = plant_id;

			$.ajax({
				url : "/aspa_nomination/data_bydaterange", 
				headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },                
                data : params,
                type : "POST",
                error : function(XMLHttpRequest, status, error){
                	console.log(error)
                	if (XMLHttpRequest.status === 401) {
				      location.href = '/';
				    } 
                },
                success : function(ret) {
                	var data = ret.data;
                	var total_records = parseInt(ret.total_records);
                	$('#tablepanel').html('').removeAttr('class');
                	var resource_ids = ret.resource_ids;
                	var resource_id_list = [];

                	console.log(total_records)
                    if (total_records > 0) {
                    	var html = '<table class="table table-striped table-condensed table-bordered" id="list">'
                    	html+='<thead>';
                    	html+='<tr>';
                    	html+='<th rowspan="2" style="vertical-align:middle; text-align:center;">Date</th>';
                    	html+='<th rowspan="2" style="vertical-align:middle; text-align:center;">Interval</th>';
                    	$.each(resource_ids,function(key,resource){
                    		resource_id_list.push('resource_'+key);
                            html+='<th colspan="10" style="text-align:center;">Unit '+resource.unit_no+'</th>';
                        });
                    	html+='</tr>';
                    	$('#resource_ids').val(resource_id_list.join(','));

                    	html+='<tr>';
                    	$.each(resource_ids,function(key,resource){
                            html+='<th style="text-align:center; width:200px;">Available Capacity (MW)</th>';
                            html+='<th style="text-align:center;width:200px;">Pump (MW)</th>';
                            html+='<th style="text-align:center;width:200px;">RR (MW)</th>';
                            html+='<th style="text-align:center;width:200px;">CR (MW)</th>';
                            html+='<th style="text-align:center;width:200px;">DR (MW)</th>';
                            html+='<th style="text-align:center;width:200px;">RPS (Mvar)</th>';
                            html+='<th style="text-align:center;width:200px;">Nominated Price (Pesos)</th>';
                            html+='<th style="text-align:center;width:200px;">Scheduled Capacity</th>';
                            html+='<th style="text-align:center;width:200px;">Dispatched Capacity</th>';
                            html+='<th style="text-align:center;width:200px;">Remarks</th>';
                        });
                    	html+='</tr>';
						html+='</thead>';
                    	
						html+='<tbody>';


						$.each(data,function(date,date_row){
                            for (var i=1;i<=24;i++){
								html+='<tr>';
								html+='<td style="text-align:center;">'+date+'</td>';
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
		                            html+='<td style="text-align:right;">'+scheduled_capacity+'</td>';
		                            html+='<td style="text-align:right;">'+dispatch_capacity+'</td>';
		                            html+='<td style="text-align:left;">'+remarks+'</td>';

		                            
		                            
		                        });
		                        html+='</tr>';

							}
                        });


						
                    	html+='</tbody>';		
						html+='</table>'
						$('#tablepanel').html(html).attr('class','table-responsive');

						$('#list').DataTable({
					    	scrollY:        "300px",
					        scrollX:        true,
					        scrollCollapse: true,
					        paging:         false,
					        searching: 		false,
					        bSort: 			false,
					        columnDefs: [
					            { width: 100, targets: 0 }
					        ],
					        fixedColumns:   {
					            leftColumns: 2
					        }
					    });
						$('.numeric').autoNumeric('init',{
				            mDec: '2'
				            ,vMin : -9999999999      
				        });
						
						var buttons = '<button class="btn btn-success btn-sm" type="button" id="btn_excel">Export to Excel</button>&nbsp;';
	                    $( "#result_button" ).html(buttons);

	                    $('#btn_excel').unbind().bind('click',function(){
	                    	$.downloadFile();
	                    });
                    } else {
                    	$('#tablepanel').html('No record available').attr('class','alert alert-info');
                    } 
                }
			});
        }, 
        downloadFile : function(){
        	var tmp_ = $('#delivery_date').val().split('-');
            var sdate = $.trim(tmp_[0]);
            var edate = $.trim(tmp_[1]);
            var plant_id = $('#plant_id').val();

            var errors = [];
            if (plant_id.length <= 0 ) {
            	errors.push('Please select Plant');
            }

            if (sdate.length <= 0 ) {
            	errors.push('Please select start date');
            }

            if (edate.length <= 0 ) {
            	errors.push('Please select end date');
            }

            $('#errror').html('').removeAttr('class');
            if (errors.length > 0 ) {
            	$('#error').removeAttr('class').html('<ul>'+errors.join('')+'</ul>').attr('class','alert alert-info');

            }else {
            	var params = '';
	            params+='?plant_id='+plant_id;
	            params+='&sdate='+sdate;
	            params+='&edate='+edate;
	            window.location.href = '/aspa_nomination/view/file'+params;
            }
        }
	})

    $('input[name="delivery_date"]').daterangepicker({
        singleDatePicker: false,
        showDropdowns: true
    });


    $('.btn-retrieve').on('click', function(e){
    	e.preventDefault();
    	$.retrieve();
    })

    // $.retrieve();

});
</script>
@endsection