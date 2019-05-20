@extends('layouts.app')

@section('content')
	<div class="container-fluid">
	    <div class="row">
	        <div class="col-md-2">
	            <div class="list-group">
	                @include('plant_capability.menu')
	            </div>
	        </div>
	        <div class="col-md-10">
	            <legend>Realtime Plant Capability</legend>
	            <div id="info_box" class="col-md-12"></div>
	            <div class="well bs-component col-md-12">
	            	<form class="form-horizontal" id="rpc_form" method="post">
	            		{{ csrf_field() }}
	            		<input type="hidden" name="page_source" value="RT">
	            		<div class="row">
			            	<div class="form-group col-md-4"> 
								<label for="plant" class="col-lg-5 control-label">Plant:</label>
								<div class="col-lg-7"> 
									<select class="form-control input-sm" id="plant" name="plant">
										@foreach($plants as $id => $plant)
											<option value="{{ $id }}">{{ $plant }}</option>
										@endforeach
									</select>									
								</div>
							</div>
						</div>
						<div class="row">
							<div class="form-group col-md-4"> 
								<label for="unit" class="col-lg-5 control-label">Unit:</label>
								<div class="col-lg-7">
									<select class="form-control input-sm" id="unit" name="unit">
									</select>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="form-group col-md-4"> 
								<label for="delivery_date" class="col-lg-5 control-label">Delivery Date:</label>
							    <div class="col-lg-7">
							      <input type="text" class="form-control input-sm" id="delivery_date" name="delivery_date" value="{{ date('m/d/Y') }}"> 
							    </div>
							</div>
							<div class="col-md-5">
								<button type="submit" class="btn btn-sm btn-primary" id="retrieve">Retrieve</button>
							</div>
						</div>
					</form>
	            </div>
	            <h4>Populate Fields</h4>
	            <span class="form-horizontal">
		            <div class="row">
						<div class="form-group col-md-4"> 
							<label for="pop_mw" class="col-lg-5 control-label">Populate Text Box</label>
						    <div class="col-lg-3">
						    	<input type="text" class="form-control input-sm" value="1-24" id="interval">
						    </div>
						    <div class="col-lg-4 input-group">
						    	<input type="text" class="form-control input-sm" value="300" id="txt_mw">
						    	<span class="input-group-addon">MW</span>		
						    </div>
						</div>
					    <div class="col-lg-7">
							<div class="col-lg-12">
								<label for="pop_remarks" class="col-lg-2 control-label">Remarks</label>
								<div class="col-lg-4">
									<select name="pop_remarks" class="form-control input-sm">
					                    <option value="" disabled selected>Select Outage Type</option>
					                    @foreach($remarks as $id => $status)
	                	  					<option value="{{ $id }}">{{ $status }}</option>
	                	  				@endforeach              
					                </select>
								</div>
								<button class="btn btn-primary btn-sm" id="populate">Populate</button>
							</div>
						</div>
					</div>
				</span>
				<br \>
				<small class="text-info"><strong>Delivery Date : <span id="dd_text"></span></strong></small>
	            <div class="well bs-component">
		            <form class="form-horizontal" id="data_form">
		            {{ csrf_field() }}
		            <table class="table table-condensed">
		            	<thead>
		            		<tr>
		            			<th width="10%">Interval</th>
		            			<th width="10%">Unit</th>
		            			<th width="15%">Remarks</th>
		            			<th width="30%">Description</th>
		            			<th width="5%">Source</th>
		            		</tr>
		            	</thead>
		            	<tbody id="rtpc_table">
		            	@for($i=1;$i<=24;$i++)
		            		<tr>        
	                	  		<th>
	                	  			{{ $i }}&nbsp;{{ '('.str_pad($i*100+1-100,4,0,STR_PAD_LEFT) .'-'. str_pad($i*100,4,0,STR_PAD_LEFT) .'H)' }}
	                	  			{{-- <input type="hidden" name="interval[{{ $i }}]" value="{{ str_pad($i*100+1-100,4,0,STR_PAD_LEFT) }}-{{ str_pad($i*100,4,0,STR_PAD_LEFT) }}H"--}}
	                	  			<input type="hidden" name="hour[0][{{ $i }}]" value="{{ $i }}">
	                	  		</th>
	                	  		<th><span class="input-group"><input type="text" class="form-control input-sm" name="capability[0][{{ $i }}]" required="required"><span class="input-group-addon">MW</span></span></th>
	                	  		<td>
	                	  			<select name="status[0][{{ $i }}]" class="form-control input-sm">
	                	  				@foreach($remarks as $id => $status)
	                	  					<option value="{{ $id }}">{{ $status }}</option>
	                	  				@endforeach				       		  
				          			</select>
				          		</td>
	                	  		<td><textarea rows="4" class="form-control" name="description[0][{{ $i }}]"></textarea></td>
	                	  		<th><span id="source_0_{{ $i }}"></span><input type="hidden" value="RT" name="source[0][{{ $i }}]"></th>
	                		</tr>
	                	@endfor
		            	</tbody>
		            </table>
		           	<btn class="btn btn-primary" id="submit_data">Submit Plant Availability</btn>
		            </form>		            	          
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
	    });
	    function pad (str, max) {
		  str = str.toString();
		  return str.length < max ? pad("0" + str, max) : str;
		}
	    $.extend({
	    	getUnit : function () {
	    		$.ajax({
			    	url : "/resources/list_by_plant_id",
			    	headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
			    	data : {plant_id : $('select[name="plant"]').val()},
			    	type : "POST",
			    	error : function(error){
			    		console.log('Error : '+error)
			    	},
			    	success : function(data){
			    		var options = '';
			    		$.each(data,function(i,val){
			    			options += '<option value="'+val.id+'">'+val.resource_id+'</option>';
			    		})

			    		$('select[name="unit"]').html(options);
			    	}
			    })
	    	}
	    })
	    $.getUnit();
	    $('#populate').unbind('click').bind('click',function(e){
			e.preventDefault();
			
			interval 	= $('#interval').val();
			mw 			= $('#txt_mw').val();
			rem 		= $('select[name="pop_remarks"]').val();
			hour = interval.split('-');
			start = parseInt(hour[0]);
			end = parseInt(hour[1]); 
			if (!end) end = start;

		    for(x=start;x<=end;x++){
		        $('input[name="capability[0]['+x+']"]').val(mw);
		        if(rem){
		        	$('select[name="status[0]['+x+']"]').val(rem);
		        }
		        
		    }
		});
		$('select[name="plant"]').on('change',function(){
			$.getUnit();
		})
		$('#retrieve').unbind('click').bind('click',function(e){
			e.preventDefault();
			$('#info_box').removeClass().html('');
			$('#dd_text').html('');
			$('#data_form')[0].reset();
			$('span[id*="source"]').html('')	
			$.ajax({
		    	url : "/plant_capability/retrieve",			    	
		    	data : $('#rpc_form').serialize(),
		    	type : "POST",
		    	error : function(error){
		    		var error_msgs = '';
		    		$.each(error.responseJSON,function(key,i){			                
		                error_msgs += '<li>'+i+'</li>'
		            })			      
		            $('#info_box').html('<ul>'+error_msgs+'</ul>').addClass('alert alert-danger')		   
		    	},
		    	success : function(data){
		    		
		    		if(data['RT'] !== undefined && data['RT'].length > 0){
		    			data = data['RT']
		    		}else if(data['DAP'] !== undefined && data['DAP'].length > 0){
		    			data = data['DAP']
		    		}else if(data['WAP'] !== undefined && data['WAP'].length > 0){
		    			data = data['WAP']
		    		}else{
		    			data = 0;
		    		}
		    		if(data.length > 0){
		    			$.each(data,function(i,val){
		    				$('#dd_text').html(val.delivery_date);
		    				$('input[name="capability[0]['+val.hour+']"]').val(val.capability)
		    				$('select[name="status[0]['+val.hour+']"]').val(val.plant_capability_status_id)
		    				$('textarea[name="description[0]['+val.hour+']"]').val(val.desc)
		    				$('span[id="source_0_'+val.hour+'"]').html(val.type)
		    			})
		    		}else{
		    			$('#info_box').html('No Data').addClass('alert alert-info')	
		    			$(document).scrollTop(0)
		    		}
		    	}
			})
			
		})
		$('#submit_data').unbind('click').bind('click',function(e){
			$('#info_box').removeClass();
			e.preventDefault();
			$.ajax({
		    	url : "/plant_capability/store",			    	
		    	data : $('#data_form').serialize()+'&delivery_date='+$('#delivery_date').val()+'&unit='+$('#unit').val()+'&plant='+$('#plant').val(),
		    	type : "POST",
		    	error : function(error){
		    		var error_msgs = '';
		    		$.each(error.responseJSON,function(key,i){			                
		                error_msgs += '<li>'+i+'</li>'
		            })			      
		            $('#info_box').html('<ul>'+error_msgs+'</ul>').addClass('alert alert-danger')		            
		    	},
		    	success : function(data){
		    		$('#info_box').html('<p>'+data+'</p>').addClass('alert alert-success')
		    	}
			})
			$(document).scrollTop(0)
		})
	    
   })
    </script>

@stop