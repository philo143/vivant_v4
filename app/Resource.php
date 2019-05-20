<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $fillable = [
    	'resource_id','plant_id','region','pmin','pmax','ramp_rate','ramp_up','ramp_down','unit_no'
    ];

    public function plant()
    {
    	return $this->belongsTo(Plant::class);
    }
    public function plantCapability()
    {
        return $this->hasMany(PlantCapability::class,'resources_id'); 
    }
    public function offerUnits()
    {
        return $this->hasMany(OfferSubmissionUnits::class,'resources_id'); 
    }
    public function mmsRtem(){
        return $this->hasMany(MmsRtem::class,'resources_id');
    }
    public function mmsOpres(){
        return $this->hasMany(MmsOpres::class,'resources_id');
    }
    public function user_widgets(){
        return $this->hasMany(UserWidget::class);
    }
    public function scopeFilterByPlant($query,$filters){
        if ( $plant_id = $filters['plant_id']){
            $query->where('plant_id',$plant_id);
        }
    }

}
