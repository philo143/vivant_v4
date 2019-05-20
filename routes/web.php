<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Route::get('/home', ['as'=>'home','uses'=>'HomeController@index']);	
// users
Route::get('admin/users/list', ['as'=>'users.list','uses'=>'UserController@index','middleware'=>['permission:users-read']]);
Route::get('admin/users/data', ['as'=>'users.data','uses'=>'UserController@usersData','middleware'=>['permission:users-read']]);
Route::get('admin/users/create', ['as'=>'users.create','uses'=>'UserController@create','middleware'=>['permission:users-create']]);
Route::post('admin/users/store', ['as'=>'users.store','uses'=>'UserController@store','middleware'=>['permission:users-create']]);
Route::get('admin/users/edit/{user}', ['as'=>'users.edit','uses'=>'UserController@edit','middleware'=>['permission:users-update']]);
Route::patch('admin/users/edit/{user}', ['as'=>'users.update','uses'=>'UserController@update','middleware'=>['permission:users-update']]);
Route::post('admin/users/delete', ['as'=>'users.delete','uses'=>'UserController@delete','middleware'=>['permission:users-delete']]);
// privilege
Route::get('admin/privilege/list', ['as'=>'priv.list','uses'=>'RoleController@index','middleware'=>['permission:acl-read']]);
Route::get('admin/privilege/data', ['as'=>'priv.data','uses'=>'RoleController@privData','middleware'=>['permission:acl-read']]);
Route::get('admin/privilege/create', ['as'=>'priv.create','uses'=>'RoleController@create','middleware'=>['permission:acl-create']]);
Route::post('admin/privilege/store', ['as'=>'priv.store','uses'=>'RoleController@store','middleware'=>['permission:acl-create']]);
Route::get('admin/privilege/edit/{user}', ['as'=>'priv.edit', 'uses'=>'RoleController@edit','middleware'=>['permission:acl-update']]);
Route::patch('admin/privilege/edit/{user}', ['as'=>'priv.update', 'uses'=>'RoleController@update','middleware'=>['permission:acl-update']]);
Route::post('admin/privilege/delete', ['as'=>'priv.delete','uses'=>'RoleController@delete','middleware'=>['permission:acl-delete']]);
Route::post('admin/privilege/has_plant', ['as'=>'priv.has_plant','uses'=>'RoleController@getPrivPlant']);
// participants
Route::get('admin/participant/list', ['as'=>'participants.list', 'uses'=>'ParticipantsController@index']);
Route::get('admin/participant/data', ['as'=>'participants.data', 'uses'=>'ParticipantsController@data']);
Route::get('admin/participant/create', ['as'=>'participants.create', 'uses'=>'ParticipantsController@create']);
Route::post('admin/participant/store', ['as'=>'participants.store', 'uses'=>'ParticipantsController@store']);
Route::get('admin/participant/edit/{participant}', ['as'=>'participants.edit', 'uses'=>'ParticipantsController@edit']);
Route::patch('admin/participant/edit/{participant}', ['as'=>'participants.update', 'uses'=>'ParticipantsController@update']);
Route::post('admin/participant/delete', ['as'=>'participants.delete', 'uses'=>'ParticipantsController@delete']);
// plants
Route::get('admin/plant/list', ['as'=>'plants.list', 'uses'=>'PlantsController@index']);
Route::get('admin/plant/data', ['as'=>'plants.data', 'uses'=>'PlantsController@data']);
Route::get('admin/plant/create', ['as'=>'plants.create', 'uses'=>'PlantsController@create']);
Route::post('admin/plant/store', ['as'=>'plants.store', 'uses'=>'PlantsController@store']);
Route::get('admin/plant/edit/{plant}', ['as'=>'plants.edit', 'uses'=>'PlantsController@edit']);
Route::patch('admin/plant/edit/{plant}', ['as'=>'plants.update', 'uses'=>'PlantsController@update']);
Route::post('admin/plant/delete', ['as'=>'plants.delete', 'uses'=>'PlantsController@delete']);
// resources
Route::get('admin/resources/list', ['as'=>'resources.list', 'uses'=>'ResourcesController@index']);
Route::get('admin/resources/data', ['as'=>'resources.data', 'uses'=>'ResourcesController@data']);
Route::get('admin/resources/create', ['as'=>'resources.create', 'uses'=>'ResourcesController@create']);
Route::post('admin/resources/store', ['as'=>'resources.store', 'uses'=>'ResourcesController@store']);
Route::get('admin/resources/edit/{resource}', ['as'=>'resources.edit', 'uses'=>'ResourcesController@edit']);
Route::patch('admin/resources/edit/{resource}', ['as'=>'resources.update', 'uses'=>'ResourcesController@update']);
Route::post('admin/resources/delete', ['as'=>'resources.delete', 'uses'=>'ResourcesController@delete']);
// customers
Route::get('admin/customers/list', ['as'=>'customers.list', 'uses'=>'CustomersController@index']);
Route::get('admin/customers/data', ['as'=>'customers.data', 'uses'=>'CustomersController@data']);
Route::get('admin/customers/create', ['as'=>'customers.create', 'uses'=>'CustomersController@create']);
Route::post('admin/customers/store', ['as'=>'customers.store', 'uses'=>'CustomersController@store']);
Route::get('admin/customers/edit/{id}', ['as'=>'customers.edit', 'uses'=>'CustomersController@edit']);
Route::patch('admin/customers/edit/{id}', ['as'=>'customers.update', 'uses'=>'CustomersController@update']);
Route::post('admin/customers/delete', ['as'=>'customers.delete', 'uses'=>'CustomersController@delete']);
// customer sein
Route::get('admin/sein/c_list', ['as'=>'customer_sein.list', 'uses'=>'SeinController@c_index']);
Route::get('admin/sein/c_data', ['as'=>'customer_sein.data', 'uses'=>'SeinController@c_data']);
Route::get('admin/sein/c_create', ['as'=>'customer_sein.create', 'uses'=>'SeinController@c_create']);
Route::post('admin/sein/c_store', ['as'=>'customer_sein.store', 'uses'=>'SeinController@c_store']);
Route::get('admin/sein/c_edit/{id}', ['as'=>'customer_sein.edit', 'uses'=>'SeinController@c_edit']);
//resource sein 
Route::get('admin/sein/r_list', ['as'=>'resource_sein.list', 'uses'=>'SeinController@r_index']);
Route::get('admin/sein/r_data', ['as'=>'resource_sein.data', 'uses'=>'SeinController@r_data']);
Route::get('admin/sein/r_create', ['as'=>'resource_sein.create', 'uses'=>'SeinController@r_create']);
Route::post('admin/sein/r_store', ['as'=>'resource_sein.store', 'uses'=>'SeinController@r_store']);
Route::get('admin/sein/r_edit/{id}', ['as'=>'resource_sein.edit', 'uses'=>'SeinController@r_edit']);

