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
	            <legend>Plant Capability Templates</legend>
	            <div id="info_box" class="col-md-12"></div>
	            <div class="well col-md-12">
	            	{{ Form::open(['route' => 'plant_capability.download_template','class'=>'form-horizontal','id' => 'form_display']) }}
	            		<div class="row">
							<div class="form-group col-md-5">
								{{ Form::label('plant', 'Plant:', ['class'=>'col-lg-4 control-label']) }}
							    <div class="col-lg-6">
							    	{{ Form::select('plant',$plants,[],['class'=>'form-control input-sm']) }}
							    </div>
							</div>
						</div>
						<div class="row">
							<div class="form-group col-md-5">
								{{ Form::label('resource', 'Resource:', ['class'=>'col-lg-4 control-label']) }}
							    <div class="col-lg-6">
							    	{{ Form::select('resource',[],[],['class'=>'form-control input-sm']) }}
							    </div>
							</div>
						</div>		
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
								{{ Form::label('template_type', 'Template Type:', ['class'=>'col-lg-4 control-label']) }}
							    <div class="col-lg-6">
							    	{{ Form::radio('template_type', 'day_ahead', true) }} <small>DAP (1 Day)</small> <br \>
							    	{{ Form::radio('template_type', 'week_ahead') }} <small>WAP (7 Days)</small>
							    </div>
							</div>
						</div>
						<div class="row">
							<div class="form-group col-md-5">
								<div class="col-lg-4"></div>
								<div class="col-lg-4">
									<div class="input-group">
										{{ Form::submit('Download', ['class'=>'form-control btn btn-success btn-sm','id'=>'btn_display']) }}
										<span class="input-group-addon hidden" id="pls_wait">Please Wait...</span>
									</div>
								</div>
							</div>
						</div>			            										
					{{ Form::close() }}
	            </div>	                 
	        </div>
	    </div>	    
	</div>	
@stop

@section('scripts')
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.standalone.min.css" />
	<script type="text/javascript">
		$(document).ready(function(){
			$('#delivery_date').datepicker();

			$('input[name="template_type"]').on('change',function(){
				$('#delivery_date').datepicker('remove');
				if($(this).val() == 'week_ahead'){
					$('#delivery_date').datepicker({
						daysOfWeekDisabled: "0,1,2,3,4,5,",
	                    daysOfWeekHighlighted: "6",
	                    autoclose: true,	                    
	                })
	                $('#delivery_date').datepicker('setDate', moment().day(0 + 6).format('MM/DD/YYYY'));
				}else{
					$('#delivery_date').datepicker({
	                    autoclose: true
	                }) 
				}
			})
			$('select[name="plant"]').on('change',function(){
				$.ajax({
                    url : "/resources/list_by_plant_id",
                    async : false,
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data : { plant_id : $('select[name="plant"]').val()},
                    type : "POST",
                    error : function(error){
                        console.log('Error : '+error)
                    },
                    success : function(data){
                         $('select[name="resource"]').html('');
                        var html = '';
                        for (var i=0;i<data.length;i++){
                            html+='<option value="'+data[i].id+'">'+data[i].resource_id+'</option>';
                        }
                        $('select[name="resource"]').html(html);
                    }
                }) 
			})
			$('select[name="plant"]').trigger('change');
		})
	</script>
@stop