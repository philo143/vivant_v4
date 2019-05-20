<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ASPANomination extends Model
{
    protected $fillable = [
    	'date','hour','interval','plant_id','resource_id','dispatch_capacity','remarks','submitted_by'
    	,'available_capacity','pump','rr','cr','dr','rps','nominated_price','scheduled_capacity','filename'
	];
	
    protected $table = 'aspa_nominations';


    public function resource()
    {
 		return $this->belongsTo(Resource::class, 'resource_id', 'id');
    }
}
