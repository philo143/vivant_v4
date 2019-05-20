@extends('layouts.app')

@section('content')
	<div class="container-fluid">
	    <div class="row">
	        <div class="col-md-2">
	            <div class="list-group">
	                @include('mms_data.menu')
	            </div>
	        </div>
	        <div class="col-md-10">
	            <legend>Locational Marginal Prices</legend>
	            <div id="info_box" class="col-md-12"></div>
	            <div class="well bs-component col-md-12">
	            	<form class="form-horizontal" id="rpc_form" method="post">
	            		{{ csrf_field() }}

						<div class="form-group"> 
	                        {{ Form::label('region', 'Region ', ['class'=>'col-lg-2 control-label']) }}
	                        <div class="col-lg-3"> 
	                             {{ Form::select('region', $regions, '',['class'=>'form-control input-sm' , 'id' => 'region']) }}
	                        </div>
	                    </div>


	                    <div class="form-group"> 
	                        {{ Form::label('type', 'Type ', ['class'=>'col-lg-2 control-label']) }}
	                        <div class="col-lg-3"> 
	                             {{ Form::select('type', $types, '',['class'=>'form-control input-sm', 'id' => 'type']) }}
	                        </div>
	                    </div>


						<div class="form-group"> 
	                        {{ Form::label('resources', 'Resources : ', ['class' => 'col-lg-2 control-label']) }}
	                        <div class="col-lg-5 input-group" style="padding-left:15px;">
								<input type="text" readonly="true" name="resource_id" id="resource_id" class="form-control input-sm">
								<a href="#"  data-toggle="modal" id="show_resources" class="input-group-addon"><i class="glyphicon glyphicon-th"></i></a>
							</div>
	                    </div>

						


						<div class="form-group"> 
	                        <div class="col-lg-2" style="text-align: right; margin-top: -8px;">
	                            <span class="control-label">Hour : </span><br>
	                            <input type="checkbox" name="chk_all_hour" id="chk_all_hour" checked="true"> <span class="control-label">All</span>
	                            
	                        </div>
	                        <div class="col-lg-8">
	                            <table>
	                            <tr>
	                            @for ($x = 1; $x < 25; $x++) 
	                                @php 
	                                $colspan = ' ';
	                                @endphp 

	                                <td style="width:70px;">
	                                    <input type="checkbox" name="hour" value="{{ $x }}" checked>&nbsp;{{ $x }}
	                                </td>

	                                @if (($x % 12) == 0 ) 
	                                    </tr><tr>
	                                @endif

	                            @endfor
	                            </tr>
	                            </table>

	                        </div>  

                    	</div>


                    <div class="form-group"> 
                        <div class="col-lg-2" style="text-align: right; margin-top: -8px;">
                            <span class="control-label">Interval : </span><br>
                            <input type="checkbox" name="chk_all_interval" id="chk_all_interval" checked="true"> <span class="control-label">All</span>
                            
                        </div>
                        <div class="col-lg-8">
                            <table>
                            <tr>
                            @for ($x = 5; $x <= 60; $x+=5) 
                                @php 
                                $colspan = ' ';
                                $display_intra = $x;
                                $real_intra = $x;
                                @endphp 


                                @if ( $x === 60 )
                                	@php 
	                                $display_intra = '+00';
	                                $real_intra = 0;
	                                @endphp 
                                @endif 

                                <td style="width:70px;">
                                    <input type="checkbox" name="interval" value="{{ str_pad($real_intra,2,"0",STR_PAD_LEFT) }}" checked>&nbsp;{{ str_pad($display_intra,2,"0",STR_PAD_LEFT) .'H' }}
                                </td>

                                @if (($x % 35) == 0 && $x != 0 ) 
                                    </tr><tr>
                                @endif

                            @endfor
                            </tr>
                            </table>

                        </div>  

                    </div>

					 <div class="form-group"> 
                        {{ Form::label('dateRange', 'Date', ['class' => 'col-lg-2 control-label']) }}
                        <div class="col-lg-3">
                           {{ Form::text('dateRange','', array('class' => 'form-control input-sm', 'id' => 'dateRange')) }} 
                           
                        </div>  
                        <div class="col-lg-2" style="padding-left:0px;">
                        	<button class="btn btn-primary btn-sm" type="button" id="btn_display">Show Data</button>
                        </div>
                    </div>

					 

					</form>
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
   		#result tr:first-child td , #result tr:nth-child(2) {
		    text-align: center;
		    font-weight: bold;
		}

		#result td {
			text-align: center;
		}
   </style>
   <script type="text/javascript">

   $.extend({
   		list_resources  : function(){
            $.ajax({
                url : "/resources_lookup/list",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data : {'type' : $('#type').val(), 'region' : $('#region').val()},
                type : "POST",
                async : false,
                error : function(error){
                    console.log('Error : '+error)
                },
                success : function(data){
                    if (data !== null ) { 

                    	var resource_ids = $('#resource_id').val().split(',');
		                var resource='', html = '', x=0, checked = '';
		                html = '<tr>';

		                $.each(data, function(i, val) {
		                    x++;	

		                    checked = '';
		                    if ( resource_ids.indexOf(val.resource_id) >= 0 ) {
		                		checked = ' checked="true"';
		                	}
 		                    html+='<td id='+val.resource_id+'><input type="checkbox" id="r_id" name="r_id[]" '+checked+' value="'+val.resource_id+'">&nbsp;'+val.resource_id+'</td>';
		                    if (x % 4 === 0) {
		                            html+='</tr><tr>';
		                    }
		                   
		                })
		                html+='</tr>';
		                $('#list-table-res').html('<tbody>'+html+'</tbody>')
		                $('#list-table-res td').css('cursor','pointer');
		            } else {
		                $('#list-table-res').html('No data Available');
		            }

		            $('#modal_resource').modal('show');
                }
            }) 
   		} //
   		, retrieve : function(){
   			var params = {};

   			var tmp_ = $('#dateRange').val().split('-');
            var sdate = $.trim(tmp_[0]);
            var edate = $.trim(tmp_[1]);
            var resource_id = $('#resource_id').val();
            var hour = $.trim($("input[name=hour]:checked").map(function() { return this.value;}).get().join(","));
            var interval = $.trim($("input[name=interval]:checked").map(function() { return this.value;}).get().join(","));
            params['sdate'] = sdate;
            params['edate'] = edate;
            params['resource_id'] = resource_id;
            params['hour'] = hour;
            params['interval'] = interval;

            $('#result').removeAttr('class').html('').removeAttr('style');
            $('#result_buttons').remove();
            $('#btn_excel').unbind();
            var errors = [];
            if (resource_id.length <= 0 ) {
            	errors.push('Please select at least one resource');
            }

            if (hour.length <= 0 ) {
            	errors.push('Please select at least one Hour checkbox');
            }

            if (interval.length <= 0 ) {
            	errors.push('Please select at least one Interval checkbox');
            }

            if (errors.length > 0 ) {
            	$('#result').removeAttr('class').html('<ul>'+errors.join('')+'</ul>').attr('class','alert alert-info');

            }else {
            	$.ajax({
	                url : "/mms_data/lmp/retrieve",
	                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
	                data : params,
	                type : "POST",
	                async : false,
	                error : function(error){
	                    console.log('Error : '+error)
	                },
	                success : function(data){
	                	if (data.length != 0) {
	                		var resource_ids = $('#resource_id').val().split(',');
		                    var hour_list = $("input[name=hour]:checked").map(function() { return this.value;}).get();
		                    var interval_list = $("input[name=interval]:checked").map(function() { return this.value;}).get();

		                    var html = '<table class="table table-striped table-bordered">';
		                    html+= '<tbody>';
		                    html+='<tr><td colspan="2">Date</td>';

		                    var date_list = [];
		                    $.each( data, function( dte, per_hour_list ) {
		                    	date_list.push(dte);

		                    	var x = new Date(dte);
                    			var formatted_dte = moment(x).format('YYYYMMDD');

		                    	for (var r=0;r<resource_ids.length;r++){
		                    		html+='<td>'+formatted_dte+'</td>';
		                    	}
		                    }) // each 
		                    html+='</tr>';


		                    // second header
		                    html+='<tr><td style="max-width:90px;">Hour</td><td  style="max-width:90px;">Interval</td>';
		                    for(var d=0;d<date_list.length;d++){
		                    	for (var r=0;r<resource_ids.length;r++){
		                    		html+='<td style="max-width:110px;">'+resource_ids[r]+'</td>';
		                    	}
		                    }
		                    html+='</tr>';


		                    // content
		                    for (var h=0;h<hour_list.length;h++){
		                    	var hr = hour_list[h];
		                    	for (var i=0;i<interval_list.length;i++){

		                    		var prev_hr = hr - 1;
		                    		var int =interval_list[i];
	                    			if (int == '00' ) {
	                    				var interval = $.strPad(hr,2,'0') + ':' + int + 'H';
		                    			var interval_key = $.strPad(hr,2,'0') + ':' +int + ':00';

	                    			}else {
	                    				var interval = $.strPad(prev_hr,2,'0') + ':' + int + 'H';
		                    			var interval_key = $.strPad(prev_hr,2,'0') + ':' +int + ':00';
	                    			}


		                    		html+='<tr><td>'+hr+'</td>';
		                    		html+='<td>'+interval+'</td>';


		                    		for(var d=0;d<date_list.length;d++){
		                    			var dte = date_list[d];


				                    	for (var r=0;r<resource_ids.length;r++){
				                    		var resource_id = resource_ids[r];
				                    		var price = '';


				                    		if (interval_key === '01:00:00') {
				                    			console.log('hr' + hr );
				                    			console.log(data[dte][hr]) 
				                    		}
				                    		if ( typeof data[dte] != 'undefined' ) {	
			                    				if ( typeof data[dte][hr] != 'undefined' ) {
				                    				if ( typeof data[dte][hr][interval_key] != 'undefined' ) {
				                    					if ( typeof data[dte][hr][interval_key][resource_id] != 'undefined' ) {
				                    						price = $.formatNumberToSpecificDecimalPlaces(data[dte][hr][interval_key][resource_id]['lmp'],2);
				                    					}
				                    				}
			                    				}
		                    				}


				                    		html+='<td style="text-align:right;">'+price+'</td>';
				                    	}
				                    }


		                    		html+='</tr>';
		                    	
		                    	} // hr

		                    } // hr


		                    
		                    html+= '</tbody>';
		                    html+= '</table>';
		                    
		                    var w = $('#info_box').width() -150;
		                    $('#result').removeAttr('class').html(html).attr('style','height:160px;width:'+w+'px;overflow:auto;');


		                    var buttons = '<button class="btn btn-success" type="button" id="btn_excel">Export to Excel</button>&nbsp;';
		                    buttons+= '<button class="btn btn-success" type="button" id="btn_csv">Export to CSV</button><div id="export_info"></div>';

		                    $( "<div id='result_buttons'><br>"+buttons+"</div>" ).insertAfter( '#result');

		                    $('#btn_excel').unbind().bind('click',function(){
		                    	$.downloadFile('excel');
		                    });

		                    $('#btn_csv').unbind().bind('click',function(){
		                    	$.downloadFile('csv');
		                    });
	                	}else {
	                		$('#result').removeAttr('class').html('No available data.').attr('class','alert alert-info');
	                	}

	                    
	                }
	            });
            }
   			 
   		} //


   		,downloadFile :function(file_format){
            var params = {};

   			var tmp_ = $('#dateRange').val().split('-');
            var sdate = $.trim(tmp_[0]);
            var edate = $.trim(tmp_[1]);
            var resource_id = $('#resource_id').val();
            var hour = $.trim($("input[name=hour]:checked").map(function() { return this.value;}).get().join(","));
            var interval = $.trim($("input[name=interval]:checked").map(function() { return this.value;}).get().join(","));
            params['sdate'] = sdate;
            params['edate'] = edate;
            params['resource_id'] = resource_id;
            params['hour'] = hour;
            params['interval'] = interval;

            var errors = [];
            if (resource_id.length <= 0 ) {
            	errors.push('Please select at least one resource');
            }

            if (hour.length <= 0 ) {
            	errors.push('Please select at least one Hour checkbox');
            }

            if (interval.length <= 0 ) {
            	errors.push('Please select at least one Interval checkbox');
            }

            if (errors.length > 0 ) {
            	$('#export_info').removeAttr('class').html('<ul>'+errors.join('')+'</ul>').attr('class','alert alert-info');

            }else {
            	var params = '';
	            params+='?sdate='+sdate;
	            params+='&edate='+edate;
	            params+='&resource_id='+resource_id;
	            params+='&hour='+hour;
	            params+='&interval='+interval;
	            params+='&file_format='+file_format;
	            window.location.href = '/mms_data/lmp/file'+params;

            }


            


        } //
   });



   $(document).ready(function(){

   		
   		
   		$('input[name="dateRange"]').daterangepicker({
            singleDatePicker: false,
            showDropdowns: false
         });


   		$('#show_resources').unbind().bind('click',function(){
   			$.list_resources();
   		});


   		$('#checkallresource').unbind().bind('click',function(){
   			var hid_check_all = parseInt($('#hid_check_all').val(),10);

   			if (hid_check_all === 1) {
   				$('#list-table-res input[type="checkbox"]').attr('checked',false);
	            $('#checkallresource #btn_text').text('Check All');
	            $('#hid_check_all').val(0)
   			}else {
   				$('#list-table-res input[type="checkbox"]').attr('checked',true);
		        $('#checkallresource #btn_text').text('Uncheck All')
		        $('#hid_check_all').val(1)
   			}
   		});

  	

		$('#get_rid').click(function(){
			$('#resource_id').val('');
			var arr_res_id = Array();
		    $("#list-table-res input[type=checkbox]:checked").each(function() {
		       arr_res_id.push($(this).val());
		    });
		   	$('#resource_id').val( arr_res_id.join( ","));
		});

		$('#chk_all_hour').unbind().bind('click',function(){
            var is_checked = $('#chk_all_hour').prop('checked');
            if (is_checked) {
                $('input[name=hour]').prop('checked',true)
            }else {
                $('input[name=hour]').prop('checked',false)
            }
        });


  		$('#chk_all_interval').unbind().bind('click',function(){
            var is_checked = $('#chk_all_interval').prop('checked');
            if (is_checked) {
                $('input[name=interval]').prop('checked',true)
            }else {
                $('input[name=interval]').prop('checked',false)
            }
        });
        
		$('#btn_display').unbind().bind('click',function(){
			$.retrieve();
		});
   });
   </script>

@stop