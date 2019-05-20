
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
    <div class="row">
        <div class="col-md-2">
            <div class="list-group">
                @include('plant_capability.menu')
            </div>
        </div>
        <div class="col-md-10">
            <h4>Week Ahead Plant Capability</h4>
            
            <hr>
            <div class="col-md-12">
                
                @php
                ## generate default values for form inputs
                $form_data = array();
                for ($ky=0;$ky<=6;$ky++){
                    for($i=1;$i<=24;$i++){
                        $form_data[$ky][$i] = array(
                            'interval' => $i,
                            'net_energy' => '',
                            'remarks' => 'ok',
                             'description' => ''
                        );
                    }
                }
                
                @endphp
                @if(Session::has('message_uploading'))
                     <p id="info_box" class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message_uploading') }}</p>
                    @php
                    $form_data = Session::get('week_ahead_template_data');

                    $date_list = Session::get('excel_date_list');
                    $date = date('m/d/Y',strtotime($date_list[0]));
                    $end_date = date('m/d/Y',strtotime(end($date_list)));

                    @endphp
                @else 
                   <div id="info_box"></div>         
                @endif

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
                    {{ Form::open(array('url'=>'/plant_capability/weekahead/upload','files'=>true),['class'=>'form-horizontal']) }}
                    <legend>Upload File</legend>
                    


                     <div class="form-group"> 
                        {{ Form::label('filename', 'File:', ['class'=>'col-lg-2 control-label']) }}
                        <div class="col-lg-10"> 
                             {{  Form::file('filename', ['class'=>'form-control','style'=>'border:none; background:transparent;']) }}
                        </div>
                    </div>

                    <div class="form-group"> 
                        <div class="col-lg-2"> 
                        </div>
                        <div class="col-lg-5" style="margin-top:10px;"> 
                            {{ Form::submit('Submit File', ['class'=>'btn btn-primary']) }}
                        </div>
                    </div>

                    {{ Form::close() }}
                </div>


                <div class="well bs-component col-md-12"> 
                    {{ Form::open(['class'=>'form-horizontal','id' => 'form_retrieve']) }}
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
                            {{ Form::select('unit', [], '',['class'=>'form-control input-sm']) }}
                        </div>
                    </div>

                    <div class="form-group"> 
                        {{ Form::label('start_date', 'Delivery Date:', ['class'=>'col-lg-2 control-label']) }}
                        <div class="col-lg-4 input-group" style="padding-left:15px;"> 
                            {{ Form::text('start_date', $date, ['class'=>'form-control input-sm', 'placeholder'=>'Delivery Date', 'required'=>'required']) }}
                             <span class="input-group-addon" id="basic-addon1"> - </span>

                             {{ Form::text('end_date', $end_date, ['class'=>'form-control input-sm', 'id'=>'end_date', 'required'=>'required', 'readonly' => true]) }}
                              <span class="input-group-btn">
                                <button class="btn btn-primary input-sm" type="button" id="btn_retrieve">Retrieve</button>
                              </span>
                        </div>

                        
                    </div>
                    {{ Form::close() }}
                </div>

                 <div class="well bs-component col-md-12">
                    <h5>Populate fields </h5>
                    <div class="row">
                        <div class="form-group col-md-5"> 
                            <label for="pop_mw" class="col-lg-5 control-label">Populate Text Box</label>
                            <div class="col-lg-3">
                                <input type="text" class="form-control input-sm" value="1-24" id="interval">
                            </div>
                            <div class="col-lg-4 input-group">
                                <input type="text" class="form-control input-sm" value="300" id="txt_mw">
                                <span class="input-group-addon">MW</span>       
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="col-lg-12">
                                <label for="pop_remarks" class="col-lg-2 control-label">Remarks</label>
                                <div class="col-lg-4">
                                    {{ Form::select('pop_remarks', $remarks, 'ok', ['class'=>'form-control input-sm'])  }}
                                </div>
                                <button class="btn btn-primary btn-sm" type="button" id="btn_populate">Populate</button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-2" >
                            <input type="hidden" name="hid_check_uncheck" id="hid_check_uncheck" value="1">
                            <button class="btn btn-default btn-sm" id="btn_check_uncheck" ><span class="glyphicon glyphicon-unchecked"></span> Uncheck All</button>
                        </div>
                        <div class="col-lg-9" id="checkbox_container" style="margin-top:10px;">
                            @foreach ($date_list as $key=> $dte)
                                {{  Form::checkbox('chk_pop_date', $key, true
                                    , ['id'=>'chk_pop_date_'.$dte ]) }} <span class="control-label">{{ date('d-M-Y',strtotime($dte)) }} &nbsp;</span>

                            @endforeach

                            
                        </div>
                    </div>
                 </div>
                

                <div class="tabpanel">

                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist" id="tab_list">

                        @foreach ($date_list as $key=> $dte)
                        
                            <li role="presentation" @if($key == 0) class="active" @endif>
                                <a href="#tab-{{$key}}" aria-controls="#tab-one" role="tab" data-toggle="tab">{{ date('d-M-Y',strtotime($dte)) }}</a>
                            </li>

                        @endforeach
                    </ul>

                    <!-- Tab panes -->
                    <form class="form-horizontal" id="data_form">
                    {{ csrf_field() }}
                    <div class="tab-content" id="tab_content">
                        
                        @foreach ($date_list as $key=> $dte)
                        
                            <div role="tabpanel" class="tab-pane @if($key == 0) active @endif" id="tab-{{$key}}">
                                <div class="well bs-component col-md-12">
                                <small class="text-info">
                                    <strong>Delivery Date : 
                                    <span id="dd_text_{{$key}}">{{ date('d-M-Y',strtotime($dte)) }}</span>
                                    </strong>
                                </small>
                                <br><br>
                                
                                    <input type="hidden" id="datekey_{{$key}}" value="{{$dte}}">
                                    
                                    <table class="table">
                                        <tr>
                                            <th style="width:120px;">Interval</th>
                                            <th>Unit</th>
                                            <th style="width:200px;">Remarks</th>
                                            <th >Description</th>
                                            <th style="width:120px;">Source</th>
                                        </tr>

                                        @php
                                        $x = 1;
                                        $y = 100;
                                        @endphp
                                        
                                        @for ($i = 1; $i <= 24; $i++)

                                            <tr>
                                                <td style="font-weight:bold;"> {{ $i . '&nbsp;(' .  str_pad($x,4,"0",STR_PAD_LEFT) . '-' . str_pad($y,4,"0",STR_PAD_LEFT) . 'H' }})
                                                <input type="hidden" name="hour[{{$key}}][{{ $i }}]" value="{{ $i }}">
                                                 </td>


                                                <td style="width:140px;">
                                                    <span class="input-group">
                                                    <input type="text" class="form-control input-sm" 
                                                        id="unit_val_{{$key}}_{{ $i }}" name="capability[{{$key}}][{{ $i }}]" value="{{ $form_data[$key][$i]['net_energy'] }}" >
                                                        <span class="input-group-addon">MW</span></span>    
                                                </td>


                                                <td>
                                                    {{ Form::select('status['.$key.']['.$i .']', $remarks, $form_data[$key][$i]['remarks'], ['class'=>'form-control input-sm'
                                                        ,'id' => 'status_'.$key.'_'.$i] 
                                                         )  }}
                                                </td>

                                                <td>
                                                    <textarea rows="4" class="form-control" name="description[{{$key}}][{{ $i }}]" id="desc_{{$key}}_{{ $i }}">{{ $form_data[$key][$i]['description']}}
                                                    </textarea>

                                                </td>

                                                <td>
                                                    <span id="source_{{$key}}_{{ $i }}"></span><input type="hidden" value="WAP" name="source[{{$key}}][{{ $i }}]">
                                                </td>
                                            </tr>

                                            @php
                                            $x = ($i * 100) + 1;
                                            $y = ($i+1) * 100;
                                            @endphp
                                        @endfor
                                    </table>
                                    
                                    </div>
                            </div>
                        @endforeach
                    </div>
                    </form>
                    <btn class="btn btn-primary" id="submit_data">Submit Plant Availability</btn>
                </div>
            </div>
        </div>
    </div>
    </div>
    <br><br>
