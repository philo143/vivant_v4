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
	            <legend>BCQ Report</legend>
	            <div id="info_box" class="col-md-12"></div>
	            <div class="well bs-component col-md-12"> 
                    {{ Form::open(['class'=>'form-horizontal','id' => 'form_retrieve']) }}
                    <div class="form-group"> 
                        {{ Form::label('customers', 'Customer :', ['class'=>'col-lg-2 control-label']) }}
                       <div class="col-lg-3"> 
                             {{ Form::select('region', $customers, '',['class'=>'form-control input-sm' , 'id' => 'region']) }}
                        </div>
                    </div>

                    @php 
                    $months = array('January','February','March','April','May','June','July','August','September','October','November','December');
                    $year = date("Y");
                    $s_year = $year - 5;
                    @endphp 
                    <div class="form-group"> 
                        {{ Form::label('billing', 'Billing Period :', ['class'=>'col-lg-2 control-label']) }}
                        <div class="col-lg-2"  style="padding-right:0px;"> 
                            <select class="form-control input-sm" id="month" name="month">
                                @foreach($months as $id => $month)
                                    <option value="{{ $id+1 }}">{{ $month }}</option>
                                @endforeach
                            </select>   


                        </div>

                        <div class="col-lg-2"> 
                            <select class="form-control input-sm" id="year" name="year">
                                @for ($i = $s_year; $i <= $year; $i++)
							        <option value="{{ $i }}" selected="true">{{ $i }}</option>
							    @endfor
                            </select>   

                            
                        </div>
                    </div>

                    <div class="form-group"> 
                        <div class="col-lg-2"> 
                            &nbsp;
                        </div>
                        <div class="col-lg-5"> 
                            <button type="button" class="btn btn-primary" id="btn_display">Display</button>
                            <button type="button" class="btn btn-primary" id="btn_export" disabled="true">Export to Excel</button>
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
   </style>
   <script type="text/javascript">

   $.extend({
   		displayData : function(){
   			var mn = $('#month').val();
   			var yr = $('#year').val();

   			var days = moment(yr+"-"+mn, "YYYY-MM").daysInMonth();
   			var html = '<table class="table table-condensed table-striped table-bordered" >';
   			html+='<tr>';
   			html+='<td style="min-width:90px;">Interval</td>';

   			for(var d=1;d<=days;d++){
   				var cur_dte = moment(yr+"-"+mn+'-'+d, "YYYY-MM-DD").format('MM/DD/YYYY');
   				html+='<td style="min-width:90px;">'+cur_dte+'</td>';
   			}

   			html+='</tr>';

   			var interval_list = [];
   			for(var x=5;x<=55;x+=5){
                interval_list.push(x);
            }
            interval_list.push(0);

   			for (var hr=1;hr<=24;hr++){

   				var prev_hr = hr - 1;
                if (hr === 1) {
                    prev_hr = 24;
                }

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
		   			html+='<td>'+intra_interval+'</td>';

		   			for(var d=1;d<=days;d++){
		   				html+='<td style="text-align:right;">0.0000</td>';
		   			}

		   			html+='</tr>';

	            });

   			}

   			html+='</table>';


   			$('#result').html(html).attr('style','overflow:auto; width:1000px; height:300px;')
   		}
   });



   $(document).ready(function(){

   		$('#btn_display').unbind().bind('click',function(){
   			$.displayData();
   		});
   		
   		
   });
   </script>

@stop