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
	            <legend>Standing Offer Reserve</legend>
	            @php
                $form_data = array();
                $form_data['delivery_date'] = '';
                $form_data['resource_id'] = '';
                $form_data['reserve_class'] = '';
                $form_data['opres_ramp_rate'] = '';
                $form_data['day_type'] = '';
                $form_data['expiry_date'] = '';
                $form_data['intervals'] = array();
                for($i=1;$i<=24;$i++){
                    $form_data['intervals'][$i] = array(
                            'price_quantity' => '',
                            'remarks' => ''
                    );
                }

                @endphp
	            @if(Session::has('message_uploading'))
                     <p id="info_box" class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message_uploading') }}</p>
                    @php
                    $form_data = Session::get('energy_offer_template_data');
                    @endphp
                @else 
                   <div id="info_box"></div>         
                @endif
	            @if ( count($errors) )
                    <div class="alert alert-danger col-md-12">
                        <ul>
                        @foreach ($errors as $error)
                            <li>{{$error}}</li>
                        @endforeach
                        </ul>
                    </div>
                @endif
	            <div id="info_box" class="col-md-12"></div>
	            <div class="well bs-component col-md-12">
	            	{{ Form::open(['class'=>'form-horizontal','id' => 'form_retrieve']) }}
	            		<div class="row">
							<div class="form-group col-md-5">
								{{ Form::label('delivery_date', 'Delivery Date:', ['class'=>'col-lg-4 control-label']) }}
							    <div class="col-lg-6">
							    	{{ Form::select('delivery_date',$arr_date,($form_data['delivery_date'] ? $form_data['delivery_date']->format('Ymd') : date('Ymd',strtotime('+1 day'))),['class'=>'form-control input-sm']) }}
							    </div>
							</div>
							<div class="form-group col-md-5"> 
								{{ Form::label('day_type', 'Day Type:', ['class'=>'col-lg-4 control-label']) }}
							    <div class="col-lg-3">
							    	{{ Form::select('day_type',['ALL'=>'ALL','MON'=>'MON','TUE'=>'TUE','WED'=>'WED','THU'=>'THU','FRI'=>'FRI','SAT'=>'SAT','SUN'=>'SUN'],$form_data['day_type'],['class'=>'form-control input-sm']) }}
							    </div>
							</div>
						</div>
						<div class="row">
			            	<div class="form-group col-md-5">
			            		{{ Form::label('reserve_class', 'Reserve Class:', ['class'=>'col-lg-4 control-label']) }}
								<div class="col-lg-6"> 
									{{ Form::select('reserve_class', ['REG'=>'REGULATION','CON'=>'CONTINGENCY','DIS'=>'DISPATCH','ILD'=>'INTERRUPTIBLE LOAD'],$form_data['reserve_class'] ? $form_data['reserve_class'] : 'DIS', ['class'=>'form-control input-sm']) }}														
								</div>
							</div>
							<div class="form-group col-md-5">
			            		{{ Form::label('flag', 'Standing Flag:', ['class'=>'col-lg-4 control-label']) }}								
								<div class="col-lg-3"> 
									{{ Form::select('flag', ['YES'=>'YES'], '',['class'=>'form-control input-sm']) }}														
								</div>
							</div>
						</div>
	            		<div class="row">
			            	<div class="form-group col-md-5">
			            		{{ Form::label('action', 'Action:', ['class'=>'col-lg-4 control-label']) }}								
								<div class="col-lg-6"> 
									{{ Form::select('action', ['submit'=>'SUBMIT','cancel'=>'CANCEL'], '',['class'=>'form-control input-sm']) }}
								</div>
							</div>
							<div class="form-group col-md-5"> 							
            					{{ Form::label('expiry_date', 'Expiry Date:', ['class'=>'col-lg-4 control-label']) }}								
								<div class="col-lg-4">
									{{ Form::text('expiry_date',isset($form_data['expiry_date']) ? $form_data['expiry_date'] :Date('m/d/Y',strtotime('+1 day')),['class'=>'form-control input-sm']) }}
								</div>
							</div>	
						</div>
						<div class="row">
							<div class="form-group col-md-5"> 							
            					{{ Form::label('unit', 'Unit:', ['class'=>'col-lg-4 control-label']) }}								
								<div class="col-lg-6">
									<div class="input-group">
										{{ Form::text('unit',$form_data['resource_id'] ? $form_data['resource_id'] : '', ['class'=>'form-control input-sm','readonly'=>'readonly','required'=>'required']) }}
										<a href="#modal_resource" role="button" data-toggle="modal" id="resource_show" class="input-group-addon"><i class="glyphicon glyphicon-th text-primary"></i></a>
									</div>
								</div>
							</div>							
						</div>
						<div class="row">
							<div class="form-group col-md-5"> 
								{{ Form::label('opres_ramp_rate', 'Opres Ramp Rate', ['class'=>'col-lg-4 control-label']) }}
							    <div class="col-lg-4">
							    	{{ Form::text('opres_ramp_rate',$form_data['opres_ramp_rate'] ? $form_data['opres_ramp_rate'] : '',['class'=>'form-control input-sm']) }}
							    </div>
							</div>
						</div>
						<div class="row">
							<div class="form-group col-md-5">
								<div class="col-lg-4"></div>
								<div class="col-lg-4">
									<div class="input-group">
										{{ Form::submit('Retrieve', ['class'=>'form-control btn btn-primary btn-sm','id'=>'btn_retrieve']) }}
										<span class="input-group-addon hidden" id="pls_wait_retrieve">Please Wait...</span>
									</div>
								</div>
							</div>
						</div>							
					{{ Form::close() }}
	            </div>
	            <div class="well col-md-12">
			      	<h5>Upload Standing Reserve Offer Template &nbsp;&nbsp;
			      		<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
			        		<i class="glyphicon glyphicon-collapse-down"></i>
			        	</a>
			        </h5>					        					      
					<div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
					      <div class="panel-body">
					        {{ Form::open(array('route'=>'energy_offer.upload','files'=>true,'method'=>'POST'),['class'=>'form-horizontal']) }}
					        {{ Form::hidden('upload_action','')}}
					        {{ Form::hidden('template_type','so_reserve')}}
					        {{ Form::hidden('view','web')}}
					        <div class="form-group col-lg-12">
		                        <div class="col-lg-6">
			                        <div class="fileinput fileinput-new input-group" data-provides="fileinput">
										<div class="form-control input-sm" data-trigger="fileinput" style="overflow:hidden;white-space: nowrap"><i class="glyphicon glyphicon-file fileinput-exists"></i><span class="fileinput-filename"></span></div>
										<span class="input-group-addon btn btn-default btn-file"><span class="fileinput-new btn-sm">Select file</span><span class="fileinput-exists btn-sm">Change</span>
										<input type="file" name="file" required="required"></span>
										<a href="#" class="input-group-addon btn btn-default fileinput-exists input-sm" data-dismiss="fileinput">Remove</a>
									</div>									
		                        </div>
		                        <div class="col-lg-6"> 
		                        	{{ Form::submit('Upload File', ['class'=>'btn btn-primary btn-sm']) }}
		                        </div>   
		                    </div>		                    
		                    {{ Form::close() }}
					    </div>
					</div>
					<hr>
					<h5>Populate Values &nbsp;&nbsp;
			      		<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapsePopulate" aria-expanded="true" aria-controls="collapsePopulate">
			        		<i class="glyphicon glyphicon-collapse-down"></i>
			        	</a>
			        </h5>					        					      
					<div id="collapsePopulate" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
					      <div class="panel-body">
					        {{ Form::open(['class'=>'form-horizontal']) }}
					        <div class="col-lg-12">
					        	<div class="row"> 		                        
			                        <div class="form-group col-lg-10">
			                        	{{ Form::label('pq', 'Price/Quantity:', ['class'=>'col-lg-2 control-label']) }}	
			                        	<div class="col-lg-10 input-group">
			                        		{{ Form::text('web-price_qty', '(0,0),(0,47);', ['class'=>'form-control input-sm', 'placeholder'=>'Price/Quantity']) }}	
			                        		<label class="input-group-addon">{{ Form::checkbox('web-price_qty_cb','',true)}}</label>
			                        	</div>
			                        </div>
			                    </div>		                        
			                    <div class="row"> 		                        
			                        <div class="form-group col-lg-10">
			                        	{{ Form::label('rem', 'Remarks:', ['class'=>'col-lg-2 control-label']) }}	
			                        	<div class="col-lg-10 input-group">
			                        		{{ Form::text('web-remarks', 'MAN', ['class'=>'form-control input-sm', 'placeholder'=>'Remarks']) }}
			                        		<label class="input-group-addon">{{ Form::checkbox('web-remarks_cb','',true) }}</label>		
			                        	</div>      						
			                        </div>
			                    </div>
			                    <div class="row"> 		                        
			                        <div class="form-group col-lg-10">
										{{ Form::label('rem', 'Hour:', ['class'=>'col-lg-2 control-label']) }}
										<div class="col-lg-3 input-group">
											{{ Form::text('web-hour', '1-24', ['class'=>'form-control input-sm', 'placeholder'=>'Hour']) }}
											<span class="input-group-btn"><button class="btn btn-primary btn-sm" id="populate">Populate</button><button class="btn btn-default btn-sm" id="clear">Clear</button></span>
										</div>			                        	  						
			                        </div>
			                    </div>		                      
		                    </div>
		                    {{ Form::close() }}
					    </div>
					</div>							
				</div>	            	

	            <div class="well col-md-12">
	            	<h4>Offer</h4>
			        <ul class="nav nav-tabs" id="tabs">
			            <li class="active" id="web"><a href="#web_content" data-toggle="tab" id="web_format_btn">Web</a></li>
			            {{-- <li id="xml"><a href="#xml_content" data-toggle="tab" id="xml_format_btn">XML</a></li> --}}
			        </ul>
	            	{{ Form::open(['class'=>'form-horizontal','id' => 'form_submit']) }}
		            <div class="tab-content">
			            <div class="tab-pane active" id="web_content">
			                <table class="table table-hover table-striped table-condensed">
			                    <thead>
								<tr >
			                        <th width="1%">Hour <br /><label><input id="check_all" type="checkbox" /> All</label></th>
			                        <th width="54%">Price / Quantity</th>									
									<th width="30%">Remarks</th>
								</tr>
			                    </thead>
			                    <tbody>																		
									@for($i=1; $i<=24; $i++)			
										<tr id="tr{{ $i }}">
				                            <td>				                                
				                                <input name="go-{{ $i }}" id="go-'.$i.'" type="checkbox" value="1" {{ $form_data['intervals'][$i]['price_quantity'] != '' ? 'checked="checked"' : ''  }}/> {{ $i }}				                                
				                            </td>
				                            <td>
				                                <input name="web-price_qty-{{ $i }}" type="text" class="form-control price_qty input-sm" value="{{ $form_data['intervals'][$i]['price_quantity']  ? $form_data['intervals'][$i]['price_quantity'] : ''}}"/>
				                            </td>				                            
				                            <td>
				                                <input name="web-remarks-{{ $i }}" type="text" class="form-control remarks input-sm" value="{{ $form_data['intervals'][$i]['remarks']  ? $form_data['intervals'][$i]['remarks'] : ''}}"/>
				                            </td>
				                        </tr>
									@endfor
			                    </tbody>
							</table>
			            </div>
			            {{-- <div class="tab-pane" id="xml_content">
			                <textarea name="xml" rows="20" style="width:95%;background-color:#F9F9D6;font-size:12px"></textarea>
			            </div> --}}
			            <div class="row col-lg-7">
			            	<div class="col-lg-1"></div>
			           		{{ Form::submit('Submit', ['class'=>'btn btn-primary col-lg-4','id'=>'submit_offer']) }}
			           		<div id="pls_wait_submit"></div>
			           	</div>
			        </div>
		            {{ Form::close() }}
		        </div>
	        </div>
	    </div>
	</div>
	<div class="modal fade" id="modal_resource" tabindex="-1" role="dialog" aria-labelledby="modal_resourceLabel" style="z-index: 9999">
		 <div class="modal-dialog modal-sm" role="document">
		    <div class="modal-content">
		    	<div class="modal-header">
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			        <h4 class="modal-title" id="modal_resourceLabel">Choose a Resource ID</h4>
		      	</div>
	      		<table  class="table table-condensed" id="resource_table" align="left">
	      			<tbody>
			        	@foreach($resources as $id => $resource)
		                	<tr>
		                		<td id="{{ $id }}">
		                			<label class="input-group">
										<span class="input-group-addon" id="basic-addon1"><input type="radio" name="resource_dum" value="{{ $resource }}"/></span>
										<span class="form-control input-sm" aria-describedby="basic-addon1">{{ $resource }}</span>
									</label>		                			
		                		</td>
		                	</tr>
		               	@endforeach
	               	</tbody>
               	</table>
		      	<div class="modal-footer">
		      		<button id="get_resource" class="btn btn-info" data-dismiss="modal" aria-hidden="true">Ok</button>
		        	<button type="button" class="btn btn" data-dismiss="modal">Close</button>
		      	</div>
		    </div>
		 </div>
	</div>
