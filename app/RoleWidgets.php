<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoleWidgets extends Model
{
    protected $table = 'role_widgets';

    public function widgets()
    {
    	return $this->belongsTo(Widgets::class,'widgets_id'); 
    }
}
