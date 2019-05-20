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
				{!! Breadcrumbs::render('resource_sein') !!}
				@include ('user.message')
				<h4>Manage SEIN</h4>
				<table class="table table-striped table-narrow table-hover datatable" id="sein">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Sein</th>
                        <th>Resource</th>
                        <th>Date&nbsp;Created</th>
                        <th>Last&nbsp;Modified</th>
                        <th style="width:50px"></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <a href=" {{ route('resource_sein.create') }} " class="btn btn-primary btn-sm">Add SEIN</a>
			</div>
		</div>
	</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function(){

        var table = $('#sein').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('resource_sein.data') }}',
            order: [[3, "desc"]],
            columns: [
                {data: 'id'},
                {data: 'sein'},
                {data: 'resource.resource_id'},
                {data: 'created_at'},
                {data: 'updated_at'},
                {data: 'action', orderable: false, searchable: false}
            ]
        }); 

        $('#sein tbody').on('click', '.btnDelete', function(){
            var r = confirm("Do you want to delete " + $(this).attr('name') + "?");
            if (r) {
                $.post('/admin/sein/delete', 
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

