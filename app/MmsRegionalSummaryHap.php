<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MmsRegionalSummaryHap extends Model
{
    protected $table = 'mms_regional_summary_hap';
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
