
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
            {!! Breadcrumbs::render('ip_tables') !!}
            @include ('user.message')
            <h4>Manage IP Address</h4>
            <hr>
            {{ Form::open(['route' => 'ip_tables.store', 'method'=>'post', 'class'=>'form-horizontal']) }}
            @foreach ($data as $key => $rows)
                @php 
                $label = strtoupper($key);
                @endphp 
                <div class="form-group"> 
                    {{ Form::label($key, $label .' :', ['class'=>'col-lg-3 control-label']) }}
                    <div class="col-lg-4"> 
                        <select class="form-control input-sm" name="{{$key}}" id="{{$key}}">
                            @foreach ($rows as $row)
                                <option value="{{$row->id}}" {{ $row->status === 1 ? "selected=true" : "" }}>{{$row->ip_address}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>


            @endforeach

            <div class="form-group">
                <div class="col-lg-8 col-lg-offset-3">
                    {{ Form::submit('Set and Save', ['class'=>'btn btn-primary btn-sm']) }}
                    <a href="{{ route('ip_tables.create') }}" class="btn btn-success btn-sm">Create New IP Address</a>
                </div>
            </div>

            {{ Form::close() }}
        </div>
    </div>
    </div>
    <br><br><br><br><br><br><br><br><br><br>
@stop

@section('scripts')
<script>
    $(document).ready(function(){

        
    });
    
</script>
@stop
