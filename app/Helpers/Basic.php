<?php

use App\SmClass;
use App\SmSection;
use App\SmStudent;
use App\SmSubject;
use App\SmCurrency;
use App\Models\Theme;
use App\SmClassSection;
use App\SmAssignSubject;
use App\Models\StudentRecord;

if (!function_exists('color_theme')) {
    function color_theme()
    {
        if (!auth()->check()) {
           return userColorThemeActive();
        } else if(auth()->user()) {  
            return userColorThemeActive(auth()->user()->id);
        }
        
    }

}

if (!function_exists('userColorThemeActive')) 
{
    function userColorThemeActive(int $user_id = null)  {

        $theme = Theme::with('colors')->where('is_default', 1)
        ->when($user_id, function ($q) use ($user_id) {
            $q->where('created_by', $user_id);
        })->first();
        if ($user_id && !$theme) {
            $theme = Theme::with('colors')->where('is_default', 1)->first();
        }

        if(!$theme) {
            $theme = Theme::with('colors')->first();
        }
        return $theme;
    }
}
if (!function_exists('userColorThemes')) 
{
    function userColorThemes(int $user_id = null)  {

        $themes = Theme::with('colors')
        ->when($user_id, function ($q) use ($user_id) {
            $q->where('created_by', $user_id);
        })->get();
        if ($user_id && !$themes) {
            $themes = Theme::with('colors')->where('is_system', 1)->get();
        }
        return $themes;
    }
}

if (!function_exists('activeStyle')) {
    function activeStyle()
    {
        if (session()->has('active_style')) {
            $active_style = session()->get('active_style');
            return $active_style;
        } else {
            $active_style = auth()->check() ? Theme::where('id', auth()->user()->style_id)->first() :
                Theme::where('church_id', 1)->where('is_default', 1)->first();

            if ($active_style == null) {
                $active_style = Theme::where('church_id', 1)->where('is_default', 1)->first();
            }
            
            session()->put('active_style', $active_style);
            return session()->get('active_style');
        }
    }
}

if(!function_exists('currency_format_list')) {
    function currency_format_list()
    {
        $symbol = generalSetting()->currency_symbol ?? '$';
        $code = generalSetting()->currency ?? 'USD';
        $formats = [
            [ 'name'=>'symbol_amount','format'=>'symbol(amount) =  '.$symbol.' 1'],
            ['name'=>'amount_symbol', 'format'=>'amount(symbol) = 1'.$symbol],
            ['name'=>'code_amount', 'format'=>'code(amount) = '.$code.' 1'],
            ['name'=>'amount_code', 'format'=>'amount(code) = 1 ' .$code],
        ];

        return $formats;
    }
}
if(!function_exists('currency_format')) {
    function currency_format($amount = null, string $format = null)
    {

        if(!$amount) return false; 

        $code = generalSetting()->currency ?? 'USD';
        
        $format = SmCurrency::where('code', $code)->where('church_id', generalSetting()->church_id)->first();
        
        if(!$format) return $amount;

        $decimal = $format->decimal_digit ?? 0;
        $decimal_separator = $format->decimal_separator ?? "";
        $thousands_separator = $format->thousand_separator ?? "";
        $amount = number_format($amount, $decimal, $decimal_separator, $thousands_separator);
        $symbolCode = $format->currency_type == 'C' ? $format->code : $format->symbol;
       
        $symbolCodeSpace = $format->space ? 
                            ($format->currency_position == 'S' ? $symbolCode.' ' : ' '. $symbolCode) : $symbolCode;
        
        if ($format->currency_position == 'S') {
            return $symbolCodeSpace . $amount;
        } elseif($format->currency_position == 'P') {
            return $amount . $symbolCodeSpace;
        }
    }
}
if(!function_exists('classes')) {
    function classes(int $church_year = null)
    {
        return SmClass::withOutGlobalScopes()
        ->when($church_year, function($q) use($church_year){
            $q->where('church_year_id', $church_year);
        }, function($q){
            $q->where('church_year_id', getAcademicId());
        })->where('church_id', auth()->user()->church_id)
        ->where('active_status', 1)->get();
    }
}
if(!function_exists('sections')) {
    function sections(int $age_group_id, int $church_year = null)
    {
       return  SmClassSection::withOutGlobalScopes()->where('age_group_id', $age_group_id)
                            ->where('church_id', auth()->user()->church_id)
                            ->when($church_year, function($q) use($church_year){
                                $q->where('church_year_id', $church_year);
                            }, function($q){
                                $q->where('church_year_id', getAcademicId());
                            })->groupBy(['age_group_id', 'mgender_id'])->get();

    }
}
if(!function_exists('subjects')) {
    function subjects(int $age_group_id, int $mgender_id, int $church_year = null)
    {
         $subjects = SmAssignSubject::withOutGlobalScopes()
         ->where('age_group_id', $age_group_id)
         ->where('mgender_id', $mgender_id)
         ->where('church_id', auth()->user()->church_id)
         ->when($church_year, function($q) use($church_year){
            $q->where('church_year_id', $church_year);
        }, function($q){
            $q->where('church_year_id', getAcademicId());
        })->groupBy(['age_group_id', 'mgender_id', 'subject_id'])->get(); 
        
        return $subjects;

    }
}
if(!function_exists('students')) {
    function students(int $age_group_id, int $mgender_id = null, int $church_year = null)
    {
         $member_ids = StudentRecord::where('age_group_id', $age_group_id)
         ->when($mgender_id, function($q) use($mgender_id){
            $q->where('mgender_id', $mgender_id);
         })->when('church_year', function($q) use($church_year) {
            $q->where('church_year_id', $church_year);
         })->where('church_id', auth()->user()->church_id)->pluck('member_id')->unique()->toArray();

         $students = SmStudent::withOutGlobalScopes()->whereIn('id', $member_ids)->get();
        
        return $students;

    }
}
if(!function_exists('classSubjects')) {
    function classSubjects($age_group_id = null) {
        $subjects = SmAssignSubject::query();
        if (teacherAccess()) {
            $subjects->where('teacher_id', auth()->user()->staff->id) ;
        }
        if ($age_group_id !="all_class") {
            $subjects->where('age_group_id', '=', $age_group_id);
        } else {
            $subjects->groupBy('age_group_id');
        }
        $subjectIds = $subjects->groupBy('subject_id')->get()->pluck(['subject_id'])->toArray();        

        return SmSubject::whereIn('id', $subjectIds)->get(['id','subject_name']);
    }
}
if(!function_exists('subjectSections')) {
    function subjectSections($age_group_id = null, $subject_id =null) {
        if(!$age_group_id || !$subject_id) return null;
        $sectionIds = SmAssignSubject::where('age_group_id', $age_group_id)
        ->where('subject_id', '=', $subject_id)                         
        ->where('church_id', auth()->user()->church_id)
        ->when(teacherAccess(), function($q) {
            $q->where('teacher_id',auth()->user()->staff->id);
        })
        ->groupby(['age_group_id','mgender_id'])
        ->pluck('mgender_id')
        ->toArray();
        return SmSection::whereIn('id',$sectionIds)->get(['id','mgender_name']);

    }
}