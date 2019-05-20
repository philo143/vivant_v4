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
	            <legend>Reserve Prices</legend>
	            <div class="well bs-component col-md-12" id="frm">
	            	<form class="form-horizontal" id="rpc_form" method="post">
	            		{{ csrf_field() }}

						
                        <div class="form-group"> 
                            {{ Form::label('region', 'Region ', ['class'=>'col-lg-2 control-label']) }}
                            <div class="col-lg-3"> 
                                 {{ Form::select('region', $regions, '',['class'=>'form-control input-sm','id'=> 'region']) }}
                            </div>
                        </div>


                        <div class="form-group"> 
                            {{ Form::label('region', 'Reserve Class ', ['class'=>'col-lg-2 control-label']) }}
                            <div class="col-lg-3"> 
                                 {{ Form::select('reserve_class', $reserve_types, 'DIS',['class'=>'form-control input-sm','id'=> 'reserve_class']) }}
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


	            <div id="info_box"></div>
	            <div class="well bs-component col-md-12" id="chart_box" style="height:400px; display:none; padding-bottom: 30px;">
                    <center id="canvas_title" style="text-align: center; font-size:16px; font-weight: bold; padding: 6px;">
                    	Reserve Schedules
                    </center>
                    <canvas id="myChart" ></canvas>
                    <br>
                    <div id="js-legend" class="chart-legend"></div>
                </div>

                <div id="export_info"></div>
                <div id="grid_box" style="max-height:400px; display:none;">
                    <table class="table table-striped table-bordered" id="tbl_data">
                        <tr>
                            <td></td>
                        </tr>
                    </table>
                </div>

                
                 <br> <br> 
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
   		#tbl_data tr:first-child th {
		    text-align: center;
		    font-weight: bold;
            min-width : 80px;
		}



   </style>
   <script type="text/javascript">
   var myLineChart;
   
   var randomColorGenerator = function () { 
    return '#' + (Math.random().toString(16) + '0000000').slice(2, 8); 
   };

   $.extend({
   		list_resources  : function(){
            $.ajax({
                url : "/resources_lookup/list",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data : {'is_mms_reserve' : 1, 'region' : $('#region').val(), 'reserve_class' : $('#reserve_class').val()},
                type : "POST",
                async : false,
                error : function(error){
                    console.log('Error : '+error)
                },
                success : function(data){
                    console.log(data.length)
                	if (data !== null  && data.length > 0 ) { 
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
		            } else if ( data.length == 0 ) {
                        $('#list-table-res').html('No available resource');
                    } else {
		                $('#list-table-res').html('No available resource');
		            }

                    $('#modal_resource').modal('show');
                }
            }) 
   		} //
   		, retrieve : function(){
   			var params = {};

            var date = $('#dateRange').val()
            var resource_id = $('#resource_id').val();
            var hour = $.trim($("input[name=hour]:checked").map(function() { return this.value;}).get().join(","));
            var reserve_class = $('#reserve_class').val();
            var region = $('#region').val();
            params['date'] = date;
            params['resource_id'] = resource_id;
            params['hour'] = hour;
            params['reserve_class'] = reserve_class;
            params['region'] = region;

            $('#info_box').removeAttr('class').html('').removeAttr('style');
            $('#export_info').removeAttr('class').html('').removeAttr('style');
            $('#chart_box').hide();
            $('#grid_box').hide();
            $('#result_buttons').remove();
            $('#btn_excel').unbind();
            var errors = [];
            if (resource_id.length <= 0 ) {
            	errors.push('Please select at least one resource');
            }

            if (hour.length <= 0 ) {
            	errors.push('Please select at least one Hour checkbox');
            }

            
            if (errors.length > 0 ) {
            	$('#info_box').removeAttr('class').html('<ul>'+errors.join('')+'</ul>').attr('class','alert alert-info');

            }else {

            	if ( typeof myLineChart !== 'undefined') {
	                myLineChart.destroy();
	            }

            	$.ajax({
	                url : "/mms_data/reserve_prices/retrieve",
	                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
	                data : params,
	                type : "POST",
	                async : false,
	                error : function(error){
	                    console.log('Error : '+error)
	                },
	                success : function(data){

	                	if (data.length != 0) {
                            $('#info_box').html('').removeAttr('class');
                            $('#chart_box').show();
                            $('#grid_box').show();
	                		$.generateGrid(data);
	                		$.generateChart(data);


                             var buttons = '<button class="btn btn-success" type="button" id="btn_excel">Export to Excel</button>&nbsp;';

                            $( "<div id='result_buttons'><br>"+buttons+"</div>" ).insertAfter( '#export_info');

                            $('#btn_excel').unbind().bind('click',function(){
                                $.downloadFile();
                            });
	                	}else {
	                		$('#info_box').removeAttr('class').html('No available data.').attr('class','alert alert-info');
	                	}

	                    
	                }
	            });
            }
   			 
   		} //

        ,generateGrid : function(data){
            var source = ['mms','ngcp'];
            var headers = $("input[name=hour]:checked").map(function() { return this.value;}).get();
           
            var html = '';
            html = '<thead>';
            html+= '<tr>';
            html+= '<th style="text-align:center;">Date</th>';
            html+= '<th style="text-align:center;">Resource ID</th>';
            html+= '<th style="text-align:center;">Source</th>';
            html+= '<th style="text-align:center;">Area ID</th>';
            html+= '<th style="text-align:center;">Reserve Class</th>';
            

            var totals = {};
            totals['NGCP'] = {};
            totals['MMS'] = {};

            for(var h=0;h<headers.length;h++){
                html+= '<th style="text-align:center;">H'+headers[h]+'</th>';
                totals['NGCP']['h_'+headers[h]] = 0;
                totals['MMS']['h_'+headers[h]] = 0;
            }
            html+= '</tr></thead>';
            html+= '<tbody>';


            $.each(data, function(key, rows) {
                var tmp = key.split('|');
                var date = tmp[0];
                var resource_id = tmp[1];
                var reserve_class = tmp[2];
                var area_id = tmp[3];
                var source = tmp[4];

                var x = new Date(date);
                var date_label = moment(x).format('YYYYMMDD');

                html+='<tr>';
                html+='<td style="text-align:center;">'+date_label+'</td>';
                html+='<td style="text-align:center;">'+resource_id+'</td>';
                html+='<td style="text-align:center;">'+source+'</td>';
                html+='<td style="text-align:center;">'+area_id+'</td>';
                html+='<td style="text-align:center;">'+reserve_class+'</td>';
                

                var data_row = [];
                for (var i=0;i<headers.length;i++){
                    var hr = headers[i];
                    var price = '';
                    var price_val = 0;
                    if (  typeof rows[hr] != 'undefined' ) {
                        price = $.formatNumberToSpecificDecimalPlaces(rows[hr].price,2);
                        price_val  =  parseFloat(rows[hr].price);
                    } 


                    var ctotal = totals[source]['h_'+hr];
                    var ntotal = ctotal + price_val
                    totals[source]['h_'+hr] = ntotal;

                    html+='<td style="text-align:right;">'+price+'</td>';
                }
                html+='</tr>';

            });



            html+= '</tbody>';
            $('#tbl_data').html(html);

            $('#tbl_data').DataTable({
                scrollY:        300,
                destroy: true,
                scrollX:        true,
                scrollCollapse: true,
                paging:         false,
                autoWidth:      false,
                searching:      false,
                bSort:          false,
                fixedColumns:   {
                    leftColumns: 5
                }
            });

            //$('#grid_box').css('width',$('#frm').width()+30 + 'px').css('overflow','auto');
        }
   		,generateChart : function(data){
            var labels = $("input[name=hour]:checked").map(function() { return this.value;}).get();
            var my_data_sets = [];

            $.each(data, function(key, rows) {
                var tmp = key.split('|');
                var date = tmp[0];
                var resource_id = tmp[1];
                var reserve_class = tmp[2];
                var area_id = tmp[3];
                var source = tmp[4];

                var data_row = [];
                for (var i=0;i<labels.length;i++){
                	var hr = labels[i];
                	var price = null;
                	if (  typeof rows[hr] != 'undefined' ) {
                		price = rows[hr].price;
                	} 
                	data_row.push(price);
                }

                var color = randomColorGenerator();
                var dataset_row = {
                	'label' : resource_id + ' ' + reserve_class + ' ' + source,
                	borderColor: color,
                	backgroundColor : color,
        			fill: false, 
                	'data' : data_row
                };

                my_data_sets.push(dataset_row);
            });

            
            
            var data_chart = {
                labels: labels,
		        datasets: my_data_sets
            };
            
            if ( typeof myLineChart !== 'undefined') {
                myLineChart.destroy();

            }
            
           
		     var ctx = document.getElementById("myChart");
			 

             myLineChart = Chart.Line(ctx, {
                data: data_chart,
                options: {
                    responsive: true, 
					maintainAspectRatio: false,
                    title: {
                        display: false,
                        text: 'Reserve Prices'
                    },
                    legend: {
                        display: true,
                         position: 'bottom',
                         fullWidth : true
                    },
                    tooltips: {
                        enabled : true,
                        displayColors: false,
                        mode: 'x',
                    },
                    scales: {
                        xAxes: [{
                            ticks: {
                                autoSkip: false,
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }],
                        yAxes: [{
                          scaleLabel: {
                            display: true,
                            labelString: ''
                          },
                          ticks: {
                                callback: function(label, index, labels) {
                                    return $.formatNumberToSpecificDecimalPlaces(label,2);
                                }
                            }
                        }]
                    },
                    scaleLabel: function (value) {
                        return Number(valuevaluePayload.value).toFixed(2).replace('.',',') + '$';
                    }
                }
            });

   		}

   		,downloadFile :function(){
            var date = $('#dateRange').val()
            var resource_id = $('#resource_id').val();
            var hour = $.trim($("input[name=hour]:checked").map(function() { return this.value;}).get().join(","));
            

            var errors = [];
            if (resource_id.length <= 0 ) {
            	errors.push('Please select at least one resource');
            }

            if (hour.length <= 0 ) {
            	errors.push('Please select at least one Hour checkbox');
            }

            

            if (errors.length > 0 ) {
            	$('#export_info').removeAttr('class').html('<ul>'+errors.join('')+'</ul>').attr('class','alert alert-info');

            }else {
            	var params = '';
                var reserve_class = $('#reserve_class').val();
                var region = $('#region').val();
	            params+='?date='+date;
	            params+='&resource_id='+resource_id;
	            params+='&hour='+hour;
	            params+='&reserve_class='+reserve_class;
                params+='&region='+region;
	            window.location.href = '/mms_data/reserve_prices/file'+params;

            }


            


        } //
   });



   $(document).ready(function(){

   		$('#show_resources').unbind().bind('click',function(){
            $.list_resources();
        });
   		
   		$('input[name="dateRange"]').daterangepicker({
            singleDatePicker: true,
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