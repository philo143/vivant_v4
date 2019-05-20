<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserResource extends Model
{
	protected $fillable = ['resources_id','users_id'];
    protected $table = 'user_resource';

    public function resources()
    {
    	return $this->hasOne(Resource::class,'resources_id','id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'users_id');
    }
}