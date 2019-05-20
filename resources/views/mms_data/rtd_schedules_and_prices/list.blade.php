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
	            <legend>RTD Schedules and Prices</legend>
	            <div id="info_box" class="col-md-12"></div>
	            <div class="well bs-component col-md-12">
	            	<form class="form-horizontal" id="rpc_form" method="post">
	            		{{ csrf_field() }}
						<div class="form-group"> 
	                        {{ Form::label('resources', 'Resources : ', ['class' => 'col-lg-2 control-label']) }}
	                        <div class="col-lg-5 input-group" style="padding-left:15px;">
								<input type="text" readonly="true" name="resource_id" id="resource_id" class="form-control input-sm">
								<a href="#" data-target="#modal_resource" data-toggle="modal" id="show_resources" class="input-group-addon"><i class="glyphicon glyphicon-th"></i></a>
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
                        {{ Form::label('ticker', 'Columns : ', ['class' => 'col-lg-2 control-label']) }}
                        <div class="col-lg-5 input-group" style="padding-left:15px;">
							<input type="checkbox" name="columns" id="is_show_schedule" value="1" checked>&nbsp;<span class="control-label">Schedule</span>
							&nbsp;&nbsp;&nbsp;
							<input type="checkbox" name="columns" id="is_show_price" value="1" checked>&nbsp;<span class="control-label"> Price </span>
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
			        <h4 class="modal-title" id="modal_resourceLabel">Choose a Resource ID <button id="checkallresource" class="btn btn-default btn-xs" style="float:right; margin-right: 10px;"><i class="icon-ok"></i><span id="btn_text">Check All Resources</span></button>&nbsp;&nbsp;</h4>

		      	</div>
		      	<div class="modal-body" style="max-height: 300px; overflow: auto;">
        			<table id="list-table-res" class="table table-striped"></table>
		      	</div>
		      	<div class="modal-footer">
		      		<button id="get_rid" class="btn btn-info" data-dismiss="modal" aria-hidden="true">Ok</button>
		        	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		      	</div>
		    </div>
		 </div>
	</div>
@stop

