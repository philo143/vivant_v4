<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OfferSubmissionUnits extends Model
{
    protected $fillable = ['delivery_date','participants_id','expiry_date','day_type','reserve_class','opres_ramp_rate','status','generated_xml','response_str','response_trans_id','offer_params','action','remarks','submitted_by','offer_type_id','resources_id'];
    protected $table = 'offer_submission_units';

    public function offer_type()
    {
    	return $this->belongsTo(OfferType::class);
    }
    public function resource()
    {
    	return $this->belongsTo(Resource::class,'resources_id');
    }
    public function offer_data(){
        return $this->hasMany(OfferSubmissionData::class,'offer_submission_units_id');
    }
    public function user() 
    {
        return $this->belongsTo(User::class,'submitted_by')->select('id','fullname','username');
    }
}
