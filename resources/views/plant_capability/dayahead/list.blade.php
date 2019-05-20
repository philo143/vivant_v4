
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
            <h4>Day Ahead Plant Capability</h4>
            
            <hr>
            <div class="col-md-12">
                
                @php
                ## generate default values for form inputs
                $form_data = array();
                for($i=1;$i<=24;$i++){
                    $form_data[$i] = array(
                            'interval' => $i,
                            'net_energy' => '',
                            'remarks' => 'ok',
                             'description' => ''
                    );
                }
                @endphp
                @if(Session::has('message_uploading'))
                     <p id="info_box" class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message_uploading') }}</p>
                    @php
                    $form_data = Session::get('day_ahead_template_data');
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
                    {{ Form::open(array('url'=>'/plant_capability/dayahead/upload','files'=>true),['class'=>'form-horizontal']) }}
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
                        {{ Form::label('delivery_date', 'Delivery Date:', ['class'=>'col-lg-2 control-label']) }}
                        <div class="col-lg-5"> 
                            {{ Form::text('delivery_date', '', ['class'=>'form-control input-sm', 'placeholder'=>'Delivery Date', 'required'=>'required']) }}

                        </div>
                        <div class="col-lg-5"> 
                            <button type="button" class="btn btn-primary" id="btn_retrieve">Retrive</button>
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
                 </div>
                
                <div class="well bs-component col-md-12"> 
                    <br \>
                    <small class="text-info">
                        <strong>Delivery Date : 
                        <span id="dd_text"></span>
                        </strong>
                    </small>
                     <form class="form-horizontal" id="data_form">
                    {{ csrf_field() }}
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
                                <input type="hidden" name="hour[0][{{ $i }}]" value="{{ $i }}">
                                 </td>


                                <td style="width:140px;">
                                    <span class="input-group">
                                    <input type="text" class="form-control input-sm" 
                                        id="unit_val_{{ $i }}" name="capability[0][{{ $i }}]" value="{{ $form_data[$i]['net_energy'] }}" >
                                        <span class="input-group-addon">MW</span></span>    
                                </td>


                                <td>
                                    {{ Form::select('status[0]['.$i .']', $remarks, $form_data[$i]['remarks'], ['class'=>'form-control input-sm'
                                        ,'id' => 'status_'.$i] 
                                         )  }}
                                </td>

                                <td>
                                    <textarea rows="4" class="form-control" name="description[0][{{ $i }}]" id="desc_{{ $i }}">{{ $form_data[$i]['description']}}
                                    </textarea>

                                </td>

                                <td>
                                    <span id="source_0_{{ $i }}"></span><input type="hidden" value="DAP" name="source[0][{{ $i }}]">
                                </td>
                            </tr>

                            @php
                            $x = ($i * 100) + 1;
                            $y = ($i+1) * 100;
                            @endphp
                        @endfor
                    </table>
                    <btn class="btn btn-primary" id="submit_data">Submit Plant Availability</btn>
                    </form>
                </div>
        </div>
    </div>
    </div>
    <br><br>
@stop



@section('scripts')
<script>
    $(document).ready(function(){

        $(function() {
            $('input[name="delivery_date"]').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true
            }, 
                function(start, end, label) {
                    if(start.format('MM/DD/YYY') <= moment().format('MM/DD/YYYY')){
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


                for (var x=start;x<=end;x++){
                    $('input[id="unit_val_'+x+'"]').val(mw);
                    $('select[id="status_'+x+'"]').val(remarks);
                }

            }

            , submit : function(){
                $.ajax({
                    url : "/plant_capability/store",                    
                    data : $('#data_form').serialize()+'&delivery_date='+$('#delivery_date').val()+'&unit='+$('#unit').val()+'&plant='+$('#plant_id').val(),
                    type : "POST",
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
                $(document).scrollTop(0);
            }
            ,retrieve : function(){
                $('#info_box').removeClass().html('');
                $('#dd_text').html('');
                $('#data_form')[0].reset();
                $('span[id*="source"]').html('')    
                $.ajax({
                    url : "/plant_capability/retrieve",                 
                    data : $('#form_retrieve').serialize(),
                    type : "POST",
                    error : function(error){
                        var error_msgs = '';
                        $.each(error.responseJSON,function(key,i){                          
                            error_msgs += '<li>'+i+'</li>'
                        })                
                        $('#info_box').html('<ul>'+error_msgs+'</ul>').addClass('alert alert-danger')          
                    },
                    success : function(data){
                        data = data['DAP'] === undefined ? 0 : data['DAP'] ;
                        if(data.length > 0){
                            $.each(data,function(i,val){
                                $('#dd_text').html(val.delivery_date);
                                $('input[name="capability[0]['+val.hour+']"]').val(val.capability)
                                $('select[name="status[0]['+val.hour+']"]').val(val.plant_capability_status_id)
                                $('textarea[name="description[0]['+val.hour+']"]').val($.trim(val.desc));
                                $('span[id="source_0_'+val.hour+'"]').html(val.type)
                            })
                        }else{
                            $('#info_box').html('No Data').addClass('alert alert-info') 
                            $(document).scrollTop(0)
                        }
                    }
                })
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
            $.retrieve();
        });


        
        $('#plant_id').trigger('change');
    });
    
</script>
@endsection


