<h5><strong>Trading</strong></h5>
<hr>
<div class="list-group">

	@if(Auth::check())

	    <a href="{{ route('aspa_nomination.input') }}"  class="list-group-item {!! Request::is('aspa_nomination/input') ? 'active' : '' !!}">ASPA Nomination Input</a>

	    <a href="{{ route('aspa_nomination.view') }}"  class="list-group-item {!! Request::is('aspa_nomination/view') ? 'active' : '' !!}">ASPA Nomination View Page</a>
	@endif
    
    
    
</div>