@stop

@section('scripts')
   <script type="text/javascript">
   $(document).ready(function(){
	    $.extend({
	    	populateValues : function() {
	    		$('input[name*="_cb"]:checked').each(function(){
	    			tmp_name = $(this).attr('name').replace('_cb','');
			    	tmp_content = $('input[name="'+tmp_name+'"]').val();  	
					interval_arr = $('input[name="web-hour"]').val().split('-');
					
					if(!interval_arr[1]) interval_arr[1] = interval_arr[0];
					if(interval_arr[1]/1 < interval_arr[0]/1) return false;

					for(var i=interval_arr[0]/1; i<=interval_arr[1]/1; i++){
						$('input[name=go-'+i+']').attr('checked','checked');
						$('input[name="'+tmp_name+'-'+i+'"]').val(tmp_content);
					}
			    })			    				
	    	},
	    	clearValues : function () {
	    		$('input[name*="_cb"]:checked').each(function(){
	    			tmp_name = $(this).attr('name').replace('_cb','');
			    	tmp_content = $('input[name="'+tmp_name+'"]').val();			    						
					interval_arr = $('input[name="web-hour"]').val().split('-');
					
					if(!interval_arr[1]) interval_arr[1] = interval_arr[0];
					if(interval_arr[1]/1 < interval_arr[0]/1) return false;

					for(var i=interval_arr[0]/1; i<=interval_arr[1]/1; i++){
						$('input[name=go-'+i+']').removeAttr('checked');
						$('input[name='+tmp_name+'-'+i+']').val('');
					}
			    })						
	    	},
	    	convert: function (view){
				var url = '/bids_and_offers/offer_content';
				var parameters = $('#form_submit').serialize() +'&'+$('#form_retrieve').serialize() +'&view=' + view;
				$.ajax({
				   type: "POST",
				   url: url,
				   data: parameters,
				   success: function(ret){
						if(view == 'web'){
							if(ret.web != undefined){
								$.each(ret.web.intervals, function(i, val){
									if(val.gate_closure=="open"){
										$('input[name=go-'+i+']').attr('checked','checked');
									}else{
										$('input[name=go-'+i+']').attr('disabled','disabled');
										$('input[name=web-price_qty-'+i+']').attr('disabled','disabled');
										$('input[name=web-remarks-'+i+']').attr('disabled','disabled');
									}
									$('input[name=web-price_qty-'+i+']').val(val.price_quantity);
									$('input[name=web-remarks-'+i+']').val(val.remarks);
								});
							}
							
				   		}
				   		// else{
				   		// 	var decoded = $("<div/>").html(ret).text();
				   		// 	$('textarea[name=xml]').val(decoded);
				   		// }
				   }
				 });						
				return false;
			},
			determineDisabled : function(){
	            var year = $('select[name="delivery_date"]').val().substr(0, 4)
	            var month = $('select[name="delivery_date"]').val().substr(4, 2) -1
	            var day = $('select[name="delivery_date"]').val().substr(6, 2)
	            var date_selected = new Date(year, month, day)
	            var date = new Date()
	            var current_date = new Date(date.getFullYear(), date.getMonth(), date.getDate())

	            if (date_selected > current_date) {
	                $('select[name=action]').removeAttr('disabled')
	                $('#submit_offer').removeAttr('disabled')
	                $('.price_qty').removeAttr('readonly')
	                $('.remarks').removeAttr('readonly')
	                $('#update_btn').removeAttr('disabled')
	            } else {
	                $('select[name=action]').attr('disabled','disabled')
	                $('#submit_offer').attr('disabled','disabled')
	                $('.price_qty').attr('readonly','readonly')
	                $('.remarks').attr('readonly','readonly')
	                $('#update_btn').attr('disabled','disabled')
	            }
        	},
        	submitBids : function(){
        		$('#info_box').removeClass().html('');        		
        		if($('input[name="unit"]').val() == ''){
					$('#info_box').html('<ul><li>Unit is required.</li></ul>').addClass('alert alert-danger')
					$(document).scrollTop(0);
					return false;
				}
			
				var action = $("select[name=action]").val();
				bootbox.confirm('Are you sure you want to '+action+'?', function(result){ 
					if(result === true){
						$('#pls_wait_submit').html('Please wait...');
			            var view = $("#tabs li.active").attr('id')
						var url = '/bids_and_offers/submit_offer';
						var parameters = $('#form_submit').serialize() +'&'+$('#form_retrieve').serialize() +'&view=' + view;
						$.ajax({
						   url: url,
						   type: "POST",
						   data: parameters,
						   error: function(error){
						   		var error_msgs = '';
		                        $.each(error.responseJSON,function(key,i){
		                            error_msgs += '<li>'+i+'</li>'
		                        })
		                        $('#info_box').html('<ul>'+error_msgs+'</ul>').addClass('alert alert-danger')
		                        $(document).scrollTop(0)
						   },						   
						   success: function(msg){
			                   	bootbox.alert({message:msg,size: 'large'})
								$('#pls_wait_submit').html('');
							}
						 });	
					 }
				});
				return false;	
        	},
        	retrieveBids : function(){        		
        		$('#info_box').removeClass().html('');
        		if($('input[name="unit"]').val() == ''){
					$('#info_box').html('<ul><li>Unit is required.</li></ul>').addClass('alert alert-danger')
					$(document).scrollTop(0);
					return false;
				}
				$('#pls_wait_retrieve').removeClass('hidden');
				for (i=1;i<=24;i++) {
				    $('input[name=web-price_qty-'+i+']').val('');
				    $('input[name=web-remarks-'+i+']').val('');
				}
				var view = $("#tabs li.active").attr('id');
				var url = '/bids_and_offers/retrieve_offer';
				var parameters = $('#form_submit').serialize() +'&'+$('#form_retrieve').serialize() +'&view=' + view;
				$.ajax({
				   type: "POST",
				   url: url,
				   data: parameters,
				   error : function(error){
				   		var error_msgs = '';
                        $.each(error.responseJSON,function(key,i){
                            error_msgs += '<li>'+i+'</li>'
                        })
                        $('#info_box').html('<ul>'+error_msgs+'</ul>').addClass('alert alert-danger')
                        $(document).scrollTop(0)
                        $('#pls_wait_retrieve').addClass('hidden');
				   },
				   success: function(msg){
		                if (msg) {
		                    if(view == 'web'){
		                        $('input.go').removeAttr('checked');
		                        if(msg.web != undefined){
		                            $.each(msg.web.intervals, function(i, val){
		                                if(val.gate_closure=="open"){
		                                    $('input[name=go-'+i+']').attr('checked','checked');
		                                }else{
		                                    $('#tr'+i+' td').addClass("gclosed");
		                                    $('input[name=go-'+i+']').attr('disabled','disabled');
		                                    $('input[name=web-price_qty-'+i+']').attr('disabled','disabled');
		                                    $('input[name=web-remarks-'+i+']').attr('disabled','disabled');
		                                }
		                                $('input[name=web-price_qty-'+i+']').val(val.price_quantity);
		                                $('input[name=web-remarks-'+i+']').val(val.remarks);
		                            });
		                        }
		                        $('input[name="opres_ramp_rate"]').val(msg.web.opres_ramp_rate)
		                    }
		                    /*else{
		                        var decoded = $("<div/>").html(msg).text();
		                        $('textarea[name=xml]').val(decoded);
		                    }*/
		                    $('#pls_wait_retrieve').addClass('hidden');
		                }
					}
				 });
				return false;
        	},
        	formatDate : function () {        		
					var date    = $('select[name="delivery_date"]').val(),
				    yr      = date.substring(0,4),
				    month   = date.substring(4,6),
				    day     = date.substring(6,8),
				    start_date = month + '/' + day + '/' + yr;
				    if($('input[name="expiry_date"]').val() === ''){
				    	$('input[name="expiry_date"]').daterangepicker({
			                singleDatePicker: true,
			                showDropdowns: true,
			                startDate: start_date,
			                minDate: start_date,
			        	})
				    }else{
				    	$('input[name="expiry_date"]').daterangepicker({
				    		singleDatePicker: true,
			                showDropdowns: true,
				    	});
				    }
        	}
	    })
		$.formatDate();
   		$('select[name="delivery_date"]').bind('change',function(){
   			$.formatDate();
   		})
	    $('select[name="action"]').bind('change',(function(){
	    	var action = $(this).val();	        
	       	$('input[name="upload_action"]').val(action);
		}));
	    $('select[name="action"]').trigger('change');
	    $('#populate').unbind('click').bind('click',function(e){
			e.preventDefault();
			$.populateValues();
		});
		$('#clear').unbind('click').bind('click',function(e){
			e.preventDefault();
			$.clearValues();
		})
		$('select[name="delivery_date"]').bind('change',function(){
			$.determineDisabled();
		})
		$('#submit_offer').bind('click', function(e){
			e.preventDefault();		
			$.submitBids();
		});
		// $('#btn_retrieve').unbind('click').bind('click',function(e){
		// 	e.preventDefault();	
		// 	$.retrieveBids();		
		// })
		$('#check_all').bind('change', function(e){
			var chk = $(this).is(':checked');
			if(chk){
	            $('#form_submit input[type=checkbox]').prop('checked', 'checked');
			}else{
	            $('#form_submit input[type=checkbox]').prop('checked',false);
			}
			return false;
		});
		$('#get_resource').click(function(){	        
	        $("#resource_table input[type=radio]:checked").each(function() {
	           resource = $(this).val();	          
	        });
	        $('input[name="unit"]').val(resource);
		});
	   /* $('#web_format_btn').bind('click', function(){
			$('input.remarks').val('');
			$('input.price_qty').val('');
			$('input[name="view"]').val('web');
			$.convert('web');			
		});	*/
		/*$('#xml_format_btn').bind('click', function(){
			$('textarea[name=xml]').val('');
			$('input[name="view"]').val('xml');
			$.convert('xml');
		});*/
   })
    </script>

@stop