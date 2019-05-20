<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResourcesLookup extends Model
{
    protected $table = 'resource_lookup';

    protected $fillable = [
    	'resource_id','region','type','is_mms_reserve','reserve_classes'
    ];
}
