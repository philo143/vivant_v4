
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
    <div class="row">
        <div class="col-md-2">
            <div class="list-group">
                @include('trading_shift_reports.menu')
            </div>
        </div>
        <div class="col-md-10">
            <h4>Shift Report Extraction Page</h4>
            <hr>
            <div class="col-md-12">

                <div class="well bs-component col-md-12"> 
                    {{ Form::open(['class'=>'form-horizontal','id' => 'form_retrieve']) }}
                    
                    <div class="form-group"> 
                        {{ Form::label('dateRange', 'Date', ['class' => 'col-lg-2 control-label']) }}
                        <div class="col-sm-3">
                           {{ Form::text('dateRange','', array('class' => 'form-control input-sm', 'id' => 'dateRange')) }} 
                        </div>  
                    </div>


                    <div class="form-group"> 
                        {{ Form::label('shift_report_type', 'Shift Report', ['class' => 'col-lg-2 control-label']) }}
                        <div class="col-sm-3">
                           <select id="shift_report_type" name="shift_report_type" class="form-control input-sm">
                               <option value="all">All</option>
                               <option value="plant">Plant</option>
                               <option value="trading">Trading</option>
                           </select>
                        </div>  
                    </div>

                    <div class="form-group"  name="trading_row">
                        <div class="col-lg-2" style="text-align: right; margin-top: 15px;" >
                            {{  Form::checkbox('chk_all_traders', '', true
                                    , ['id'=>'chk_all_traders' ]) }} &nbsp;<span class="control-label">All Traders</span>
                        </div>
                        <div class="col-lg-3" id="checkbox_container" style="margin-top:10px;">
                            {{ Form::select('trader_list', $trader_users,'' ,
                                [
                                 'class'=>'form-control input-sm'
                                 ,'disabled' => true
                                 ,'id' => 'trader_list'])  }}
                        </div>
                    </div>


                    <div class="form-group" name="plant_row">
                        
                        {{ Form::label('plant', 'Plant', ['class' => 'col-lg-2 control-label']) }}
                         <div class="col-lg-3">
                           {{ Form::select('plant', $plants,'' ,
                                [
                                 'class'=>'form-control input-sm'
                                 ,'id' => 'plant'])  }}

                        </div>  
                        <div class="col-lg-3" style="margin-top: 7px;">
                            <input type="checkbox" name="chk_all_plant" id="chk_all_plant" checked="true"> <span class="control-label">All Plant</span>
                        </div>
                    </div>


                    <div class="form-group" name="plant_row">
                        
                        {{ Form::label('resource_id', 'Resource ID', ['class' => 'col-lg-2 control-label']) }}
                        <div class="col-lg-3">
                           {{ Form::select('resource_id', [],'' ,
                                [
                                 'class'=>'form-control input-sm'
                                 ,'id' => 'resource_id'])  }}
                        </div>  
                        <div class="col-lg-3" style="margin-top: 7px;">
                            <input type="checkbox" name="chk_all_resources" id="chk_all_resources" checked="true"> <span class="control-label">All Resources</span>
                        </div>
                    </div>


                     <div class="form-group" name="plant_row">
                       {{ Form::label('plant_operator_list', 'Plant Operator', ['class' => 'col-lg-2 control-label']) }}
                        <div class="col-lg-3" id="checkbox_container">
                            {{ Form::select('plant_operator_list', $plant_operators,'' ,
                                [
                                 'class'=>'form-control input-sm'
                                 ,'disabled' => true
                                 ,'id' => 'plant_operator_list'])  }}
                        </div>

                        <div class="col-lg-3" style="margin-top: 4px;">
                            <input type="checkbox" name="chk_all_plant_ops" id="chk_all_plant_ops" checked="true"> <span class="control-label">All Plant Operators</span>
                        </div>
                    </div>

                    

                    <div class="form-group"> 
                        <div class="col-lg-2" style="text-align: right; margin-top: -8px;">
                            <span class="control-label">Hour</span><br>
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
                            <span class="control-label">Interval</span><br>
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
                        {{ Form::label('report_type', 'Report Type', ['class' => 'col-lg-2 control-label']) }}
                        <div class="col-lg-8">
                            <table>
                            <tr id="report_types_container">
                            </tr>
                            </table>

                        </div>  

                    </div>


                    <div class="form-group"  name="plant_row"> 
                        {{ Form::label('island_mode', 'Island Mode', ['class' => 'col-lg-2 control-label']) }}
                        <div class="col-lg-3">
                           <select id="island_mode" name="island_mode" class="form-control input-sm">
                               <option value="">All</option>
                               <option value="1">On</option>
                               <option value="0">Off</option>
                           </select>
                        </div>  
                    </div>


                     <div class="form-group"> 
                        {{ Form::label('content', 'Content', ['class' => 'col-lg-2 control-label']) }}
                        <div class="col-lg-3">
                           {{ Form::text('content','', array('class' => 'form-control input-sm', 'id' => 'content')) }} 
                        </div>  
                    </div>


                    <div class="form-group"> 
                        <div class="col-lg-2">&nbsp;</div>
                        <div class="col-lg-8">
                            <button class="btn btn-primary" type="button" id="btn_excel_export">Export to Excel</button>

                            <button class="btn btn-danger" type="button" id="btn_pdf_export">Export to PDF</button>
                        </div>  

                    </div>


                    {{ Form::close() }}
                </div>

            </div>  


        </div>    

    </div>

    <div class="row">
        <div class="col-md-2"></div>
        <div class="col-md-10">
            <div id="retrieve_info_box">
            </div> 
            <div id="result">
                    
            </div>
        </div>
             
    </div>
    </div>
    
    <br><br>
