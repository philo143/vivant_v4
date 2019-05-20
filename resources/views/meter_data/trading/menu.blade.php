<h5><strong>Meter Data</strong></h5>
<hr>
<div class="list-group">
    <a href="{{ route('meter_data.mq_load.index') }}" class="list-group-item {!! Request::is('meter_data/mq_load') ? 'active' : '' !!}">Daily MQ Load</a>
    
    <a href="{{ route('meter_data.mq_gen.index') }}" class="list-group-item {!! Request::is('meter_data/mq_gen') ? 'active' : '' !!}">Daily MQ Gen</a>
   
</div>
