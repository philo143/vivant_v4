<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerNomination extends Model
{
    protected $fillable = ['customers_id','nominations_id'];
    protected $table = 'customer_nomination';

     public function nominations()
    {
    	return $this->hasOne(Nomination::class, 'id', 'nominations_id');
    }
    public function customers()
    {
    	return $this->belongsTo(Customer::class);
    }

}
