<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ASPANominationAudit extends Model
{
    protected $fillable = ['action','data','user'];

	protected $table = 'aspa_nominations_audit';
}
