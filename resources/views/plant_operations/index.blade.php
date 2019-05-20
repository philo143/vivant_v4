@extends('layouts.app')

@section('content')
    <div class="container-fluid">
    <div class="row">
        <div class="col-md-2">
            <h5><strong>Plant Operations</strong></h5>
            <hr>
            <div class="list-group">
                <a href="{{ route('realtime_plant_capability.list') }}" class="list-group-item">Plant Capability</a>
                <a href="{{ route('realtime_plant_monitoring.plantList') }}" class="list-group-item">Realtime Plant Monitoring</a>
                <a href="{{ route('plant_shift_report.index') }}" class="list-group-item">Plant Operational Shift Report</a>
            </ul>
            </div>
        </div>
        <div class="col-md-10">
            <h4></h4>
            <hr>
        </div>
    </div>
    </div>

    
  
  

	<div class="columns"></div>
@endsection

