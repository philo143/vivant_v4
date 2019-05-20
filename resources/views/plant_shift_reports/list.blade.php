
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
    <div class="row">
        <div class="col-md-2">
            <div class="list-group">
                @include('plant_shift_reports.menu')
            </div>
        </div>
        <div class="col-md-10">
            {!! Breadcrumbs::render('plant_ops_shift_report') !!}
            <h4>Plant Operational Shift Report View</h4>
            <hr>
            <div class="col-md-12">
                <input type="hidden" name="current_date" id="current_date" value="{{$dte}}">
                <input type="hidden" name="cur_hour" id="cur_hour" value="{{$hour}}">
                <input type="hidden" name="cur_min" id="cur_min" value="{{$min}}">
                <input type="hidden" name="resource" id="resource" value="{{$resource}}">
                
                <div class="well bs-component col-md-12"> 
                    {{ Form::open(['class'=>'form-horizontal','id' => 'form_retrieve']) }}
                    
                    <div class="form-group"> 
                        {{ Form::label('dateRange', 'Date', ['class' => 'col-lg-2 control-label']) }}
                        <div class="col-sm-3">
                           {{ Form::text('dateRange','', array('class' => 'form-control input-sm', 'id' => 'dateRange')) }} 
                        </div>  
                    </div>


                    <div class="form-group">
                        
                        {{ Form::label('plant', 'Plant', ['class' => 'col-lg-2 control-label']) }}
                        <div class="col-sm-3">
                           {{ Form::select('plant', $plants,'' ,
                                [
                                 'class'=>'form-control input-sm'
                                 ,'id' => 'plant' ,'disabled' => true])  }}
                        </div>  
                    </div>


                     <div class="form-group">
                        <div class="col-lg-2" style="text-align: right; margin-top: 15px;" >
                            {{  Form::checkbox('chk_all_operators', '', true
                                    , ['id'=>'chk_all_operators' ]) }} &nbsp;<span class="control-label">All Plant Ops</span>
                        </div>
                        <div class="col-lg-3" id="checkbox_container" style="margin-top:10px;">
                            {{ Form::select('plant_operator_list', [],'' ,
                                [
                                 'class'=>'form-control input-sm'
                                 ,'disabled' => true
                                 ,'id' => 'plant_operator_list'])  }}
                        </div>
                    </div>

                    

                    <div class="form-group"> 
                        {{ Form::label('hour', 'Hour', ['class' => 'col-lg-2 control-label']) }}
                        <div class="col-sm-3">
                            <select id="hour" name="hour" class="form-control input-sm'"> 
                                @for ($i = 1; $i < 25; $i++) 
                                    @if ($i===$hour) 
                                        @php 
                                        $selected = 'selected=true';
                                        @endphp 
                                    @else 
                                        @php 
                                        $selected = '';
                                        @endphp 
                                    @endif
                                    <option value="{{ $i }}" {{ $selected }}>{{ $i }}</option>
                                @endfor
                                
                            </select>
                        </div>  

                        <div class="col-sm-3"> 
                            {{ Form::button('Display', ['class'=>'btn btn-primary','id' => 'btn_display']) }}
                        </div>
                    </div>

                    {{ Form::close() }}
                </div>
                <div id="retrieve_info_box"></div> 

                <div id="result">
                        
                </div>
                


                
                
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
        font-size:10vh;
        width: 20px;
        vertical-align: middle;
        text-align: center;
    }
</style>


