<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use App\SmAssignSubject;
use App\SmGeneralSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class CustomResultSetting extends Model
{

    public function examTypeName(){
        return $this->belongsTo('App\SmExamType','exam_type_id','id');

    }
    public static function getGpa($marks){
        try {
            $marks_gpa=DB::table('sm_marks_grades')->where('percent_from','<=',$marks)->where('percent_upto','>=',$marks)->where('church_year_id', getAcademicId())->first();
            return $marks_gpa->gpa;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }
    public static function getDrade($marks){
        try {
            $marks_gpa=DB::table('sm_marks_grades')->where('percent_from','<=',$marks)->where('percent_upto','>=',$marks)->where('church_year_id', getAcademicId())->first();
            return $marks_gpa->grade_name;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }
    public static function gpaToGrade($gpa){
        try {
            $marks_gpa=DB::table('sm_marks_grades')->where('from','<=',$gpa)->where('up','>=',$gpa)->where('church_year_id', getAcademicId())->first();
                return $marks_gpa->grade_name;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }
    public static function termResult($exam_id,$age_group_id,$mgender_id,$member_id,$subject_count){
        try {
            $assigned_subject=SmAssignSubject::where('age_group_id',$age_group_id)->where('mgender_id',$mgender_id)->get();
            $mark_store=DB::table('sm_mark_stores')->where([['age_group_id', $age_group_id], ['mgender_id', $mgender_id], ['exam_term_id', $exam_id], ['member_id', $member_id]])->first();
            $subject_marks=[];
            $subject_gpas=[];
            foreach ($assigned_subject as $subject) {
                $subject_mark=DB::table('sm_mark_stores')->where([['age_group_id', $age_group_id], ['mgender_id', $mgender_id], ['exam_term_id', $exam_id], ['member_id', $member_id],['subject_id', $subject->subject_id]])->first();
                $custom_result = new CustomResultSetting;  // correct
                
                $subject_gpa=$custom_result->getGpa($subject_mark->total_marks);
                // return $subject_mark;
                $subject_marks[$subject->subject_id][0]= $subject_mark->total_marks;
                $subject_marks[$subject->subject_id][1]= $subject_gpa;
                $subject_gpas[$subject->subject_id]= $subject_gpa;
            }
            $total_gpa= array_sum($subject_gpas);
            $term_result=$total_gpa/$subject_count;
            return $term_result;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }
    public static function getSubjectGpa($age_group_id,$mgender_id,$exam_id,$member_id,$subject){
        try {
            $subject_marks=[];
            $subject_mark=DB::table('sm_mark_stores')->where('member_id', $member_id)
            ->where('exam_term_id','=', $exam_id)->first();
            
            $custom_result = new CustomResultSetting; 
            $subject_gpa=$custom_result->getGpa($subject_mark->total_marks);

            $subject_marks[$subject][0]= $subject_mark->total_marks;
            $subject_marks[$subject][1]= $subject_gpa;
            
            // return $subject_mark->total_marks;
            return $subject_marks;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }

    public static function getFinalResult($exam_id,$age_group_id,$mgender_id,$member_id,$percentage){
        try {
            $system_setting=SmGeneralSettings::where('id',auth()->user()->church_id)->first();
                $system_setting=$system_setting->session_id;
                $custom_result_setup=CustomResultSetting::where('church_year',$system_setting)->first();

                $assigned_subject=SmAssignSubject::where('age_group_id',$age_group_id)->where('mgender_id',$mgender_id)->get();

                $all_subjects_gpa=[];
                foreach ($assigned_subject as  $subject) {
                    $custom_result = new CustomResultSetting;
                    $subject_gpa=$custom_result->getSubjectGpa($exam_id,$age_group_id,$mgender_id,$member_id,$subject->subject_id);
                    $all_subjects_gpa[]=$subject_gpa[$subject->subject_id][1];
                }
                $percentage= $custom_result_setup->$percentage;
                $term_gpa= array_sum($all_subjects_gpa)/$assigned_subject->count();;
                $percentage= number_format((float)$percentage, 2, '.', '');
                $new_width = ($percentage / 100) * $term_gpa;
                return $new_width;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }
    public function scopeStatus($query){
        return $query->where('church_id', auth()->user()->church_id)->where('church_year_id',getAcademicId());
    }
}