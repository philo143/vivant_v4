<h5><strong>MMS Data</strong></h5>
<hr>
<div class="list-group">
    <a href="{{ route('dap_schedules.list') }}" class="list-group-item {!! Request::is('mms_data/dap_schedules') ? 'active' : '' !!}">DAP Schedules</a>
    
    <a href="{{ route('hap_prices_and_sched.list') }}" class="list-group-item {!! Request::is('mms_data/hap_prices_and_sched/*') ? 'active' : '' !!}">HAP Schedules</a>


    <a href="{{ route('rtd_schedules.list') }}" class="list-group-item {!! Request::is('mms_data/rtd_schedules/*') ? 'active' : '' !!}">RTD Schedules and Prices</a>

    <a href="{{ route('system_messages.list') }}" class="list-group-item {!! Request::is('mms_data/system_messages/*') ? 'active' : '' !!}">System Messages</a>

    {{--  <a href="{{ route('lmp.list') }}" class="list-group-item {!! Request::is('mms_data/lmp/*') ? 'active' : '' !!}">Locational Marginal Prices</a> --}}

     <a href="{{ route('reserve_schedules.list') }}" class="list-group-item {!! Request::is('mms_data/reserve_schedules/*') ? 'active' : '' !!}">Reserve Schedules</a>

     <a href="{{ route('reserve_prices.list') }}" class="list-group-item {!! Request::is('mms_data/reserve_prices/*') ? 'active' : '' !!}">Reserve Prices</a>
    {{-- <a href="{{ route('plant_capability.day_ahead_list') }}" class="list-group-item {!! Request::is('plant_capability/dayahead/*') ? 'active' : '' !!}">Day Ahead Plant Capability</a> --}}
</div>
