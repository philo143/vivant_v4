<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MmsDapPriceAndSchedule extends Model
{
    protected $table = 'mms_mpd_dap_sched';
    protected $fillable = [
   		'run_time','interval_end','price_node','mw','lmp','loss_factor','energy','loss','congestion'
   	];
}