// resource lookup
Route::get('admin/resource_lookup/list', ['as'=>'resource_lookup.admin.list', 'uses'=>'ResourcesLookupController@resourceLookupAdmin']);
Route::get('admin/resource_lookup/data', ['as'=>'resource_lookup.admin.data', 'uses'=>'ResourcesLookupController@data']);
Route::get('admin/resource_lookup/create', ['as'=>'resource_lookup.admin.create', 'uses'=>'ResourcesLookupController@create']);
Route::post('admin/resource_lookup/store', ['as'=>'resource_lookup.admin.store', 'uses'=>'ResourcesLookupController@store']);
Route::get('admin/resource_lookup/edit/{id}', ['as'=>'resource_lookup.admin.edit', 'uses'=>'ResourcesLookupController@edit']);
Route::patch('admin/resource_lookup/edit/{plant}', ['as'=>'resource_lookup.admin.update', 'uses'=>'ResourcesLookupController@update']);
Route::post('admin/resource_lookup/delete', ['as'=>'resource_lookup.admin.delete', 'uses'=>'ResourcesLookupController@delete']);


// ip tables
Route::get('admin/ip_tables/index', ['as'=>'ip_tables.index', 'uses'=>'IpTablesController@index']);
Route::post('admin/ip_tables/store', ['as'=>'ip_tables.store', 'uses'=>'IpTablesController@store']);
Route::get('admin/ip_tables/create', ['as'=>'ip_tables.create', 'uses'=>'IpTablesController@create']);
Route::post('admin/ip_tables/save', ['as'=>'ip_tables.save', 'uses'=>'IpTablesController@save']);


