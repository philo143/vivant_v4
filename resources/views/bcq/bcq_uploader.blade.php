@extends('layouts.app')

@section('content')
	<div class="container-fluid">
	    <div class="row">
	        <div class="col-md-2">
	            <div class="list-group">
	                @include('bcq.menu')
	            </div>
	        </div>
	        <div class="col-md-10">
	            <legend>BCQ Uploader</legend>
	            <div id="info_box" class="col-md-12"></div>
	            <div class="well bs-component col-md-12">
                    {{ Form::open(array('url'=>'','files'=>true),['class'=>'form-horizontal']) }}
                     <div class="form-group"> 
                        {{ Form::label('filename', 'File:', ['class'=>'col-lg-2 control-label']) }}
                        <div class="col-lg-10"> 
                             {{  Form::file('filename', ['class'=>'form-control','style'=>'border:none; background:transparent;']) }}
                        </div>
                    </div>

                    <div class="form-group"> 
                        <div class="col-lg-2"> 
                        </div>
                        <div class="col-lg-5" style="margin-top:10px;"> 
                            {{ Form::submit('Submit File', ['class'=>'btn btn-primary']) }}
                        </div>
                    </div>

                    {{ Form::close() }}
                </div>   


	            <br><br>
	            <div id="result">
	            	
	            </div>
	            <br><br>

	        </div>
	    </div>
	</div>
	<div class="modal fade" id="modal_resource" tabindex="-1" role="dialog" aria-labelledby="modal_resourceLabel" style="z-index: 9999">
		 <div class="modal-dialog" role="document">
		    <div class="modal-content">
		    	<div class="modal-header">
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			        <input type="hidden" name="hid_check_all" id="hid_check_all" value="0">
			        <h4 class="modal-title" id="modal_resourceLabel">Choose a Resource ID <button id="checkallresource" class="btn btn-basic btn-xs" style="float:right; margin-right: 10px;"><i class="icon-ok"></i><span id="btn_text">Check All Resources</span></button>&nbsp;&nbsp;</h4>

		      	</div>
		      	<div class="modal-body" style="max-height: 300px; overflow: auto;">
        			<table id="list-table-res" class="table table-striped"></table>
		      	</div>
		      	<div class="modal-footer">
		      		<button id="get_rid" class="btn btn-info" data-dismiss="modal" aria-hidden="true">Ok</button>
		        	<button type="button" class="btn btn" data-dismiss="modal">Close</button>
		      	</div>
		    </div>
		 </div>
	</div>
@stop

@section('scripts')
   <style type="text/css">
   		#result tr th {
		    text-align: center;
		    vertical-align: middle;
		    font-weight: bold;
		}

		#result td {
			text-align: center;
		}
   </style>
   <script type="text/javascript">

   $.extend({
   		
   });



   $(document).ready(function(){

   		
   		
   		
   });
   </script>

@stop