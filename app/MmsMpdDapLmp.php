<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MmsMpdDapLmp extends Model
{
    protected $table = 'mms_mpd_dap_lmp';
   	protected $fillable = [
   		'run_time','interval_end','price_node','lmp'
   	];
}
