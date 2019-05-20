<h5><strong>Plant Capability</strong></h5>
<hr>
<div class="list-group">
    <a href="{{ route('scheduled_offer') }}" class="list-group-item {!! Request::is('bids_and_offers/scheduled_offer') ? 'active' : '' !!}">Scheduled Bids and Offer</a>
    <a href="{{ route('energy_offer') }}" class="list-group-item {!! Request::is('bids_and_offers/energy_offer') ? 'active' : '' !!}">Energy Offer</a>
    <a href="{{ route('standing_offer') }}" class="list-group-item {!! Request::is('bids_and_offers/standing_offer') ? 'active' : '' !!}">Standing Offer</a>
    <a href="{{ route('day_ahead_reserve') }}" class="list-group-item {!! Request::is('bids_and_offers/day_ahead_reserve') ? 'active' : '' !!}">Day Ahead Offer (Reserve)</a>
    <a href="{{ route('standing_reserve') }}" class="list-group-item {!! Request::is('bids_and_offers/standing_reserve') ? 'active' : '' !!}">Standing Offer (Reserve)</a>
    <a href="{{ route('offer_summary') }}" class="list-group-item {!! Request::is('bids_and_offers/offer_summary') ? 'active' : '' !!}">Offer Summary</a>
    <a href="{{ route('offer_templates') }}" class="list-group-item {!! Request::is('bids_and_offers/offer_templates') ? 'active' : '' !!}">Offer Templates</a>
</div>
