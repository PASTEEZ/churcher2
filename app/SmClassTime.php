<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmClassTime extends Model
{
    use HasFactory;
    protected static function boot()
    {
        parent::boot();
  
        static::addGlobalScope(new AcademicSchoolScope);
    }
    
    public function examSchedules()
    {
        return $this->hasMany(SmExamSchedule::class,'exam_period_id');
    }

    public function routineUpdates()
    {
        return $this->hasMany(SmClassRoutineUpdate::class,'class_period_id')->where('church_year_id',getAcademicId());
    }
    public function studentRoutineUpdates()
    {
        return $this->hasMany(SmClassRoutineUpdate::class,'class_period_id')->where('church_year_id',getAcademicId())->where('age_group_id', $this->age_group_id)
            ->where('mgender_id', $this->mgender_id);
    }
}
