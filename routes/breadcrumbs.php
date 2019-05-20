<?php

// Admin
Breadcrumbs::register('admin', function ($breadcrumbs)
{
	$breadcrumbs->push('admin', route('admin.index'));
});
// User
Breadcrumbs::register('user', function($breadcrumbs)
{
	$breadcrumbs->parent('admin');
	$breadcrumbs->push('users', route('users.list'));
});
Breadcrumbs::register('user.add', function($breadcrumbs)
{
	$breadcrumbs->parent('user');
	$breadcrumbs->push('add user', route('users.create'));		
});
Breadcrumbs::register('user.edit', function($breadcrumbs)
{
	$breadcrumbs->parent('user');
	$breadcrumbs->push('edit user', '');		
});
// Privs
Breadcrumbs::register('priv', function($breadcrumbs)
{
	$breadcrumbs->parent('admin');
	$breadcrumbs->push('privileges', route('priv.list'));
});
Breadcrumbs::register('priv.add', function($breadcrumbs)
{
	$breadcrumbs->parent('priv');
	$breadcrumbs->push('Add Privilege', route('priv.create'));
});
Breadcrumbs::register('priv.edit', function($breadcrumbs)
{
	$breadcrumbs->parent('priv');
	$breadcrumbs->push('Edit Privilege', '');
});
// Participants
Breadcrumbs::register('participant', function($breadcrumbs)
{
	$breadcrumbs->parent('admin');
	$breadcrumbs->push('participants', route('participants.list'));
});
Breadcrumbs::register('participant.add', function($breadcrumbs)
{
	$breadcrumbs->parent('participant');
	$breadcrumbs->push('Add Participants', route('participants.create'));
});
Breadcrumbs::register('participant.edit', function($breadcrumbs)
{
	$breadcrumbs->parent('participant');
	$breadcrumbs->push('Edit Participants', '');
});
// Plants
Breadcrumbs::register('plant', function($breadcrumbs)
{
	$breadcrumbs->parent('admin');
	$breadcrumbs->push('plants', route('plants.list'));
});
Breadcrumbs::register('plant.add', function($breadcrumbs)
{
	$breadcrumbs->parent('plant');
	$breadcrumbs->push('Add Plant', route('plants.create'));
});
Breadcrumbs::register('plant.edit', function($breadcrumbs)
{
	$breadcrumbs->parent('plant');
	$breadcrumbs->push('Edit Plant', '');
});
// Resources
Breadcrumbs::register('resource', function($breadcrumbs)
{
	$breadcrumbs->parent('admin');
	$breadcrumbs->push('resources', route('resources.list'));
});
Breadcrumbs::register('resource.add', function($breadcrumbs)
{
	$breadcrumbs->parent('resource');
	$breadcrumbs->push('Add Resource', route('resources.create'));
});
Breadcrumbs::register('resource.edit', function($breadcrumbs)
{
	$breadcrumbs->parent('resource');
	$breadcrumbs->push('Edit Resource', '');
});
// Customers
Breadcrumbs::register('customer', function($breadcrumbs)
{
	$breadcrumbs->parent('admin');
	$breadcrumbs->push('customers', route('customers.list'));
});
Breadcrumbs::register('customer.add', function($breadcrumbs)
{
	$breadcrumbs->parent('customer');
	$breadcrumbs->push('Add Customer', route('customers.create'));
});
Breadcrumbs::register('customer.edit', function($breadcrumbs)
{
	$breadcrumbs->parent('customer');
	$breadcrumbs->push('Edit Customer', '');
});
// Customer Sein
Breadcrumbs::register('customer_sein', function($breadcrumbs)
{
	$breadcrumbs->parent('admin');
	$breadcrumbs->push('sein', route('customer_sein.list'));
});
Breadcrumbs::register('customer_sein.add', function($breadcrumbs)
{
	$breadcrumbs->parent('customer_sein');
	$breadcrumbs->push('Add Sein', route('customer_sein.create'));
});
Breadcrumbs::register('customer_sein.edit', function($breadcrumbs)
{
	$breadcrumbs->parent('customer_sein');
	$breadcrumbs->push('Edit Sein', '');
});
// Resource Sein
Breadcrumbs::register('resource_sein', function($breadcrumbs)
{
	$breadcrumbs->parent('admin');
	$breadcrumbs->push('sein', route('resource_sein.list'));
});
Breadcrumbs::register('resource_sein.add', function($breadcrumbs)
{
	$breadcrumbs->parent('resource_sein');
	$breadcrumbs->push('Add Sein', route('resource_sein.create'));
});
Breadcrumbs::register('resource_sein.edit', function($breadcrumbs)
{
	$breadcrumbs->parent('resource_sein');
	$breadcrumbs->push('Edit Sein', '');
});


