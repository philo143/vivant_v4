
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
            <h4>Trading Shift Report</h4>
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
        saveActivityReport : function(summernote_element,info_box_element,hour,interval){
             if ( $('#'+summernote_element).summernote('isEmpty') ) {
                var error_msgs = '<li>Report is required</li>';
                $('#'+info_box_element).html('<ul>'+error_msgs+'</ul>').addClass('alert alert-danger');
             }else {
                 var params = {};
                 params['type'] = 'activity';
                 params['report'] = $('#'+summernote_element).summernote('code');
                 params['hour'] = hour;
                 params['interval'] = interval;

                 $.ajax({
                    url : "/trading_shift_report/store",
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
        ,retrieveData : function(){
            var s_date = $.trim($('#dateRange').val());
            var e_date = $.trim($('#dateRange').val());
            var hour = $('#hour').val();


            var parameters = $('#form_retrieve').serialize()+'&s_date='+s_date +'&e_date='+e_date;
            var is_checked = $('#chk_all_traders').prop('checked');
            if (!is_checked) {
               parameters+='&submitted_by=' + $('#trader_list').val();
            }

            $.ajax({
                url : "/trading_shift_report/retrieve",                 
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

                    if (typeof data.length !== 'undefined') {
                        data = {};
                    }

                    $.populateDisplay(data);

                }
            })
        }


        ,getShiftReportTypes : function(){
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
                        REPORT_TYPES = data;
                    }
                }) 
        }

        ,populateDisplay : function(data){
            console.log(data)
             var hours = $('#hour').val();

             var error_msgs = [];
             if (hours.length <= 0 ) {
                error_msgs.push('<li>Hour selection is required</li>');
             }
              
             $('#result').html(''); 
             if (error_msgs.length > 0 ){
                $('#retrieve_info_box').html('<ul>'+error_msgs+'</ul>').addClass('alert alert-danger')          
             }else {
                var result = '<table class="table table-bordered table-condensed table-striped">';
                var hour_list = hours.split(',');
                var interval_list = [];
                for(var x=5;x<=60;x+=5){
                    interval_list.push(x);
                }
                interval_list.push(0);
                var i = '', hr = '', prev_hr = '';
                $.each( hour_list, function( i, hr ) {
                    prev_hr = hr - 1;
                    if (hr === 1) {
                        prev_hr = 0;
                    }
                    $.each( interval_list, function( ii, interval ) {
                            var pad = '00';
                            var new_interval = (pad + interval).slice(-pad.length)

                            $.each( REPORT_TYPES, function( iii, type ) {

                                var type_id = type.id;
                                var type_name = type.type ;
                                var dte = new Date($('#dateRange').val());
                                var dte_selected = moment(dte).format('YYYY-MM-DD');
                                var intra_interval = '', time= '';
                                if (interval === 0) {
                                    intra_interval = $.strPad(hr,2,'0') + ':' + new_interval;
                                    time = $.strPad(hr,2,'0') + ':' + new_interval +':00';
                                }else {
                                    intra_interval = $.strPad(prev_hr,2,'0') + ':' + new_interval;
                                    time = $.strPad(prev_hr,2,'0') + ':' + new_interval+':00';
                                }

                                console.log(time)
                                var reports_html = '';
                                if (typeof data[dte_selected] !=='undefined') {
                                    if (typeof data[dte_selected][hr] !=='undefined') {
                                        if (typeof data[dte_selected][hr][time] !=='undefined') {
                                            if (typeof data[dte_selected][hr][time][type_id] !=='undefined') {
                                                var list = data[dte_selected][hr][time][type_id];
                                                for (var x=0;x<list.length;x++){

                                                    var dt = '[' + moment(list[x].created_at).format('MMM D,YYYY') + '] [' + moment(list[x].created_at).format('HH:MM:SS') + ']'

                                                    var cur_html = '<div style="background:#FFF;margin:2px;padding:3px;border-radius:3px">';
                                                    cur_html +='<b>'+dt+'  ' + list[x].fullname + '</b><br>';
                                                    cur_html += list[x].report;
                                                    cur_html +='</div>'
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

                                // if (iii==0) {
                                //     result+='<td rowspan="3" style="width:70px; font-weight:bold;  text-align:center; vertical-align:middle; ">OFF</td>';
                                // }
                                result+='</tr>';

                            });

                    });
                });    

                result+='</table>';

                $('#result').html(result);

                // add summer note
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
        },
        showOfferAudit : function (id) {
            $.ajax({
                url : '{{ route('trading_shift_report.transactions') }}',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data : {id : id,type : 'offer'},
                type : "POST",
                async : false,
                error : function(error){
                    console.log('Error : '+error)
                },
                success : function(msg){
                    var data = {}, intervals_data = {};
                    if ( typeof msg.data != 'undefined') {
                        data = $.parseJSON(msg.data);
                        intervals_data = typeof data.intervals !== 'undefined' ? data.intervals : {};
                    }
                    console.log(data);
                    var sh = '<tr><th>Hour</th>' +
                        '<th>Price/Quantity</th>' +
                        '<th>Ramp&nbsp;Rate</th>' +
                    '</tr>';
                    var h = '<tr><td>Delivery&nbsp;Date</td>' +
                        '<td colspan="2">'+msg.delivery_date+'</td></tr>' +
                        '<tr><td>Resource&nbsp;ID</td>' +
                        '<td colspan="2">'+msg.resource_id+'</td></tr>' +
                        '<tr><td>MMS&nbsp;Transaction&nbsp;ID</td>' +
                        '<td colspan="2">'+( data.transaction_id_wesm === null ? '' : data.transaction_id_wesm )+'</td></tr>';
                    var rows = '';
                    var bg = '';
                    var loop_interval_data = {};
                    var price_value = '';
                    var ramp_rate = '';
                    var remarks = '';
                    var c_pv = '';
                    for(var x=1; x<=24; x++){
                        bg = (x%2==0) ? '#DDDDDD' : '#FFFFFF';
                        //intervals_data = data['intervals_']
                        loop_interval_data = intervals_data[x];

                        // prive/value
                        price_value = '';
                        for (var c=0;c<=7;c++){
                            if ( typeof loop_interval_data['b_p'+c] != 'undefined' &&
                                typeof loop_interval_data['b_v'+c] != 'undefined' ) {

                                c_pv = '(' + loop_interval_data['b_p'+c] + ',' + loop_interval_data['b_v'+c] + ')';

                                if ( price_value.length > 1 ) {
                                    price_value+= ',';
                                }
                                price_value+= c_pv;
                            }
                        }

                        // ramp rate
                        ramp_rate = '';
                        if ( typeof loop_interval_data['breakpoint0'] != 'undefined' &&
                            typeof loop_interval_data['ramp_up0'] != 'undefined' &&
                            typeof loop_interval_data['ramp_down0'] != 'undefined') {
                            ramp_rate = '(' + loop_interval_data['breakpoint0'] + ',' + loop_interval_data['ramp_up0'] + ',' + loop_interval_data['ramp_down0']  + ')';
                        }


                        rows+= '<tr><td>'+ x +'</td>' +
                            '<td>'+ price_value +'</td>' +
                            '<td>'+ ramp_rate +'</td>' +
                        '</tr>';

                    }
                    html = '<table id="t1" class="table table-bordered table-condensed">'+ h + sh + rows +'</table>';         
                    bootbox.alert({ 
                      title: "Offer Log",
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


       $('#chk_all_traders').unbind().bind('click',function(){
            var is_checked = $('#chk_all_traders').prop('checked');
            if (is_checked) {
                $('#trader_list').attr('disabled',true);
            }else {
                $('#trader_list').removeAttr('disabled');
            }
        });
       $('#result').on('click','.trans_link',function(e){
            e.preventDefault();
            if ( $(this).parent().text().indexOf('Nomination') >= 0 ) {
                $('#modal_nom').modal('show')
                $.showNominationAudit($(this).attr('id'));
            }else if ( $(this).parent().text().indexOf('Load Profile') >= 0 ) {
                $('#modal_load_profile').modal('show')
                $.downloadLoadProfileFile($(this).attr('id'));
            }else if ( $(this).parent().text().indexOf('Offer') >= 0 ) {
                $('#modal_offer').modal('show')
                $.showOfferAudit($(this).attr('id'));
            }else if ( $(this).parent().text().indexOf('BCQ') >= 0 ) {
                $('#modal_bcq').modal('show')
                $.showBCQAudit($(this).attr('id'));
            }else if ( $(this).parent().text().indexOf('ACC') >= 0 ) {
                $('#modal_acc').modal('show')
                $.downloadUploadedDanWanAccFile($(this).attr('id'));
            }else{
                $('#modal_plant').modal('show')
                $.showPlantCapabilityAudit($(this).attr('id'));
            }
       })

       $.retrieveData();
    });
    
</script>
@endsection


