<h5><strong>Shift Reports</strong></h5>
<hr>
<div class="list-group">
    <a href="{{ route('trading_shift_report.list') }}" class="list-group-item {!! Request::is('trading/shift_report/index') ? 'active' : '' !!}">Trading Shift Report</a>

    <a href="{{ route('plant_shift_report.list') }}" class="list-group-item {!! Request::is('trading/shift_report/plantIndex') ? 'active' : '' !!}">Plant Shift Report</a>

    <a href="{{ route('shift_report.extraction') }}" class="list-group-item {!! Request::is('trading/shift_report/extractionIndex') ? 'active' : '' !!}">Shift Report Extraction</a>
</div>
