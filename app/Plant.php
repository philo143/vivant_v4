<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Plant extends Model
{
	protected $fillable = [
		'plant_name', 'participant_id', 'long_name', 'description', 'location' ,'is_aspa' , 'aspa_type' ,'is_island_mode', 'engines'
	];
    public function resource()
    {
    	return $this->hasMany(Resource::class);
    }
    public function participant()
    {
    	return $this->belongsTo(Participant::class);
    }

    public function aspa_types()
    {
        return $this->belongsTo(AspaType::class,'aspa_type','type');
    }
}
