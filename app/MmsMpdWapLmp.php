<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MmsMpdWapLmp extends Model
{
    protected $table = 'mms_mpd_wap_lmp';
   	protected $fillable = [
   		'run_time','interval_end','price_node','lmp'
   	];
}
