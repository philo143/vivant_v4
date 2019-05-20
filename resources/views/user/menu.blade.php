<h5><strong>Manage User</strong></h5>
<hr>
<div class="list-group">
    <a href="{{ route('users.list') }}" class="list-group-item {!! Request::is('admin/users/*') ? 'active' : '' !!}">Users</a>
    <a href="{{ route('priv.list') }}" class="list-group-item {!! Request::is('admin/privilege/*') ? 'active' : '' !!}">Privileges</a>
</div>
<h5><strong>Maintenance</strong></h5>
<hr>
<div class="list-group">
    <a href="#" class="list-group-item">User Activity</a>
    <a href="#" class="list-group-item">Reset User Password</a>
</div>
