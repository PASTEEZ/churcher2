<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Saas\Entities\SmSubscriptionPayment;

class SmSchool extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function subscription()
    {
        return $this->hasOne(SmSubscriptionPayment::class, 'church_id')->latest();
    }

    public function academicYears()
    {
        return $this->hasMany(SmAcademicYear::class, 'church_id', 'id');
    }

    public function sections()
    {
        return $this->hasMany(SmSection::class, 'church_id');
    }

    public function classes()
    {
        return $this->hasMany(SmClass::class, 'church_id');
    }

    public function classTimes()
    {
        return $this->hasMany(SmClassTime::class, 'church_id')->where('type', 'class');
    }
    public function weekends()
    {
        return $this->hasMany(SmWeekend::class, 'church_id')->where('active_status', 1);
    }
    public function routineUpdates()
    {
        return $this->hasMany(SmClassRoutineUpdate::class, 'church_id')->where('active_status', 1);
    }
}
