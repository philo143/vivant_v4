<h5><strong>Manage Participant</strong></h5>
<hr>
<div class="list-group">
    <a href="{{ route('participants.list') }}" class="list-group-item {!! Request::is('admin/participant/*') ? 'active' : '' !!}">Participants</a>
    <a href="{{ route('plants.list') }}" class="list-group-item {!! Request::is('admin/plant/*') ? 'active' : '' !!}">Plants</a>
    <a href="{{ route('resources.list') }}" class="list-group-item {!! Request::is('admin/resources/*') ? 'active' : '' !!}">Resources</a>
    <a href="{{ route('resource_sein.list') }}" class="list-group-item {!! Request::is('admin/sein/*') ? 'active' : '' !!}">SEIN</a>

   @if( Auth::user()->hasRole('superadministrator') )
	     <a href="{{ route('resource_lookup.admin.list') }}" class="list-group-item {!! Request::is('admin/resource_lookup/*') ? 'active' : '' !!}">Resource Lookup</a>
	@endif
   
</div>

