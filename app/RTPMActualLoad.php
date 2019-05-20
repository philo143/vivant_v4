<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RTPMActualLoad extends Model
{
    protected $fillable = [
    	'date','hour','interval','plant_id','resource_id','actual_load','actual_load_acknowledged','actual_load_acknowledged_by','actual_load_acknowledged_dt','rtd_acknowledged','rtd','rtd_acknowledged_by','rtd_acknowledged_dt','submitted_by'
	];
	
    protected $table = 'rtpm_actual_load_acknowledge';

    public function resource()
    {
 		return $this->belongsTo(Resource::class, 'resource_id');
    }
}
