<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IslandMode extends Model
{
	protected $fillable = [
    	'date','hour','interval','plant_id','im','submitted_by'
	];
	
    protected $table = 'island_mode';
}
