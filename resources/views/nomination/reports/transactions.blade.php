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
         	{!! Breadcrumbs::render('transations_nomination') !!}
			@include ('user.message')
			<h4>Nomination Transactions</h4>

			<div class="well bs-component col-md-12">
				{{ Form::open(['id'=>'form_retrieve', 'class'=>'form-horizontal']) }}
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
	         </div> <!-- end -->

	        <div class="error col-md-12"></div> 
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
			params['date'] = $('#delivery_date').val();

			$.ajax({
                url : "/nomination/transactions/data",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data : params,
                type : "POST",
                async : false,
                error : function(error){
                    console.log('Error : '+error)
                },
                success : function(data){
                	if (data.length > 0) {
                		var html = '<table class="table table-striped" id="list">';
		                    html+= '<thead>';
		                    html+='<tr><th style="min-width:70px;">Transaction ID</th>';
		                    html+='<th style="min-width:70px;">Type</th>';
		                    html+='<th style="min-width:70px;">Remarks</th>';
		                    html+='<th style="min-width:70px;">User</th>';
		                    html+='<th style="min-width:70px;">Date Created</th>';
		                    html+='</tr> </thead>';

		                    html+= '<tbody>';
		                    $.each( data, function( i, row ) {
		                    	DATA['trans_'+row['id']] = row;
		                    	html+='<tr>';
		                    	html+='<td><a style="cursor:pointer;" name="transaction" id="trans_'+row['id']+'">'+row['transaction_id']+'</a></td>';
		                    	html+='<td>'+row['type']+'</td>';
		                    	html+='<td>'+row['remarks']+'</td>';
		                    	html+='<td>'+row['user']['fullname']+'</td>';
		                    	html+='<td>'+row['created_at']+'</td>';
		                    	html+='</tr>';
		                    }); // each
		                    html+= '</tbody>';
		                    html+='</table>';

		                    $('#result').removeAttr('class').html(html);
		                    $('#list').DataTable();


		                    $('a[name=transaction]').unbind().bind('click',function(){
		                    	var trans_id = $(this).attr('id');
		                    	$.show_details(trans_id);
		                    });
                	}else {
                		$('#result').removeAttr('class').html('No available data.').attr('class','alert alert-info');
                	}                    
                }
            });
        },
        show_details : function(trans_id){
        	var row = DATA[trans_id];
        	var data = $.parseJSON(row.data);
        	var start = new Date(row['sdate']);
			var end = new Date(row['edate']);
			var loop = new Date(start);

			$('#modal_title').html('<h5 style="font-weight:bold;">Transaction ID : ' + row.transaction_id+'</h5>');

			// setup table headers
			$('#result_modal').html('');
			var html = '<table class="table table-striped" id="list2"><thead>';
            html+='<tr><th>Interval</th>';
            
            // header
			while(loop <= end){
			   var dte = moment(loop).format("YYYY-MM-DD");
			   html+='<th style="width:80px;">'+dte+'</th>'; // header

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
				   if ( typeof data[dte] != 'undefined') {
				   		if ( typeof data[dte][i] != 'undefined') {
				   			nom = $.formatNumberToSpecificDecimalPlaces(data[dte][i],2);
				   		} 
				   } 
				   html+='<td>'+nom+'</td>';

				   var newDate = loop.setDate(loop.getDate() + 1);
				   loop = new Date(newDate);
				}
				html+='</tr>';

            }



			
			html+= '</tbody>';
			var contents = html + '</table>';
			$('#result_modal').removeAttr('class').html(contents);
		    $('#list2').DataTable({
		    	scrollY:        300,
		        scrollX:        true,
		        scrollCollapse: true,
		        paging:         false,
		        autoWidth: 		false,
		        searching: 		false,
		        bSort: 			false
		    });

        	$('#modal_contents').modal('show');
        	console.log(data)
        }
	})

    $(function() {
        $('input[name="delivery_date"]').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true
        });

        
    });

    $('.btn-retrieve').on('click', function(e){
    	e.preventDefault();
    	$.retrieve();
    });


    $('#modal_contents').on('shown.bs.modal', function (e) {
  		$($.fn.dataTable.tables(true)).DataTable().columns.adjust();});

    

});
</script>
@endsection