// nominations
Route::get('nomination', ['as'=>'nomination.index', 'uses'=>'NominationsController@index']);
Route::get('nomination/day_ahead', ['as'=>'nomination.day_ahead', 'uses'=>'NominationsController@day_ahead']);
Route::post('nomination/day_ahead/data', ['as'=>'nomination.day_ahead.data', 'uses'=>'NominationsController@day_ahead_data']);
Route::post('nomination/day_ahead/store', ['as'=>'nomination.day_ahead.store', 'uses'=>'NominationsController@day_ahead_store']);
Route::get('nomination/week_ahead', ['as'=>'nomination.week_ahead', 'uses'=>'NominationsController@week_ahead']);
Route::post('nomination/week_ahead/data', ['as'=>'nomination.week_ahead.data', 'uses'=>'NominationsController@week_ahead_data']);
Route::post('nomination/week_ahead/store', ['as'=>'nomination.week_ahead.store', 'uses'=>'NominationsController@week_ahead_store']);
Route::get('nomination/month_ahead', ['as'=>'nomination.month_ahead', 'uses'=>'NominationsController@month_ahead']);
Route::post('nomination/month_ahead/data', ['as'=>'nomination.month_ahead.data', 'uses'=>'NominationsController@month_ahead_data']);
Route::post('nomination/month_ahead/store', ['as'=>'nomination.month_ahead.store', 'uses'=>'NominationsController@month_ahead_store']);

Route::get('nomination/template', ['as'=>'nomination.template', 'uses'=>'NominationsController@template']);
Route::post('nomination/dan/upload', ['as'=>'nomination.dan.upload', 'uses'=>'NominationsController@day_ahead_upload']);
Route::post('nomination/wan/upload', ['as'=>'nomination.wan.upload', 'uses'=>'NominationsController@week_ahead_upload']);
Route::post('nomination/man/upload', ['as'=>'nomination.man.upload', 'uses'=>'NominationsController@month_ahead_upload']);
Route::get('nomination/template/file', ['as'=>'nomination.template.file', 'uses'=>'NominationsController@file_template']); 


## transaction report
Route::get('nomination/transactions', ['as'=>'nomination.transactions',
	 'uses'=>'NominationsReportController@transactions']);
Route::post('nomination/transactions/data', ['as'=>'nomination.transactions.data', 'uses'=>'NominationsReportController@transactions_data']);

## running report
Route::get('nomination/running_report', ['as'=>'nomination.running_report',
	 'uses'=>'NominationsReportController@running_report']);
Route::post('nomination/running_report/data', ['as'=>'nomination.running_report.data', 'uses'=>'NominationsReportController@running_report_data']);
Route::get('nomination/running_report/file', ['as'=>'nomination.running_report.file', 'uses'=>'NominationsReportController@running_report_excel']); 

## extraction report
Route::get('nomination/extraction', ['as'=>'nomination.extraction_report', 'uses'=>'NominationsReportController@extraction_report']);
Route::post('nomination/extraction_report/data', ['as'=>'nomination.extraction_report.data', 'uses'=>'NominationsReportController@extraction_report_data']);
Route::get('nomination/extraction_report/file', ['as'=>'nomination.extraction_report.file', 'uses'=>'NominationsReportController@extraction_report_file']); 

## override 
Route::get('nomination/override', ['as'=>'nomination.override', 'uses'=>'NominationsController@override']);


// common sein functions
Route::patch('admin/sein/edit/{id}', ['as'=>'sein.update', 'uses'=>'SeinController@update']);
Route::post('admin/sein/delete', ['as'=>'sein.delete', 'uses'=>'SeinController@delete']);
//Main Dashboard
Route::get('dashboard', ['as'=>'dashboard', 'uses'=>'DashboardController@dashboard']);
Route::post('dashboard/rtd_sched', ['as'=>'dashboard.get_rtd_sched', 'uses'=>'DashboardController@dashboard_rtd_sched']);
Route::post('dashboard/ticker_data', ['as'=>'dashboard.get_ticker_data', 'uses'=>'DashboardController@dashboard_ticker_data']);
Route::post('dashboard/dap_prices_data', ['as'=>'dashboard.get_dap_prices_data', 'uses'=>'DashboardController@dashboard_dap_prices_data']);
Route::post('dashboard/dap_schedules_data', ['as'=>'dashboard.get_dap_schedules_data', 'uses'=>'DashboardController@dashboard_dap_schedules_data']);
Route::post('dashboard/hap_lmp_data', ['as'=>'dashboard.get_hap_lmp_data', 'uses'=>'DashboardController@dashboard_hap_lmp_data']);
Route::post('dashboard/hap_schedules_data', ['as'=>'dashboard.get_hap_schedules_data', 'uses'=>'DashboardController@dashboard_hap_schedules_data']);
Route::post('dashboard/actual_load_data', ['as'=>'dashboard.get_actual_load_data', 'uses'=>'DashboardController@dashboard_actual_load']);

