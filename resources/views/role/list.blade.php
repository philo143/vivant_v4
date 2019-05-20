
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
            {!! Breadcrumbs::render('priv') !!}
            @include ('user.message')
            <h4>Manage Privileges</h4>
            <hr>
            <table class="table table-striped table-narrow table-hover datatable" id="privilege">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Display Name</th>
                        <th>Description</th>
                        <th>Permission</th>
                        <th style="width:25px"></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <a href=" {{ route('priv.create') }} " class="btn btn-primary btn-sm">Add Privilege</a>
            
        </div>
    </div>
    </div>
    <br><br><br><br><br><br><br><br><br><br>
@stop

@section('scripts')
<script>
    $(document).ready(function(){

        var table = $('#privilege').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('priv.data') }}',
            columns: [
                {data: 'id'},
                {data: 'name'},
                {data: 'display_name'},
                {data: 'description'},
                {data: 'permission', orderable: false, searchable: false},
                {data: 'action', orderable: false, searchable: false}
            ]
        }); 

        $('#privilege tbody').on('click', '.btnDelete', function(){
            var r = confirm("Do you want to delete " + $(this).attr('name') + "?");
            if (r) {
                $.post('/admin/privilege/delete', 
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
