<?php

namespace App\Http\Controllers\Admin\Examination;

use App\User;
use App\SmExam;
use App\SmClass;
use App\SmStaff;
use App\SmParent;
use App\SmHoliday;
use App\SmSection;
use App\SmStudent;
use App\SmSubject;
use App\YearCheck;
use App\SmExamType;
use App\SmClassRoom;
use App\SmClassTime;
use App\SmExamSetup;
use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\Models\StudentRecord;
use App\SmAcademicYear;
use App\SmExamSchedule;
use App\SmNotification;
use App\SmAssignSubject;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Modules\University\Entities\UnFaculty;
use Modules\University\Entities\UnSession;
use Modules\University\Entities\UnSemester;
use Illuminate\Support\Facades\Notification;
use Modules\University\Entities\UnDepartment;
use Modules\University\Entities\UnAcademicYear;
use Modules\University\Entities\UnSemesterLabel;
use App\Notifications\StudentExamCreateNotification;
use App\Http\Requests\Admin\Examination\SmExamScheduleSearchRequest;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;

class SmExamRoutineController extends Controller
{

    public function __construct()
    {
        $this->middleware('PM');
        // User::checkAuth();
    }

    public function examSchedule()
    {
        try {
            $exam_types = SmExamType::where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();
            if (teacherAccess()) {
                $teacher_info=SmStaff::where('user_id',Auth::user()->id)->first();
                $classes= SmAssignSubject::where('teacher_id',$teacher_info->id)->join('sm_classes','sm_classes.id','sm_assign_subjects.age_group_id')
                    ->where('sm_assign_subjects.church_year_id', getAcademicId())
                    ->where('sm_assign_subjects.active_status', 1)
                    ->where('sm_assign_subjects.church_id',Auth::user()->church_id)
                    ->select('sm_classes.id','age_group_name')
                    ->groupBy('sm_classes.id')
                    ->get();
            } else {
                $classes = SmClass::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id',Auth::user()->church_id)
                    ->get();
            }
            return view('backEnd.examination.exam_schedule', compact('classes', 'exam_types'));
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examScheduleCreate()
    {
        try {

            if (teacherAccess()) {
                $teacher_info=SmStaff::where('user_id', Auth::user()->id)->first();
                $classes= $teacher_info->classes;
            } else {
                $classes = SmClass::get();
            }
            $sections = SmSection::get();
            $subjects = SmSubject::get();
            $exams = SmExam::get();
            $exam_types = SmExamType::get();
            return view('backEnd.examination.exam_schedule_create', compact('classes', 'exams', 'exam_types'));
        } catch (\Exception $e) {
            
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function addExamRoutineModal($subject_id, $exam_period_id, $age_group_id, $mgender_id, $exam_term_id,$mgender_id_all)
    {
        try {
            $rooms = SmClassRoom::where('active_status', 1)
                ->where('church_id',Auth::user()->church_id)
                ->get();

            return view('backEnd.examination.add_exam_routine_modal', compact('subject_id', 'exam_period_id', 'age_group_id', 'mgender_id', 'exam_term_id', 'rooms','mgender_id_all'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function checkExamRoutinePeriod(Request $request)
    {

        try {
            $exam_period_check = SmExamSchedule::where('age_group_id', $request->age_group_id)
                ->where('mgender_id', $request->mgender_id)
                ->where('exam_period_id', $request->exam_period_id)
                ->where('exam_term_id', $request->exam_term_id)
                ->where('date', date('Y-m-d', strtotime($request->date)))
                ->where('church_id',Auth::user()->church_id)
                ->first();

            return response()->json(['exam_period_check' => $exam_period_check]);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function updateExamRoutinePeriod(Request $request)
    {

        try {
            $update_exam_period_check = SmExamSchedule::where('age_group_id', $request->age_group_id)
                ->where('mgender_id', $request->mgender_id)
                ->where('exam_period_id', $request->exam_period_id)
                ->where('exam_term_id', $request->exam_term_id)
                ->where('date', date('Y-m-d', strtotime($request->date)))
                ->where('church_id',Auth::user()->church_id)
                ->first();

            return response()->json(['update_exam_period_check' => $update_exam_period_check]);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function EditExamRoutineModal($subject_id, $exam_period_id, $age_group_id, $mgender_id, $exam_term_id, $assigned_id,$mgender_id_all)
    {

        try {
            $rooms = SmClassRoom::where('active_status', 1)
                ->where('church_id',Auth::user()->church_id)
                ->get();

            $assigned_exam = SmExamSchedule::find($assigned_id);

            return view('backEnd.examination.add_exam_routine_modal', compact('subject_id', 'exam_period_id', 'age_group_id', 'mgender_id', 'exam_term_id', 'rooms', 'assigned_exam','mgender_id_all'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function deleteExamRoutineModal($assigned_id,$mgender_id_all)
    {

        try {
            return view('backEnd.examination.delete_exam_routine', compact('assigned_id','mgender_id_all'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }



    public function checkExamRoutineDate(Request $request)
    {

        try {
            if ($request->assigned_id == "") {
                $check_date = SmExamSchedule::where('age_group_id', $request->age_group_id)->where('mgender_id', $request->mgender_id)->where('exam_term_id', $request->exam_term_id)->where('date', date('Y-m-d', strtotime($request->date)))->where('exam_period_id', $request->exam_period_id)->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();
            } else {
                $check_date = SmExamSchedule::where('id', '!=', $request->assigned_id)->where('age_group_id', $request->age_group_id)->where('mgender_id', $request->mgender_id)->where('exam_term_id', $request->exam_term_id)->where('date', date('Y-m-d', strtotime($request->date)))->where('exam_period_id', $request->exam_period_id)->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();
            }

            $holiday_check = SmHoliday::where('from_date', '<=', date('Y-m-d', strtotime($request->date)))->where('to_date', '>=', date('Y-m-d', strtotime($request->date)))->where('church_id',Auth::user()->church_id)->first();

            if ($holiday_check != "") {
                $from_date = date('jS M, Y', strtotime($holiday_check->from_date));
                $to_date = date('jS M, Y', strtotime($holiday_check->to_date));
            } else {
                $from_date = '';
                $to_date = '';
            }

            return response()->json([$check_date, $holiday_check, $from_date, $to_date]);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examScheduleReportSearch(SmExamScheduleSearchRequest $request)
    {
        try {
            if(moduleStatusCheck('University')){

                $un_session = UnSession::find($request->un_session_id);
                $un_faculty = UnFaculty::find($request->un_faculty_id);
                $un_department= UnDepartment::find($request->un_department_id);
                $un_academic = UnAcademicYear::find($request->un_church_year_id);
                $un_semester = UnSemester::find($request->un_semester_id);
                $un_semester_label = UnSemesterLabel::find($request->un_semester_label_id);
                $un_section = SmSection::find($request->un_mgender_id);

                $examName = SmExamType::where('id', $request->exam_type)
                            ->first()
                            ->title;

                $SmExamSchedule = SmExamSchedule::query();
                $exam_schedules = universityFilter($SmExamSchedule, $request)
                                ->where('exam_term_id', $request->exam_type)
                                ->with('subjectDetails')
                                ->get();

                $data['un_semester_label_id'] = $request->un_semester_label_id;
                $interface = App::make(UnCommonRepositoryInterface::class);
                $data = $interface->oldValueSelected($request);

                return view('backEnd.examination.exam_schedule',compact(
                    'exam_schedules',
                    'un_session',
                    'un_faculty',
                    'un_department',
                    'un_academic',
                    'un_semester',
                    'un_semester_label',
                    'un_section',
                    'examName',
                    ))->with($data);
            }else{
                $assign_subjects=SmAssignSubject::query();
                if(!empty($request->section)){
                    $assign_subjects ->where('mgender_id', $request->section);
                }
                $assign_subjects =  $assign_subjects ->where('age_group_id', $request->class)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->groupby(['mgender_id','subject_id'])
                    ->get();

                if ($assign_subjects->count() == 0) {
                    Toastr::error('No Subject Assigned. Please assign subjects in this class.', 'Failed');
                    return redirect()->back();
                    // return redirect('exam-schedule-create')->with('message-danger', 'No Subject Assigned. Please assign subjects in this class.');
                }

                $assign_subjects=SmAssignSubject::query();
                if(!empty($request->section)){
                    $assign_subjects ->where('mgender_id', $request->section);
                }
                $assign_subjects =  $assign_subjects ->where('age_group_id', $request->class)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id',Auth::user()->church_id)
                    ->groupby(['mgender_id','subject_id'])
                    ->get();
                $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();
                $exams = SmExam::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();
                $age_group_id = $request->class;
                if(empty($request->section)){
                    $mgender_id=0;
                }else{
                    $mgender_id = $request->section;
                }

                $exam_id = $request->exam_type;

                $exam_types = SmExamType::where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();

                $exam_schedules = SmExamSchedule::query();
                if(!empty($request->section)){
                    $exam_schedules->where('mgender_id', $request->section);
                }
                $exam_schedules = $exam_schedules->where('exam_term_id', $exam_id)
                    ->where('age_group_id', $request->class)
                    ->where('church_id',Auth::user()->church_id)
                    ->get();

                //  return $exam_schedules;

                $exam_type_id=$request->exam_type;

                $examName     = SmExamType::where('id', $request->exam_type)->where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)->first()->title;
                $search_current_class   = SmClass::find($request->class);
                $search_current_section = SmSection::find($request->section);

                return view('backEnd.examination.exam_schedule_new', compact('classes', 'exams', 'exam_schedules', 'assign_subjects', 'age_group_id', 'mgender_id', 'exam_id', 'exam_types', 'exam_type_id', 'examName', 'search_current_class', 'search_current_section'));
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function compareByTimeStamp($time1, $time2)
    {

        try {
            if (strtotime($time1) < strtotime($time2)) {
                return 1;
            } else if (strtotime($time1) > strtotime($time2)) {
                return -1;
            } else {
                return 0;
            }

        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examScheduleReportSearchOld(Request $request)
    {
        $request->validate([
            'exam' => 'required',
            'class' => 'required',
            'section' => 'required',
        ]);

        try {
            $assign_subjects = SmAssignSubject::where('age_group_id', $request->class)->where('mgender_id', $request->section)->where('church_id',Auth::user()->church_id)->get();

            if ($assign_subjects->count() == 0) {
                Toastr::success('No Subject Assigned. Please assign subjects in this class.', 'Success');
                return redirect('exam-schedule-create');
            }

            $assign_subjects = SmAssignSubject::where('age_group_id', $request->class)->where('mgender_id', $request->section)->where('church_id',Auth::user()->church_id)->get();

            $classes = SmClass::where('active_status', 1)->where('church_id',Auth::user()->church_id)->get();
            $exams = SmExam::where('active_status', 1)->where('church_id',Auth::user()->church_id)->get();

            $age_group_id = $request->class;
            $mgender_id = $request->section;
            $exam_id = $request->exam;

            $exam_types = SmExamType::all();
            $exam_periods = SmClassTime::where('type', 'exam')->where('church_id',Auth::user()->church_id)->get();

            return view('backEnd.examination.exam_schedule', compact('classes', 'exams', 'assign_subjects', 'age_group_id', 'mgender_id', 'exam_id', 'exam_types', 'exam_periods'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function examSchedulePrint(Request $request)
    {

        try {
            $assign_subjects = SmAssignSubject::query();

            if($request->mgender_id !=0){
                $assign_subjects->where('mgender_id', $request->mgender_id);
            }
            $assign_subjects =$assign_subjects->where('age_group_id', $request->age_group_id)
                ->where('church_year_id', getAcademicId())
                ->where('church_id',Auth::user()->church_id)
                ->groupby(['mgender_id','subject_id'])
                ->get();

            $exam_periods = SmClassTime::where('type', 'exam')
                ->where('church_year_id', getAcademicId())
                ->where('church_id',Auth::user()->church_id)
                ->get();

            $church_year=SmAcademicYear::find(getAcademicId());

            $age_group_id = $request->age_group_id;

            // if($request->mgender_id==0){
            //     $mgender_id='All Sections';
            // }else{
            //     $mgender_id = $request->mgender_id;
            // }
            $exam_id = $request->exam_id;
            $print = request()->print;
            return view(
                'backEnd.examination.exam_schedult_print',
                [
                    'assign_subjects' => $assign_subjects,
                    'exam_periods' => $exam_periods,
                    'age_group_id' => $request->age_group_id,
                    'church_year' => $church_year,

                    'mgender_id' => $request->mgender_id,
                    'exam_id' => $request->exam_id,
                    'print' => $print,
                ]
            );

//            $pdf = PDF::loadView(
//                'backEnd.examination.exam_schedult_print',
//                [
//                    'assign_subjects' => $assign_subjects,
//                    'exam_periods' => $exam_periods,
//                    'age_group_id' => $request->age_group_id,
//                    'church_year' => $church_year,
//
//                    'mgender_id' => $request->mgender_id,
//                    'exam_id' => $request->exam_id,
//                ]
//            )->setPaper('A4', 'landscape');
//            return $pdf->stream('EXAM_SCHEDULE.pdf');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examRoutineReport(Request $request)
    {

        try {
            $exam_types = SmExamType::where('church_id', Auth::user()->church_id)->where('church_year_id', getAcademicId())->get();
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();

            return view('backEnd.reports.exam_routine_report', compact('classes', 'exam_types'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examRoutineReportSearch(Request $request)
    {
        $request->validate([
            'exam' => 'required',
            'class' => 'required',
            'section' => 'sometimes|nullable',
        ]);

        try {


            $classes = SmClass::get();
            $exams = SmExam::get();
            $age_group_id = $request->class ? $request->class : 0;
            $mgender_id = $request->section ? $request->section : 0;
            $exam_id = $request->exam;

            $exam_types = SmExamType::get();

            $exam_schedules = SmExamSchedule::where('church_id', Auth::user()->church_id)

                ->when($request->exam, function ($query) use ($request) {
                    $query->where('exam_term_id', $request->exam);
                })
                ->when($request->class, function ($query) use ($request) {
                    $query->where('age_group_id', $request->class);
                })
                ->when($request->section, function ($query) use ($request) {
                    $query->where('mgender_id', $request->section);
                })
                ->get();

            $exam_type_id=$request->exam;

            $examName     = SmExamType::where('id', $request->exam)->first()->title;
            $search_current_class   = SmClass::find($request->class);
            $search_current_section = SmSection::find($request->section);

            return view('backEnd.reports.exam_routine_report', compact('classes', 'exams', 'exam_schedules','age_group_id', 'mgender_id', 'exam_id', 'exam_types', 'exam_type_id', 'examName', 'search_current_class', 'search_current_section'));
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function examRoutineReportSearchPrint($exam_id)
    {

        try {
            $exam_types = SmExamType::where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();
            $exam_routines = SmExamSchedule::where('exam_term_id', $exam_id)->orderBy('date', 'ASC')->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();
            $exam_routines = $exam_routines->groupBy('date');
            $church_year = SmAcademicYear::find(getAcademicId());


        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examScheduleSearch(SmExamScheduleSearchRequest $request)
    {
        try {
            if (moduleStatusCheck('University')) {
                $data= [];
                $exam_type_id = $request->exam_type;

                $un_session = UnSession::find($request->un_session_id);
                $un_faculty = UnFaculty::find($request->un_faculty_id);
                $un_department= UnDepartment::find($request->un_department_id);
                $un_academic = UnAcademicYear::find($request->un_church_year_id);
                $un_semester = UnSemester::find($request->un_semester_id);
                $un_semester_label = UnSemesterLabel::find($request->un_semester_label_id);
                $un_section = SmSection::find($request->un_mgender_id);


                $SmExam = SmExam::query();
                $subjects = universityFilter($SmExam, $request)
                            ->where('exam_type_id', $request->exam_type)
                            ->with('subjectDetails')
                            ->orWhereNull('un_mgender_id')
                            ->get();

                $teachers = SmStaff::where('role_id', 4)->where('active_status', 1)
                            ->where('church_id', auth()->user()->church_id)
                            ->get(['id','user_id','full_name']);

                $rooms = SmClassRoom::where('active_status', 1)->where('church_id', auth()->user()->church_id)
                        ->get(['id','room_no']);

                $examName = SmExamType::where('id', $request->exam_type)
                        ->where('active_status', 1)
                        ->first()
                        ->title;

                $SmExamSchedule = SmExamSchedule::query();
                $exam_schedule = universityFilter($SmExamSchedule, $request)
                                ->where('exam_term_id', $request->exam_type)
                                ->get();

                $rooms = SmClassRoom::where('active_status', 1)
                        ->where('church_id', auth()->user()->church_id)
                        ->get(['id','room_no']);

                $data['un_semester_label_id'] = $request->un_semester_label_id;
                $interface = App::make(UnCommonRepositoryInterface::class);
                $data = $interface->oldValueSelected($request);

                return view('backEnd.examination.exam_schedule_create',
                compact('examName',
                        'un_session',
                        'un_faculty',
                        'un_department',
                        'un_academic',
                        'un_semester',
                        'un_semester_label',
                        'un_section',
                        'exam_type_id',
                        'exam_schedule',
                        'teachers',
                        'rooms',
                        'subjects',
                    ))->with($data);
            }else{
                $subject_ids     = SmExamSetup::query();
                $assign_subjects = SmAssignSubject::query();

                if ($request->class !=null) {
                    $assign_subjects->where('age_group_id', $request->class);
                    $subject_ids->where('age_group_id', $request->class);
                }

                if ($request->section !=null) {
                    $assign_subjects->where('mgender_id', $request->section);
                    $subject_ids->where('mgender_id', $request->section);
                }


                $assign_subjects = $assign_subjects->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->get();
                $subject_ids    = $subject_ids->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->where('exam_term_id', $request->exam_type)
                    ->pluck('subject_id')->toArray();


                if ($assign_subjects->count() == 0) {
                    Toastr::success('No Subject Assigned. Please assign subjects in this class.', 'Success');
                    return redirect('exam-schedule-create');
                }



                if (teacherAccess()) {
                    $teacher_info = SmStaff::where('user_id', Auth::user()->id)->first();
                    $classes      = $teacher_info->classes;
                } else {
                    $classes      = SmClass::get();
                }


                $age_group_id     = $request->class;
                $mgender_id   = $request->section !=null ? $request->section : 0;
                $exam_type_id = $request->exam_type;
                $exam_types   = SmExamType::where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->get();

                $exam_schedule = SmExamSchedule::query();
                if ($request->class) {
                    $exam_schedule->where('age_group_id', $request->class);
                }
                if ($request->section) {
                    $exam_schedule->where('mgender_id', $request->section);
                }
                $exam_schedule = $exam_schedule->where('exam_term_id', $request->exam_type)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->get();

                $subjects     = SmSubject::whereIn('id', $subject_ids)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->get(['id','subject_name']);

                $teachers     = SmStaff::where('role_id', 4)->where('active_status', 1)
                    ->where('church_id', Auth::user()->church_id)
                    ->get(['id','user_id','full_name']);

                $rooms        = SmClassRoom::where('active_status', 1)->where('church_id', Auth::user()->church_id)
                    ->get(['id','room_no']);

                $examName     = SmExamType::where('id', $request->exam_type)->where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)->first()->title;

                $search_current_class   = SmClass::find($request->class);
                $search_current_section = SmSection::find($request->section);

                return view('backEnd.examination.exam_schedule_new_update', compact('classes', 'subjects', 'exam_schedule', 'age_group_id', 'mgender_id', 'exam_type_id', 'exam_types', 'teachers', 'rooms', 'examName', 'search_current_class', 'search_current_section'));
            }

        } catch (\Exception $e) {
           
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    //end




    public function addExamRoutineStore(Request $request)
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            if(moduleStatusCheck('University')){
                $exam_type_id = $request->exam_type_id;

                $SmExamSchedule = SmExamSchedule::query();
                $exam_schedule = universityFilter($SmExamSchedule, $request)
                                ->where('exam_term_id', $exam_type_id)
                                ->delete();

                foreach ($request->routine as $routine_data) {

                    $SmExam = SmExam::query();
                    $sm_exam = universityFilter($SmExam, $request)
                                //->where('exam_type_id', $exam_type_id)
                                //->where('un_subject_id', gv($routine_data, 'subject'))
                                ->first();

                    if (gv($routine_data, 'subject')=="Select Subject *") {
                        Toastr::error('Subject Can not Be Empty', 'Failed');
                    }
                    if (!gv($routine_data, 'subject') || gv($routine_data, 'subject')=="Select Subject *" || !gv($routine_data, 'start_time') || !gv($routine_data, 'end_time')) {
                        continue;
                    }
                    $is_exist= SmExamSchedule::where(
                        [
                            'exam_term_id'=>$exam_type_id,
                            'exam_id'=> $sm_exam->id,
                            'subject_id'=>gv($routine_data, 'subject'),
                            'date'=> date('Y-m-d', strtotime(gv($routine_data, 'date'))),
                            'start_time' =>  date('H:i:s', strtotime(gv($routine_data, 'start_time'))),
                            'end_time' => date('H:i:s', strtotime(gv($routine_data, 'end_time'))),
                            'room_id' => gv($routine_data, 'room'),
                            'un_session_id' => $request->un_session_id,
                            'un_faculty_id' => $request->un_faculty_id,
                            'un_department_id' => $request->un_department_id,
                            'un_church_year_id' => $request->un_church_year_id,
                            'un_semester_id' => $request->un_semester_id,
                            'un_semester_label_id' => $request->un_semester_label_id,
                            'un_mgender_id' => $request->un_mgender_id,
                        ]
                    )->where('church_id', Auth::user()->church_id)->first();

                    if ($is_exist) {
                        continue;
                    }

                    $exam_routine = new SmExamSchedule();
                    $exam_routine->exam_term_id = $exam_type_id;
                    $exam_routine->exam_id = $sm_exam->id;

                    $common = App::make(UnCommonRepositoryInterface::class);
                    $common->storeUniversityData($exam_routine, $request);

                    $exam_routine->un_subject_id = gv($routine_data, 'subject');
                    $exam_routine->teacher_id = gv($routine_data, 'teacher_id');
                    $exam_routine->date = date('Y-m-d', strtotime(gv($routine_data, 'date')));
                    $exam_routine->start_time = date('H:i:s', strtotime(gv($routine_data, 'start_time')));
                    $exam_routine->end_time = date('H:i:s', strtotime(gv($routine_data, 'end_time')));
                    $exam_routine->room_id = gv($routine_data, 'room');
                    $exam_routine->church_id = Auth::user()->church_id;
                    $exam_routine->church_year_id = getAcademicId();
                    $exam_routine->save();
                }

                Toastr::success('Exam routine has been assigned successfully', 'Success');
                return redirect('exam-schedule');
            }else{
                $age_group_id   = $request->age_group_id;
                $mgender_id = $request->mgender_id == 0 ? 0 : $request->mgender_id;
                $exam_term_id = $request->exam_type_id;

                $exam_schedule  = SmExamSchedule::query();
                if ($request->age_group_id) {
                    $exam_schedule->where('age_group_id', $request->age_group_id);
                }
                if ($request->mgender_id !=0) {
                    $exam_schedule->where('mgender_id', $request->section);
                }
                $exam_schedule = $exam_schedule->where('exam_term_id', $request->exam_type_id)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->delete();

                    foreach ($request->routine as $routine_data) {
                        if (gv($routine_data, 'subject')=="Select Subject *") {
                            Toastr::error('Subject Can not Be Empty', 'Failed');
                            return redirect('exam-routine-view/' . $age_group_id . '/' . $mgender_id . '/' . $exam_term_id);
                        }
                        if (!gv($routine_data, 'subject') || gv($routine_data, 'subject')=="Select Subject *" || !gv($routine_data, 'start_time') || !gv($routine_data, 'end_time')) {
                            continue;
                        }

                        $is_exist= SmExamSchedule::where(
                            [
                                'exam_term_id'=>$request->exam_type_id,
                                'subject_id'=>gv($routine_data, 'subject'),
                                'date'=> date('Y-m-d', strtotime(gv($routine_data, 'date'))),
                                'start_time' =>  date('H:i:s', strtotime(gv($routine_data, 'start_time'))),
                                'end_time' => date('H:i:s', strtotime(gv($routine_data, 'end_time'))),
                                'room_id' => gv($routine_data, 'room'),
                                'age_group_id' => $request->age_group_id,
                                'mgender_id' => gv($routine_data, 'section')
                            ]
                        )->where('church_id', Auth::user()->church_id)->first();


                        if ($is_exist) {
                            continue;
                        }

                        $exam_routine = new SmExamSchedule();
                        $exam_routine->exam_term_id = $request->exam_type_id;
                        $exam_routine->age_group_id = $request->age_group_id;
                        $exam_routine->mgender_id = gv($routine_data, 'section');
                        $exam_routine->subject_id = gv($routine_data, 'subject');
                        $exam_routine->teacher_id = gv($routine_data, 'teacher_id');
                        $exam_routine->date = date('Y-m-d', strtotime(gv($routine_data, 'date')));
                        $exam_routine->start_time = date('H:i:s', strtotime(gv($routine_data, 'start_time')));
                        $exam_routine->end_time = date('H:i:s', strtotime(gv($routine_data, 'end_time')));
                        $exam_routine->room_id = gv($routine_data, 'room');
                        $exam_routine->church_id = Auth::user()->church_id;
                        $exam_routine->church_year_id = getAcademicId();
                        $exam_routine->save();
                    }

                Toastr::success('Exam routine has been assigned successfully', 'Success');
                return redirect('exam-routine-view/' . $age_group_id . '/' . $mgender_id . '/' . $exam_term_id);
            }
        } catch (\Exception $e) {
       
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function examRoutineView($age_group_id, $mgender_id, $exam_term_id)
    {

        try {

            $subject_ids = SmExamSetup::query();

            if($age_group_id !=null){
                $subject_ids->where('age_group_id', $age_group_id);
            }

            if($mgender_id !=0){
                $subject_ids->where('mgender_id', $mgender_id);
            }

            $subject_ids  = $subject_ids->where('church_year_id', getAcademicId())
                ->where('church_id',Auth::user()->church_id)
                ->where('exam_term_id', $exam_term_id)
                ->pluck('subject_id')->toArray();

            $classes = SmClass::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id',Auth::user()->church_id)
                ->get();
            $exams        = SmExam::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id',Auth::user()->church_id)
                ->get();

            $exam_type_id = $exam_term_id;

            $exam_types   = SmExamType::where('church_year_id', getAcademicId())
                ->where('church_id',Auth::user()->church_id)
                ->get();
            $exam_periods = SmClassTime::where('type', 'exam')
                ->where('church_year_id', getAcademicId())
                ->where('church_id',Auth::user()->church_id)
                ->get();
            $rooms        = SmClassRoom::where('active_status', 1)
                ->where('church_id',Auth::user()->church_id)
                ->get();

            $subjects     = SmSubject::whereIn('id',$subject_ids)
                ->where('church_year_id', getAcademicId())
                ->where('church_id',Auth::user()->church_id)
                ->get(['id','subject_name']);

            $teachers     = SmStaff::where('role_id',4)->where('active_status',1)
                ->where('church_id',Auth::user()->church_id)
                ->get(['id','user_id','full_name']);


            $search_current_class   = SmClass::find($age_group_id);
            $search_current_section = SmSection::find($mgender_id);

            if($mgender_id ==0){
                $exam_schedule          = SmExamSchedule::where('age_group_id',$age_group_id)->where('exam_term_id',$exam_type_id)->get();
            }else{
                $exam_schedule          = SmExamSchedule::where('age_group_id',$age_group_id)->where('mgender_id',$mgender_id)
                    ->where('exam_term_id',$exam_type_id)->get();
            }

            $examName               = SmExamType::where('id',$exam_type_id)->where('active_status',1)
                ->where('church_id',Auth::user()->church_id)
                ->first()->title;

            return view('backEnd.examination.exam_schedule_new_update', compact('classes','subjects','exam_schedule','exams', 'age_group_id', 'mgender_id', 'exam_type_id', 'exam_types','teachers','rooms','examName','search_current_class','search_current_section'));
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function examRoutinePrint($age_group_id, $mgender_id, $exam_term_id)
    {
        try {
            $exam_type_id   = $exam_term_id;
            $exam_type      = SmExamType::find($exam_type_id)->title;
            $church_year_id    = SmExamType::find($exam_type_id)->church_year_id;
            $church_year  = SmAcademicYear::find($church_year_id);
            $age_group_name     = $age_group_id !=0 ? SmClass::find($age_group_id)->age_group_name : 'All Classes';
            $mgender_name   = $mgender_id !=0 ? SmSection::find($mgender_id)->mgender_name :'All Sections';
            $exam_schedules = SmExamSchedule::where('church_id', Auth::user()->church_id)
                ->when($exam_term_id, function ($query) use ($exam_term_id) {
                    $query->where('exam_term_id', $exam_term_id);
                })
                ->when($age_group_id !=0, function ($query) use ($age_group_id) {
                    $query->where('age_group_id', $age_group_id);
                })
                ->when($mgender_id !=0, function ($query) use ($mgender_id) {
                    $query->where('mgender_id', $mgender_id);
                })
                ->get();

            // return view('backEnd.examination.exam_schedule_print', [
            //     'exam_schedules' => $exam_schedules,
            //     'exam_type' => $exam_type,
            //     'age_group_name' => $age_group_name,
            //     'church_year' => $church_year,
            //     'mgender_name' => $mgender_name,
            // ]);
            $print = request()->print;
            return view('backEnd.examination.exam_schedule_print',
                [
                    'exam_schedules' => $exam_schedules,
                    'exam_type' => $exam_type,
                    'age_group_name' => $age_group_name,
                    'church_year' => $church_year,
                    'mgender_name' => $mgender_name,
                    'print' => $print,
                ]);

            $pdf = PDF::loadView(
                'backEnd.examination.exam_schedule_print',
                [
                    'exam_schedules' => $exam_schedules,
                    'exam_type' => $exam_type,
                    'age_group_name' => $age_group_name,
                    'church_year' => $church_year,
                    'mgender_name' => $mgender_name,


                ]
            )->setPaper('A4', 'landscape');
            return $pdf->stream('EXAM_SCHEDULE.pdf');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function deleteExamRoutine(Request $request)
    {

        try {

            $exam_routine = SmExamSchedule::find($request->id);
            $result = $exam_routine->delete();

            return response(["done"]);

        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}