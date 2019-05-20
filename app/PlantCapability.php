<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlantCapability extends Model
{
   protected $fillable = [
    	'delivery_date','hour','interval','capability','description','plant_capability_type_id','plant_capability_status_id'
	];
	protected $table = 'plant_capability';
	public function resources() {
		return $this->belongsTo(Resource::class);
	}
	public function plantCapabilityStatus(){
		return $this->belongsTo(PlantCapabilityStatus::class);
	}
	public function plantCapabilityType(){
		return $this->belongsTo(PlantCapabilityType::class);
	}
	public function scopeMerged($query)  // if you need something inside resources,plant_capability_status,plant_capability_type tables;
	{
		return $query->with('resources','plantCapabilityStatus','plantCapabilityType');
	}
}