<script>
   REPORT_TYPES = [];
    $.extend({
        retrieveData : function(){
            var s_date = $.trim($('#dateRange').val());
            var e_date = $.trim($('#dateRange').val());
            var hour = $('#hour').val();
            var plant = $('#plant').val();

            var parameters = $('#form_retrieve').serialize()+'&s_date='+s_date +'&e_date='+e_date;
            var is_checked = $('#chk_all_operators').prop('checked');
            if (!is_checked) {
               
               parameters+='&submitted_by=' + $('#plant_operator_list').val();
            }
            parameters+='&plant=' + plant;

            $.ajax({
                url : "/plant/shift_report/retrieve",                 
                data : parameters,
                type : "POST",
                error : function(error){
                    var error_msgs = '';
                    $.each(error.responseJSON,function(key,i){                          
                        error_msgs += '<li>'+i+'</li>'
                    })                
                    $('#retrieve_info_box').html('<ul>'+error_msgs+'</ul>').addClass('alert alert-danger')          
                },
                success : function(data){
                    var list = {};
                    if (typeof data.list.length !== 'undefined') {
                        list = {};
                    }else {
                        list = data.list;
                    }

                    $('#current_date').val(data.date);
                    $('#cur_hour').val(data.hour);
                    $('#cur_min').val(data.min);
                    
                    $.populateDisplay(list);

                }
            })
        } //

        ,retrievePlantOps : function(){
            var plant = $('#plant').val();

            $.ajax({
                url : "/plant/shift_report/operatorList",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data : {'plant' : plant},
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

        ,getShiftReportTypes : function(){
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
                        REPORT_TYPES = data;
                    }
                }) 
        }

        ,submitIM : function(result,elm, im){
            var tmp = elm.split('_')
            var hour = tmp[1];
            var min = tmp[2];
            var params = {'plant_id':$('#plant').val(),'im' : im};
            params['im_remarks'] = result;
            params['resource_id'] = $('#resource').val();
            params['hour'] = hour;
            params['min'] = min;
            params['date'] = $('#dateRange').val();
            $.ajax({
                url : "/plant/shift_report/storeIM",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data : params,
                type : "POST",
                error : function(error){
                    console.log('Error : '+error)
                },
                success : function(data){
                     $.retrieveData();
                     bootbox.alert(data);   
                }
            });
                
        } //

        ,saveActivityReport : function(summernote_element,info_box_element,hour,interval){
             if ( $('#'+summernote_element).summernote('isEmpty') ) {
                var error_msgs = '<li>Report is required</li>';
                $('#'+info_box_element).html('<ul>'+error_msgs+'</ul>').addClass('alert alert-danger');
             }else {
                 var params = {};
                 params['type'] = 'activity';
                 params['report'] = $('#'+summernote_element).summernote('code');
                 params['hour'] = hour;
                 params['min'] = interval;
                 params['date'] = $('#dateRange').val();
                 params['plant'] = $('#plant').val();
                 params['resource'] = $('#resource').val();

                 $.ajax({
                    url : "/plant/shift_report/store",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data : params,
                    type : "POST",
                    async : false,
                    error : function(error){
                        var error_msgs = '';
                        $.each(error.responseJSON,function(key,i){                          
                            error_msgs += '<li>'+i+'</li>'
                        })                
                        $('#'+info_box_element).html('<ul>'+error_msgs+'</ul>').removeAttr('class').addClass('alert alert-danger');     
                    },
                    success : function(data){
                        $('#'+summernote_element).summernote('reset');
                        $('#'+info_box_element).html('<p>'+data+'</p>').removeAttr('class').addClass('alert alert-success');
                        $.retrieveData();
                    }
                }); 

                 
             }
            
        } // 
        ,populateDisplay : function(data){
            
             var hours = $('#hour').val();
             var error_msgs = [];
             if (hours.length <= 0 ) {
                error_msgs.push('<li>Hour selection is required</li>');
             }
             var island_modes = [];
              
             $('#result').html(''); 
             if (error_msgs.length > 0 ){
                $('#retrieve_info_box').html('<ul>'+error_msgs+'</ul>').addClass('alert alert-danger')          
             }else {
                var result = '<table class="table table-bordered table-condensed table-striped">';
                var hour_list = hours.split(',');
                var interval_list = [];
                for(var x=5;x<=55;x+=5){
                    interval_list.push(x);
                }
                interval_list.push(0);
                var i = '', hr = '', prev_hr = '';
                $.each( hour_list, function( i, hr ) {
                    prev_hr = hr - 1;
                    if (hr === 1) {
                        prev_hr = 24;
                    }
                    $.each( interval_list, function( ii, interval ) {
                            var pad = '00';
                            var new_interval = (pad + interval).slice(-pad.length);
                            var im = 0;
                            var intra_interval = '';
                            if (interval === 0) {
                                intra_interval = $.strPad(hr,2,'0') + ':' + new_interval;
                                var time = $.strPad(hr,2,'0') + ':'+new_interval+':00';
                            }else {
                                intra_interval = $.strPad(prev_hr,2,'0') + ':' + new_interval;
                                var time =  $.strPad(prev_hr,2,'0') + ':'+new_interval+':00';
                            }
                            $.each( REPORT_TYPES, function( iii, type ) {

                                var type_id = type.id;
                                var type_name = type.type ;
                                var dte = new Date($('#dateRange').val());
                                var dte_selected = moment(dte).format('YYYY-MM-DD');
                                
                                



                                var reports_html = '';
                                
                                if (typeof data[dte_selected] !=='undefined') {
                                    if (typeof data[dte_selected][hr] !=='undefined') {
                                        if (typeof data[dte_selected][hr][time] !=='undefined') {
                                            if (typeof data[dte_selected][hr][time][type_id] !=='undefined') {
                                                var list = data[dte_selected][hr][time][type_id];

                                                for (var x=0;x<list.length;x++){

                                                    var dt = '[' + moment(list[x].created_at).format('MMM D,YYYY') + '] [' + moment(list[x].created_at).format('HH:MM:SS') + ']'

                                                    var cur_html = '<div style="background:#FFF;margin:2px;padding:3px;border-radius:3px">';
                                                    cur_html +='<b>'+dt+'  ' + list[x].user.fullname + '</b><br>';
                                                    cur_html += list[x].report;
                                                    cur_html +='</div>';

                                                    im = list[x].im;
                                                    reports_html+=cur_html;
                                                }
                                            }
                                        }
                                    }
                                }


                                result+='<tr>';
                                var hour_rowspan = '';
                                var x = interval_list.length * 3;

                                if (ii==0 && iii === 0) {
                                    result+='<td rowspan="'+x+'" class="big_font">'+hr+'</td>';
                                }
                                
                                if (iii==0) {
                                    result+='<td rowspan="3" style="width:70px; text-align:center; vertical-align:middle;">'+intra_interval+'H</td>';
                                }

                                result+='<td style="width:90px; text-align:center;">'+type.description+'</td>';



                                result+='<td>'+reports_html;

                                if (type_name === 'activity') {
                                    result+='<div>'
                                    var elem_id = 'activity_'+hr+'_'+interval;
                                    var note_elm_id = 'note_'+hr+'_'+interval;
                                    var btn_elm_id =  'btn_'+hr+'_'+interval;
                                    result+='<span data-toggle="collapse" data-target="#'+elem_id+'" class="glyphicon glyphicon-plus-sign"></span>';
                                    result+='<div id="'+elem_id+'" class="collapse">';
                                    result+='<div id="'+note_elm_id+'" name="add_activity"></div>';
                                    result+='<button class="btn btn-success btn-xs" style="margin-top:-10px;" name="btn_save_activity" id="'+btn_elm_id+'" hour="'+hr+'" interval="'+interval+'">Save</button>';
                                    result+='<div id="info_box_'+hr+'_'+interval+'"></div>'
                                    result+='</div>';


                                    result+='</div>'
                                }
                                

                                result+='</td>';


                                if (iii==0) {
                                    result+='<td rowspan="3" style="width:120px; font-weight:bold;  text-align:center; vertical-align:middle; ">';

                                    result+= '<div class="btn-group colors" data-toggle="buttons">';
                                    result+= '<label class="btn btn-default btn-sm active">';
                                    result+= '<input type="radio" name="im_'+hr+'_'+new_interval+'" value="1" autocomplete="off"> IM';
                                    result+= '</label>';
                                    result+= '<label class="btn btn-default btn-sm">';
                                    result+= '<input type="radio" name="im_'+hr+'_'+new_interval+'"  value="0" autocomplete="off" checked> OFF';
                                    result+= '</label>';
                                    result+= '</div></td>';
                                }
                                result+='</tr>';

                            });
                            var island_mode = im;
                            var elm = $.trim('im_'+hr+'_'+new_interval);
                            island_modes.push({'element' : elm , 'value' : island_mode});
                            
                    });
                });    

                result+='</table>';

                $('#result').html(result);
                console.log(island_modes)
                for (var x=0;x<island_modes.length;x++){
                    var elm = island_modes[x].element;
                    var im = island_modes[x].value  === null ? 0 : parseInt(island_modes[x].value,10);
                    $("input[name="+elm+"][value="+im+"]").prop('checked', true);
                    $("input[name="+elm+"][value="+im+"]").parent().parent().find('label').removeClass('active');
                    $("input[name="+elm+"][value="+im+"]").parent().addClass('active');  
                }
                

                $("input[name^='im_']").each(function(){
                   $(this).prop('disabled',true);
                   $(this).parent().attr('disabled',true);

                   var tmp = $(this).attr('name').split('_');
                   var hour = tmp[1];
                   var min = $.strPad(tmp[2],2,'0');

                   var cur_date = moment(new Date($('#current_date').val())).format('YYYY-MM-DD');
                   var date_selected = moment(new Date($('#dateRange').val())).format('YYYY-MM-DD');
                   var cur_hour = $('#cur_hour').val();
                   var cur_min = $('#cur_min').val();
                   var hour_selected =  $('#hour').val();
                   var cur_hourmin = $.strPad(cur_hour,2,'0') + $.strPad(cur_min,2,'0');
                   if (cur_date === date_selected && cur_hour === hour_selected) {
                        if ( min === cur_min ) {

                            $(this).prop('disabled',false);
                            $(this).parent().attr('disabled',false);   
                        }
                   }
                });


                $("input[name^='im_']").unbind().bind('change',function(){
                    var elm = $(this).attr('name');
                    var im = parseInt($('input[name='+elm+']:checked').val(),10);

                    bootbox.prompt("Island mode remarks", function(result){ 
                        if (result !== null) {
                            $.submitIM(result,elm,im);
                        }else {
                            $('input[name='+elm+']').prop('checked',false);
                            $('input[name='+elm+'][value=0]').parent().removeClass('active');
                            $('input[name='+elm+'][value=1]').parent().removeClass('active');

                            var old_im = im == 1 ? 0 :1;
                            $('input[name='+elm+'][value='+old_im+']').parent().addClass('active');
                            $('input[name='+elm+'][value='+old_im+']').prop('checked',true);
                        }   
                    });

                });


                $('div[name=add_activity]').summernote({
                  toolbar: [
                    // [groupName, [list of button]]
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']]
                  ],
                  disableDragAndDrop: true,
                  height:50
                });
                


                // add click event to button
                $('button[name=btn_save_activity]').unbind().bind('click',function(){
                    console.log( $(this).attr('hour') );
                    console.log( $(this).attr('interval') );

                    var note_elm = 'note_'+$(this).attr('hour')+'_'+$(this).attr('interval');
                    var info_elm = 'info_box_'+$(this).attr('hour')+'_'+$(this).attr('interval');
                    $.saveActivityReport(note_elm,info_elm,$(this).attr('hour'),$(this).attr('interval'));
               });

             }
        },
        showPlantCapabilityAudit : function (id) {
            $.ajax({
                url : '{{ route('trading_shift_report.transactions') }}',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data : {id : id,type : 'plant_capability'},
                type : "POST",
                async : false,
                error : function(error){
                    console.log('Error : '+error)
                },
                success : function(data){
                    html = '<table class="table table-striped table-condensed">';
                    $.each(data,function(i,val){
                        html+='<tr><td colspan="4"><b>Date : '+val['delivery_date']+'<b></td></tr>';
                        html+='<tr><th>Interval</th><th>Unit</th><th>Remarks</th><th>Description</th></tr>';
                        for(i=1;i<=24;i++){
                            html+='<tr><td>'+i+'</td><td>'+val[i].capability+'</td><td>'+val[i].status+'</td><td>'+val[i].description+'</td></tr>';
                        }
                    })                    
                    bootbox.alert({ 
                      title: "Plant Projections",
                      message: html,
                    })
                }
            }) 
            setTimeout(function(){
                $('.bootbox').scrollTop(0); 
            },500)
        } 
    });

    $(document).ready(function(){
         $.getShiftReportTypes();


         var dte = new Date($('#current_date').val());
         var dte_ = moment(dte).format('YYYY-MM-DD');

         $('input[name="dateRange"]').daterangepicker({
            singleDatePicker: true,
            showDropdowns: false,
            maxDate : dte
         })
        

      

       $('#btn_display').unbind().bind('click',function(){
            $.retrieveData();
       });


       $('#chk_all_operators').unbind().bind('click',function(){
            var is_checked = $('#chk_all_operators').prop('checked');
            if (is_checked) {
                $('#plant_operator_list').attr('disabled',true);
            }else {
                $('#plant_operator_list').removeAttr('disabled');
            }
        });

       $('#result').on('click','.trans_link',function(e){
            e.preventDefault();
            var id = $(this).attr('id');
            $.showPlantCapabilityAudit(id);
       })
       $.retrievePlantOps();
       $.retrieveData();
    });
    
</script>
@endsection


