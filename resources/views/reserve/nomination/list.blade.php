
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
            <h4>Reserve Nomination History</h4>
            
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
                            {{ Form::text('delivery_date', $date, ['class'=>'form-control input-sm', 'placeholder'=>'Target Date', 'required'=>'required' ,'id'=> 'dateRange']) }}

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

                    <div class="form-group" > 
                        {{ Form::label('resource', 'Resource ID :', ['class'=>'col-lg-2 control-label']) }}
                        <div class="col-lg-5" style="margin-top: 10px;"> 
                            <table id="tbl_resource_id">
                                
                            </table>
                        </div>
                    </div>

                    <div class="form-group"> 
                        {{ Form::label('Unit', 'Unit :', ['class'=>'col-lg-2 control-label']) }}
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
                            <button type="button" class="btn btn-primary" id="btn_generate">Generate CSV</button>
                        </div>
                    </div>
                    
                    {{ Form::close() }}

                </div>

                <br>

                <div style="margin-top:340px;">
                     <div id="result" style="display: none;"> 
                    
                    </div>    
                </div>
               
                
                
        </div>
    </div>
    </div>
    <br><br>
@stop



@section('scripts')
<style type="text/css">
    #result a {
        color:#ffffff;
        cursor: pointer;
    }
</style>
<script>
    $(document).ready(function(){

       $('input[name="delivery_date"]').daterangepicker({
            singleDatePicker: false,
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
                        $('#tbl_resource_id').html('');
                        var html = '<tr>';
                        var rctr = 1;
                        for (var i=0;i<data.length;i++){
                            html+='<td style="width:150px;">';
                            html+='<input type="checkbox" name="resource_id" value="'+data[i].id+'" checked>&nbsp;' + data[i].resource_id;
                            if ( (rctr % 12) === 0 ) {
                                html += '</tr><tr>';
                            }
                            rctr++;
                           
                        } // for

                        $('#tbl_resource_id').html(html);

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
                        $('#reserve_class').val(reserve_class);

                        var engines = parseInt(data.engines,10);
                        $('#tbl_unit').html('');
                        var html = '<tr>';
                        for (var i=1;i<=engines;i++){
                            html+='<td style="width:40px;">';
                            html+='<input type="checkbox" name="unit_no" value="Unit '+i+'" checked>&nbsp;' + i;
                            if ( (i % 12) === 0 ) {
                                html += '</tr><tr>';
                            }
                           
                        } // for

                        $('#tbl_unit').html(html);
                    }
                })          
            } // get plant details 


            , generate : function(){

                var tmp_ = $('#dateRange').val().split('-');
                var sdate = $.trim(tmp_[0]);
                var edate = $.trim(tmp_[1]);
                var unit_nos = $.trim($("input[name=unit_no]:checked").map(function() { return this.value;}).get().join(","));
                var params = {};
                params['plant_id'] = $('#plant_id').val();
                params['plant'] = $("#plant_id option:selected").text();
                params['sdate'] = sdate;
                params['edate'] = edate;
                params['unit_nos'] = unit_nos;
                $('#filelink').unbind();


                if (unit_nos.length <= 0 ) {
                    bootbox.alert('Please select at least one Unit No');
                } else {

                    $('#result').show().html('Please wait ... ').addClass('alert').addClass('alert-info');
                    $.ajax({
                        url : "/reserve/nomination/generateFileLink",
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        data : params,
                        type : "POST",
                        error : function(error){
                            console.log('Error : '+error)
                        },
                        success : function(ret){
                            var success = ret.success;
                            var message = ret.message;

                            $('#result').html(message).addClass('alert').addClass('alert-info');

                            if (success === 1) {
                                $('#filelink').unbind().bind('click',function(){
                                    $.downloadFile();
                                });
                            }
                            
                        }
                    });

                }

                


                

                $('#result').show();
            } // eof retrieve


            ,downloadFile :function(file_format){
                

                var tmp_ = $('#dateRange').val().split('-');
                var sdate = $.trim(tmp_[0]);
                var edate = $.trim(tmp_[1]);
                var unit_nos = $.trim($("input[name=unit_no]:checked").map(function() { return this.value;}).get().join(","));
                var params = {};
                params['plant_id'] = $('#plant_id').val();
                params['plant'] = $("#plant_id option:selected").text();
                params['sdate'] = sdate;
                params['edate'] = edate;
                params['unit_nos'] = unit_nos;


                var errors = [];
                if (unit_nos.length <= 0 ) {
                    errors.push('Please select at least one Unit No');
                }

                
                if (errors.length > 0 ) {

                    bootbox.alert(+errors.join(''));
                  

                }else {
                    var params = '';
                    params+='?sdate='+sdate;
                    params+='&edate='+edate;
                    params+='&plant_id='+$('#plant_id').val();
                    params+='&plant='+$("#plant_id option:selected").text();
                    params+='&unit_nos='+unit_nos;
                    window.location.href = '/reserve/nomination/file'+params;

                }

        } //
            
        });


        $('#plant_id').unbind().bind('change',function(){
            $.list_unit();
            $.get_plant_details();
        });


        $('#btn_generate').unbind().bind('click',function(){
            $.generate();
        })
        

        

        $('#plant_id').trigger('change');
    });
    
</script>
@endsection


