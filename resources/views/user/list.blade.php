
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
    <div class="row">
        <div class="col-md-2">
            <div class="list-group">
                @include('user.menu')
            </div>
        </div>
        <div class="col-md-10">
            {!! Breadcrumbs::render('user') !!}
            @include ('user.message')
            <h4>Manage Users</h4>
            <hr>
            <table class="table table-striped table-narrow table-hover datatable" id="users">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Privilege</th>
                        <th>Last Login</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <a href=" {{ route('users.create') }} " class="btn btn-primary btn-sm">Add User</a>
        </div>
    </div>
    </div>
    <br><br><br><br><br><br><br><br><br><br>
@endsection

@section('scripts')
<script>
    $.extend({
        list : function() {
                   
        }
    })

    $(document).ready(function(){

        var table = $('#users').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('users.data') }}',
            columns: [
                {data: 'id', name: 'users.id'},
                {data: 'username', name: 'users.username'},
                {data: 'fullname', name: 'users.fullname'},
                {data: 'email', name: 'users.email'},
                {data: 'mobile', name: 'users.mobile'},
                {
                    data: 'role', orderable: false, searchable: false,
                    render : function(data, type, row ){
                        var role = row.role;
                        role = role.replace(/"/gi, "");
                        role = role.replace(/\[/gi, "");
                        role = role.replace(/]/gi, "");
                        return role;
                    }
                },
                {data: 'last_login', name: 'users.last_login'},
                {data: 'status', name: 'users.status'},
                {data: 'action', orderable: false, searchable: false}
            ]
        }); 

        $('#users tbody').on('click', '.btnDelete', function(){
            var r = confirm("Do you want to delete " + $(this).attr('name') + "?");
            if (r) {
                $.post('/admin/users/delete', 
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
@stop