@section('scripts')
   <style type="text/css">
   		#result th {
		    text-align: center;
		    font-weight: bold;
		}



   </style>
   <script type="text/javascript">

   $.extend({
   		list_resources  : function(){
            $.ajax({
                url : "/resources_lookup/list",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data : {'is_own_resources' : 1},
                type : "POST",
                async : false,
                error : function(error){
                    console.log('Error : '+error)
                },
                success : function(data){
                    if (data !== null ) { 
		                var resource='', html = '', x=0;
		                html = '<tr>';

		                $.each(data, function(i, val) {
		                    x++;	

		                    html+='<td id='+val.resource_id+'><input type="checkbox" id="r_id" name="r_id[]" value="'+val.resource_id+'">&nbsp;'+val.resource_id+'</td>';
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

            var columns = $.trim($("input[name=columns]:checked").map(function() { return this.value;}).get().join(","));

            var is_show_schedule = $("input[id=is_show_schedule]:checked").length > 0 ? 1 : 0;
            var is_show_price = $("input[id=is_show_price]:checked").length > 0 ? 1 : 0;

            params['sdate'] = sdate;
            params['edate'] = edate;
            params['resource_id'] = resource_id;
            params['hour'] = hour;
            params['interval'] = interval;
            params['is_show_schedule'] = is_show_schedule;
            params['is_show_price'] = is_show_price;

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

            if (columns.length <= 0 ) {
            	errors.push('Please select at least one data columns to display');
            }

            if (errors.length > 0 ) {
            	$('#result').removeAttr('class').html('<ul>'+errors.join('')+'</ul>').attr('class','alert alert-info');

            }else {
            	$.ajax({
	                url : "/mms_data/rtd_schedules/retrieve",
	                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
	                data : params,
	                type : "POST",
	                async : false,
	                error : function(error){
	                    console.log('Error : '+error)
	                },
	                success : function(data){
	                	var rtd_data = data.rtd_data;
	                	var delivery_date_list = data.delivery_date_list;
	                	var resource_id_list = data.resource_id_list;
	                	var price_data = data.price_data;
	                	var is_show_schedule = $("input[id=is_show_schedule]:checked").length > 0 ? 1 : 0;
            			var is_show_price = $("input[id=is_show_price]:checked").length > 0 ? 1 : 0;
            			var columns_colspan = 1;
            			if (is_show_schedule === 1 && is_show_price ===1 ) {
            				columns_colspan = 2;
            			}
	                	if (delivery_date_list.length != 0) {
	                		//var resource_ids = $('#resource_id').val().split(',');
		                    var hour_list = $("input[name=hour]:checked").map(function() { return this.value;}).get();
		                    var interval_list = $("input[name=interval]:checked").map(function() { return this.value;}).get();

		                    var html = '<table class="table table-striped table-bordered" id="list">';
		                    html+= '<thead>';
		                    html+='<tr><th rowspan="2" style="min-width:70px;">Date</th>';
		                    html+='<th rowspan="2" style="min-width:70px;">Hour</th>';
		                    html+='<th rowspan="2" style="min-width:70px;">Interval</th>';			

		                    // resource id column
		                    var resource_ids = []; 
		                    var header2 = '';
		                    for(var r=0;r<resource_id_list.length;r++){
		                    	var resource_id = resource_id_list[r];

		                    	html+='<th colspan="'+columns_colspan+'"  style="min-width:70px;">'+resource_id+'</th>';
		                    	if ( is_show_schedule ) {
		                    		header2 += '<th>Sched</th>';
		                    	}

		                    	if ( is_show_price ) {
		                    		header2 += '<th>Price</th>';
		                    	}
                				
                				
		                    }
		                    html+='<th  colspan="'+columns_colspan+'" style="min-width:70px;">Total</th>';
		                    html+='</tr>';


		                    if ( is_show_schedule ) {
	                    		header2 += '<th>Sched</th>';
	                    	}

	                    	if ( is_show_price ) {
	                    		header2 += '<th>Price</th>';
	                    	}
		                    html+= '<tr>' + header2 + '</tr>';


		                    html+= '</thead>';
		                    html+= '<tbody>';
		                    for(var d=0;d<delivery_date_list.length;d++){
		                    	var delivery_date = delivery_date_list[d];

		                    	$.each( hour_list, function( x, hr ) {
		                    		$.each( interval_list, function( xx, int ) {

		                    			var prev_hr = hr - 1;
		                    			if (int == '00' ) {
		                    				var int_formatted =  $.strPad(hr,2,'0') + ':' +int + ':00';
		                    			}else {
		                    				var int_formatted =  $.strPad(prev_hr,2,'0') + ':' +int + ':00';
		                    			}

		                    			html+='<tr><td style="text-align:center;">'+delivery_date +'</td>';
		                    			html+='<td style="text-align:center;">'+hr +'</td>';
		                    			html+='<td style="text-align:center;">'+int_formatted +'</td>';

		                    			var mw = '', price = '';
		                    			var total_mw = 0; total_price = 0;

		                    			for (var r=0;r<resource_id_list.length;r++){
											var res_id = resource_id_list[r];
	            							

											// ######  check schedule data 
											if ( typeof rtd_data[delivery_date] != 'undefined' ) {
												if ( typeof rtd_data[delivery_date][hr] != 'undefined' ) {
													if ( typeof rtd_data[delivery_date][hr][int_formatted] != 'undefined' ) {
														var per_interval_rtd_data = rtd_data[delivery_date][hr][int_formatted];

														if (typeof per_interval_rtd_data[res_id] != 'undefined') {
					                    					mw = $.formatNumberToSpecificDecimalPlaces(per_interval_rtd_data[res_id].mw,2);
					                    					total_mw = total_mw + parseFloat(per_interval_rtd_data[res_id].mw);
					                    				}
													}
												}
											}


											// ######  check price data 
											if ( typeof price_data[delivery_date] != 'undefined' ) {
												if ( typeof price_data[delivery_date][hr] != 'undefined' ) {
													if ( typeof price_data[delivery_date][hr][int_formatted] != 'undefined' ) {
														var per_interval_price_data = price_data[delivery_date][hr][int_formatted];

														if (typeof per_interval_price_data[res_id] != 'undefined') {
					                    					price = $.formatNumberToSpecificDecimalPlaces(per_interval_price_data[res_id].lmp,2);
					                    					total_price = total_price + parseFloat(per_interval_price_data[res_id].lmp);
					                    				}
													}
												}
											}



											if ( is_show_schedule ) {
					                    		html+='<td style="text-align:right;">'+mw+'</td>';
					                    	}

					                    	if ( is_show_price ) {
					                    		html+='<td style="text-align:right;">'+price+'</td>';
					                    	}
	            									                    				
		                    				
	            							

	            						} // for resources


	            						// total column

	            						if ( is_show_schedule ) {
				                    		html+='<td style="text-align:right;">'+$.formatNumberToSpecificDecimalPlaces(total_mw,2)+'</td>';
				                    	}

				                    	if ( is_show_price ) {
				                    		html+='<td style="text-align:right;">'+$.formatNumberToSpecificDecimalPlaces(total_price,2)+'</td>';
				                    	}	                    			
		                    			html+'</tr>';


									}); // interval_list



								}); // hour_list
		                    	
		                    }



		                    
		                    html+= '</tbody>';
		                    html+= '</table>';
		                    
		                    var w = $('#info_box').width() -150;
		                    $('#result').removeAttr('class').html(html);


		                    var buttons = '<button class="btn btn-success" type="button" id="btn_excel">Export to Excel</button>&nbsp;';
		                    buttons+= '<button class="btn btn-success" type="button" id="btn_csv">Export to CSV</button><div id="export_info"></div>';

		                    $( "<div id='result_buttons'><br>"+buttons+"</div>" ).insertAfter( '#result');

		                    $('#btn_excel').unbind().bind('click',function(){
		                    	$.downloadFile('excel');
		                    });

		                    $('#btn_csv').unbind().bind('click',function(){
		                    	$.downloadFile('csv');
		                    });


		                    $('#list').DataTable({
	                    		scrollY:        300,
						        scrollX:        true,
						        scrollCollapse: true,
						        paging:         false,
						        autoWidth: 		false,
						        searching: 		false,
						        bSort: 			false,
						        fixedColumns:   {
						            leftColumns: 3
						        }
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
            var columns = $.trim($("input[name=columns]:checked").map(function() { return this.value;}).get().join(","));

            var is_show_schedule = $("input[id=is_show_schedule]:checked").length > 0 ? 1 : 0;
            var is_show_price = $("input[id=is_show_price]:checked").length > 0 ? 1 : 0;
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

            if (columns.length <= 0 ) {
            	errors.push('Please select at least one data columns to display');
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
	            params+='&is_show_schedule='+is_show_schedule;
	            params+='&is_show_price='+is_show_price;
	            window.location.href = '/mms_data/rtd_schedules/file'+params;

            }


            


        } //
   });



   $(document).ready(function(){

   		$.list_resources();
   		
   		$('input[name="dateRange"]').daterangepicker({
            singleDatePicker: false,
            showDropdowns: true
         });


   		$('#checkallresource').unbind().bind('click',function(){
   			var hid_check_all = parseInt($('#hid_check_all').val(),10);

   			if (hid_check_all === 1) {
   				$('#list-table-res input[type="checkbox"]').attr('checked',false);
	            $('#checkallresource #btn_text').text('Check All Resources');
	            $('#hid_check_all').val(0)
   			}else {
   				$('#list-table-res input[type="checkbox"]').attr('checked',true);
		        $('#checkallresource #btn_text').text('Uncheck All')
		        $('#hid_check_all').val(1)
   			}
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


		$('#get_rid').click(function(){
			$('#resource_id').val('');
			var arr_res_id = Array();
		    $("#list-table-res input[type=checkbox]:checked").each(function() {
		       arr_res_id.push($(this).val());
		    });
		   	$('#resource_id').val( arr_res_id.join( ","));
		});


		$('#btn_display').unbind().bind('click',function(){
			$.retrieve();
		});
   });
   </script>

@stop