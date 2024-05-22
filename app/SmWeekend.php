<?php

namespace App;

use App\SmStaff;
use App\SmStudent;
use App\SmClassRoutineUpdate;
use App\Scopes\SchoolScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmWeekend extends Model
{
    use HasFactory;
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new SchoolScope);
    }
    public function classRoutine()
    {
        return $this->hasMany('App\SmClassRoutineUpdate', 'day', 'id');
    }

    public function studentClassRoutine()
    {
        $student = SmStudent::where('user_id', auth()->user()->id)->first();         
        return $this->hasMany('App\SmClassRoutineUpdate', 'day', 'id')
        ->where('age_group_id', $student->age_group_id)
        ->where('mgender_id', $student->mgender_id)
        ->where('church_year_id', getAcademicId())
        ->where('church_id', auth()->user()->church_id)->oderBy('start_time');
    }
    public static function studentClassRoutineFromRecord($age_group_id, $mgender_id, $day_id)
    {
         
        $routine = SmClassRoutineUpdate::where('day', $day_id)
                                    ->where('age_group_id', $age_group_id)
                                    ->where('mgender_id', $mgender_id)
                                    ->where('church_year_id', getAcademicId())
                                    ->where('church_id', auth()->user()->church_id)->get();
        return  $routine;
    }
    
    public static function studentClassRoutineFromRecordUniversity($un_church_year_id, $un_semester_label_id, $day_id)
    {
         
        $routine = SmClassRoutineUpdate::where('day', $day_id)
                                    ->where('un_church_year_id', $un_church_year_id)
                                    ->where('un_semester_label_id', $un_semester_label_id)
                                    ->where('church_year_id', getAcademicId())
                                    ->where('church_id', auth()->user()->church_id)->get();
        return  $routine;
    }
    public function teacherClassRoutine()
    {
        $teacher_id = SmStaff::where('user_id', auth()->user()->id)
        ->where(function($q) {
            $q->where('role_id', 4)->orWhere('previous_role_id', 4);
        })
        ->first()->id;
         
        return $this->hasMany('App\SmClassRoutineUpdate', 'day', 'id')
        ->where('teacher_id', $teacher_id)
        ->where('church_year_id', getAcademicId())
        ->where('church_id', auth()->user()->church_id);
    }

    public function teacherClassRoutineAdmin()
    {
        return $this->hasMany('App\SmClassRoutineUpdate', 'day', 'id')
        ->where('teacher_id', request()->teacher)
        ->where('church_year_id', getAcademicId())
        ->where('church_id', auth()->user()->church_id);
    }

    public static function teacherClassRoutineById($day, $teacher_id)
    {

        return SmClassRoutineUpdate::where('day', $day)->where('teacher_id', $teacher_id)
        ->where('church_year_id', getAcademicId())
        ->where('church_id', auth()->user()->church_id)->get();
    }

    public static function unTeacherClassRoutineById($day, $teacher_id)
    {
        return SmClassRoutineUpdate::where('day', $day)->where('teacher_id', $teacher_id)
            ->where('un_church_year_id', getAcademicId())
            ->where('church_id', auth()->user()->church_id)->get();
    }

    public static function parentClassRoutine($day, $member_id)
    {
        $student = SmStudent::find($member_id);

        return SmClassRoutineUpdate::where('day', $day)->where('age_group_id', $student->age_group_id)
            ->where('mgender_id', $student->mgender_id)
            ->where('church_year_id', getAcademicId())
            ->where('church_id', auth()->user()->church_id)->get();
    }



    public static function universityStudentClassRoutine($un_semester_label_id, $mgender_id, $day_id)
    {
         
        $routine = SmClassRoutineUpdate::where('day', $day_id)
                                    ->where('un_semester_label_id', $un_semester_label_id)
                                    ->where('un_mgender_id', $mgender_id)
                                    ->where('church_id', auth()->user()->church_id)->get();
        return  $routine;
    }

}
