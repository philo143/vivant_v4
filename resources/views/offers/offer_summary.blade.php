@extends('layouts.app')

@section('content')
	<div class="container-fluid">
	    <div class="row">
	        <div class="col-md-2">
	            <div class="list-group">
	                @include('offers.menu')
	            </div>
	        </div>
	        <div class="col-md-10">
	            <legend>Offer Summary</legend>    	            
	            <div id="info_box" class="col-md-12"></div>
	            <div class="well col-md-12">
	            	{{ Form::open(['class'=>'form-horizontal','id' => 'form_display']) }}
	            		<div class="row">
							<div class="form-group col-md-5">
								{{ Form::label('delivery_date', 'Delivery Date:', ['class'=>'col-lg-4 control-label']) }}
							    <div class="col-lg-6">
							    	{{ Form::text('delivery_date',Date('m/d/Y'),['class'=>'form-control input-sm']) }}
							    </div>
							</div>
						</div>
						<div class="row">
			            	<div class="form-group col-md-5">
			            		{{ Form::label('offer_type', 'Offer Type:', ['class'=>'col-lg-4 control-label']) }}
								<div class="col-lg-6"> 
									{{ Form::select('offer_type', array_merge([0 => "ALL"],$offer_types),'', ['class'=>'form-control input-sm']) }}
								</div>
							</div>
						</div>
						<div class="row">
							<div class="form-group col-md-5">
								<div class="col-lg-4"></div>
								<div class="col-lg-4">
									<div class="input-group">
										{{ Form::submit('Display', ['class'=>'form-control btn btn-primary btn-sm','id'=>'btn_display']) }}
										<span class="input-group-addon hidden" id="pls_wait">Please Wait...</span>
									</div>
								</div>
							</div>
						</div>			            										
					{{ Form::close() }}
	            </div>
	            <div class="col-lg-12">
	            	<table id="summary_table" class="table table-striped table-hover" width="100%">
				        <thead>
				            <tr>
				                <th>Transaction ID</th>
				                <th>Resource ID</th>
				                <th>Delivery Date</th>
				                <th>Date Created</th>
				                <th>User</th>
				                <th>Type</th>
				                <th>Action</th>
				                <th>Status</th>
				                <th width="5%"></th>
				            </tr>
				        </thead>				        
				        <tbody>				            
			            </tbody>
		            </table>
	            </div>	           
	        </div>
	    </div>	    
	</div>	
@stop

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			$('#delivery_date').daterangepicker({
				singleDatePicker: true,
                showDropdowns: true
			})
			var table = $('#summary_table').DataTable( {
				order: [[ 3, "desc" ]],		       
	            ajax: {
	            	url : '{{ route('offer_summary.data') }}',
	            	type : "POST",
	            	data: function ( d ) {
		                d.delivery_date = $('input[name="delivery_date"]').val(),
		                d.offer_type = $('select[name="offer_type"]').val();
		            },
	            	headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
	            },
	            columns: [
	                {data: 'response_trans_id' },
	                {data: 'resource.resource_id'},
	                {data: 'delivery_date'},
	                {data: 'created_at'},
	                {data: 'user.username'},
	                {data: 'offer_type.offer_type'},
	                {data: 'action'},
	                {data: 'status'},
	                {data: 'download'}	                
	            ]			
		      });
			
			$.extend({
				getOfferInfo : function (id) {
					$.ajax({						
					    type: "POST",
					    url: '{{ route('offer_summary.info') }}',
					    data: {id : id},
					    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
					    success: function(msg){
					    	msg.generated_xml = msg.generated_xml == null ? '' : msg.generated_xml ;
					    	msg.response_str = msg.response_str == null ? '' : msg.response_str ;
			                var h = '<table class="table table-bordered" width="100%">';
					   		h+= '<tr><th class="col-lg-2">Resource ID:</th><th>'+msg.resource.resource_id+'</th></tr>'
			                    +'<tr><th>Delivery Date:</th><th>'+msg.delivery_date+'</th></tr>'
			                    +'<tr><th>Created Date:</th><th>'+msg.created_at+'</th></tr>'
			                    +'<tr><th>User:</th><th>'+msg.user.fullname+' ('+msg.user.username+')</th></tr>';
			                    +'<tr><th>User:</th><th>'+msg.offer_type+'</th></tr>';
			                h+= '<tr><td colspan="2"><div class="col-lg-6">&nbsp;&nbsp;Request: <textarea class="col-lg-12" rows="20" style="background-color:#F9F9D6;min-height: 450px; height:100%;margin:5px" readonly>'+msg.generated_xml+'</textarea></div>'
			                    +'<div class="col-lg-6">Response: <div style="width:100%;  min-height: 450px; height:100%; background-color:#F4F6FC; margin:4px 2px 0px 0px; border:1px solid #00A7FB; text-align:center">'+msg.response_str+'</div></div>'
			                    +'</td></tr>'+
			                    +'<tr><th>'+msg.status+'</th></tr></table>';
			                bootbox.alert({
			                	size: 'large',
			                	title: '<h4 id="ModalLabel">WESM XML/RESPONSE INFO</h4>',
			                	message: h
			                })
					    }
					});
				},
				extractOffer : function (id) {
					window.location.href = '{{ route('offer_summary.download') }}'+'?id='+id;
				}
			})
			$('#btn_display').unbind('click').bind('click',function(e){
				e.preventDefault();
				table.ajax.reload();
			})
			$('#summary_table').on('click','a.offer_info',function(e){
				e.preventDefault();
			    var id = $(this).attr("id");
			    $.getOfferInfo(id);
			})
			$('#summary_table').on('click','.download',function(e){
				e.preventDefault();
			    var id = $(this).attr("id");
			    $.extractOffer(id);
			})
			socket.on('app.offer.status:App\\Events\\OfferStatus',function(msg){
				msg = msg.data;
				console.log(msg);
				var b_id = $('table td a:contains('+msg.bid_id+')').attr('id');
				if(b_id){
					if(msg.status == "Valid"){
						console.log('asdadsa')
					   $('#s_'+b_id).removeClass();
                       $('#s_'+b_id).addClass('label label-success');
                       $('#s_'+b_id+' strong').text('VALID');
					}
                    else if(msg.status == "Invalid"){
                       $('#s_'+b_id).removeClass();
                       $('#s_'+b_id).addClass('label label-danger');
                       $('#s_'+b_id+' strong').text('INVALID');
                    }
                    
				}
			})
		})
	</script>
@stop