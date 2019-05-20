<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlantCapabilityAudit extends Model
{
	protected $fillable = ['transaction_id','action','data','user'];
    protected $table = "plant_capability_audit";
}
