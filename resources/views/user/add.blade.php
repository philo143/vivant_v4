@extends('layouts.app')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-2">
				@include('user.menu')
			</div>
			<div class="col-md-10">
				{!! Breadcrumbs::render('user.add') !!}
            	@if ( count($errors) )
					<div class="alert alert-danger col-md-7">
						<ul>
						@foreach ($errors->all() as $error)
							<li>{{$error}}</li>
						@endforeach
						</ul>
					</div>
				@endif
				{{-- <div class="col-lg-6">  --}}
				<div class="well bs-component col-md-7"> 
				{{ Form::open(['route' => 'users.store', 'class'=>'form-horizontal']) }}
				<legend>Add User</legend>
				<div class="form-group"> 
					{{ Form::label('username', 'Username:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10"> 
						{{ Form::text('username', '', ['class'=>'form-control input-sm', 'placeholder'=>'username', 'required'=>'required']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('fullname', 'Full Name:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10"> 
						{{ Form::text('fullname', '', ['class'=>'form-control input-sm', 'placeholder'=>'full name', 'required'=>'required']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('email', 'Email:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10"> 
						{{ Form::text('email', '', ['class'=>'form-control input-sm', 'placeholder'=>'email', 'required'=>'required']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('mobile', 'Mobile No:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10"> 
						{{ Form::text('mobile', '', ['class'=>'form-control input-sm', 'placeholder'=>'mobile no']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('password', 'Password:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-4"> 
						{{ Form::password('password', ['class'=>'form-control input-sm', 'placeholder'=>'password', 'required'=>'required']) }}
					</div>
					{{ Form::label('confirm-password', 'Confirm&nbsp;Password:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-4">
						{{ Form::password('confirm-password', ['class'=>'form-control input-sm', 'placeholder'=>'confirm password', 'required'=>'required']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('role', 'Privilege:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10"> 
						{{ Form::select('role', $roles, '',['class'=>'form-control input-sm']) }}
					</div>
				</div>
				<div id="plant_div" class="form-group hidden"> 
					{{ Form::label('plant', 'Plant:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10"> 
						{{ Form::select('plant', $plants, '',['class'=>'form-control input-sm','disabled']) }}
					</div>
				</div>
				<div id="resource_div" class="form-group hidden"> 
					{{ Form::label('resource', 'Resource:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10"> 
						{{ Form::select('resource', [], '',['class'=>'form-control input-sm','disabled']) }}
					</div>
				</div>
				<div class="form-group"> 
					{{ Form::label('status', 'Status:', ['class'=>'col-lg-2 control-label']) }}
					<div class="col-lg-10"> 
						{{ Form::select('status', ['0'=>'inactive','1'=>'active'], '',['class'=>'form-control input-sm']) }}
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-10 col-lg-offset-2">
						{{ Form::submit('Add User', ['class'=>'btn btn-primary btn-sm']) }}
					</div>
				</div>
				{{ Form::close() }}
				</div>
				{{-- </div> --}}
			</div>
		</div>
	</div>
@stop

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			$('#role').on('change',function(){
				var has_plant = null;
				$.ajax({
                    url : "/admin/privilege/has_plant",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data : { id : $('select[name="role"]').val()},
                    type : "POST",
                    async: false,
                    error : function(error){
                        console.log('Error : '+error)
                    },
                    success : function(data){
                        has_plant = data;
                    }
                })              
				if(has_plant == 1){
					$('#plant_div,#resource_div').removeClass('hidden');
					$('#plant_div select,#resource_div select').prop('disabled',false);
				}else{
					$('#plant_div,#resource_div').addClass('hidden');
					$('#plant_div select,#resource_div select').prop('disabled',true);
				}
			})
			$('select[name="plant"]').on('change',function(){
				$.ajax({
                    url : "/resources/list_by_plant_id",
                    async : false,
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data : { plant_id : $('select[name="plant"]').val()},
                    type : "POST",
                    error : function(error){
                        console.log('Error : '+error)
                    },
                    success : function(data){
                         $('select[name="resource"]').html('');
                        var html = '';
                        for (var i=0;i<data.length;i++){
                            html+='<option value="'+data[i].id+'">'+data[i].resource_id+'</option>';
                        }
                        $('select[name="resource"]').html(html);
                    }
                }) 
			})
			$('select[name="plant"],select[name="role"]').trigger('change');
		})
	</script>
@stop