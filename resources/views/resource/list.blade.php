
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
    <div class="row">
        <div class="col-md-2">
            <div class="list-group">
                @include('participant.menu')
            </div>
        </div>
        <div class="col-md-10">
            {!! Breadcrumbs::render('resource') !!}
            @include ('user.message')
            <h4>Manage Resources</h4>
            <hr>
            <table class="table table-striped table-narrow table-hover datatable" id="resources">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Resource&nbsp;ID</th>
                        <th>Region</th>
                        <th>Pmin</th>
                        <th>Pmax</th>
                        <th>Ramp&nbsp;Rate</th>
                        <th>Ramp&nbsp;Up</th>
                        <th>Ramp&nbsp;Down</th>
                        <th>Last&nbsp;Modified</th>
                        <th style="width:50px"></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <a href=" {{ route('resources.create') }} " class="btn btn-primary btn-sm">Add Resources</a>
            
        </div>
    </div>
    </div>
    <br><br><br><br><br><br><br><br><br><br>
@stop

@section('scripts')
<script>
    $(document).ready(function(){

        var table = $('#resources').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('resources.data') }}',
            columns: [
                {data: 'id'},
                {data: 'resource_id'},
                {data: 'region'},
                {data: 'pmin'},
                {data: 'pmax'},
                {data: 'ramp_rate'},
                {data: 'ramp_up'},
                {data: 'ramp_down'},
                {data: 'updated_at'},
                {data: 'action', orderable: false, searchable: false}
            ]
        }); 

        $('#resources tbody').on('click', '.btnDelete', function(){
            var r = confirm("Do you want to delete " + $(this).attr('name') + "?");
            if (r) {
                $.post('/admin/resources/delete', 
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
