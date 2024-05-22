<?php

namespace App\Http\Controllers\Admin\Report;

use App\SmExam;
use App\SmClass;
use App\SmExamSetting;
use App\SmSection;
use App\SmStudent;
use App\YearCheck;
use App\SmExamType;
use App\SmExamSetup;
use App\SmMarkStore;
use App\SmMarksGrade;
use App\ApiBaseMethod;
use App\SmResultStore;
use App\SmAssignSubject;
use App\CustomResultSetting;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\SmClassOptionalSubject;
use App\SmOptionalSubjectAssign;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\University\Http\Controllers\ExamCommonController;
use App\Http\Controllers\Admin\StudentInfo\SmStudentReportController;
use App\Http\Requests\Admin\Examination\ProgressCardReportRequest;
use App\Http\Requests\Admin\Examination\TabulationSheetReportRequest;

class SmReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
        // User::checkAuth();
    }

    public function tabulationSheetReport(Request $request)
    {
        try {
            $exam_types = SmExamType::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['exam_types'] = $exam_types->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.reports.tabulation_sheet_report', compact('exam_types', 'classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function tabulationSheetReportSearch(TabulationSheetReportRequest $request)
    {
        try {
            if (moduleStatusCheck('University')) {
                $common = new ExamCommonController();
                return $common->tabulationReportSearch($request);
            } else {
                if (!$request->student) {
                    $allClass = 0;
                    $exam_term_id = $request->exam;
                    $age_group_id = $request->class;
                    $mgender_id = $request->section;

                    $exam_content = SmExamSetting::where('exam_type', $exam_term_id)
                        ->where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->first();

                    $exam_types = SmExamType::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->get();

                    $classes = SmClass::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->get();


                    $marks = SmMarkStore::where([
                        ['exam_term_id', $exam_term_id],
                        ['age_group_id', $age_group_id],
                        ['mgender_id', $mgender_id],
                    ])->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->get();

                    $grade_chart = SmMarksGrade::select('grade_name', 'gpa', 'percent_from as start', 'percent_upto as end', 'description')
                        ->where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->orderBy('gpa', 'desc')
                        ->get()
                        ->toArray();
                    $single_exam_term = SmExamType::find($request->exam);
                    $className = SmClass::find($request->class);
                    $sectionName = SmSection::find($request->section);

                    $tabulation_details['exam_term'] = $single_exam_term->title;
                    $tabulation_details['class'] = $className->age_group_name;
                    $tabulation_details['section'] = $sectionName->mgender_name;
                    $tabulation_details['grade_chart'] = $grade_chart;
                    $year = YearCheck::getYear();

                    $examSubjects = SmExam::where([['exam_type_id', $exam_term_id], ['mgender_id', $mgender_id], ['age_group_id', $age_group_id]])
                        ->where('church_id', Auth::user()->church_id)
                        ->where('church_year_id', getAcademicId())
                        ->get();

                    $examSubjectIds = [];
                    foreach ($examSubjects as $examSubject) {
                        $examSubjectIds[] = $examSubject->subject_id;
                    }

                    $subjects = SmAssignSubject::where([
                        ['age_group_id', $request->class],
                        ['mgender_id', $request->section]
                    ])->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->whereIn('subject_id', $examSubjectIds)
                        ->get();

                    $optional_subject_setup = SmClassOptionalSubject::where('age_group_id', '=', $request->class)->first();
                    $member_ids = SmStudentReportController::classSectionStudent($request);
                    $students = SmStudent::whereIn('id', $member_ids)
                        ->where('church_id', Auth::user()->church_id)
                        ->get()->sortBy('roll_no');
                    if(!$students->count()){
                        Toastr::error(__('common.no_student_found'));
                        return redirect()->route('tabulation_sheet_report');
                    }

                    $max_grade = SmMarksGrade::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->max('gpa');

                    $fail_grade = SmMarksGrade::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->min('gpa');

                    $fail_grade_name = SmMarksGrade::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->where('gpa', $fail_grade)
                        ->first();


                    return view('backEnd.reports.tabulation_sheet_report', compact('allClass',
                        'exam_types',
                        'classes',
                        'marks',
                        'tabulation_details',
                        'year',
                        'subjects',
                        'optional_subject_setup',
                        'exam_term_id',
                        'age_group_id',
                        'mgender_id',
                        'students',
                        'max_grade',
                        'fail_grade_name',
                    'exam_content'
                    ));

                } else {
                    $exam_term_id = $request->exam;
                    $age_group_id = $request->class;
                    $mgender_id = $request->section;
                    $member_id = $request->student;
                    $exam_content = SmExamSetting::where('exam_type', $exam_term_id)
                        ->where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->first();

                    $optional_subject_setup = SmClassOptionalSubject::where('age_group_id', '=', $request->class)->first();

                    $fail_grade = SmMarksGrade::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->min('gpa');

                    $fail_grade_name = SmMarksGrade::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->where('gpa', $fail_grade)
                        ->first();

                    $max_grade = SmMarksGrade::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->max('gpa');


                    $examSubjects = SmExam::where([['exam_type_id', $exam_term_id], ['mgender_id', $mgender_id], ['age_group_id', $age_group_id]])
                        ->where('church_id', Auth::user()->church_id)
                        ->where('church_year_id', getAcademicId())
                        ->get();

                    $examSubjectIds = [];
                    foreach ($examSubjects as $examSubject) {
                        $examSubjectIds[] = $examSubject->subject_id;
                    }


                    $student_detail = $studentDetails = StudentRecord::where('member_id', $request->student)->where('age_group_id', $age_group_id)->where('mgender_id', $mgender_id)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->first();

                    $subjects = $studentDetails->class->subjects->whereIn('subject_id', $examSubjectIds)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id);

                    $year = YearCheck::getYear();

                    $optional_subject_mark = '';

                    $get_optional_subject = SmOptionalSubjectAssign::where('member_id', '=', $student_detail->member_id)
                        ->where('session_id', '=', $student_detail->session_id)
                        ->first();

                    if ($get_optional_subject != '') {
                        $optional_subject_mark = $get_optional_subject->subject_id;
                    }

                    $mark_sheet = SmResultStore::where([['age_group_id', $request->class], ['exam_type_id', $request->exam], ['mgender_id', $request->section], ['member_id', $request->student]])
                        ->whereIn('subject_id', $subjects->pluck('subject_id')
                            ->toArray())
                        ->where('church_id', Auth::user()->church_id)
                        ->get();

                    if ($request->student == "") {
                        $eligible_subjects = SmAssignSubject::where('age_group_id', $age_group_id)->where('mgender_id', $mgender_id)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
                        $eligible_students = SmStudent::where('age_group_id', $age_group_id)->where('mgender_id', $mgender_id)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
                        foreach ($eligible_students as $SingleStudent) {
                            foreach ($eligible_subjects as $subject) {
                                $getMark = SmResultStore::where([
                                    ['exam_type_id', $exam_term_id],
                                    ['age_group_id', $age_group_id],
                                    ['mgender_id', $mgender_id],
                                    ['member_id', $SingleStudent->id],
                                    ['subject_id', $subject->subject_id]
                                ])->first();
                                if ($getMark == "") {
                                    Toastr::error('Please register marks for all students.!', 'Failed');
                                    return redirect()->back();
                                    // return redirect()->back()->with('message-danger', 'Please register marks for all students.!');
                                }
                            }
                        }
                    } else {
                        $eligible_subjects = SmAssignSubject::where('age_group_id', $age_group_id)
                            ->where('mgender_id', $mgender_id)
                            ->where('church_year_id', getAcademicId())
                            ->where('church_id', Auth::user()->church_id)
                            ->get();

                        foreach ($eligible_subjects as $subject) {


                            $getMark = SmResultStore::where([
                                ['exam_type_id', $exam_term_id],
                                ['age_group_id', $age_group_id],
                                ['mgender_id', $mgender_id],
                                ['member_id', $request->student]
                            ])->first();


                            if ($getMark == "") {
                                Toastr::error('Please register marks for all students.!', 'Failed');
                                return redirect()->back();
                                // return redirect()->back()->with('message-danger', 'Please register marks for all students.!');
                            }
                        }
                    }

                    if ($request->student != '') {
                        $marks = SmMarkStore::where([
                            ['exam_term_id', $request->exam],
                            ['age_group_id', $request->class],
                            ['mgender_id', $request->section],
                            ['member_id', $request->student]
                        ])->where('church_year_id', getAcademicId())
                            ->where('church_id', Auth::user()->church_id)
                            ->get();

                        $students = SmStudent::where('id', $request->student)
                            ->where('church_id', Auth::user()->church_id)
                            ->get();

                        $subjects = SmAssignSubject::where([
                            ['age_group_id', $request->class],
                            ['mgender_id', $request->section]
                        ])->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)
                            ->whereIn('subject_id', $examSubjectIds)
                            ->get();


                        foreach ($subjects as $sub) {
                            $subject_list_name[] = $sub->subject->subject_name;
                        }

                        $grade_chart = SmMarksGrade::select('grade_name', 'gpa', 'percent_from as start', 'percent_upto as end', 'description')
                            ->where('active_status', 1)
                            ->where('church_year_id', getAcademicId())
                            ->where('church_id', Auth::user()->church_id)
                            ->orderBy('gpa', 'desc')
                            ->get()
                            ->toArray();

                        $single_student = StudentRecord::where('member_id', $request->student)
                            ->where('age_group_id', $request->class)
                            ->where('mgender_id', $request->section)
                            ->where('church_year_id', getAcademicId())
                            ->where('church_id', Auth::user()->church_id)->first();
                        $single_exam_term = SmExamType::find($request->exam);

                        $tabulation_details['member_name'] = $single_student->studentDetail->full_name;
                        $tabulation_details['student_roll'] = $single_student->roll_no;
                        $tabulation_details['member_registration_no'] = $single_student->studentDetail->registration_no;
                        $tabulation_details['member_group'] = $single_student->Class->age_group_name;
                        $tabulation_details['member_gender'] = $single_student->section->mgender_name;
                        $tabulation_details['exam_term'] = $single_exam_term->title;
                        $tabulation_details['subject_list'] = $subject_list_name;
                        $tabulation_details['grade_chart'] = $grade_chart;
                        $tabulation_details['record_id'] = $single_student->id;
                    } else {
                        $marks = SmMarkStore::where([
                            ['exam_term_id', $request->exam],
                            ['age_group_id', $request->class],
                            ['mgender_id', $request->section]
                        ])->where('church_year_id', getAcademicId())
                            ->where('church_id', Auth::user()->church_id)
                            ->get();
                        $students = SmStudent::where('id', $request->student)->where('church_year_id', getAcademicId())
                            ->where('church_id', Auth::user()->church_id)
                            ->get();
                    }


                    $exam_types = SmExamType::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->get();

                    $classes = SmClass::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->get();

                    $single_class = SmClass::find($request->class);
                    $single_section = SmSection::find($request->section);
                    $subjects = SmAssignSubject::where([
                        ['age_group_id', $request->class],
                        ['mgender_id', $request->section]
                    ])
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->whereIn('subject_id', $examSubjectIds)
                        ->get();


                    foreach ($subjects as $sub) {
                        $subject_list_name[] = $sub->subject->subject_name;
                    }
                    $grade_chart = SmMarksGrade::select('grade_name', 'gpa', 'percent_from as start', 'percent_upto as end', 'description')
                        ->where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->orderBy('gpa', 'desc')
                        ->get()
                        ->toArray();

                    $single_exam_term = SmExamType::find($request->exam);

                    $tabulation_details['member_group'] = $single_class->age_group_name;
                    $tabulation_details['member_gender'] = $single_section->mgender_name;
                    $tabulation_details['exam_term'] = $single_exam_term->title;
                    $tabulation_details['subject_list'] = $subject_list_name;
                    $tabulation_details['grade_chart'] = $grade_chart;

                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        $data = [];
                        $data['exam_types'] = $exam_types->toArray();
                        $data['classes'] = $classes->toArray();
                        $data['marks'] = $marks->toArray();
                        $data['subjects'] = $subjects->toArray();
                        $data['exam_term_id'] = $exam_term_id;
                        $data['age_group_id'] = $age_group_id;
                        $data['mgender_id'] = $mgender_id;
                        $data['students'] = $students->toArray();
                        return ApiBaseMethod::sendResponse($data, null);
                    }
                    $get_class = SmClass::where('active_status', 1)
                        ->where('id', $request->class)
                        ->first();

                    $get_section = SmSection::where('active_status', 1)
                        ->where('id', $request->section)
                        ->first();
                    $single = 0;

                    $age_group_name = $get_class->age_group_name;
                    $mgender_name = $get_section->mgender_name;
                    return view('backEnd.reports.tabulation_sheet_report',
                        compact('optional_subject_setup',
                            'exam_types',
                            'classes',
                            'marks',
                            'subjects',
                            'exam_term_id',
                            'age_group_id',
                            'mgender_id',
                            'age_group_name',
                            'mgender_name',
                            'students',
                            'member_id',
                            'tabulation_details',
                            'max_grade',
                            'optional_subject_mark',
                            'mark_sheet',
                            'fail_grade_name',
                            'year',
                            'single',
                        'exam_content'
                        )
                    );
                }
            }
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    //tabulationSheetReportPrint
    public function tabulationSheetReportPrint(Request $request)
    {

        try {
            if (moduleStatusCheck('University')) {
                $common = new ExamCommonController();
                return $common->tabulationReportSearchPrint((object)$request->all());
            } else {
                $member_ids = StudentRecord::when($request->church_year, function ($query) use ($request) {
                    $query->where('church_year_id', $request->church_year);
                })
                    ->when($request->age_group_id, function ($query) use ($request) {
                        $query->where('age_group_id', $request->age_group_id);
                    })
                    ->when($request->mgender_id, function ($query) use ($request) {
                        $query->where('mgender_id', $request->mgender_id);
                    })
                    ->when(!$request->church_year, function ($query) use ($request) {
                        $query->where('church_year_id', getAcademicId());
                    })->where('church_id', auth()->user()->church_id)->where('is_promote', 0)->pluck('member_id')->unique();

                if ($request->allSection == "allSection") {
                    $exam_term_id = $request->exam_term_id;
                    $age_group_id = $request->age_group_id;
                    $mgender_id = $request->mgender_id;
                    $exam_content = SmExamSetting::where('exam_type', $exam_term_id)
                        ->where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->first();
                    $fail_grade = SmMarksGrade::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->min('gpa');

                    $fail_grade_name = SmMarksGrade::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->where('gpa', $fail_grade)
                        ->first();

                    $max_grade = SmMarksGrade::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->max('gpa');

                    $grade_chart = SmMarksGrade::select('grade_name', 'gpa', 'percent_from as start', 'percent_upto as end', 'description')
                        ->where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->orderBy('gpa', 'desc')
                        ->get()
                        ->toArray();

                    $students = SmStudent::whereIn('id', $member_ids)
                        ->where('church_id', Auth::user()->church_id)
                        ->get()->sortBy('roll_no');

                    $examSubjects = SmExam::where([['exam_type_id', $exam_term_id], ['mgender_id', $mgender_id], ['age_group_id', $age_group_id]])
                        ->where('church_id', Auth::user()->church_id)
                        ->where('church_year_id', getAcademicId())
                        ->get();

                    $examSubjectIds = [];
                    foreach ($examSubjects as $examSubject) {
                        $examSubjectIds[] = $examSubject->subject_id;
                    }

                    $subjects = SmAssignSubject::where([
                        ['age_group_id', $request->age_group_id],
                        ['mgender_id', $request->mgender_id]
                    ])->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->whereIn('subject_id', $examSubjectIds)
                        ->get();

                    $optional_subject_setup = SmClassOptionalSubject::where('age_group_id', '=', $request->age_group_id)->first();

                    $single_exam_term = SmExamType::find($exam_term_id);
                    $className = SmClass::find($age_group_id);
                    $sectionName = SmSection::find($mgender_id);
                    $year = YearCheck::getYear();

                    $tabulation_details['exam_term'] = $single_exam_term->title;
                    $tabulation_details['class'] = $className->age_group_name;
                    $tabulation_details['section'] = $sectionName->mgender_name;
                    $tabulation_details['grade_chart'] = $grade_chart;

                    $optional_subject_mark = '';

                    foreach ($students as $student) {
                        $get_optional_subject = SmOptionalSubjectAssign::where('member_id', $student->id)
                            ->where('session_id', '=', $student->session_id)
                            ->first();
                    }

                    if ($get_optional_subject != '') {
                        $optional_subject_mark = $get_optional_subject->subject_id;
                    }

                    $mark_sheet = SmResultStore::where([['age_group_id', $request->age_group_id], ['exam_type_id', $request->exam_term_id], ['mgender_id', $request->mgender_id]])
                        ->whereIn('subject_id', $subjects->pluck('subject_id')
                            ->toArray())
                        ->where('church_id', Auth::user()->church_id)
                        ->get();

                    $allClass = 0;
                    $year = YearCheck::getYear();


                    return view('backEnd.reports.tabulation_sheet_report_print',
                        compact('allClass',
                            'exam_term_id',
                            'age_group_id',
                            'mgender_id',
                            'fail_grade',
                            'max_grade',
                            'fail_grade_name',
                            'tabulation_details',
                            'year',
                            'students',
                            'subjects',
                            'optional_subject_setup',
                            'optional_subject_mark',
                            'mark_sheet',
                        'exam_content'
                        ));
                } else {
                    $exam_term_id = $request->exam_term_id;
                    $age_group_id = $request->age_group_id;
                    $mgender_id = $request->mgender_id;
                    $member_id = $request->member_id;
                    $exam_content = SmExamSetting::where('exam_type', $exam_term_id)
                        ->where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->first();


                    $examSubjects = SmExam::where([['exam_type_id', $exam_term_id], ['mgender_id', $mgender_id], ['age_group_id', $age_group_id]])
                        ->where('church_id', Auth::user()->church_id)
                        ->where('church_year_id', getAcademicId())
                        ->get();

                    $examSubjectIds = [];
                    foreach ($examSubjects as $examSubject) {
                        $examSubjectIds[] = $examSubject->subject_id;
                    }

                    $subjects = SmAssignSubject::where([
                        ['age_group_id', $request->age_group_id],
                        ['mgender_id', $request->mgender_id]
                    ])->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->whereIn('subject_id', $examSubjectIds)
                        ->get();

                    $optional_subject_setup = SmClassOptionalSubject::where('age_group_id', '=', $request->age_group_id)->first();

                    $fail_grade = SmMarksGrade::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->min('gpa');

                    $fail_grade_name = SmMarksGrade::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->where('gpa', $fail_grade)
                        ->first();

                    $max_grade = SmMarksGrade::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->max('gpa');

                    $student_detail = $studentDetails = StudentRecord::where('member_id', $request->member_id)
                        ->where('age_group_id', $request->age_group_id)
                        ->where('mgender_id', $request->mgender_id)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->first();

                    $subjects_optional = $studentDetails->class->subjects->where('mgender_id', $request->mgender_id)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id);

                    $optional_subject_mark = '';

                    $get_optional_subject = SmOptionalSubjectAssign::where('member_id', '=', $student_detail->id)
                        ->where('session_id', '=', $student_detail->session_id)
                        ->first();

                    if ($get_optional_subject != '') {
                        $optional_subject_mark = $get_optional_subject->subject_id;
                    }

                    $mark_sheet = SmResultStore::where([['age_group_id', $request->age_group_id], ['exam_type_id', $request->exam_term_id], ['mgender_id', $request->mgender_id], ['member_id', $request->member_id]])
                        ->whereIn('subject_id', $subjects->pluck('subject_id')
                            ->toArray())
                        ->where('church_id', Auth::user()->church_id)
                        ->get();

                    if (!empty($request->member_id)) {

                        $marks = SmMarkStore::where([
                            ['exam_term_id', $request->exam_term_id],
                            ['age_group_id', $request->age_group_id],
                            ['mgender_id', $request->mgender_id],
                            ['member_id', $request->member_id]
                        ])
                            ->where('church_year_id', getAcademicId())
                            ->where('church_id', Auth::user()->church_id)
                            ->get();

                        $students = SmStudent::where('id', $request->member_id)
                            ->where('church_id', Auth::user()->church_id)
                            ->get();

                        $single_class = SmClass::find($request->age_group_id);
                        $single_section = SmSection::find($request->mgender_id);
                        $single_exam_term = SmExamType::find($request->exam_term_id);
                        $subject_list_name = [];

                        foreach ($subjects as $sub) {
                            $subject_list_name[] = $sub->subject->subject_name;
                        }

                        $grade_chart = SmMarksGrade::select('grade_name', 'gpa', 'percent_from as start', 'percent_upto as end', 'description')
                            ->where('active_status', 1)
                            ->where('church_year_id', getAcademicId())
                            ->where('church_id', Auth::user()->church_id)
                            ->orderBy('gpa', 'desc')
                            ->get()
                            ->toArray();


                        $single_student = StudentRecord::where('member_id', $request->member_id)
                            ->where('age_group_id', $request->age_group_id)
                            ->where('mgender_id', $request->mgender_id)
                            ->where('church_year_id', getAcademicId())
                            ->where('church_id', Auth::user()->church_id)->first();
                        $single_exam_term = SmExamType::find($request->exam_term_id);
                        $tabulation_details['member_name'] = $single_student->studentDetail->full_name;
                        $tabulation_details['student_roll'] = $single_student->roll_no;
                        $tabulation_details['member_registration_no'] = $single_student->studentDetail->registration_no;
                        $tabulation_details['member_group'] = $single_student->Class->age_group_name;
                        $tabulation_details['member_gender'] = $single_student->section->mgender_name;
                        $tabulation_details['exam_term'] = $single_exam_term->title;
                        $tabulation_details['subject_list'] = $subject_list_name;
                        $tabulation_details['grade_chart'] = $grade_chart;
                        $tabulation_details['record_id'] = $student_detail->id;
                    } else {
                        $marks = SmMarkStore::where([
                            ['exam_term_id', $request->exam_term_id],
                            ['age_group_id', $request->age_group_id],
                            ['mgender_id', $request->mgender_id]
                        ])
                            ->where('church_year_id', getAcademicId())
                            ->where('church_id', Auth::user()->church_id)
                            ->get();

                        $students = SmStudent::whereIn('id', $member_ids)->where('church_year_id', getAcademicId())
                            ->where('church_id', Auth::user()->church_id)
                            ->get();
                    }

                    $exam_types = SmExamType::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
                    $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();

                    foreach ($subjects as $sub) {
                        $subject_list_name[] = $sub->subject->subject_name;
                    }
                    $grade_chart = SmMarksGrade::select('grade_name', 'gpa', 'percent_from as start', 'percent_upto as end', 'description')
                        ->where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->orderBy('gpa', 'desc')
                        ->get()
                        ->toArray();

                    $tabulation_details['member_group'] = $single_class->age_group_name;
                    $tabulation_details['member_gender'] = $single_section->mgender_name;
                    $tabulation_details['exam_term'] = $single_exam_term->title;
                    $tabulation_details['subject_list'] = $subject_list_name;
                    $tabulation_details['grade_chart'] = $grade_chart;


                    $get_class = SmClass::where('active_status', 1)
                        ->where('id', $request->age_group_id)
                        ->first();

                    $get_section = SmSection::where('active_status', 1)
                        ->where('id', $request->mgender_id)
                        ->first();

                    $age_group_name = $get_class->age_group_name;
                    $mgender_name = $get_section->mgender_name;

                    $customPaper = array(0, 0, 700.00, 1500.80);
                    $single = 0;
                    $year = YearCheck::getYear();

                    return view('backEnd.reports.tabulation_sheet_report_print',
                        compact('optional_subject_setup',
                            'exam_types',
                            'classes',
                            'marks',
                            'subjects',
                            'exam_term_id',
                            'age_group_id',
                            'mgender_id',
                            'age_group_name',
                            'mgender_name',
                            'students',
                            'member_id',
                            'tabulation_details',
                            'max_grade',
                            'fail_grade_name',
                            'optional_subject_mark',
                            'mark_sheet',
                            'subjects_optional',
                            'single',
                            'year',
                        'exam_content'
                        ));
                }
            }
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function progressCardReport(Request $request)
    {
        try {
            $exams = SmExam::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['routes'] = $exams->toArray();
                $data['assign_vehicles'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.reports.progress_card_report', compact('exams', 'classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    //student progress report search by Amit
    public function progressCardReportSearch(ProgressCardReportRequest $request)
    {
        try {
            if (moduleStatusCheck('University')) {
                $common = new ExamCommonController();
                return $common->progressCardReportSearch((object)$request->all());
            } else {
                $exam_content = SmExamSetting::whereNull('exam_type')
                    ->where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->first();
                $exams = SmExam::where('active_status', 1)
                    ->where('age_group_id', $request->class)
                    ->where('mgender_id', $request->section)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->get();

                $exam_types = SmExamType::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->pluck('id');


                $classes = SmClass::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->get();

                $fail_grade = SmMarksGrade::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->min('gpa');

                $fail_grade_name = SmMarksGrade::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->where('gpa', $fail_grade)
                    ->first();

                $studentDetails = StudentRecord::where('member_id', $request->student)
                    ->where('age_group_id', $request->class)
                    ->where('mgender_id', $request->section)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->first();

                $marks_grade = SmMarksGrade::where('church_id', Auth::user()->church_id)
                    ->where('church_year_id', getAcademicId())
                    ->orderBy('gpa', 'desc')
                    ->get();

                $maxGrade = SmMarksGrade::where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->max('gpa');

                $optional_subject_setup = SmClassOptionalSubject::where('age_group_id', '=', $request->class)
                    ->first();

                $student_optional_subject = SmOptionalSubjectAssign::where('member_id', $request->student)
                    ->where('session_id', '=', $studentDetails->session_id)
                    ->first();

                $exam_setup = SmExamSetup::where([
                    ['age_group_id', $request->class],
                    ['mgender_id', $request->section]])
                    ->where('church_id', Auth::user()->church_id)
                    ->get();

                $age_group_id = $request->class;
                $mgender_id = $request->section;
                $member_id = $request->student;

                $examSubjects = SmExam::where([['mgender_id', $mgender_id], ['age_group_id', $age_group_id]])
                    ->where('church_id', Auth::user()->church_id)
                    ->where('church_year_id', getAcademicId())
                    ->get();

                $examSubjectIds = [];
                foreach ($examSubjects as $examSubject) {
                    $examSubjectIds[] = $examSubject->subject_id;
                }

                $subjects = SmAssignSubject::where([
                    ['age_group_id', $request->class],
                    ['mgender_id', $request->section]])
                    ->where('church_id', Auth::user()->church_id)
                    ->whereIn('subject_id', $examSubjectIds)
                    ->get();

                $assinged_exam_types = [];
                foreach ($exams as $exam) {
                    $assinged_exam_types[] = $exam->exam_type_id;
                }
                $assinged_exam_types = array_unique($assinged_exam_types);


                foreach ($assinged_exam_types as $assinged_exam_type) {
                    if($request->custom_mark_report != 'custom_mark_report'){
                        $is_percentage = CustomResultSetting::where('exam_type_id', $assinged_exam_type)
                            ->where('church_id', Auth::user()->church_id)
                            ->where('church_year_id', getAcademicId())
                            ->first();

                        if (is_null($is_percentage)) {
                            Toastr::error('Please Complete Exam Result Settings .', 'Failed');
                            return redirect('custom-result-setting');
                        }
                    }

                    foreach ($subjects as $subject) {
                        $is_mark_available = SmResultStore::where([
                            ['age_group_id', $request->class],
                            ['mgender_id', $request->section],
                            ['member_id', $request->student]
                            // ['exam_type_id', $assinged_exam_type]]
                        ])
                            ->first();
                        if ($is_mark_available == "") {
                            Toastr::error('Ops! Your result is not found! Please check mark register.', 'Failed');
                            return redirect('progress-card-report');

                        }
                    }
                }
                $is_result_available = SmResultStore::where([
                    ['age_group_id', $request->class],
                    ['mgender_id', $request->section],
                    ['member_id', $request->student]])
                    ->where('church_id', Auth::user()->church_id)
                    ->get();

                $custom_mark_report = $request->custom_mark_report ?? null;

                if ($is_result_available->count() > 0) {
                    if($request->custom_mark_report == 'custom_mark_report'){
                        $view = 'backEnd.reports.custom_percent_progress_card_report';
                    }else{
                        $view = 'backEnd.reports.progress_card_report';
                    }
                    return view($view,
                        compact(
                            'exams',
                            'optional_subject_setup',
                            'student_optional_subject',
                            'classes', 'studentDetails',
                            'is_result_available',
                            'subjects',
                            'age_group_id',
                            'mgender_id',
                            'member_id',
                            'exam_types',
                            'assinged_exam_types',
                            'marks_grade',
                            'fail_grade_name',
                            'fail_grade',
                            'maxGrade',
                            'custom_mark_report',
                            'exam_content'
                        ));

                } else {
                    Toastr::error('Ops! Your result is not found! Please check mark register.', 'Failed');
                    return redirect('progress-card-report');
                }
            }
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function progressCardPrint(Request $request)
    {
        $church_year_id = $request->church_year_id ?? getAcademicId();
        try {
            if (moduleStatusCheck('University')) {
                $common = new ExamCommonController();
                return $common->progressCardReportPrint((object)$request->all());
            } else {
                $exam_content = SmExamSetting::withOutGlobalScopes()->whereNull('exam_type')
                    ->where('active_status', 1)
                    ->where('church_year_id', $church_year_id)
                    ->where('church_id', Auth::user()->church_id)
                    ->first();
                $exams = SmExam::withOutGlobalScopes()->where('active_status', 1)
                    ->where('age_group_id', $request->age_group_id)
                    ->where('mgender_id', $request->mgender_id)
                    ->where('church_year_id', $church_year_id)
                    ->where('church_id', Auth::user()->church_id)
                    ->get();

                $exam_types = SmExamType::withOutGlobalScopes()->where('active_status', 1)
                    ->where('church_year_id', $church_year_id)
                    ->where('church_id', Auth::user()->church_id)
                    ->get();

                $classes = SmClass::withOutGlobalScopes()->where('active_status', 1)
                    ->where('church_year_id', $church_year_id)
                    ->where('church_id', Auth::user()->church_id)
                    ->get();

                $marks_grade = SmMarksGrade::withOutGlobalScopes()->where('church_id', Auth::user()->church_id)
                    ->where('church_year_id', $church_year_id)
                    ->orderBy('gpa', 'desc')
                    ->get();

                $fail_grade = SmMarksGrade::withOutGlobalScopes()->where('active_status', 1)
                    ->where('church_year_id', $church_year_id)
                    ->where('church_id', Auth::user()->church_id)
                    ->min('gpa');

                $fail_grade_name = SmMarksGrade::withOutGlobalScopes()->where('active_status', 1)
                    ->where('church_year_id', $church_year_id)
                    ->where('church_id', Auth::user()->church_id)
                    ->where('gpa', $fail_grade)
                    ->first();

                $exam_setup = SmExamSetup::where([
                    ['age_group_id', $request->age_group_id],
                    ['mgender_id', $request->mgender_id]
                ])
                    ->where('church_year_id', $church_year_id)
                    ->where('church_id', Auth::user()->church_id)
                    ->get();

                $member_id = $request->member_id;
                $age_group_id = $request->age_group_id;
                $mgender_id = $request->mgender_id;

                $student_detail = StudentRecord::where('id', $member_id)
                    ->where('church_id', Auth::user()->church_id)
                    ->where('church_year_id', $church_year_id)
                    ->first();


                $examSubjects = SmExam::withOutGlobalScopes()->where([['mgender_id', $mgender_id], ['age_group_id', $age_group_id]])
                    ->where('church_id', Auth::user()->church_id)
                    ->where('church_year_id', $church_year_id)
                    ->get();

                $examSubjectIds = [];
                foreach ($examSubjects as $examSubject) {
                    $examSubjectIds[] = $examSubject->subject_id;
                }

                $assinged_exam_types = [];
                foreach ($exams as $exam) {
                    $assinged_exam_types[] = $exam->exam_type_id;
                }

                $subjects = SmAssignSubject::withOutGlobalScopes()->where([
                    ['age_group_id', $request->age_group_id],
                    ['mgender_id', $request->mgender_id]])
                    ->where('church_id', Auth::user()->church_id)
                    ->whereIn('subject_id', $examSubjectIds)
                    ->get();

                $assinged_exam_types = array_unique($assinged_exam_types);
                foreach ($assinged_exam_types as $assinged_exam_type) {
                    foreach ($subjects as $subject) {
                        $is_mark_available = SmResultStore::where([
                            ['age_group_id', $request->age_group_id],
                            ['mgender_id', $request->mgender_id],
                            ['student_record_id', $member_id],
                            ['subject_id', $subject->subject_id],
                            // ['exam_type_id', $assinged_exam_type]
                        ])
                            ->where('church_year_id', $church_year_id)
                            ->first();
                        if ($is_mark_available == "") {
                            Toastr::error('Ops! Your result is not found! Please check mark register.', 'Failed');
                            return redirect('progress-card-report');
                            // return redirect('progress-card-report')->with('message-danger', 'Ops! Your result is not found! Please check mark register.');
                        }
                    }
                }
                $is_result_available = SmResultStore::where([
                    ['age_group_id', $request->age_group_id],
                    ['mgender_id', $request->mgender_id],
                    ['student_record_id', $member_id]
                ])
                    ->where('church_year_id', $church_year_id)
                    ->where('church_id', auth()->user()->church_id)
                    ->get();

                $optional_subject_setup = SmClassOptionalSubject::where('age_group_id', '=', $request->age_group_id)->first();

                $student_optional_subject = SmOptionalSubjectAssign::where('member_id', $member_id)->where('church_year_id', '=', $student_detail->church_year_id)->first();
                //    return $student_optional_subject;
                // $studentDetails = SmStudent::where('sm_students.id', $request->student)
                // $studentDetails = SmStudent::where('sm_students.id', $request->student)
                if($request->custom_mark_report == 'custom_mark_report'){
                    $view = 'backEnd.reports.custom_percent_progress_card_report_print';
                }else{
                    $view = 'backEnd.reports.progress_card_report_print';
                }
                return view($view,
                    compact(
                        'optional_subject_setup',
                        'student_optional_subject',
                        'exams',
                        'classes',
                        'student_detail',
                        'is_result_available',
                        'subjects',
                        'age_group_id',
                        'mgender_id',
                        'member_id',
                        'exam_types',
                        'assinged_exam_types',
                        'marks_grade',
                        'fail_grade_name',
                        'exam_content'
                    )
                );

                // $customPaper = array(0, 0, 700.00, 1500.80);

                // $pdf = PDF::loadView(
                //     'backEnd.reports.progress_card_report_print',
                //     [
                //         'optional_subject_setup'=> $optional_subject_setup ,
                //         'student_optional_subject'=> $student_optional_subject,
                //         'exams'    => $exams,
                //         'classes'       => $classes,
                //         'student_detail'         => $student_detail,
                //         'is_result_available'         => $is_result_available,
                //         'subjects'         => $subjects,
                //         'age_group_id'         => $age_group_id,
                //         'mgender_id'         => $mgender_id,
                //         'member_id'         => $member_id,
                //         'exam_types'         => $exam_types,
                //         'assinged_exam_types'         => $assinged_exam_types,
                //         'marks_grade'         => $marks_grade,
                //         'studentDetails'         => $studentDetails,
                //         'fail_grade_name' => $fail_grade_name,
                //     ]
                // )->setPaper('A4', 'portrait');
                // return $pdf->stream('progressCardReportPrint.pdf');
            }
        }
        catch
        (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function customProgressCardReport(Request $request)
    {
        try {
            $exams = SmExam::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $custom_mark_report = 'custom_mark_report';
            return view('backEnd.reports.progress_card_report', compact('exams', 'classes', 'custom_mark_report'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}