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
	            <legend>Offer Templates</legend>    	            
	            <div id="info_box" class="col-md-12"></div>
	            <div class="well col-md-12">
	            	{{ Form::open(['class'=>'form-horizontal','id' => 'template_form']) }}
	            	<div class="row">
						<div class="form-group col-lg-12"> 
							{{ Form::label('energy', 'Energy Offer Template', ['class'=>'col-lg-3 control-label']) }}
						    <div class="col-lg-6">
						    	{{ Form::button('Download &nbsp;&nbsp;&nbsp;<i class="glyphicon glyphicon-download-alt"></i>',['class'=>'btn btn-success btn-sm','id'=>'energy']) }}
						    </div>
						</div>						
					</div>
					<div class="row">
						<div class="form-group col-lg-12"> 
							{{ Form::label('standing', 'Standing Offer Template', ['class'=>'col-lg-3 control-label']) }}
						    <div class="col-lg-6">
						    	{{ Form::button('Download &nbsp;&nbsp;&nbsp;<i class="glyphicon glyphicon-download-alt"></i>',['class'=>'btn btn-success btn-sm','id'=>'standing']) }}
						    </div>
						</div>						
					</div>
					<div class="row">
						<div class="form-group col-lg-12"> 
							{{ Form::label('da_reserve', 'Day Ahead Reserve Offer Template', ['class'=>'col-lg-3 control-label']) }}
						    <div class="col-lg-6">
						    	{{ Form::button('Download &nbsp;&nbsp;&nbsp;<i class="glyphicon glyphicon-download-alt"></i>',['class'=>'btn btn-success btn-sm','id'=>'da_reserve']) }}
						    </div>
						</div>						
					</div>	
					<div class="row">
						<div class="form-group col-lg-12"> 
							{{ Form::label('so_reserve', 'Standing Reserve Offer Template', ['class'=>'col-lg-3 control-label']) }}
						    <div class="col-lg-6">
						    	{{ Form::button('Download &nbsp;&nbsp;&nbsp;<i class="glyphicon glyphicon-download-alt"></i>',['class'=>'btn btn-success btn-sm', 'id'=>'so_reserve']) }}
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
	<script type="text/javascript">
		$(document).ready(function(){
			$('button').unbind('click').bind('click',function(e){
				e.preventDefault();
				var template = $(this).attr("id");
				window.location.href = '{{ route('offer_templates.download') }}'+'?template='+template;
			})
		})
	</script>
@stop