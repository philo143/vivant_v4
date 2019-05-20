<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerParticipant extends Model
{
	protected $fillable = ['customers_id','participants_id'];
    protected $table = 'customer_participant';

    public function participants()
    {
    	return $this->hasOne(Participant::class, 'id', 'participants_id');
    }
    public function customers()
    {
    	return $this->belongsTo(Customer::class);
    }

}
