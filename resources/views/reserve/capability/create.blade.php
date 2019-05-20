
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
    <div class="row">
        <div class="col-md-2">
            <div class="list-group">
                @include('reserve.menu')
            </div>
        </div>
        <div class="col-md-10">
            <h4>Reserve Capability Submission</h4>
            
            <hr>
            <div class="col-md-12">
                
                @if ( count($errors) )
                    <div class="alert alert-danger col-md-12">
                        <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{$error}}</li>
                        @endforeach
                        </ul>
                    </div>
                @endif
               


               

                <div class="well bs-component col-md-12"> 
                    {{ Form::open(['class'=>'form-horizontal','id' => 'form_retrieve']) }}


                    <div class="form-group"> 
                        {{ Form::label('delivery_date', 'Target Date:', ['class'=>'col-lg-2 control-label']) }}
                        <div class="col-lg-5"> 
                            {{ Form::text('delivery_date', $date, ['class'=>'form-control input-sm', 'placeholder'=>'Target Date', 'required'=>'required']) }}

                        </div>
                        
                    </div>


                    <div class="form-group"> 
                        {{ Form::label('plant_id', 'Plant:', ['class'=>'col-lg-2 control-label']) }}
                        <div class="col-lg-5"> 
                            <select class="form-control input-sm" id="plant_id" name="plant_id">
                                @foreach($plants as $id => $plant)
                                    <option value="{{ $id }}">{{ $plant }}</option>
                                @endforeach
                            </select>   
                        </div>
                    </div>

                    <div class="form-group"> 
                        {{ Form::label('unit', 'Unit :', ['class'=>'col-lg-2 control-label']) }}
                        <div class="col-lg-5"> 
                            {{ Form::select('unit', [], '',['class'=>'form-control input-sm', 'id' => 'unit']) }}
                        </div>
                    </div>


                     <div class="form-group"> 
                        {{ Form::label('reserve_class', 'Reserve Class :', ['class'=>'col-lg-2 control-label']) }}
                        <div class="col-lg-5"> 
                            <input type="text" name="reserve_class" id="reserve_class" value="" readonly="true" class="form-control input-sm">
                        </div>
                    </div>

                    <div class="form-group"> 
                        <div class="col-lg-2 control-label">&nbsp;</div>
                        <div class="col-lg-5"> 
                            <button type="button" class="btn btn-primary" id="btn_retrieve">Retrive</button>
                        </div>
                    </div>
                    
                    {{ Form::close() }}

                </div>

                <div class="well bs-component col-md-12" id="result" style="display: none;"> 
                    {{ Form::open(['class'=>'form-horizontal','id' => 'form_populate']) }}


                    <div class="form-group"> 
                        {{ Form::label('mw', 'MW :', ['class'=>'col-lg-2 control-label']) }}
                        <div class="col-lg-5"> 
                            <input type="text" name="populate" id="populate" value="1-24, 96.5;" class="form-control input-sm">
                        </div>
                        <div class="col-lg-5"> 
                            <button type="button" class="btn btn-primary btn-sm" id="btn_update">Update</button>
                            <button type="button" class="btn btn-primary btn-sm" id="btn_clear">Clear</button>
                        </div>
                    </div>
                    {{ Form::close() }}


                    <div>
                    <span id="reserve_schedules_title">Reserve Schedules for <b>Resource Name</b></span>
                    
                    <div style="margin-top:10px;">
                        <table class="table table-striped table-bordered" style="width:30%;">
                            <tr>
                                <td style="width:12%;">Interval</td>
                                <td>MW</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="text-align: left;"><input type="checkbox" name="chk_all_hour" id="chk_all_hour">&nbsp; All</td>
                            </tr>
                             @for ($i = 1; $i <= 24; $i++)

                                <tr>
                                    <td><input type="checkbox" name="hour" id="hour_{{$i}}" > {{ $i }}</td>
                                    <td><input type="text" name="mw_{{$i}}" id="mw_{{$i}}" class="form-control input-sm numeric"></td>
                                </tr>

                            @endfor
                        </table>
                    </div>

                    <div class="form-group"> 
                        <div class="col-lg-5"> 
                            <button type="button" class="btn btn-primary" id="btn_save">&nbsp;&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;&nbsp;</button>
                        </div>
                    </div>
                    

                    </div>
                </div>    
                
                
        </div>
    </div>
    </div>
    <br><br>
