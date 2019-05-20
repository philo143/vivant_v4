<nav class="nav has-shadow">
    <div class="container">
        <div class="nav-left">
            
            @if ( Request::is('admin/*') || Request::is('admin') )
				<a class="nav-item is-tab {{ Request::is('admin/users/*') || Request::is('admin/users') ? 'is-active' : '' }}" href=" {{ route('users.list') }}">Users</a>
            	<a class="nav-item is-tab {{ Request::is('admin/privileges/*') || Request::is('admin/privileges') ? 'is-active' : '' }}" href=" {{ route('privilege.index') }} ">Privileges</a>
            @endif

            {{-- <li class="{{ Request::is('admin/*') || Request::is('admin') ? 'is-active' : '' }}"><a href="{{ route('admin.index') }}">Admin Tools</a></li> --}}

        </div>
    </div>
</nav>