//Manage Dashboard
Route::get('admin/dashboard/manage', ['as'=>'dashboard.manage','uses'=>'DashboardController@manage_dashboard']);
Route::post('admin/dashboard/manage', ['as'=>'dashboard.manage.store','uses'=>'DashboardController@manage_dashboard_store']);
Route::post('admin/dashboard/role_widgets', ['as'=>'dashboard.role_widgets','uses'=>'DashboardController@role_widgets']);
// Dashboard Settings
Route::get('settings/dashboard', ['as'=>'dashboard.settings','uses'=>'DashboardController@dashboard_settings']);
Route::post('settings/dashboard', ['as'=>'dashboard.settings.store','uses'=>'DashboardController@dashboard_settings_store']);
Route::post('settings/user_widgets', ['as'=>'dashboard.user_widgets','uses'=>'DashboardController@user_widgets']);


Route::get('buyer', ['as'=>'buyer.index','uses'=>'BuyerController@index']);
Route::get('admin', ['as'=>'admin.index','uses'=>'AdminController@index']);
Route::get('admin/privileges', ['as'=>'privilege.index','uses'=>'PrivilegeController@index']);
//Route::get('users', ['as'=>'users.index','uses'=>'UsersController@index','middleware'=>['permission:users-read']]);

//2fa
Route::get('/2fa/enable', 'Google2FAController@enableTwoFactor');
Route::get('/2fa/disable', 'Google2FAController@disableTwoFactor');
Route::get('/2fa/validate', 'Auth\LoginController@getValidateToken');
//Route::post('/2fa/validate', ['middleware' => 'throttle:5', 'uses' => 'Auth\LoginController@postValidateToken']);
Route::post('/2fa/validate', 'Auth\LoginController@postValidateToken');

// change password
Route::get('password/form', ['as'=>'password.form', 'uses'=>'ChangePasswordController@form']);
Route::post('password/submit', ['as'=>'password.submit', 'uses'=>'ChangePasswordController@change']);

//settings
Route::get('settings/2fa', 'SettingsController@twofa');

// Plant Capability
Route::get('plant_capability/realtime/list', ['as'=>'realtime_plant_capability.list', 'uses'=>'PlantCapabilityController@realtimeIndex']); // Realtime
Route::get('/plant_capability/dayahead/list', ['as'=>'plant_capability.day_ahead_list','uses'=>'PlantCapabilityController@dayaheadIndex']); // Day Ahead
Route::post('/plant_capability/dayahead/upload','PlantCapabilityController@dayAheadUploadTemplate');
Route::post('plant_capability/store', ['as' => 'plant_capability.store', 'uses'=>'PlantCapabilityController@store']); // Store Data for Realtime and Day Ahead
Route::post('plant_capability/retrieve', ['as' => 'plant_capability.retrieve', 'uses'=>'PlantCapabilityController@retrieve']); // Store Data for Realtime and Day Ahead
Route::get('/plant_capability/weekahead/list', ['as'=>'plant_capability.week_ahead_list','uses'=>'PlantCapabilityController@weakaheadIndex']); // Week Ahead
Route::post('/plant_capability/weekahead/upload','PlantCapabilityController@weekAheadUploadTemplate'); // week ahead upload
Route::get('/plant_capability/templates/',['as'=>'plant_capability.templates','uses'=>'PlantCapabilityController@templates']); 
Route::post('/plant_capability/templates/download',['as' => 'plant_capability.download_template','uses'=>'PlantCapabilityController@downloadTemplate']); 
// For Unit dropdown
Route::post('/resources/list_by_plant_id', ['uses'=>'ResourcesController@list_resources_by_plant_id']);




// Trading / Shift Reports menu
Route::get('trading/shift_report/index', ['as'=>'trading_shift_report.list', 'uses'=>'ShiftReportsController@tradingList']); // Trading Shift Report index

