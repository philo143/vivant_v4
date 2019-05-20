<h5><strong>Manage System Settings</strong></h5>
<hr>
<div class="list-group">
   @if( Auth::user()->hasRole('superadministrator') )
	     <a href="{{ route('resource_lookup.admin.list') }}" class="list-group-item {!! Request::is('admin/resource_lookup/*') ? 'active' : '' !!}">Resource Lookup</a>

	     <a href="{{ route('ip_tables.index') }}" class="list-group-item {!! Request::is('admin/ip_tables/*') ? 'active' : '' !!}">IP Address</a>
	@endif
   
</div>