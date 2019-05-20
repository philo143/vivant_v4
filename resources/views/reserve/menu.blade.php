<h5><strong>Reserve</strong></h5>
<hr>
<div class="list-group">
    <a href="{{ route('reserve_capability.create') }}" class="list-group-item {!! Request::is('reserve/capability/create') ? 'active' : '' !!}">Reserve Capability Submission</a>

    <a href="{{ route('reserve_capability.list') }}" class="list-group-item {!! Request::is('reserve/capability/list') ? 'active' : '' !!}">Reserve Capability History</a>
    
    <a href="{{ route('reserve_nomination.create') }}" class="list-group-item {!! Request::is('reserve/nomination/create') ? 'active' : '' !!}">Reserve Nomination Submission</a>


    <a href="{{ route('reserve_nomination.list') }}" class="list-group-item {!! Request::is('reserve/nomination/list') ? 'active' : '' !!}">Reserve Nomination History</a>

    <a href="{{ route('reserve_schedule.create') }}" class="list-group-item {!! Request::is('reserve/schedule/create') ? 'active' : '' !!}">Reserve Schedules</a>


    <a href="{{ route('reserve_schedule.list') }}" class="list-group-item {!! Request::is('reserve/schedule/list') ? 'active' : '' !!}">Reserve Schedule History</a>

</div>
