<?php

namespace App\Http\Controllers\Admin\StudentInfo;

use App\User;
use App\SmClass;
use App\SmRoute;
use App\SmStaff;
use App\SmParent;
use App\SmSchool;
use App\SmStudent;
use App\SmVehicle;
use App\SmExamType;
use App\SmBaseSetup;
use App\SmFeesMaster;
use App\SmMarksGrade;
use App\ApiBaseMethod;
use App\SmAcademicYear;
use App\SmEmailSetting;
use App\SmExamSchedule;
use App\SmStudentGroup;
use App\SmDormitoryList;
use App\SmGeneralSettings;
use App\SmStudentCategory;
use App\SmStudentTimeline;
use App\CustomResultSetting;
use App\Traits\CustomFields;
use Illuminate\Http\Request;
use App\Models\SmCustomField;
use App\Models\StudentRecord;
use App\StudentBulkTemporary;
use App\Imports\StudentsImport;
use App\Traits\FeesAssignTrait;
use Modules\Lead\Entities\Lead;
use Modules\Lead\Entities\Source;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Modules\Lead\Entities\LeadCity;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\DirectFeesAssignTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Modules\Saas\Entities\SmPackagePlan;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Support\Facades\Validator;
use App\Models\SmStudentRegistrationField;
use Modules\University\Entities\UnSubject;
use Modules\University\Entities\UnAcademicYear;
use Modules\University\Entities\UnAssignSubject;
use Modules\University\Entities\UnSemesterLabel;
use Modules\University\Entities\UnSubjectComplete;
use Modules\ParentRegistration\Entities\SmStudentField;
use Modules\University\Entities\UnSubjectAssignStudent;
use App\Http\Controllers\Admin\Hr\StaffAsParentController;
use Modules\ParentRegistration\Entities\SmStudentRegistration;
use App\Http\Requests\Admin\StudentInfo\SmStudentAdmissionRequest;
use Modules\University\Http\Controllers\UnStudentPromoteController;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;
use Modules\University\Repositories\Interfaces\UnSubjectRepositoryInterface;
use Modules\University\Repositories\Interfaces\UnDepartmentRepositoryInterface;
use Modules\University\Repositories\Interfaces\UnSemesterLabelRepositoryInterface;

class SmNonadultAdmissionController extends Controller
{
    use CustomFields;
    use FeesAssignTrait;
    use DirectFeesAssignTrait;

    public function __construct()
    {
        $this->middleware('PM');
    }

