<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlantShiftReport extends Model
{
    protected $fillable = [
    	'date','hour','interval','plant_id','resource_id','type_id','report','submitted_by'
	];

	protected $table = 'plant_shift_report';


	public function shift_report_type()
    {
    	return $this->belongsTo(PlantShiftReportType::class,'type_id');
    }


    public function user()
    {
    	return $this->belongsTo(User::class,'submitted_by');
    }


    public function island_mode()
    {
        return $this->hasOne(IslandMode::class,'date','hour','interval','plant_idx');
    }


    public function plant()
    {
        return $this->belongsTo(Plant::class,'plant_id');
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class,'resource_id');
    }
}
