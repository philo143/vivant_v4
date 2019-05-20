<h5><strong>BCQ</strong></h5>
<hr>
<div class="list-group">
    <a href="{{ route('bcq.uploader.index') }}" class="list-group-item {!! Request::is('bcq/uploader') ? 'active' : '' !!}">BCQ Uploader</a>
    
    <a href="{{ route('bcq.report.index') }}" class="list-group-item {!! Request::is('bcq/report') ? 'active' : '' !!}">BCQ Report</a>
   
</div>
