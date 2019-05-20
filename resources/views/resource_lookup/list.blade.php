
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
    <div class="row">
        <div class="col-md-2">
            <div class="list-group">
                @include('system.menu')
            </div>
        </div>
        <div class="col-md-10">
            {!! Breadcrumbs::render('resource_lookup') !!}
            @include ('user.message')
            <h4>Manage Resource Lookup</h4>
            <hr>
            <table class="table table-striped table-narrow table-hover datatable" id="plant">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Resource ID</th>
                        <th>Region</th>
                        <th>Type</th>
                        <th>Is MMS Reserve</th>
                        <th>Reserve Classes</th>
                        <th style="width:50px"></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <a href=" {{ route('resource_lookup.admin.create') }} " class="btn btn-primary btn-sm">Add Resource</a>
            
        </div>
    </div>
    </div>
    <br><br><br><br><br><br><br><br><br><br>
@stop

@section('scripts')
<script>
    $(document).ready(function(){

        var table = $('#plant').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('resource_lookup.admin.data') }}',
            columns: [
                {data: 'id'},
                {data: 'resource_id'},
                {data: 'region'},
                {data: 'type'},
                {data: 'is_mms_reserve'},
                {data: 'reserve_classes'},
                {data: 'action', orderable: false, searchable: false}
            ]
        }); 

        $('#plant tbody').on('click', '.btnDelete', function(){
            var r = confirm("Do you want to delete " + $(this).attr('name') + "?");
            if (r) {
                $.post('/admin/resource_lookup/delete', 
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
