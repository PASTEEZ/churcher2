<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\OnlineExam\Entities\InfixStudentTakeOnlineExam;

class SmOnlineExam extends Model
{
    use HasFactory;
    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
 
    
    public function studentInfo()
    {
        return $this->belongsTo('App\SmStudent', 'id', 'member_id');
    }

    public function class()
    {
        return $this->belongsTo('App\SmClass', 'age_group_id', 'id');
    }

    public function section()
    {
        return $this->belongsTo('App\SmSection', 'mgender_id', 'id');
    }

    public function subject()
    {
        return $this->belongsTo('App\SmSubject', 'subject_id', 'id');
    }

    public function assignQuestions()
    {
        return $this->hasMany('App\SmOnlineExamQuestionAssign', 'online_exam_id', 'id');
    }

    public static function obtainedMarks($exam_id, $member_id, $record_id = null)
    {

        try {
            if (moduleStatusCheck('OnlineExam')==true) {
                $marks = InfixStudentTakeOnlineExam::select('status', 'student_done', 'total_marks')
                ->where('online_exam_id', $exam_id)->where('member_id', $member_id)
                ->where('student_record_id', $record_id)
                ->first();
            } else {
                $marks = SmStudentTakeOnlineExam::select('status', 'total_marks')
                ->where('online_exam_id', $exam_id)
                ->where('member_id', $member_id)
                ->first();
            }
            return $marks;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

}
