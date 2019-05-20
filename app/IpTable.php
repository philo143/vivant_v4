<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IpTable extends Model
{
    protected $table = 'ip_tables';

    protected $fillable = [
    	'type','ip_address','status'
	];
}
