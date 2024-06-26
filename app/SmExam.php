<?php

namespace App;

use App\SmExamType;
use App\Scopes\AcademicSchoolScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmExam extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new AcademicSchoolScope);
    }

    public function class()
    {
        return $this->belongsTo('App\SmClass', 'age_group_id', 'id');
    }

    public function getClassName()
    {
        return $this->belongsTo('App\SmClass', 'age_group_id', 'id');
    }

    public function GetSectionName()
    {
        return $this->belongsTo('App\SmSection', 'mgender_id', 'id');
    }
    public function GetSubjectName()
    {
        return $this->belongsTo('App\SmSubject', 'subject_id', 'id');
    }
    public function GetExamTitle()
    {
        return $this->belongsTo('App\SmExamType', 'exam_type_id', 'id');
    }
    public function subject()
    {
        if (moduleStatusCheck('University')) {
            return $this->belongsTo('Modules\University\Entities\UnSubject', 'un_subject_id', 'id');
        }
        return $this->belongsTo('App\SmSubject', 'subject_id', 'id');
    }
    public function section()
    {
        return $this->belongsTo('App\SmSection', 'mgender_id', 'id');
    }

    public function GetExamSetup()
    {
        return $this->hasMany('App\SmExamSetup', 'exam_id', 'id');
    }
    public function examType()
    {
        return $this->hasOne(SmExamType::class, 'id', 'exam_type_id');
    }

    public function markRegistered()
    {
        return $this->hasOne(SmMarkStore::class, 'exam_term_id', 'exam_type_id')
        ->where('age_group_id', $this->age_group_id)->where('mgender_id', $this->mgender_id);
    }
    public function marks()
    {
        return $this->hasMany('App\SmExamSetup', 'exam_id', 'id');
    }

    public function markDistributions()
    {
        return $this->marks();
    }


    public static function getMarkDistributions($ex_id, $age_group_id, $mgender_id, $subject_id)
    {
        try {
            $data = SmExamSetup::where([
                ['exam_term_id', $ex_id],
                ['age_group_id', $age_group_id],
                ['mgender_id', $mgender_id],
                ['subject_id', $subject_id],
            ])->get();

            return $data;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function getMarkREgistered($ex_id, $age_group_id, $mgender_id, $subject_id)
    {
        try {
            $data = SmMarkStore::where([
                ['exam_term_id', $ex_id],
                ['age_group_id', $age_group_id],
                ['mgender_id', $mgender_id],
                ['subject_id', $subject_id],
            ])->first();

            return $data;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public function markStore()
    {
        return $this->hasOne(SmMarkStore::class, 'exam_term_id', 'exam_type_id')
            ->where('age_group_id', $this->age_group_id)->where('mgender_id', $this->mgender_id)->where('subject_id', $this->subject_id)
            ->where('church_id', Auth::user()->church_id);
    }

    public function sessionDetails()
    {
        return $this->belongsTo('Modules\University\Entities\UnSession', 'un_session_id', 'id')->withDefault();
    }

    public function semesterDetails()
    {
        return $this->belongsTo('Modules\University\Entities\UnSemester', 'un_semester_id', 'id')->withDefault();
    }

    public function academicYearDetails()
    {
        return $this->belongsTo('Modules\University\Entities\UnAcademicYear', 'un_church_year_id', 'id')->withDefault();
    }

    public function departmentDetails()
    {
        return $this->belongsTo('Modules\University\Entities\UnDepartment', 'un_department_id', 'id')->withDefault();
    }

    public function facultyDetails()
    {
        return $this->belongsTo('Modules\University\Entities\UnFaculty', 'un_faculty_id', 'id')->withDefault();
    }

    public function subjectDetails()
    {
        return $this->belongsTo('Modules\University\Entities\UnSubject', 'un_subject_id', 'id')->withDefault();
    }
}