// ## Trading Shift Report
Route::post('trading_shift_report/store', ['as' => 'trading_shift_report.store', 'uses'=>'ShiftReportsController@tradingStore']); // Store Data for Trading Shift Report
Route::post('trading_shift_report/transactions',['as' => 'trading_shift_report.transactions', 'uses'=>'ShiftReportsController@transactions']);
Route::post('trading_shift_report/retrieve', ['as' => 'trading_shift_report.retrieve', 'uses'=>'ShiftReportsController@tradingRetrieve']); // Retrieve Trading Shift Report Data

Route::post('trading_shift_report_type/list', ['as' => 'trading_shift_report_type.list', 'uses'=>'TradingShiftReportTypeController@listAll']); // Retrieve Trading Shift REport Types


//TRADING OFFERS
Route::group(['prefix' => 'bids_and_offers'],function () {
	Route::get('scheduled_offer', ['as'=>'scheduled_offer', 'uses'=>'OffersController@scheduledOfferindex']);
	Route::get('energy_offer', ['as'=>'energy_offer', 'uses'=>'OffersController@energyOfferIndex']);
	Route::get('standing_offer', ['as'=>'standing_offer', 'uses'=>'OffersController@standingIndex']);
	Route::get('day_ahead_reserve', ['as'=>'day_ahead_reserve', 'uses'=>'OffersController@dayAheadReserveindex']);
	Route::get('standing_reserve/list', ['as'=>'standing_reserve', 'uses'=>'OffersController@standingReserveindex']);
	Route::get('offer_summary', ['as'=>'offer_summary', 'uses'=>'OffersController@summaryIndex']);
	Route::get('offer_templates', ['as'=>'offer_templates', 'uses'=>'OffersController@templatesIndex']);

	Route::post('offer_summary_data', ['as'=>'offer_summary.data','uses'=>'OffersController@summaryData']);
	Route::post('offer_info', ['as'=>'offer_summary.info','uses'=>'OffersController@offerInfo']);
	Route::post('energy_offer/upload', ['as'=>'energy_offer.upload', 'uses'=>'OffersController@uploadTemplate']); 
	Route::post('offer_content', ['uses'=>'OffersController@convert']);
	Route::post('submit_offer', ['uses'=>'OffersController@submitOffer']); 
	Route::post('retrieve_offer', ['uses'=>'OffersController@retrieveOffer']);
	Route::get('offer_templates/download', ['as'=>'offer_templates.download', 'uses'=>'OffersController@downloadTemplate']);
	Route::get('offer_summary/download', ['as'=>'offer_summary.download', 'uses'=>'OffersController@downloadOfferInfo']);
});


// MMS DATA 
Route::get('mms_data/dap_schedules', ['as'=>'dap_schedules.list', 'uses'=>'DapSchedulesController@index']);
Route::post('mms_data/dap_schedules/retrieve', ['as'=>'dap_schedules.retrieve', 'uses'=>'DapSchedulesController@retrieve']);
Route::get('mms_data/dap_schedules/file', ['as'=>'dap_schedules.file', 'uses'=>'DapSchedulesController@file']); 

## RTD Schedules
Route::get('mms_data/rtd_schedules/list', ['as'=>'rtd_schedules.list', 'uses'=>'MmsRtdsSchedulesController@mmsReportIndex']);
Route::post('mms_data/rtd_schedules/retrieve', ['as'=>'rtd_schedules.retrieve', 'uses'=>'MmsRtdsSchedulesController@retrieve']);
Route::get('mms_data/rtd_schedules/file', ['as'=>'rtd_schedules.file', 'uses'=>'MmsRtdsSchedulesController@file']); 

## System Messages
Route::get('mms_data/system_messages/list', ['as'=>'system_messages.list', 'uses'=>'MmsSystemMessagesController@mmsReportIndex']);
Route::get('mms_data/system_messages/data', ['as'=>'system_messages.data', 'uses'=>'MmsSystemMessagesController@data']); // 
Route::get('mms_data/system_messages/file', ['as'=>'system_messages.file', 'uses'=>'MmsSystemMessagesController@file']);


## Locational Marginal Prices (LMP)
Route::get('mms_data/lmp/list', ['as'=>'lmp.list', 'uses'=>'MmsRTDPricesController@mmsReportIndex']);
Route::post('mms_data/lmp/retrieve', ['as'=>'lmp.retrieve', 'uses'=>'MmsRTDPricesController@retrieve']);
Route::get('mms_data/lmp/file', ['as'=>'lmp.file', 'uses'=>'MmsRTDPricesController@file']); 


