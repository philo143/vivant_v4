<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NominationItem extends Model
{
    protected $fillable = ['nominations_id','hour','nomination','date','interval'];

    public function nomination()
    {
 		return $this->belongsTo(Nomination::class, 'id', 'nominations_id');
    }
}
