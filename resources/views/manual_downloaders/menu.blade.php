<h5><strong>Manual Downloaders</strong></h5>
<hr>
<div class="list-group">
    <a href="{{ route('manual_downloader.rtd_lmp.index') }}" class="list-group-item {!! Request::is('manual_downloader/rtd_lmp') ? 'active' : '' !!}">RTD Output Display - LMP</a>

    <a href="{{ route('manual_downloader.rtd_resource_specific.index') }}" class="list-group-item {!! Request::is('manual_downloader/rtd_resource') ? 'active' : '' !!}">RTD Output Display - Resource Specific</a>
    
   
    <a href="{{ route('manual_downloader.mpd_lmp.index') }}" class="list-group-item {!! Request::is('manual_downloader/mpd_lmp') ? 'active' : '' !!}">Market Projection Display - LMP</a>
</div>
