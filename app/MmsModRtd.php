<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MmsModRtd extends Model
{
    protected $table = 'mms_mod_rtd';
   	protected $fillable = [
   		'date','interval','price_node','mw','lmp','loss_factor','energy','loss','congestion'
   	];
}