## Reserve Schedules
Route::get('mms_data/reserve_schedules/list', ['as'=>'reserve_schedules.list', 'uses'=>'MmsReserveRtdSchedulesController@mmsReportIndex']);
Route::post('mms_data/reserve_schedules/retrieve', ['as'=>'reserve_schedules.retrieve', 'uses'=>'MmsReserveRtdSchedulesController@retrieve']);
Route::get('mms_data/reserve_schedules/file', ['as'=>'reserve_schedules.file', 'uses'=>'MmsReserveRtdSchedulesController@file']); //


## Reserve Prices
Route::get('mms_data/reserve_prices/list', ['as'=>'reserve_prices.list', 'uses'=>'MmsReserveRtdPricesController@mmsReportIndex']);
Route::post('mms_data/reserve_prices/retrieve', ['as'=>'reserve_prices.retrieve', 'uses'=>'MmsReserveRtdPricesController@retrieve']);
Route::get('mms_data/reserve_prices/file', ['as'=>'reserve_prices.file', 'uses'=>'MmsReserveRtdPricesController@file']); //


## HAP Prices and Schedules
Route::get('mms_data/hap_prices_and_sched/list', ['as'=>'hap_prices_and_sched.list', 'uses'=>'MmsHapPricesAndSchedulesController@mmsReportIndex']);
Route::post('mms_data/hap_prices_and_sched/retrieve', ['as'=>'hap_prices_and_sched.retrieve', 'uses'=>'MmsHapPricesAndSchedulesController@retrieve']);
Route::get('mms_data/hap_prices_and_sched/file', ['as'=>'rtd_schedules.file', 'uses'=>'MmsHapPricesAndSchedulesController@file']); 


Route::post('reserve_resources_lookup/list', ['as'=>'reserve_resources_lookup.list', 'uses'=>'MmsReserveRtdSchedulesController@resourceList']);
Route::post('resources_lookup/list', ['as'=>'resources_lookup.list', 'uses'=>'ResourcesLookupController@list']);



// ### Plant Shift Report ( under Trading Menu )
Route::get('trading/shift_report/plantIndex', ['as'=>'plant_shift_report.list', 'uses'=>'ShiftReportsController@plantList']); // TRading / Plant Shift Report list

Route::post('plant/shift_report/retrieve', ['as' => 'plant_shift_report.retrieve', 'uses'=>'ShiftReportsController@plantRetrieve']); // Retrieve Plant Shift Report Data

Route::post('plant/shift_report/transactions',['as' => 'plant_shift_report.transactions', 'uses'=>'ShiftReportsController@transactions']);
Route::post('plant_shift_report_type/list', ['as' => 'plant_shift_report_type.list', 'uses'=>'PlantShiftReportTypeController@listAll']); // Retrieve Plant Shift REport Types

Route::post('plant/shift_report/store', ['as' => 'plant_shift_report.store', 'uses'=>'PlantShiftReportController@store']); // Store Data for Trading Shift Report


Route::get('plant_operations', ['as'=>'plant_operations.index','uses'=>'PlantOperationsController@index']);
Route::get('plant/shift_report/index', ['as'=>'plant_shift_report.index', 'uses'=>'PlantShiftReportController@plantOpsIndex']); 
Route::post('plant/shift_report/storeIM', ['as' => 'plant_shift_report.storeIM', 'uses'=>'PlantShiftReportController@storeIslandMode']); // Retrieve Plant Shift Report Data
Route::post('plant/shift_report/operatorList', ['as' => 'plant_shift_report.operatorList', 'uses'=>'PlantShiftReportController@listPlantOperators']); // Retrieve Plant Shift Report Data



// ##### Realtime Plant Monitoring Modules ##### //
Route::get('realtime_plant_monitoring/plantList', ['as' => 'realtime_plant_monitoring.plantList', 'uses'=>'RealtimePlantMonitoringController@plantIndex']);  // for Plant / Realtime Plant monitoring

// ##### Realtime Plant Monitoring Modules ##### //
Route::post('realtime_plant_monitoring/retrieve', ['as' => 'realtime_plant_monitoring.retrieveRealtimeData', 'uses'=>'RealtimePlantMonitoringController@retrieve']);  

