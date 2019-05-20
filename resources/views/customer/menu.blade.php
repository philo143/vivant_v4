<h5><strong>Manage Customer</strong></h5>
<hr>
<div class="list-group">
    <a href="{{ route('customers.list') }}" class="list-group-item {!! Request::is('admin/customers/*') ? 'active' : '' !!}">Customers</a>
    {{-- <a href="{{ route('users.list') }}" class="list-group-item {!! Request::is('admin/customer_types/*') ? 'active' : '' !!}">Customer Types</a> --}}
    <a href="{{ route('customer_sein.list') }}" class="list-group-item {!! Request::is('admin/sein/*') ? 'active' : '' !!}">Sein</a>
</div>
