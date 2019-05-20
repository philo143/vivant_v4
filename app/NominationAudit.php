<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NominationAudit extends Model
{
    protected $fillable = ['transaction_id','nominations_id','sdate','edate','type','participants_id','customers_id','data','remarks','submitted_by'];
    protected $table = 'nominations_audit';


    public function user()
    {
 		return $this->belongsTo(User::class, 'submitted_by', 'id');
    }
}
