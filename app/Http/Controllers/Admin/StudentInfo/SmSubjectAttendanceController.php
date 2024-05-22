<?php

namespace App\Http\Controllers\Admin\StudentInfo;

use App\SmClass;
use App\SmSection;
use App\SmParent;
use App\SmStudent;
use App\SmSubject;
use App\SmBaseSetup;
use App\ApiBaseMethod;
use App\SmClassSection;
use App\SmAssignSubject;
use App\SmStudentCategory;
use App\SmStudentAttendance;
use App\SmSubjectAttendance;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Routing\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Notifications\FlutterAppNotification;
use App\Http\Requests\Admin\StudentInfo\StudentSubjectWiseAttendanceStoreRequest;
use App\Http\Requests\Admin\StudentInfo\StudentSubjectWiseAttendancSearchRequest;
use App\Http\Requests\Admin\StudentInfo\StudentSubjectWiseAttendanceSearchRequest;
use App\Http\Requests\Admin\StudentInfo\subjectAttendanceAverageReportSearchRequest;
use App\Models\StudentRecord;
use App\Notifications\StudentAttendanceSetNotification;
use App\SmNotification;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SmSubjectAttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
        // User::checkAuth();
    }

    public function index(Request $request)
    {
        try{

            $classes = SmClass::get();
            return view('backEnd.studentInformation.subject_attendance', compact('classes'));

        }catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function search(StudentSubjectWiseAttendancSearchRequest $request)
    {

        try{

            $input['attendance_date']= $request->attendance_date;
            $input['class']= $request->class;
            $input['subject']= $request->subject;
            $input['section']= $request->section;

            $classes = SmClass::get();
            $sections = SmClassSection::with('sectionName')->where('age_group_id', $input['class'])->get();
            $subjects = SmAssignSubject::with('subject')->where('age_group_id', $input['class'])->where('mgender_id', $input['section'])
                ->groupBy('subject_id')->get();

            $students = StudentRecord::with(['studentDetail' => function($q){
                return $q->where('active_status', 1);
            } , 'studentDetail.DateSubjectWiseAttendances'])
                ->whereHas('studentDetail', function($q){
                    return $q->where('active_status', 1);
                })
                ->where('age_group_id', $input['class'])
                ->where('mgender_id', $input['section'])
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->where('is_promote', 0)
                ->get()->sortBy('roll_no');

            if ($students->isEmpty()) {
                Toastr::error('No Result Found', 'Failed');
                return redirect('subject-wise-attendance');
            }

            $attendance_type= $students[0]['studentDetail']['DateSubjectWiseAttendances'] != null  ? $students[0]['studentDetail']['DateSubjectWiseAttendances']['attendance_type']:'';




            $search_info['age_group_name'] = SmClass::find($request->class)->age_group_name;
            $search_info['mgender_name'] = SmSection::find($request->section)->mgender_name;
            $search_info['subject_name'] = SmSubject::find($request->subject)->subject_name;
            $search_info['date'] = $input['attendance_date'];



            if (generalSetting()->attendance_layout==1) {
                return view('backEnd.studentInformation.subject_attendance_list', compact('classes','subjects','sections','students', 'attendance_type', 'search_info', 'input'));
            } else {
                return view('backEnd.studentInformation.subject_attendance_list2', compact('classes','subjects','sections','students', 'attendance_type', 'search_info', 'input'));
            }


        }catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function storeAttendance(StudentSubjectWiseAttendanceStoreRequest $request)
    {
        try {
            foreach ($request->attendance as $record_id => $student) {
                $attendance = SmSubjectAttendance::where('member_id', gv($student, 'student'))
                    ->where('subject_id', $request->subject)
                    ->where('attendance_date', date('Y-m-d', strtotime($request->date)))
                    ->where('age_group_id', gv($student, 'class'))
                    ->where('mgender_id', gv($student, 'section'))
                    ->where('student_record_id', $record_id)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->first();

                if ($attendance != "")
                {
                    $attendance->delete();
                }

                $attendance = new SmSubjectAttendance();
                $attendance->student_record_id = $record_id;
                $attendance->subject_id = $request->subject;
                $attendance->member_id = gv($student, 'student');
                $attendance->age_group_id = gv($student, 'class');
                $attendance->mgender_id = gv($student, 'section');
                $attendance->attendance_type = gv($student, 'attendance_type');
                $attendance->notes = gv($student, 'note');
                $attendance->church_id = Auth::user()->church_id;
                $attendance->church_year_id = getAcademicId();
                $attendance->attendance_date = date('Y-m-d', strtotime($request->date));
                $r= $attendance->save();

                $messege = "";
                $date = dateConvert($attendance->attendance_date);

                if(gv($student, 'student')){

                    $student = SmStudent::find(gv($student, 'student'));
                    $subject = SmSubject::find($request->subject);
                    $subject_name = $subject->subject_name;
                    if($student){
                        if($attendance->attendance_type == "P"){
                            $messege = app('translator')->get('student.Your_teacher_has_marked_you_present_in_the_attendance_on_subject', ['date' => $date,'subject_name' => $subject_name]);

                        }
                        elseif($attendance->attendance_type == "L"){
                            $messege = app('translator')->get('student.Your_teacher_has_marked_you_late_in_the_attendance_on_subject', ['date' => $date,'subject_name' => $subject_name]);
                        }
                        elseif($attendance->attendance_type == "A"){
                            $messege = app('translator')->get('student.Your_teacher_has_marked_you_absent_in_the_attendance_on_subject', ['date' => $date,'subject_name' => $subject_name]);
                        }
                        elseif($attendance->attendance_type == "F"){
                            $messege = app('translator')->get('student.Your_teacher_has_marked_you_halfday_in_the_attendance_on_subject', ['date' => $date,'subject_name' => $subject_name]);
                        }

                        $notification = new SmNotification();
                        $notification->user_id = $student->user_id;
                        $notification->role_id = 2;
                        $notification->date = date('Y-m-d');
                        $notification->message = $messege ;
                        $notification->church_id = Auth::user()->church_id;
                        $notification->church_year_id = getAcademicId();
                        $notification->save();
                        try{
                            if($student->user){
                                $title = app('translator')->get('student.attendance_notication');
                                Notification::send($student->user, new FlutterAppNotification($notification,$title));
                            }

                        }
                        catch (\Exception $e) {

                            Log::info($e->getMessage());
                        }



                        // for parent user
                        $parent = SmParent::find($student->parent_id);
                        if($parent){
                            if($attendance->attendance_type == "P"){
                                $messege = app('translator')->get('student.Your_child_is_marked_present_in_the_attendance_on_subject', ['date' => $date , 'member_name'=> $student->full_name."'s" ,'subject_name' => $subject_name ]);

                            }
                            elseif($attendance->attendance_type == "L"){
                                $messege = app('translator')->get('student.Your_child_is_marked_late_in_the_attendance_on_subject', ['date' => $date ,'member_name'=> $student->full_name."'s" , 'subject_name' => $subject_name]);
                            }
                            elseif($attendance->attendance_type == "A"){
                                $messege = app('translator')->get('student.Your_child_is_marked_absent_in_the_attendance_on_subject', ['date' => $date, 'member_name'=> $student->full_name."'s" , 'subject_name' => $subject_name]);
                            }
                            elseif($attendance->attendance_type == "F"){
                                $messege = app('translator')->get('student.Your_child_is_marked_halfday_in_the_attendance_on_subject', ['date' => $date, 'member_name'=> $student->full_name."'s" , 'subject_name' => $subject_name]);
                            }

                            $notification = new SmNotification();
                            $notification->user_id = $parent->user_id;
                            $notification->role_id = 3;
                            $notification->date = date('Y-m-d');
                            $notification->message = $messege;
                            $notification->church_id = Auth::user()->church_id;
                            $notification->church_year_id = getAcademicId();
                            $notification->save();

                            try{
                                $user=User::find($notification->user_id);
                                if($user){
                                    $title = app('translator')->get('student.attendance_notication');
                                    Notification::send($user, new FlutterAppNotification($notification,$title));
                                }

                            }
                            catch (\Exception $e) {

                                Log::info($e->getMessage());
                            }
                        }
                    }


                }


            }
            Toastr::success('Operation successful', 'Success');
            return redirect('subject-wise-attendance');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function storeAttendanceSecond(Request $request)
    {

        try {
            foreach ($request->attendance as $record_id => $student) {

                $attendance_type = gv($student, 'attendance_type') ? gv($student, 'attendance_type') : 'A' ;
                $attendance = SmSubjectAttendance::where('member_id', gv($student, 'student'))
                    ->where('subject_id', $request->subject)
                    ->where('attendance_date', date('Y-m-d', strtotime($request->attendance_date)))
                    ->where('age_group_id', gv($student, 'class'))
                    ->where('mgender_id', gv($student, 'section'))
                    ->where('student_record_id', $record_id)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->first();
                if ($attendance !="") {
                    $attendance->delete();
                }

                $attendance = new SmSubjectAttendance();
                $attendance->student_record_id = $record_id;
                $attendance->subject_id = $request->subject;
                $attendance->member_id = gv($student, 'student');
                $attendance->age_group_id = gv($student, 'class');
                $attendance->mgender_id = gv($student, 'section');
                $attendance->attendance_type = $attendance_type;
                $attendance->notes = gv($student, 'note');
                $attendance->church_id = Auth::user()->church_id;
                $attendance->church_year_id = getAcademicId();
                $attendance->attendance_date = date('Y-m-d', strtotime($request->attendance_date));
                $r= $attendance->save();
            }
            return response()->json('success');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function subjectHolidayStore(Request $request)
    {
        $active_students = SmStudent::where('active_status', 1)
            ->where('church_year_id', getAcademicId())
            ->where('church_id', Auth::user()->church_id)
            ->get()->pluck('id')->toArray();
        $students = StudentRecord::where('age_group_id', $request->age_group_id)
            ->where('mgender_id', $request->mgender_id)
            ->whereIn('member_id', $active_students)
            ->where('church_year_id', getAcademicId())
            ->where('church_id', Auth::user()->church_id)
            ->get();

        if ($students->isEmpty()) {
            Toastr::error('No Result Found', 'Failed');
            return redirect('subject-wise-attendance');
        }
        if ($request->purpose == "mark") {
            foreach ($students as $record) {
                $attendance = SmSubjectAttendance::where('member_id', $record->member_id)
                    ->where('subject_id', $request->subject_id)
                    ->where('attendance_date', date('Y-m-d', strtotime($request->attendance_date)))
                    ->where('age_group_id', $request->age_group_id)->where('mgender_id', $request->mgender_id)
                    ->where('student_record_id', $record->id)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->first();
                if (!empty($attendance)) {
                    $attendance->delete();
                    $attendance = new SmSubjectAttendance();
                    $attendance->attendance_type= "H";
                    $attendance->notes= "Holiday";
                    $attendance->attendance_date = date('Y-m-d', strtotime($request->attendance_date));
                    $attendance->member_id = $record->member_id;
                    $attendance->subject_id = $request->subject_id;
                    $attendance->student_record_id = $record->id;
                    $attendance->age_group_id = $record->age_group_id;
                    $attendance->mgender_id = $record->mgender_id;
                    $attendance->church_year_id = getAcademicId();
                    $attendance->church_id = Auth::user()->church_id;
                    $attendance->save();
                } else {
                    $attendance = new SmSubjectAttendance();
                    $attendance->attendance_type= "H";
                    $attendance->notes= "Holiday";
                    $attendance->attendance_date = date('Y-m-d', strtotime($request->attendance_date));
                    $attendance->member_id = $record->member_id;
                    $attendance->subject_id = $request->subject_id;

                    $attendance->student_record_id = $record->id;
                    $attendance->age_group_id = $record->age_group_id;
                    $attendance->mgender_id = $record->mgender_id;

                    $attendance->church_year_id = getAcademicId();
                    $attendance->church_id = Auth::user()->church_id;
                    $attendance->save();
                }


                //notification

                $messege = "";
                $date = dateConvert($attendance->attendance_date);

                $student = SmStudent::find($record->member_id);
                $subject = SmSubject::find($request->subject_id);
                $subject_name = $subject->subject_name;

                if($student){
                    $messege = app('translator')->get('student.Your_teacher_has_marked_holiday_in_the_attendance_on_subject', ['date' => $date,'subject_name' => $subject_name]);

                    $notification = new SmNotification();
                    $notification->user_id = $student->user_id;
                    $notification->role_id = 2;
                    $notification->date = date('Y-m-d');
                    $notification->message = $messege ;
                    $notification->church_id = Auth::user()->church_id;
                    $notification->church_year_id = getAcademicId();
                    $notification->save();
                    try{
                        if($student->user){
                            $title = app('translator')->get('student.attendance_notication');
                            Notification::send($student->user, new FlutterAppNotification($notification,$title));
                        }

                    }
                    catch (\Exception $e) {
                        Log::info($e->getMessage());
                    }



                    // for parent user
                    $parent = SmParent::find($student->parent_id);
                    if($parent){
                        $messege = app('translator')->get('student.Your_child_is_marked_holiday_in_the_attendance_on_subject', ['date' => $date , 'member_name'=> $student->full_name."'s" ,'subject_name' => $subject_name ]);

                        $notification = new SmNotification();
                        $notification->user_id = $parent->user_id;
                        $notification->role_id = 3;
                        $notification->date = date('Y-m-d');
                        $notification->message = $messege;
                        $notification->church_id = Auth::user()->church_id;
                        $notification->church_year_id = getAcademicId();
                        $notification->save();

                        try{
                            $user=User::find($notification->user_id);
                            if($user){
                                $title = app('translator')->get('student.attendance_notication');
                                Notification::send($user, new FlutterAppNotification($notification,$title));
                            }

                        }
                        catch (\Exception $e) {
                            Log::info($e->getMessage());
                        }
                    }
                }

            }
        } elseif ($request->purpose == "unmark") {
            foreach ($students as $record) {
                $attendance = SmSubjectAttendance::where('member_id', $record->member_id)
                    ->where('subject_id', $request->subject_id)
                    ->where('attendance_date', date('Y-m-d', strtotime($request->attendance_date)))
                    ->where('age_group_id', $request->age_group_id)->where('mgender_id', $request->mgender_id)
                    ->where('student_record_id', $record->id)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id', Auth::user()->church_id)
                    ->first();
                if (!empty($attendance)) {
                    $attendance->delete();
                }
            }
        }
        Toastr::success('Operation successful', 'Success');
        return redirect('subject-wise-attendance');
    }

    public function subjectAttendanceReport(Request $request)
    {
        try{

            $classes = SmClass::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id',Auth::user()->church_id)
                ->get();

            $types = SmStudentCategory::where('church_id',Auth::user()->church_id)->get();

            $genders = SmBaseSetup::where('active_status', '=', '1')
                ->where('base_group_id', '=', '1')
                ->where('church_id',Auth::user()->church_id)
                ->get();

            return view('backEnd.studentInformation.subject_attendance_report_view', compact('classes', 'types', 'genders'));
        }catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function subjectAttendanceReportSearch(StudentSubjectWiseAttendanceSearchRequest $request)
    {

        try{
            $year = $request->year;
            $month = $request->month;
            $age_group_id = $request->class;
            $mgender_id = $request->section;
            $assign_subjects = SmAssignSubject::where('age_group_id',$age_group_id)
                ->where('mgender_id',$mgender_id)
                ->first();

            $subject_id = $assign_subjects->subject_id;
            $current_day = date('d');

            $days = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);
            $classes = SmClass::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id',Auth::user()->church_id)
                ->get();

            $students = SmStudent::where('age_group_id', $request->class)
                ->where('mgender_id', $request->section)
                ->where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id',Auth::user()->church_id)
                ->get();

            $attendances = [];

            foreach ($students as $student) {
                $attendance = SmSubjectAttendance::where('sm_subject_attendances.member_id', $student->id)
                    ->join('sm_students','sm_students.id','=','sm_subject_attendances.member_id')
                    ->where('attendance_date', 'like', $year . '-' . $month . '%')
                    ->where('sm_subject_attendances.church_year_id', getAcademicId())
                    ->where('sm_subject_attendances.church_id',Auth::user()->church_id)
                    ->get();

                if ($attendance) {
                    $attendances[] = $attendance;
                }
            }

            return view('backEnd.studentInformation.subject_attendance_report_view', compact('classes', 'attendances', 'days', 'year', 'month', 'current_day', 'age_group_id', 'mgender_id','subject_id'));
        }catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function subjectAttendanceAverageReport(Request $request)

    {

        try{

            $classes = SmClass::get();

            $types = SmStudentCategory::withoutGlobalScope(AcademicSchoolScope::class)->where('church_id',Auth::user()->church_id)->get();

            $genders = SmBaseSetup::where('base_group_id', '=', '1')->get();

            return view('backEnd.studentInformation.subject_attendance_report_average_view', compact('classes', 'types', 'genders'));

        }catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        }

    }
    public function subjectAttendanceAverageReportSearch(subjectAttendanceAverageReportSearchRequest $request)

    {

        // return $request->all();

        try{

            $year = $request->year;

            $month = $request->month;

            $age_group_id = $request->class;

            $mgender_id = $request->section;

            $assign_subjects=SmAssignSubject::where('age_group_id', $age_group_id)->where('mgender_id', $mgender_id)->first();

            if(!$assign_subjects){

                Toastr::error('No Subject Assign ', 'Failed');

                return redirect()->back();
            }
            $subject_id = $assign_subjects->subject_id;

            $current_day = date('d');

            $days = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);

            $classes = SmClass::get();
            $activeStudentIds = SmStudentAttendanceController::activeStudent()->pluck('id')->toArray();
            $students = StudentRecord::where('age_group_id', $request->class)->where('mgender_id', $request->section)->whereIn('member_id', $activeStudentIds)->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get()->sortBy('roll_no');

            $attendances = [];

            foreach ($students as $record) {

                $attendance = SmSubjectAttendance::where('sm_subject_attendances.member_id', $record->member_id)

                    //  ->join('student_records','student_records.member_id','=','sm_subject_attendances.member_id')

                    // // ->where('subject_id', $subject_id)

                    ->where('attendance_date', 'like', $year . '-' . $month . '%')
                    ->where('sm_subject_attendances.student_record_id', $record->id)
                    ->where('sm_subject_attendances.church_year_id', getAcademicId())
                    ->where('sm_subject_attendances.church_id', Auth::user()->church_id)

                    ->get();

                if ($attendance) {

                    $attendances[] = $attendance;

                }

            }

            //   return $attendances;
            return view('backEnd.studentInformation.subject_attendance_report_average_view', compact('classes', 'attendances', 'days', 'year', 'month', 'current_day', 'age_group_id', 'mgender_id', 'subject_id'));

        }catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        }

    }


    public function studentAttendanceReportPrint($age_group_id, $mgender_id, $month, $year)
    {
        try{
            $current_day = date('d');
            $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $classes = SmClass::where('active_status', 1)->where('church_id',Auth::user()->church_id)->get();
            $activeStudentIds = SmStudentAttendanceController::activeStudent()->pluck('id')->toArray();
            $students = StudentRecord::where('age_group_id', $age_group_id)->where('mgender_id', $mgender_id)->whereIn('member_id', $activeStudentIds)->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();

            $attendances = [];
            foreach ($students as $record) {
                $attendance = SmStudentAttendance::where('member_id', $record->member_id)->where('attendance_date', 'like', $year . '-' . $month . '%')->where('church_id',Auth::user()->church_id)
                    ->where('student_record_id', $record->id)
                    ->get();
                if (count($attendance) != 0) {
                    $attendances[] = $attendance;
                }
            }

            return view('backEnd.studentInformation.student_attendance_report', compact('classes', 'attendances', 'days', 'year', 'month', 'current_day', 'age_group_id', 'mgender_id'));

        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }

    }
    public function subjectAttendanceReportAveragePrint($age_group_id, $mgender_id, $month, $year){
        set_time_limit(2700);
        try{
            $current_day = date('d');

            $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $activeStudentIds = SmStudentAttendanceController::activeStudent()->pluck('id')->toArray();
            $students = StudentRecord::where('age_group_id', $age_group_id)
                ->where('mgender_id', $mgender_id)
                ->whereIn('member_id', $activeStudentIds)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $attendances = [];

            foreach ($students as $record) {
                $attendance = SmSubjectAttendance::where('sm_subject_attendances.member_id', $record->member_id)
                    // ->join('student_records','student_records.member_id','=','sm_subject_attendances.member_id')
                    ->where('sm_subject_attendances.student_record_id', $record->id)
                    ->where('attendance_date', 'like', $year . '-' . $month . '%')
                    ->where('sm_subject_attendances.church_year_id', getAcademicId())
                    ->where('sm_subject_attendances.church_id',Auth::user()->church_id)
                    ->get();

                if ($attendance) {
                    $attendances[] = $attendance;
                }
            }

            return view('backEnd.studentInformation.student_subject_attendance',compact('attendances','days' , 'year'  , 'month','age_group_id'  ,'mgender_id'));

        }catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function subjectAttendanceReportPrint($age_group_id, $mgender_id, $month, $year)
    {
        set_time_limit(2700);
        try{
            $current_day = date('d');

            $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $students = SmStudent::where('age_group_id', $age_group_id)
                ->where('mgender_id', $mgender_id)
                ->where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id',Auth::user()->church_id)
                ->get();

            $attendances = [];

            foreach ($students as $record) {
                $attendance = SmSubjectAttendance::where('sm_subject_attendances.member_id', $record->member_id)
                    ->join('sm_students','sm_students.id','=','sm_subject_attendances.member_id')
                    ->where('sm_subject_attendances.student_record_id', $record->id)
                    ->where('attendance_date', 'like', $year . '-' . $month . '%')
                    ->where('sm_subject_attendances.church_year_id', getAcademicId())
                    ->where('sm_subject_attendances.church_id',Auth::user()->church_id)
                    ->get();

                if ($attendance) {
                    $attendances[] = $attendance;
                }
            }

            return view('backEnd.studentInformation.student_subject_attendance',compact('attendances','days' , 'year'  , 'month','age_group_id'  ,'mgender_id'));

        }catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}