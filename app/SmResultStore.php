<?php

namespace App;

use App\SmMarkStore;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmResultStore extends Model
{
    use HasFactory;
    public function studentInfo(){
    	return $this->belongsTo('App\SmStudent', 'member_id', 'id');
    }
    public function exam(){
        return $this->belongsTo(SmExamType::class, 'exam_type_id');
    }

    public function subject(){
        return $this->belongsTo('App\SmSubject', 'subject_id', 'id');
    }
    public function class(){
        return $this->belongsTo('App\SmClass', 'age_group_id', 'id');
    }
     public function section()
    {
        return $this->belongsTo('App\SmSection', 'mgender_id', 'id');
    }

     public function studentRecord()
    {
        return $this->belongsTo(StudentRecord::class, 'student_record_id', 'id');
    }

    public static function remarks($gpa){
    try{
        $mark = SmMarksGrade::where([
            ['from', '<=', $gpa], 
            ['up', '>=', $gpa]]
            )
            ->where('church_id',Auth::user()->church_id)
            ->where('church_year_id', getAcademicId())
            ->first();
            return $mark;
    } catch (\Exception $e) {
        $mark=[];
        return $mark;
    }


    }
    public static function  GetResultBySubjectId($age_group_id, $mgender_id, $subject_id,$exam_id,$member_id){
    	
        try {
            $data = SmMarkStore::withOutGlobalScopes()->where([
                ['age_group_id',$age_group_id],
                ['mgender_id',$mgender_id],
                ['exam_term_id',$exam_id],
                ['student_record_id',$member_id],
                ['subject_id',$subject_id]
            ])->get();
            return $data;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }

    public static function  un_GetResultBySubjectId($subject_id, $exam_id, $member_id, $request){

        try {
            $SmMarkStore = SmMarkStore::query();
            $data = universityFilter($SmMarkStore, $request)
            ->where([
                ['exam_term_id',$exam_id],
                ['member_id',$member_id],
                ['un_subject_id',$subject_id]
            ])->get();
            return $data;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }

    public static function  GetFinalResultBySubjectId($age_group_id, $mgender_id, $subject_id,$exam_id,$member_id){
        
        try {
            $data = SmResultStore::where([
                ['age_group_id',$age_group_id],
                ['mgender_id',$mgender_id],
                ['exam_type_id',$exam_id],
                ['student_record_id',$member_id],
                ['subject_id',$subject_id]
                ])->first();

                return $data;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }

    public static function  un_GetFinalResultBySubjectId($subject_id, $exam_id, $member_id, $request)
    {
        try {
            $SmResultStore = SmResultStore::query();
            $data = universityFilter($SmResultStore, $request)
            ->where([
                ['exam_type_id',$exam_id],
                ['member_id',$member_id],
                ['un_subject_id',$subject_id]
                ])->first();

                return $data;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }

    public static function termBaseMark($age_group_id, $mgender_id, $subject_id,$exam_id,$member_id){
        $data = SmResultStore::where([
            ['age_group_id',$age_group_id],
            ['mgender_id',$mgender_id],
            ['exam_type_id',$exam_id],
            ['student_record_id',$member_id],
            ['subject_id',$subject_id]
            ])
            ->groupBy('exam_type_id')
            ->sum('total_gpa_point');
            return $data;
    }

    public static function un_termBaseMark($subject_id, $exam_id, $member_id, $request){

        $SmResultStore = SmResultStore::query();
            $data = universityFilter($SmResultStore, $request)
            ->where([
                ['exam_type_id',$exam_id],
                ['member_id',$member_id],
                ['un_subject_id',$subject_id]
            ])
            ->groupBy('exam_type_id')
            ->sum('total_gpa_point');
            return $data;
    }

    public function unSubjectDetails()
    {
        return $this->belongsTo('Modules\University\Entities\UnSubject', 'un_subject_id', 'id');
    }

}
