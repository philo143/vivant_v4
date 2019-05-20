<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Widgets extends Model
{
    public function role_widgets(){
    	return $this->hasMany(RoleWidgets::class);
    }
    public function user_widgets(){
    	return $this->hasMany(UserWidget::class);
    }
}
