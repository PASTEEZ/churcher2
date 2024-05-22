<?php

namespace App\Http\Controllers\Admin\Examination;

use App\SmExam;
use App\SmClass;
use App\SmStaff;
use App\SmSection;
use App\SmStudent;
use App\SmSubject;
use App\YearCheck;
use App\SmExamType;
use App\SmSeatPlan;
use App\SmClassRoom;
use App\SmClassTime;
use App\SmExamSetup;
use App\SmMarkStore;
use App\SmMarksGrade;
use App\ApiBaseMethod;
use App\SmExamSetting;
use App\SmResultStore;
use App\SmAcademicYear;
use App\SmExamSchedule;
use App\SmAssignSubject;
use App\SmMarksRegister;
use App\SmSeatPlanChild;
use App\SmExamAttendance;
use App\SmGeneralSettings;
use App\SmStudentPromotion;
use App\CustomResultSetting;
use App\SmStudentAttendance;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\SmTemporaryMeritlist;
use App\SmExamAttendanceChild;
use App\SmExamScheduleSubject;
use App\SmClassOptionalSubject;
use App\SmOptionalSubjectAssign;
use App\Models\ExamMeritPosition;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Support\Facades\Validator;
use Modules\University\Entities\UnFaculty;
use Modules\University\Entities\UnSession;
use Modules\University\Entities\UnSubject;
use Modules\University\Entities\UnSemester;
use Modules\University\Entities\UnDepartment;
use Modules\University\Entities\UnAcademicYear;
use Modules\University\Entities\UnSemesterLabel;
use App\Http\Requests\AdmissionNumberGetStudentRequest;
use Modules\University\Http\Controllers\ExamCommonController;
use App\Http\Requests\Admin\Examination\MarkSheetReportRequest;
use App\Http\Requests\Admin\Examination\MeritListReportRequest;
use App\Http\Requests\Examination\PercentMarkSheetReportRequest;
use App\Http\Controllers\Admin\StudentInfo\SmStudentReportController;
use App\Http\Requests\Admin\Examination\SmExamAttendanceSearchRequest;
use App\Http\Controllers\Admin\StudentInfo\SmStudentAttendanceController;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;

