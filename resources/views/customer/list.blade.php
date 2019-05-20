@extends('layouts.app')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-2">
	            <div class="list-group">
	                @include('customer.menu')
	            </div>
	        </div>
	        <div class="col-md-10">
				{!! Breadcrumbs::render('customer') !!}
				@include ('user.message')
				<h4>Manage Customers</h4>
				<table class="table table-striped table-narrow table-hover datatable" id="customer">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer&nbsp;Name</th>
                        <th>Customer&nbsp;Full&nbsp;Name</th>
                        <th>Customer&nbsp;Type</th>
                        <th>Sein</th>
                        <th>Users</th>
                        <th>Participants</th>
                        <th>Date&nbsp;Created</th>
                        <th>Last&nbsp;Modified</th>
                        <th style="width:50px"></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <a href=" {{ route('customers.create') }} " class="btn btn-primary btn-sm">Add Customer</a>
			</div>
		</div>
	</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function(){

        var table = $('#customer').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('customers.data') }}',
            order: [[8, "desc"]],
            columns: [
                {data: 'id'},
                {data: 'customer_name'},
                {data: 'customer_full_name'},
                {data: 'customer_type.customer_type', orderable:false},
                {data: 'sein', orderable:false, searchable:false},
                {data: 'users', orderable:false, searchable:false},
                {data: 'participants', orderable:false, searchable:false},
                {data: 'created_at'},
                {data: 'updated_at'},
                {data: 'action', orderable: false, searchable: false}
            ]
        }); 

        $('#customer tbody').on('click', '.btnDelete', function(){
            var r = confirm("Do you want to delete " + $(this).attr('name') + "?");
            if (r) {
                $.post('/admin/customers/delete', 
                    {
                        '_token': $('meta[name=csrf-token]').attr('content'),
                        'id': $(this).attr('id')
                    }, 
                    function(data){
                        table.ajax.reload();
                    }
                )
            }
        });
    });
    
</script>
@endsection

