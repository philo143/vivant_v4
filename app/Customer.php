<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['customer_name', 'customer_full_name','customer_type_id'];

    public function customer_type()
    {
    	return $this->hasOne(CustomerType::class, 'id', 'customer_type_id');
    }
    public function customer_participant()
    {
    	return $this->hasMany(CustomerParticipant::class, 'customers_id', 'id');
    }
    public function sein()
    {
    	return $this->hasMany(Sein::class, 'customers_id', 'id');
    }
    public function user_customer()
    {
        return $this->hasMany(UserCustomer::class, 'customers_id', 'id');
    }


    public function nominations()
    {
        return $this->belongsToMany(Nomination::class, 'customer_nomination','customers_id','nominations_id');
    }
}
