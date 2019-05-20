<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MmsRtem extends Model
{
    protected $table = 'mms_rtem';

    public function resource(){
    	return $this->belongsTo(Resource::class);
    }
}
