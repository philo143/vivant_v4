<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserCustomer extends Model
{
	protected $fillable = ['customers_id','users_id'];
    protected $table = 'user_customer';

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }
}
