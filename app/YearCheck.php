<?php


namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class YearCheck extends Model
{
    public static function getYear()
    {
        try {
            $year = SmGeneralSettings::where('church_id', Auth::user()->church_id)->first();
            if(moduleStatusCheck('University')){
                return  $year->unchurch_year->created_at->format('Y');
            }else{
                return $year->church_year->year;
            }
            
        } catch (\Exception $e) {
            return date('Y');
        }
    }
    public static function getAcademicId()
    {
        try {
            $year = SmGeneralSettings::where('church_id', Auth::user()->church_id)->first();
            return $year->session_id;
        } catch (\Exception $e) {
            return "1";
        }
    }
    public static function AcStartDate()
    {
        try { 
            $start_date = SmGeneralSettings::where('church_id',Auth::user()->church_id)->first(); 
            return $start_date->church_year->starting_date;
        } catch (\Exception $e) {
            return date('Y');
        }
    }
    public static function AcEndDate()
    {
        try { 
            $end_date = SmGeneralSettings::where('church_id',Auth::user()->church_id)->first(); 
            return $end_date->church_year->ending_date;
        } catch (\Exception $e) {
            return date('Y');
        }
    }
}
