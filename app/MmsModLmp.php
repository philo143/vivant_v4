<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MmsModLmp extends Model
{
    protected $table = 'mms_mod_lmp';
   	protected $fillable = [
   		'date','interval','price_node','lmp'
   	];
}
