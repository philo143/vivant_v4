<div class="container">
    <nav class="tabs is-boxed">
        <ul>
            <li class="{{ Request::is('dashboard') ? 'is-active' : '' }}"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="{{ Request::is('trading') ? 'is-active' : '' }}"><a href="#">Trading</a></li>
            <li class="{{ Request::is('market_analysis') ? 'is-active' : '' }}"><a href="#">Market Analysis</a></li>
            <li class="{{ Request::is('plant') ? 'is-active' : '' }}"><a href="#">Plant Operations</a></li>
            <li class="{{ Request::is('bcq') ? 'is-active' : '' }}"><a href="#">BCQ</a></li>
            <li class="{{ Request::is('billing') ? 'is-active' : '' }}"><a href="#">Billing & Settlements</a></li>
            <li class="{{ Request::is('sales') ? 'is-active' : '' }}"><a href="#">Sales</a></li>
            <li class="{{ Request::is('buyer/*') || Request::is('buyer') ? 'is-active' : '' }}"><a href="{{ route('buyer.index') }}">Buyer/Customer</a></li>
            @role('superadministrator')
            <li class="{{ Request::is('admin/*') || Request::is('admin') ? 'is-active' : '' }}"><a href="{{ route('admin.index') }}">Admin Tools</a></li>
            @endrole
        </ul>
    </nav>
</div>