<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MmsRegionalSummaryDap extends Model
{
    protected $table = 'mms_regional_summary_dap';
   	protected $fillable = [
   		'run_time',
        'interval_end',
        'region',
        'commodity',
        'scenario',
        'commodity_req',
        'bid_in_demand',
        'curtailed_load',
        'energy_loss',
        'generation',
        'import',
        'export'
   	];
}