    public function index()
    {

        try {
            if (isSubscriptionEnabled() && auth()->user()->school_id != 1) {

                $active_student = SmStudent::where('school_id', Auth::user()->school_id)->where('active_status', 1)->count();

                if (\Modules\Saas\Entities\SmPackagePlan::student_limit() <= $active_student) {

                    Toastr::error('Your student limit has been crossed.', 'Failed');
                    return redirect()->back();

                }
            }
           
            $data = $this->loadData();
            $data['max_admission_id'] = SmStudent::where('school_id', Auth::user()->school_id)->max('id');
            $data['max_roll_id'] = SmStudent::where('school_id', Auth::user()->school_id)->max('roll_no');

            if (moduleStatusCheck('University')) {
                return view('university::admission.add_student_admission', $data);
            }
            return view('backEnd.studentInformation.student_admission', $data);

        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

 
  
    public function view(Request $request, $id, $type = null)
    {
        try {
            $next_labels = null;
            $student_detail = SmStudent::withoutGlobalScope(StatusAcademicSchoolScope::class)->find($id);

            $records = studentRecords(null, $student_detail->id)->get();
            $siblings = SmStudent::where('parent_id', $student_detail->parent_id)->where('id', '!=', $id)->status()->whereNotNull('parent_id')->withoutGlobalScope(StatusAcademicSchoolScope::class)->get();
            $exams = SmExamSchedule::where('class_id', $student_detail->class_id)
                ->where('section_id', $student_detail->section_id)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $academic_year = SmAcademicYear::where('id', $student_detail->session_id)
                ->first();

            $result_setting = CustomResultSetting::where('school_id',auth()->user()->school_id)->where('academic_id',getAcademicId())->get();

            $grades = SmMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $max_gpa = SmMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->max('gpa');

            $fail_gpa = SmMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->min('gpa');

            $fail_gpa_name = SmMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->where('gpa', $fail_gpa)
                ->first();

            $timelines = SmStudentTimeline::where('staff_student_id', $id)
                ->where('type', 'stu')->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            if (!empty($student_detail->vechile_id)) {
                $driver_id = SmVehicle::where('id', '=', $student_detail->vechile_id)->first();
                $driver_info = SmStaff::where('id', '=', $driver_id->driver_id)->first();
            } else {
                $driver_id = '';
                $driver_info = '';
            }

            $exam_terms = SmExamType::where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $custom_field_data = $student_detail->custom_field;

            if (!is_null($custom_field_data)) {
                $custom_field_values = json_decode($custom_field_data);
            } else {
                $custom_field_values = null;
            }
            $sessions = SmAcademicYear::get(['id', 'year', 'title']);
            if(moduleStatusCheck('University')){
                $next_labels = null;
                if($student_detail->defaultClass){
                    $next_labels = UnSemesterLabel::where('un_department_id', $student_detail->defaultClass->un_department_id)
                                                    ->where('un_faculty_id', $student_detail->defaultClass->un_faculty_id)
                                                    ->whereNotIn('id', $student_detail->studentRecords->pluck('un_semester_label_id')->toArray())->get()->map(function ($item) {
                                                        return [
                                                            'id'=>$item->id,
                                                            'name'=>$item->name,
                                                            'title'=>$item->semesterDetails->name .'['. $item->academicYearDetails->name .'] '. $item->name,
                                                        ]; 
                                                    });
                }
                $student_id = $student_detail->id;
                $studentDetails = SmStudent::find($student_id);
                $studentRecordDetails = StudentRecord::where('student_id',$student_id);
                $studentRecords = StudentRecord::where('student_id',$student_id)->groupBy('un_academic_id')->get();
                return view('backEnd.studentInformation.student_view', compact('timelines','student_detail', 'driver_info', 'exams', 'siblings', 'grades', 'academic_year', 'exam_terms', 'max_gpa', 'fail_gpa_name', 'custom_field_values', 'sessions', 'records', 'next_labels', 'type','studentRecordDetails','studentDetails','studentRecords','result_setting'));
            }else{
                return view('backEnd.studentInformation.student_view', compact('timelines','student_detail', 'driver_info', 'exams', 'siblings', 'grades', 'academic_year', 'exam_terms', 'max_gpa', 'fail_gpa_name', 'custom_field_values', 'sessions', 'records', 'next_labels', 'type','result_setting'));
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentDetails(Request $request)
    {
        try {
            $classes = SmClass::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $students = SmStudent::where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $sessions = SmAcademicYear::where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            return view('backEnd.studentInformation.student_details', compact('classes', 'sessions'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function jymemberDetails(Request $request)
    {
        try {
            $classes = SmClass::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

                $students = SmStudent::where('academic_id', getAcademicId())
             ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();


                //$students = DB::table('sm_jymembers')
               // ->where('academic_id', getAcademicId())
              

            $sessions = SmAcademicYear::where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            return view('backEnd.studentInformation.jymember_details', compact('classes', 'sessions'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
  
 
    public function checkExitStudent(Request $request)
    {
        try {
            $email = $request->email;
            $phone = $request->phone;
            $student = null;
            if ($email || $phone) {
                $x_student = SmStudent::query();
                if ($email && $phone) {
                    $x_student->where('mobile', $phone);
                } else if ($email) {
                    $x_student->where('email', $email);
                } else if ($phone) {
                    $x_student->where('mobile', $phone);
                }
                $student = $x_student->first();
            }
            return response()->json(['student' => $student]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

 

    public static function loadData()
    {
        $data['classes'] = SmClass::get(['id', 'class_name']);
        $data['religions'] = SmBaseSetup::where('base_group_id', '=', '2')->get(['id', 'base_setup_name']);
        $data['blood_groups'] = SmBaseSetup::where('base_group_id', '=', '3')->get(['id', 'base_setup_name']);
        $data['genders'] = SmBaseSetup::where('base_group_id', '=', '1')->get(['id', 'base_setup_name']);
        $data['route_lists'] = SmRoute::get(['id', 'title']);
        $data['dormitory_lists'] = SmDormitoryList::get(['id', 'dormitory_name']);
        $data['categories'] = SmStudentCategory::get(['id', 'category_name']);
        $data['groups'] = SmStudentGroup::get(['id', 'group']);
        $data['sessions'] = SmAcademicYear::get(['id', 'year', 'title']);
        $data['driver_lists'] = SmStaff::where([['active_status', '=', '1'], ['role_id', 9]])->where('school_id', Auth::user()->school_id)->get(['id', 'full_name']);
        $data['custom_fields'] = SmCustomField::where('form_name', 'student_registration')->where('school_id', Auth::user()->school_id)->get();
        $data['vehicles'] = SmVehicle::get();
        $data['staffs'] = SmStaff::where('role_id', '!=', 1)->get(['first_name', 'last_name', 'full_name', 'id', 'user_id', 'parent_id']);
        $data['lead_city'] = [];
        $data['sources'] = [];

        if (moduleStatusCheck('Lead') == true) {
            $data['lead_city'] = \Modules\Lead\Entities\LeadCity::where('school_id', auth()->user()->school_id)->get(['id', 'city_name']);
            $data['sources'] = \Modules\Lead\Entities\Source::where('school_id', auth()->user()->school_id)->get(['id', 'source_name']);
        }

        if (moduleStatusCheck('University') == true) {
            $data['un_session'] = \Modules\University\Entities\UnSession::where('school_id', auth()->user()->school_id)->get(['id', 'name']);
            $data['un_academic_year'] = \Modules\University\Entities\UnAcademicYear::where('school_id', auth()->user()->school_id)->get(['id', 'name']);
        }

        session()->forget('fathers_photo');
        session()->forget('mothers_photo');
        session()->forget('guardians_photo');
        session()->forget('student_photo');
        return $data;
    }

    public function studentBulkStore(Request $request)
    {

        if (moduleStatusCheck('University')) {
            $request->validate(
                [
                    'un_session_id' => 'required',
                    'un_faculty_id' => 'nullable',
                    'un_department_id' => 'required',
                    'un_academic_id' => 'required',
                    'un_semester_id' => 'required',
                    'un_semester_label_id' => 'required',
                    'un_section_id' => 'required',
                    'file' => 'required'
                ],
                [
                    'session.required' => 'Financial year field is required.'
                ]
            );
        } else {
            $request->validate(
                [
                    'session' => 'required',
                    'class' => 'required',
                    'section' => 'required',
                    'file' => 'required'
                ],
                [
                    'session.required' => 'Financial year field is required.'
                ]
            );

        }

        $file_type = strtolower($request->file->getClientOriginalExtension());
        if ($file_type <> 'csv' && $file_type <> 'xlsx' && $file_type <> 'xls') {
            Toastr::warning('The file must be a file of type: xlsx, csv or xls', 'Warning');
            return redirect()->back();
        } else {
            try {
                DB::beginTransaction();
                $path = $request->file('file');
                Excel::import(new StudentsImport, $request->file('file'), 's3', \Maatwebsite\Excel\Excel::XLSX);
                $data = StudentBulkTemporary::where('user_id', Auth::user()->id)->get();

                $shcool_details = SmGeneralSettings::where('school_id', auth()->user()->school_id)->first();
                $school_name = explode(' ', $shcool_details->school_name);
                $short_form = '';
                foreach ($school_name as $value) {
                    $ch = str_split($value);
                    $short_form = $short_form . '' . $ch[0];
                }

                if (!empty($data)) {
                    foreach ($data as $key => $value) {
                        if (isSubscriptionEnabled()) {

                            $active_student = SmStudent::where('school_id', Auth::user()->school_id)->where('active_status', 1)->count();

                            if (\Modules\Saas\Entities\SmPackagePlan::student_limit() <= $active_student) {

                                DB::commit();
                                StudentBulkTemporary::where('user_id', Auth::user()->id)->delete();
                                Toastr::error('Your student limit has been crossed.', 'Failed');
                                return redirect('member-list');

                            }
                        }


                        $ad_check = SmStudent::where('admission_no', (String)$value->admission_number)->where('school_id', Auth::user()->school_id)->get();
                        //  return $ad_check;

                        if ($ad_check->count() > 0) {
                            if ($value->phone_number || $value->email) {
                                $user = User::when($value->phone_number && !$value->email, function ($q) use ($value) {
                                    $q->where('phone_number', $value->phone_number)->orWhere('username', $value->phone_number);
                                })
                                ->when($value->email && !$value->phone_number, function ($q) use ($value) {
                                    $q->where('email', $value->email)->orWhere('username', $value->email);
                                })
                                ->when($value->email && $value->phone_number, function ($q) use ($value) {
                                    $q->where('email', $value->email);
                                })->first();
                                if ($user) {
                                    if ($user->role_id == 2) {
                                        if (moduleStatusCheck('University')) {
                                            $model = StudentRecord::query();
                                            $studentRecord = universityFilter($model, $request)->first();
                                        } else {
                                            $studentRecord = StudentRecord::where('class_id', $request->class)
                                                ->where('section_id', $request->section)
                                                ->where('academic_id', $request->session)
                                                ->where('student_id', $user->student->id)
                                                ->where('school_id', auth()->user()->school_id)
                                                ->first();
                                        }
                                        if (!$studentRecord) {
                                            $this->insertStudentRecord($request->merge([
                                                'student_id' => $user->student->id,
                                                'roll_number'=>$request->admission_number
                                            ]));

                                        }

                                    }
                                }
                            }
                            DB::rollback();
                            StudentBulkTemporary::where('user_id', Auth::user()->id)->delete();
                            Toastr::error('Admission number should be unique.', 'Failed');
                            return redirect()->back();
                        }

                        if ($value->email != "") {
                            $chk = DB::table('sm_students')->where('email', $value->email)->where('school_id', Auth::user()->school_id)->count();
                            if ($chk >= 1) {
                                DB::rollback();
                                StudentBulkTemporary::where('user_id', Auth::user()->id)->delete();
                                Toastr::error('Student Email address should be unique.', 'Failed');
                                return redirect()->back();
                            }
                        }

                        if ($value->guardian_email != "") {
                            $chk = DB::table('sm_parents')->where('guardians_email', $value->guardian_email)->where('school_id', Auth::user()->school_id)->count();
                            if ($chk >= 1) {
                                DB::rollback();
                                StudentBulkTemporary::where('user_id', Auth::user()->id)->delete();
                                Toastr::error('Guardian Email address should be unique.', 'Failed');
                                return redirect()->back();
                            }
                        }

                        $parentInfo = ($value->father_name || $value->father_phone || $value->mother_name || $value->mother_phone || $value->guardian_email || $value->guardian_phone )  ? true : false;
                        try {

                            if ($value->admission_number == null) {
                                continue;
                            } else {

                            }

                            $academic_year = moduleStatusCheck('University') 
                            ? UnAcademicYear::find($request->un_session_id) : SmAcademicYear::find($request->session);


                            $user_stu = new User();
                            $user_stu->role_id = 2;
                            $user_stu->full_name = $value->first_name . ' ' . $value->last_name;

                            if (empty($value->email)) {
                                $user_stu->username = $value->admission_number;
                            } else {
                                $user_stu->username = $value->email;
                            }

                            $user_stu->email = $value->email;

                            $user_stu->school_id = Auth::user()->school_id;

                            $user_stu->password = Hash::make(123456);

                            $user_stu->created_at = $academic_year->year . '-01-01 12:00:00';

                            $user_stu->save();

                            $user_stu->toArray();

                            try {
                                $userIdParent = null;
                                $hasParent = null;
                                if ($value->guardian_email || $value->guardian_phone) {
                                    $user_parent = new User();
                                    $user_parent->role_id = 3;
                                    $user_parent->full_name = $value->father_name;

                                    if (empty($value->guardian_email)) {
                                        $data_parent['email'] = 'par_' . $value->admission_number;

                                        $user_parent->username = 'par_' . $value->admission_number;
                                    } else {

                                        $data_parent['email'] = $value->guardian_email;

                                        $user_parent->username = $value->guardian_email;
                                    }

                                    $user_parent->email = $value->guardian_email;

                                    $user_parent->password = Hash::make(123456);
                                    $user_parent->school_id = Auth::user()->school_id;

                                    $user_parent->created_at = $academic_year->year . '-01-01 12:00:00';

                                    $user_parent->save();
                                    $user_parent->toArray();
                                    $userIdParent = $user_parent->id;
                                }
                                try {
                                    if ($parentInfo) {
                                        $parent = new SmParent();

                                        if (
                                            $value->relation == 'F' ||
                                            $value->guardian_relation == 'F' ||
                                            $value->guardian_relation == 'F' ||
                                            strtolower($value->guardian_relation) == 'father' ||
                                            strtolower($value->guardian_relation) == 'father'
                                        ) {
                                            $relationFull = 'Father';
                                            $relation = 'F';
                                        } elseif (
                                            $value->relation == 'M' ||
                                            $value->guardian_relation == 'M' ||
                                            $value->guardian_relation == 'M' ||
                                            strtolower($value->guardian_relation) == 'mother' ||
                                            strtolower($value->guardian_relation) == 'mother'
                                        ) {
                                            $relationFull = 'Mother';
                                            $relation = 'M';
                                        } else {
                                            $relationFull = 'Other';
                                            $relation = 'O';
                                        }
                                        $parent->guardians_relation = $relationFull;
                                        $parent->relation = $relation;

                                        $parent->user_id = $userIdParent;
                                        $parent->fathers_name = $value->father_name;
                                        $parent->fathers_mobile = $value->father_phone;
                                        $parent->fathers_occupation = $value->fathe_occupation;
                                        $parent->mothers_name = $value->mother_name;
                                        $parent->mothers_mobile = $value->mother_phone;
                                        $parent->mothers_occupation = $value->mother_occupation;
                                        $parent->guardians_name = $value->guardian_name;
                                        $parent->guardians_mobile = $value->guardian_phone;
                                        $parent->guardians_occupation = $value->guardian_occupation;
                                        $parent->guardians_address = $value->guardian_address;
                                        $parent->guardians_email = $value->guardian_email;
                                        $parent->school_id = Auth::user()->school_id;
                                        $parent->academic_id = $request->session;

                                        $parent->created_at = $academic_year->year . '-01-01 12:00:00';

                                        $parent->save();
                                        $parent->toArray();
                                        $hasParent = $parent->id;
                                    }
                                    try {
                                        $student = new SmStudent();
                                        // $student->siblings_id = $value->sibling_id;
                                        // $student->class_id = $request->class;
                                        // $student->section_id = $request->section;
                                        $student->session_id = $request->session;
                                        $student->user_id = $user_stu->id;

                                        $student->parent_id = $hasParent ? $parent->id : null;
                                        $student->role_id = 2;

                                        $student->admission_no = $value->admission_number;
                                        $student->roll_no = $value->admission_number;
                                        $student->first_name = $value->first_name;
                                        $student->last_name = $value->last_name;
                                        $student->full_name = $value->first_name . ' ' . $value->last_name;
                                        $student->gender_id = $value->gender;
                                        $student->date_of_birth = date('Y-m-d', strtotime($value->date_of_birth));
                                        $student->caste = $value->caste;
                                        $student->email = $value->email;
                                        $student->mobile = $value->mobile;
                                        $student->admission_date = date('Y-m-d', strtotime($value->admission_date));
                                        $student->bloodgroup_id = $value->blood_group;
                                        $student->religion_id = $value->religion;
                                        $student->height = $value->height;
                                        $student->weight = $value->weight;
                                        $student->current_address = $value->current_address;
                                        $student->permanent_address = $value->permanent_address;
                                        $student->national_id_no = $value->national_identification_no;
                                        $student->local_id_no = $value->local_identification_no;
                                        $student->bank_account_no = $value->bank_account_no;
                                        $student->bank_name = $value->bank_name;
                                        $student->previous_school_details = $value->previous_school_details;
                                        $student->aditional_notes = $value->note;
                                        $student->school_id = Auth::user()->school_id;
                                        $student->academic_id = $request->session;
                                        if (moduleStatusCheck('University')) {
                                        
                                            $student->un_academic_id = $request->un_academic_id;
                                        }
                                        $student->created_at = $academic_year->year . '-01-01 12:00:00';
                                        $student->save();
                                        $this->insertStudentRecord($request->merge([
                                            'student_id' => $student->id,
                                            'is_default' => 1,
                                            'roll_number'=>$value->admission_number
                                        ]));
                                        
                                        $user_info = [];

                                        if ($value->email != "") {
                                            $user_info[] = array('email' => $value->email, 'username' => $value->email);
                                        }


                                        if ($value->guardian_email != "") {
                                            $user_info[] = array('email' => $value->guardian_email, 'username' => $data_parent['email']);
                                        }
                                    } catch (\Illuminate\Database\QueryException $e) {
                                        DB::rollback();
                                        Toastr::error('Operation Failed', 'Failed');
                                        return redirect()->back();
                                    } catch (\Exception $e) {
                                        DB::rollback();
                                        Toastr::error('Operation Failed', 'Failed');
                                        return redirect()->back();
                                    }
                                } catch (\Exception $e) {
                                    DB::rollback();
                                    Toastr::error('Operation Failed', 'Failed');
                                    return redirect()->back();
                                }
                            } catch (\Exception $e) {
                                DB::rollback();
                                Toastr::error('Operation Failed', 'Failed');
                                return redirect()->back();
                            }
                        } catch (\Exception $e) {
                            DB::rollback();
                            Toastr::error('Operation Failed', 'Failed');
                            return redirect()->back();
                        }
                    }

                    StudentBulkTemporary::where('user_id', Auth::user()->id)->delete();

                    DB::commit();
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                }
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        }
    }

    public function mm(){
        return view('backEnd.studentInformation.mm');
    }

    public static function staffAsParent(int $staff_id)
    {

    }

}
