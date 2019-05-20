<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MmsMpdHapLmp extends Model
{
    protected $table = 'mms_mpd_hap_lmp';
   	protected $fillable = [
   		'run_time','interval_end','price_node','lmp'
   	];
}
