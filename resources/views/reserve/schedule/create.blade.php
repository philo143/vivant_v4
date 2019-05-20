
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
            <h4>Reserve Schedules</h4>
            
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
                        {{ Form::label('source', 'Source :', ['class'=>'col-lg-2 control-label']) }}
                        <div class="col-lg-5"> 
                            <select class="form-control input-sm" name="source" id="source">
                                <option value="ngcp">ASPA NGCP Website</option>
                                <option value="mms">Operating Reserve via MMS</option>
                            </select>
                            
                        </div>
                    </div>


                    <div class="form-group"> 
                        {{ Form::label('unit', 'Unit :', ['class'=>'col-lg-2 control-label']) }}
                        <div class="col-lg-5" style="margin-top: 10px;"> 
                           <table id="tbl_unit">
                                
                            </table>
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
                            <button type="button" class="btn btn-primary" id="btn_retrieve">Display</button>
                            <button type="button" class="btn btn-primary" id="btn_manual_downloader" disabled="true">Manual Downloader</button>
                        </div>
                    </div>
                    
                    {{ Form::close() }}

                </div>

                <div id="status_log"></div>
                <div class="well bs-component col-md-12" id="result" style="display: none;"> 
                    
                    <div id="container">
                        
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
                        $('#tbl_unit').html('');
                        var html = '<tr>';
                        var rctr = 1;
                        for (var i=0;i<data.length;i++){
                            html+='<td style="width:150px;">';
                            html+='<input type="checkbox" name="resource_id" value="'+data[i].resource_id+'" checked>&nbsp;' + data[i].resource_id;
                            if ( (rctr % 12) === 0 ) {
                                html += '</tr><tr>';
                            }
                            rctr++;
                        }
                        

                        $('#tbl_unit').html(html);

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
                var resource_ids = $.trim($("input[name=resource_id]:checked").map(function() { return this.value;}).get().join(","));


                if (resource_ids.length <= 0 ) {
                    bootbox.alert('Please select at least one Resource ID');
                } else {
                    var params = {};
                    params['plant_id'] = $('#plant_id').val();
                    params['plant'] = $("#plant_id option:selected").text();
                    params['resource_ids'] = resource_ids;
                    params['sdate'] = $('#delivery_date').val();
                    params['edate'] = $('#delivery_date').val();
                    params['source'] = $('#source').val();
                    
                    $('#status_log').html('Please wait');
                    $('#result').hide();
                    $.ajax({
                        url : "/reserve/schedule/listByDate",
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        data : params,
                        type : "POST",
                        error : function(error){
                            console.log('Error : '+error)
                        },
                        success : function(ret){
                            var ret1 = ret.data;
                            var total = ret.total;
                            var x = new Date($('#delivery_date').val());
                            var date = moment(x).format('YYYY-MM-DD');
                            var data = ret1[date];

                            var resource_id_list = $("input[name=resource_id]:checked").map(function() { return this.value;}).get();
                            var html = "";
                            $.each(resource_id_list, function(i, resource_id) {
                                html+='<div>';
                                html+='<span >Reserve Schedules for <b>'+resource_id+'</b></span>';
                                html+=' <div style="margin-top:10px; margin-bottom:10px;overflow: auto;">';
                                html+='<table class="table table-striped table-bordered">';
                                html+='<tr>';
                                html+='<td style="min-width:100px; text-align: left;">Interval</td>';
                                for (var h=1;h<=24;h++){
                                    html+='<td style="min-width:110px;">'+h+'</td>';
                                }
                                html+='</tr>';

                                html+='<tr>';
                                html+='<td style="text-align: left;">MW</td>';
                                for (var h=1;h<=24;h++){

                                    var mw = ''; 
                                    if ( typeof data[resource_id] != 'undefined') {
                                        if ( typeof data[resource_id][h] != 'undefined') {
                                            mw = $.formatNumberToSpecificDecimalPlaces(data[resource_id][h],2);
                                        }
                                    } 
                                    html+='<td><input type="text" name="mw_'+resource_id +'_'+h+'" id="mw_'+resource_id +'_'+h+'" class="form-control input-sm numeric" readonly="true" value="'+mw+'" style="font-weight:normal; text-align:right;"></td>';
                                }
                                html+='</tr></table></div>';
                            });

                            $('#container').html(html);
                            $('#status_log').html('');
                            
                            $('.numeric').autoNumeric('init',{
                                mDec: '2'
                              ,vMin : -9999999999      
                            });
                            
                        }
                    }) 


                    

                    $('#result').show();
                    

                }

                
            } // eof retrieve

            
        });


        $('#plant_id').unbind().bind('change',function(){
            $.list_unit();
            $.get_plant_details();
        });


        $('#btn_retrieve').unbind().bind('click',function(){
            $.retrieve();
        })
        

        $('#plant_id').trigger('change');
    });
    
</script>
@endsection


