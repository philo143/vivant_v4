@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard Settings</div>

                <div class="panel-body">
                     {{ Form::open(['route' => 'dashboard.settings.store', 'class'=>'form-horizontal']) }}
                    <table class="table table-striped table-bordered">
                        @if($role_widgets->count() > 0)
                            @foreach($role_widgets as $role_widget)
                            <tr>
                                <td>
                                    <div class="checkbox">
                                        <label><input type="checkbox" value="{{ $role_widget->widgets->id }}" name="widgets[]">{{ $role_widget->widgets->name }}</label>
                                    </div>
                                </td>
                                <td>
                                    {{ $role_widget->widgets->description }}                                    
                                        @if($role_widget->widgets->with_resources)
                                            <div id="resource_checkbox_{{ $role_widget->widgets->id }}" class="hide">
                                            <br \>
                                                @foreach($resources as $key => $resource)      
                                                    @if($key % 6 == 0) 
                                                        <br \>
                                                    @endif
                                                    <label><input type="checkbox" value="{{ $resource->id }}" name="widgets_resources[{{ $role_widget->widgets->id }}][]">&nbsp;{{ $resource->resource_id }}</label>&nbsp;
                                                @endforeach
                                            </div>
                                        @endif
                                        
                                </td>
                            </tr>                        
                            @endforeach
                        </table>
                        {{ Form::submit('Save Widgets', ['class'=>'btn btn-primary btn-sm']) }}
                        {{ Form::close() }}
                    @else
                        <div class="well">
                            There are no widgets selected for your current privilege.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script type="text/javascript">
    $(document).ready(function(){
        $.ajax({
            url : "{{ route('dashboard.user_widgets') }}",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            // data : {},
            type : "POST",
            error : function(error){
                console.log('Error : '+error)
            },
            success : function(data){
                $.each(data,function(i,val){
                    $('input[name="widgets[]"][value="'+val.widgets_id+'"]').prop('checked',true);
                    $('#resource_checkbox_'+val.widgets_id+'').removeClass('hide');
                    $('input[name="widgets_resources['+val.widgets_id+'][]"][value="'+val.resources_id+'"]').prop('checked',true);
                })                                       
            }
        })            

        $('input[name="widgets[]"]').on('click',function(){
            var checked = (this.checked) ? true : false;
            var widget = $(this).val();
            $('input[name="widgets_resources['+widget+'][]"]').prop('checked', checked);
            if(checked == false){
                $('#resource_checkbox_'+widget+'').addClass('hide');
            }else{
                $('#resource_checkbox_'+widget+'').removeClass('hide');
            }
        })
    })
</script>
@endsection

