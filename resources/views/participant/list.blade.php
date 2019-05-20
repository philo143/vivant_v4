
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
            {!! Breadcrumbs::render('participant') !!}
            @include ('user.message')
            <h4>Manage Participants</h4>
            <hr>
            <table class="table table-striped table-narrow table-hover datatable" id="participant">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Participant&nbsp;Name</th>
                        <th>Description</th>
                        <th>Plants</th>
                        <th>Cert&nbsp;Status</th>
                        <th>Cert&nbsp;User</th>
                        <th>Status</th>
                        <th>Date&nbsp;Created</th>
                        <th>Last&nbsp;Modified</th>
                        <th style="width:50px"></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <a href=" {{ route('participants.create') }} " class="btn btn-primary btn-sm">Add Participant</a>
            
        </div>
    </div>
    </div>
    <br><br><br><br><br><br><br><br><br><br>
@stop

@section('scripts')
<script>
    $(document).ready(function(){

        var table = $('#participant').DataTable({
            processing: true,
            // serverSide: true,
            ajax: '{{ route('participants.data') }}',
            columns: [
                {data: 'id'},
                {data: 'participant_name'},
                {data: 'description'},
                {
                    data: 'plants',
                    render : function(data, type, row ){
                        var plants = row.plants;
                        plants = plants.replace(/"/gi, "");
                        plants = plants.replace(/\[/gi, "");
                        plants = plants.replace(/]/gi, "");
                        return plants;
                    }
                    
                },
                {data: 'cert_file'},
                {data: 'cert_user'},
                {data: 'status'},
                {data: 'created_at'},
                {data: 'updated_at'},
                {data: 'action', orderable: false, searchable: false}
            ]
        }); 

        $('#participant tbody').on('click', '.btnDelete', function(){
            var r = confirm("Do you want to delete " + $(this).attr('name') + "?");
            if (r) {
                $.post('/admin/participant/delete', 
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
