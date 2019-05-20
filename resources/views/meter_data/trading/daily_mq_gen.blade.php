@extends('layouts.app')

@section('content')
	<div class="container-fluid">
	    <div class="row">
	        <div class="col-md-2">
	            <div class="list-group">
	                @include('meter_data.trading.menu')
	            </div>
	        </div>
	        <div class="col-md-10">
	            <legend>Daily MQ Gen</legend>
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

                <div class="well bs-component col-md-12"> 
                    {{ Form::open(['class'=>'form-horizontal','id' => 'form_retrieve']) }}
                    

                    <div class="form-group"> 
                        {{ Form::label('dateRange', 'Date', ['class' => 'col-lg-2 control-label']) }}
                        <div class="col-lg-3">
                           {{ Form::text('dateRange','', array('class' => 'form-control input-sm', 'id' => 'dateRange')) }} 
                           
                        </div>  
                        
                    </div>
                    

                    <div class="form-group"> 
                        {{ Form::label('participant', 'Participants :', ['class'=>'col-lg-2 control-label']) }}
                       <div class="col-lg-3"> 
                             {{ Form::select('region', $participants, '',['class'=>'form-control input-sm' , 'id' => 'participant']) }}
                        </div>
                    </div>


                    <div class="form-group"> 
                        <div class="col-lg-2"> 
                            &nbsp;
                        </div>
                        <div class="col-lg-5"> 
                            <button type="button" class="btn btn-primary" id="btn_display">Retrieve</button>
                            <button type="button" class="btn btn-success" id="btn_export" disabled="true">Export to Excel</button>
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
   		displayData : function(){

   			var html = '<table class="table table-condensed table-striped table-bordered" >';
   			html+='<tr>';
   			html+='<td colspan="2">Participant</td>';
   			html+='<td colspan="2">'+$("#participant option:selected"). text()+'</td>';
   			html+='</tr>';
   			
   			var customers = ['Customer 1'];
   			var seins = ['SEIN001'];

   			html+='<tr>';
   			html+='<td colspan="2">Customer</td>';
   			$.each( customers, function( ii, customer ) {
   				html+='<td colspan="2">'+customer+'</td>';
   			});
   			html+='</tr>';


   			html+='<tr>';
   			html+='<td colspan="2">SEIN</td>';
   			$.each( seins, function( ii, sein ) {
   				html+='<td colspan="2">'+sein+'</td>';
   			});
   			html+='</tr>';


   			html+='<tr>';
   			html+='<td style="min-width:90px;">Hour</td>';
   			html+='<td style="min-width:90px;">Interval</td>';
        html+='<td style="min-width:90px;">DEL</td>';
        html+='<td style="min-width:90px;">REC</td>';
   			
   			html+='</tr>';

   			var interval_list = [];
   			for(var x=5;x<=55;x+=5){
                interval_list.push(x);
            }
            interval_list.push(0);

   			for (var hr=1;hr<=24;hr++){

   				var prev_hr = hr - 1;
                // if (hr === 1) {
                //     prev_hr = 0;
                // }

   				$.each( interval_list, function( ii, interval ) {
                    var pad = '00';
                    var new_interval = (pad + interval).slice(-pad.length);
                    var intra_interval = '';
                    if (interval === 0) {
                        intra_interval = $.strPad(hr,2,'0') + ':' + new_interval;
                    }else {
                        intra_interval = $.strPad(prev_hr,2,'0') + ':' + new_interval;
                    }
	              	     
	                html+='<tr>';
	                html+='<td>'+hr+'</td>';
      		   			html+='<td>'+intra_interval+'H</td>';

      		   			html+='<td style="text-align:right;">0.000000000</td>';
                  html+='<td style="text-align:right;">0.000000000</td>';

      		   			html+='</tr>';

      	   });

   			}

   			html+='</table>';


   			$('#result').html(html).attr('style','overflow:auto; width:1000px; height:300px;')


   		} // eof
   });



   $(document).ready(function(){
   		$('input[name="dateRange"]').daterangepicker({
            singleDatePicker: true,
            showDropdowns: false
         });
   		

   		$('#btn_display').unbind().bind('click',function(){
   			$.displayData();
   		});
   		
   		
   });
   </script>

@stop