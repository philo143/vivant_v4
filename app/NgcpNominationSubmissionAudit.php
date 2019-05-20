<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NgcpNominationSubmissionAudit extends Model
{
    protected $fillable = ['action','data','user'];

	protected $table = 'ngcp_nominations_submission_audit';
}
