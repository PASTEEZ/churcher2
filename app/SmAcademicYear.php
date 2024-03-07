<?php

namespace App;

use App\SmGeneralSettings;
use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmAcademicYear extends Model
{
    use HasFactory;
    protected static function boot()
    {
        parent::boot();
  
        return static::addGlobalScope(new ActiveStatusSchoolScope);
    }
    public function scopeActive($query)
    {

        return $query->where('active_status', 1);
    }
    public static function API_church_year($church_id)
    {
        try {
            $settings = SmGeneralSettings::where('church_id', $church_id)->first();
            if(moduleStatusCheck('University')){
                return $settings->un_church_year_id;
             }
             return $settings->session_id;
        } catch (\Exception $e) {
            return 1;
        }

    }
    public static function SINGLE_SCHOOL_API_church_year()
    {
        try {
            $settings = SmGeneralSettings::where('church_id', 1)->first();
            if(moduleStatusCheck('University')){
               return $settings->un_church_year_id;
            }

            return $settings->session_id;
            
        } catch (\Exception $e) {
            return 1;
        }
    }
}
