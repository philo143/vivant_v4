@extends('layouts.app')

@section('content')

<div class="container-fluid">
	<div class="row">
		<div class="col-md-2">
            <div class="list-group">
                @include('trading.menu')
            </div>
        </div>
    </div>
</div>

@endsection