@stop




@section('scripts')
<style type="text/css">
    #result td.big_font {
        font-weight: bold;
        font-size:15vh;
        width: 20px;
        vertical-align: middle;
        text-align: center;
    }
</style>


<script>
    TRADING_REPORT_TYPES = {};
    PLANT_REPORT_TYPES = {};
    ALL_REPORT_TYPES = {};
    $.extend({
        getTradingShiftReportTypes : function(){
            $.ajax({
                url : "/trading_shift_report_type/list",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data : {},
                type : "POST",
                async : false,
                error : function(error){
                    console.log('Error : '+error)
                },
                success : function(data){
                    for (var x=0;x<data.length;x++){
                        var cur = data[x];
                        ALL_REPORT_TYPES[cur.type] = cur.description;
                        TRADING_REPORT_TYPES[cur.type] = cur.description;
                    }
                    $.getPlantShiftReportTypes();
                }
            }); 
        }//

        ,getPlantShiftReportTypes : function(){
            $.ajax({
                    url : "/plant_shift_report_type/list",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data : {},
                    type : "POST",
                    async : false,
                    error : function(error){
                        console.log('Error : '+error)
                    },
                    success : function(data){

                        for (var x=0;x<data.length;x++){
                            var cur = data[x];
                            PLANT_REPORT_TYPES[cur.type] = cur.description;

                            if (typeof ALL_REPORT_TYPES[cur.type] == 'undefined') {
                                ALL_REPORT_TYPES[cur.type] = cur.description;
                            }
                            
                            
                        }
                    }
                }) 
        } //
        ,populateReportTypes : function(){
            var shift_report_type = $('#shift_report_type').val();
            var list = {};

            if (shift_report_type === 'all') {
                list = ALL_REPORT_TYPES;
            }else if (shift_report_type === 'trading') {
                list = TRADING_REPORT_TYPES;
            }else if (shift_report_type === 'plant') {
                list = PLANT_REPORT_TYPES;
            }

            var html = '';
            $('#report_types_container').html('');
            for (var type in list) {
                var description = list[type];
                html += '<td style="padding-right:10px;">';
                html += '<input type="checkbox" name="report_type" value="'+type+'" checked>&nbsp;'+description+'</td>';
            }
            $('#report_types_container').html(html);


        } //

        ,list_unit : function(){
            var params = {'plant_id':$('#plant').val()};


            $.ajax({
                url : "/resources/list_by_plant_id",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data : params,
                type : "POST",
                error : function(error){
                    console.log('Error : '+error)
                },
                success : function(data){
                     $('#resource_id').html('');
                    var html = '';
                    for (var i=0;i<data.length;i++){
                        html+='<option value="'+data[i].id+'">'+data[i].resource_id+'</option>';
                    }
                    $('#resource_id').html(html);
                    $('#resource_id').trigger('change');
                }
            })                
        }//

        , generateFileLink : function(file_format){
            var params = {};
            var tmp_ = $('#dateRange').val().split('-');
            var sdate = $.trim(tmp_[0]);
            var edate = $.trim(tmp_[0]);
            var shift_report_type = $('#shift_report_type').val();
            var island_mode = $('#island_mode').val();
            var traders = '';
            if ( !$('#chk_all_traders').prop('checked') ) {
                traders = $('#trader_list').val();
            }

            var plant_id = '';
            if ( !$('#chk_all_plant').prop('checked') ) {
                plant_id = $('#plant').val();
            }

            var resource_id = '';
            if ( !$('#chk_all_resources').prop('checked') ) {
                resource_id = $('#resource_id').val();
            }

            var plant_operator = '';
            if ( !$('#chk_all_plant_ops').prop('checked') ) {
                plant_operator = $('#plant_operator_list').val();
            }

            var hour = $.trim($("input[name=hour]:checked").map(function() { return this.value;}).get().join(","));

            var interval = $.trim($("input[name=interval]:checked").map(function() { return this.value;}).get().join(","));

            var report_type = $.trim($("input[name=report_type]:checked").map(function() { return this.value;}).get().join(","));

            var content = $('#content').val();

            var errors = [];
            if (hour.length <= 0 ) {
                errors.push('<li>Please select at least one hour</li>')
            }

            if (interval.length <= 0 ) {
                errors.push('<li>Please select at least one interval</li>')
            }

            if (errors.length > 0 ) {
                $('#result').html(errors.join('<br>')).removeAttr('class').addClass('alert alert-warning');
            }else {
                var params = {};
                params['sdate'] = sdate;
                params['edate'] = edate;
                params['shift_report_type'] = shift_report_type;
                params['traders'] = traders;
                params['plant_id'] = plant_id;
                params['resource_id'] = resource_id;
                params['plant_operator'] = plant_operator;
                params['hour'] = hour;
                params['interval'] = interval;
                params['report_type'] = report_type;
                params['content'] = content;
                params['file_format'] = file_format;
                params['island_mode'] = island_mode;

                $.ajax({
                    url : "/trading/shift_report/extractCheckData",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data : params,
                    type : "POST",
                    error : function(error){
                        console.log('Error : '+error)
                    },
                    success : function(data){

                        if (data.success === 1) {
                            var msg = '<a id="lnk_file" style="cursor:pointer; color:#ffffff;">'+data.message+'</a>';
                            $('#result').html(msg).removeAttr('class').addClass('alert alert-success');

                            $('#lnk_file').unbind().bind('click',function(){
                                var extension = $('#lnk_file').html().split('.')[1];
                                var file_format = extension === 'xlsx' ? 'excel' : 'pdf';
                                $.downloadFile(file_format);
                            });
                        }else {
                            $('#result').html(data.message).removeAttr('class').addClass('alert alert-info');
                        }
                        
                    }
                }) ;    
            }

              
        } //

        ,downloadFile :function(file_format){
            var params = {};
            var tmp_ = $('#dateRange').val().split('-');
            var sdate = $.trim(tmp_[0]);
            var edate = $.trim(tmp_[0]);
            var shift_report_type = $('#shift_report_type').val();
            var island_mode = $('#island_mode').val();
            var traders = '';
            if ( !$('#chk_all_traders').prop('checked') ) {
                traders = $('#trader_list').val();
            }

            var plant_id = '';
            if ( !$('#chk_all_plant').prop('checked') ) {
                plant_id = $('#plant').val();
            }

            var resource_id = '';
            if ( !$('#chk_all_resources').prop('checked') ) {
                resource_id = $('#resource_id').val();
            }

            var plant_operator = '';
            if ( !$('#chk_all_plant_ops').prop('checked') ) {
                plant_operator = $('#plant_operator_list').val();
            }

            var hour = $.trim($("input[name=hour]:checked").map(function() { return this.value;}).get().join(","));

            var interval = $.trim($("input[name=interval]:checked").map(function() { return this.value;}).get().join(","));

            var report_type = $.trim($("input[name=report_type]:checked").map(function() { return this.value;}).get().join(","));

            var content = $('#content').val();

            var params = '';
            params+='?sdate='+sdate;
            params+='&edate='+edate;
            params+='&shift_report_type='+shift_report_type;
            params+='&traders='+traders;
            params+='&plant_id='+plant_id;
            params+='&resource_id='+resource_id;
            params+='&plant_operator='+plant_operator;
            params+='&hour='+hour;
            params+='&interval='+interval;
            params+='&report_type='+report_type;
            params+='&content='+content;
            params+='&file_format='+file_format;
            params+='&island_mode='+island_mode;
            window.location.href = '/trading/shift_report/extractFile'+params;



        } //
        ,retrievePlantOps : function(){
            var plant = $('#plant').val();
            var is_all_plant = $('#chk_all_plant').prop('checked') ? 1 : 0 ;
            $.ajax({
                url : "/plant/shift_report/operatorList",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data : {'plant' : plant , 'is_all_plant' : is_all_plant},
                type : "POST",
                async : false,
                error : function(error){
                    console.log('Error : '+error)
                },
                success : function(data){
                    $('#plant_operator_list').html('');
                    var html = '';
                    for (var x=0;x<data.length;x++){
                        html+='<option value="'+data[x].id+'">'+data[x].fullname+'</option>'
                    }
                    $('#plant_operator_list').html(html);
                }
            }) 
        }
    });

    $(document).ready(function(){
         $('input[name="dateRange"]').daterangepicker({
            singleDatePicker: false,
            showDropdowns: false
         })
            
         $('#shift_report_type').unbind().bind('change',function(){
            $.populateReportTypes();

            var shift_report_type = $('#shift_report_type').val();

            $('div[name=plant_row]').hide();
            $('div[name=trading_row]').hide();
            if (shift_report_type === 'all') {
                $('div[name=plant_row]').show();
                $('div[name=trading_row]').show();

            }else if (shift_report_type === 'trading') {
                $('div[name=trading_row]').show();


            }else if (shift_report_type === 'plant') {
                $('div[name=plant_row]').show();
            }


            // default form inputs
            $('#chk_all_traders').prop('checked', true);
            $('#trader_list').attr('disabled',true);
            $('#trader_list option:first-child').prop('selected', true);


            $('#chk_all_plant').prop('checked', true);
            $('#plant').attr('disabled',true);
            $('#plant option:first-child').prop('selected', true);
            $('#plant').trigger('change');

            $('#chk_all_resources').prop('checked', true);
            $('#resource_id').attr('disabled',true);
            $('#resource_id option:first-child').prop('selected', true);

            $('#chk_all_plant_ops').prop('checked', true);
            $('#plant_operator_list').attr('disabled',true);
            $('#plant_operator_list option:first-child').prop('selected', true);
         });
         $.getTradingShiftReportTypes();   
         

         $('#plant').unbind().bind('change',function(){
            $.list_unit();
             $.retrievePlantOps();
         });


 


         $('#chk_all_traders').unbind().bind('click',function(){
            var is_checked = $('#chk_all_traders').prop('checked');
            if (is_checked) {
                $('#trader_list').attr('disabled',true);
            }else {
                $('#trader_list').removeAttr('disabled');
            }
         });

         $('#chk_all_plant').unbind().bind('click',function(){
            var is_checked = $('#chk_all_plant').prop('checked');
            if (is_checked) {
                $('#plant').attr('disabled',true);
            }else {
                $('#plant').removeAttr('disabled');
            }
            $.retrievePlantOps();
        });


        $('#chk_all_resources').unbind().bind('click',function(){
            var is_checked = $('#chk_all_resources').prop('checked');
            if (is_checked) {
                $('#resource_id').attr('disabled',true);
            }else {
                $('#resource_id').removeAttr('disabled');
            }
        });
        
        $('#chk_all_plant_ops').unbind().bind('click',function(){
            var is_checked = $('#chk_all_plant_ops').prop('checked');
            if (is_checked) {
                $('#plant_operator_list').attr('disabled',true);
            }else {
                $('#plant_operator_list').removeAttr('disabled');
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

        $('#btn_excel_export').unbind().bind('click',function(){
            $.generateFileLink('excel');
        });

        $('#btn_pdf_export').unbind().bind('click',function(){
            $.generateFileLink('pdf');
        });
         
        $('#shift_report_type').trigger('change');
        
        $('#plant').trigger('change');
    });
    
</script>
@endsection


