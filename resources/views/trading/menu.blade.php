<h5><strong>Trading</strong></h5>
<hr>
<div class="list-group">

	@if(Auth::check())
	    <a href="{{ route('scheduled_offer') }}" class="list-group-item {!! Request::is('bids_and_offers/*') ? 'active' : '' !!}">Bids and Offer</a>
	    <a href="{{ route('availability_report.list') }}" class="list-group-item">Plant Availability</a>
	    <a href="{{ route('realtime_plant_monitoring.tradingList') }}" class="list-group-item">Realtime Plant Monitoring</a>
	    <a href="{{ route('meter_data.mq_load.index') }}" class="list-group-item">Meter Data</a>
	    <a href="{{ route('trading_shift_report.list') }}" class="list-group-item">Shift Reports</a>
	    <a href="{{ route('aspa_nomination.input') }}"  class="list-group-item {!! Request::is('aspa_nomination/*') ? 'active' : '' !!}">ASPA Nomination</a>
	@endif
    
    
    
</div>
