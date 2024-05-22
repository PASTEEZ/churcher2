<?php

namespace App;

use App\SmStudent;
use App\SmExamSetup;
use App\SmMarkStore;
use App\SmMarksGrade;
use App\SmOptionalSubjectAssign;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;

class SmAssignSubject extends Model
{
    protected static function boot()
    {
        parent::boot();
  
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
    use HasFactory;
    public function subject()
    {
        return $this->belongsTo('App\SmSubject', 'subject_id', 'id')->withDefault()->withOutGlobalScopes();
    }

    public function results()
    {
        return $this->hasMany(SmResultStore::class, 'subject_id', 'subject_id');
    }
    public function resultBySubject()
    {
        return $this->hasMany(SmResultStore::class, 'subject_id', 'subject_id')->where('mgender_id', $this->mgender_id)
            ->where('age_group_id', $this->age_group_id);
    }

    public function class()
    {
        return $this->belongsTo('App\SmClass', 'age_group_id', 'id');
    }

    public function teacher()
    {
        return $this->belongsTo('App\SmStaff', 'teacher_id', 'id');
    }

    public function section()
    {
        return $this->belongsTo('App\SmSection', 'mgender_id', 'id');
    }

    public function examSetups()
    {
        return $this->hasMany(SmExamSetup::class, 'age_group_id', 'age_group_id')->where('age_group_id', $this->age_group_id)
            ->where('mgender_id', $this->mgender_id);
    }

    public function markBySubject()
    {
        return $this->hasMany(SmMarkStore::class, 'subject_id', 'subject_id')->where('mgender_id', $this->mgender_id)
            ->where('age_group_id', $this->age_group_id);
    }
    public function exam()
    {
        return $this->hasOne(SmExam::class,'subject_id','subject_id');
    }

    public function examSchedule()
    {
        return $this->hasMany(SmExamSchedule::class,'subject_id','subject_id')
            ->where('age_group_id', $this->age_group_id)->where('mgender_id', $this->mgender_id);
    }

    public static function getNumberOfPart($subject_id, $age_group_id=null, $mgender_id, $exam_term_id)
    {
        try {
            $results = SmExamSetup::where([
                ['age_group_id', $age_group_id],
                ['subject_id', $subject_id],
                ['mgender_id', $mgender_id],
                ['exam_term_id', $exam_term_id],
            ])->get();
            return $results;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function un_getNumberOfPart($subject_id, $exam_type, $request)
    {
        try {
            $SmExamSetup = SmExamSetup::query();
            $results = universityFilter($SmExamSetup, $request)
                            ->where([
                                ['un_subject_id', $subject_id],
                                ['exam_term_id', $exam_type],
                            ])
                            ->get();
            return $results;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function getNumberOfPartStudent($subject_id, $age_group_id, $mgender_id, $exam_term_id)
    {
        try {
            $results = SmExamSetup::where([
                ['age_group_id', $age_group_id],
                ['subject_id', $subject_id],
                ['mgender_id', $mgender_id],
                ['exam_term_id', $exam_term_id]
            ])->get();
            return $results;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function getMarksOfPart($member_id, $subject_id, $age_group_id, $mgender_id, $exam_term_id)
    {
        try {
            $results = SmMarkStore::where([
                ['member_id', $member_id],
                ['age_group_id', $age_group_id],
                ['subject_id', $subject_id],
                ['mgender_id', $mgender_id],
                ['exam_term_id', $exam_term_id],
            ])->get();
            return $results;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function un_getMarksOfPart($member_id, $subject_id, $request, $exam_term_id)
    {
        try {
            $SmMarkStore = SmMarkStore::query();
            $results = universityFilter($SmMarkStore, $request)
                    ->where([
                        ['member_id', $member_id],
                        ['un_subject_id', $subject_id],
                        ['exam_term_id', $exam_term_id],
                    ])->get();
            return $results;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function getSumMark($member_id, $subject_id, $age_group_id, $mgender_id, $exam_term_id)
    {
        try {
            $results = SmMarkStore::where([
                ['member_id', $member_id],
                ['age_group_id', $age_group_id],
                ['subject_id', $subject_id],
                ['mgender_id', $mgender_id],
                ['exam_term_id', $exam_term_id],
            ])->sum('total_marks');
            return $results;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function un_getSumMark($member_id, $subject_id, $request, $exam_term_id)
    {
        try {
            $SmMarkStore = SmMarkStore::query();
            $results = universityFilter($SmMarkStore, $request)
                    ->where([
                        ['member_id', $member_id],
                        ['un_subject_id', $subject_id],
                        ['exam_term_id', $exam_term_id],
                    ])->sum('total_marks');
            return $results;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function getHighestMark($subject_id, $age_group_id, $mgender_id, $exam_term_id)
    {
        try {
            $results = DB::table('sm_mark_stores')
                ->select('member_id', DB::raw('SUM(total_marks) as total_amount'))
                ->where([
                    ['age_group_id', $age_group_id],
                    ['subject_id', $subject_id],
                    ['mgender_id', $mgender_id],
                    ['exam_term_id', $exam_term_id]
                ])
                ->groupBy('member_id')
                ->get();
            $totalMark = [];
            foreach ($results as $result) {
                $totalMark[] = $result->total_amount;
            }
            return max($totalMark);
            $results = SmMarkStore::groupBy('member_id')
                ->selectRaw('sum(total_marks) as sum, member_id')
                ->where([
                    ['age_group_id', $age_group_id],
                    ['subject_id', $subject_id],
                    ['mgender_id', $mgender_id],
                    ['exam_term_id', $exam_term_id],
                ])
                ->select('sum', 'member_id');
            return $results;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function getSubjectMark($subject_id, $age_group_id, $mgender_id, $exam_term_id)
    {
        try {
            $results = SmExamSetup::where([
                ['age_group_id', $age_group_id],
                ['subject_id', $subject_id],
                ['mgender_id', $mgender_id],
                ['exam_term_id', $exam_term_id],
            ])->sum('exam_mark');
            return $results;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }


    public static function get_student_result($member_id, $subject_id, $age_group_id, $mgender_id, $exam_term_id, $optional_subject_id, $optional_subject_setup)
    {
        try {
            $this_student_failed = 0;
            $total_gpa_point = 0;
            $student_info = SmStudent::where('id', '=', $member_id)->first();
            $optional_subject = SmOptionalSubjectAssign::where('member_id', '=', $student_info->id)->where('session_id', '=', $student_info->session_id)->first();
            $subjects = SmAssignSubject::where([['age_group_id', $age_group_id], ['mgender_id', $mgender_id]])->get();
            $assign_subjects = SmAssignSubject::where([['age_group_id', $age_group_id], ['mgender_id', $mgender_id]])->get();
            foreach ($subjects as $row) {
                $subject_id = $row->subject_id;
                $total_mark = SmAssignSubject::getSumMark($member_id, $subject_id, $age_group_id, $mgender_id, $exam_term_id);
                $mark_grade = SmMarksGrade::where([['percent_from', '<=', $total_mark], ['percent_upto', '>=', $total_mark]])->first();
                $optional_subject_id = '';
                if (!empty($optional_subject)) {
                    $optional_subject_id = $optional_subject->subject_id;
                }
                if ($subject_id == $optional_subject_id) {

                    // return $optional_subject_id;
                    if ($mark_grade->gpa < $optional_subject_setup->gpa_above) {
                        $total_gpa_point = $total_gpa_point + 0;
                        if ($mark_grade->gpa < 1) {
                            $this_student_failed = 1;
                        }
                    } else {
                        $optional_mark_grade = $mark_grade->gpa - $optional_subject_setup->gpa_above;
                        $total_gpa_point = $total_gpa_point + $optional_mark_grade;
                        if ($mark_grade->gpa < 1) {
                            $this_student_failed = 1;
                        }
                    }
                } else {
                    $total_gpa_point = $total_gpa_point + $mark_grade->gpa;
                    if ($mark_grade->gpa < 1) {
                        $this_student_failed = 1;
                    }
                }
            }
            if ($this_student_failed != 1) {
                if ($optional_subject_id != '') {
                    $number_of_subject = count($assign_subjects);
                    $number_of_subject = $number_of_subject - 1;
                    if ($total_gpa_point != 0 && $number_of_subject != "") {
                        $final_result = number_format($total_gpa_point / $number_of_subject, 2, '.', ' ');
                        return $final_result;
                    } else {
                        return '0.00';
                    }
                } else {
                    $number_of_subject = count($assign_subjects);

                    if ($total_gpa_point != 0 && $number_of_subject != "") {
                        $final_result = number_format($total_gpa_point / $number_of_subject, 2, '.', ' ');
                        return $final_result;
                    } else {
                        return '0.00';
                    }
                }
            } else {
                return '0.00';
            }
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function get_student_result_without_optional($member_id, $subject_id, $age_group_id, $mgender_id, $exam_term_id, $optional_subject_id, $optional_subject_setup)
    {
        try {
            $this_student_failed = 0;
            $total_gpa_point = 0;
            $student_info = SmStudent::where('id', '=', $member_id)->first();
            $optional_subject = SmOptionalSubjectAssign::where('member_id', '=', $student_info->id)->where('session_id', '=', $student_info->session_id)->first();

            $subjects = SmAssignSubject::where([['age_group_id', $age_group_id], ['mgender_id', $mgender_id]])->get();
            $assign_subjects = SmAssignSubject::where([['age_group_id', $age_group_id], ['mgender_id', $mgender_id]])->get();
            foreach ($subjects as $row) {
                $subject_id = $row->subject_id;
                $total_mark = SmAssignSubject::getSumMark($member_id, $subject_id, $age_group_id, $mgender_id, $exam_term_id);
                $mark_grade = SmMarksGrade::where([['percent_from', '<=', $total_mark], ['percent_upto', '>=', $total_mark]])->first();
                $optional_subject_id = '';
                if (!empty($optional_subject)) {
                    $optional_subject_id = $optional_subject->subject_id;
                }
                $total_gpa_point = $total_gpa_point + $mark_grade->gpa;
                if ($mark_grade->gpa < 1) {
                    $this_student_failed = 1;
                }
            }
            if ($this_student_failed != 1) {
                if ($optional_subject_id != '') {

                    $number_of_subject = count($assign_subjects);
                    if ($total_gpa_point != 0 && $number_of_subject != "") {
                        $final_result = number_format($total_gpa_point / $number_of_subject, 2, '.', ' ');
                        return $final_result;
                    } else {
                        return '0.00';
                    }

                } else {
                    $number_of_subject = count($assign_subjects);

                    if ($total_gpa_point != 0 && $number_of_subject != "") {
                        $final_result = number_format($total_gpa_point / $number_of_subject, 2, '.', ' ');
                        return $final_result;
                    } else {
                        return '0.00';
                    }
                }

            } else {
                return '0.00';
            }
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function subjectPosition($subject_id, $age_group_id, $custom_result)
    {

        $students = SmStudent::where('age_group_id', $age_group_id)->get();

        $subject_mark_array = [];
        foreach ($students as $student) {
            $subject_marks = 0;

            $first_exam_mark = SmMarkStore::where('member_id', $student->id)->where('age_group_id', $age_group_id)->where('subject_id', $subject_id)->where('exam_term_id', $custom_result->exam_term_id1)->sum('total_marks');

            $subject_marks = $subject_marks + $first_exam_mark / 100 * $custom_result->percentage1;

            $second_exam_mark = SmMarkStore::where('member_id', $student->id)->where('age_group_id', $age_group_id)->where('subject_id', $subject_id)->where('exam_term_id', $custom_result->exam_term_id2)->sum('total_marks');

            $subject_marks = $subject_marks + $second_exam_mark / 100 * $custom_result->percentage2;

            $third_exam_mark = SmMarkStore::where('member_id', $student->id)->where('age_group_id', $age_group_id)->where('subject_id', $subject_id)->where('exam_term_id', $custom_result->exam_term_id3)->sum('total_marks');

            $subject_marks = $subject_marks + $third_exam_mark / 100 * $custom_result->percentage3;

            $subject_mark_array[] = round($subject_marks);


        }

        arsort($subject_mark_array);

        $position_array = [];
        foreach ($subject_mark_array as $position_mark) {
            $position_array[] = $position_mark;
        }


        return $position_array;

    }

    public function scopeStatus($query)
    {
        return $query->where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', auth()->user()->church_id);
    }
}
