<h5><strong>Plant Capability</strong></h5>
<hr>
<div class="list-group">
    <a href="{{ route('realtime_plant_capability.list') }}" class="list-group-item {!! Request::is('plant_capability/realtime/*') ? 'active' : '' !!}">Realtime Plant Capability</a>
    <a href="{{ route('plant_capability.day_ahead_list') }}" class="list-group-item {!! Request::is('plant_capability/dayahead/*') ? 'active' : '' !!}">Day Ahead Plant Capability</a>
     <a href="{{ route('plant_capability.week_ahead_list') }}" class="list-group-item {!! Request::is('plant_capability/weekahead/*') ? 'active' : '' !!}">Week Ahead Plant Capability</a>
     <a href="{{ route('plant_capability.templates') }}" class="list-group-item {!! Request::is('plant_capability/templates') ? 'active' : '' !!}">Plant Capability Templates</a>
</div>