// Resource Lookup
Breadcrumbs::register('resource_lookup', function($breadcrumbs)
{
	$breadcrumbs->parent('admin');
	$breadcrumbs->push('resource lookup', route('resource_lookup.admin.list'));
});
Breadcrumbs::register('resource_lookup.add', function($breadcrumbs)
{
	$breadcrumbs->parent('resource_lookup');
	$breadcrumbs->push('Add Resource', route('resource_lookup.admin.create'));
});
Breadcrumbs::register('resource_lookup.edit', function($breadcrumbs)
{
	$breadcrumbs->parent('resource_lookup');
	$breadcrumbs->push('Edit Resource', '');
});


// IP Address
Breadcrumbs::register('ip_tables', function($breadcrumbs)
{
	$breadcrumbs->parent('admin');
	$breadcrumbs->push('ip tables', route('ip_tables.index'));
});
Breadcrumbs::register('ip_tables.add', function($breadcrumbs)
{
	$breadcrumbs->parent('ip_tables');
	$breadcrumbs->push('Create New IP Address', route('ip_tables.create'));
});


// Nominations
Breadcrumbs::register('nomination', function($breadcrumbs)
{
	$breadcrumbs->push('nomination', route('nomination.index'));
});
Breadcrumbs::register('download_nomination_template', function($breadcrumbs)
{
	$breadcrumbs->parent('nomination');
	$breadcrumbs->push('Download Nomination Template', route('nomination.template'));
});
Breadcrumbs::register('day_ahead_nomination', function($breadcrumbs)
{
	$breadcrumbs->parent('nomination');
	$breadcrumbs->push('Day Ahead Nomination', route('nomination.day_ahead'));
});
Breadcrumbs::register('week_ahead_nomination', function($breadcrumbs)
{
	$breadcrumbs->parent('nomination');
	$breadcrumbs->push('Week Ahead Nomination', route('nomination.week_ahead'));
});
Breadcrumbs::register('month_ahead_nomination', function($breadcrumbs)
{
	$breadcrumbs->parent('nomination');
	$breadcrumbs->push('Month Ahead Nomination', route('nomination.month_ahead'));
});
Breadcrumbs::register('transations_nomination', function($breadcrumbs)
{
	$breadcrumbs->parent('nomination');
	$breadcrumbs->push('Nomination Transactions', route('nomination.transactions'));
});

Breadcrumbs::register('running_nomination', function($breadcrumbs)
{
	$breadcrumbs->parent('nomination');
	$breadcrumbs->push('Running Nominations Report', route('nomination.running_report'));
});

Breadcrumbs::register('extraction_nomination', function($breadcrumbs)
{
	$breadcrumbs->parent('nomination');
	$breadcrumbs->push('Nomination Extraction Report', route('nomination.extraction_report'));
});

// Manage Dashboard
Breadcrumbs::register('manage_dashboard', function($breadcrumbs)
{
	$breadcrumbs->parent('admin');
	$breadcrumbs->push('dashboard', route('dashboard.manage'));
});
### Plant Operations
Breadcrumbs::register('plant_operations', function ($breadcrumbs)
{
	$breadcrumbs->push('plant operations', route('plant_operations.index'));
});

Breadcrumbs::register('plant_ops_shift_report', function($breadcrumbs)
{
	$breadcrumbs->parent('plant_operations');
	$breadcrumbs->push('plant operational shift report', route('plant_shift_report.index'));

});


// Nominations
Breadcrumbs::register('trading', function($breadcrumbs)
{
	$breadcrumbs->push('trading', route('trading.index'));
});
Breadcrumbs::register('aspa_nomination_input', function($breadcrumbs)
{
	$breadcrumbs->parent('trading');
	$breadcrumbs->push('ASPA Nomination Input', route('aspa_nomination.input'));
});

Breadcrumbs::register('aspa_nomination_view', function($breadcrumbs)
{
	$breadcrumbs->parent('trading');
	$breadcrumbs->push('ASPA Nomination View Page', route('aspa_nomination.view'));
});