@stop



@section('scripts')
<style type="text/css">
    tbody tr:first-child td {
        text-align: center;
        font-weight: bold;
    }

    tbody tr:nth-child(2) td {
        font-weight: bold;
    }

    #result tbody td:first-child {
        text-align: center;
        vertical-align: middle;
    }
</style>
<script>
    $(document).ready(function(){

       $('input[name="delivery_date"]').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true
        });

        $.extend({
            list_unit : function(){
                var params = {'plant_id':$('#plant_id').val()};


                $.ajax({
                    url : "/resources/list_by_plant_id",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data : params,
                    type : "POST",
                    error : function(error){
                        console.log('Error : '+error)
                    },
                    success : function(data){
                         $('#unit').html('');
                        var html = '';
                        for (var i=0;i<data.length;i++){
                            html+='<option value="'+data[i].id+'">'+data[i].resource_id+'</option>';
                        }
                        $('#unit').html(html);
                    }
                })                
            } // list units

            , get_plant_details : function(){
                var params = {'plant_id':$('#plant_id').val()};
                $.ajax({
                    url : "/plant/get",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data : params,
                    type : "POST",
                    error : function(error){
                        console.log('Error : '+error)
                    },
                    success : function(data){
                        var reserve_class = data.aspa_types.description;
                        $('#reserve_class').val(reserve_class)
                    }
                })          
            } // get plant details 


            , retrieve : function(){

                $('#btn_save').unbind('click');
                var unit = $('#unit').val();
                var unit_name = $("#unit option:selected").text();

                var params = {};
                params['plant_id'] = $('#plant_id').val();
                params['plant'] = $("#plant_id option:selected").text();
                params['resource_id'] = unit;
                params['resource_name'] = unit_name;
                params['date'] = $('#delivery_date').val();;
                
                $.ajax({
                    url : "/reserve/capability/listByDate",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data : params,
                    type : "POST",
                    error : function(error){
                        console.log('Error : '+error)
                    },
                    success : function(ret){
                        var data = ret.data;
                        var total = ret.total;
                        var source  = ret.source;


                        $('#reserve_schedules_title').html('Reserve Schedules for <b>'+unit_name+'</b>');
                        
                        for (var i=1;i<=24;i++){
                            var mw = 0;

                            if (total > 0 ) {

                                if (  typeof data[i] != 'undefined'  ) {
                                    mw = data[i].mw;
                                }
                            }

                            $('#mw_'+i).val($.formatNumberToSpecificDecimalPlaces(mw,2));

                        } //


                        // prompt if source is not ngcp capability
                        if ( source.length > 0  ) {
                            bootbox.alert(source);
                            
                        }

                        $('input[name=hour]').prop('checked',true);
                        $('#btn_save').unbind('click').bind('click',function(){
                            $.save();
                        });
                    }
                }) 


                

                $('#result').show();
            } // eof retrieve

            , save : function(){

                var unit = $('#unit').val();
                var unit_name = $("#unit option:selected").text();
                var total_checked = 0;
                var params = {};
                params['plant_id'] = $('#plant_id').val();
                params['plant'] = $("#plant_id option:selected").text();
                params['resource_id'] = unit;
                params['resource_name'] = unit_name;
                params['date'] = $('#delivery_date').val();
                
                for (var i=1;i<=24;i++){
                    var is_checked = $('#hour_'+i).prop('checked');

                    if (is_checked) {
                        params['hour'+i] = $('#mw_'+i).val();
                        total_checked++;
                    }
                    
                }

                if (total_checked <= 0 ) {
                    bootbox.alert("Invalid Input. No interval was check for saving");
                }else {
                    $.ajax({
                        url : "/reserve/capability/save",
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        data : params,
                        type : "POST",
                        error : function(error){
                            console.log('Error : '+error)
                        },
                        success : function(ret){
                            bootbox.alert(ret.message);
                        }
                    }) 
                }
                
            }
        });


        $('#plant_id').unbind().bind('change',function(){
            $.list_unit();
            $.get_plant_details();
        });


        $('#btn_retrieve').unbind().bind('click',function(){
            $.retrieve();
        })
        

        $("#btn_update").unbind().bind("click", function() {
            var conditions = $("#populate").val().split(";");

            for(var i = 0; i < conditions.length; i++) {
                if ($.trim(conditions[i]) !== "") {
                    var data = conditions[i].split(",");
                    if (data.length != 2) {
                        bootbox.alert("Invalid populate text format");
                        return false;
                    } else {
                        var int_range = $.trim(data[0]);
                        var mw =  $.trim(data[1]);

                        // check if mw is number
                        if ( !$.isNumeric(mw) ) {
                            bootbox.alert('Invalid MW value ('+ mw +')');
                            return false;
                        }

                        // check if the interval is in range or not
                        var s_int = 0;
                        var e_int = 0;
                        if ( int_range.indexOf('-') >= 0 ) {

                            var range = int_range.split('-');
                            if ( range.length != 2 ) {
                                bootbox.alert('Invalid interval range ( '+ int_range +')');
                                return false;
                            }


                            if ( !$.isNumeric(range[0]) ) {
                                bootbox.alert('Invalid interval value ( '+ int_range +')');
                                return false;
                            }

                            if ( !$.isNumeric(range[1]) ) {
                                bootbox.alert('Invalid interval value ( '+ int_range +')');
                                return false;
                            }

                            s_int = parseInt(range[0],10);
                            e_int = parseInt(range[1],10);


                            if (e_int < s_int) {
                                bootbox.alert('Invalid interval range value ( '+ int_range +')');
                                return false;
                            }

                        }else {
                            if ( !$.isNumeric(int_range) ) {
                                bootbox.alert('Invalid interval value ( '+ int_range +')');
                                return false;
                            }

                            s_int = parseInt(int_range,10);
                            e_int = parseInt(int_range,10);

                        }

                    }

                    
                    if ( s_int >= 1 && s_int <=  24) {
                        // valid
                    }else {
                        bootbox.alert('Invalid interval value ( '+ s_int +')');
                        return false;
                    }

                    if ( e_int >= 1 && e_int <=  24) {
                        // valid
                    }else {
                        bootbox.alert('Invalid interval value ( '+ e_int +')');
                        return false;
                    }



                    for (var c_i=s_int;c_i<=e_int;c_i++){
                        $('#mw_'+c_i).val($.formatNumberToSpecificDecimalPlaces(mw,2));
                        $('#hour_'+c_i).prop('checked',true);

                    }
                    
                }
            }
        });



        $("#btn_clear").unbind().bind("click", function() {
            bootbox.confirm("Are you sure you want to clear all the data?", function(result){ 
                if (result) {
                    for(var i = 1; i <= 24; i++) {
                        $('#mw_'+i).val('');
                    }
                }
            });
        });



        $('#chk_all_hour').unbind().bind('click',function(){
            var is_checked = $('#chk_all_hour').prop('checked');
            if (is_checked) {
                $('input[name=hour]').prop('checked',true)
            }else {
                $('input[name=hour]').prop('checked',false)
            }
        });


        $('.numeric').autoNumeric('init',{
            mDec: '2'
          ,vMin : -9999999999      
        });

        $('#plant_id').trigger('change');
    });
    
</script>
@endsection


