<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NgcpSchedule extends Model
{
    protected $guarded = ['id'];
    protected $table = 'ngcp_schedules';
}
