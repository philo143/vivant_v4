
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h4>Realtime Plant Monitoring</h4>
            
            <hr>
            <div class="container-fluid">

                <div class="well bs-component col-md-12"> 
                    {{ Form::open(['class'=>'form-horizontal','id' => 'form_retrieve']) }}
                    
                    <div class="row">
                      <div class="col-md-4">
                          <div class="form-group"> 
                            {{ Form::label('plant_id', 'Plant:', ['class'=>'col-sm-3 control-label']) }}
                            <div class="col-lg-9"> 
                                <select class="form-control input-sm" id="plant_id" name="plant_id">
                                    @foreach($plants as $id => $plant)
                                        <option value="{{ $id }}">{{ $plant }}</option>
                                    @endforeach
                                </select>   
                            </div>
                        </div>

                        <div class="form-group"> 
                            {{ Form::label('resource_id', 'Unit :', ['class'=>'col-sm-3 control-label']) }}
                            <div class="col-lg-9"> 
                                {{ Form::select('resource_id', $resources, '',['class'=>'form-control input-sm', 'id' => 'resource_id']) }}
                            </div>
                        </div>

                      </div>
                      <div class="col-md-8">
                          <button class="btn btn-danger" type="button" style="width:100%;height:80px;font-size:300%; font-weight:bold;" id="btn_acknowledge_al">Acknowledge Actual Load</button>
                      </div>
                    </div>

                    {{ Form::close() }}
                </div>

                
                <div id="info_box"></div>
                <h5>Result</h5>
                <hr>

                <div class="well bs-component col-md-12">
                    <center id="canvas_title" style="text-align: center; font-size:16px; font-weight: bold; padding: 6px;"></center>
                    <canvas id="myChart" style="width: 100%; height: 300px;"></canvas>
                </div>
                

                <div> 
                    <div class="col-md-2 info_boxes">
                        
                        <div class="alert alert-info">
                            Current Hour
                            <p id="current_hour"></p>
                        </div>
                    </div>

                    <div class="col-md-2 info_boxes" >
                        
                        <div class="alert alert-info">
                             Current Interval
                             <input type="hidden" name="current_interval" id="current_interval" value="">
                            <p id="current_interval_display"></p>
                        </div>
                    </div>

                    <div class="col-md-4 info_boxes" >
                        
                        <div class="alert alert-info">
                             RTD <span name="resource_name"></span>
                            <p id="current_rtd"></p>
                        </div>
                    </div>
                    <div class="col-md-4 info_boxes" >
                        
                        <div class="alert alert-info">
                             Previous ACTUAL <span name="resource_name"></span>
                            <p id="previous_actual"></p>
                        </div>
                    </div>

                </div>


                
                

                <h5 style="">Shift Report</h5>
                <hr>
                <div id="shift_report">
                        
                </div>

                <div class="row">
                  <div class="col-md-8">
                      <button class="btn btn-primary" id="btn_submit_report">Submit Report</button>
                  </div>
                </div>

                <div style="margin-top: 20px;">
                    <table id="tbl_shift_report">
                        
                    </table>
                </div>

                
                
            </div>  


        </div>    
    </div>
    </div>
    <br><br>
@stop




@section('scripts')
<style type="text/css">
    .info_boxes {
        padding:4px;
    }

    .info_boxes div {
        border:1px solid #4f9aad;
        text-align: center;
        color:#204954;
        font-weight: bold;
        font-size:14px;
    }

    .info_boxes div p {
        font-size:320%; 
    }

    .action_boxes {
        height:80px;
        border:1px solid #4f9aad; 
        padding:10px; 
        margin:0px; 
        color:#204954; 
        font-weight: bold;
    }

    .action_boxes label {
        color:#204954; 
        font-weight: bold;
    }

    table#tbl_shift_report {
        width:100%;
    }
    table#tbl_shift_report tr {
        border-bottom: 1px solid #cccccc;
    }

    table#tbl_shift_report td {
        padding:4px;
    }
</style>

