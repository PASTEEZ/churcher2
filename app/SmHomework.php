<?php

namespace App;

use App\Models\StudentRecord;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmHomework extends Model
{
    use HasFactory;
    protected $table = "sm_homeworks";
    protected $fillable = [
        'age_group_id', 'mgender_id', 'subject_id', 'created_by', 'evaluated_by',
    ];
    protected $appends=['HomeworkPercentage'];

    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
    public function classes(){
        return $this->belongsTo('App\SmClass', 'age_group_id', 'id');
    }


    public function class()
    {
        return $this->belongsTo('App\SmClass', 'age_group_id', 'id');
    }

    public function sections()
    {
        return $this->belongsTo('App\SmSection', 'mgender_id', 'id');
    }
    public function section()
    {
        return $this->belongsTo('App\SmSection', 'mgender_id', 'id');
    }
    public function homeworkCompleted()
    {
        return $this->hasMany('App\SmHomeworkStudent', 'homework_id', 'id')->where('complete_status', 'C');
    }

    public function lmsHomeworkCompleted()
    {
        return $this->hasOne('App\SmHomeworkStudent', 'homework_id','id');
    }


    public function subjects()
    {
        return $this->belongsTo('App\SmSubject', 'subject_id', 'id');
    }

    public function users()
    {
        return $this->belongsTo('App\User', 'created_by', 'id');
    }
    public function evaluatedBy()
    {
        return $this->belongsTo('App\User', 'evaluated_by', 'id');
    }

    public static function getHomeworkPercentage($age_group_id, $mgender_id, $homework_id)
    {
        try {
            $totalStudents = StudentRecord::where('age_group_id', $age_group_id)
                ->where('mgender_id', $mgender_id)
                ->where('church_id', auth()->user()->church_id)
                ->where('church_year_id', getAcademicId())
                ->count();
            $totalHomeworkCompleted = SmHomeworkStudent::select('id')
                ->where('homework_id', $homework_id)
                ->where('church_id', auth()->user()->church_id)
                ->where('church_year_id', getAcademicId())
                ->where('complete_status', 'C')
                ->count();



            if (isset($totalStudents)) {
                $homeworks = array(
                    'totalStudents' => $totalStudents,
                    'totalHomeworkCompleted' => $totalHomeworkCompleted,

                );
                return $homeworks;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
    public function getHomeworkPercentageAttribute()
    {
        try {
            $totalStudents = SmStudent::withOutGlobalScope(StatusAcademicSchoolScope::class)->select('id')
                ->where('age_group_id', $this->age_group_id)
                ->where('mgender_id', $this->mgender_id)
                ->where('church_id', auth()->user()->church_id)
              
                ->count();

            $totalHomeworkCompleted = SmHomeworkStudent::select('id')
                ->where('homework_id', $this->homework_id)
                ->where('church_year_id', getAcademicId())
                ->where('complete_status', 'C')
                ->count();

            if (isset($totalStudents)) {
                $homeworks = array(
                    'totalStudents' => $totalStudents,
                    'totalHomeworkCompleted' => $totalHomeworkCompleted,

                );
                return $homeworks;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function evaluationHomework($s_id, $h_id)
    {

        try {
            $abc = SmHomeworkStudent::where('homework_id', $h_id)->where('member_id', $s_id)->first();
            return $abc;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function uploadedContent($s_id, $h_id)
    {
        try {
            $abc = SmUploadHomeworkContent::where('homework_id', $h_id)->where('member_id', $s_id)->get();
            return $abc;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public function unSession()
    {
        return $this->belongsTo('Modules\University\Entities\UnSession', 'un_session_id', 'id')->withDefault();
    }
    public function unFaculty()
    {
        return $this->belongsTo('Modules\University\Entities\UnFaculty', 'un_faculty_id', 'id')->withDefault();
    }
    public function unDepartment()
    {
        return $this->belongsTo('Modules\University\Entities\UnDepartment', 'un_department_id', 'id')->withDefault();
    }
    public function unAcademic()
    {
        return $this->belongsTo('Modules\University\Entities\UnAcademicYear', 'un_church_year_id', 'id')->withDefault();
    }
    public function unSemester()
    {
        return $this->belongsTo('Modules\University\Entities\UnSemester', 'un_semester_id', 'id')->withDefault();
    }


    public function semester()
    {
        return $this->belongsTo('Modules\University\Entities\UnSemester', 'un_semester_id', 'id')->withDefault();
    }

    public function semesterLabel()
    {
        return $this->belongsTo('Modules\University\Entities\UnSemesterLabel', 'un_semester_label_id', 'id')->withDefault();
    }

    public function unSubject()
    {
        return $this->belongsTo('Modules\University\Entities\UnSubject', 'un_subject_id', 'id')->withDefault();
    }
}
