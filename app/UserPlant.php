<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPlant extends Model
{
	protected $fillable = ['plant_id','users_id'];
    protected $table = 'user_plant';

    public function plants()
    {
    	return $this->hasOne(Plant::class,'id','plants_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'users_id');
    }
}