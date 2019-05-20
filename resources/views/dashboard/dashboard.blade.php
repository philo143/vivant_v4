@extends('layouts.app')

@section('content')
<style>
    table.rtd-grid-table td.rtd-grid-box {
        background: none repeat scroll 0 0 #f9f9f9;
        margin: 10px;
        padding: 10px;
        min-width: 130px;
    }

    table.rtd-grid-table td.rtd-grid-section-title {
        background: none repeat scroll 0 0 #f9f9f9;
        font-weight:bold;
        font-size:13px;
        padding-bottom: 10px;
    }

    table.rtd-grid-table td.rtd-grid-section-data {
        background: none repeat scroll 0 0 #80cde5;
        margin: 10px;
        padding: 4px;
        height: 60px;
        text-align: center;
        vertical-align: middle;
        font-size:20px;
    }
    #rtd_grid_mw{
        font-size:9.5em;
        min-width:364px;
    }

    .price {
        color : #62c462;
        font-weight: bold;
    }
    .widget {
        /* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#409cce+0,e0e0e0+100 */
        /*background: #409cce; /* Old browsers */*/
        /*background: -moz-linear-gradient(top, #409cce 0%, #e0e0e0 100%); /* FF3.6-15 */*/
        /*background: -webkit-linear-gradient(top, #409cce 0%,#e0e0e0 100%); /* Chrome10-25,Safari5.1-6 */*/
        /*background: linear-gradient(to bottom, #409cce 0%,#e0e0e0 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */*/
        /*filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#409cce', endColorstr='#e0e0e0',GradientType=0 ); /* IE6-9 */*/
    }

    .widget td {
        padding:6px;
    }
    table#nodal_prices_table tr:first-child td {
        text-align: center;
        font-weight: bold;
    }

    table#nodal_prices_table td:nth-child(2) {
        background-color: #CBE0F1;
    }    
    @media (max-width: 550px) {
        table.rtd-grid-table td.rtd-grid-section-data {
            height: 20%;
            text-align: center;
            vertical-align: middle;
            font-size:3vw;
        }
        table.rtd-grid-table td.rtd-grid-section-title {
            font-size:2vw;
        }
        .panel-heading {
            font-size:3vw;
        }
        #rtd_grid_mw{
            height: 88px;
            font-size:9.4vw;
            min-width:0;
        }
    }

    .widget-body {
        background-color: #f8fcfc;
    }

    .actual-load-body {
        padding:4px;
        background-color:#80cde5;
        color : #16566a;
    }

    .actual-load-body h6 {
        color:#eaf7fb; margin-top:6px; padding-left:10px; margin-bottom: 0px;
    }
    .actual-load-body div {
        font-size: 4em;
        font-weight: bold;
        text-align: center;
        color :#15576a;
    }


    .panel-actions {
      margin-top: -20px;
      margin-bottom: 0;
      text-align: right;
    }
    .panel-actions a {
      color:#333;
    }
    .panel-fullscreen {
        display: block;
        z-index: 9999;
        position: fixed;
        width: 100%;
        height: 100%;
        top: 0;
        right: 0;
        left: 0;
        bottom: 0;
        overflow: auto;
    }


    .rtd-grid-panel {
        border : 1px solid #aadeee;
    }
