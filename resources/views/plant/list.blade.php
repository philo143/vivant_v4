
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
            {!! Breadcrumbs::render('plant') !!}
            @include ('user.message')
            <h4>Manage Plants</h4>
            <hr>
            <table class="table table-striped table-narrow table-hover datatable" id="plant">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Plant&nbsp;Name</th>
                        <th>Resources</th>
                        <th>Description</th>
                        <th>Location</th>
                        <th>Date&nbsp;Created</th>
                        <th>Last&nbsp;Modified</th>
                        <th style="width:50px"></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <a href=" {{ route('plants.create') }} " class="btn btn-primary btn-sm">Add Plant</a>
            
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
            ajax: '{{ route('plants.data') }}',
            columns: [
                {data: 'id'},
                {data: 'plant_name'},
                {   
                    data: 'resources',orderable: false, searchable: false,
                    render : function(data, type, row ){
                        var resources = row.resources;
                        resources = resources.replace(/"/gi, "");
                        resources = resources.replace(/\[/gi, "");
                        resources = resources.replace(/]/gi, "");
                        return resources;
                    }
                },
                {data: 'description'},
                {data: 'location'},
                {data: 'created_at'},
                {data: 'updated_at'},
                {data: 'action', orderable: false, searchable: false}
            ]
        }); 

        $('#plant tbody').on('click', '.btnDelete', function(){
            var r = confirm("Do you want to delete " + $(this).attr('name') + "?");
            if (r) {
                $.post('/admin/plant/delete', 
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
