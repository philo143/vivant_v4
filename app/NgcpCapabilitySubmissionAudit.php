<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NgcpCapabilitySubmissionAudit extends Model
{
    protected $fillable = ['action','data','user'];

	protected $table = 'ngcp_capabilities_submission_audit';
}