</style>
    {{-- {{ dd($rtd_data) }} --}}
    @php 
    $is_with_nodal_prices_grid = 0;
    $nodal_prices_resources = array();
    $intra_intervals =array();
    
    $is_with_dap_prices_grid = 0;
    $dap_prices_resources = array();

    $is_with_nodal_price_ticker = 0;

    $is_with_dap_schedules_grid = 0;
    $dap_schedules_resources = array();

    $is_with_dap_prices_and_schedules_grid = 0;
    $dap_prices_and_schedules_resources = array();

    $is_with_hap_prices_grid = 0;
    $hap_prices_resources = array();

    $is_with_hap_prices_and_schedules_grid = 0; 
    $hap_prices_and_schedules_resources = array();

    $is_with_actual_load = 0;
    $actual_load_resources = array();
    
    @endphp
    @if($user_widgets->count() > 0)
        
        <div class="col-lg-12">
            <div class="jumbotron dashboard-preloader">
                <h2><center>Loading Dashboard <br\> <h4>Please Wait...</h4></center></h2>
            </div>
            <div class="main-content" style="display:none; display: flex; flex-direction: row;  flex-wrap: wrap;">
                @foreach($user_widgets as $user_widget)
                    @if($user_widget->widgets->id == 1) <!-- FOR NODAL PRICES TICKER -->     
                        @php 
                        $is_with_nodal_price_ticker = 1;
                        @endphp

                        <div class="col-lg-12" id="nodal_price_ticker">
                            <div class="panel panel-default" style="background-color:#F9F9F9">
                                <div class="panel-body row no-gutter">
                                    <span class="col-lg-3 col-xs-12">
                                        <div class="btn-group col-lg-6 col-xs-6" style="padding:0" data-toggle="buttons" id="ticker_btn">
                                          <label class="btn btn-primary active btn-sm col-lg-5 col-xs-6">
                                            <input type="radio" name="ticker_options" id="gen_ticker_btn" checked value="GEN" class="form-control"> GEN
                                          </label>
                                          <label class="btn btn-primary btn-sm col-lg-5 col-xs-6">
                                            <input type="radio" name="ticker_options" id="load_ticker_btn" value="LD" class="form-control"> LOAD
                                          </label>   
                                        </div>
                                        <div class="col-lg-6 col-xs-6" style="padding:0">
                                            <select class="form-control input-sm" id="zone">
                                                @foreach($zones as $zone)
                                                    <option value="{{ $zone->zone_prefix }}">{{ $zone->zone }}</option>
                                                @endforeach
                                            </select>
                                        </div> 
                                    </span>                                    
                                    <span class="col-lg-9 col-xs-12" id="marquee_container">
                                        
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if($user_widget->widgets->id == 2) <!-- FOR RTD GRID -->                        
                        <div class="col-lg-6 col-xl-4" id="rtd_grid_{{ $user_widget->resources->resource_id }}">
                            <div class="panel panel-info rtd-grid-panel">
                                <div class="panel-heading">RTD Grid for <b>{{ $user_widget->resources->resource_id }}</b> with <b>+1.5% -3%</b></div>

                                <div>

                                    <table width="100%" class="rtd-grid-table" cellspacing="4" cellpadding="4" style="border-spacing: 4px;">
                                    <tr>
                                       <td rowspan="2" class="rtd-grid-box">
                                            <table width="100%">
                                                <tr><td class="rtd-grid-section-title" id="rtd_grid_interval">Hour 15 (Interval: 15:06 - 15:10H)</td></tr>
                                                <tr><td class="rtd-grid-section-data" id="rtd_grid_mw">&nbsp;</td></tr>                            
                                            </table>
                                        </td>
                                        <td class="rtd-grid-box">
                                            <table width="100%">
                                                <tr><td class="rtd-grid-section-title">+1.5%</td></tr>
                                                <tr><td class="rtd-grid-section-data" id="rtd_grid_plus">MW</td></tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="rtd-grid-box">
                                            <table width="100%"><tr><td class="rtd-grid-section-title">-3%</td></tr>
                                                <tr><td class="rtd-grid-section-data" id="rtd_grid_minus">MW</td></tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                                    
                                </div>
                                
                            </div>
                        </div>
                    @endif


                    @if($user_widget->widgets->id == 3) <!-- FOR NODAL PRICES -->
                        @php 
                        $is_with_nodal_prices_grid = 1;
                        $nodal_prices_resources[] = $user_widget->resources->resource_id;
                        @endphp
                    @endif


                    @if($user_widget->widgets->id == 4) <!-- FOR WEATHER -->
                         @foreach ($weather as $city => $record)
                            @php 
                            // $date = date('F d, Y', strtotime($record['condition']->date));
                            $code = $record['condition']->code;
                            $condition = $record['condition']->text;

                            $temp = $record['condition']->temperature;
                            $humidity = $record['humidity'];
                            $visibility = $record['visibility'];
                            $pressure = $record['pressure'];
                            $sunrise = $record['sunrise'];
                            $sunset = $record['sunset'];
                            $forecast = $record['forecast'];
                            @endphp
                            <div class="col-lg-3"  >
                                <div class="panel panel-default weather-widget">
                                    {{-- <div class="panel-heading"><b>Manila</b> </div> --}}
                                    <table class="table table-condensed weather-list">
                                       <tr style="color :#f4f4f4;">
                                            <td style="width:60%;">
                                                <span style="font-size:16px;">{{$city}}</span><br>
                                                {{-- <span style="font-size:9px;">{{$date}}</span> --}}
                                                <br>
                                                <i class="{{ $icons[$code] }} wi-big"></i><br>
                                                <span style="font-size:9px;">{{$condition}}</span>
                                            <td>
                                            <td style="text-align:right;">
                                                <span style="font-size:30px; ">{{ $temp }}° C</span>
                                                
                                            </td>
                                       <tr> 
                                    </table>

                                    <div class="weather-summary">
                                        <table class="table table-condensed" style="margin-bottom: 0px;">
                                            <tbody><tr>
                                                <td><i class="wi-windy"></i>humidity: <br>{{$humidity}}%</td>
                                                <td><i class="wi-cloud-down"></i>visibility: <br>{{$visibility}}</td>
                                                <td><i class="wi-sprinkles"></i>pressure: <br>{{$pressure}}</td>
                                                <td><i class="wi-sunrise"></i>sunrise: <br>{{$sunrise}}</td>
                                                <td><i class="wi-sunset"></i>sunset: <br>{{$sunset}}<br></td>
                                            </tr>
                                          </tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="weather-forecast">
                                        <table style="margin-bottom: 0px; width:100%; ">

                                            @foreach ($forecast as $d => $fc)
                                                @php
                                                    $f_dte = date('d/M/Y',strtotime($fc->date));
                                                    $day = $fc->day;
                                                    $low = $fc->low;
                                                    $high = $fc->high;
                                                    $f_code = $fc->code;

                                                @endphp
                                            
                                                <tr>
                                                    <td>{{$day}}</td>
                                                    <td>{{$f_dte}}</td>
                                                    <td><i class="wi-down"></i>{{$low}}&#176</td>
                                                    <td><i class="wi-up"></i>{{$high}}&#176</td>
                                                    <td><i class="{{$icons[$f_code]}} wi-medium "></i></td>
                                                </tr>
                                            @endforeach

                                        

                                        </table>
                                    </div>


                                    
                                </div>
                            </div>

                        @endforeach
                    @endif {{-- end for the weather --}}

                    {{-- FOR DAP PRICES --}}
                    @if($user_widget->widgets->id == 9) 
                        @php 
                        $is_with_dap_prices_grid = 1;
                        $dap_prices_resources[] = $user_widget->resources->resource_id;
                        @endphp
                    @endif

                    {{-- FOR DAP SHEDULES --}}
                    @if($user_widget->widgets->id == 10) 
                        @php 
                        $is_with_dap_schedules_grid = 1;
                        $dap_schedules_resources[] = $user_widget->resources->resource_id;
                        @endphp
                    @endif


                    {{-- FOR DAP PRICES AND SCHEDULES --}}
                    @if($user_widget->widgets->id == 11) 
                        @php 
                        $is_with_dap_prices_and_schedules_grid = 1;
                        $dap_prices_and_schedules_resources[] = $user_widget->resources->resource_id;
                        @endphp
                    @endif


                    {{-- FOR HAP PRICES --}}
                    @if($user_widget->widgets->id == 7) 
                        @php 
                        $is_with_hap_prices_grid = 1;
                        $hap_prices_resources[] = $user_widget->resources->resource_id;
                        @endphp
                    @endif

                    {{-- FOR HAP PRICES and Schedules --}}
                    @if($user_widget->widgets->id == 8) 
                        @php 
                        $is_with_hap_prices_and_schedules_grid = 1;
                        $hap_prices_and_schedules_resources[] = $user_widget->resources->resource_id;
                        @endphp
                    @endif


                    @if($user_widget->widgets->id == 6) <!-- FOR ACTUAL LOAD --> 
                        @php 
                        $is_with_actual_load = 1;
                        $actual_load_resources[] = $user_widget->resources->resource_id;
                        @endphp
                        <div class="col-lg-3 col-xl-2" id="actual_load_{{ $user_widget->resources->resource_id }}">
                            <div class="panel panel-info">
                                <div class="panel-heading">Actual Load for <b>{{ $user_widget->resources->resource_id }}</b> </div>
                                <div class="panel-body actual-load-body">
                                    <h6 name="actual_load_interval">--</h6>
                                    <div name="actual_load_val">---</div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach


                

                

                @if($is_with_dap_prices_grid === 1) <!-- FOR DAP PRICES -->
                    <div class="col-lg-6 col-xl-4">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">DAP Prices</h3>
                                <ul class="list-inline panel-actions">
                                    <li><a href="#" name="panel_fullscreen" grid_redraw="dap_prices_table" role="button" title="Toggle fullscreen"><i class="glyphicon glyphicon-resize-full"></i></a></li>
                                </ul>
                                
                            </div>
                            <div class="panel-body widget-body">
                                <div style="width: 100%; height: 300px;">
                                    <canvas id="dap_prices_chart" ></canvas>
                                </div>
                                <div>
                                    <table width="100%" id="dap_prices_table" class="table table-condensed table-striped" cellspacing="4" cellpadding="4" style="border-spacing: 4px; width: 100%;">
                                        
                                        <thead>
                                            <th>Resource</th>
                                            @for ($i = 1; $i <= 24; $i++)
                                                <th style="width:80px; text-align: center;"> H{{$i}}</th>
                                            @endfor
                                        </thead>

                                        <tbody>
                                            
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            
                        </div>
                    </div>
                    
                @endif


                @if($is_with_dap_schedules_grid === 1) <!-- FOR DAP SCHEDULES -->
                    <div class="col-lg-6 col-xl-4">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">DAP Schedules</h3>
                                <ul class="list-inline panel-actions">
                                    <li><a href="#" name="panel_fullscreen" grid_redraw="dap_schedules_table"   role="button" title="Toggle fullscreen"><i class="glyphicon glyphicon-resize-full"></i></a></li>
                                </ul>                            
                            </div>
                            <div class="panel-body widget-body">
                                <div style="width: 100%; height: 300px;">
                                        <canvas id="dap_schedules_chart"></canvas>
                                    </div>
                                    <div>
                                        <table width="100%" id="dap_schedules_table" class="table table-condensed table-striped" cellspacing="4" cellpadding="4" style="border-spacing: 4px; width: 100%;">
                                            
                                            <thead>
                                                <th>Resource</th>
                                                @for ($i = 1; $i <= 24; $i++)
                                                    <th style="width:80px; text-align: center;"> H{{$i}}</th>
                                                @endfor
                                            </thead>

                                            <tbody>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                            </div>    
                            
                            
                        </div>
                    </div>
                    
                @endif

                @if($is_with_dap_prices_and_schedules_grid === 1) <!-- FOR DAP PRICES AND SCHEDULES -->
                    <div class="col-lg-12 col-xl-4">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">DAP Prices and Schedules</h3>
                                <ul class="list-inline panel-actions">
                                    <li><a href="#" name="panel_fullscreen" grid_redraw="dap_prices_schedules_table"   role="button" title="Toggle fullscreen"><i class="glyphicon glyphicon-resize-full"></i></a></li>
                                </ul>     
                            </div>
                            <div class="panel-body widget-body">
                                <div style="width: 100%; min-height: 500px;">
                                    <canvas id="dap_prices_and_schedules_chart"></canvas>
                                </div>
                                <div>
                                    <table width="100%" id="dap_prices_schedules_table" class="table table-condensed table-striped" cellspacing="4" cellpadding="4" style="border-spacing: 4px; width: 100%;">
                                        
                                        <thead>
                                            <th>Resource</th>
                                            <th>Type</th>
                                            @for ($i = 1; $i <= 24; $i++)
                                                <th style="width:80px; text-align: center;"> H{{$i}}</th>
                                            @endfor
                                        </thead>

                                        <tbody>
                                            
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            
                        </div>
                    </div>
                    
                @endif


                @if($is_with_hap_prices_grid === 1) <!-- FOR HAP PRICES -->
                    <div class="col-lg-6 col-xl-4">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">HAP Prices</h3>
                                <ul class="list-inline panel-actions">
                                    <li><a href="#" name="panel_fullscreen" grid_redraw="hap_prices_table" role="button" title="Toggle fullscreen"><i class="glyphicon glyphicon-resize-full"></i></a></li>
                                </ul>
                            </div>
                            <div class="panel-body widget-body">
                                <div>
                                    <table width="100%" id="hap_prices_table" class="table table-condensed table-striped" cellspacing="4" cellpadding="4" style="border-spacing: 4px; width: 100%;">
                                        
                                        <thead>
                                            
                                        </thead>

                                        <tbody>
                                            
                                        </tbody>
                                    </table>
                                </div>
                            </div>    
                            
                            
                        </div>
                    </div>
                    
                @endif


                @if($is_with_hap_prices_and_schedules_grid === 1) <!-- FOR HAP PRICES -->
                    <div class="col-lg-6 col-xl-4">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">HAP Prices and Schedules</h3>
                                <ul class="list-inline panel-actions">
                                    <li><a href="#" name="panel_fullscreen" grid_redraw="hap_prices_and_schedules_table" role="button" title="Toggle fullscreen"><i class="glyphicon glyphicon-resize-full"></i></a></li>
                                </ul>
                                
                            </div>
                            <div class="panel-body widget-body">
                                <div>
                                    <table width="100%" id="hap_prices_and_schedules_table" class="table table-condensed table-striped" cellspacing="4" cellpadding="4" style="border-spacing: 4px; width: 100%;">
                                        
                                        <thead>
                                            
                                        </thead>

                                        <tbody>
                                            
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            
                        </div>
                    </div>
                    
                @endif


                @if($is_with_nodal_prices_grid === 1) <!-- FOR NODAL PRICES -->
                    @php 
                    $intra_intervals =$interval['intra_intervals'];
                    @endphp
                    <div class="col-lg-6 col-xl-4" id="nodal_prices_grid">
                        <div class="panel panel-default widget">
                            <div class="panel-heading">Nodal Prices</div>
                            <div class="panel-body widget-body" style="max-height:400px; overflow-x:hidden; overflow-y:auto; padding:0px;">
                                    <table width="100%" id="nodal_prices_table" cellspacing="4" cellpadding="4" style="border-spacing: 4px; width: 100%;">
                                    <tr>
                                        <td style="text-align: center;" id="current_hour">Hour {{$interval['hour']}}</td>
                                        @foreach ($intra_intervals as $key)
                                            @php 
                                            $dt_ = date('Hi',strtotime($key)).'H';
                                            @endphp
                                            <td style="text-align: center;">{{$dt_}}</td>
                                        @endforeach

                                        
                                        
                                    </tr>

                                     @foreach ($nodal_prices_resources as $resource_id)
                                        
                                        <tr>
                                            <td>{{$resource_id}}</td>
                                            
                                            @foreach ($intra_intervals as $key)
                                                @php 
                                                $dt_ = date('Hi',strtotime($key)).'H';
                                                $np = '';
                                                if ( isset( $nodal_prices_data[$resource_id] ) ) {
                                                    if ( isset( $nodal_prices_data[$resource_id][$key] ) ) {
                                                        $np = $nodal_prices_data[$resource_id][$key];
                                                    }
                                                }
                                                @endphp
                                                <td style="text-align: right;">{{$np}}</td>
                                            @endforeach

                                        </tr>
                                    @endforeach


                                </table>
                            </div>
                            
                        </div>
                    </div>
                    
                @endif
                
                 @if($user_widget->widgets->id == 7) <!-- FOR TWITTER -->
                    <div class="col-lg-3 "  >
                        <div class="panel panel-default widget">
                        {{-- <div class="panel-heading">Acacia twitter list</div> --}}
                            <a class="twitter-timeline" data-width="450" data-height="400" href="https://twitter.com/acaciasoftgroup/lists/acacia-twitter-list">A Twitter List by acaciasoftgroup</a> <script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>

                        </div>
                        
                    </div>
                    
                @endif
            </div>                
        </div>      
    @else
        <div class="col-lg-12">
            <div class="jumbotron">
                <h2><center>You have no Dashboard Widgets selected <br \> <h4>Please set your Widgets in <a href="{{ route('dashboard.settings') }}">Dashboard Settings</a></h4></center></h2>
            </div>
        </div>
    @endif 

    @php 
    $nodal_prices_resources_list = implode(',', $nodal_prices_resources);
    $dap_prices_resources_list = implode(',', $dap_prices_resources);
    $dap_schedules_resources_list = implode(',', $dap_schedules_resources);
    $dap_prices_and_schedules_resources_list = implode(',', $dap_prices_and_schedules_resources);
    $hap_prices_resources_list = implode(',', $hap_prices_resources);
    $hap_prices_and_schedules_resources_list = implode(',', $hap_prices_and_schedules_resources);
    $actual_load_resources_list = implode(',', $actual_load_resources);
    
    @endphp                        
