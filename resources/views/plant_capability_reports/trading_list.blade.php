
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
    <div class="row">
        <div class="col-md-2">
            <div class="list-group">
                @include('plant_capability_reports.menu')
            </div>
        </div>
        <div class="col-md-10">
            <h4>Availability Report</h4>
            <hr>
            <div class="col-md-12">

                <div class="well bs-component col-md-12"> 
                    {{ Form::open(['class'=>'form-horizontal','id' => 'form_retrieve']) }}
                    
                     <div class="form-group"> 
                        {{ Form::label('resource_id', 'Resource ID ', ['class'=>'col-lg-2 control-label']) }}
                        <div class="col-lg-3"> 
                             {{ Form::select('resource_id', $resources, '',['class'=>'form-control input-sm']) }}
                        </div>
                    </div>



                    <div class="form-group"> 
                        {{ Form::label('type', 'Type ', ['class'=>'col-lg-2 control-label']) }}
                        <div class="col-lg-3"> 
                             {{ Form::select('type', $types, '',['class'=>'form-control input-sm']) }}
                        </div>
                    </div>


                    <div class="form-group"> 
                        {{ Form::label('dateRange', 'Date', ['class' => 'col-lg-2 control-label']) }}
                        <div class="col-sm-3">
                           {{ Form::text('dateRange','', array('class' => 'form-control input-sm', 'id' => 'dateRange')) }} 
                        </div>  
                    </div>


                    <div class="form-group"> 
                        <div class="col-lg-2">&nbsp;</div>
                        <div class="col-lg-5"> 
                            {{ Form::button('Display', ['class'=>'btn btn-primary','id' => 'btn_display']) }}

                            {{ Form::button('Export to Excel', ['class'=>'btn btn-success','id' => 'btn_xls', 'disabled' => true]) }}
                            {{ Form::button('Export to PDF', ['class'=>'btn btn-danger','id' => 'btn_pdf', 'disabled' => true]) }}
                        </div>
                    </div>
                     
                        
                    </div>

                    {{ Form::close() }}
                </div>
                <div id="retrieve_info_box"></div> 

                <div id="result">
                     <table class="table table-striped table-narrow table-hover datatable" id="tbl_capability">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Interval</th>
                                <th>Hour</th>
                                <th>Net Energy (MWH) </th>
                                <th>Remarks</th>
                                <th>Description</th>
                                <th>Source</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>  
                </div>
                


                
                
            </div>  


        </div>    
    </div>
    </div>
    <br><br>
@stop




@section('scripts')



<script>
   REPORT_TYPES = [];
    $.extend({
       list : function(){

            if ( $.fn.dataTable.isDataTable( '#tbl_capability' ) ) {
                table = $('#tbl_capability').DataTable();
                table.destroy();
            }
            var table = $('#tbl_capability').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    "url": '{{ route('availability_report.data') }}',
                    "data": function ( d ) {
                        var tmp_ = $('#dateRange').val().split('-');
                        var sdate = $.trim(tmp_[0]);
                        var edate = $.trim(tmp_[1]);

                        d.sdate = sdate;
                        d.edate = edate;
                        d.resource_id = $('#resource_id').val();
                        d.type_id = $('#type').val();

                    }
                },
                order: [[1, "asc"]],
                columns: [
                    {
                        data: 'delivery_date',
                        render : function(data, type, row ){
                            var dte = new Date(row.delivery_date);
                            var formatted_dte = moment(dte).format('MM/DD/YYYY');
                            return formatted_dte;
                        }
                    },
                    {data: 'hour'},
                    {
                        data: 'interval', orderable:false,
                        render : function ( data, type, row ) {

                                    var hr = parseInt(row.hour,10);
                                    var prev = hr - 1;
                                    var hour = $.strPad(prev,2,'0') + ':01 - ' + $.strPad(hr,2,'0') + ':00';
                                    return hour;
                                }

                    },
                    {data: 'capability', orderable:false, searchable:true},
                    {data: 'plant_capability_status.status', orderable:true, searchable:false},
                    {data: 'description', orderable:false, searchable:true},
                    {data: 'plant_capability_type.type', orderable:false, searchable:false}
                ],
                fnDrawCallback : function(oSettings){
                    var total_records = $("#tbl_capability").DataTable().page.info().recordsTotal;

                    $('#btn_pdf').attr('disabled',true);
                    $('#btn_xls').attr('disabled',true);
                    if (total_records > 0) {
                        $('#btn_pdf').removeAttr('disabled');
                        $('#btn_xls').removeAttr('disabled');
                    }
                }
            }); 
       } //

       ,downloadFile :function(file_format){
            var tmp_ = $('#dateRange').val().split('-');
            var sdate = $.trim(tmp_[0]);
            var edate = $.trim(tmp_[1]);
            
            var params = '';
            params+='?sdate='+sdate;
            params+='&edate='+edate;
            params+='&resource_id='+$('#resource_id').val();
            params+='&type_id='+$('#type').val();
            params+='&file_format='+file_format;
            window.location.href = '/trading/availability_report/file'+params;



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


         $('#btn_xls').unbind().bind('click',function(){
            $.downloadFile('excel');
        });

        $('#btn_pdf').unbind().bind('click',function(){
            $.downloadFile('pdf');
        });

         $('#btn_display').trigger('click');

    });
    
</script>
@endsection


