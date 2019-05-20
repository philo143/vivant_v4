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
	            <legend>System Messages</legend>
	            <div id="info_box" class="col-md-12"></div>
	            <div class="well bs-component col-md-12">
	            	<form class="form-horizontal" id="rpc_form" method="post">
	            		{{ csrf_field() }}

						

						<div class="form-group"> 
	                        {{ Form::label('content', 'Look for :', ['class'=>'col-lg-2 control-label']) }}
	                        <div class="col-lg-5"> 
	                            {{ Form::text('content', '', ['class'=>'form-control input-sm', 'placeholder'=>'', 'required'=>'required']) }}

	                        </div>
	                    </div>

						


						<div class="form-group"> 
	                        <div class="col-lg-2" style="text-align: right; margin-top: -8px;">
	                            <span class="control-label">Urgency : </span><br>
	                            
	                        </div>
	                        <div class="col-lg-8">
	                            <table>
	                            <tr>
	                            	<td style="width:70px;"><input type="checkbox" name="urgency" value="RED" checked>&nbsp; Red<td>
	                            	<td style="width:70px;"><input type="checkbox" name="urgency" value="BLUE" checked>&nbsp; Blue<td>
	                            	<td style="width:70px;"><input type="checkbox" name="urgency" value="GREEN" checked>&nbsp; Green<td>
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
                     <table class="table table-striped table-narrow table-hover datatable" id="tbl_system_messages">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>  
                </div>
	            <br><br>

	        </div>
	    </div>
	</div>
	
@stop

@section('scripts')
   <style type="text/css">
   		
   		.red {
   			color:red;
   		}

   		.blue {
   			color:blue;
   		}

   		.green {
   			color : green;
   		}

   </style>
   <script type="text/javascript">
   var table ;

   $.extend({
   		list : function(){
   			if ( $.fn.dataTable.isDataTable( '#tbl_system_messages' ) ) {
                table = $('#tbl_system_messages').DataTable();
                table.destroy();
            }
            var table = $('#tbl_system_messages').DataTable({
                processing: true,
                serverSide: true,
                bFilter : false,
                ajax: {
                    "url": '{{ route('system_messages.data') }}',
                    "data": function ( d ) {
                        var tmp_ = $('#dateRange').val().split('-');
                        var sdate = $.trim(tmp_[0]);
                        var edate = $.trim(tmp_[1]);
                        var content = $('#content').val();
            			var urgency = $.trim($("input[name=urgency]:checked").map(function() { return this.value;}).get().join(","));

                        d.sdate = sdate;
                        d.edate = edate;
                        d.content = content;
                        d.urgency = urgency;

                    }
                },
                order: [[1, "asc"]],
                columns: [
                    {
                        data: 'date',
                        render : function(data, type, row ){
                            var dte = new Date(row.date);
                            var urgency = row.urgency.toLowerCase();
                            var formatted_dte = moment(dte).format('MM/DD/YYYY');
                            return '<span class="'+urgency+'"> ' + formatted_dte + '</span>';
                        },
                        sWidth:40
                    },
                    {
                        data: 'message',
                        render : function(data, type, row ){
                            var message = row.message;
                            var urgency = row.urgency.toLowerCase();
                            return '<span class="'+urgency+'"> ' + message + '</span>';
                        }
                    }
                ],
                fnDrawCallback : function(oSettings){
                     var total_records = $("#tbl_system_messages").DataTable().page.info().recordsTotal;

                    $('#result_buttons').remove();
            		$('#btn_excel').unbind();

                    if (total_records > 0) {
                     var buttons = '<button class="btn btn-success" type="button" id="btn_excel">Export to Excel</button>&nbsp;';
	                    $( "<div id='result_buttons'><br>"+buttons+"</div>" ).insertAfter( '#result');

	                    $('#btn_excel').unbind().bind('click',function(){
	                    	$.downloadFile('excel');
	                    });
                    }
                }
            }); 


   		} //


   		,downloadFile :function(file_format){
            var params = {};

   			var tmp_ = $('#dateRange').val().split('-');
            var sdate = $.trim(tmp_[0]);
            var edate = $.trim(tmp_[1]);
            var content = $('#content').val();
            var urgency = $.trim($("input[name=urgency]:checked").map(function() { return this.value;}).get().join(","));


            var errors = [];
            if (urgency.length <= 0 ) {
            	errors.push('Please select at least one urgency type');
            }

            
            if (errors.length > 0 ) {
            	$('#export_info').removeAttr('class').html('<ul>'+errors.join('')+'</ul>').attr('class','alert alert-info');

            }else {
            	var params = '';
	            params+='?sdate='+sdate;
	            params+='&edate='+edate;
	            params+='&content='+content;
	            params+='&urgency='+urgency;
	            params+='&file_format='+file_format;
	            window.location.href = '/mms_data/system_messages/file'+params;

            }


            


        } //
   });



   $(document).ready(function(){

   		$('input[name="dateRange"]').daterangepicker({
            singleDatePicker: false,
            showDropdowns: false
         });


   		
		$('#btn_display').unbind().bind('click',function(){
            $.list();
        });

        $('#btn_display').trigger('click');
   });
   </script>

@stop