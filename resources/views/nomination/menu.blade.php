<h5><strong>Nominations</strong></h5>
<hr>
<div class="list-group">

	@if(Auth::check())
	    @if( Auth::user()->hasCustomers() == true)
			<a href="{{ route('nomination.template') }}" class="list-group-item {!! Request::is('nomination/template') ? 'active' : '' !!}">Download Nomination Template</a>

			<a href="{{ route('nomination.day_ahead') }}" class="list-group-item {!! Request::is('nomination/day_ahead') ? 'active' : '' !!}">Day Ahead Nominations</a>
		    <a href="{{ route('nomination.week_ahead') }}" class="list-group-item {!! Request::is('nomination/week_ahead') ? 'active' : '' !!}">Week Ahead Nominations</a>
		    <a href="{{ route('nomination.month_ahead') }}" class="list-group-item {!! Request::is('nomination/month_ahead') ? 'active' : '' !!}">Month Ahead Nominations</a>
		    <a href="{{ route('nomination.transactions') }}" class="list-group-item">Nomination Transactions</a>
    		<a href="{{ route('nomination.running_report') }}" class="list-group-item">Running Nominations Report</a>

	    @endif

	    @if( Auth::user()->hasRole('superadministrator') ||
            Auth::user()->hasRole('administrator') ||
            Auth::user()->hasRole('trader') )
             <a href="{{ route('nomination.extraction_report') }}" class="list-group-item">Nominations Extraction</a>
			 <a href="{{ route('nomination.override') }}" class="list-group-item">Override Nominations</a>
        @endif	   
	@endif
    
    
    
</div>
