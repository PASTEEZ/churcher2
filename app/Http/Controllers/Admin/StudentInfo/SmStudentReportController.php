<?php

namespace App\Http\Controllers\Admin\StudentInfo;

use App\User;
use App\SmClass;
use App\Models\Membershiptype;
use App\Models\Gender;
use App\SmStaff;
use App\SmSection;
use App\SmStudent;
use App\SmUserLog;
use Carbon\Carbon;
use App\SmBaseSetup;
use App\ApiBaseMethod;
use Barryvdh\DomPDF\PDF;
use App\SmGeneralSettings;
use App\SmStudentCategory;
use App\InfixModuleManager;
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

class SmStudentReportController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('PM');

    }
        //studentReport modified by jmrashed
        public function studentReport(Request $request)
        {
            try {
                $classes = SmClass::get();
                $types = SmStudentCategory::get();
                $genders = SmBaseSetup::where('base_group_id', '=', '1')->get();
                
                return view('backEnd.studentInformation.student_report', compact('classes', 'types', 'genders'));
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        }
    
        //student report search modified by jmrashed
        public function studentReportSearch(Request $request)
        {
           if(moduleStatusCheck('University')){
                $request->validate([
                    'un_session_id' => 'required'
                ]);
           }else{
                $request->validate([
                    'class' => 'required'
                ]);
           }
            
            try {
                $data = [];
                $student_records = StudentRecord::query();
                $student_records->where('church_id',Auth::user()->church_id)->whereHas('studentDetail', function ($q){
                    $q->where('active_status', 1);
                });
                if($request->class){
                    $student_records->where('age_group_id',$request->class);
                }
                if($request->section){
                    $student_records->where('mgender_id',$request->section);
                }
                if (moduleStatusCheck('University')) {
                    $student_records = universityFilter($student_records, $request);
                }

            $students =  $student_records->with('student')->get();

              $data['student_records'] = $students;  
              $data['classes'] = SmClass::get();
              $data['types'] =   SmStudentCategory::get();
              $data['genders'] = SmBaseSetup::where('base_group_id', '=', '1')->get();
              $data['age_group_id'] = $request->class;
              $data['type_id'] = $request->type;
              $data['gender_id'] = $request->gender;
              $data['type_id'] = $request->type;
              if (moduleStatusCheck('University')) {
                $interface = App::make(UnCommonRepositoryInterface::class);
                $data += $interface->getCommonData($request);
            }
            return view('backEnd.studentInformation.student_report',$data);
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        }
    
        public function studentAttendanceReport(Request $request)
        {
            try {
                if (teacherAccess()) {
                    $teacher_info=SmStaff::where('user_id',Auth::user()->id)->first();
                   $classes=$teacher_info->classes;
                } else {
                    $classes = SmClass::get();
                }
                $types = SmStudentCategory::get();
                $genders = SmBaseSetup::where('base_group_id', '=', '1')->get();    

                return view('backEnd.studentInformation.student_attendance_report', compact('classes', 'types', 'genders'));
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        }
    
        public function studentAttendanceReportSearch(Request $request)
        {
    
            $input = $request->all();
            $validator = Validator::make($input, [
                'class' => 'required',
                'section' => 'required',
                'month' => 'required',
                'year' => 'required'
            ]);
    
            if ($validator->fails()) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
                }
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            try {
                $year = $request->year;
                $month = $request->month;
                $age_group_id = $request->class;
                $mgender_id = $request->section;
                $current_day = date('d');
                $clas = SmClass::findOrFail($request->class);
                $sec = SmSection::findOrFail($request->section);
                $days = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);
                if (teacherAccess()) {
                    $teacher_info=SmStaff::where('user_id',Auth::user()->id)->first();
                    $classes=$teacher_info->classes;
                } else {
                    $classes = SmClass::get();
                }
                $students = SmStudent::where('age_group_id', $request->class)
                            ->where('mgender_id', $request->section)->get();
    
                $attendances = [];
                foreach ($students as $student) {
                    $attendance = SmStudentAttendance::where('member_id', $student->id)->where('attendance_date', 'like', $request->year . '-' . $request->month . '%')->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
                    if (count($attendance) != 0) {
                        $attendances[] = $attendance;
                    }
                }
    
               
                return view('backEnd.studentInformation.student_attendance_report', compact('classes','attendances','students', 'days', 'year', 'month', 'current_day',
                    'age_group_id', 'mgender_id', 'clas', 'sec'));
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        }
    
    
        public function studentAttendanceReportPrint($age_group_id, $mgender_id, $month, $year)
        {
            set_time_limit(2700);
            try {
                $current_day = date('d');
                $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    
                $students = DB::table('sm_students')
                ->where('age_group_id', $age_group_id)
                ->where('mgender_id', $mgender_id)
                ->get();
    
                $attendances = [];
                foreach ($students as $student) {
                    $attendance = SmStudentAttendance::where('member_id', $student->id)
                    ->where('attendance_date', 'like', $year . '-' . $month . '%')
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







        public function guardianReport(Request $request)
        {
            try {
                $classes = SmClass::get();
                return view('backEnd.studentInformation.guardian_report', compact('classes'));
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        }
    
        public function guardianReportSearch(Request $request)
        {
            $input = $request->all();
            if(moduleStatusCheck('University')){
                $validator = Validator::make($input, [
                    'un_session_id' => 'required'
                ]);
            }else{
                $validator = Validator::make($input, [
                    'class' => 'required'
                ]);
            }
            
    
            if ($validator->fails()) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
                }
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            try {
                $student_records = StudentRecord::query();
                $student_records->where('church_id',Auth::user()->church_id);
                if($request->class){
                    $student_records->where('age_group_id',$request->class);
                }
                if($request->section){
                    $student_records->where('mgender_id',$request->section);
                }
                if (moduleStatusCheck('University')) {
                    $student_records = universityFilter($student_records, $request);
                }

                $students =  $student_records->with('student')->get();
                $data = [];
                $data['student_records'] =  $students;
                $data['classes'] = SmClass::get();
                $data['age_group_id'] = $request->class;
                $data['clas'] = SmClass::find($request->class);
                $data['mgender_id'] = $request->section;
                if (moduleStatusCheck('University')) {
                    $interface = App::make(UnCommonRepositoryInterface::class);
                    $data += $interface->getCommonData($request);
                }
                return view('backEnd.studentInformation.guardian_report',$data);
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        }
    







        public function membershipReport(Request $request)
        {
            try {
                $memberstypes = Membershiptype::get();
                return view('backEnd.studentInformation.membershiptype_report', compact('memberstypes'));
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        }
    
        public function membershipReportSearch(Request $request)
        {
           
     
            try {
                $student_records = StudentRecord::query();
                $student_records->where('church_id',Auth::user()->church_id);
                if($request->memberstype){
                    $student_records->where('age_group_id',$request->class);
                }
                if($request->gender){
                    $student_records->where('mgender_id',$request->gender);
                }
                

                $students =  $student_records->with('student')->get();
                $data = [];
                $data['student_records'] =  $students;
                $data['classes'] = Membershiptype::get();
                $data['age_group_id'] = $request->class;
                $data['clas'] = Membershiptype::find($request->class);
                $data['mgender_id'] = $request->gender;
                 
                return view('backEnd.studentInformation.membershiptype_report',$data);
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        }
    
        public function studentLoginReport(Request $request)
        {
            try {
                $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
    
                return view('backEnd.studentInformation.login_info', compact('classes'));
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        }
    
    
        public function studentLoginSearch(Request $request){

            $input = $request->all();
            if(moduleStatusCheck('University')){
                $request->validate([
                    'un_session_id' => 'required'
                ]);
           }else{
                $request->validate([
                    'class' => 'required'
                ]);
           }
            try {
                $data = [];
                $student_records = StudentRecord::query();
                $student_records->where('church_id',Auth::user()->church_id);
                if($request->class){
                    $student_records->where('age_group_id',$request->class);
                }
                if($request->section){
                    $student_records->where('mgender_id',$request->section);
                }
                if (moduleStatusCheck('University')) {
                    $student_records = universityFilter($student_records, $request);
                }

                $students =  $student_records->with('student')->get();
                $data['student_records'] = $students;  
                $data['classes'] = SmClass::get();
                $data['age_group_id'] = $request->class;
                $data['mgender_id'] = $request->section;
                $data['clas']= SmClass::find($request->class);
                if (moduleStatusCheck('University')) {
                    $interface = App::make(UnCommonRepositoryInterface::class);
                    $data += $interface->getCommonData($request);
                }
                return view('backEnd.studentInformation.login_info',$data);
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        }

        public function studentHistory(Request $request)
        {
            try {
                $classes = SmClass::get();
    
               
    
                $years = SmStudent::select('admission_date')->where('active_status', 1)
                    ->where('church_year_id', getAcademicId())->get()
                    ->groupBy(function ($val) {
                         return Carbon::parse($val->admission_date)->format('Y');
                    });
                    

    
                return view('backEnd.studentInformation.student_history', compact('classes', 'years'));
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        }
    
        public function studentHistorySearch(Request $request)
        {
            $input = $request->all();
            $validator = Validator::make($input, [
                'class' => 'required'
            ]);
    
            if ($validator->fails()) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
                }
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            try {
                $member_ids = $this->classSectionStudent($request);
                $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
                $students = SmStudent::query();
                $students->where('church_year_id', getAcademicId())->where('active_status', 1);
                if ($request->admission_year != "") {
                    $students->where('admission_date', 'like',  $request->admission_year . '%');
                }
    
                $students = $students->whereIn('id', $member_ids)->with('parents','section','class','promotion','session')->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
               
                $years = SmStudent::select('admission_date')->where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->get()
                    ->groupBy(function ($val) {
                        return Carbon::parse($val->admission_date)->format('Y');
                    });
                $age_group_id = $request->class;
                $year = $request->admission_year;
                $member_id=null;   

                $clas = SmClass::find($request->class);
                return view('backEnd.studentInformation.student_history', compact('students', 'classes', 'years', 'age_group_id', 'year', 'clas', 'member_id'));
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        }
    // this function call others 
    public static function classSectionStudent($request)
    {
         $member_ids = StudentRecord::when($request->church_year, function ($query) use ($request) {
            $query->where('church_year_id', $request->church_year);
        })
        ->when($request->class, function ($query) use ($request) {
            $query->where('age_group_id', $request->class);
        })
        ->when($request->section, function ($query) use ($request) {
            $query->where('mgender_id', $request->section);
        })
        ->when(!$request->church_year, function ($query) use ($request) {
            $query->where('church_year_id', getAcademicId());
        })->where('church_id', auth()->user()->church_id)->where('is_promote', 0)->pluck('member_id')->unique();

        return $member_ids;
    }
    
}