@endsection

@section('scripts')
<link rel="stylesheet" href="{{ asset('css/weather-icons.css') }}" media="screen">

{{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
 --}}{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.0.3/socket.io.js"></script>  included in gulp file --}}
<script type="text/javascript">
    NODAL_PRICES_RESOURCES = '{{$nodal_prices_resources_list}}';
    DAP_PRICES_RESOURCES = '{{$dap_prices_resources_list}}';
    IS_WITH_DAP_PRICES = parseInt('{{$is_with_dap_prices_grid}}');
    IS_WITH_NODAL_PRICE_TICKER = parseInt('{{$is_with_nodal_price_ticker}}');
    DAP_SCHEDULES_RESOURCES = '{{$dap_schedules_resources_list}}';
    IS_WITH_DAP_SCHEDULES = parseInt('{{$is_with_dap_schedules_grid}}');
    DAP_PRICES_AND_SCHEDULES_RESOURCES = '{{$dap_prices_and_schedules_resources_list}}';
    IS_WITH_DAP_PRICES_AND_SCHEDULES = parseInt('{{$is_with_dap_prices_and_schedules_grid}}');
    HAP_PRICES_RESOURCES = '{{$hap_prices_resources_list}}';
    IS_WITH_HAP_PRICES = parseInt('{{$is_with_hap_prices_grid}}');
    HAP_PRICES_AND_SCHEDULES_RESOURCES = '{{$hap_prices_and_schedules_resources_list}}';
    IS_WITH_HAP_PRICES_AND_SCHEDULES = parseInt('{{$is_with_hap_prices_and_schedules_grid}}');
    IS_WITH_ACTUAL_LOAD = parseInt('{{$is_with_actual_load}}');
    ACTUAL_LOAD_RESOURCES = '{{$actual_load_resources_list}}';

    IS_WITH_NODAL_PRICES_GRID = parseInt('{{$is_with_nodal_prices_grid}}');
    
    RANDOM_COLORS = ['#df6290','#62a82a','#55769d','#990099','#5591a7','#139c96','#a836b8','#7b8ff1','#638db4'
        ,'#4e2b29','#ff6666','#9933ff','#006666','#ffcc00','#cc0066','#666699','#009933','#9933ff'
        ,'#cc6699','#990033','#003399','#ffbb33','#660066','#003300','#3366cc','#ff5050','#009900'
        ,'#333399','#cc3399','#33ccff','#ff9999','#00cc00','#33ccff','#cccc00','#333399','#ff9933'
        ,'#9999ff','#ff33cc']
    function getRandomColor(i) {

        if ( i <= RANDOM_COLORS.length ) {
            color = RANDOM_COLORS[i];
        }else {
            var letters = '0123456789ABCDEF';
            var color = '#';
            for (var i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
        }
      
      return color;
    }




    $(document).ready(function(){
        var ticker_data;
        // // var socket = io('http://127.0.0.1:3000');
        // var server_ip = "{{ $server_ip }}";
        // console.log('server_ip ' + server_ip)

        // var server = "{{ URL::to('/')}}";
        // server = server.replace(':8000','');
        // console.log('server ' + server)
        // // var server = '127.0.0.1'
        // var socket = io(server+':3000');
        $.extend({
            initNodalPriceTicker : function() {     
                var type    = $('input[name="ticker_options"]:checked').val();
                var zone    = $('#zone').val();
                $.ajax({
                    url : "{{ route('dashboard.get_ticker_data') }}",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },                
                    type : "POST",
                    error : function(error){
                        console.log('Error : '+error)
                    },
                    success : function(data){
                        ticker_data = data;
                        var selected = ticker_data[type][zone];
                        var html = '<marquee class="form-control input-sm" scrollamount="3" onmouseover="this.stop()" onmouseout="this.start()">';
                                                                                
                        $.each(selected,function(res,price){
                            html+=' <b>'+res+'</b> : '+'<span class="price">'+price+'</span>&nbsp;&nbsp;'
                        })
                        html += '</marquee>';
                        $('#marquee_container').html();
                        $('#marquee_container').html(html);                             
                    }
                })                                                    
            },
            getNodalPriceTicker : function() {     
                var type    = $('input[name="ticker_options"]:checked').val();
                var zone    = $('#zone').val();
                var selected = ticker_data[type][zone];
                var html = '<marquee class="form-control input-sm" scrollamount="3" onmouseover="this.stop()" onmouseout="this.start()">';                                                                            
                $.each(selected,function(res,price){
                    html+=' <b>'+res+'</b> : '+'<span class="price">'+price+'</span>&nbsp;&nbsp;'
                })
                html += '</marquee>';
                $('#marquee_container').html();
                $('#marquee_container').html(html);
            },
            getRtdSched : function (){
                $.ajax({
                    url : "{{ route('dashboard.get_rtd_sched') }}",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },                
                    type : "POST",
                    error : function(error){
                        console.log('Error : '+error)
                    },
                    success : function(data){
                        $.each(data,function(res,val){
                            val.mw = val.mw == null ? '&nbsp;' : val.mw;
                            $('#rtd_grid_'+res+' #rtd_grid_mw').html(val.mw)
                            $('#rtd_grid_'+res+' #rtd_grid_plus').html(val.plus)
                            $('#rtd_grid_'+res+' #rtd_grid_minus').html(val.minus)
                            $('#rtd_grid_'+res+' #rtd_grid_interval').html(val.interval)
                        })                               
                    }
                })
                setInterval( function() {
                    var minutes = new Date().getMinutes();
                    var min = ( minutes < 10 ? "0":"" ) + minutes
                }, 1000);
            } 
            ,getDAPPriceData : function(){
                var chart_data = [];
                $.ajax({
                    url : "{{ route('dashboard.get_dap_schedules_data') }}",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data : {'resource_ids' : DAP_PRICES_RESOURCES} ,               
                    type : "POST",
                    error : function(error){
                        console.log('Error : '+error)
                    },
                    success : function(data){
                        $('#dap_prices_table tbody').html('');
                        var html = '';
                        var r = 0;
                        $.each(data,function(resource_id,hourly_data){
                            html+='<tr>';
                            html+='<td>'+resource_id+'</td>';

                            
                            var resource_data = [];

                            for(var i=1;i<=24;i++){
                                var lmp = '';
                                if ( typeof hourly_data[i] != 'undefined' ) {
                                    lmp = hourly_data[i]['lmp'];
                                    resource_data.push(lmp);
                                }else {
                                    resource_data.push(null);
                                }

                                html+='<td>'+$.formatNumberToSpecificDecimalPlaces(lmp,2)+'</td>';
                            }
                            html+='</tr>';

                            var color_rgb = getRandomColor(r);
                            var resource_chart = {
                                label: resource_id,
                                data: resource_data,
                                borderColor: color_rgb,
                                backgroundColor : color_rgb,
                                borderWidth: 2,
                                fill : false
                            };
                            chart_data.push(resource_chart);
                            r++;
                        })
                        
                        if ( $.fn.DataTable.isDataTable( '#dap_prices_table' ) ) {
                            $('#dap_prices_table').DataTable().clear();
                            $('#dap_prices_table').DataTable().destroy();
                        }
                        
                        $('#dap_prices_table tbody').html(html);     
                        $('#dap_prices_table').DataTable({
                            scrollY:        "200px",
                            scrollX:        true,
                            scrollCollapse: true,
                            paging:         false,
                            searching:      false,
                            bSort:          false
                        });      



                        /// ### chart 
                        var ctx = document.getElementById("dap_prices_chart");
                        var labels = [];
                        for (var h=1;h<=24;h++){
                            labels.push('H'+h);
                        }
                        
                        if ( typeof DAP_PRICE_CHART !== 'undefined') {
                            DAP_PRICE_CHART.destroy();
                            // /$('#dap_prices_chart').removeAttr('width');
                            //$('#dap_prices_chart').removeAttr('height');
                            //$('#dap_prices_chart').css('width: 100%; height: 200px;');
                        }
                        

                         DAP_PRICE_CHART = Chart.Line(ctx, {
                            data: {
                                labels: labels,
                                datasets: chart_data
                            },
                            options: {
                                maintainAspectRatio: false,
                                responsive: true,
                                legend: {
                                    position: "bottom",
                                },
                                scales: {
                                    xAxes: [{
                                        display: true,
                                        scaleLabel: {
                                            display: true,
                                            labelString: 'Interval'
                                        }
                                    }],
                                    yAxes: [{
                                        display: true,
                                        scaleLabel: {
                                            display: true,
                                            labelString: 'LMP'
                                        },
                                        ticks: {
                                            callback: function(label, index, labels) {
                                                return $.formatNumberToSpecificDecimalPlaces(label,0);
                                            }
                                        }
                                    }]
                                },
                                title: {
                                    display: true,
                                    text: 'DAP Prices'
                                }
                            }
                        });              
                    }
                }) 
            }
            ,getDAPScheduleData : function(){
                var chart_data = [];
                $.ajax({
                    url : "{{ route('dashboard.get_dap_schedules_data') }}",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data : {'resource_ids' : DAP_SCHEDULES_RESOURCES} ,               
                    type : "POST",
                    error : function(error){
                        console.log('Error : '+error)
                    },
                    success : function(data){
                        $('#dap_schedules_table tbody').html('');
                        var html = '';
                        var r = 0;
                        $.each(data,function(resource_id,hourly_data){
                            html+='<tr>';
                            html+='<td>'+resource_id+'</td>';

                            
                            var resource_data = [];

                            for(var i=1;i<=24;i++){
                                var mw = '';
                                if ( typeof hourly_data[i] != 'undefined' ) {
                                    mw = hourly_data[i]['mw'];
                                    resource_data.push(mw);
                                }else {
                                    resource_data.push(null);
                                }

                                html+='<td>'+$.formatNumberToSpecificDecimalPlaces(mw,2)+'</td>';
                            }
                            html+='</tr>';

                            var color_rgb = getRandomColor(r);
                            var resource_chart = {
                                label: resource_id,
                                data: resource_data,
                                borderColor: color_rgb,
                                backgroundColor : color_rgb,
                                borderWidth: 2,
                                fill : false
                            };
                            chart_data.push(resource_chart);
                            r++;
                        })
                        
                        if ( $.fn.DataTable.isDataTable( '#dap_schedules_table' ) ) {
                            $('#dap_schedules_table').DataTable().clear();
                            $('#dap_schedules_table').DataTable().destroy();
                        }
                        $('#dap_schedules_table tbody').html(html);     
                        $('#dap_schedules_table').DataTable({
                            scrollY:        "200px",
                            scrollX:        true,
                            scrollCollapse: true,
                            paging:         false,
                            searching:      false,
                            bSort:          false
                        });      



                        /// ### chart 
                        var ctx = document.getElementById("dap_schedules_chart");
                        var labels = [];
                        for (var h=1;h<=24;h++){
                            labels.push('H'+h);
                        }
                        
                        if ( typeof DAP_SCHEDULE_CHART !== 'undefined') {
                            DAP_SCHEDULE_CHART.destroy();
                            $('#dap_schedules_chart').removeAttr('width');
                            $('#dap_schedules_chart').removeAttr('height');
                            $('#dap_schedules_chart').css('width: 100%; height: 300px;');
                        }
                        

                         DAP_SCHEDULE_CHART = Chart.Line(ctx, {
                            data: {
                                labels: labels,
                                datasets: chart_data
                            },
                            options: {
                                maintainAspectRatio: false,
                                responsive: true,
                                legend: {
                                    position: "bottom",
                                },
                                scales: {
                                    xAxes: [{
                                        display: true,
                                        scaleLabel: {
                                            display: true,
                                            labelString: 'Interval'
                                        }
                                    }],
                                    yAxes: [{
                                        display: true,
                                        scaleLabel: {
                                            display: true,
                                            labelString: 'Schedule'
                                        }
                                    }]
                                },
                                title: {
                                    display: true,
                                    text: 'DAP Schedules'
                                }
                            }
                        });              
                    }
                }) 
            } //

            ,getDAPPriceAndScheduleData : function(){
                var chart_data = [];
                $.ajax({
                    url : "{{ route('dashboard.get_dap_schedules_data') }}",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data : {'resource_ids' : DAP_PRICES_AND_SCHEDULES_RESOURCES} ,               
                    type : "POST",
                    error : function(error){
                        console.log('Error : '+error)
                    },
                    success : function(data){
                        $('#dap_prices_schedules_table tbody').html('');
                        var html = '';
                        var r = 0;
                        $.each(data,function(resource_id,hourly_data){

                            var sched_html = '', price_html = '';

                            sched_html+='<tr>';
                            sched_html+='<td>'+resource_id+'</td>';
                            sched_html+='<td>Schedule</td>';

                            price_html+='<tr>';
                            price_html+='<td>'+resource_id+'</td>';
                            price_html+='<td>Price</td>';
                            
                            var schedule_data = [];
                            var price_data = [];
                            for(var i=1;i<=24;i++){
                                var lmp = '';
                                var schedule = '';
                                if ( typeof hourly_data[i] != 'undefined' ) {
                                    lmp = hourly_data[i]['lmp'];
                                    schedule = hourly_data[i]['mw'];
                                    price_data.push(lmp);
                                    schedule_data.push(schedule);
                                }else {
                                    price_data.push(null);
                                    schedule_data.push(null);
                                }

                                sched_html+='<td>'+$.formatNumberToSpecificDecimalPlaces(schedule,2)+'</td>';
                                price_html+='<td>'+$.formatNumberToSpecificDecimalPlaces(lmp,2)+'</td>';
                            }
                            sched_html+='</tr>';
                            price_html+='</tr>';

                            html = html + sched_html + price_html;


                            var color_rgb = getRandomColor(r);
                            chart_data.push({
                                label: resource_id + '(Schedule)',
                                data: schedule_data,
                                borderColor: color_rgb,
                                backgroundColor : color_rgb,
                                borderWidth: 2,
                                fill : false,
                                yAxisID: "y-axis-1"
                            });
                            r++;
                            var color_rgb = getRandomColor(r);
                            chart_data.push({
                                label: resource_id + '(Price)',
                                data: price_data,
                                borderColor: color_rgb,
                                backgroundColor : color_rgb,
                                borderWidth: 2,
                                fill : false,
                                yAxisID: "y-axis-2"
                            });

                            r++;
                        })
                        
                        if ( $.fn.DataTable.isDataTable( '#dap_prices_schedules_table' ) ) {
                            $('#dap_prices_schedules_table').DataTable().clear();
                            $('#dap_prices_schedules_table').DataTable().destroy();
                        }
                        
                        $('#dap_prices_schedules_table tbody').html(html);     
                        $('#dap_prices_schedules_table').DataTable({
                            scrollY:        "200px",
                            scrollX:        true,
                            scrollCollapse: true,
                            paging:         false,
                            searching:      false,
                            bSort:          false
                        });      



                        /// ### chart 
                        var ctx = document.getElementById("dap_prices_and_schedules_chart");
                        var labels = [];
                        for (var h=1;h<=24;h++){
                            labels.push('H'+h);
                        }
                        
                        if ( typeof DAP_PRICE_AND_SCHEDULE_CHART !== 'undefined') {
                            DAP_PRICE_AND_SCHEDULE_CHART.destroy();
                            $('#dap_prices_and_schedules_chart').removeAttr('width');
                            $('#dap_prices_and_schedules_chart').removeAttr('height');
                            $('#dap_prices_and_schedules_chart').css('width: 100%; height: 400px;');
                        }
                        

                         DAP_PRICE_AND_SCHEDULE_CHART = Chart.Line(ctx, {
                            data: {
                                labels: labels,
                                datasets: chart_data
                            },
                            options: {
                                maintainAspectRatio: false,
                                responsive: true,
                                legend: {
                                    position: "bottom",
                                },
                                scales: {
                                    xAxes: [{
                                        display: true,
                                        scaleLabel: {
                                            display: true,
                                            labelString: 'Interval'
                                        }
                                    }],
                                    yAxes: [{
                                        type: "linear", 
                                        display: true,
                                        position: "left",
                                        id: "y-axis-1",
                                        scaleLabel: {
                                            display: true,
                                            labelString: "Schedule"
                                        }
                                    }, {
                                        type: "linear", 
                                        display: true,
                                        position: "right",
                                        id: "y-axis-2",
                                        gridLines: {
                                            drawOnChartArea: false
                                        },
                                        scaleLabel: {
                                            display: true,
                                            labelString: "Price"
                                        },
                                        ticks: {
                                            callback: function(label, index, labels) {
                                                return $.formatNumberToSpecificDecimalPlaces(label,0);
                                            }
                                        }
                                    }]
                                },
                                title: {
                                    display: true,
                                    text: 'DAP Prices and Schedules'
                                }
                            }
                        });              
                    }
                }) 
            } //

            ,getHAPPriceData : function(){
                var chart_data = [];
                $.ajax({
                    url : "{{ route('dashboard.get_hap_schedules_data') }}",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data : {'resource_ids' : HAP_PRICES_RESOURCES} ,               
                    type : "POST",
                    error : function(error){
                        console.log('Error : '+error)
                    },
                    success : function(ret){
                        $('#hap_prices_table tbody').html('');
                        $('#hap_prices_table thead').html('');
                        var html = '';
                        var r = 0;
                        var data = ret.data;
                        var hours = ret.hours;
                        var intra_intervals = [5,10,15,20,25,30,35,40,45,50,55,0];
                        // CREATE HEADER
                        var header = '';
                        header = '<tr>';
                        header += '<th>Resource</th>';
                        for(var h=0;h<hours.length;h++){
                            var loop_hour = hours[h];
                            var label_hour = $.strPad(loop_hour,2,'0');
                            for (var i=0;i<intra_intervals.length;i++) {
                                if ( intra_intervals[i] == 0 ) {
                                    if (loop_hour < 23 ) {
                                        var new_hour = loop_hour+1;
                                        label_hour = $.strPad(new_hour,2,'0');
                                    }else {
                                        var new_hour = 24;
                                        label_hour = $.strPad(new_hour,2,'0');
                                    }
                                    
                                }
                                header += '<th>'+label_hour+':'+$.strPad(intra_intervals[i],2,'0')+'</th>';
                            }
                        }
                        header += '</tr>';
                        
                        var resources = HAP_PRICES_RESOURCES.split(',');
                        for( var r=0;r<resources.length;r++){
                            var resource_id = resources[r];
                            html+='<tr>';
                            html+='<td>'+resource_id+'</td>';

                            for(var h=0;h<hours.length;h++){
                                var loop_hour = hours[h];
                                for (var i=0;i<intra_intervals.length;i++) {
                                    var intra_interval = intra_intervals[i];
                                    if ( intra_intervals[i] == 0 ) {
                                        if (loop_hour < 23 ) {
                                            loop_hour = loop_hour+1;
                                        }else {
                                            loop_hour = 24;
                                        }
                                    }
                                    var lmp = '';
                                    if (typeof data[resource_id] != 'undefined') {
                                        var hourly_data = data[resource_id];
                                        if ( typeof hourly_data[loop_hour] != 'undefined') {
                                            if ( typeof hourly_data[loop_hour][intra_interval] != 'undefined') {
                                                lmp = $.formatNumberToSpecificDecimalPlaces(hourly_data[loop_hour][intra_interval]['lmp'],2);
                                            }
                                        }
                                    }

                                    
                                    html+='<td>'+lmp+'</td>';;
                                }
                            }
                            html+='</tr>';

                        }// loop resource
                        
                        
                        $('#hap_prices_table thead').html(header);

                        if ( $.fn.DataTable.isDataTable( '#hap_prices_table' ) ) {
                            $('#hap_prices_table').DataTable().clear();
                            $('#hap_prices_table').DataTable().destroy();
                        }
                        

                        $('#hap_prices_table tbody').html(html);     
                        $('#hap_prices_table').DataTable({
                            scrollY:        "350px",
                            scrollX:        true,
                            scrollCollapse: true,
                            paging:         false,
                            searching:      false,
                            bSort:          false,
                            autoWidth :      false

                        });      


                        // quick fix , fix misaligmment issue of header to data
                        setTimeout(function(){
                            $('#hap_prices_table').DataTable().draw();
                        }, 1500);

      
                    }
                }) 
            } //


            ,getHAPPriceAndSchedulesData : function(){
                var chart_data = [];
                $.ajax({
                    url : "{{ route('dashboard.get_hap_schedules_data') }}",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data : {'resource_ids' : HAP_PRICES_AND_SCHEDULES_RESOURCES} ,               
                    type : "POST",
                    error : function(error){
                        console.log('Error : '+error)
                    },
                    success : function(ret){
                        $('#hap_prices_and_schedules_table tbody').html('');
                        $('#hap_prices_and_schedules_table thead').html('');
                        var html = '';
                        var r = 0;
                        var data = ret.data;
                        var hours = ret.hours;
                        var intra_intervals = [5,10,15,20,25,30,35,40,45,50,55,0];
                        // CREATE HEADER
                        var header = '';
                        header = '<tr>';
                        header += '<th>Resource</th>';
                        header += '<th>Type</th>';
                        for(var h=0;h<hours.length;h++){
                            var loop_hour = hours[h];
                            var label_hour = $.strPad(loop_hour,2,'0');
                            for (var i=0;i<intra_intervals.length;i++) {
                                if ( intra_intervals[i] == 0 ) {
                                    if (loop_hour < 23 ) {
                                        var new_hour = loop_hour+1;
                                        label_hour = $.strPad(new_hour,2,'0');
                                    }else {
                                        var new_hour = 24;
                                        label_hour = $.strPad(new_hour,2,'0');
                                    }
                                    
                                }
                                header += '<th>'+label_hour+':'+$.strPad(intra_intervals[i],2,'0')+'</th>';
                            }
                        }
                        header += '</tr>';
                        
                        console.log(ret)

                        var resources = HAP_PRICES_AND_SCHEDULES_RESOURCES.split(',');
                        for( var r=0;r<resources.length;r++){
                            var resource_id = resources[r];
                            var price_html = '';
                            var schedule_html = '';

                            price_html+='<tr>';
                            price_html+='<td>'+resource_id+'</td>';
                            price_html+='<td>PRICE</td>';

                            schedule_html+='<tr>';
                            schedule_html+='<td>'+resource_id+'</td>';
                            schedule_html+='<td>SCHEDULE</td>';

                            for(var h=0;h<hours.length;h++){
                                var loop_hour = hours[h];
                                for (var i=0;i<intra_intervals.length;i++) {
                                    var intra_interval = intra_intervals[i];
                                    if ( intra_intervals[i] == 0 ) {
                                        if (loop_hour < 23 ) {
                                            loop_hour = loop_hour+1;
                                        }else {
                                            loop_hour = 24;
                                        }
                                    }
                                   var lmp = '';
                                   var mw = '';

                                    if (typeof data[resource_id] != 'undefined') {
                                        var hourly_data = data[resource_id];
                                        if ( typeof hourly_data[loop_hour] != 'undefined') {
                                            if ( typeof hourly_data[loop_hour][intra_interval] != 'undefined') {
                                                lmp = $.formatNumberToSpecificDecimalPlaces(hourly_data[loop_hour][intra_interval]['lmp'],2);
                                                mw = $.formatNumberToSpecificDecimalPlaces(hourly_data[loop_hour][intra_interval]['mw'],2);
                                            }
                                        }
                                    }                                    
                                    price_html+='<td>'+lmp+'</td>';
                                    schedule_html+='<td>'+mw+'</td>';
                                }
                            }
                            price_html+='</tr>';
                            schedule_html+='</tr>';

                            html = html + price_html + schedule_html;

                        }// loop resource
                                                
                        $('#hap_prices_and_schedules_table thead').html(header);

                        if ( $.fn.DataTable.isDataTable( '#hap_prices_and_schedules_table' ) ) {
                            $('#hap_prices_and_schedules_table').DataTable().clear();
                            $('#hap_prices_and_schedules_table').DataTable().destroy();
                        }

                        

                        $('#hap_prices_and_schedules_table tbody').html(html);     
                        $('#hap_prices_and_schedules_table').DataTable({
                            scrollY:        "350px",
                            scrollX:        true,
                            scrollCollapse: true,
                            paging:         false,
                            searching:      false,
                            bSort:          false,
                            autoWidth :      false
                        });      

                        setTimeout(function(){
                            $('#hap_prices_and_schedules_table').DataTable().draw();
                        }, 2000);
      
                    }
                }) 
            } //

            ,getActualLoad : function (){
                $.ajax({
                    url : "{{ route('dashboard.get_actual_load_data') }}",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },                
                    type : "POST",
                    error : function(error){
                        console.log('Error : '+error)
                    },
                    success : function(data){
                        // var resources = data.resource_id_list;
                        var resources = ACTUAL_LOAD_RESOURCES.split(',');
                        var hour = data.hour;
                        var interval = data.interval;
                        var list = data.data;

                        for (var r=0;r<resources.length;r++) {
                            var resource_id = resources[r];
                            var actual_load = 'N/A';
                            if ( typeof list[resource_id] != 'undefined' ) {
                                actual_load = $.formatNumberToSpecificDecimalPlaces(list[resource_id]['actual_load'],1);
                            }
                            $('#actual_load_'+resource_id+ ' h6[name=actual_load_interval]').html(interval);
                            $('#actual_load_'+resource_id+ ' div[name=actual_load_val]').html(actual_load);

                        }
                        console.log(data)                         
                    }
                })
                setInterval( function() {
                    var minutes = new Date().getMinutes();
                    var min = ( minutes < 10 ? "0":"" ) + minutes
                }, 1000);
            }
        })

        // RTD GRID
        if($('div[id*="rtd_grid"')){
            $.getRtdSched();

            // rtd grid data
            socket.on("app.dashboard.rtd_grid:App\\Events\\RtdGrid", function(message){
                console.log(message);
                var data = message.data;
                $.each(data,function(res,val){
                    val.mw = val.mw == null ? '&nbsp;' : val.mw;
                    $('#rtd_grid_'+res+' #rtd_grid_mw').html(val.mw)
                    $('#rtd_grid_'+res+' #rtd_grid_plus').html(val.plus)
                    $('#rtd_grid_'+res+' #rtd_grid_minus').html(val.minus)
                    $('#rtd_grid_'+res+' #rtd_grid_interval').html(val.interval)
                })
            });
            
        }


        // ticker data
        if(IS_WITH_NODAL_PRICE_TICKER == 1){
            $.initNodalPriceTicker();
            
            socket.on("app.dashboard.ticker_data:App\\Events\\TickerData", function(message){
                var type    = $('input[name="ticker_options"]:checked').val();
                var zone    = $('#zone').val();
                var data = message.data;                    
                ticker_data = data;
                var selected = ticker_data[type][zone];
                var html = '<marquee class="form-control input-sm" scrollamount="3" onmouseover="this.stop()" onmouseout="this.start()">';
                                                                        
                $.each(selected,function(res,price){
                    html+=' <b>'+res+'</b> : '+'<span class="price">'+price+'</span>&nbsp;&nbsp;'
                })
                html += '</marquee>';
                $('#marquee_container').html();
                $('#marquee_container').html(html);
            });  
        }

        if (IS_WITH_DAP_PRICES == 1) {
            $.getDAPPriceData();
        }

        if (IS_WITH_DAP_SCHEDULES == 1) {
            $.getDAPScheduleData();
        }

        if (IS_WITH_DAP_PRICES_AND_SCHEDULES == 1) {
            $.getDAPPriceAndScheduleData();
        }

        if (IS_WITH_HAP_PRICES == 1) {
            $.getHAPPriceData();

            
        }

        if (IS_WITH_HAP_PRICES_AND_SCHEDULES == 1) {
            $.getHAPPriceAndSchedulesData();

            
        }   

        // ACTUAL LOAD
        if (IS_WITH_ACTUAL_LOAD == 1) {
            $.getActualLoad();

            socket.on("app.dashboard.actual_load_data:App\\Events\\DashboardActualLoadData", function(message){
                console.log("socket actual load");
                console.log(message)
                var list = message.data.data;
                var interval = message.data.interval;
                var resources = ACTUAL_LOAD_RESOURCES.split(',');
                for (var r=0;r<resources.length;r++) {
                    var resource_id = resources[r];
                    var actual_load = 'N/A';

                    if ( typeof list[resource_id] != 'undefined' ) {
                        actual_load = $.formatNumberToSpecificDecimalPlaces(list[resource_id]['actual_load'],1);
                    }
                    $('#actual_load_'+resource_id+ ' h6[name=actual_load_interval]').html(interval);
                    $('#actual_load_'+resource_id+ ' div[name=actual_load_val]').html(actual_load);

                }

            });
        }

        // nodal prices grid
        if ( IS_WITH_NODAL_PRICES_GRID == 1 ) {
            socket.on("app.dashboard.nodal_price_grid:App\\Events\\NodalPriceGrid", function(message){
                
                var data = message.data;
                var intrainterval_data = data.intrainterval;
                var intrainterval = intrainterval_data.date+' '+intrainterval_data.intra_interval;
                var new_data = data.data;

                var hour = intrainterval_data.hour;
                if (intra_intervals_list.indexOf(intrainterval) === -1) {
                    intra_intervals_list.pop();
                    intra_intervals_list.unshift(intrainterval);
                }
                // console.log(data);
                // update nodal price grid data 
                var resource_list = NODAL_PRICES_RESOURCES.split(',');

                $.each(new_data,function(resource,val){
                    if( resource_list.indexOf(resource) >= 0 ){
                        var np = val[intrainterval];
                        if ( typeof nodal_prices_grid[resource] != 'undefined' ) {
                            nodal_prices_grid[resource][intrainterval] = np;
                        }
                    }
                    
                });
                console.log(nodal_prices_grid);
                // update grid
                var html = '<tr>';
                html+= '<td style="text-align: center;" id="current_hour">Hour '+hour+'</td>';

                for (var i=0;i<intra_intervals_list.length;i++){
                    var ikey = intra_intervals_list[i];
                    var x = new Date(ikey);
                    var intra = moment(x).format('HHmm');
                    html+= '<td style="text-align: center;">'+intra+'H</td>';
                }
                html+='</tr>';

                $.each(nodal_prices_grid,function(resource,val){
                    html += '<tr>';
                    html += '<td>'+resource+'</td>';

                    for (var i=0;i<intra_intervals_list.length;i++){
                        var ikey = intra_intervals_list[i];
                        var np = '';

                        if ( typeof val[ikey] != 'undefined' ) {
                            np = val[ikey];
                        }

                        html+= '<td style="text-align: right;">'+np+'</td>';
                    }

                    html += '</tr>';

                });

                $('#nodal_prices_table').html(html);
                
            });
        }

        socket.on('app.dashboard.dap_prices_schedules:App\\Events\\DapPricesAndSchedules',function(message){
            var chart_data = [];
            var data = message.data;
            $('#dap_schedules_table tbody').html('');
            var html = '';
            var r = 0;
            $.each(data,function(resource_id,hourly_data){
                if($.inArray(resource_id,DAP_SCHEDULES_RESOURCES.split(','))){
                    html+='<tr>';
                    html+='<td>'+resource_id+'</td>';

                    
                    var resource_data = [];

                    for(var i=1;i<=24;i++){
                        var mw = '';
                        if ( typeof hourly_data[i] != 'undefined' ) {
                            mw = hourly_data[i]['mw'];
                            resource_data.push(mw);
                        }else {
                            resource_data.push(null);
                        }

                        html+='<td>'+$.formatNumberToSpecificDecimalPlaces(mw,2)+'</td>';
                    }
                    html+='</tr>';

                    var color_rgb = getRandomColor(r);
                    var resource_chart = {
                        label: resource_id,
                        data: resource_data,
                        borderColor: color_rgb,
                        backgroundColor : color_rgb,
                        borderWidth: 2,
                        fill : false
                    };
                    chart_data.push(resource_chart);
                    r++;
                } 
            })
            
            if ( $.fn.DataTable.isDataTable( '#dap_schedules_table' ) ) {
                $('#dap_schedules_table').DataTable().clear();
                $('#dap_schedules_table').DataTable().destroy();
            }
            
            $('#dap_schedules_table tbody').html(html);     
            $('#dap_schedules_table').DataTable({
                scrollY:        "200px",
                scrollX:        true,
                scrollCollapse: true,
                paging:         false,
                searching:      false,
                bSort:          false
            });      



            /// ### chart 
            var ctx = document.getElementById("dap_schedules_chart");
            var labels = [];
            for (var h=1;h<=24;h++){
                labels.push('H'+h);
            }
            
            if ( typeof DAP_SCHEDULE_CHART !== 'undefined') {
                DAP_SCHEDULE_CHART.destroy();
                $('#dap_schedules_chart').removeAttr('width');
                $('#dap_schedules_chart').removeAttr('height');
                $('#dap_schedules_chart').css('width: 100%; height: 300px;');
            }
            

             DAP_SCHEDULE_CHART = Chart.Line(ctx, {
                data: {
                    labels: labels,
                    datasets: chart_data
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: {
                        position: "bottom",
                    },
                    scales: {
                        xAxes: [{
                            display: true,
                            scaleLabel: {
                                display: true,
                                labelString: 'Interval'
                            }
                        }],
                        yAxes: [{
                            display: true,
                            scaleLabel: {
                                display: true,
                                labelString: 'Schedule'
                            }
                        }]
                    },
                    title: {
                        display: true,
                        text: 'DAP Schedules'
                    }
                }
            });
            var chart_data = [];
            
            $('#dap_prices_schedules_table tbody').html('');
            var html = '';
            var r = 0;
            $.each(data,function(resource_id,hourly_data){
                if($.inArray(resource_id,DAP_PRICES_AND_SCHEDULES_RESOURCES.split(','))){
                    var sched_html = '', price_html = '';

                    sched_html+='<tr>';
                    sched_html+='<td>'+resource_id+'</td>';
                    sched_html+='<td>Schedule</td>';

                    price_html+='<tr>';
                    price_html+='<td>'+resource_id+'</td>';
                    price_html+='<td>Price</td>';
                    
                    var schedule_data = [];
                    var price_data = [];
                    for(var i=1;i<=24;i++){
                        var lmp = '';
                        var schedule = '';
                        if ( typeof hourly_data[i] != 'undefined' ) {
                            lmp = hourly_data[i]['lmp'];
                            schedule = hourly_data[i]['mw'];
                            price_data.push(lmp);
                            schedule_data.push(schedule);
                        }else {
                            price_data.push(null);
                            schedule_data.push(null);
                        }

                        sched_html+='<td>'+$.formatNumberToSpecificDecimalPlaces(schedule,2)+'</td>';
                        price_html+='<td>'+$.formatNumberToSpecificDecimalPlaces(lmp,2)+'</td>';
                    }
                    sched_html+='</tr>';
                    price_html+='</tr>';

                    html = html + sched_html + price_html;


                    var color_rgb = getRandomColor(r);
                    chart_data.push({
                        label: resource_id + '(Schedule)',
                        data: schedule_data,
                        borderColor: color_rgb,
                        backgroundColor : color_rgb,
                        borderWidth: 2,
                        fill : false,
                        yAxisID: "y-axis-1"
                    });

                    r++;
                    var color_rgb = getRandomColor(r);
                    chart_data.push({
                        label: resource_id + '(Price)',
                        data: price_data,
                        borderColor: color_rgb,
                        backgroundColor : color_rgb,
                        borderWidth: 2,
                        fill : false,
                        yAxisID: "y-axis-2"
                    });

                    r++;
                }
            })
            
            if ( $.fn.DataTable.isDataTable( '#dap_prices_schedules_table' ) ) {
                $('#dap_prices_schedules_table').DataTable().clear();
                $('#dap_prices_schedules_table').DataTable().destroy();
            }
            
            $('#dap_prices_schedules_table tbody').html(html);     
            $('#dap_prices_schedules_table').DataTable({
                scrollY:        "200px",
                scrollX:        true,
                scrollCollapse: true,
                paging:         false,
                searching:      false,
                bSort:          false
            });      



            /// ### chart 
            var ctx = document.getElementById("dap_prices_and_schedules_chart");
            var labels = [];
            for (var h=1;h<=24;h++){
                labels.push('H'+h);
            }
            
            if ( typeof DAP_PRICE_AND_SCHEDULE_CHART !== 'undefined') {
                DAP_PRICE_AND_SCHEDULE_CHART.destroy();
                $('#dap_prices_and_schedules_chart').removeAttr('width');
                $('#dap_prices_and_schedules_chart').removeAttr('height');
                $('#dap_prices_and_schedules_chart').css('width: 100%; height: 300px;');
            }
            

             DAP_PRICE_AND_SCHEDULE_CHART = Chart.Line(ctx, {
                data: {
                    labels: labels,
                    datasets: chart_data
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: {
                        position: "bottom",
                    },
                    scales: {
                        xAxes: [{
                            display: true,
                            scaleLabel: {
                                display: true,
                                labelString: 'Interval'
                            }
                        }],
                        yAxes: [{
                            type: "linear", 
                            display: true,
                            position: "left",
                            id: "y-axis-1",
                            scaleLabel: {
                                display: true,
                                labelString: "Schedule"
                            }
                        }, {
                            type: "linear", 
                            display: true,
                            position: "right",
                            id: "y-axis-2",
                            gridLines: {
                                drawOnChartArea: false
                            },
                            scaleLabel: {
                                display: true,
                                labelString: "Price"
                            },
                            ticks: {
                                callback: function(label, index, labels) {
                                    return $.formatNumberToSpecificDecimalPlaces(label,0);
                                }
                            }
                        }]
                    },
                    title: {
                        display: true,
                        text: 'DAP Prices and Schedules'
                    }
                }
            });             

            var chart_data = [];
            $('#dap_prices_table tbody').html('');
            var html = '';
            var r = 0;
            $.each(data,function(resource_id,hourly_data){
                if($.inArray(resource_id,DAP_PRICES_RESOURCES.split(','))){
                    html+='<tr>';
                    html+='<td>'+resource_id+'</td>';
                    
                    var resource_data = [];

                    for(var i=1;i<=24;i++){
                        var lmp = '';
                        if ( typeof hourly_data[i] != 'undefined' ) {
                            lmp = hourly_data[i]['lmp'];
                            resource_data.push(lmp);
                        }else {
                            resource_data.push(null);
                        }

                        html+='<td>'+$.formatNumberToSpecificDecimalPlaces(lmp,2)+'</td>';
                    }
                    html+='</tr>';

                    var color_rgb = getRandomColor(r);
                    var resource_chart = {
                        label: resource_id,
                        data: resource_data,
                        borderColor: color_rgb,
                        backgroundColor : color_rgb,
                        borderWidth: 2,
                        fill : false
                    };
                    chart_data.push(resource_chart);
                    r++;
                }
            })

            if ( $.fn.DataTable.isDataTable( '#dap_prices_table' ) ) {
                $('#dap_prices_table').DataTable().clear();
                $('#dap_prices_table').DataTable().destroy();
            }
            
            $('#dap_prices_table tbody').html(html);     
            $('#dap_prices_table').DataTable({
                scrollY:        "200px",
                scrollX:        true,
                scrollCollapse: true,
                paging:         false,
                searching:      false,
                bSort:          false
            });      



            /// ### chart 
            var ctx = document.getElementById("dap_prices_chart");
            var labels = [];
            for (var h=1;h<=24;h++){
                labels.push('H'+h);
            }
            
            if ( typeof DAP_PRICE_CHART !== 'undefined') {
                DAP_PRICE_CHART.destroy();
               $('#dap_prices_chart').removeAttr('width');
               $('#dap_prices_chart').removeAttr('height');
                $('#dap_prices_chart').css('width: 100%; height: 300px;');
            }
            

             DAP_PRICE_CHART = Chart.Line(ctx, {
                data: {
                    labels: labels,
                    datasets: chart_data
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: {
                        position: "bottom",
                    },
                    scales: {
                        xAxes: [{
                            display: true,
                            scaleLabel: {
                                display: true,
                                labelString: 'Interval'
                            }
                        }],
                        yAxes: [{
                            display: true,
                            scaleLabel: {
                                display: true,
                                labelString: 'LMP'
                            },
                            ticks: {
                                callback: function(label, index, labels) {
                                    return $.formatNumberToSpecificDecimalPlaces(label,0);
                                }
                            }
                        }]
                    },
                    title: {
                        display: true,
                        text: 'DAP Prices'
                    }
                }
            });          
        })
        
        socket.on('app.dashboard.hap_prices_schedules:App\\Events\\HapPricesAndSchedules',function(message){
            var chart_data = [];
            var msg = message.data;
            console.log(message);
            
            $('#hap_prices_table tbody').html('');
            $('#hap_prices_table thead').html('');
            var html = '';
            var r = 0;
            var data = msg.data;
            var hours = msg.hours;
            console.log(hours);
            var intra_intervals = [5,10,15,20,25,30,35,40,45,50,55,0];
            // CREATE HEADER
            var header = '';
            header = '<tr>';
            header += '<th>Resource</th>';
            for(var h=0;h<hours.length;h++){
                var loop_hour = hours[h];
                var label_hour = $.strPad(loop_hour,2,'0');
                for (var i=0;i<intra_intervals.length;i++) {
                    if ( intra_intervals[i] == 0 ) {
                        if (loop_hour < 23 ) {
                            var new_hour = loop_hour+1;
                            label_hour = $.strPad(new_hour,2,'0');
                        }else {
                            var new_hour = 24;
                            label_hour = $.strPad(new_hour,2,'0');
                        }
                        
                    }
                    header += '<th>'+label_hour+':'+$.strPad(intra_intervals[i],2,'0')+'</th>';
                }
            }
            header += '</tr>';
            

            var resources = HAP_PRICES_RESOURCES.split(',');
            for( var r=0;r<resources.length;r++){
                var resource_id = resources[r];
                html+='<tr>';
                html+='<td>'+resource_id+'</td>';

                for(var h=0;h<hours.length;h++){
                    var loop_hour = hours[h];
                    for (var i=0;i<intra_intervals.length;i++) {
                        var intra_interval = intra_intervals[i];
                        if ( intra_intervals[i] == 0 ) {
                            if (loop_hour < 23 ) {
                                loop_hour = loop_hour+1;
                            }else {
                                loop_hour = 24;
                            }
                        }
                        var lmp = '';
                        if (typeof data[resource_id] != 'undefined') {
                            var hourly_data = data[resource_id];
                            if ( typeof hourly_data[loop_hour] != 'undefined') {
                                if ( typeof hourly_data[loop_hour][intra_interval] != 'undefined') {
                                    lmp = $.formatNumberToSpecificDecimalPlaces(hourly_data[loop_hour][intra_interval]['lmp'],2);
                                }
                            }
                        }

                        
                        html+='<td>'+lmp+'</td>';;
                    }
                }
                html+='</tr>';

            }// loop resource
            
            $('#hap_prices_table thead').html(header);

            if ( $.fn.DataTable.isDataTable( '#hap_prices_table' ) ) {
                $('#hap_prices_table').DataTable().clear();
                $('#hap_prices_table').DataTable().destroy();
            }

            $('#hap_prices_table tbody').html(html);     
            $('#hap_prices_table').DataTable({
                scrollY:        "350px",
                scrollX:        true,
                scrollCollapse: true,
                paging:         false,
                searching:      false,
                bSort:          false,
                autoWidth :      false

            }); 
            var chart_data = [];
            $('#hap_prices_and_schedules_table tbody').html('');
            $('#hap_prices_and_schedules_table thead').html('');
            var html = '';
            // var r = 0;
            // var data = msg.data;
            // var hours = ret.hours;
            // var intra_intervals = [5,10,15,20,25,30,35,40,45,50,55,0];

            // CREATE HEADER
            var header = '';
            header = '<tr>';
            header += '<th>Resource</th>';
            header += '<th>Type</th>';
            for(var h=0;h<hours.length;h++){
                var loop_hour = hours[h];
                var label_hour = $.strPad(loop_hour,2,'0');
                for (var i=0;i<intra_intervals.length;i++) {
                    if ( intra_intervals[i] == 0 ) {
                        if (loop_hour < 23 ) {
                            var new_hour = loop_hour+1;
                            label_hour = $.strPad(new_hour,2,'0');
                        }else {
                            var new_hour = 24;
                            label_hour = $.strPad(new_hour,2,'0');
                        }
                        
                    }
                    header += '<th>'+label_hour+':'+$.strPad(intra_intervals[i],2,'0')+'</th>';
                }
            }
            header += '</tr>';
            console.log(hours);
            console.log(data);
           var resources = HAP_PRICES_AND_SCHEDULES_RESOURCES.split(',');
            for( var r=0;r<resources.length;r++){
                var resource_id = resources[r];
                var price_html = '';
                var schedule_html = '';

                price_html+='<tr>';
                price_html+='<td>'+resource_id+'</td>';
                price_html+='<td>PRICE</td>';

                schedule_html+='<tr>';
                schedule_html+='<td>'+resource_id+'</td>';
                schedule_html+='<td>SCHEDULE</td>';

                for(var h=0;h<hours.length;h++){
                    var loop_hour = hours[h];
                    for (var i=0;i<intra_intervals.length;i++) {
                        var intra_interval = intra_intervals[i];
                        if ( intra_intervals[i] == 0 ) {
                            if (loop_hour < 23 ) {
                                loop_hour = loop_hour+1;
                            }else {
                                loop_hour = 24;
                            }
                        }
                       var lmp = '';
                       var mw = '';

                        if (typeof data[resource_id] != 'undefined') {
                            var hourly_data = data[resource_id];
                            if ( typeof hourly_data[loop_hour] != 'undefined') {
                                if ( typeof hourly_data[loop_hour][intra_interval] != 'undefined') {
                                    lmp = $.formatNumberToSpecificDecimalPlaces(hourly_data[loop_hour][intra_interval]['lmp'],2);
                                    mw = $.formatNumberToSpecificDecimalPlaces(hourly_data[loop_hour][intra_interval]['mw'],2);
                                }
                            }
                        }                                    
                        price_html+='<td>'+lmp+'</td>';
                        schedule_html+='<td>'+mw+'</td>';
                    }
                }
                price_html+='</tr>';
                schedule_html+='</tr>';

                html = html + price_html + schedule_html;

            }// loop resource
            
            $('#hap_prices_and_schedules_table thead').html(header);

            if ( $.fn.DataTable.isDataTable( '#hap_prices_and_schedules_table' ) ) {
                $('#hap_prices_and_schedules_table').DataTable().clear();
                $('#hap_prices_and_schedules_table').DataTable().destroy();
            }

            

            $('#hap_prices_and_schedules_table tbody').html(html);     
            $('#hap_prices_and_schedules_table').DataTable({
                scrollY:        "300px",
                scrollX:        true,
                scrollCollapse: true,
                paging:         false,
                searching:      false,
                bSort:          false,
                autoWidth :      false

            }); 
        });
        
        



        

        setTimeout(function(){ 
            $('.dashboard-preloader').slideUp(400);
            $('.main-content').fadeIn(1000); 
        }, 1000);

        $('#ticker_btn').click(function(){
            setTimeout(function(){
                $.getNodalPriceTicker();
            },250)            
        })
        $('#zone').change(function(){
            $.getNodalPriceTicker();
        })      
        


        $("a[name=panel_fullscreen]").click(function (e) {
            e.preventDefault();
            
            var $this = $(this);
        
            if ($this.children('i').hasClass('glyphicon-resize-full'))
            {
                $this.children('i').removeClass('glyphicon-resize-full');
                $this.children('i').addClass('glyphicon-resize-small');
            }
            else if ($this.children('i').hasClass('glyphicon-resize-small'))
            {
                $this.children('i').removeClass('glyphicon-resize-small');
                $this.children('i').addClass('glyphicon-resize-full');
            }

            // redraw table
            if ( typeof $this.attr('grid_redraw') != 'undefined' ) {
                var tbl_name = '#' + $this.attr('grid_redraw');

                setTimeout(function(){
                    $(tbl_name).DataTable().draw(false);
                }, 100);
                
            }

            $(this).closest('.panel').toggleClass('panel-fullscreen');
        });
        
    })
</script>
<script>    
    var nodal_prices_grid = {!! json_encode($nodal_prices_data) !!};
    var intra_intervals_list = {!! json_encode($intra_intervals) !!};
</script>
@endsection

