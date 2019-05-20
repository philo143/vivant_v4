@extends('layouts.app')

@section('content')
	<div class="container-fluid">
	    <div class="row">
	        <div class="col-md-2">
	            <div class="list-group">
	                @include('manual_downloaders.menu')
	            </div>
	        </div>
	        <div class="col-md-10">
	            <legend>RTD Output Display - LMP</legend>
	            <div id="info_box" class="col-md-12"></div>
	            
                <div class="well bs-component col-md-12"> 
                    {{ Form::open(['class'=>'form-horizontal','id' => 'form_retrieve']) }}
                    

                    <div class="form-group"> 
                        {{ Form::label('participant', 'Participants :', ['class'=>'col-lg-2 control-label']) }}
                       <div class="col-lg-3"> 
                             {{ Form::select('region', $participants, '',['class'=>'form-control input-sm' , 'id' => 'participant']) }}
                        </div>
                    </div>


                    <div class="form-group"> 
                        {{ Form::label('dateRange', 'Date', ['class' => 'col-lg-2 control-label']) }}
                        <div class="col-lg-5">
                           {{ Form::text('dateRange','', array('class' => 'form-control input-sm', 'id' => 'dateRange')) }} 
                           
                        </div>  
                        
                    </div>
                    


                    <div class="form-group"> 
                        <div class="col-lg-2"> 
                            &nbsp;
                        </div>
                        <div class="col-lg-5"> 
                            <button type="button" class="btn btn-primary" id="btn_display">Download</button>
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

		#result tr:nth-child(-n+4) td {
			font-weight: bold;
		}
   </style>
   <script type="text/javascript">

   $.extend({
   		
   });



   $(document).ready(function(){
   		$('input[name="dateRange"]').daterangepicker({
            timePicker: true,
            timePickerIncrement: 5,
            locale: {
                format: 'MM/DD/YYYY HH:mm'
            },
            timePicker24Hour : true
         });
   		

   		
   		
   		
   });
   </script>

@stop