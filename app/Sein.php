<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sein extends Model
{
	protected $fillable = ['sein','type','customers_id','resources_id'];
    protected $table = 'sein';

    public function customer()
    {
    	return $this->belongsTo(Customer::class, 'customers_id', 'id');	
    }
    public function resource()
    {
    	return $this->belongsTo(Resource::class, 'resources_id', 'id');
    }
}

