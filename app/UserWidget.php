<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserWidget extends Model
{
    protected $table = 'user_widgets';

    public function widgets(){
    	return $this->belongsTo(Widgets::class,'widgets_id'); 
    }
    public function users(){
    	return $this->belongsTo(User::class,'users_id'); 
    }
    public function resources(){
    	return $this->belongsTo(Resource::class,'resources_id'); 
    }

    public function scopeUserWidgets($query) 
	{
		return $query->with('widgets','users','resources');
	}
}
