<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OfferAudit extends Model
{
	protected $fillable = [
    	'transaction_id','data','delivery_date','resource_id','type','submitted_by'
	];

    protected $table = 'offer_audit';
}
