@extends('layouts.app')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-2">
	            <div class="list-group">
	                @include('dashboard.manage_menu')
	            </div>
	        </div>
	        <div class="col-md-10">
                @include ('user.message')
                {!! Breadcrumbs::render('manage_dashboard') !!}		
				<h4>Manage Dashboard</h4>
                <hr>
                {{ Form::open(['route' => 'dashboard.manage.store', 'class'=>'form-horizontal']) }}
                <div class="form-group"> 
                    <div class="col-lg-1">
                        {{ Form::label('role', 'Privilege:', ['class'=>'col-lg-2 control-label']) }}
                    </div>
                    <div class="col-lg-3"> 
                        <select class="form-control input-sm" id="privilege" name="privilege">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                            @endforeach
                        </select>   
                    </div>
                </div>
                <table class="table table-striped table-bordered">
                    @foreach($widgets as $widget)
                    <tr>
                        <td>
                            <div class="checkbox">
                                <label><input type="checkbox" value="{{ $widget->id }}" name="widgets[]">{{ $widget->name }}</label>
                            </div>
                        </td>
                        <td>
                            {{ $widget->description }}
                        </td>
                    </tr>                        
    				@endforeach
                </table>
                {{ Form::submit('Save Widgets', ['class'=>'btn btn-primary btn-sm']) }}
                {{ Form::close() }}
			</div>
		</div>
	</div>
    <br>
@endsection

@section('scripts')
<script type="text/javascript">
    $(document).ready(function(){
        $('#privilege').on('change',function(){
            $('input[type="checkbox"]').attr('checked',false);
            $.ajax({
                url : "{{ route('dashboard.role_widgets') }}",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data : { id : $(this).val() },
                type : "POST",
                error : function(error){
                    console.log('Error : '+error)
                },
                success : function(data){
                    $.each(data,function(i,val){
                        $('input[value="'+val+'"]').attr('checked',true);
                    })                                       
                }
            })    
        })
        $('#privilege').trigger('change');
    })
</script>
@endsection