<script type="text/javascript">
    var myLineChart;
    var _previous_actual = '';
    var _previous_interval = '';
    var _data = {};
    $.extend({
         retrieve : function(){
            var plant = $('#plant').val();
            var parameters = $('#form_retrieve').serialize();

            $('span[name=resource_name]').html($("#resource_id option:selected"). text());
            $.ajax({
                url : "/realtime_plant_monitoring/retrieve",                 
                data : parameters,
                type : "POST",
                async : true,
                error : function(error){
                    var error_msgs = '';
                    $.each(error.responseJSON,function(key,i){                          
                        error_msgs += '<li>'+i+'</li>'
                    })                
                    $('#retrieve_info_box').html('<ul>'+error_msgs+'</ul>').addClass('alert alert-danger')          
                },
                success : function(data){
                    $('#current_hour').html(data.current_hour);
                    var x1 = data.current_interval.split(':');
                    var current_interval_display = x1[0]+ ':' + x1[1] + 'H';

                    $('#current_interval_display').html(current_interval_display);
                    $('#current_interval').val(data.current_interval);
                    $('#current_rtd').html($.formatNumberToSpecificDecimalPlaces(data.current_rtd,1,'&nbsp;'));
                   
                    $('#previous_hour').html(data.previous_hour);

                    var x2 = data.previous_interval.split(':');
                    var previous_interval_display = x2[0]+ ':' + x2[1] + 'H';
                    $('#previous_actual').html($.formatNumberToSpecificDecimalPlaces(data.previous_actual,1,'&nbsp;'));

                    _previous_actual = data.previous_actual;
                    _previous_interval = data.previous_interval;
                    // actual load
                    var actual = data.cur_actual_load;
                    var is_acknowledged = false;

                    var previous_actual = data.prev_actual_load;
                    if (previous_actual != null) {
                          $('#previous_actual').html($.formatNumberToSpecificDecimalPlaces(previous_actual.actual_load,1,'&nbsp;'));

                          is_acknowledged = previous_actual.actual_load_acknowledged === 1 ? true : false;
                    }else {
                          $('#previous_actual').html('&nbsp;');
                    }



                    if (is_acknowledged ) {
                        $('#btn_acknowledge_al').removeAttr('class').addClass('btn btn-success');
                    }else {
                        $('#btn_acknowledge_al').removeAttr('class').addClass('btn btn-danger');
                        $('#btn_acknowledge_al').unbind().bind('click',function(){
                            $.acknowledgeAL();
                        });
                    }


                    $.retrieveShiftReport();

                    // title 
                    var dte = moment(data.date).format('MMMM DD, YYYY');
                    var title = 'Realtime Plant Monitoring<br>'+dte + ' - ' + $("#resource_id option:selected"). text();
                    $('#canvas_title').html(title)
                    $.populateChart(data.previous_hour,data.current_hour,data.list,data.actual_load_list,title);


                    _data['previous_hour'] = data.previous_hour;
                    _data['previous_intrainterval'] = data.previous_interval;
                    _data['current_hour'] = data.current_hour;
                    _data['current_intrainterval'] = data.current_interval;
                    _data['current_rtd'] = data.current_rtd;
                    _data['previous_actual_load'] = data.previous_actual;
                    _data['actual_load_list'] = data.actual_load_list;
                    _data['grid_list'] = data.list;
                    // actual load
                    var actual = data.cur_actual_load;
                    if (actual != null) {
                        _data['current_actual_load'] = actual.actual_load;
                    }else {
                         _data['current_actual_load'] = null;
                    }
                }
            })
         } // 

         ,retrieveShiftReport : function(){
            $.ajax({
                url : "/realtime_plant_monitoring/retrieveTradingShiftReport",   
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },              
                data : {},
                type : "POST",
                async : true,
                error : function(error){
                    var error_msgs = '';
                    $.each(error.responseJSON,function(key,i){                          
                        error_msgs += '<li>'+i+'</li>'
                    })                
                    $('#retrieve_info_box').html('<ul>'+error_msgs+'</ul>').addClass('alert alert-danger')          
                },
                success : function(data){

                    if (data != null) {
                        var html = '';
                        for (var i=0;i<data.length;i++){
                            var rec = data[i];
                            var created_at = new Date(rec.created_at);
                            var created_at_dte = moment(created_at).format('MMM DD, YYYY');
                            var created_at_tme = moment(created_at).format('H:mm:ss');

                            html+='<tr><td style="width:220px;">Posted by: <b>'+rec.user.fullname+'</b></td>';
                            html+='<td style="width:150px;">Date: <b>'+created_at_dte+'</b></td>';
                            html+='<td style="width:150px;">Time: <b>'+created_at_tme+'</b></td>';
                            html+='<td>&nbsp;</td>';
                            html+='</tr>';

                            html+='<tr><td colspan="4">'+rec.shift_report_type.description+': '+rec.report+'</td></tr>'
                        }   
                        $('#tbl_shift_report').html(html); 
                    }
                    
                }
            })
         } // 

         ,populateChart : function(prev,curr,list, al_list,title){
            var ctx = document.getElementById("myChart");
            var labels = [];
            var my_rtd_data = [], my_al_data = [];

            for(var x=prev;x<=curr;x++){
                for (var i=5;i<=60;i+=5){
                    var i_val = '', cur_hr = x, prev_hr = x-1;
                    if ( i === 60 ) {
                        i_val = $.strPad(cur_hr,2,'0') + ':00:00';
                        labels.push(x +':00');
                    }else {
                        i_val = $.strPad(prev_hr,2,'0') + ':'+$.strPad(i,2,'0') + ':00';
                        labels.push(x +':' + $.strPad(i,2,'0'));
                    }

                    

                    

                    // for rtd
                    var m_val = null;
                    if (typeof list[x] !== 'undefined') {
                        if (typeof list[x][i_val] !== 'undefined') {
                            m_val = list[x][i_val].mw;
                        }
                    }
                    my_rtd_data.push(m_val);


                    // for al
                    var al_val = null;
                    if (typeof al_list[x] !== 'undefined') {
                        if (typeof al_list[x][i_val] !== 'undefined') {
                            al_val = al_list[x][i_val].actual_load;
                        }
                    }
                    my_al_data.push(al_val);


                }
            }
            
            var data = {
                labels: labels,
                datasets: [
                    {
                        label: "RTD ",
                        fill: false,
                        lineTension: 0.1,
                        backgroundColor: "rgba(75,192,192,0.4)",
                        borderColor: "rgba(75,192,192,1)",
                        borderCapStyle: 'butt',
                        borderDash: [],
                        borderDashOffset: 0.0,
                        borderJoinStyle: 'miter',
                        pointBorderColor: "rgba(75,192,192,1)",
                        pointBackgroundColor: "#fff",
                        pointBorderWidth: 1,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: "rgba(75,192,192,1)",
                        pointHoverBorderColor: "rgba(220,220,220,1)",
                        pointHoverBorderWidth: 2,
                        pointRadius: 2,
                        pointHitRadius: 10,
                        data: my_rtd_data,
                        spanGaps: false,
                    }
                    ,{
                        label: "Actual Load ",
                        fill: false,
                        lineTension: 0.1,
                        backgroundColor: "#ff0000",
                        borderColor: "#ff0000",
                        borderCapStyle: 'butt',
                        borderDash: [],
                        borderDashOffset: 0.0,
                        borderJoinStyle: 'miter',
                        pointBorderColor: "#ff0000",
                        pointBackgroundColor: "#fff",
                        pointBorderWidth: 1,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: "#ff0000",
                        pointHoverBorderColor: "#ff0000",
                        pointHoverBorderWidth: 2,
                        pointRadius: 2,
                        pointHitRadius: 10,
                        data: my_al_data,
                        spanGaps: false,
                    }
                ]
            };
            
            if ( typeof myLineChart !== 'undefined') {
                myLineChart.destroy();
                $('#myChart').removeAttr('width');
                $('#myChart').removeAttr('height');
                $('#myChart').css('width: 100%; height: 300px;');
            }
            

             myLineChart = Chart.Line(ctx, {
                data: data,
                options: {
                    responsive: true,
                    title: {
                        display: false,
                        text: title
                    },
                    legend: {
                        display: false
                    },
                    tooltips: {
                        enabled : true,
                        displayColors: false,
                        mode: 'single',
                        callbacks: {
                            label: function(tooltipItems, data) { 
                                return tooltipItems.yLabel + ' MW';
                            }
                        }
                    },
                    scales: {
                        xAxes: [{
                            ticks: {
                                autoSkip: false,
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }]
                    }
                }
            });
         } //
         ,list_unit : function(){
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
         

        ,submitShiftReport : function(){

            
            if ( $('#shift_report').summernote('isEmpty') ) {
                var error_msgs = '<li>Report is required</li>';
                bootbox.alert('<ul>'+error_msgs+'</ul>');   
             }else {
                 var params = {};
                 params['type'] = 'activity';
                 params['report'] = $('#shift_report').summernote('code');

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
                        bootbox.alert('<ul>'+error_msgs+'</ul>');   
                    },
                    success : function(data){
                        $.retrieveShiftReport();
                        $('#shift_report').summernote('reset');
                        bootbox.alert('<ul>Successfully saved.</ul>');   
                    }
                }); 
            }
                
        } // eof

         ,acknowledgeAL : function(result){
            if (  $('#previous_actual').html() !== '&nbsp;') {
                var params = {'plant_id':$('#plant_id').val()};
                params['actual_load'] = $('#previous_actual').html().replace(/,/gi, "");
                params['resource_id'] = $('#resource_id').val();
                params['hour'] = $('#current_hour').html();
                params['interval'] = _previous_interval;


                $.ajax({
                    url : "/realtime_plant_monitoring/acknowledgeAL",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data : params,
                    type : "POST",
                    error : function(error){
                        console.log('Error : '+error)
                    },
                    success : function(data){
                         $('#btn_acknowledge_rtd').removeAttr('class').addClass('btn btn-success');
                         $.retrieveShiftReport();
                         $('#btn_acknowledge_al').removeAttr('class').addClass('btn btn-success');
                         bootbox.alert(data);   
                    }
                });
            }else {
                bootbox.alert('<ul>No actual load available</ul>');  
            }
            
            
                
        } // eof
    });
    $(document).ready(function(){
        $('#shift_report').summernote({
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
          height:100
        });


        $('#resource_id').unbind().bind('change',function(){
            $.retrieve()
        });
        $.retrieve();

        $('#plant_id').unbind().bind('change',function(){
            $.list_unit()
        });

        $('#btn_submit_report').unbind().bind('click',function(){
            $.submitShiftReport();
        });


        // var socket = io('http://127.0.0.1:3000');
        socket.on("app.dashboard.rtd_grid:App\\Events\\RtdGrid", function(message){
            var data = message.data;
            var resource_name = $("#resource_id option:selected").text();
            var is_update_chart_only = true;
            var interval = '';
            _data['previous_rtd'] =  _data['current_rtd'];
            var rtd = null
            if ( typeof data[resource_name] !== 'undefined') {
                rtd = data[resource_name].mw;
                interval = $.trim(data[resource_name].interval.split("-")[1].replace(/H\)/g, ""));
            }
            _data['current_rtd'] = rtd;

            // update ui
            $('#current_rtd').html($.formatNumberToSpecificDecimalPlaces(_data['current_rtd'],1,'&nbsp;'));
            $('#previous_rtd').html($.formatNumberToSpecificDecimalPlaces(_data['previous_rtd'],1,'&nbsp;'));
            

            if ( typeof _data['grid_list'][ _data['current_hour'] ] === 'undefined' ) { // if array not yet created, defined array keys
                _data['grid_list'][ _data['current_hour'] ] = {};
                _data['grid_list'][ _data['current_hour'] ][ _data['current_intrainterval'] ] = { 'mw' : null} ;
            }
            _data['grid_list'][ _data['current_hour'] ][ _data['current_intrainterval'] ] = { 'mw' : _data['current_rtd']} 


            if ( is_update_chart_only && interval.length > 0 ) {
                var tmp = interval.split(':');
                var key = tmp[0]+ ':' + tmp[1];
                var index = myLineChart.chart.config.data.labels.indexOf(key);
                myLineChart.chart.config.data.datasets[0].data[index] = rtd;
                myLineChart.update();

            }else {
                $.populateChart(_data['previous_hour'],_data['current_hour'],_data['grid_list'],_data['actual_load_list'],$('#canvas_title').html());
            }


        });


        socket.on("app.realtime_monitoring.data:App\\Events\\RealtimeMonitoringData", function(message){
            var data = message.data;
            var is_update_chart_only = true;
            var resource_name = $("#resource_id option:selected").text();
            if (  _data['current_intrainterval'] !== data.current_intrainterval ) {

                if ( _data['current_hour'] !== data.current_hour ) {
                    _data['previous_hour'] = _data['current_hour'];
                    _data['current_hour'] = data.current_hour;

                    is_update_chart_only = false;
                }else {
                    is_update_chart_only = true;
                }


                _data['previous_intrainterval'] = _data['current_intrainterval'];
                _data['current_intrainterval'] = data.current_intrainterval;
                _data['previous_actual_load'] = _data['current_actual_load'];

                var island_mode = null;
                if ( typeof data.resource_actual_load_data[resource_name] !== 'undefined') {
                    _data['current_actual_load'] = data.resource_actual_load_data[resource_name].actual_load;
                    island_mode = data.resource_actual_load_data[resource_name].im;
                }else {
                    _data['current_actual_load'] = null;
                }

                if ( typeof _data['actual_load_list'][ _data['current_hour'] ] === 'undefined' ) { // if array not yet created, defined array keys
                    _data['actual_load_list'][ _data['current_hour'] ] = {};
                    _data['actual_load_list'][ _data['current_hour'] ][ _data['current_intrainterval'] ] = { 'actual_load' : null} ;
                }
                _data['actual_load_list'][ _data['current_hour'] ][ _data['current_intrainterval'] ] = { 'actual_load' : _data['current_actual_load']} 

                // update ui
                $('#current_hour').html(_data['current_hour']);
                var x1 = _data['current_intrainterval'].split(':');
                var current_interval_display = x1[0]+ ':' + x1[1] + 'H';

                $('#current_interval_display').html(current_interval_display);
                $('#current_interval').val(_data['current_intrainterval']);

                $('#previous_hour').html(_data['previous_hour']);

               var x2 = _data['previous_intrainterval'].split(':');
               var previous_interval_display = x2[0]+ ':' + x2[1] + 'H';

               $('#previous_interval').html(previous_interval_display);
               var is_acknowledged = false;

               var tmp = _data['current_intrainterval'].split(':');
               var interval_selected = tmp[0] + tmp[1];
               $('#interval').val(interval_selected);

               if ( _data['current_actual_load'] != null ) {
                    var al = $.formatNumberToSpecificDecimalPlaces(_data['current_actual_load'],0).replace(/,/g, "");
                    $('#actual_load').val(al);
                    $('#current_actual').html($.formatNumberToSpecificDecimalPlaces(_data['current_actual_load'],1,'&nbsp;'));
                    is_acknowledged = data.resource_actual_load_data[resource_name].is_rtd_acknowledged === 1 ? true : false;
               }
               
                
               

               // previous actual load
               var is_acknowledged = false;
               if (_data['previous_actual_load'] != null) {
                    $('#previous_actual').html($.formatNumberToSpecificDecimalPlaces(_data['previous_actual_load'],1,'&nbsp;'));
                    is_acknowledged = previous_actual.actual_load_acknowledged === 1 ? true : false;
               }else {
                    $('#previous_actual').html('&nbsp;');
               }

               if (is_acknowledged ) {
                    $('#btn_acknowledge_al').removeAttr('class').addClass('btn btn-success').unbind();
                }else {
                    $('#btn_acknowledge_al').removeAttr('class').addClass('btn btn-danger');
                    $('#btn_acknowledge_al').unbind().bind('click',function(){
                        $.acknowledgeAL();
                    });
                }



               // update grid/chart
               if ( is_update_chart_only ) {
                    var tmp = _data['previous_intrainterval'].split(':');
                    var key = tmp[0]+ ':' + tmp[1];
                    var index = myLineChart.chart.config.data.labels.indexOf(key);
                    myLineChart.chart.config.data.datasets[1].data[index] = 88;
                    myLineChart.update();

                }else {
                    $.populateChart(_data['previous_hour'],_data['current_hour'],_data['grid_list'],_data['actual_load_list'],$('#canvas_title').html());
                }


               // update shiftreport
               $.retrieveShiftReport();
            }
            
        });
        
    });


</script>
@endsection


