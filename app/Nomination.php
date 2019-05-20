<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Nomination extends Model
{
    protected $fillable = ['date','type','participants_id','customers_id','submitted_by','remarks','sdate','edate'];

    public function nomination_items()
    {
    	return $this->hasMany(NominationItem::class, 'nominations_id', 'id');
    }


    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'id', 'customers_id');
    }
}