Route::post('realtime_plant_monitoring/storeIM', ['as' => 'realtime_plant_monitoring.storeIM', 'uses'=>'RealtimePlantMonitoringController@storeIslandMode']);  

Route::post('realtime_plant_monitoring/storeASPANom', ['as' => 'realtime_plant_monitoring.storeASPANom', 'uses'=>'RealtimePlantMonitoringController@storeASPANomination']);  

Route::post('realtime_plant_monitoring/storeActualLoad', ['as' => 'realtime_plant_monitoring.storeActualLoad', 'uses'=>'RealtimePlantMonitoringController@storeActualLoad']);  

Route::post('realtime_plant_monitoring/acknowledgeRTD', ['as' => 'realtime_plant_monitoring.acknowledgeRTD', 'uses'=>'RealtimePlantMonitoringController@acknowledgeRTD']);  

Route::post('realtime_plant_monitoring/acknowledgeAL', ['as' => 'realtime_plant_monitoring.acknowledgeAL', 'uses'=>'RealtimePlantMonitoringController@acknowledgeAL']);  




Route::post('realtime_plant_monitoring/retrievePlantShiftReport', ['as' => 'realtime_plant_monitoring.retrievePlantShiftReport', 'uses'=>'RealtimePlantMonitoringController@retrievePlantShiftReport']);  



// ### Trading / Realtime Plant Monitoring
Route::get('realtime_plant_monitoring/tradingList', ['as' => 'realtime_plant_monitoring.tradingList', 'uses'=>'RealtimePlantMonitoringController@tradingIndex']); 


Route::post('realtime_plant_monitoring/retrieveTradingShiftReport', ['as' => 'realtime_plant_monitoring.retrieveTradingShiftReport', 'uses'=>'RealtimePlantMonitoringController@retrieveTradingShiftReport']);  



// ### Shift Report Extraction
Route::get('trading/shift_report/extractionIndex', ['as'=>'shift_report.extraction', 'uses'=>'ShiftReportsController@extractionIndex']); // 

Route::post('trading/shift_report/extractCheckData', ['as'=>'shift_report.extraction_data', 'uses'=>'ShiftReportsController@extractionCheckData']); // 

Route::get('trading/shift_report/extractFile', ['as'=>'shift_report.extraction_download', 'uses'=>'ShiftReportsController@extractionFile']); //




### Plant Availability
Route::get('trading/availability_report/list', ['as'=>'availability_report.list', 'uses'=>'PlantCapabilityReportsController@tradingIndex']); // 
Route::get('trading/availability_report/data', ['as'=>'availability_report.data', 'uses'=>'PlantCapabilityReportsController@data']); // 

Route::get('trading/availability_report/file', ['as'=>'availability_report.file', 'uses'=>'PlantCapabilityReportsController@file']); // 

## For Reserve Capability 
Route::get('reserve/capability/create', ['as'=>'reserve_capability.create', 'uses'=>'ReserveCapabilityController@create']);
Route::post('reserve/capability/listByDate', ['as'=>'reserve_capability.listByDate', 'uses'=>'ReserveCapabilityController@listByDate']);
Route::post('reserve/capability/save', ['as'=>'reserve_capability.save', 'uses'=>'ReserveCapabilityController@save']);
Route::get('reserve/capability/list', ['as'=>'reserve_capability.list', 'uses'=>'ReserveCapabilityController@list']);
Route::post('reserve/capability/generateFileLink', ['as'=>'reserve_capability.generateFileLink', 'uses'=>'ReserveCapabilityController@generateFileLink']);
Route::get('reserve/capability/file', ['as'=>'reserve_capability.file', 'uses'=>'ReserveCapabilityController@fileCsv']);


## For Reserve Nomination 
Route::get('reserve/nomination/create', ['as'=>'reserve_nomination.create', 'uses'=>'ReserveNominationController@create']);
Route::post('reserve/nomination/listByDate', ['as'=>'reserve_nomination.listByDate', 'uses'=>'ReserveNominationController@listByDate']);
Route::post('reserve/nomination/save', ['as'=>'reserve_nomination.save', 'uses'=>'ReserveNominationController@save']);

Route::get('reserve/nomination/list', ['as'=>'reserve_nomination.list', 'uses'=>'ReserveNominationController@list']);
Route::post('reserve/nomination/generateFileLink', ['as'=>'reserve_nomination.generateFileLink', 'uses'=>'ReserveNominationController@generateFileLink']);
Route::get('reserve/nomination/file', ['as'=>'reserve_nomination.file', 'uses'=>'ReserveNominationController@fileCsv']);



