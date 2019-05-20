
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
            <h4>Plant Operational Shift Report View</h4>
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
                        
                        {{ Form::label('plant', 'Plant', ['class' => 'col-lg-2 control-label']) }}
                        <div class="col-sm-3">
                           {{ Form::select('plant', $plants,'' ,
                                [
                                 'class'=>'form-control input-sm'
                                 ,'id' => 'plant'])  }}
                        </div>  
                    </div>


                     <div class="form-group">
                        <div class="col-lg-2" style="text-align: right; margin-top: 15px;" >
                            {{  Form::checkbox('chk_all_operators', '', true
                                    , ['id'=>'chk_all_operators' ]) }} &nbsp;<span class="control-label">All Plant Ops</span>
                        </div>
                        <div class="col-lg-3" id="checkbox_container" style="margin-top:10px;">
                            {{ Form::select('plant_operator_list', $plant_operators,'' ,
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

                    $.populateDisplay(list);

                }
            })
        }

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
                            var new_interval = (pad + interval).slice(-pad.length)
                            var im = 0;

                            var intra_interval = '', time = '';
                            if (interval === 0) {
                                intra_interval = $.strPad(hr,2,'0') + ':' + new_interval;
                                 time = $.strPad(hr,2,'0') + ':' + new_interval +':00';
                            }else {
                                intra_interval = $.strPad(prev_hr,2,'0') + ':' + new_interval;
                                time = $.strPad(prev_hr,2,'0') + ':' + new_interval+':00';
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
                                result+='</td>';


                                if (iii==0) {
                                    result+='<td id="im_'+hr+new_interval+'" rowspan="3" style="width:70px; font-weight:bold;  text-align:center; vertical-align:middle; "></td>';
                                }
                                result+='</tr>';

                            });
                            var island_mode = im == 1 ? 'ON' : 'OFF';
                            var elm = $.trim('im_'+hr+new_interval);
                            island_modes.push({'element' : elm , 'value' : island_mode});
                            
                    });
                });    

                result+='</table>';

                $('#result').html(result);
                for (var x=0;x<island_modes.length;x++){
                    var elm = island_modes[x].element;
                    var island_mode = island_modes[x].value;
                    $('#'+elm).html(island_mode);
                }
                
 
             }
        },
        showPlantCapabilityAudit : function (id) {
            $.ajax({
                url : '{{ route('plant_shift_report.transactions') }}',
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

         $('input[name="dateRange"]').daterangepicker({
            singleDatePicker: true,
            showDropdowns: false
         })
        

      

       $('#btn_display').unbind().bind('click',function(){
            $.retrieveData();
       });


       $('#plant').unbind().bind('change',function(){
            $.retrievePlantOps();
       });
       $('#plant').trigger('change');
       


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

        
       $.retrieveData();
    });
    
</script>
@endsection