@stop



@section('scripts')
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.standalone.min.css" />
<script>
    $(document).ready(function(){

        $(function() {

            $('input[name="start_date"]').datepicker({
                    daysOfWeekDisabled: "0,1,2,3,4,5,",
                    daysOfWeekHighlighted: "6",
                    autoclose: true
                })
                .on('changeDate', function(e) {
                    var x = new Date($('#start_date').val());
                    var end_date = moment(x).add(6, 'days').format('MM/DD/YYYY');
                    $('#end_date').val(end_date);
                    if($('#start_date').val() <= moment().format('MM/DD/YYYY')){
                        $('#submit_data').attr('disabled',true);
                    }else{
                        $('#submit_data').attr('disabled',false);
                    }
                });
            
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
            }

            , populate_fields : function(){

                var interval = $('#interval').val();
                var mw  = $('#txt_mw').val();
                var remarks = $('select[name="pop_remarks"]').val();

                var tmp = interval.split('-');
                var start = tmp[0];
                var end = start;
                if (tmp.length > 1) {
                    end = tmp[1];;
                }

                var pop_dates = $.trim($("input[name=chk_pop_date]:checked").map(function() { return this.value;}).get().join(","));
                $('input[name=chk_pop_date]').each(function () {
                   if (this.checked) {
                        var key = $(this).val();
                        
                        for (var x=start;x<=end;x++){
                            $('input[id="unit_val_'+key+'_'+x+'"]').val(mw);
                            $('select[id="status_'+key+'_'+x+'"]').val(remarks);
                        }

                   }
                });

            }

            , submit : function(){
                $('#info_box').removeClass().html('');    
                var start_date = new Date($('#start_date').val());
                var date = moment(start_date).format('YYYY-MM-DD');
                var total_with_values = 0;
                // for(var i=0;i<=6;i++){

                //     if (i===0) {
                //         var date = moment(start_date).format('YYYY-MM-DD');
                //         var date_str = moment(start_date).format('DD-MMM-YYYY');
                //     }else {
                //         var date = moment(start_date).add(i, 'days').format('YYYY-MM-DD');
                //         var date_str = moment(start_date).add(i, 'days').format('DD-MMM-YYYY');                       
                //     }

                     $('#info_box').removeClass().html('Saving data');    
                     
                     $.ajax({
                        url : "/plant_capability/store",
                        data : $('#data_form').serialize()+'&delivery_date='+date+'&unit='+$('#unit').val()+'&plant='+$('#plant_id').val(),
                        type : "POST",
                        async : false,
                        error : function(error){
                            var error_msgs = '';
                            $.each(error.responseJSON,function(key,i){                          
                                error_msgs += '<li>'+i+'</li>'
                            })                
                            $('#info_box').html('<ul>'+error_msgs+'</ul>').addClass('alert alert-danger')                   
                        },
                        success : function(data){
                            $('#info_box').html('<p>'+data+'</p>').addClass('alert alert-success')
                        }
                    })
                // } // end for loop


                
                $(document).scrollTop(0);
            }
            ,retrieve_data : function(){
                $('#info_box').removeClass().html('');
                $('input[name*="capability"').val('');
                $('span[id*="source"').html('');
                $('#data_form textarea').val('');
                $('#data_form select').val($('#data_form select option:first').val());    
                var start_date = new Date($('#start_date').val());
                var total_with_values = 0;
                for(var i=0;i<=6;i++){

                    if (i===0) {
                        var date = moment(start_date).format('YYYY-MM-DD');
                        var date_str = moment(start_date).format('DD-MMM-YYYY');
                    }else {
                        var date = moment(start_date).add(i, 'days').format('YYYY-MM-DD');
                        var date_str = moment(start_date).add(i, 'days').format('DD-MMM-YYYY');                       
                    }

                     $('#info_box').removeClass().html('Retrieving data');    
                     
                     $.ajax({
                        url : "/plant_capability/retrieve",                 
                        data : $('#form_retrieve').serialize()+'&delivery_date='+date,
                        type : "POST",
                        async : false,
                        success : function(data){
                            data = data['WAP'] === undefined ? '' : data['WAP'] ;
                            if(data.length > 0){
                                $.each(data,function(k,val){
                                    $('#dd_text_'+i).html(val.delivery_date);
                                    $('input[name="capability['+i+']['+val.hour+']"]').val(val.capability)
                                    $('select[name="status['+i+']['+val.hour+']"]').val(val.plant_capability_status_id)
                                    $('textarea[name="description['+i+']['+val.hour+']"]').val($.trim(val.desc))
                                    $('span[id="source_'+i+'_'+val.hour+'"]').html(val.type)
                                });

                                total_with_values++;
                            }
                        }
                    });

                     // break;
                } // end for loop

                if (total_with_values > 0 ) {
                    $('#info_box').removeClass().html('');    
                }else {
                    $('#info_box').html('No available data').addClass('alert alert-info'); 
                }


                
                
            }
            ,retrive_tabs : function(){
                var start_date = new Date($('#start_date').val());
                $('#checkbox_container').html('');

                var pop_checkbox_html = '';
                for(var i=0;i<=6;i++){

                    if (i===0) {
                        var date = moment(start_date).format('YYYY-MM-DD');
                        var date_str = moment(start_date).format('DD-MMM-YYYY');
                    }else {
                        var date = moment(start_date).add(i, 'days').format('YYYY-MM-DD');
                        var date_str = moment(start_date).add(i, 'days').format('DD-MMM-YYYY');
                    }

                    $( 'a[href*="tab-'+i+'"]' ).html(date_str);
                    $( 'a[href*="tab-'+i+'"]' ).parent().removeClass('active');

                    if (i===0){
                        $( 'a[href*="tab-'+i+'"]' ).parent().addClass('active');
                    }
                    

                    // tab contents
                    $('#dd_text_'+i).html(date_str);
                    $('#datekey_'+i).val(date);
                    $('#data_form')[0].reset();

                    // pop checkboxes
                    pop_checkbox_html+='<input id="chk_pop_date_'+date+'" checked="checked" name="chk_pop_date" type="checkbox" value="'+i+'">';
                    pop_checkbox_html+='&nbsp;<span class="control-label">'+date_str+' &nbsp;</span>';    

                } // end for loop

                $('#checkbox_container').html(pop_checkbox_html);

                $.retrieve_data();

            }

        });


        $('#plant_id').unbind().bind('change',function(){
            $.list_unit();
        });

        $('#btn_populate').unbind().bind('click',function(){
            $.populate_fields();
        });

        $('#submit_data').unbind().bind('click',function(){
            $.submit();
        });

        $('#btn_retrieve').unbind().bind('click',function(){
            $.retrive_tabs();
        });

        $('#btn_check_uncheck').unbind().bind('click',function(){
            var hid_check_uncheck = parseInt($('#hid_check_uncheck').val());
            if (hid_check_uncheck === 1) {
                $('#btn_check_uncheck').html('<span class="glyphicon glyphicon-check"></span> Check All');
                $('#hid_check_uncheck').val('0');
                $('input[name=chk_pop_date]').prop('checked',false)
            }else {
                 $('#btn_check_uncheck').html('<span class="glyphicon glyphicon-unchecked"></span> Uncheck All');
                $('#hid_check_uncheck').val('1');
                $('input[name=chk_pop_date]').prop('checked',true)
            }
        });
        


        $('#plant_id').trigger('change');

    });
    
</script>
@endsection


