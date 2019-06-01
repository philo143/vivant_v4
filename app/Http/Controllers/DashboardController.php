<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Widgets;
use App\Role;
use App\RoleWidgets;
use App\UserWidget;
use App\Resource;
use App\UserPlant;
use App\Plant;
use App\Zones;
use Illuminate\Support\Facades\Artisan;
use App\Events\UserSignedUp;
use App\ResourcesLookup;
use \Carbon\Carbon;
use App\MmsMpdDapLmp;
use App\MmsDapPriceAndSchedule;
use App\MmsMpdHapLmp;
use App\MmsHapPriceAndSchedule;
use App\RTPMActualLoad;
use App\MmsModRtd;
use App\MmsModLmp;

use DB;
class DashboardController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
    } //


    public function dashboard(Request $request) 
	{

        // for weather
        $weather_obj = $this->list_weather_data();
        $weather = $weather_obj['weather'];
        $icons = $weather_obj['icons'];

        // nodal price 
        $zones = $this->zones();
        $nodal_price_obj = $this->nodal_price();
        $nodal_prices_data = $nodal_price_obj['data'];
        $interval = $nodal_price_obj['interval_data'];

        $user = auth()->user();
    	$role_widgets = RoleWidgets::where('roles_id',$user->roles[0]->id)->get()->pluck('widgets_id')->toArray();
    	$user_widgets = UserWidget::where('users_id',$user->id)->whereIn('widgets_id',$role_widgets)->with('widgets','resources')->get();
        
        $server_ip = $request->ip();    

		return view('dashboard.dashboard',compact('user_widgets','rtd_data','weather','icons','zones','nodal_prices_data','server_ip'))->with(['interval'=>$interval]);

	}
    public function manage_dashboard(){
    	$widgets = Widgets::where('is_active',1)->get();
    	$roles = Role::all();
    	return view('dashboard.manage',compact('widgets','roles'));
    }
    public function manage_dashboard_store(Role $role){
    	$selected_role = $role->find(request('privilege'));
    	$widgets = request('widgets');
    	RoleWidgets::where('roles_id', $selected_role->id)->delete();
    	if(request('widgets') != null) $this->add_privilege_widgets($selected_role,$widgets);
    	    	
    	return redirect()->route('dashboard.manage')->with('success','Widgets updated successfully');
    }
    private function add_privilege_widgets($role,$widgets){
    	$privilege_widgets_array = array();
        foreach ($widgets as $widget) {
            $privilege_widgets_array[] = array( 
                'widgets_id' => $widget,
                'roles_id' => $role->id
            );
        }
        RoleWidgets::insert($privilege_widgets_array);
    }
    public function role_widgets(){
    	$role_widgets = RoleWidgets::where('roles_id',request('id'))->pluck('widgets_id');
    	return $role_widgets;
    }
    public function dashboard_settings() 
    {
    	$current_role = auth()->user()->roles()->pluck('id');
    	$current_user = auth()->user()->id;
  		
    	$role_widgets = RoleWidgets::where('roles_id',$current_role[0])->with('widgets')->get();
    	$user_plant = UserPlant::where('users_id',$current_user);
    	$resources = Resource::all();

    	if($user_plant->get()->count() > 0){
    		$resources = Plant::where('id',$user_plant->first()->plants_id)->with('resource')->first();
    		$resources = $resources->resource;
    	}

    	return view('settings.dashboard',compact('role_widgets','resources'));
    }
    public function dashboard_settings_store(){
    	$widgets = request('widgets');
    	$widgets_resources = request('widgets_resources');

    	UserWidget::where('users_id', auth()->user()->id)->delete();
    	if(request('widgets_resources') != null) $this->add_user_widgets($widgets,$widgets_resources);
    	    	
    	return redirect()->route('dashboard.settings')->with('success','Widgets updated successfully');
    }
    private function add_user_widgets($widgets,$widgets_resources){
    	$user_widgets_array = array();
        foreach ($widgets as $widget) {
        	if(isset($widgets_resources[$widget])){
        		foreach($widgets_resources[$widget] as $resources => $resource)
		            $user_widgets_array[] = array(
		            	'users_id'	=> auth()->user()->id,
		                'widgets_id' => $widget,
		                'resources_id' => $resource
		            );
        	}else{
        		$user_widgets_array[] = array(
	            	'users_id'	=> auth()->user()->id,
	                'widgets_id' => $widget,
	                'resources_id' => null
	            );
        	}
        }
       	UserWidget::insert($user_widgets_array);
    }
    public function user_widgets(){
    	$user_widgets = UserWidget::where('users_id',auth()->user()->id)->get();
    	return $user_widgets;
    }
    public function dashboard_rtd_sched()
    {
        $dt = Carbon::createFromTimestamp(ceil(time() / 300) * 300); // added subMinutes to match nmms late data. remove this when nmms fixed their data
        $dt_from = Carbon::createFromTimestamp(ceil(time() / 300) * 300)->subMinutes(4); // subtract 4 minutes only. change after nmms fixed their data        
        $resources = UserWidget::whereNotNull('resources_id')->with('resources')->get();
        $data = array();
        foreach($resources as $resource){
            $rtd = MmsModRtd::where(['date'=>Date('Y-m-d'),'interval'=>$dt,'price_node'=>$resource->resources->resource_id])->first();
            $mw = $rtd !== null ? $rtd->mw : null;
            $data[$resource->resources->resource_id] = array(
                'hour' => ($dt->hour+1),
                'interval' => 'Hour '.($dt->hour+1).' (Interval: '.$dt_from->hour .':'.Date('i',strtotime($dt_from)).' - '.$dt->hour.':'.Date('i',strtotime($dt)).'H)', //Used php Date, Carbon has no leading zeros for minutes below 10
                'mw' => $mw == null ? '--': round($mw, 1),
                'plus' => $mw == null ? '--': round($mw + ($mw * 1.5/100),1),
                'minus' => $mw == null ? '--': round($mw - ($mw * 3/100),1),
                'current' => Carbon::now()
            );
        }
        return $data;
    }   



    private function weather_build_base_string($baseURI, $method, $params) {
        $r = array();
        ksort($params);
        foreach($params as $key => $value) {
            $r[] = "$key=" . rawurlencode($value);
        }
        return $method . "&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r));
    }

    private function weather_build_authorization_header($oauth) {
        $r = 'Authorization: OAuth ';
        $values = array();
        foreach($oauth as $key=>$value) {
            $values[] = "$key=\"" . rawurlencode($value) . "\"";
        }
        $r .= implode(', ', $values);
        return $r;
    }

    private function list_weather_data(){
        $cities = array();
        $cities[] = '1199477';
        $cities[] = '1199079';
        $cities[] = '1198888';
        $cities[] = '91464848';
        $cities[] = '1199136';
        $cities[] = '2346685';

        $weather_data = array();

        // from v3 weather
        $url = 'https://weather-ydn-yql.media.yahoo.com/forecastrss';
        $app_id = 'ivvgBz30d';
        $consumer_key = 'dj0yJmk9c0d6MWpkbGZPMWFPJnM9Y29uc3VtZXJzZWNyZXQmc3Y9MCZ4PTdl';
        $consumer_secret = '3499d1f08409284772ad54494c4a4d262dbde5a7';

        foreach ($cities as $city_key) {
                
            $query = array(
                'woeid' => $city_key,
                'format' => 'json',
                'u' => 'c'
            );


            $oauth = array(
                'oauth_consumer_key' => $consumer_key,
                'oauth_nonce' => uniqid(mt_rand(1, 1000)),
                'oauth_signature_method' => 'HMAC-SHA1',
                'oauth_timestamp' => time(),
                'oauth_version' => '1.0'
            );


            $base_info = $this->weather_build_base_string($url, 'GET', array_merge($query, $oauth));
            $composite_key = rawurlencode($consumer_secret) . '&';
            $oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
            $oauth['oauth_signature'] = $oauth_signature;

            $header = array(
                $this->weather_build_authorization_header($oauth),
                'Yahoo-App-Id: ' . $app_id
            );


            $options = array(
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_HEADER => false,
                CURLOPT_URL => $url . '?' . http_build_query($query),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false
            );
            $ch = curl_init();
            curl_setopt_array($ch, $options);
            $response = curl_exec($ch);
            curl_close($ch);
            $yw_channel= json_decode($response);

            $atmosphere = $yw_channel->current_observation->atmosphere;
            $astronomy= $yw_channel->current_observation->astronomy;

            $city = ucfirst((string) $yw_channel->location->city);
            $humidity = (string) $atmosphere->humidity;
            $visibility = (string) $atmosphere->visibility;
            $pressure = (string) $atmosphere->pressure;
            $sunrise = (string) $astronomy->sunrise;
            $sunset = (string) $astronomy->sunset;
            $yw_forecast = $yw_channel->current_observation;
          
            $weather_data[$city] = array(
                'humidity'      => $humidity,
                'visibility'    => $visibility,
                'pressure'      => $pressure,
                'sunrise'       => $sunrise,
                'sunset'        => $sunset,
                'condition'     => $yw_forecast->condition,
                'forecast'      => $yw_channel->forecasts,
                'date'          =>$yw_forecast->pubDate
            ); 

            // $url = "https://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20weather.forecast%20where%20u=%27c%27%20and%20woeid=$city_key%20&format=json";
            // // Make call with cURL
            // $session = curl_init($url);
            // curl_setopt($session, CURLOPT_RETURNTRANSFER,true);
            // $json = curl_exec($session);
            // $yw_obj =  json_decode($json);

            // $yw_channel = $yw_obj->query->results->channel;
            // $city = (string) $yw_channel->location->city;
            // $humidity = (string) $yw_channel->atmosphere->humidity;
            // $visibility = (string) $yw_channel->atmosphere->visibility;
            // $pressure = (string) $yw_channel->atmosphere->pressure;
            // $sunrise = (string) $yw_channel->astronomy->sunrise;
            // $sunset = (string) $yw_channel->astronomy->sunset;
            // $yw_forecast = $yw_channel->item;

            // $weather_data[$city] = array(
            //     'humidity'      => $humidity,
            //     'visibility'    => $visibility,
            //     'pressure'      => $pressure,
            //     'sunrise'       => $sunrise,
            //     'sunset'        => $sunset,
            //     'condition'     => $yw_forecast->condition,
            //     'forecast'      => $yw_forecast->forecast
            // );
        } // foreach


        $icons[0]   = 'wi-tornado';
        $icons[1]   = 'wi-storm-showers';
        $icons[2]   = 'wi-tornado';
        $icons[3]   = 'wi-thunderstorm';
        $icons[4]   = 'wi-thunderstorm';
        $icons[5]   = 'wi-rain-mix';
        $icons[6]   = 'wi-rain-mix';
        $icons[7]   = 'wi-snow';
        $icons[8]   = 'wi-sprinkle';
        $icons[9]   = 'wi-sprinkle';
        $icons[10]  = 'wi-rain';
        $icons[11]  = 'wi-showers';
        $icons[12]  = 'wi-showers';
        $icons[13]  = 'wi-snow';
        $icons[14]  = 'wi-snow';
        $icons[15]  = 'wi-snow';
        $icons[16]  = 'wi-snow';
        $icons[17]  = 'wi-hail';
        $icons[18]  = 'wi-rain-mix';
        $icons[19]  = 'wi-fog';
        $icons[20]  = 'wi-fog';
        $icons[21]  = 'wi-fog';
        $icons[22]  = 'wi-fog';
        $icons[23]  = 'wi-cloudy-gusts';
        $icons[24]  = 'wi-cloudy-windy';
        $icons[25]  = 'wi-cloudy-gusts';
        $icons[26]  = 'wi-cloudy';
        $icons[27]  = 'wi-night-cloudy';
        $icons[28]  = 'wi-day-cloudy';
        $icons[29]  = 'wi-night-cloudy';
        $icons[30]  = 'wi-day-cloudy';
        $icons[31]  = 'wi-night-clear';
        $icons[32]  = 'wi-day-sunny';
        $icons[33]  = 'wi-day-sunny-overcast';
        $icons[34]  = 'wi-day-sunny';
        $icons[35]  = 'wi-rain-mix';
        $icons[36]  = 'wi-day-sunny';
        $icons[37]  = 'wi-thunderstorm';
        $icons[38]  = 'wi-thunderstorm';
        $icons[39]  = 'wi-thunderstorm';
        $icons[40]  = 'wi-showers';
        $icons[41]  = 'wi-snow';
        $icons[42]  = 'wi-snow';
        $icons[43]  = 'wi-snow';
        $icons[44]  = 'wi-day-cloudy';
        $icons[45]  = 'wi-storm-showers';
        $icons[46]  = 'wi-snow';
        $icons[47]  = 'wi-day-thunderstorm';
        $icons[3200]  = 'icon-question';

        return array(
                'weather' => $weather_data,
                'icons' => $icons
            );
    }  // end of weather 


    private function nodal_price(){
        $nodal_prices_data  = array();
        $interval_data = getIntraIntervalDetails();
        $intra_intervals = $interval_data['intra_intervals'];
        
        $user = auth()->user();
        $resources = UserWidget::select('resources.resource_id')
                ->join('resources', 'user_widgets.resources_id', '=', 'resources.id')
                ->where('user_widgets.widgets_id', 3)
                ->where('user_widgets.users_id', $user->id)
                ->get();

        // get resource id name values
        //$resources = Resource::whereIn('id',$resource_ids)->get();
        foreach ($resources as $row) {
             $resource_id = $row->resource_id;
             foreach ($intra_intervals as $intra) {
                $intra_x = explode(' ',$intra);
                $date = $intra_x[0];
                $interval = $intra_x[1];
                $d = MmsModLmp::where(['date'=>$date,'interval'=>$interval,'price_node'=>$resource_id])->first();
                $nodal_prices_data[$resource_id][$intra] = $d['lmp'] == null ? '--' : number_format($d['lmp'],2);
             }

        } // end foreach

        return array(
                'data' => $nodal_prices_data,
                'interval_data' => $interval_data
            );
    }
    private function zones(){
        $zones = Zones::all();
        return $zones;
    }
    public function dashboard_ticker_data()
    {

        $interval = getIntraIntervalDetails();
        $zones = Zones::all();
        $data = array();
        $data = array();
        $di = explode(' ',$interval['intra_intervals'][0]);
        $date = $di[0];
        $int = $di[1];
        foreach($zones as $zone){
            $resources = ResourcesLookup::orWhereRaw('SUBSTRING(resource_id,2) like "'. $zone->zone_prefix.'%"' )->get();      
            foreach ($resources as $resource) {
                 $type = $resource->type;
                 $resource_id = $resource->resource_id;
                 $mw = MmsModLmp::where(['date'=>$date,'interval'=>$int,'price_node'=>$resource_id])->pluck('lmp')->first();
                 $data[$type][$zone->zone_prefix][$resource_id] = $mw !== null ? number_format($mw,2) : '--';
            }
        }
       return $data;
    } 


    public function dashboard_dap_prices_data(Request $request){

        $resorce_ids = explode(',',$request->resource_ids);
        $date_today = Carbon::now()->format('Y-m-d');

        $list = MmsMpdDapLmp::whereIn('price_node',$resorce_ids)
            // ->whereRaw("date(interval_end) = '".$date_today."' ")
            ->whereRaw("hour(interval_end) != 0 ")
            ->where('run_time',function($query) use ($date_today,$resorce_ids){

                $query->select(DB::raw('max(run_time)'))
                      ->from('mms_mpd_dap_lmp')
                      ->whereIn('price_node',$resorce_ids)
                      ->whereRaw("date(interval_end) = '".$date_today."' ")
                      ->whereRaw("hour(interval_end) != 0 ");
            })
            ->get();

        $data = array();
        $total = 0;
        foreach ($list as $row) {
            $hour = date("G",strtotime($row['interval_end']));
            $data[$row->price_node][$hour] = $row;
            $total++;
        }


        ## get next day for interval 24
        $date_tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $list2 = MmsMpdDapLmp::whereIn('price_node',$resorce_ids)
            ->whereRaw("date(interval_end) = '".$date_tomorrow."' ")
            ->whereRaw("hour(interval_end) = 0 ")
            ->where('run_time',function($query) use ($date_today,$resorce_ids){

                $query->select(DB::raw('max(run_time)'))
                      ->from('mms_mpd_dap_lmp')
                      ->whereIn('price_node',$resorce_ids)
                      ->whereRaw("date(interval_end) = '".$date_today."' ")
                      ->whereRaw("hour(interval_end) = 0 ");
            })
            ->get();
        foreach ($list2 as $row) {
            $hour = date("G",strtotime($row['interval_end']));
            $data[$row->price_node][24] = $row;
            $total++;
        }

        return $data;
    } // eof 


    public function dashboard_dap_schedules_data(Request $request){

        $resorce_ids = explode(',',$request->resource_ids);
        $date_today = Carbon::now()->format('Y-m-d');

        $list = MmsDapPriceAndSchedule::whereIn('price_node',$resorce_ids)
            // ->whereRaw("date(interval_end) = '".$date_today."' ")
            ->whereRaw("hour(interval_end) != 0 ")
            ->where('run_time',function($query) use ($date_today,$resorce_ids){

                $query->select(DB::raw('max(run_time)'))
                      ->from('mms_mpd_dap_sched')
                      ->whereIn('price_node',$resorce_ids)
                      ->whereRaw("date(interval_end) = '".$date_today."' ")
                      ->whereRaw("hour(interval_end) != 0 ");
            })
            ->get();

        $data = array();
        $total = 0;
        foreach ($list as $row) {
            $hour = date("G",strtotime($row['interval_end']));
            $data[$row->price_node][$hour] = $row;
            $total++;
        }


        ## get next day for interval 24
        $date_tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $list2 = MmsDapPriceAndSchedule::whereIn('price_node',$resorce_ids)
            ->whereRaw("date(interval_end) = '".$date_tomorrow."' ")
            ->whereRaw("hour(interval_end) = 0 ")
            ->where('run_time',function($query) use ($date_tomorrow,$resorce_ids){

                $query->select(DB::raw('max(run_time)'))
                      ->from('mms_mpd_dap_sched')
                      ->whereIn('price_node',$resorce_ids)
                      ->whereRaw("date(interval_end) = '".$date_tomorrow."' ")
                      ->whereRaw("hour(interval_end) = 0 ");
            })
            ->get();
        foreach ($list2 as $row) {
            $hour = date("G",strtotime($row['interval_end']));
            $data[$row->price_node][24] = $row;
            $total++;
        }

        return $data;
    } // eof 


    public function dashboard_hap_lmp_data(Request $request){
        $resorce_ids = explode(',',$request->resource_ids);
        $dt = Carbon::createFromTimestamp(ceil(time() / 300) * 300);
        $current_hr = $dt->hour+1;
        $date1 = Carbon::now()->format('Y-m-d');
        $list = MmsMpdHapLmp::select(
                DB::raw('ANY_VALUE(price_node) as price_node'),
                DB::raw('ANY_VALUE(interval_end) as interval_end'),
                DB::raw('max(run_time) as run_time'),
                DB::raw('ANY_VALUE(lmp) as lmp') )
            ->whereIn('price_node',$resorce_ids)
            ->whereRaw("date(interval_end) = '".$date1."' ")
            ->whereRaw("hour(interval_end) = ".$current_hr)
            ->groupBy('price_node','interval_end')
            ->get();
        $data = array();
        $total = 0;
        foreach ($list as $row) {
            $hour = date("G",strtotime($row['interval_end']));
            $intra_interval = intval(date("i",strtotime($row['interval_end'])));
            $data[$row->price_node][$hour][$intra_interval] = $row;
            $total++;
        }

        $hours = array($current_hr);
        $return = array(
            'hours' => $hours,
            'data' => $data
        );
        

        return $return;
    } // eof 


    public function dashboard_hap_schedules_data(Request $request){

        $resorce_ids = explode(',',$request->resource_ids);
        $dt = Carbon::createFromTimestamp(ceil(time() / 300) * 300);
        $current_hr = $dt->hour;
        $date1 = Carbon::now()->format('Y-m-d');

        $list = MmsHapPriceAndSchedule::select(
                DB::raw('ANY_VALUE(price_node) as price_node'),
                DB::raw('ANY_VALUE(interval_end) as interval_end'),
                DB::raw('max(run_time) as run_time'),
                DB::raw('ANY_VALUE(lmp) as lmp'),
                DB::raw('ANY_VALUE(mw) as mw')  )
            ->whereIn('price_node',$resorce_ids)
            ->whereRaw("date(interval_end) = '".$date1."' ")
            ->whereRaw("hour(interval_end) = ".$current_hr)
            ->groupBy('price_node','interval_end')
            ->get();
        $data = array();
        $total = 0;
        foreach ($list as $row) {
            $hour = date("G",strtotime($row['interval_end']));
            $intra_interval = intval(date("i",strtotime($row['interval_end'])));
            $data[$row->price_node][$hour][$intra_interval] = $row;
            $total++;
        }

        // get data for 24/00 intrainterval
        $next_hour = $current_hr + 1;
        $date2 = Carbon::now()->format('Y-m-d');
        if ($current_hr == 23) {
            $date2 = Carbon::tomorrow()->format('Y-m-d');
            $next_hour = 0;
        }
        $list2 = MmsHapPriceAndSchedule::select(
                DB::raw('ANY_VALUE(price_node) as price_node'),
                DB::raw('ANY_VALUE(interval_end) as interval_end'),
                DB::raw('max(run_time) as run_time'),
                DB::raw('ANY_VALUE(lmp) as lmp'),
                DB::raw('ANY_VALUE(mw) as mw') )
            ->whereIn('price_node',$resorce_ids)
            ->whereRaw("date(interval_end) = '".$date2."' ")
            ->whereRaw("hour(interval_end) = ".$next_hour)
            ->whereRaw("minute(interval_end) = 0 ")
            ->groupBy('price_node','interval_end')
            ->get();
        foreach ($list2 as $row) {
            $hour = date("G",strtotime($row['interval_end']));
            $intra_interval = intval(date("i",strtotime($row['interval_end'])));
            $data[$row->price_node][$hour][$intra_interval] = $row;
            $total++;
        }


        $hours = array($current_hr);
        $return = array(
            'hours' => $hours,
            'data' => $data,
            'time_now' =>date('H:i:s')
        );
        

        return $return;
    } // eof 


    public function dashboard_actual_load()
    {
        $dt = Carbon::createFromTimestamp(ceil(time() / 300) * 300);
        $dt_from = Carbon::createFromTimestamp(ceil(time() / 300) * 300)->subMinutes(4);        
        
        $user_id = auth()->user()->id;

        ## get resources 
        $resources = UserWidget::where('users_id',$user_id)
            ->with('resources')
            ->where('widgets_id',13)
            ->whereNotNull('resources_id')->get();
        $resource_id_list = array();
        $resources_ids = array();
        foreach ($resources as $res_obj) {
            $resources_ids[] = $res_obj->resources_id;
            $resource_id_list[] = $res_obj->resources->resource_id;
        }
       


        $hour = $dt->hour+1;
        $min = intval(Date('i',strtotime($dt_from))) - 1;
        $min_s = $min - 4;
        $real_hour = $dt->hour;

        $list = RTPMActualLoad::whereIn('resource_id',$resources_ids)
            ->where('date',date('Y-m-d'))
            ->where('hour',$hour)
            ->whereRaw('minute(`interval`) = '.$min)
            ->with('resource')
            ->get();

        $data = array();
        foreach ($list as $rec) {
            $resource_id = $rec->resource['resource_id'];
            $data[$resource_id] = array(
                'actual_load' => $rec->actual_load
            );
        }

        $intrainterval = getIntraIntervalDetails();
        $ret = array(
            'resource_id_list' => $resource_id_list,
            'data' => $data ,
            'hour' => $hour,
            'min' => $min,
            'intrainterval' => $intrainterval, 
            'interval' => 'Hour '.($dt->hour+1).' (Prev. Interval: '.$dt_from->hour .':'.str_pad($min_s,2,"0",STR_PAD_LEFT).' - '.$dt_from->hour.':'.str_pad($min,2,"0",STR_PAD_LEFT).'H)'
        );
        
        return $ret;
    }   

}
