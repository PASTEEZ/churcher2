<?php

namespace App\Http\Controllers\Admin\StudentInfo;

use App\SmClass;
use App\SmStaff;
use App\SmSection;
use App\SmStudent;
use App\SmBaseSetup;
use App\ApiBaseMethod;
use App\SmAssignSubject;
use Barryvdh\DomPDF\PDF;
use App\SmStudentCategory;
use App\SmStudentAttendance;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;
use App\Http\Requests\Admin\StudentInfo\StudentAttendanceReportSearchRequest;

class SmStudentAttendanceReportController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('PM');
   

    }
    public function index(Request $request)
    {
        try {
            if (teacherAccess()) {
                $teacher_info=SmStaff::where('user_id',Auth::user()->id)->first();
               $classes= $teacher_info->classes;
            } else {
                $classes = SmClass::get();
            }


            return view('backEnd.studentInformation.student_attendance_report', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function search(StudentAttendanceReportSearchRequest $request)
    {

        try {
            // return $request->all();
            $data=[];
            $year = $request->year;
            $month = $request->month;
            $age_group_id = $request->class;
            $mgender_id = $request->section;
            $current_day = date('d');
            $class = null;
            $section = null;
            if (!moduleStatusCheck('University')) {
                $class = SmClass::findOrFail($request->class);
                $section = SmSection::findOrFail($request->section);
            }

            $days = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);
            if (teacherAccess()) {
                $teacher_info=SmStaff::where('user_id', Auth::user()->id)->first();
                $classes= $teacher_info->classes;
            } else {
                $classes = SmClass::get();
            }
            $students = StudentRecord::where('age_group_id', $request->class)
            ->where('mgender_id', $request->section)
            ->where('church_year_id', getAcademicId())
            ->where('church_id', Auth::user()->church_id)->get()->sortBy('roll_no');
           
            if (moduleStatusCheck('University')) {
                $data['un_semester_label_id'] = $request->un_semester_label_id;
                $interface = App::make(UnCommonRepositoryInterface::class);
                $data += $interface->oldValueSelected($request);
                $model = StudentRecord::query();
                $students = universityFilter($model, $request)->get()->sortBy('roll_no');
                // return $data;
            }
            $attendances = [];
            foreach ($students as $record) {
                $attendance = SmStudentAttendance::where('member_id', $record->member_id)
                ->where('attendance_date', 'like', $request->year . '-' . $request->month . '%')
                ->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)
                ->where('student_record_id', $record->id)
                ->get();
                if (count($attendance) != 0) {
                    $attendances[] = $attendance;
                }
            }
            // return $attendance;
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['attendances'] = $attendances;
                $data['days'] = $days;
                $data['year'] = $year;
                $data['month'] = $month;
                $data['current_day'] = $current_day;
                $data['age_group_id'] = $age_group_id;
                $data['mgender_id'] = $mgender_id;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.studentInformation.student_attendance_report', compact('classes', 'attendances', 'students', 'days', 'year', 'month', 'current_day', 'age_group_id', 'mgender_id', 'class', 'section'))->with($data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function print($age_group_id, $mgender_id, $month, $year)
    {
        set_time_limit(2700);
        try {
            $current_day = date('d');
            $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $active_students = SmStudent::where('active_status', 1)->where('church_id', Auth::user()->church_id)->where('church_year_id', getAcademicId())->pluck('id')->toArray();
            $students = DB::table('student_records')
            ->where('age_group_id', $age_group_id)
            ->where('mgender_id', $mgender_id)
            ->where('church_year_id', getAcademicId())
            ->whereIn('member_id', $active_students)
            ->where('church_id', Auth::user()->church_id)
            ->get();

            $attendances = [];
            foreach ($students as $record) {
                $attendance = SmStudentAttendance::where('member_id', $record->member_id)
                ->where('attendance_date', 'like', $year . '-' . $month . '%')
                ->where('church_id', Auth::user()->church_id)
                ->where('church_year_id', getAcademicId())
                ->where('student_record_id', $record->id)
                ->get();

                if ($attendance) {
                    $attendances[] = $attendance;
                }
            }

            // $pdf = PDF::loadView(
            //     'backEnd.studentInformation.student_attendance_print',
            //     [
            //         'attendances' => $attendances,
            //         'days' => $days,
            //         'year' => $year,
            //         'month' => $month,
            //         'age_group_id' => $age_group_id,
            //         'mgender_id' => $mgender_id,
            //         'class' => SmClass::find($age_group_id),
            //         'section' => SmSection::find($mgender_id),
            //     ]
            // )->setPaper('A4', 'landscape');
            // return $pdf->stream('student_attendance.pdf');

            $class = SmClass::find($age_group_id);
            $section = SmSection::find($mgender_id);
            return view('backEnd.studentInformation.student_attendance_print', compact('class', 'section', 'attendances', 'days', 'year', 'month', 'current_day', 'age_group_id', 'mgender_id'));
        } catch (\Exception $e) {
          
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function unPrint($semester_id, $month, $year)
    {
        set_time_limit(2700);
        try {
            $current_day = date('d');
            $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $active_students = SmStudent::where('active_status', 1)->where('church_id', Auth::user()->church_id)->where('church_year_id', getAcademicId())->pluck('id')->toArray();
            $students = DB::table('student_records')
            ->where('un_semester_label_id', $semester_id)
            // ->where('church_year_id', getAcademicId())
             ->whereIn('member_id', $active_students)
            ->where('church_id', Auth::user()->church_id)
            ->get();

            $attendances = [];
            foreach ($students as $record) {
                $attendance = SmStudentAttendance::where('member_id', $record->member_id)
                ->where('attendance_date', 'like', $year . '-' . $month . '%')
                ->where('church_id', Auth::user()->church_id)
                ->where('church_year_id', getAcademicId())
                ->where('student_record_id', $record->id)
                ->get();

                if ($attendance) {
                    $attendances[] = $attendance;
                }
            }
            $request = (object)[
                'un_session_id'=>null,
                'un_faculty_id'=>null,
                'un_department_id'=>null,
                'un_church_year_id'=>null,
                'un_semester_id'=>null,
                'un_semester_label_id'=>$semester_id,
            ];
            $interface = App::make(UnCommonRepositoryInterface::class);
            $data = $interface->searchInfo($request);
            // $pdf = PDF::loadView(
            //     'backEnd.studentInformation.student_attendance_print',
            //     [
            //         'attendances' => $attendances,
            //         'days' => $days,
            //         'year' => $year,
            //         'month' => $month,
            //         'age_group_id' => $age_group_id,
            //         'mgender_id' => $mgender_id,
            //         'class' => SmClass::find($age_group_id),
            //         'section' => SmSection::find($mgender_id),
            //     ]
            // )->setPaper('A4', 'landscape');
            // return $pdf->stream('student_attendance.pdf');
            
            return view('backEnd.studentInformation.student_attendance_print', compact('attendances', 'days', 'year', 'month', 'current_day', 'semester_id'))->with($data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
