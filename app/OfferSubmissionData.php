<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OfferSubmissionData extends Model
{
	protected $fillable = ['delivery_date','hour','interval','b_p0','b_p1','b_p2','b_p3','b_p4','b_p5','b_p6','b_p7','b_p8','b_p9','b_p10'
		,'b_v0','b_v1','b_v2','b_v3','b_v4','b_v5','b_v6','b_v7','b_v8','b_v9','b_v10','breakpoint0','breakpoint1','breakpoint2','breakpoint3','breakpoint4'
		,'ramp_up0','ramp_up1','ramp_up2','ramp_up3','ramp_up4','ramp_down0','ramp_down1','ramp_down2','ramp_down3','ramp_down4','remarks','go_status'
		,'return_code','submitted_by','offer_submission_units_id'];
    protected $table = 'offer_submission_data';

    public function offer_unit()
    {
    	return $this->belongsTo(OfferSubmissionUnits::class);
    }
}
