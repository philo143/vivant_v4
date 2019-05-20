<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $fillable = [
    	'participant_name','description','cert_loc','cert_file','cert_user','cert_pass','status'
	];

	public function plant()
	{
		return $this->hasMany(Plant::class);
	}
}