class SmExaminationController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
        // User::checkAuth();
    }

    public function examSchedule()
    {
        try {
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            return view('backEnd.examination.exam_schedule', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function resultsArchiveView()
    {
        try {
            $church_years = SmAcademicYear::where('church_id', Auth::user()->church_id)->get();
            $exam_types = SmExamType::where('church_id', Auth::user()->church_id)->get();
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            return view('backEnd.examination.resultsArchiveView', compact('classes', 'exam_types', 'church_years'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function previousClassResults()
    {
        try {
            return view('backEnd.reports.previousClassResults');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function previousClassResultsView($registration_no, Request $request)
    {
        $request->validate([
            'admission_number' => 'required',
        ]);
        try {
            $admission_number = $registration_no;
            $promotes = SmStudentPromotion::where('admission_number', '=', $registration_no)
                ->join('sm_academic_years', 'sm_academic_years.id', '=', 'sm_student_promotions.previous_session_id')
                ->join('sm_classes', 'sm_classes.id', '=', 'sm_student_promotions.previous_age_group_id')
                ->join('sm_students', 'sm_students.id', '=', 'sm_student_promotions.member_id')
                ->join('sm_sections', 'sm_sections.id', '=', 'sm_student_promotions.previous_mgender_id')
                // ->select('admission_number', 'member_id', 'previous_age_group_id', 'age_group_name', 'previous_mgender_id', 'mgender_name', 'year', 'previous_session_id')
                ->get();
            if ($promotes->count() < 1) {
                Toastr::error('Ops! Admission number is not found in previous academic year', 'Failed');
                return redirect()->back()->withInput();
                // return redirect()->back()->withInput()->with('message-danger', 'Ops! Admission number is not found in previous academic year. Please try again');
            }
            $studentDetails = SmStudentPromotion::where('admission_number', '=', $registration_no)
                ->join('sm_academic_years', 'sm_academic_years.id', '=', 'sm_student_promotions.previous_session_id')
                ->join('sm_classes', 'sm_classes.id', '=', 'sm_student_promotions.previous_age_group_id')
                ->join('sm_students', 'sm_students.id', '=', 'sm_student_promotions.member_id')
                ->join('sm_sections', 'sm_sections.id', '=', 'sm_student_promotions.previous_mgender_id')
                // ->select('admission_number', 'member_id', 'previous_age_group_id', 'age_group_name', 'previous_mgender_id', 'mgender_name', 'year', 'previous_session_id')
                ->first();
            //  return $promotes;

            $generalSetting = SmGeneralSettings::where('church_id', auth()->user()->church_id)->first();

            if ($promotes->count() > 0) {
                $member_id = $studentDetails->member_id;

                $current_class = SmStudent::where('sm_students.id', $member_id)->join('sm_classes', 'sm_classes.id', '=', 'sm_students.age_group_id')->first();
                $current_section = SmStudent::where('sm_students.id', $member_id)->join('sm_sections', 'sm_sections.id', '=', 'sm_students.mgender_id')->first();
                $current_session = SmStudent::where('sm_students.id', $member_id)->join('sm_academic_years', 'sm_academic_years.id', '=', 'sm_students.session_id')->first();

                return view('backEnd.reports.previousClassResults', compact('promotes', 'studentDetails', 'generalSetting', 'current_class', 'current_section', 'current_session', 'admission_number'));
            } else {
                Toastr::error('Ops! Your result is not found! Please check mark register', 'Failed');
                return redirect('previous-class-results');
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function previousClassResultsViewPost(Request $request)
    {
        try {
            $studentRecord = StudentRecord::find($request->recordId);
            $age_group_id = $studentRecord->age_group_id;
            $mgender_id = $studentRecord->mgender_id;
            $church_year_id = $studentRecord->church_year_id;
            $member_id = $studentRecord->member_id;
            $record_id = $studentRecord->id;
            $church_id = $studentRecord->church_id;

            $exam_content = SmExamSetting::withOutGlobalScopes()->whereNull('exam_type')
                ->where('active_status', 1)
                ->where('church_year_id', $church_year_id)
                ->where('church_id', Auth::user()->church_id)
                ->first();

            $exams = SmExam::withOutGlobalScopes()->where('active_status', 1)
                ->where('age_group_id', $age_group_id)
                ->where('mgender_id', $mgender_id)
                ->where('church_year_id', $church_year_id)
                ->where('church_id', $church_id)
                ->get();

            $exam_types = SmExamType::withOutGlobalScopes()->where('active_status', 1)
                ->where('church_year_id', $church_year_id)
                ->where('church_id', $church_id)
                ->pluck('id');


            $classes = SmClass::withOutGlobalScopes()->where('active_status', 1)
                ->where('church_year_id', $church_year_id)
                ->where('church_id', $church_id)
                ->get();

            $fail_grade = SmMarksGrade::withOutGlobalScopes()->where('active_status', 1)
                ->where('church_year_id', $church_year_id)
                ->where('church_id', $church_id)
                ->min('gpa');

            $fail_grade_name = SmMarksGrade::withOutGlobalScopes()->where('active_status', 1)
                ->where('church_year_id', $church_year_id)
                ->where('church_id', $church_id)
                ->where('gpa', $fail_grade)
                ->first();

            $studentDetails = $studentRecord;

            $marks_grade = SmMarksGrade::withOutGlobalScopes()->where('church_id', $church_id)
                ->where('church_year_id', $church_year_id)
                ->orderBy('gpa', 'desc')
                ->get();

            $maxGrade = SmMarksGrade::withOutGlobalScopes()->where('church_year_id', $church_year_id)
                ->where('church_id', $church_id)
                ->max('gpa');

            $optional_subject_setup = SmClassOptionalSubject::where('age_group_id', '=', $age_group_id)
                ->first();

            $student_optional_subject = SmOptionalSubjectAssign::where('member_id', $studentDetails->member_id)
                ->where('session_id', '=', $studentDetails->session_id)
                ->first();

            $exam_setup = SmExamSetup::where([
                ['age_group_id', $age_group_id],
                ['mgender_id', $mgender_id]])
                ->where('church_id', $church_id)
                ->get();

            $examSubjects = SmExam::withOutGlobalScopes()->where([['mgender_id', $mgender_id], ['age_group_id', $age_group_id]])
                ->where('church_id', $church_id)
                ->where('church_year_id', $church_year_id)
                ->get();

            $examSubjectIds = [];
            foreach ($examSubjects as $examSubject) {
                $examSubjectIds[] = $examSubject->subject_id;
            }

            $subjects = SmAssignSubject::withOutGlobalScopes()->where([
                ['age_group_id', $age_group_id],
                ['mgender_id', $mgender_id]])
                ->where('church_id', $church_id)
                ->whereIn('subject_id', $examSubjectIds)
                ->get();

            $assinged_exam_types = [];
            foreach ($exams as $exam) {
                $assinged_exam_types[] = $exam->exam_type_id;
            }
            $assinged_exam_types = array_unique($assinged_exam_types);


            foreach ($assinged_exam_types as $assinged_exam_type) {
                foreach ($subjects as $subject) {
                    $is_mark_available = SmResultStore::where([
                        ['age_group_id', $age_group_id],
                        ['mgender_id', $mgender_id],
                        ['member_id', $studentDetails->member_id]
                    ])
                        ->first();
                    if ($is_mark_available == "") {
                        Toastr::error('Ops! Your result is not found! Please check mark register.', 'Failed');
                        return redirect('progress-card-report');
                    }
                }
            }

            $is_result_available = SmResultStore::where([
                ['age_group_id', $age_group_id],
                ['mgender_id', $mgender_id],
                ['member_id', $studentDetails->member_id]])
                ->where('church_id', $church_id)
                ->get();

            $data ['exams'] = $exams;
            $data ['optional_subject_setup'] = $optional_subject_setup;
            $data ['student_optional_subject'] = $student_optional_subject;
            $data ['classes'] = $classes;
            $data ['studentDetails'] = $studentDetails;
            $data ['is_result_available'] = $is_result_available;
            $data ['subjects'] = $subjects;
            $data ['age_group_id'] = $age_group_id;
            $data ['mgender_id'] = $mgender_id;
            $data ['member_id'] = $member_id;
            $data ['exam_types'] = $exam_types;
            $data ['assinged_exam_types'] = $assinged_exam_types;
            $data ['marks_grade'] = $marks_grade;
            $data ['fail_grade_name'] = $fail_grade_name;
            $data ['fail_grade'] = $fail_grade;
            $data ['maxGrade'] = $maxGrade;
            $data ['custom_mark_report'] = null;
            $data ['exam_content'] = $exam_content;


            $html = view('backEnd.reports._progress_card_report_content', $data)->render();

            if ($is_result_available->count() > 0) {
                return response()->json(['success' => true, 'html' => $html]);
            }else{
                return response()->json(['error' => true]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => true]);
        }
    }

    public function previousStudentRecord(AdmissionNumberGetStudentRequest $request)
    {
        $admission_number = $request->admission_number;
        $studentInfo = SmStudent::where('registration_no', $admission_number)->first();
        if(!$studentInfo){
            return response()->json(['not_valid']);
        }
        $allPromotedYearData = StudentRecord::with('class', 'section', 'academic')->where('is_promote', 1)->where('member_id', $studentInfo->id)->get();
        return response()->json([$allPromotedYearData]);
    }

    public function previousClassResultsViewPrint(Request $request)
    {
        try {
            $promotes = SmStudentPromotion::where('admission_number', '=', $request->admission_number)
                ->join('sm_academic_years', 'sm_academic_years.id', '=', 'sm_student_promotions.previous_session_id')
                ->join('sm_classes', 'sm_classes.id', '=', 'sm_student_promotions.previous_age_group_id')
                ->join('sm_students', 'sm_students.id', '=', 'sm_student_promotions.member_id')
                ->join('sm_sections', 'sm_sections.id', '=', 'sm_student_promotions.previous_mgender_id')
                // ->select('admission_number', 'member_id', 'previous_age_group_id', 'age_group_name', 'previous_mgender_id', 'mgender_name', 'year', 'previous_session_id')
                ->get();
            $studentDetails = SmStudentPromotion::where('admission_number', '=', $request->admission_number)
                ->join('sm_academic_years', 'sm_academic_years.id', '=', 'sm_student_promotions.previous_session_id')
                ->join('sm_classes', 'sm_classes.id', '=', 'sm_student_promotions.previous_age_group_id')
                ->join('sm_students', 'sm_students.id', '=', 'sm_student_promotions.member_id')
                ->join('sm_sections', 'sm_sections.id', '=', 'sm_student_promotions.previous_mgender_id')
                // ->select('admission_number', 'member_id', 'previous_age_group_id', 'age_group_name', 'previous_mgender_id', 'mgender_name', 'year', 'previous_session_id')
                ->first();
            $member_id = $studentDetails->member_id;


            $exams = SmExam::where('active_status', 1)
                ->where('age_group_id', $request->age_group_id)
                ->where('mgender_id', $request->mgender_id)
                ->where('church_year_id', $studentDetails->church_year_id)
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $exam_types = SmExamType::where('active_status', 1)
                ->where('church_year_id', $studentDetails->church_year_id)
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $classes = SmClass::where('active_status', 1)
                ->where('church_year_id', $studentDetails->church_year_id)
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $marks_grade = SmMarksGrade::where('church_id', Auth::user()->church_id)
                ->where('church_year_id', $studentDetails->church_year_id)
                ->orderBy('gpa', 'desc')
                ->get();

            $fail_grade = SmMarksGrade::where('active_status', 1)
                ->where('church_year_id', $studentDetails->church_year_id)
                ->where('church_id', Auth::user()->church_id)
                ->min('gpa');

            $fail_grade_name = SmMarksGrade::where('active_status', 1)
                ->where('church_year_id', $studentDetails->church_year_id)
                ->where('church_id', Auth::user()->church_id)
                ->where('gpa', $fail_grade)
                ->first();

            $student_detail = SmStudent::find($request->member_id);

            $exam_setup = SmExamSetup::where([
                ['age_group_id', $request->age_group_id],
                ['mgender_id', $request->mgender_id]])
                ->where('church_year_id', $studentDetails->church_year_id)
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $age_group_id = $request->age_group_id;
            $mgender_id = $request->mgender_id;
            $member_id = $request->member_id;

            $examSubjects = SmExam::where([['mgender_id', $request->mgender_id], ['age_group_id', $request->age_group_id]])
                ->where('church_id', Auth::user()->church_id)
                ->where('church_year_id', $studentDetails->church_year_id)
                ->get();

            $examSubjectIds = [];
            foreach ($examSubjects as $examSubject) {
                $examSubjectIds[] = $examSubject->subject_id;
            }

            $subjects = SmAssignSubject::where([
                ['age_group_id', $request->age_group_id],
                ['mgender_id', $request->mgender_id]])
                ->where('church_year_id', $studentDetails->church_year_id)
                ->where('church_id', Auth::user()->church_id)
                ->whereIn('subject_id', $examSubjectIds)
                ->get();

            $assinged_exam_types = [];
            foreach ($exams as $exam) {
                $assinged_exam_types[] = $exam->exam_type_id;
            }
            $assinged_exam_types = array_unique($assinged_exam_types);
            foreach ($assinged_exam_types as $assinged_exam_type) {
                foreach ($subjects as $subject) {
                    $is_mark_available = SmResultStore::where([
                        ['age_group_id', $request->age_group_id],
                        ['mgender_id', $request->mgender_id],
                        ['member_id', $studentDetails->id],
                        ['subject_id', $subject->subject_id],
                    ])
                        ->where('church_year_id', getAcademicId())
                        ->first();
                    if ($is_mark_available == "") {
                        Toastr::error('Ops! Your result is not found! Please check mark register.', 'Failed');
                        return redirect()->redirect('previous-class-results');
                        // return redirect('progress-card-report')->with('message-danger', 'Ops! Your result is not found! Please check mark register.');
                    }
                }
            }

            $is_result_available = SmResultStore::where([
                ['age_group_id', $request->age_group_id], ['mgender_id', $request->mgender_id], ['member_id', $studentDetails->id]])
                ->where('church_year_id', $studentDetails->church_year_id)
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $optional_subject_setup = SmClassOptionalSubject::where('age_group_id', '=', $request->age_group_id)->first();

            $student_optional_subject = SmOptionalSubjectAssign::where('member_id', $request->member_id)
                ->where('church_year_id', '=', $studentDetails->church_year_id)
                ->first();

            if ($promotes->count() > 0) {
                return view('backEnd.reports.student_archive_print',
                    compact(
                        'optional_subject_setup',
                        'student_optional_subject',
                        'exams',
                        'classes',
                        'is_result_available',
                        'subjects',
                        'age_group_id',
                        'mgender_id',
                        'member_id',
                        'exam_types',
                        'assinged_exam_types',
                        'marks_grade',
                        'studentDetails',
                        'fail_grade_name'
                    ));
            } else {
                Toastr::error('Ops! Your result is not found! Please check mark register', 'Failed');
                return redirect()->route('previous-class-results');
            }

        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function resultsArchiveSearch(Request $request)
    {
        $request->validate([
            'exam' => 'required',
            'class' => 'required',
            'section' => 'required',
        ]);
    }

    public function examScheduleCreate()
    {
        try {
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $sections = SmSection::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $subjects = SmSubject::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $exams = SmExam::where('church_id', Auth::user()->church_id)->get();
            $exam_types = SmExamType::where('church_id', Auth::user()->church_id)->get();
            return view('backEnd.examination.exam_schedule_create', compact('classes', 'exams', 'exam_types'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examScheduleSearch(Request $request)
    {
        $request->validate([
            'exam' => 'required',
            'class' => 'required',
            'section' => 'required',
        ]);

        try {
            $assign_subjects = SmAssignSubject::where('age_group_id', $request->class)->where('mgender_id', $request->section)->where('church_year_id', getAcademicId())->get();

            if ($assign_subjects->count() == 0) {
                Toastr::error('No Subject Assigned. Please assign subjects in this class', 'Failed');
                return redirect('exam-schedule-create');
            }

            $assign_subjects = SmAssignSubject::where('age_group_id', $request->class)->where('mgender_id', $request->section)->where('church_year_id', getAcademicId())->get();

            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $exams = SmExam::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $age_group_id = $request->class;
            $mgender_id = $request->section;
            $exam_id = $request->exam;

            $exam_types = SmExamType::where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $exam_periods = SmClassTime::where('type', 'exam')->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();

            return view('backEnd.examination.exam_schedule_create', compact('classes', 'exams', 'assign_subjects', 'age_group_id', 'mgender_id', 'exam_id', 'exam_types', 'exam_periods'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examScheduleStore(Request $request)
    {
        $update_check = SmExamSchedule::where('exam_id', $request->exam_id)->where('age_group_id', $request->age_group_id)->where('mgender_id', $request->mgender_id)->first();

        DB::beginTransaction();

        try {
            if ($update_check == "") {
                $exam_schedule = new SmExamSchedule();
            } else {
                $exam_schedule = $update_check = SmExamSchedule::where('exam_id', $request->exam_id)->where('age_group_id', $request->age_group_id)->where('mgender_id', $request->mgender_id)->first();
            }

            $exam_schedule->age_group_id = $request->age_group_id;
            $exam_schedule->mgender_id = $request->mgender_id;
            $exam_schedule->exam_id = $request->exam_id;
            $exam_schedule->church_id = Auth::user()->church_id;
            $exam_schedule->church_year_id = getAcademicId();
            $exam_schedule->save();
            $exam_schedule->toArray();

            $counter = 0;

            if ($update_check != "") {
                SmExamScheduleSubject::where('exam_schedule_id', $exam_schedule->id)->delete();
            }

            foreach ($request->subjects as $subject) {
                $counter++;
                $date = 'date_' . $counter;
                $start_time = 'start_time_' . $counter;
                $end_time = 'end_time_' . $counter;
                $room = 'room_' . $counter;
                $full_mark = 'full_mark_' . $counter;
                $pass_mark = 'pass_mark_' . $counter;

                $exam_schedule_subject = new SmExamScheduleSubject();
                $exam_schedule_subject->exam_schedule_id = $exam_schedule->id;
                $exam_schedule_subject->subject_id = $subject;
                $exam_schedule_subject->date = date('Y-m-d', strtotime($request->$date));
                $exam_schedule_subject->start_time = $request->$start_time;
                $exam_schedule_subject->end_time = $request->$end_time;
                $exam_schedule_subject->room = $request->$room;
                $exam_schedule_subject->full_mark = $request->$full_mark;
                $exam_schedule_subject->pass_mark = $request->$pass_mark;
                $exam_schedule_subject->church_id = Auth::user()->church_id;
                $exam_schedule_subject->church_year_id = getAcademicId();
                $exam_schedule_subject->save();
            }

            DB::commit();
            Toastr::success('Operation successful', 'Success');
            return redirect('exam-schedule');
        } catch (\Exception $e) {
            DB::rollBack();
        }
        Toastr::error('Operation Failed', 'Failed');
        return redirect()->back();
    }

    public function viewExamSchedule($age_group_id, $mgender_id, $exam_id)
    {
        try {
            $class = SmClass::find($age_group_id);
            $section = SmSection::find($mgender_id);
            $assign_subjects = SmExamScheduleSubject::where('exam_schedule_id', $exam_id)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            return view('backEnd.examination.view_exam_schedule_modal', compact('class', 'section', 'assign_subjects'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function viewExamStatus($exam_id)
    {
        try {
            $exam = SmExam::find($exam_id);
            $view_exams = SmExamSchedule::where('exam_id', $exam_id)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            return view('backEnd.examination.view_exam_status', compact('exam', 'view_exams'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    // Mark Register View Page
    public function marksRegister()
    {
        try {
            $exams = SmExam::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->get();
            if (teacherAccess()) {
                $teacher_info = SmStaff::where('user_id', Auth::user()->id)->first();
                $classes = SmAssignSubject::where('teacher_id', $teacher_info->id)->join('sm_classes', 'sm_classes.id', 'sm_assign_subjects.age_group_id')
                    ->where('sm_assign_subjects.church_year_id', getAcademicId())
                    ->where('sm_assign_subjects.active_status', 1)
                    ->where('sm_assign_subjects.church_id', Auth::user()->church_id)
                    ->select('sm_classes.id', 'age_group_name')
                    ->groupBy('sm_classes.id')
                    ->get();
            } else {
                $classes = SmClass::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->get();
            }

            $exam_types = SmExamType::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            return view('backEnd.examination.masks_register', compact('exams', 'classes', 'exam_types'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function marksRegisterCreate()
    {
        try {
            $exams = SmExam::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $exam_types = SmExamType::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->get();

            if (teacherAccess()) {
                $teacher_info = SmStaff::where('user_id', Auth::user()->id)->first();
                $classes = SmAssignSubject::where('teacher_id', $teacher_info->id)->join('sm_classes', 'sm_classes.id', 'sm_assign_subjects.age_group_id')
                    ->where('sm_assign_subjects.church_year_id', getAcademicId())
                    ->where('sm_assign_subjects.active_status', 1)
                    ->where('sm_assign_subjects.church_id', Auth::user()->church_id)
                    ->select('sm_classes.id', 'age_group_name')
                    ->groupBy('sm_classes.id')
                    ->get();
            } else {
                $classes = SmClass::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->get();
            }
            $subjects = SmSubject::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            return view('backEnd.examination.masks_register_create', compact('exams', 'classes', 'subjects', 'exam_types'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    //show exam type method from sm_exams_types table
    public function exam_type()
    {
        try {
            $exams = SmExam::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            if (teacherAccess()) {
                $teacher_info = SmStaff::where('user_id', Auth::user()->id)->first();
                $classes = SmAssignSubject::where('teacher_id', $teacher_info->id)->join('sm_classes', 'sm_classes.id', 'sm_assign_subjects.age_group_id')
                    ->where('sm_assign_subjects.church_year_id', getAcademicId())
                    ->where('sm_assign_subjects.active_status', 1)
                    ->where('sm_assign_subjects.church_id', Auth::user()->church_id)
                    ->select('sm_classes.id', 'age_group_name')
                    ->groupBy('sm_classes.id')
                    ->get();
            } else {
                $classes = SmClass::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->get();
            }
            $exams_types = SmExamType::get();
            return view('backEnd.examination.exam_type', compact('exams', 'classes', 'exams_types'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    //edit exam type method from sm_exams_types table
    public function exam_type_edit($id)
    {
        try {
            if (checkAdmin()) {
                $exam_type_edit = SmExamType::find($id);
            } else {
                $exam_type_edit = SmExamType::where('id', $id)->first();
            }
            $exams_types = SmExamType::get();
            return view('backEnd.examination.exam_type', compact('exam_type_edit', 'exams_types'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    //update exam type method from sm_exams_types table
    public function exam_type_update(Request $request)
    {
        $request->validate([
            'exam_type_title' => 'required|max:50',
            // 'active_status' => 'required'
        ]);
        // school wise uquine validation
        $is_duplicate = SmExamType::where('title', $request->exam_type_title)->where('id', '!=', $request->id)->first();
        if ($is_duplicate) {
            Toastr::error('Duplicate name found!', 'Failed');
            return redirect()->back()->withInput();
        }
        DB::beginTransaction();
        try {
            if (checkAdmin()) {
                $update_exame_type = SmExamType::find($request->id);
            } else {
                $update_exame_type = SmExamType::where('id', $request->id)->first();
            }
            $update_exame_type->title = $request->exam_type_title;
            $update_exame_type->save();
            $update_exame_type->toArray();

            DB::commit();
            Toastr::success('Operation successful', 'Success');
            return redirect('exam-type');
        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    //store exam type method from sm_exams_types table
    public function exam_type_store(Request $request)
    {

        $request->validate([
            'exam_type_title' => 'required|max:50',
        ]);
        // school wise uquine validation
        $is_duplicate = SmExamType::where('title', $request->exam_type_title)->first();
        if ($is_duplicate) {
            Toastr::error('Duplicate name found!', 'Failed');
            return redirect()->back()->withInput();
        }
        try {
            $update_exame_type = new SmExamType();
            $update_exame_type->title = $request->exam_type_title;
            $update_exame_type->active_status = 1; //1 for status active & 0 for inactive
            $update_exame_type->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
            $update_exame_type->church_id = Auth::user()->church_id;
            if (moduleStatusCheck('University')) {
                $update_exame_type->un_church_year_id = getAcademicId();
            } else {
                $update_exame_type->church_year_id = getAcademicId();
            }
            $result = $update_exame_type->save();

            if ($result) {
                Toastr::success('Operation successful', 'Success');
                return redirect('exam-type');
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    //delete exam type method from sm_exams_types table
    public function exam_type_delete(Request $request, $id)
    {
        ;
        try {
            $id_key = 'exam_type_id';
            $term_key = 'exam_term_id';

            $type = \App\tableList::getTableList($id_key, $id);

            $term = \App\tableList::getTableList($term_key, $id);

            $tables = $type . '' . $term;
            try {
                if ($tables == null || $tables == '') {
                    if (checkAdmin()) {

                        $delete_query = SmExamType::destroy($id);
                    } else {
                        $data = SmExamType::where('id', $id)->first();
                        $delete_query = $data->delete();

                    }
                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        if ($delete_query) {
                            return ApiBaseMethod::sendResponse(null, 'Exam Type has been deleted successfully');
                        } else {
                            return ApiBaseMethod::sendError('Something went wrong, please try again.');
                        }
                    } else {
                        if ($delete_query) {
                            Toastr::success('Operation successful', 'Success');
                            return redirect()->back();
                        } else {
                            Toastr::error('Operation Failed', 'Failed');
                            return redirect()->back();
                        }
                    }
                } else {
                    // return $tables;
                    $msg = 'This data already used in   : ' . $tables . ' Please remove those data first';
                    Toastr::error($msg, 'Failed');
                    return redirect()->back();
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error($msg, 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function marksRegisterSearch(Request $request)
    {

        $request->validate([
            'exam' => 'required',
            'class' => 'required',
            // 'section' => 'required',
            'subject' => 'required',
        ]);
        try {
            if ($request->section == '') {
                $classSections = SmAssignSubject::where('age_group_id', $request->class)
                    ->where('subject_id', $request->subject)
                    ->where('church_id', auth()->user()->church_id)
                    ->where('church_year_id', getAcademicId())
                    ->groupby(['mgender_id', 'subject_id'])
                    ->get(['mgender_id']);

                $exam_attendance = SmExamAttendance::where('age_group_id', $request->class)->where('exam_id', $request->exam)->where('subject_id', $request->subject)->first();

            } else {
                $exam_attendance = SmExamAttendance::where('age_group_id', $request->class)->where('mgender_id', $request->section)->where('exam_id', $request->exam)->where('subject_id', $request->subject)->first();

            }

            if ($exam_attendance == "") {

                Toastr::error('Exam Attendance not taken yet, please check exam attendance', 'Failed');
                return redirect()->back();

            }
            $exams = SmExam::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $exam_types = SmExamType::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $exam_id = $request->exam;
            $age_group_id = $request->class;
            $mgender_id = $request->section;
            $subject_id = $request->subject;
            $subjectNames = SmSubject::where('id', $subject_id)->first();

            $exam_type = SmExamType::find($request->exam);
            $class = SmClass::find($request->class);
            $section = SmSection::find($request->section);

            $search_info['exam_name'] = $exam_type->title;
            $search_info['age_group_name'] = $class->age_group_name;
            if ($request->section != '') {
                $search_info['mgender_name'] = $section->mgender_name;

            } else {
                $search_info['mgender_name'] = 'All Sections';
            }

            if ($request->section != '') {
                $students = SmStudent::where('active_status', 1)->where('age_group_id', $request->class)->where('mgender_id', $request->section)->where('church_year_id', getAcademicId())->get();
            } else {
                $students = SmStudent::where('active_status', 1)->where('age_group_id', $request->class)->where('church_year_id', getAcademicId())->get();
            }

            $exam_schedule = SmExamSchedule::where('exam_id', $request->exam)->where('age_group_id', $request->class)->where('mgender_id', $request->section)->where('church_year_id', getAcademicId())->first();

            if ($students->count() < 1) {
                Toastr::error('Student is not found in according this class and section!', 'Failed');
                return redirect()->back();
                // return redirect()->back()->with('message-danger', 'Student is not found in according this class and section! Please add student in this section of that class.');
            } else {
                if ($request->section != '') {
                    $marks_entry_form = SmExamSetup::where(
                        [
                            ['exam_term_id', $exam_id],
                            ['age_group_id', $age_group_id],
                            ['mgender_id', $mgender_id],
                            ['subject_id', $subject_id],
                        ]
                    )->where('church_year_id', getAcademicId())->get();
                } else {
                    $marks_entry_form = SmExamSetup::where(
                        [
                            ['exam_term_id', $exam_id],
                            ['age_group_id', $age_group_id],
                            ['subject_id', $subject_id],
                        ]
                    )->whereIn('mgender_id', $classSections)->groupby(['subject_id', 'exam_title'])->where('church_year_id', getAcademicId())->orderby('id', 'ASC')->get();
                }

                if ($marks_entry_form->count() > 0) {
                    $number_of_exam_parts = count($marks_entry_form);

                    return view('backEnd.examination.masks_register_create', compact('exams', 'classes', 'students', 'exam_id', 'age_group_id', 'mgender_id', 'subject_id', 'subjectNames', 'number_of_exam_parts', 'marks_entry_form', 'exam_types', 'search_info'));
                } else {
                    Toastr::error('No result found or exam setup is not done!', 'Failed');
                    return redirect()->back();
                    // return redirect()->back()->with('message-danger', 'No result found or exam setup is not done!');
                }
                return view('backEnd.examination.masks_register_create', compact('exams', 'classes', 'students', 'exam_id', 'age_group_id', 'mgender_id', 'marks_register_subjects', 'assign_subject_ids', 'search_info'));
            }
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function marksRegisterStore(Request $request)
    {
        // return $request->all();
        DB::beginTransaction();
        try {
            $abc = [];
            $age_group_id = $request->age_group_id;
            if ($request->mgender_id != '') {
                $mgender_id = $request->mgender_id;
            }
            $subject_id = $request->subject_id;
            $exam_id = $request->exam_id;
            $counter = 0; // Initilize by 0

            foreach ($request->member_ids as $member_id) {
                $sid = $member_id;
                if ($request->mgender_id == '') {
                    $mgender_id = SmStudent::where('church_id', auth()->user()->church_id)->where('id', $sid)->where('active_status', 1)->first()->mgender_id;
                }
                $registration_no = ($request->student_admissions[$sid] == null) ? '' : $request->student_admissions[$sid];
                $roll_no = ($request->student_rolls[$sid] == null) ? '' : $request->student_rolls[$sid];

                if (!empty($request->marks[$sid])) {
                    $exam_setup_count = 0;
                    $total_marks_persubject = 0;
                    foreach ($request->marks[$sid] as $part_mark) {
                        $mark_by_exam_part = ($part_mark == null) ? 0 : $part_mark;
                        // 0=If exam part is empty
                        $total_marks_persubject = $total_marks_persubject + $mark_by_exam_part;
                        // $is_absent = ($request->abs[$sid]==null) ? 0 : 1;
                        $exam_setup_id = $request->exam_Sids[$sid][$exam_setup_count];

                        $previous_record = SmMarkStore::where([
                            ['age_group_id', $age_group_id],
                            ['mgender_id', $mgender_id],
                            ['subject_id', $subject_id],
                            ['exam_term_id', $exam_id],
                            ['exam_setup_id', $exam_setup_id],
                            ['member_id', $sid],
                        ])->where('church_year_id', getAcademicId())->first();
                        // Is previous record exist ?

                        if ($previous_record == "" || $previous_record == null) {

                            $marks_register = new SmMarkStore();
                            $marks_register->exam_term_id = $exam_id;
                            $marks_register->age_group_id = $age_group_id;
                            $marks_register->mgender_id = $mgender_id;
                            $marks_register->subject_id = $subject_id;
                            $marks_register->member_id = $sid;
                            $marks_register->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                            $marks_register->total_marks = $mark_by_exam_part;
                            $marks_register->exam_setup_id = $exam_setup_id;
                            if (isset($request->absent_students)) {
                                if (in_array($sid, $request->absent_students)) {
                                    $marks_register->is_absent = 1;
                                } else {
                                    $marks_register->is_absent = 0;
                                }
                            }

                            $marks_register->teacher_remarks = $request->teacher_remarks[$sid][$subject_id];

                            $marks_register->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                            $marks_register->church_id = Auth::user()->church_id;
                            $marks_register->church_year_id = getAcademicId();

                            $marks_register->save();
                            $marks_register->toArray();
                        } else { //If already exists, it will updated
                            $pid = $previous_record->id;
                            $marks_register = SmMarkStore::find($pid);
                            $marks_register->total_marks = $mark_by_exam_part;

                            if (isset($request->absent_students)) {
                                if (in_array($sid, $request->absent_students)) {
                                    $marks_register->is_absent = 1;
                                } else {
                                    $marks_register->is_absent = 0;
                                }
                            }

                            $marks_register->teacher_remarks = $request->teacher_remarks[$sid][$subject_id];

                            $marks_register->save();
                        }

                        $exam_setup_count++;
                    } // end part insertion

                    $subject_full_mark = subjectFullMark($request->exam_id, $request->subject_id, $age_group_id, $mgender_id);
                    $student_obtained_mark = $total_marks_persubject;
                    $mark_by_persentage = subjectPercentageMark($student_obtained_mark, $subject_full_mark);

                    $mark_grade = SmMarksGrade::where([
                        ['percent_from', '<=', $mark_by_persentage],
                        ['percent_upto', '>=', $mark_by_persentage]])
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->first();

                    $abc[] = $total_marks_persubject;

                    $previous_result_record = SmResultStore::where([
                        ['age_group_id', $age_group_id],
                        ['mgender_id', $mgender_id],
                        ['subject_id', $subject_id],
                        ['exam_type_id', $exam_id],
                        ['member_id', $sid],
                    ])->first();

                    if ($previous_result_record == "" || $previous_result_record == null) { //If not result exists, it will create
                        $result_record = new SmResultStore();
                        $result_record->age_group_id = $age_group_id;
                        $result_record->mgender_id = $mgender_id;
                        $result_record->subject_id = $subject_id;
                        $result_record->exam_type_id = $exam_id;
                        $result_record->member_id = $sid;

                        if (isset($request->absent_students)) {
                            if (in_array($sid, $request->absent_students)) {
                                $result_record->is_absent = 1;
                            } else {
                                $result_record->is_absent = 0;
                            }
                        }

                        $result_record->total_marks = $total_marks_persubject;
                        $result_record->total_gpa_point = @$mark_grade->gpa;
                        $result_record->total_gpa_grade = @$mark_grade->grade_name;

                        $result_record->teacher_remarks = $request->teacher_remarks[$sid][$subject_id];

                        $result_record->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                        $result_record->church_id = Auth::user()->church_id;
                        $result_record->church_year_id = getAcademicId();
                        $result_record->save();
                        $result_record->toArray();
                    } else { //If already result exists, it will updated
                        $id = $previous_result_record->id;
                        $result_record = SmResultStore::find($id);
                        $result_record->total_marks = $total_marks_persubject;
                        $result_record->total_gpa_point = @$mark_grade->gpa;
                        $result_record->total_gpa_grade = @$mark_grade->grade_name;
                        $result_record->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                        if (isset($request->absent_students)) {
                            if (in_array($sid, $request->absent_students)) {
                                $result_record->is_absent = 1;
                            } else {
                                $result_record->is_absent = 0;
                            }
                        }

                        $result_record->teacher_remarks = $request->teacher_remarks[$sid][$subject_id];

                        $result_record->save();
                        $result_record->toArray();
                    }
                } // If student id is valid

            } //end student loop
            DB::commit();
            Toastr::success('Operation successful', 'Success');
            return redirect('marks-register-create');
        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function marksRegisterReportSearch(Request $request)
    {

        $request->validate([
            'exam' => 'required',
            'class' => 'required',
            // 'section' => 'required',
            'subject' => 'required',
        ]);
        try {
            $exams = SmExam::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $exam_types = SmExamType::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();

            $exams = SmExam::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $exam_types = SmExamType::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();

            $exam_id = $request->exam;
            $age_group_id = $request->class;
            $mgender_id = $request->section != null ? $request->section : null;
            $subject_id = $request->subject;
            $subjectNames = SmSubject::where('id', $subject_id)->first();

            $exam_attendance = SmExamAttendance::query();
            if ($request->class != null) {
                $exam_attendance->where('exam_id', $exam_id)->where('age_group_id', $age_group_id);
            }
            if ($request->section != null) {
                $exam_attendance->where('mgender_id', $request->section);
            }

            $exam_attendance = $exam_attendance->where('subject_id', $subject_id)->first();

            if ($exam_attendance) {
                $exam_attendance_child = SmExamAttendanceChild::where('exam_attendance_id', $exam_attendance->id)->first();
            } else {
                Toastr::error('Exam attendance not done yet', 'Failed');
                return redirect()->back();
            }

            $students = SmStudent::query();
            if ($request->class != null) {
                $students->where('age_group_id', $request->class);
            }
            if ($request->section != null) {
                $students->where('mgender_id', $request->section);
            }
            $students = $students->where('active_status', 1)->where('church_year_id', getAcademicId())->get();

            $exam_schedule = SmExamSchedule::where('exam_id', $request->exam)->where('age_group_id', $request->class)->where('mgender_id', $request->section)->where('church_year_id', getAcademicId())->first();
            if ($students->count() == 0) {
                Toastr::error('Sorry ! Student is not available Or exam schedule is not set yet.', 'Failed');
                return redirect()->back();
                // return redirect()->back()->with('message-danger', 'Sorry ! Student is not available Or exam schedule is not set yet.');
            } else {

                $marks_entry_form = SmExamSetup::query();
                if ($request->class != null) {
                    $marks_entry_form->where('exam_term_id', $exam_id)->where('age_group_id', $age_group_id);
                }
                if ($request->section != null) {
                    $marks_entry_form->where('mgender_id', $request->section);
                }
                $marks_entry_form = $marks_entry_form->where('subject_id', $subject_id)->where('church_year_id', getAcademicId())->get();

                if ($marks_entry_form->count() > 0) {
                    $number_of_exam_parts = count($marks_entry_form);
                    return view('backEnd.examination.masks_register_search', compact('exams', 'classes', 'students', 'exam_id', 'age_group_id', 'mgender_id', 'subject_id', 'subjectNames', 'number_of_exam_parts', 'marks_entry_form', 'exam_types'));
                } else {
                    Toastr::error('Sorry ! Exam setup is not set yet.', 'Failed');
                    return redirect()->back();
                    // return redirect()->back()->with('message-danger', 'Sorry ! Exam schedule is not set yet.');
                }
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }

    }

    public function seatPlan()
    {
        try {
            $exam_types = SmExamType::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $subjects = SmSubject::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            return view('backEnd.examination.seat_plan', compact('exam_types', 'classes', 'subjects'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function seatPlanCreate()
    {
        try {
            $exam_types = SmExamType::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $subjects = SmSubject::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $class_rooms = SmClassRoom::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            return view('backEnd.examination.seat_plan_create', compact('exam_types', 'classes', 'subjects', 'class_rooms'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function seatPlanSearch(Request $request)
    {

        $request->validate([
            'exam' => 'required',
            'subject' => 'required',
            'class' => 'required',
            'section' => 'required',
        ]);
        try {
            $students = SmStudent::where('age_group_id', $request->class)->where('mgender_id', $request->section)->where('active_status', 1)->where('church_year_id', getAcademicId())->get();

            if ($students->count() == 0) {
                Toastr::error('No result found', 'Failed');
                return redirect('seat-plan-create');
                // return redirect('seat-plan-create')->with('message-danger', 'No result found');
            }

            $seat_plan_assign = SmSeatPlan::where('exam_id', $request->exam)->where('subject_id', $request->subject)->where('age_group_id', $request->class)->where('mgender_id', $request->section)->where('date', date('Y-m-d', strtotime($request->date)))->first();

            $seat_plan_assign_childs = [];
            if ($seat_plan_assign != "") {
                $seat_plan_assign_childs = $seat_plan_assign->seatPlanChild;
            }

            $exam_types = SmExamType::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();

            $class_rooms = SmClassRoom::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $fill_uped = [];
            foreach ($class_rooms as $class_room) {
                $assigned_student = SmSeatPlanChild::where('room_id', $class_room->id)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
                if ($assigned_student->count() > 0) {
                    $assigned_student = $assigned_student->sum('assign_students');
                    if ($assigned_student >= $class_room->capacity) {
                        $fill_uped[] = $class_room->id;
                    }
                }
            }
            $age_group_id = $request->class;
            $mgender_id = $request->section;
            $exam_id = $request->exam;
            $subject_id = $request->subject;
            $date = $request->date;

            return view('backEnd.examination.seat_plan_create', compact('exam_types', 'classes', 'class_rooms', 'students', 'age_group_id', 'mgender_id', 'exam_id', 'subject_id', 'seat_plan_assign_childs', 'fill_uped', 'date'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function getExamRoomByAjax(Request $request)
    {
        try {
            $class_rooms = SmClassRoom::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $rest_class_rooms = [];
            foreach ($class_rooms as $class_room) {
                $assigned_student = SmSeatPlanChild::where('room_id', $class_room->id)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
                if ($assigned_student->count() > 0) {
                    $assigned_student = $assigned_student->sum('assign_students');
                    if ($assigned_student < $class_room->capacity) {
                        $rest_class_rooms[] = $class_room;
                    }
                } else {
                    $rest_class_rooms[] = $class_room;
                }
            }
            return response()->json([$rest_class_rooms]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

    public function getRoomCapacity(Request $request)
    {
        try {
            // $class_room = SmClassRoom::find($request->id);
            if (checkAdmin()) {
                $class_room = SmClassRoom::find($request->id);
            } else {
                $class_room = SmClassRoom::where('id', $request->id)->where('church_id', Auth::user()->church_id)->first();
            }
            $assigned = SmSeatPlanChild::where('room_id', $request->id)->where('date', date('Y-m-d', strtotime($request->date)))->first();
            $assigned_student = 0;
            if ($assigned != '') {
                $assigned_student = SmSeatPlanChild::where('room_id', $request->id)->where('date', date('Y-m-d', strtotime($request->date)))->where('start_time', '<=', date('H:i:s', strtotime($request->start_time)))->where('end_time', '>=', date('H:i:s', strtotime($request->end_time)))->sum('assign_students');
            }
            return response()->json([$class_room, $assigned_student]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

    public function seatPlanStore(Request $request)
    {

        $seat_plan_assign = SmSeatPlan::where('exam_id', $request->exam_id)->where('subject_id', $request->subject_id)->where('age_group_id', $request->age_group_id)->where('mgender_id', $request->mgender_id)->first();

        DB::beginTransaction();
        try {
            if ($seat_plan_assign == "") {
                $seat_plan = new SmSeatPlan();
            } else {
                $seat_plan = SmSeatPlan::where('exam_id', $request->exam_id)->where('subject_id', $request->subject_id)->where('age_group_id', $request->age_group_id)->where('mgender_id', $request->mgender_id)->where('date', date('Y-m-d', strtotime($request->exam_date)))->first();
            }
            $seat_plan->exam_id = $request->exam_id;
            $seat_plan->subject_id = $request->subject_id;
            $seat_plan->age_group_id = $request->age_group_id;
            $seat_plan->mgender_id = $request->mgender_id;
            $seat_plan->date = date('Y-m-d', strtotime($request->exam_date));
            $seat_plan->church_id = Auth::user()->church_id;
            $seat_plan->church_year_id = getAcademicId();
            $seat_plan->save();
            $seat_plan->toArray();

            if ($seat_plan_assign != "") {
                SmSeatPlanChild::where('seat_plan_id', $seat_plan->id)->delete();
            }

            $i = 0;
            foreach ($request->room as $room) {
                $seat_plan_child = new SmSeatPlanChild();
                $seat_plan_child->seat_plan_id = $seat_plan->id;
                $seat_plan_child->room_id = $room;
                $seat_plan_child->assign_students = $request->assign_student[$i];
                $seat_plan_child->start_time = date('H:i:s', strtotime($request->start_time));
                $seat_plan_child->end_time = date('H:i:s', strtotime($request->end_time));
                $seat_plan_child->date = date('Y-m-d', strtotime($request->exam_date));
                $seat_plan_child->church_id = Auth::user()->church_id;
                $seat_plan_child->church_year_id = getAcademicId();
                $seat_plan_child->save();
                $i++;
            }
            DB::commit();
            Toastr::success('Operation successful', 'Success');
            return redirect('seat-plan');
            // return redirect('seat-plan')->with('message-success', 'Seat Plan has been assigned successfully');
        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
            // return redirect()->back()->with('message-danger', 'Something went wrong, please try again');
        }
    }

    public function seatPlanReportSearch(Request $request)
    {
        try {
            $seat_plans = SmSeatPlan::query();
            $seat_plans->where('active_status', 1);
            if ($request->exam != "") {
                $seat_plans->where('exam_id', $request->exam);
            }
            if ($request->subject != "") {
                $seat_plans->where('subject_id', $request->subject);
            }

            if ($request->class != "") {
                $seat_plans->where('age_group_id', $request->class);
            }

            if ($request->section != "") {
                $seat_plans->where('mgender_id', $request->section);
            }
            if ($request->date != "") {
                $seat_plans->where('date', date('Y-m-d', strtotime($request->date)));
            }
            $seat_plans = $seat_plans->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            if ($seat_plans->count() == 0) {
                Toastr::success('No Record Found', 'Success');
                return redirect('seat-plan');
            }

            $exams = SmExam::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $subjects = SmSubject::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();

            return view('backEnd.examination.seat_plan', compact('exams', 'classes', 'subjects', 'seat_plans'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examAttendance()
    {

        try {
            $exams = SmExamType::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->get();

            if (teacherAccess()) {
                $teacher_info = SmStaff::where('user_id', Auth::user()->id)->first();
                $classes = SmAssignSubject::where('teacher_id', $teacher_info->id)->join('sm_classes', 'sm_classes.id', 'sm_assign_subjects.age_group_id')
                    ->where('sm_assign_subjects.church_year_id', getAcademicId())
                    ->where('sm_assign_subjects.active_status', 1)
                    ->where('sm_assign_subjects.church_id', Auth::user()->church_id)
                    ->select('sm_classes.id', 'age_group_name')
                    ->groupBy('sm_classes.id')
                    ->get();
            } else {
                $classes = SmClass::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->get();
            }
            $subjects = SmSubject::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            return view('backEnd.examination.exam_attendance', compact('exams', 'classes', 'subjects'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examAttendanceAeportSearch(SmExamAttendanceSearchRequest $request)
    {
        try {
            if (moduleStatusCheck('University')) {
                $data = [];

                $un_session = UnSession::find($request->un_session_id);
                $un_faculty = UnFaculty::find($request->un_faculty_id);
                $un_department = UnDepartment::find($request->un_department_id);
                $un_academic = UnAcademicYear::find($request->un_church_year_id);
                $un_semester = UnSemester::find($request->un_semester_id);
                $un_semester_label = UnSemesterLabel::find($request->un_semester_label_id);
                $un_section = SmSection::find($request->un_mgender_id);

                $SmExam = SmExam::query();
                $sm_exam = universityFilter($SmExam, $request)
                    ->where('exam_type_id', $request->exam_type)
                    ->where('un_subject_id', $request->subject_id)
                    ->first();

                $SmExamAttendance = SmExamAttendance::query();
                $exam_attendance = universityFilter($SmExamAttendance, $request)
                    ->where('un_subject_id', $request->subject_id)
                    ->where('exam_id', $sm_exam->id)
                    ->first();

                if ($exam_attendance == "") {
                    Toastr::success('No Record Found', 'Success');
                    return redirect('exam-attendance');
                }

                $exam_attendance_childs = [];
                if ($exam_attendance != "") {
                    $exam_attendance_childs = $exam_attendance->examAttendanceChild;
                }

                $exams = SmExamType::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->get();

                $subjectName = UnSubject::find($request->subject_id);

                $data['un_semester_label_id'] = $request->un_semester_label_id;
                $interface = App::make(UnCommonRepositoryInterface::class);
                $data = $interface->oldValueSelected($request);

                return view('backEnd.examination.exam_attendance', compact(
                    'exams',
                    'exam_attendance_childs',
                    'un_session',
                    'un_faculty',
                    'un_department',
                    'un_academic',
                    'un_semester',
                    'un_semester_label',
                    'un_section',
                    'subjectName',
                ))->with($data);

            } else {
                $exam_attendance = SmExamAttendance::query();
                if (!empty($request->section)) {
                    $exam_attendance->where('mgender_id', $request->section);
                }
                $exam_attendance = $exam_attendance->where('age_group_id', $request->class)
                    ->where('subject_id', $request->subject)
                    ->where('exam_id', $request->exam)
                    ->first();

                if ($exam_attendance == "") {
                    Toastr::success('No Record Found', 'Success');
                    return redirect('exam-attendance');
                }

                $exam_attendance_childs = [];
                if ($exam_attendance != "") {
                    $exam_attendance_childs = $exam_attendance->examAttendanceChild;
                }

                $exams = SmExamType::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
                if (teacherAccess()) {
                    $teacher_info = SmStaff::where('user_id', Auth::user()->id)->first();
                    $classes = SmAssignSubject::where('teacher_id', $teacher_info->id)->join('sm_classes', 'sm_classes.id', 'sm_assign_subjects.age_group_id')
                        ->where('sm_assign_subjects.church_year_id', getAcademicId())
                        ->where('sm_assign_subjects.active_status', 1)
                        ->where('sm_assign_subjects.church_id', Auth::user()->church_id)
                        ->select('sm_classes.id', 'age_group_name')
                        ->groupBy('sm_classes.id')
                        ->get();
                } else {
                    $classes = SmClass::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->get();
                }
                $subjects = SmSubject::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
                return view('backEnd.examination.exam_attendance', compact('exams', 'classes', 'subjects', 'exam_attendance_childs'));
            }

        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examAttendanceCreate()
    {
        try {
            $exams = SmExamType::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->get();

            if (teacherAccess()) {
                $teacher_info = SmStaff::where('user_id', Auth::user()->id)->first();
                $classes = SmAssignSubject::where('teacher_id', $teacher_info->id)->join('sm_classes', 'sm_classes.id', 'sm_assign_subjects.age_group_id')
                    ->where('sm_assign_subjects.church_year_id', getAcademicId())
                    ->where('sm_assign_subjects.active_status', 1)
                    ->where('sm_assign_subjects.church_id', Auth::user()->church_id)
                    ->select('sm_classes.id', 'age_group_name')
                    ->groupBy('sm_classes.id')
                    ->get();
            } else {
                $classes = SmClass::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->get();
            }
            $subjects = SmSubject::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            return view('backEnd.examination.exam_attendance_create', compact('exams', 'classes', 'subjects'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examAttendanceSearch(Request $request)
    {
        //  return $request->all();
        $request->validate([
            'exam' => 'required',
            'subject' => 'required',
            'class' => 'required',
            // 'section' => 'required'
        ]);
        try {

            $exam_schedules = SmExamSchedule::query();
            if ($request->class != null) {
                $exam_schedules->where('age_group_id', $request->class);
            }
            if ($request->section != null) {
                $exam_schedules->where('mgender_id', $request->section);
            }

            $exam_schedules = $exam_schedules->where('exam_term_id', $request->exam)
                ->where('subject_id', $request->subject)
                ->count();

            if ($exam_schedules == 0) {
                Toastr::error('You have to create exam schedule first', 'Failed');
                return redirect('exam-attendance-create');
            }

            $students = SmStudent::query();
            if ($request->class != null) {
                $students->where('age_group_id', $request->class);
            }
            if ($request->section != null) {
                $students->where('mgender_id', $request->section);
            }

            $students = $students->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->where('active_status', 1)
                ->get();

            if ($students->count() == 0) {
                Toastr::error('No Record Found', 'Failed');
                return redirect('exam-attendance-create');
            }
            if ($request->section == null) {
                $exam_attendance = SmExamAttendance::where('age_group_id', $request->class)
                    ->where('subject_id', $request->subject)
                    ->where('exam_id', $request->exam)
                    ->first();
            } else {
                $exam_attendance = SmExamAttendance::where('age_group_id', $request->class)
                    ->where('mgender_id', $request->section)
                    ->where('subject_id', $request->subject)
                    ->where('exam_id', $request->exam)
                    ->first();
            }

            $exam_attendance_childs = [];
            if ($exam_attendance != "") {
                $exam_attendance_childs = $exam_attendance->examAttendanceChild;
            }
            $exam_attendance_childs;

            $exams = SmExamType::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->get();

            if (teacherAccess()) {
                $teacher_info = SmStaff::where('user_id', Auth::user()->id)->first();

                $classes = SmAssignSubject::where('teacher_id', $teacher_info->id)
                    ->join('sm_classes', 'sm_classes.id', 'sm_assign_subjects.age_group_id')
                    ->where('sm_assign_subjects.church_year_id', getAcademicId())
                    ->where('sm_assign_subjects.active_status', 1)
                    ->where('sm_assign_subjects.church_id', Auth::user()->church_id)
                    ->select('sm_classes.id', 'age_group_name')
                    ->groupBy('sm_classes.id')
                    ->get();
            } else {
                $classes = SmClass::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->get();
            }

            $subjects = SmSubject::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $exam_id = $request->exam;
            $subject_id = $request->subject;
            $age_group_id = $request->class;

            if (($request->section != null)) {
                $mgender_id = $request->section;
            } else {
                $mgender_id = null;
            }

            // return search result
            $class_info = SmClass::find($request->class);
            if (($request->section != null)) {
                $section_info = SmSection::find($request->section);
                $section_info = $section_info->mgender_name;
            } else {
                $section_info = 'All Sections';

            }
            $subject_info = SmSubject::find($request->subject);

            $search_info['age_group_name'] = $class_info->age_group_name;
            $search_info['mgender_name'] = $section_info;
            $search_info['subject_name'] = $subject_info->subject_name;

            return view('backEnd.examination.exam_attendance_create', compact('exams', 'classes', 'subjects', 'students', 'exam_id', 'subject_id', 'age_group_id', 'mgender_id', 'exam_attendance_childs', 'search_info'));
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examAttendanceStore(Request $request)
    {
        try {

            if ($request->mgender_id == '') {
                $alreday_assigned = SmExamAttendance::where('age_group_id', $request->age_group_id)->where('subject_id', $request->subject_id)->where('exam_id', $request->exam_id)->first();

            } else {
                $alreday_assigned = SmExamAttendance::where('age_group_id', $request->age_group_id)->where('mgender_id', $request->mgender_id)->where('subject_id', $request->subject_id)->where('exam_id', $request->exam_id)->first();

            }
            DB::beginTransaction();
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            try {
                if ($request->mgender_id != '') {
                    if ($alreday_assigned == "") {
                        $exam_attendance = new SmExamAttendance();
                    } else {
                        $exam_attendance = SmExamAttendance::where('age_group_id', $request->age_group_id)->where('mgender_id', $request->mgender_id)->where('subject_id', $request->subject_id)->where('exam_id', $request->exam_id)->first();
                    }

                    $exam_attendance->exam_id = $request->exam_id;
                    $exam_attendance->subject_id = $request->subject_id;
                    $exam_attendance->age_group_id = $request->age_group_id;
                    $exam_attendance->mgender_id = $request->mgender_id;
                    $exam_attendance->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                    $exam_attendance->church_id = Auth::user()->church_id;
                    $exam_attendance->church_year_id = getAcademicId();
                    $exam_attendance->save();
                    $exam_attendance->toArray();

                    if ($alreday_assigned != "") {
                        SmExamAttendanceChild::where('exam_attendance_id', $exam_attendance->id)->delete();
                    }

                    foreach ($request->id as $student) {
                        $exam_attendance_child = new SmExamAttendanceChild();
                        $exam_attendance_child->exam_attendance_id = $exam_attendance->id;
                        $exam_attendance_child->member_id = $student;
                        $exam_attendance_child->attendance_type = $request->attendance[$student];
                        $exam_attendance_child->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                        $exam_attendance_child->church_id = Auth::user()->church_id;
                        $exam_attendance_child->church_year_id = getAcademicId();
                        $exam_attendance_child->save();
                    }
                } else {
                    $classSections = SmAssignSubject::where('age_group_id', $request->age_group_id)
                        ->where('subject_id', $request->subject_id)
                        ->where('church_id', auth()->user()->church_id)
                        ->where('church_year_id', getAcademicId())
                        ->groupby(['mgender_id', 'subject_id'])
                        ->get();
                    foreach ($classSections as $section) {
                        if ($alreday_assigned == "") {

                            $exam_attendance = new SmExamAttendance();
                        } else {
                            $exam_attendance = SmExamAttendance::where('age_group_id', $request->age_group_id)->where('mgender_id', $section->mgender_id)
                                ->where('subject_id', $request->subject_id)->where('exam_id', $request->exam_id)->first();
                        }

                        $exam_attendance->exam_id = $request->exam_id;
                        $exam_attendance->subject_id = $request->subject_id;
                        $exam_attendance->age_group_id = $request->age_group_id;
                        $exam_attendance->mgender_id = $section->mgender_id;
                        $exam_attendance->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                        $exam_attendance->church_id = Auth::user()->church_id;
                        $exam_attendance->church_year_id = getAcademicId();
                        $exam_attendance->save();
                        $exam_attendance->toArray();

                        if ($alreday_assigned != "") {
                            SmExamAttendanceChild::where('exam_attendance_id', $exam_attendance->id)->delete();
                        }

                        foreach ($request->id as $student) {
                            $exam_attendance_child = new SmExamAttendanceChild();
                            $exam_attendance_child->exam_attendance_id = $exam_attendance->id;
                            $exam_attendance_child->member_id = $student;
                            $exam_attendance_child->attendance_type = $request->attendance[$student];
                            $exam_attendance_child->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                            $exam_attendance_child->church_id = Auth::user()->church_id;
                            $exam_attendance_child->church_year_id = getAcademicId();
                            $exam_attendance_child->save();
                        }
                    }
                }

                DB::commit();
                Toastr::success('Operation successful', 'Success');
                return redirect('exam-attendance-create');
            } catch (\Exception $e) {
                DB::rollback();
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function sendMarksBySms()
    {
        $exams = SmExamType::where('active_status', 1)
            ->where('church_year_id', getAcademicId())
            ->where('church_id', Auth::user()->church_id)
            ->get();

        if (teacherAccess()) {
            $teacher_info = SmStaff::where('user_id', Auth::user()->id)->first();
            $classes = $teacher_info->classes;
        } else {
            $classes = SmClass::get();
        }
        return view('backEnd.examination.send_marks_by_sms', compact('exams', 'classes'));
    }

    public function sendMarksBySmsStore(Request $request)
    {
        $request->validate([
            'exam' => 'required',
            'class' => 'required',
            'receiver' => 'required',
        ]);
        try {
            $examType = SmExamType::find($request->exam);
            $students = StudentRecord::where('age_group_id',$request->class)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', auth()->user()->church_id)
                ->with('student')
                ->get();

            $marks = SmMarkStore::where('exam_term_id', $request->exam)
                ->whereIn('student_record_id', $students->pluck('id')->toArray())
                ->get();

            $subjectMarkinfos = [];
            foreach($marks as $mark){
                $subjectMarkinfos [$mark->student_record_id][]= ['subject'=>$mark->subjectName->subject_name, 'mark'=>$mark->total_marks];
            }

            foreach($subjectMarkinfos as $key => $subjectMarkinfo){
                $students = StudentRecord::with('student')->find($key);
                $subjects = '';
                $marks = '';
                $subjectMarks = '';
                foreach($subjectMarkinfo as $subjectMarkinf){
                    $subjects .= $subjectMarkinf['subject']. ',';
                    $marks .= $subjectMarkinf['mark']. ',';
                    $subjectMarks .= $subjectMarkinf['subject'] . "-" . $subjectMarkinf['mark']. ',';
                }
                $compact['age_group_name'] = $students->class->age_group_name;
                $compact['mgender_name'] = $students->section->mgender_name;
                $compact['user_email'] = $students->student->email;
                $compact['marks'] = $marks;
                $compact['subject_marks'] = $subjectMarks;
                $compact['exam_type'] = $examType->title;
                $compact['church_name'] = generalSetting()->church_name;
                if($request->receiver == 'students'){
                    @send_sms($students->student->mobile, 'exam_mark_student', $compact);
                }else{
                    $compact['parent_name'] = $students->student->parents->guardians_name;
                    @send_sms($students->student->parents->guardians_mobile, 'exam_mark_parent', $compact);
                }
            }
            Toastr::success('Operation successful', 'Success');
            return redirect()->route('send_marks_by_sms');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function meritListReport(Request $request)
    {
        try {
            $exams = SmExamType::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->get();

            if (teacherAccess()) {
                $teacher_info = SmStaff::where('user_id', Auth::user()->id)->first();
                $classes = SmAssignSubject::where('teacher_id', $teacher_info->id)->join('sm_classes', 'sm_classes.id', 'sm_assign_subjects.age_group_id')
                    ->where('sm_assign_subjects.church_year_id', getAcademicId())
                    ->where('sm_assign_subjects.active_status', 1)
                    ->where('sm_assign_subjects.church_id', Auth::user()->church_id)
                    ->select('sm_classes.id', 'age_group_name')
                    ->groupBy('sm_classes.id')
                    ->get();
            } else {
                $classes = SmClass::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->get();
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['exams'] = $exams->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.reports.merit_list_report', compact('exams', 'classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function reportsTabulationSheet()
    {
        try {
            $exams = SmExam::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            return view('backEnd.reports.report_tabulation_sheet', compact('exams', 'classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function reportsTabulationSheetSearch(Request $request)
    {
        try {
            $exams = SmExam::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            return view('backEnd.reports.report_tabulation_sheet', compact('exams', 'classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    //end tabulation sheet report

    public function make_merit_list($InputClassId, $InputSectionId, $InputExamId, Request $request)
    {
        $iid = time();
        $class = SmClass::find($InputClassId);
        $section = SmSection::find($InputSectionId);
        $exam = SmExamType::find($InputExamId);
        $is_data = DB::table('sm_mark_stores')->where([['age_group_id', $InputClassId], ['mgender_id', $InputSectionId], ['exam_term_id', $InputExamId]])->first();
        if (empty($is_data)) {
            Toastr::error('Your result is not found!', 'Failed');
            return redirect()->back();
            // return redirect()->back()->with('message-danger', 'Your result is not found!');
        }
        $exams = SmExamType::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
        $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
        $subjects = SmSubject::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
        $assign_subjects = SmAssignSubject::where('age_group_id', $class->id)->where('mgender_id', $section->id)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
        $age_group_name = $class->age_group_name;
        $exam_name = $exam->title;
        $eligible_subjects = SmAssignSubject::where('age_group_id', $InputClassId)->where('mgender_id', $InputSectionId)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
        $eligible_students = SmStudent::where('age_group_id', $InputClassId)->where('mgender_id', $InputSectionId)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();

        //all subject list in a specific class/section
        $subject_ids = [];
        $subject_strings = '';
        $marks_string = '';
        foreach ($eligible_students as $SingleStudent) {
            foreach ($eligible_subjects as $subject) {
                $subject_ids[] = $subject->subject_id;
                $subject_strings = (empty($subject_strings)) ? $subject->subject->subject_name : $subject_strings . ',' . $subject->subject->subject_name;

                $getMark = SmResultStore::where([
                    ['exam_type_id', $InputExamId],
                    ['age_group_id', $InputClassId],
                    ['mgender_id', $InputSectionId],
                    ['member_id', $SingleStudent->id],
                    ['subject_id', $subject->subject_id],
                ])->first();
                if ($getMark == "") {
                    Toastr::error('Please register marks for all students.!', 'Failed');
                    return redirect()->back();
                    // return redirect()->back()->with('message-danger', 'Please register marks for all students.!');
                }
                if ($marks_string == "") {
                    if ($getMark->total_marks == 0) {
                        $marks_string = '0';
                    } else {
                        $marks_string = $getMark->total_marks;
                    }
                } else {
                    $marks_string = $marks_string . ',' . $getMark->total_marks;
                }
            }

            //end subject list for specific section/class

            $results = SmResultStore::where([
                ['exam_type_id', $InputExamId],
                ['age_group_id', $InputClassId],
                ['mgender_id', $InputSectionId],
                ['member_id', $SingleStudent->id],
            ])->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $is_absent = SmResultStore::where([
                ['exam_type_id', $InputExamId],
                ['age_group_id', $InputClassId],
                ['mgender_id', $InputSectionId],
                ['is_absent', 1],
                ['member_id', $SingleStudent->id],
            ])->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();

            $total_gpa_point = SmResultStore::where([
                ['exam_type_id', $InputExamId],
                ['age_group_id', $InputClassId],
                ['mgender_id', $InputSectionId],
                ['member_id', $SingleStudent->id],
            ])->sum('total_gpa_point');

            $total_marks = SmResultStore::where([
                ['exam_type_id', $InputExamId],
                ['age_group_id', $InputClassId],
                ['mgender_id', $InputSectionId],
                ['member_id', $SingleStudent->id],
            ])->sum('total_marks');

            $sum_of_mark = ($total_marks == 0) ? 0 : $total_marks;
            $average_mark = ($total_marks == 0) ? 0 : floor($total_marks / $results->count()); //get average number
            $is_absent = (count($is_absent) > 0) ? 1 : 0; //get is absent ? 1=Absent, 0=Present
            $total_GPA = ($total_gpa_point == 0) ? 0 : $total_gpa_point / $results->count();
            $exart_gp_point = number_format($total_GPA, 2, '.', ''); //get gpa results
            $full_name = $SingleStudent->full_name; //get name
            $registration_no = $SingleStudent->registration_no; //get admission no
            $member_id = $SingleStudent->id; //get admission no
            $is_existing_data = SmTemporaryMeritlist::where([['registration_no', $registration_no], ['age_group_id', $InputClassId], ['mgender_id', $InputSectionId], ['exam_id', $InputExamId]])->first();
            if (empty($is_existing_data)) {
                $insert_results = new SmTemporaryMeritlist();
            } else {
                $insert_results = SmTemporaryMeritlist::find($is_existing_data->id);
            }
            $insert_results->member_name = $full_name;
            $insert_results->registration_no = $registration_no;
            $insert_results->subjects_string = $subject_strings;
            $insert_results->marks_string = $marks_string;
            $insert_results->total_marks = $sum_of_mark;
            $insert_results->average_mark = $average_mark;
            $insert_results->gpa_point = $exart_gp_point;
            $insert_results->iid = $iid;
            $insert_results->member_id = $member_id;
            $markGrades = SmMarksGrade::where([['from', '<=', $exart_gp_point], ['up', '>=', $exart_gp_point]])->where('church_id', Auth::user()->church_id)->first();

            if ($is_absent == "") {
                $insert_results->result = $markGrades->grade_name;
            } else {
                $insert_results->result = 'F';
            }
            $insert_results->mgender_id = $InputSectionId;
            $insert_results->age_group_id = $InputClassId;
            $insert_results->exam_id = $InputExamId;
            $insert_results->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
            $insert_results->church_id = Auth::user()->church_id;
            $insert_results->church_year_id = getAcademicId();
            $insert_results->save();

            $subject_strings = "";
            $marks_string = "";
            $total_marks = 0;
            $average = 0;
            $exart_gp_point = 0;
            $registration_no = 0;
            $full_name = "";
        } //end loop eligible_students

        $first_data = SmTemporaryMeritlist::where('iid', $iid)->first();
        $subjectlist = explode(',', $first_data->subjects_string);
        $allresult_data = SmTemporaryMeritlist::where('iid', $iid)->orderBy('gpa_point', 'desc')->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
        $merit_serial = 1;
        foreach ($allresult_data as $row) {
            $D = SmTemporaryMeritlist::where('iid', $iid)->where('id', $row->id)->first();
            $D->merit_order = $merit_serial++;
            $D->save();
        }

        $allresult_data = SmTemporaryMeritlist::orderBy('merit_order', 'asc')->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();


        $data['iid'] = $iid;
        $data['exams'] = $exams;
        $data['classes'] = $classes;
        $data['subjects'] = $subjects;
        $data['class'] = $class;
        $data['section'] = $section;
        $data['exam'] = $exam;
        $data['subjectlist'] = $subjectlist;
        $data['allresult_data'] = $allresult_data;
        $data['age_group_name'] = $age_group_name;
        $data['assign_subjects'] = $assign_subjects;
        $data['exam_name'] = $exam_name;
        $data['InputClassId'] = $InputClassId;
        $data['InputExamId'] = $InputExamId;
        $data['InputSectionId'] = $InputSectionId;
        return $data;
    }

    public function meritListReportSearch(MeritListReportRequest $request)
    {
        try {
            if (moduleStatusCheck('University')) {
                $common = new ExamCommonController();
                return $common->meritListReport((object)$request->all());
            } else {
                $iid = time();
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                if ($request->method() == 'POST') {
                    $InputClassId = $request->class;
                    $InputExamId = $request->exam;
                    $InputSectionId = $request->section;

                    $class = SmClass::with('academic')->find($InputClassId);
                    $section = SmSection::find($InputSectionId);
                    $exam = SmExamType::find($InputExamId);

                    $optional_subject_setup = SmClassOptionalSubject::where('age_group_id', '=', $request->class)->first();

                    $is_data = DB::table('sm_mark_stores')
                        ->where([
                            ['age_group_id', $InputClassId],
                            ['mgender_id', $InputSectionId],
                            ['exam_term_id', $InputExamId]])
                        ->first();
                    if (empty($is_data)) {
                        Toastr::error('Your result is not found!', 'Failed');
                        return redirect()->back();
                    }

                    $examSubjects = SmExam::where([['exam_type_id', $InputExamId], ['mgender_id', $InputSectionId], ['age_group_id', $InputClassId]])
                        ->where('church_id', Auth::user()->church_id)
                        ->where('church_year_id', getAcademicId())
                        ->get();

                    $examSubjectIds = [];
                    foreach ($examSubjects as $examSubject) {
                        $examSubjectIds[] = $examSubject->subject_id;
                    }

                    $exams = SmExamType::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->get();

                    $classes = SmClass::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->get();

                    $subjects = SmSubject::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->get();

                    $assign_subjects = SmAssignSubject::where('age_group_id', $class->id)
                        ->where('mgender_id', $section->id)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->whereIn('subject_id', $examSubjectIds)
                        ->get();

                    $age_group_name = $class->age_group_name;

                    $exam_name = $exam->title;

                    $eligible_subjects = SmAssignSubject::where('age_group_id', $InputClassId)
                        ->where('mgender_id', $InputSectionId)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->whereIn('subject_id', $examSubjectIds)
                        ->get();
                    $member_ids = SmStudentReportController::classSectionStudent($request);
                    $eligible_students = SmStudent::whereIn('id', $member_ids)->where('church_id', Auth::user()->church_id)
                        ->where('active_status', 1)->get();
                    $subject_total_mark = 0;
                    $failgpa = SmMarksGrade::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->min('gpa');

                    $failgpaname = SmMarksGrade::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->where('gpa', $failgpa)
                        ->first();

                    $maxGpa = SmMarksGrade::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->max('gpa');
                    //all subject list in a specific class/section
                    $subject_ids = [];
                    $subject_strings = '';
                    $subject_id_strings = '';
                    $marks_string = '';
                    foreach ($eligible_students as $SingleStudent) {
                        foreach ($eligible_subjects as $subject) {
                            $subject_ids[] = $subject->subject_id;
                            $subject_strings = (empty($subject_strings)) ? $subject->subject->subject_name : $subject_strings . ',' . $subject->subject->subject_name;
                            $subject_id_strings = (empty($subject_id_strings)) ? $subject->subject_id : $subject_id_strings . ',' . $subject->subject_id;
                            $getMark = SmResultStore::where([
                                ['exam_type_id', $InputExamId],
                                ['age_group_id', $InputClassId],
                                ['mgender_id', $InputSectionId],
                                ['member_id', $SingleStudent->id],
                                ['subject_id', $subject->subject_id],
                            ])->first();
                            if ($getMark == "") {
                                Toastr::error('Please register marks for all students & all subjects.!', 'Failed');
                                return redirect()->back();
                            }

                            $subject_total_mark += subjectFullMark($InputExamId, $subject->subject_id, $InputClassId, $InputSectionId);
                            $subject_total_mark = SmExam::where('age_group_id', $InputClassId)->where('mgender_id', $InputSectionId)->where('exam_type_id', $InputExamId)->where('subject_id', $subject->subject_id)->first('exam_mark')->exam_mark;
                            $obtain_mark = $getMark->total_marks;

                            if (generalSetting()->result_type == 'mark') {
                                if ($obtain_mark != 0) {
                                    $obtain_mark = (($obtain_mark * 100) / $subject_total_mark);
                                }
                            }

                            if ($marks_string == "") {
                                if ($getMark->total_marks == 0) {
                                    $marks_string = '0';
                                } else {
                                    $marks_string = $obtain_mark;
                                }
                            } else {
                                $marks_string = $marks_string . ',' . $obtain_mark;
                            }
                        }


                        //end subject list for specific section/class

                        $results = SmResultStore::where([
                            ['exam_type_id', $InputExamId],
                            ['age_group_id', $InputClassId],
                            ['mgender_id', $InputSectionId],
                            ['member_id', $SingleStudent->id],
                        ])->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();

                        $is_absent = SmResultStore::where([
                            ['exam_type_id', $InputExamId],
                            ['age_group_id', $InputClassId],
                            ['mgender_id', $InputSectionId],
                            ['is_absent', 1],
                            ['member_id', $SingleStudent->id],
                        ])->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();

                        $total_gpa_point = SmResultStore::where([
                            ['exam_type_id', $InputExamId],
                            ['age_group_id', $InputClassId],
                            ['mgender_id', $InputSectionId],
                            ['member_id', $SingleStudent->id],
                        ])->sum('total_gpa_point');

                        $total_marks = SmResultStore::where([
                            ['exam_type_id', $InputExamId],
                            ['age_group_id', $InputClassId],
                            ['mgender_id', $InputSectionId],
                            ['member_id', $SingleStudent->id],
                        ])->sum('total_marks');

                        $dat = array();
                        $sum_of_mark = ($total_marks == 0) ? 0 : $total_marks;

                        $average_mark = ($total_marks == 0) ? 0 : floor($total_marks / $results->count()); //get average number
                        $is_absent = (count($is_absent) > 0) ? 1 : 0; //get is absent ? 1=Absent, 0=Present

                        foreach ($results as $key => $gpa_result) {
                            $da = DB::table('sm_optional_subject_assigns')->where(['member_id' => $gpa_result->member_id, 'subject_id' => $gpa_result->subject_id])->count();
                            if ($da < 1) {
                                $grade_gpa = markGpa($subject_total_mark);

                                if ($grade_gpa->grade_name == $failgpaname) {
                                    array_push($dat, $grade_gpa->gpa);
                                }
                            }
                        }
                        $total_GPA = ($total_gpa_point == 0) ? 0 : $total_gpa_point / $results->count();
                        if (!empty($dat)) {
                            $exart_gp_point = $dat['0'];
                        } else {

                            $exart_gp_point = number_format($total_GPA, 2, '.', ''); //get gpa results
                        }
                        $student_gpa_point = number_format($total_GPA, 2, '.', '');

                        $full_name = $SingleStudent->full_name; //get name
                        $registration_no = $SingleStudent->registration_no; //get admission no
                        $roll_no = $SingleStudent->roll_no; //get admission no
                        $member_id = $SingleStudent->id; //get admission no

                        $is_existing_data = SmTemporaryMeritlist::where([
                            ['registration_no', $registration_no],
                            ['age_group_id', $InputClassId],
                            ['mgender_id', $InputSectionId],
                            ['exam_id', $InputExamId]])
                            ->first();

                        // return $is_existing_data;
                        if (empty($is_existing_data)) {
                            $insert_results = new SmTemporaryMeritlist();
                        } else {
                            $insert_results = SmTemporaryMeritlist::find($is_existing_data->id);
                        }
                        // $insert_results                     = new SmTemporaryMeritlist();
                        $insert_results->merit_order = $student_gpa_point;
                        $insert_results->member_name = $full_name;
                        $insert_results->registration_no = $registration_no;
                        $insert_results->roll_no = $roll_no;
                        $insert_results->subjects_id_string = implode(',', array_unique($subject_ids));
                        $insert_results->subjects_string = $subject_strings;
                        $insert_results->marks_string = $marks_string;
                        $insert_results->total_marks = $sum_of_mark;
                        $insert_results->average_mark = $average_mark;
                        $insert_results->gpa_point = $exart_gp_point;
                        $insert_results->iid = $iid;
                        $insert_results->member_id = $SingleStudent->id;
                        $markGrades = getGrade($exart_gp_point);

                        $insert_results->result = (is_null($is_absent)) ? "F" : @$markGrades->grade_name;

                        $insert_results->mgender_id = $InputSectionId;
                        $insert_results->age_group_id = $InputClassId;
                        $insert_results->exam_id = $InputExamId;
                        $insert_results->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                        $insert_results->church_id = Auth::user()->church_id;
                        $insert_results->church_year_id = getAcademicId();
                        $insert_results->save();

                        $subject_strings = "";
                        $marks_string = "";
                        $total_marks = 0;
                        $average = 0;
                        $exart_gp_point = 0;
                        $registration_no = 0;
                        $full_name = "";
                    } //end loop eligible_students

                    $first_data = SmTemporaryMeritlist::where('iid', $iid)->first();

                    $subjectlist = explode(',', @$first_data->subjects_string);

                    $meritListSettings = CustomResultSetting::first('merit_list_setting')->merit_list_setting;

                    if ($meritListSettings == "total_grade") {
                        $allresult_data = SmTemporaryMeritlist::where('exam_id', '=', $InputExamId)
                            ->where('church_year_id', getAcademicId())
                            ->where('church_id', Auth::user()->church_id)
                            ->where('age_group_id', $class->id)
                            ->where('mgender_id', $section->id)
                            ->orderBy('merit_order', 'desc')
                            ->get();

                    } elseif($meritListSettings == "total_mark") {
                        $allresult_data = SmTemporaryMeritlist::where('exam_id', '=', $InputExamId)
                            ->where('church_year_id', getAcademicId())
                            ->where('church_id', Auth::user()->church_id)
                            ->where('age_group_id', $class->id)
                            ->where('mgender_id', $section->id)
                            ->orderBy('total_marks', 'desc')
                            ->get();

                    }else{
                        $allresult_data = SmTemporaryMeritlist::where('exam_id', '=', $InputExamId)
                            ->where('church_year_id', getAcademicId())
                            ->where('church_id', Auth::user()->church_id)
                            ->where('age_group_id', $class->id)
                            ->where('mgender_id', $section->id)
                            ->orderBy('roll_no', 'asc')
                            ->get();
                    }

                    $exam_content = SmExamSetting::where('exam_type', $InputExamId)
                        ->where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->first();
                    if ($optional_subject_setup == '') {
                        return view('backEnd.reports.merit_list_report_normal', compact('iid', 'exams', 'classes', 'subjects', 'class', 'section', 'exam', 'subjectlist', 'allresult_data', 'age_group_name', 'assign_subjects', 'exam_name', 'InputClassId', 'InputExamId', 'InputSectionId', 'optional_subject_setup', 'failgpaname', 'subject_total_mark', 'exam_content'));
                    } else {
                        return view('backEnd.reports.merit_list_report', compact('iid', 'exams', 'classes', 'subjects', 'class', 'section', 'exam', 'subjectlist', 'allresult_data', 'age_group_name', 'assign_subjects', 'exam_name', 'InputClassId', 'InputExamId', 'InputSectionId', 'optional_subject_setup', 'failgpaname', 'failgpa', 'maxGpa', 'exam_content'));
                    }
                }
            }
        } catch (\Exception $e) {
            $msg = str_replace("'", " ", $e->getMessage());
            Toastr::error($msg, 'Failed');
            return redirect()->back();
        }
    }

    public function meritListPrint($exam_id, $age_group_id, $mgender_id)
    {
        set_time_limit(2700);
        try {
            // $iid = time();
            // DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // $emptyResult = SmTemporaryMeritlist::truncate();

            $InputClassId = $age_group_id;
            $InputExamId = $exam_id;
            $InputSectionId = $mgender_id;
            $exam_content = SmExamSetting::where('exam_type', $InputExamId)
                ->where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->first();

            $class = SmClass::with('academic')->find($InputClassId);
            $section = SmSection::find($InputSectionId);
            $exam = SmExamType::find($InputExamId);

            // $is_data = DB::table('sm_mark_stores')->where([['age_group_id', $InputClassId], ['mgender_id', $InputSectionId], ['exam_term_id', $InputExamId]])->first();

            $exams = SmExamType::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $classes = SmClass::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $subjects = SmSubject::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $examSubjects = SmExam::where([['mgender_id', $InputSectionId], ['age_group_id', $InputClassId]])
                ->where('church_id', Auth::user()->church_id)
                ->where('church_year_id', getAcademicId())
                ->get();

            $examSubjectIds = [];
            foreach ($examSubjects as $examSubject) {
                $examSubjectIds[] = $examSubject->subject_id;
            }

            $assign_subjects = SmAssignSubject::where('age_group_id', $class->id)
                ->where('mgender_id', $section->id)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->whereIn('subject_id', $examSubjectIds)
                ->get();
            foreach ($assign_subjects as $subjects) {
                $subject = $subjects->subject_id;

            }

            $subject_total_mark = subjectFullMark($InputExamId, $subject, $class->id, $section->id);

            $age_group_name = $class->age_group_name;
            $exam_name = $exam->title;

            $optional_subject_setup = SmClassOptionalSubject::where('age_group_id', '=', $age_group_id)->first();

            $allresult_dat = SmTemporaryMeritlist::orderBy('merit_order', 'asc')
                ->where(['exam_id' => $exam_id,
                    'age_group_id' => $age_group_id,
                    'mgender_id' => $mgender_id])
                ->where('church_year_id', getAcademicId())
                ->first();

            $meritListSettings = CustomResultSetting::first('merit_list_setting')->merit_list_setting;

            if ($meritListSettings == "total_grade") {
                $allresult_data = SmTemporaryMeritlist::where(
                    ['exam_id' => $exam_id,
                        'age_group_id' => $age_group_id,
                        'mgender_id' => $mgender_id])
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->orderBy('merit_order', 'desc')
                    ->get();
            } elseif($meritListSettings == "total_mark") {
                $allresult_data = SmTemporaryMeritlist::where(
                    ['exam_id' => $exam_id,
                        'age_group_id' => $age_group_id,
                        'mgender_id' => $mgender_id])
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->orderBy('total_marks', 'desc')
                    ->get();
            }else{
                $allresult_data = SmTemporaryMeritlist::where(
                    ['exam_id' => $exam_id,
                        'age_group_id' => $age_group_id,
                        'mgender_id' => $mgender_id])
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->orderBy('roll_no', 'asc')
                    ->get();
            }

            $grades = SmMarksGrade::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $failgpa = SmMarksGrade::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->min('gpa');

            $failgpaname = SmMarksGrade::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->where('gpa', $failgpa)
                ->first();

            $maxGpa = SmMarksGrade::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->max('gpa');

            // $allresult_data = SmTemporaryMeritlist::orderBy('merit_order', 'asc')->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();
            $subjectlist = explode(',', $allresult_dat->subjects_string);

            return view('backEnd.reports.merit_list_report_print', compact('exams', 'classes', 'subjects', 'class', 'section', 'exam', 'subjectlist', 'allresult_data', 'age_group_name', 'assign_subjects', 'exam_name', 'optional_subject_setup', 'subject_total_mark', 'failgpa', 'maxGpa', 'failgpaname', 'InputClassId', 'InputExamId', 'InputSectionId', 'exam_content'));

            $pdf = PDF::loadView(
                'backEnd.reports.merit_list_report_print',
                [
                    'exams' => $exams,
                    'classes' => $classes,
                    'subjects' => $subjects,
                    'class' => $class,
                    'section' => $section,
                    'exam' => $exam,
                    'subjectlist' => $subjectlist,
                    'allresult_data' => $allresult_data,
                    'age_group_name' => $age_group_name,
                    'assign_subjects' => $assign_subjects,
                    'exam_name' => $exam_name,
                    'grades' => $grades,
                    'optional_subject_setup' => $optional_subject_setup,
                    'exam_content' => $exam_content
                ]
            )->setPaper('A4', 'landscape');

            return $pdf->stream('student_merit_list.pdf');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }

    }

    public function markSheetReport()
    {
        try {
            $exams = SmExamType::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            return view('backEnd.reports.mark_sheet_report', compact('exams', 'classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function markSheetReportSearch(Request $request)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $request->validate([
            'exam' => 'required',
            'class' => 'required',
            'section' => 'required',
        ]);
        try {
            $class = SmClass::find($request->class);
            $section = SmSection::find($request->section);
            $exam = SmExam::find($request->exam);

            $subjects = SmAssignSubject::where('age_group_id', $request->class)->where('mgender_id', $request->section)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $all_students = SmStudent::where('age_group_id', $request->class)->where('mgender_id', $request->section)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();

            $marks_registers = SmMarksRegister::where('exam_id', $request->exam)->where('age_group_id', $request->class)->where('mgender_id', $request->section)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();

            $marks_register = SmMarksRegister::where('exam_id', $request->exam)->where('age_group_id', $request->class)->where('mgender_id', $request->section)->first();
            if ($marks_registers->count() == 0) {
                Toastr::error('Result not found', 'Failed');
                return redirect()->back();
                // return redirect('mark-sheet-report')->with('message-danger', 'Result not found');
            }
            // $marks_register_childs = $marks_register->marksRegisterChilds;
            $exams = SmExam::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $grades = SmMarksGrade::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();

            $exam_id = $request->exam;
            $age_group_id = $request->class;

            return view('backEnd.reports.mark_sheet_report', compact('exams', 'classes', 'marks_registers', 'marks_register', 'all_students', 'subjects', 'class', 'section', 'exam', 'grades', 'exam_id', 'age_group_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function markSheetReportStudent(Request $request)
    {
        try {
            $exams = SmExamType::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            return view('backEnd.reports.mark_sheet_report_student', compact('exams', 'classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    //marks     SheetReport     Student     Search

    public function markSheetReportStudentSearch(MarkSheetReportRequest $request)
    {
        try {
            $total_class_days = 0;
            $student_attendance = 0;
            $input['exam_id'] = $request->exam;
            $input['age_group_id'] = $request->class;
            $input['mgender_id'] = $request->section;
            $input['member_id'] = $request->student;

            if (moduleStatusCheck('University')) {
                $exam_type = $request->exam_type;
                $member_id = $request->member_id;

                // Attendance Part Start
                $exam_content = SmExamSetting::where('exam_type', $exam_type)
                    ->where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->first();


                if (isset($exam_content)) {
                    $total_class_day = SmStudentAttendance::whereBetween('attendance_date', [$exam_content->start_date, $exam_content->end_date])
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->where('attendance_type', '!=', 'H')
                        ->groupBy('attendance_date')
                        ->get();

                    $total_class_days = count($total_class_day);

                    $student_attendance = SmStudentAttendance::where('member_id', $member_id)
                        ->whereBetween('attendance_date', [$exam_content->start_date, $exam_content->end_date])
                        ->whereIn('attendance_type', ["P", "L", "H"])
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->count();
                }
                // Attendance Part Start

                //Falil Grade Dynamic Start
                $failgpa = SmMarksGrade::min('gpa');

                $failgpaname = SmMarksGrade::where('gpa', $failgpa)->first();
                //Falil Grade Dynamic End

                $exams = SmExamType::get();

                $exam_details = $exams->where('active_status', 1)->find($exam_type);

                $StudentRecord = StudentRecord::query();
                $student_detail = universityFilter($StudentRecord, $request)
                    ->where('member_id', $member_id)
                    ->with('student')
                    ->first();

                $SmExam = SmExam::query();
                $examSubjects = universityFilter($SmExam, $request)
                    ->where('exam_type_id', $exam_type)
                    ->get();

                $examSubjectIds = [];
                foreach ($examSubjects as $examSubject) {
                    $examSubjectIds[] = $examSubject->un_subject_id;
                }

                $UnSubject = UnSubject::query();
                $subjects = $UnSubject->when($request->un_faculty_id, function ($q) use ($request) {
                    $q->where('un_faculty_id', $request->un_faculty_id);
                })
                    ->when($request->un_department_id, function ($q) use ($request) {
                        $q->where('un_department_id', $request->un_department_id);
                    })
                    ->whereIn('id', $examSubjectIds);

                $optional_subject = '';

                $get_optional_subject = SmOptionalSubjectAssign::where('member_id', '=', $member_id)
                    ->first();

                if ($get_optional_subject != '') {
                    $optional_subject = $get_optional_subject->subject_id;
                }

                $optional_subject_setup = SmClassOptionalSubject::first();

                $SmResultStore = SmResultStore::query();
                $mark_sheet = universityFilter($SmResultStore, $request)
                    ->where([
                        ['exam_type_id', $exam_type],
                        ['member_id', $member_id]
                    ])
                    ->whereIn('un_subject_id', $subjects->pluck('id')->toArray())
                    ->with('unSubjectDetails')
                    ->get();

                $grades = SmMarksGrade::orderBy('gpa', 'desc')->get();

                $maxGrade = SmMarksGrade::max('gpa');

                if (count($mark_sheet) == 0) {
                    Toastr::error('Ops! Your result is not found! Please check mark register', 'Failed');
                    return redirect('mark-sheet-report-student');
                }

                $SmResultStor = SmResultStore::query();
                $is_result_available = universityFilter($SmResultStor, $request)
                    ->where([
                        ['exam_type_id', $exam_type],
                        ['member_id', $member_id]
                    ])
                    ->where('created_at', 'LIKE', '%' . YearCheck::getYear() . '%')
                    ->where('church_id', Auth::user()->church_id)
                    ->get();
                $un_session = UnSession::find($request->un_session_id);
                $un_faculty = UnFaculty::find($request->un_faculty_id);
                $un_department = UnDepartment::find($request->un_department_id);
                $un_academic = UnAcademicYear::find($request->un_church_year_id);
                $un_semester = UnSemester::find($request->un_semester_id);
                $un_semester_label = UnSemesterLabel::find($request->un_semester_label_id);
                $un_section = SmSection::find($request->un_mgender_id);

                //$exam_detail = SmExam::find($request->exam);

                return view('backEnd.reports.mark_sheet_report_student', compact(
                    'mark_sheet',
                    'subjects',
                    'grades',
                    'student_detail',
                    'exam_details',
                    'optional_subject',
                    'un_session',
                    'un_faculty',
                    'un_department',
                    'un_academic',
                    'un_semester',
                    'un_semester_label',
                    'maxGrade',
                    'failgpaname',
                    'exam_type',
                    'member_id',
                    'un_section'
                ));
            } else {
                // Attendance Part Start
                $exam_content = SmExamSetting::where('exam_type', $request->exam)
                    ->where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->first();

                if ($exam_content) {
                    $total_class_day = SmStudentAttendance::whereBetween('attendance_date', [$exam_content->start_date, $exam_content->end_date])
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->where('attendance_type', '!=', 'H')
                        ->groupBy('attendance_date')
                        ->get();

                    $total_class_days = count($total_class_day);

                    $student_attendance = SmStudentAttendance::where('member_id', $request->student)
                        ->whereBetween('attendance_date', [$exam_content->start_date, $exam_content->end_date])
                        ->whereIn('attendance_type', ["P", "L", "H"])
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id', Auth::user()->church_id)
                        ->count();
                }
                // Attendance Part End

                $failgpa = SmMarksGrade::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->min('gpa');

                $failgpaname = SmMarksGrade::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->where('gpa', $failgpa)
                    ->first();

                $exams = SmExamType::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->get();

                $classes = SmClass::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->get();

                $student_detail = $studentDetails = StudentRecord::where('member_id', $request->student)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->first();


                $examSubjects = SmExam::where([['exam_type_id', $request->exam], ['mgender_id', $request->section], ['age_group_id', $request->class]])
                    ->where('church_id', Auth::user()->church_id)
                    ->where('church_year_id', getAcademicId())
                    ->get();
                $examSubjectIds = [];
                foreach ($examSubjects as $examSubject) {
                    $examSubjectIds[] = $examSubject->subject_id;
                }

                $subjects = $studentDetails->class->subjects->where('mgender_id', $request->section)
                    ->whereIn('subject_id', $examSubjectIds)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id);
                $subjects = $examSubjects;

                $mgender_id = $request->section;
                $age_group_id = $request->class;
                $exam_type_id = $request->exam;
                $member_id = $request->student;
                $exam_details = $exams->where('active_status', 1)->find($exam_type_id);


                $optional_subject = '';

                $get_optional_subject = SmOptionalSubjectAssign::where('member_id', '=', $student_detail->id)
                    ->where('session_id', '=', $student_detail->session_id)
                    ->first();

                if ($get_optional_subject != '') {
                    $optional_subject = $get_optional_subject->subject_id;
                }

                $optional_subject_setup = SmClassOptionalSubject::where('age_group_id', '=', $request->class)
                    ->first();

                $mark_sheet = SmResultStore::where([['age_group_id', $request->class], ['exam_type_id', $request->exam], ['mgender_id', $request->section], ['member_id', $request->student]])
                    ->whereIn('subject_id', $subjects->pluck('subject_id')->toArray())
                    ->where('church_id', Auth::user()->church_id)
                    ->get();


                $grades = SmMarksGrade::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->orderBy('gpa', 'desc')
                    ->get();

                $maxGrade = SmMarksGrade::where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->max('gpa');

                if (count($mark_sheet) == 0) {
                    Toastr::error('Ops! Your result is not found! Please check mark register', 'Failed');
                    return redirect('mark-sheet-report-student');
                }

                $is_result_available = SmResultStore::where([['age_group_id', $request->class], ['exam_type_id', $request->exam], ['mgender_id', $request->section], ['member_id', $request->student]])
                    ->where('created_at', 'LIKE', '%' . YearCheck::getYear() . '%')
                    ->where('church_id', Auth::user()->church_id)
                    ->get();

                $marks_register = SmMarksRegister::where('exam_id', $request->exam)
                    ->where('member_id', $request->student)
                    ->first();

                $subjects = SmAssignSubject::where('age_group_id', $request->class)
                    ->where('mgender_id', $request->section)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->get();

                $exams = SmExamType::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->get();

                $classes = SmClass::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->get();

                $grades = SmMarksGrade::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->get();

                $class = SmClass::find($request->class);
                $section = SmSection::find($request->section);
                $exam_detail = SmExam::find($request->exam);
                $exam_id = $request->exam;
                $age_group_id = $request->class;

                return view('backEnd.reports.mark_sheet_report_student', compact(
                    'optional_subject',
                    'classes',
                    'studentDetails',
                    'exams',
                    'classes',
                    'marks_register',
                    'subjects',
                    'class',
                    'section',
                    'exam_detail',
                    'grades',
                    'exam_id',
                    'age_group_id',
                    'student_detail',
                    'input',
                    'mark_sheet',
                    'exam_details',
                    'maxGrade',
                    'failgpaname',
                    'exam_type_id',
                    'mgender_id', 'exam_content', 'total_class_days', 'student_attendance'));
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function markSheetReportStudentPrint($exam_id, $age_group_id, $mgender_id, $member_id)
    {
        try {
            $total_class_days = 0;
            $student_attendance = 0;

            $student_detail = $studentDetails = StudentRecord::where('member_id', $member_id)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->first();

            $examSubjects = SmExam::where([['exam_type_id', $exam_id], ['mgender_id', $mgender_id], ['age_group_id', $age_group_id]])
                ->where('church_id', Auth::user()->church_id)
                ->where('church_year_id', getAcademicId())
                ->get();

            $examSubjectIds = [];
            foreach ($examSubjects as $examSubject) {
                $examSubjectIds[] = $examSubject->subject_id;
            }

            $subjects = $examSubjects;


            $mark_sheet = SmResultStore::where([['age_group_id', $age_group_id], ['exam_type_id', $exam_id], ['mgender_id', $mgender_id], ['member_id', $member_id]])
                ->whereIn('subject_id', $subjects->pluck('subject_id')->toArray())
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $exams = SmExamType::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $grades = SmMarksGrade::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->orderBy('gpa', 'desc')
                ->get();

            $maxGrade = SmMarksGrade::where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->max('gpa');

            $classes = SmClass::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $exam_types = SmExamType::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $subjects = SmAssignSubject::where([['age_group_id', $age_group_id], ['mgender_id', $mgender_id]])
                ->whereIn('subject_id', $examSubjectIds)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $section = SmSection::where('active_status', 1)
                ->where('id', $mgender_id)
                ->first();

            $failgpa = SmMarksGrade::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->min('gpa');

            $failgpaname = SmMarksGrade::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->where('gpa', $failgpa)
                ->first();


            $exam_content = SmExamSetting::where('exam_type', $exam_id)
                ->where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->first();

            if (isset($exam_content)) {
                $total_class_day = SmStudentAttendance::whereBetween('attendance_date', [$exam_content->start_date, $exam_content->end_date])
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->where('attendance_type', '!=', 'H')
                    ->groupBy('attendance_date')
                    ->get();

                $total_class_days = count($total_class_day);

                $student_attendance = SmStudentAttendance::where('member_id', $member_id)
                    ->whereBetween('attendance_date', [$exam_content->start_date, $exam_content->end_date])
                    ->orWhere([['attendance_type', '=', "P"], ['attendance_type', '=', "L"]])
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->count();
            }

            $mgender_id = $mgender_id;
            $age_group_id = $age_group_id;
            $age_group_name = SmClass::find($age_group_id);
            $exam_type_id = $exam_id;
            $member_id = $member_id;
            $exam_details = SmExamType::where('active_status', 1)->find($exam_type_id);
            $optional_subject = '';

            $get_optional_subject = SmOptionalSubjectAssign::where('member_id', '=', $student_detail->id)
                ->where('session_id', '=', $student_detail->session_id)
                ->first();

            if ($get_optional_subject != '') {
                $optional_subject = $get_optional_subject->subject_id;
            }

            $optional_subject_setup = SmClassOptionalSubject::where('age_group_id', '=', $age_group_id)->first();
            $is_result_available = SmResultStore::where([['age_group_id', $age_group_id], ['exam_type_id', $exam_id], ['mgender_id', $mgender_id], ['member_id', $member_id]])
                ->where('church_year_id', getAcademicId())
                ->get();

            if ($is_result_available->count() > 0) {
                if ($optional_subject == '') {
                    return view('backEnd.reports.mark_sheet_report_normal_print', [
                            'exam_types' => $exam_types,
                            'grades' => $grades,
                            'classes' => $classes,
                            'subjects' => $subjects,
                            'class' => $age_group_id,
                            'age_group_name' => $age_group_name,
                            'section' => $section,
                            'exams' => $exams,
                            'mgender_id' => $mgender_id,
                            'exam_type_id' => $exam_type_id,
                            'is_result_available' => $is_result_available,
                            'student_detail' => $student_detail,
                            'age_group_id' => $age_group_id,
                            'studentDetails' => $studentDetails,
                            'member_id' => $member_id,
                            'exam_details' => $exam_details,
                            'optional_subject' => $optional_subject,
                            'optional_subject_setup' => $optional_subject_setup,
                            'exam_content' => $exam_content,
                            'failgpaname' => $failgpaname,
                            'total_class_days' => $total_class_days,
                            'student_attendance' => $student_attendance,
                            'mark_sheet' => $mark_sheet,
                        ]
                    );
                } else {
                    return view('backEnd.reports.mark_sheet_report_student_print', [
                            'exam_types' => $exam_types,
                            'classes' => $classes,
                            'subjects' => $subjects,
                            'class' => $age_group_id,
                            'age_group_name' => $age_group_name,
                            'section' => $section,
                            'grades' => $grades,
                            'exams' => $exams,
                            'maxGrade' => $maxGrade,
                            'mgender_id' => $mgender_id,
                            'exam_type_id' => $exam_type_id,
                            'is_result_available' => $is_result_available,
                            'student_detail' => $student_detail,
                            'age_group_id' => $age_group_id,
                            'studentDetails' => $studentDetails,
                            'member_id' => $member_id,
                            'exam_details' => $exam_details,
                            'optional_subject' => $optional_subject,
                            'optional_subject_setup' => $optional_subject_setup,
                            'mark_sheet' => $mark_sheet,
                            'failgpaname' => $failgpaname,
                            'total_class_days' => $total_class_days,
                            'student_attendance' => $student_attendance,
                            'exam_content' => $exam_content,
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examSettings()
    {
        try {
            $content_infos = SmExamSetting::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $exams = SmExamType::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $already_assigned = [];
            foreach ($content_infos as $content_info) {
                $already_assigned[] = $content_info->exam_type;
            }

            return view('backEnd.examination.exam_settings', compact('content_infos', 'exams', 'already_assigned'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function saveExamContent(Request $request)
    {
        $request->validate([
            'exam_type' => "required",
            'title' => "required",
            'publish_date' => "required",
            'start_date' => "required|before:end_date",
            'end_date' => "required|before:publish_date",
            'file' => "sometimes|nullable|mimes:jpg,jpeg,png,svg",
        ]);

        try {
            $fileName = "";
            if ($request->file('file') != "") {
                $maxFileSize = SmGeneralSettings::where('church_id', auth()->user()->church_id)->first()->file_size;
                $file = $request->file('file');
                $fileSize = filesize($file);
                $fileSizeKb = ($fileSize / 1000000);
                if ($fileSizeKb >= $maxFileSize) {
                    Toastr::error('Max upload file size ' . $maxFileSize . ' Mb is set in system', 'Failed');
                    return redirect()->back();
                }
                $file = $request->file('file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/exam/', $fileName);
                $fileName = 'public/uploads/exam/' . $fileName;
            }

            $add_content = new SmExamSetting();
            $add_content->exam_type = $request->exam_type;
            $add_content->title = $request->title;
            $add_content->publish_date = date('Y-m-d', strtotime($request->publish_date));
            $add_content->file = $fileName;
            $add_content->start_date = date('Y-m-d', strtotime($request->start_date));
            $add_content->end_date = date('Y-m-d', strtotime($request->end_date));
            $add_content->church_id = Auth::user()->church_id;
            $add_content->church_year_id = getAcademicId();
            $result = $add_content->save();

            if ($result) {
                Toastr::success('Operation successful', 'Success');
                return redirect('exam-settings');
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function editExamSettings($id)
    {
        try {
            $content_infos = SmExamSetting::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $editData = SmExamSetting::where('id', $id)
                ->where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->first();

            $exams = SmExamType::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $already_assigned = [];
            foreach ($content_infos as $content_info) {
                if ($editData->exam_type != $content_info->exam_type) {
                    $already_assigned[] = $content_info->exam_type;
                }
            }

            return view('backEnd.examination.exam_settings', compact('editData', 'content_infos', 'exams', 'already_assigned'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function updateExamContent(Request $request)
    {

        $request->validate([
            'exam_type' => "required",
            'title' => "required",
            'publish_date' => "required",
            'start_date' => "required|before:end_date",
            'end_date' => "required|before:publish_date",
            'file' => "sometimes|nullable|mimes:jpg,jpeg,png,svg",
        ]);

        try {
            if ($request->file('file') != "") {
                $maxFileSize = SmGeneralSettings::where('church_id', auth()->user()->church_id)->first()->file_size;
                $file = $request->file('file');
                $fileSize = filesize($file);
                $fileSizeKb = ($fileSize / 1000000);
                if ($fileSizeKb >= $maxFileSize) {
                    Toastr::error('Max upload file size ' . $maxFileSize . ' Mb is set in system', 'Failed');
                    return redirect()->back();
                }

                $signature = SmExamSetting::find($request->id);
                if ($signature->file != "") {
                    @unlink($signature->file);
                }

                $file = $request->file('file');
                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/exam/', $fileName);
                $fileName = 'public/uploads/exam/' . $fileName;
            } else {
                $signature = SmExamSetting::find($request->id);
                $fileName = $signature->file;
            }

            if (checkAdmin()) {
                $update_add_content = SmExamSetting::find($request->id);
            } else {
                $update_add_content = SmExamSetting::where('id', $request->id)
                    ->where('church_id', Auth::user()->church_id)
                    ->where('church_year_id', getAcademicId())
                    ->first();
            }
            $update_add_content->exam_type = $request->exam_type;
            $update_add_content->title = $request->title;
            $update_add_content->publish_date = date('Y-m-d', strtotime($request->publish_date));
            $update_add_content->file = $fileName;
            $update_add_content->start_date = date('Y-m-d', strtotime($request->start_date));
            $update_add_content->end_date = date('Y-m-d', strtotime($request->end_date));
            $update_add_content->church_id = Auth::user()->church_id;
            $update_add_content->church_year_id = getAcademicId();
            $update_add_content->file = $fileName;
            $result = $update_add_content->save();

            if ($result) {
                Toastr::success('Operation successful', 'Success');
                return redirect('exam-settings');
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function deleteContent($id)
    {
        try {
            if (checkAdmin()) {
                $content = SmExamSetting::find($id);
            } else {
                $content = SmExamSetting::where('id', $id)->where('church_id', Auth::user()->church_id)->first();
            }
            unlink($content->file);
            $result = $content->delete();

            if ($result) {
                Toastr::success('Operation successful', 'Success');
                return redirect('exam-settings');
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function percentMarkSheetReport(PercentMarkSheetReportRequest $request)
    {
        try {

            $exams = SmExamType::get();
            $classes = SmClass::get();
            $pass_mark = 0;
            $examInfo = SmExamType::find($request->exam_type);

            $classInfo = SmClass::find($request->class);
            $sectionInfo = SmSection::find($request->section);
            $data = [];
            if (moduleStatusCheck('University')) {
                $subjectInfo = UnSubject::find($request->un_subject_id);
                $data['semester'] = UnSemester::find($request->un_semester_id)->name;
                $data['semester_label'] = UnSemesterLabel::find($request->un_semester_label_id)->name;
                $data['session'] = UnSession::find($request->un_session_id)->name;
                $data['requestData'] = $request->all();
                $exam = SmExam::query();
                $exam = universityFilter($exam, $request);
                $exam = $exam->first();
            } else {
                $subjectInfo = SmSubject::find($request->subject);
                $exam = SmExam::where('age_group_id', $request->class)
                    ->where('mgender_id', $request->section)
                    ->where('exam_type_id', $request->exam_type)
                    ->where('subject_id', $request->subject)
                    ->first();
            }

            if ($exam) {
                $pass_mark = $exam->pass_mark;
            } else {
                Toastr::warning('Exam Setup Not Complete', 'Warning');
                return redirect()->back();
            }
            $exam_rule = CustomResultSetting::where('church_id', auth()->user()->church_id)->first();

            if ($exam_rule) {
                $mark_sheet = SmResultStore::query();
                $mark_sheet->where('exam_type_id', $request->exam_type);
                if (moduleStatusCheck('University')) {
                    $mark_sheet = universityFilter($mark_sheet, $request)->where('un_subject_id', $request->un_subject_id);
                } else {
                    $mark_sheet = $mark_sheet->where('age_group_id', $request->class)
                        ->where('mgender_id', $request->section)
                        ->where('subject_id', $request->subject);
                }
                $mark_sheet = $mark_sheet->orderBy('total_marks', 'DESC')->with('studentRecords')->get();

                return view('backEnd.examination.report.marksheetReport', compact('exams', 'classes', 'mark_sheet', 'examInfo', 'subjectInfo', 'classInfo', 'sectionInfo', 'pass_mark', 'exam_rule', 'data'));
            } else {
                $mark_sheet = SmResultStore::query();
                $mark_sheet->where('exam_type_id', $request->exam_type);
                if (moduleStatusCheck('University')) {
                    $mark_sheet = universityFilter($mark_sheet, $request)->where('un_subject_id', $request->un_subject_id);
                } else {
                    $mark_sheet = $mark_sheet->where('age_group_id', $request->class)
                        ->where('mgender_id', $request->section)
                        ->where('subject_id', $request->subject);
                }
                $mark_sheet = $mark_sheet->orderBy('total_marks', 'DESC')->with('studentRecords')->get();

                return view('backEnd.examination.report.marksheetReport', compact('exams', 'classes', 'mark_sheet', 'examInfo', 'subjectInfo', 'classInfo', 'sectionInfo', 'pass_mark', 'exam_rule', 'data'));
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function percentMarksheetPrint(Request $request)
    {
        try {
            $examInfo = SmExamType::find($request->exam);
            $subjectInfo = SmSubject::find($request->subject);
            $classInfo = SmClass::find($request->class);
            $sectionInfo = SmSection::find($request->section);

            $data = [];
            if (moduleStatusCheck('University')) {
                $subjectInfo = UnSubject::find($request->un_subject_id);
                $data['semester'] = UnSemester::find($request->un_semester_id)->name;
                $data['semester_label'] = UnSemesterLabel::find($request->un_semester_label_id)->name;
                $data['session'] = UnSession::find($request->un_session_id)->name;
                $data['requestData'] = $request->all();
                $exam = SmExam::query();
                $exam = universityFilter($exam, $request);
                $exam = $exam->first();
            } else {
                $subjectInfo = SmSubject::find($request->subject);
                $exam = SmExam::where('age_group_id', $request->class)
                    ->where('mgender_id', $request->section)
                    ->where('exam_type_id', $request->exam_type)
                    ->where('subject_id', $request->subject)
                    ->first();
            }
            $pass_mark = $exam->pass_mark;
            $mark_sheet = SmResultStore::query();
            $mark_sheet->where('exam_type_id', $request->exam);
            if (moduleStatusCheck('University')) {
                $mark_sheet = universityFilter($mark_sheet, $request)->where('un_subject_id', $request->un_subject_id);
            } else {
                $mark_sheet = $mark_sheet->where('age_group_id', $request->class)
                    ->where('mgender_id', $request->section)
                    ->where('subject_id', $request->subject);
            }
            $mark_sheet = $mark_sheet->orderBy('total_marks', 'DESC')->with('studentRecords')->get();

            $exam_rule = CustomResultSetting::where('church_id', auth()->user()->church_id)
                ->first();

            return view('backEnd.examination.report.marksheetReportPrint', compact('mark_sheet', 'examInfo', 'subjectInfo', 'classInfo', 'sectionInfo', 'pass_mark', 'exam_rule', 'data'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
