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
         	{!! Breadcrumbs::render('running_nomination') !!}
			@include ('user.message')
			<h4>Running Nominations Report</h4>

			<div class="well bs-component col-md-12">
				{{ Form::open(['id'=>'form_retrieve', 'class'=>'form-horizontal']) }}
				<div class="form-group">
					{{ Form::label('type', 'Type :', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-2">
						{{ Form::select('type', $types, '', ['class'=>'form-control input-sm', 'id' => 'type']) }}
					</div>
				</div>

				<div class="form-group">
					{{ Form::label('customer_id', 'Customer :', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-2">
						{{ Form::select('customer_id', $customers, '', ['class'=>'form-control input-sm', 'id' => 'customer_id']) }}
					</div>
				</div>

				<div class="form-group">
					{{ Form::label('participant_id', 'Participant:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-2">
						{{ Form::select('participant_id', $participants, '', ['class'=>'form-control input-sm', 'id' => 'participant_id']) }}
					</div>
				</div>
				
				<div class="form-group">
					{{ Form::label('billing', 'Billing Period:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-sm-2">
						{{ Form::selectMonth('billing_month', 1, ['class'=>'form-control input-sm','id'=>'billing_month']) }}
					</div>
					
					@php 
					$current_year = date('Y');
					$syear = $current_year - 5;
					$eyear = $current_year + 5;
					@endphp 
					<div class="col-lg-1" style="padding-left: 0px;">
						{{ Form::selectRange('billing_year',$syear ,$eyear, $current_year, ['class'=>'form-control input-sm','id'=>'billing_year']) }}
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
	         </div> <!-- end -->

	        <div class="error col-md-12" id="error"></div> 
            <div id="result">
            	
	        </div>
         </div>	
	</div>
	 

	 <div class="modal fade" id="modal_contents" tabindex="-1" role="dialog" aria-labelledby="modal_contentsLabel" style="z-index: 9999">
		 <div class="modal-dialog" role="document">
		    <div class="modal-content">
		    	<div class="modal-header" id="modal_title">
			        Title here
		      	</div>
		      	<div class="modal-body" id="result_modal">
		      	</div>
		      	<div class="modal-footer">
		        	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		      	</div>
		    </div>
		 </div>
	</div>

	        
	
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function(){
	DATA = {};
	$.extend({
		retrieve : function(){
			DATA = {};
			var params = {};
			params['customer_id'] = $('#customer_id').val();
			params['participant_id'] = $('#participant_id').val();
			params['billing_month'] = $('#billing_month').val();
			params['billing_year'] = $('#billing_year').val();
			params['type'] = $('#type').val();
			$('#result_button').html('');
			$.ajax({
                url : "/nomination/running_report/data",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data : params,
                type : "POST",
                async : false,
                error : function(error){
                    console.log('Error : '+error)
                },
                success : function(data){
                	var list = data.list;
                	var start_date = data.start_date;
                	var end_date = data.end_date;
                	var total_records = parseInt(data.total_records,10);
                	if (total_records > 0) {
                		
                		var start = new Date(start_date);
						var end = new Date(end_date);
						var loop = new Date(start);

                		var html = '<table class="table table-striped" id="list">';
                		html+='<thead>';
			            html+='<tr><th>Interval</th>';
			            
			            // header
						while(loop <= end){
						   var dte = moment(loop).format("YYYY-MM-DD");
						   html+='<th style="min-width:80px;">'+dte+'</th>'; 
						   var newDate = loop.setDate(loop.getDate() + 1);
						   loop = new Date(newDate);
						}
						html+='</tr> </thead>';

						// contents
						html += '<tbody>';
						for (var i=1;i<=24;i++){
			            	html+='<tr>';
			            	html+='<td>'+i+'</td>';
			            	var loop = new Date(start);
			            	while(loop <= end){
							   var dte = moment(loop).format("YYYY-MM-DD");
							   var nom = '';

							   // get data
							   if ( typeof list[dte] != 'undefined') {
							   		if ( typeof list[dte][i] != 'undefined') {
							   			nom =  $.formatNumberToSpecificDecimalPlaces(list[dte][i]['nomination'],2);
							   		} 
							   } 
							   html+='<td style="text-align:right;">'+nom+'</td>';

							   var newDate = loop.setDate(loop.getDate() + 1);
							   loop = new Date(newDate);
							}
							html+='</tr>';

			            }



						
						html+= '</tbody>';
						var contents = html + '</table>';
						$('#result').removeAttr('class').html(contents);
					    $('#list').DataTable({
					    	scrollY:        300,
					        scrollX:        true,
					        scrollCollapse: true,
					        paging:         false,
					        autoWidth: 		false,
					        searching: 		false,
					        bSort: 			false,
					        fixedColumns:   {
					            leftColumns: 1
					        }
					    });

				    	var buttons = '<button class="btn btn-success btn-sm" type="button" id="btn_excel">Export to Excel</button>&nbsp;';
	                    $( "#result_button" ).html(buttons);

	                    $('#btn_excel').unbind().bind('click',function(){
	                    	$.downloadFile();
	                    });


                	}else {
                		$('#result').removeAttr('class').html('No available data.').attr('class','alert alert-info');
                	}                    
                }
            });
        },
        downloadFile :function(){
            var params = {};

            var customer_id = $('#customer_id').val();
			var participant_id = $('#participant_id').val();
			var billing_month = $('#billing_month').val();
			var billing_year = $('#billing_year').val();
			var type = $('#type').val();

            var errors = [];
            if (customer_id.length <= 0 ) {
            	errors.push('Please select Customer');
            }

            if (participant_id.length <= 0 ) {
            	errors.push('Please select participant');
            }

            $('#errror').html('').removeAttr('class');
            if (errors.length > 0 ) {
            	$('#error').removeAttr('class').html('<ul>'+errors.join('')+'</ul>').attr('class','alert alert-info');

            }else {
            	var params = '';
	            params+='?customer_id='+customer_id;
	            params+='&participant_id='+participant_id;
	            params+='&billing_month='+billing_month;
	            params+='&billing_year='+billing_year;
	            params+='&type='+type;
	            window.location.href = '/nomination/running_report/file'+params;
            }


            


        } //
	})

    
    $('.btn-retrieve').on('click', function(e){
    	e.preventDefault();
    	$.retrieve();
    });


    $('#modal_contents').on('shown.bs.modal', function (e) {
  		$($.fn.dataTable.tables(true)).DataTable().columns.adjust();});

    

});
</script>
@endsection