## For Reserve Schedules
Route::get('reserve/schedule/create', ['as'=>'reserve_schedule.create', 'uses'=>'ReserveScheduleController@create']);
Route::post('reserve/schedule/listByDate', ['as'=>'reserve_schedule.listByDate', 'uses'=>'ReserveScheduleController@listByDate']);
Route::post('plant/get', ['as'=>'plant.get', 'uses'=>'ReserveCapabilityController@get_plant_data']);

Route::get('reserve/schedule/list', ['as'=>'reserve_schedule.list', 'uses'=>'ReserveScheduleController@list']);
Route::post('reserve/schedule/generateFileLink', ['as'=>'reserve_schedule.generateFileLink', 'uses'=>'ReserveScheduleController@generateFileLink']);
Route::get('reserve/schedule/file', ['as'=>'reserve_schedule.file', 'uses'=>'ReserveScheduleController@fileCsv']);


## BCQ Modules
Route::get('bcq/uploader', ['as'=>'bcq.uploader.index', 'uses'=>'BcqController@bcqUploader']);
Route::get('bcq/report', ['as'=>'bcq.report.index', 'uses'=>'BcqController@bcqReport']);


### Meter Data
Route::get('meter_data/mq_load', ['as'=>'meter_data.mq_load.index', 'uses'=>'MeterDataController@dailyMqLoad']);
Route::get('meter_data/mq_gen', ['as'=>'meter_data.mq_gen.index', 'uses'=>'MeterDataController@dailyMqGen']);

## TRading 
Route::get('trading', ['as'=>'trading.index', 'uses'=>'TradingController@index']);

#### ASPA Nomination
Route::get('aspa_nomination/input', ['as'=>'aspa_nomination.input', 'uses'=>'ASPANominationsController@input']);
Route::post('aspa_nomination/upload', ['as'=>'aspa_nomination.upload', 'uses'=>'ASPANominationsController@upload']);
Route::post('aspa_nomination/data', ['as'=>'aspa_nomination.data', 'uses'=>'ASPANominationsController@data']);
Route::post('aspa_nomination/store', ['as'=>'aspa_nomination.store', 'uses'=>'ASPANominationsController@store']);
Route::get('aspa_nomination/template', ['as'=>'aspa_nomination.template', 'uses'=>'ASPANominationsController@template']);


### ASPA Nomination View
Route::get('aspa_nomination/view', ['as'=>'aspa_nomination.view', 'uses'=>'ASPANominationsController@view']);
Route::post('aspa_nomination/data_bydaterange', ['as'=>'aspa_nomination.data_bydaterange', 'uses'=>'ASPANominationsController@data_bydaterange']);
Route::get('aspa_nomination/view/file', ['as'=>'aspa_nomination.view.file', 'uses'=>'ASPANominationsController@file']);




### Manual Downloaders
Route::get('manual_downloader/rtd_lmp', ['as'=>'manual_downloader.rtd_lmp.index', 'uses'=>'ManualDownloaderController@rtdLmp']);
Route::get('manual_downloader/rtd_resource', ['as'=>'manual_downloader.rtd_resource_specific.index', 'uses'=>'ManualDownloaderController@rtdResourceSpecific']);
Route::get('manual_downloader/mpd_lmp', ['as'=>'manual_downloader.mpd_lmp.index', 'uses'=>'ManualDownloaderController@mpdLmp']);

### FOR REALTIME DATA PARSERS ###
Route::get('/pubsub/offer_status/{participant}/{bid_id}',['uses'=>'PubSubController@parseStatus']);
Route::get('/pubsub/retrieve_status/{participant}/{bid_id}/{resource}',['uses'=>'PubSubController@retrieveOfferStatus']);
### MINER ERROR LOGS ##
Route::get('logs/rtd_log',['uses' => 'MinerLogs@rtdLog']);
Route::get('logs/lmp_log',['uses' => 'MinerLogs@lmpLog']);
Route::get('logs/lara_log',['uses' => 'MinerLogs@laraLog']);
Route::get('logs/submit_xml',['uses' => 'MinerLogs@submit_xml']);


### COMMON/GLOBAL ###
Route::get('common/get_time',['as' => 'common.get_time', 'uses' => 'OffersController@getServerTime']);