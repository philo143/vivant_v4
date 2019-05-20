@extends('layouts.app')

@section('content')

<div class="container-fluid">
	<div class="row">
		<div class="col-md-2">
            <div class="list-group">
                @include('nomination.menu')
            </div>
        </div>
        <div class="col-md-10">
        	{!! Breadcrumbs::render('download_nomination_template') !!}
			@include ('user.message')
			<h4>Download Nomination Template</h4>
			<hr>
			{{ Form::open(['id'=>'form_retrieve', 'class'=>'form-horizontal']) }}
			<div class="form-group">
				{{ Form::label('customer', 'Customer :', ['class'=>'col-lg-2 control-label']) }}
				<div class="col-lg-3">
					{{ Form::select('customer', $customers, '', ['class'=>'form-control input-sm', 'id'=>'customer']) }}
				</div>
			</div>
			<div class="form-group">
				{{ Form::label('templates', 'Templates :', ['class'=>'col-lg-2 control-label']) }}
				<div class="col-lg-4">
					@foreach ($templates as $template)
					    <div class="radio">
						  <label><input type="radio" name="templates" value="{{$template['value']}}"
						  	 daterange="{{$template['daterange']}}"
						  	 filename="{{$template['filename']}}"
						  	 >{{$template['label']}} </label>
						</div>
					@endforeach

					
				</div>
			</div>

			<div class="form-group">
				{{ Form::label('delivery_date', 'Delivery&nbsp;Date:', ['class'=>'col-lg-2 control-label']) }}
				<div class="col-lg-3">
					{{ Form::text('delivery_date', '', ['class'=>'form-control input-sm', 'disabled'=>true, 'id'=>'delivery_date']) }}
				</div>
			</div>

			<br>
			<div class="form-group">
				<div class="col-lg-12" id="filename_container">
				</div>				
			</div>

			{{ Form::close() }}
			<div class="error"></div> 
			
			<br><br>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>

$.extend({
	selectTemplate : function(){
		var template = $('input[name=templates]:checked').val();
		var daterange = $('input[name=templates]:checked').attr('daterange');
		var filename = $('input[name=templates]:checked').attr('filename');
		$('#delivery_date').val(daterange);
		$('#filename_container').html('<div class=" alert alert-success"><a style="color:white; cursor:pointer" name="lnk"><h6>'+filename+'</h6></a></div>	');

		$('a[name=lnk]').unbind().bind('click',function(){
			$.downloadFile();
		});
	}
	,downloadFile :function(){
            var template = $('input[name=templates]:checked').val();
			var daterange = $('input[name=templates]:checked').attr('daterange');
			var filename = $('input[name=templates]:checked').attr('filename');

            var params = '';
            params+='?template='+template;
            params+='&daterange='+daterange;
            params+='&filename='+filename;
            window.location.href = '/nomination/template/file'+params;
    } //
});
$(document).ready(function(){
	$("input:radio[name=templates]:first").attr('checked', true);
	$("input:radio[name=templates]").unbind().bind('change',function(){
		$.selectTemplate();
	});
	$("input:radio[name=templates]").trigger('change');

});
</script>
@endsection