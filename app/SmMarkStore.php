<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmMarkStore extends Model
{
    use HasFactory;
    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new AcademicSchoolScope);
    }
    
    public function class(){
        return $this->belongsTo('App\SmClass', 'age_group_id', 'id');
    }
     public function section()
    {
        return $this->belongsTo('App\SmSection', 'mgender_id', 'id');
    }

    public function subjectName()
    {
        return $this->belongsTo('App\SmSubject', 'subject_id', 'id');
    }
 
    public static function get_mark_by_part($member_id, $exam_id, $age_group_id, $mgender_id, $subject_id, $exam_setup_id, $record_id){
    	
        try {
            $getMark= SmMarkStore::where([
                ['member_id',$member_id], 
                ['exam_term_id',$exam_id], 
                ['age_group_id',$age_group_id], 
                ['mgender_id',$mgender_id], 
                ['exam_setup_id',$exam_setup_id], 
                ['student_record_id', $record_id], 
                ['subject_id',$subject_id]
            ])->first();
            if(!empty($getMark)){
                $output= $getMark->total_marks;
            }else{
                $output= '0';
            }

            return $output;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }


    public static function un_get_mark_by_part($member_id, $request, $exam_id, $subject_id, $exam_setup_id, $record_id)
    {
        try {
            $SmMarkStore = SmMarkStore::query();
            $getMark = universityFilter($SmMarkStore, $request)
                ->where([
                ['member_id',$member_id], 
                ['exam_term_id',$exam_id], 
                ['exam_setup_id',$exam_setup_id], 
                ['student_record_id', $record_id], 
                ['un_subject_id',$subject_id]
            ])->first();
            
            if(!empty($getMark)){
                $output= $getMark->total_marks;
            }else{
                $output= '0';
            }
            return $output;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }

    public static function is_absent_check($member_id, $exam_id, $age_group_id, $mgender_id, $subject_id, $record_id)
    {
        
        try {
            $getMark= SmMarkStore::where([
                ['member_id',$member_id], 
                ['exam_term_id',$exam_id], 
                ['age_group_id',$age_group_id], 
                ['student_record_id', $record_id], 
                ['mgender_id',$mgender_id], 
                ['subject_id',$subject_id]
            ])->first();
            if (!empty($getMark)) {
                $output= $getMark->is_absent;
            } else {
                $output= '0';
            }
            return $output;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }

    public static function un_is_absent_check($member_id, $exam_id, $request, $subject_id, $record_id)
    {
        try {
            $SmMarkStore = SmMarkStore::query();
            $getMark = universityFilter($SmMarkStore, $request)
            ->where([
                ['member_id',$member_id], 
                ['exam_term_id',$exam_id],
                ['student_record_id', $record_id], 
                ['subject_id',$subject_id]
            ])->first();
            if (!empty($getMark)) {
                $output= $getMark->is_absent;
            } else {
                $output= '0';
            }
            return $output;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }

    public static function teacher_remarks($member_id, $exam_id, $age_group_id, $mgender_id, $subject_id, $record_id) {
        
        $getMark= SmMarkStore::where([
            ['member_id',$member_id], 
            ['exam_term_id',$exam_id], 
            ['age_group_id',$age_group_id], 
            ['mgender_id',$mgender_id], 
            ['student_record_id', $record_id], 
            ['subject_id',$subject_id]
        ])->first();

        if($getMark != ""){
            $output= $getMark->teacher_remarks;
        }else{
            $output= '';
        }

        return $output;
    }

    public static function un_teacher_remarks($member_id, $exam_id, $request, $subject_id, $record_id) {
        
        $SmMarkStore = SmMarkStore::query();
            $getMark = universityFilter($SmMarkStore, $request)
            ->where([
            ['member_id',$member_id], 
            ['exam_term_id',$exam_id],
            ['student_record_id', $record_id], 
            ['un_subject_id',$subject_id]
        ])->first();

        if($getMark != ""){
            $output= $getMark->teacher_remarks;
        }else{
            $output= '';
        }

        return $output;
    }

    public static function allMarksArray($exam_id, $age_group_id, $mgender_id, $subject_id)
    {
        $all_student_marks = [];

        $marks = SmResultStore::where('age_group_id', $age_group_id)->where('mgender_id', $mgender_id)->where('subject_id', $subject_id)->where('exam_type_id', $exam_id)->get();

        foreach($marks as $mark){
            $all_student_marks[] = $mark->total_marks;
        }


        return $all_student_marks;

    }

}
