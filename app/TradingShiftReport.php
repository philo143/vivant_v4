<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TradingShiftReport extends Model
{
    protected $fillable = [
    	'date','hour','interval','type_id','report','submitted_by'
	];
	protected $table = 'trading_shift_report';


	public function shift_report_type()
    {
    	return $this->belongsTo(TradingShiftReportType::class,'type_id');
    }


    public function user()
    {
    	return $this->belongsTo(User::class,'submitted_by');
    }

}
