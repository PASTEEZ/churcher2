<?php

namespace App\Http\Controllers\Admin\Examination;

use Exception;

use App\SmExam;
use App\SmClass;
use App\SmSection;
use App\SmStudent;
use App\YearCheck;
use App\SmExamType;
use App\ApiBaseMethod;
use App\SmAssignSubject;
use App\CustomResultSetting;
use Illuminate\Http\Request;
use App\SmCustomTemporaryResult;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ExamStepSkip;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomResultSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
        // User::checkAuth();
    }

    public function index()
    {
        try {
            $exams = SmExamType::get();
            $custom_settings = CustomResultSetting::query();
            $custom_settings->where('church_id',Auth::user()->church_id);
            if(moduleStatusCheck('University')){
                $custom_settings = $custom_settings->where('un_church_year_id',getAcademicId());
            }else{
                $custom_settings = $custom_settings->where('church_year_id',getAcademicId());
            }
            $custom_settings = $custom_settings->get();
            
            $check_exist= CustomResultSetting::where('church_year','=',generalSetting()->session_id)
                ->first();

            $edit_data= $custom_settings->count();

            $meritListSettings = CustomResultSetting::first();

            $skipSteps = ['exam_schedule', 'exam_attendance'];
            $exitSkipSteps = ExamStepSkip::where('church_id', auth()->user()->church_id)->pluck('name')->toArray();
            
            return view('backEnd.systemSettings.custom_result_setting_add', compact('custom_settings', 'exams','edit_data','meritListSettings', 'skipSteps', 'exitSkipSteps'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function store(request $request)
    {
        try{
            foreach($request->exam_type_percent as $key=>$exam_percent){
                $custom_setting = new CustomResultSetting();
                $custom_setting->exam_type_id = $key;
                $custom_setting->exam_percentage = $exam_percent;
                $custom_setting->church_year = getAcademicId();
                $custom_setting->church_id = Auth::user()->church_id;
                $custom_setting->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                if(moduleStatusCheck('University')){
                    $custom_setting->un_church_year_id = getAcademicId();
                }else{
                    $custom_setting->church_year_id = getAcademicId();
                }
                $result=$custom_setting->save();
                if($result){
                    $exam_percentage=SmExamType::find($key);
                    $exam_percentage->percentage=$exam_percent;
                    $exam_percentage->update();
                }
            }
            Toastr::success('Operation successful', 'Success');
            return redirect('custom-result-setting');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function merit_list_settings(Request $request){
        try{
            $custom_setting = CustomResultSetting::first();
            if(!$custom_setting){
                $custom_setting = new CustomResultSetting();
                $custom_setting->merit_list_setting = 'total_mark';
                $custom_setting->church_id = Auth::user()->church_id;
                if(moduleStatusCheck('University')){
                    $custom_setting->un_church_year_id = getAcademicId();
                }else{
                    $custom_setting->church_year_id = getAcademicId();
                }

            }

            if($request['value']){
                $custom_setting->merit_list_setting = $request['value'];
            }

            if($request['printStatus'] == "image"){
                if($request['key'] == 1){
                    $custom_setting->profile_image = $request['printStatus'];
                }else{
                    $custom_setting->profile_image = null;
                }
            }

            if($request['printStatus'] == "header"){
                if($request['key'] == 1){
                    $custom_setting->header_background = $request['printStatus'];
                }else{
                    $custom_setting->header_background = null;
                }
            }

            if($request['printStatus'] == "body"){
                if($request['key'] == 1){
                    $custom_setting->body_background = $request['printStatus'];
                }else{
                    $custom_setting->body_background = null;
                }
            }
            if($request['printStatus'] == "vertical_boarder"){
                if($request['key'] == 1){
                    $custom_setting->vertical_boarder = $request['printStatus'];
                }else{
                    $custom_setting->vertical_boarder = null;
                }
            }

            $result = $custom_setting->save();

            if($result){
                return response()->json('success');
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }



    public function edit($id)
    {

        try {
            $result_setting = CustomResultSetting::where('id', $id)->first();
            $exams = SmExamType::get();

            return view('backEnd.systemSettings.custom_result_setting_add', compact('exams', 'result_setting'));
        } catch (\Exception $e) {
            Toastr::error('Data not found', 'Failed');
            return redirect()->back();
        }
    }

    public function update(Request $request)
    {

        try {
            $gs = generalSetting();
            foreach($request->exam_type_percent as $key=>$exam_persent){
                $custom_setting = CustomResultSetting::where('exam_type_id',$key)->first();
                if(!$custom_setting){
                    $custom_setting = new CustomResultSetting;
                }
                $custom_setting->exam_type_id = $key;
                $custom_setting->exam_percentage = $exam_persent;
                $custom_setting->church_year = $gs->session_id;
                $custom_setting->church_id = Auth::user()->church_id;
                $custom_setting->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                $custom_setting->church_year_id = getAcademicId();
                $result=$custom_setting->save();

                if($result){
                    $exam_percentage=SmExamType::find($key);
                    $exam_percentage->percentage=$exam_persent;
                    $exam_percentage->save();
                }
            }
            Toastr::success('Operation successful', 'Success');
            return redirect('custom-result-setting');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function delete($id)
    {
        try {
            $result_setting = CustomResultSetting::findOrfail($id);
            $result_setting->delete();
            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function meritListReportIndex(Request $request)
    {
        try {
            $exams = SmExamType::get();
            $classes = SmClass::get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['exams'] = $exams->toArray();
                $data['classes'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.reports.custom_merit_list_report', compact('exams', 'classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }




    function getPercentageFromExam($id, $key)
    {
        try {

            $custom_result_setup = CustomResultSetting::where('church_year', generalSetting()->session_id)->first();

            if (!empty($custom_result_setup)) {
                if ($key == 0) {

                    return $custom_result_setup->percentage1 * .01;
                } elseif ($key == 1) {
                    return $custom_result_setup->percentage2 * .01;
                } elseif ($key == 2) {
                    return $custom_result_setup->percentage3 * .01;
                }
            } else {
                Toastr::warning('Please Complete Custom Setup', 'Warning');
                return redirect()->back();
            }

        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    function getGradeNameFromGPA($marks)
    {
        try {
            $marks_gpa = DB::table('sm_marks_grades')->where('from', '<=', $marks)->where('up', '>=', $marks)->where('church_id', Auth::user()->church_id)->where('church_year_id',getAcademicId())->first();
            if (!empty($marks_gpa)) {
                return $marks_gpa->grade_name;
            } else {
                return "NotFind";
            }

        } catch (\Exception $e) {
            Toastr::error('Operation Failed 10', 'Failed');
            return redirect()->back();
        }
    }
    public function meritListReport(Request $request)
    {
        $request->validate([
            'class' => 'required',
            'section' => 'required'
        ]);

        try {
            $iid = time();
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            // if ($request->method() == 'POST') {
            $input = $request->all();
            $validator = Validator::make($input, [
                'class' => 'required',
                'section' => 'required'
            ]);

            if ($validator->fails()) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
                }
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            $InputExamId = $request->exam;
            $InputClassId = $request->class;
            $InputSectionId = $request->section;

            $class          = SmClass::find($InputClassId);
            $section        = SmSection::find($InputSectionId);

            $age_group_name = $class->age_group_name;

            $result_archive = [];

            $assigned_exam = SmExam::where('age_group_id', $InputClassId)
                ->where('mgender_id', $InputSectionId)
                ->select('exam_type_id')
                ->DISTINCT()
                ->get();

            $classes = SmClass::get();

            $eligible_students = SmStudent::where('age_group_id', $InputClassId)
                ->where('mgender_id', $InputSectionId)
                ->get();

            $eligible_subjects = SmAssignSubject::where('age_group_id', $InputClassId)
                ->get();

            $student_yearly_result = [];
            $result_archive = [];


            $student_result = [];
            foreach ($eligible_students as  $student) {
                $store = SmCustomTemporaryResult::where('member_id',  $student->id)->first();
                if ($store == null) {
                    $store = new SmCustomTemporaryResult();
                }

                $store->member_id = $student->id;
                $store->registration_no = $student->registration_no;
                $store->full_name = $student->full_name;
                $store->church_id = Auth::user()->church_id;

                $resultk49 = 0;
                $term_count = 1;
                foreach ($assigned_exam as $key => $exam_term) {
                    $term_result = CustomResultSetting::termResult($exam_term->exam_type_id, $InputClassId, $InputSectionId, $student->id, $eligible_subjects->count());
                    $custom_term = 'term' . $term_count;
                    $custom_gpa = 'gpa' . $term_count;
                    $store->$custom_term  = $exam_term->exam_type_id;
                    $store->$custom_gpa  = number_format((float) $term_result, 2, '.', '');
                    $var = ((float)$term_result *$this->getPercentageFromExam($exam_term->exam_type_id, $key));
                    $resultk49 = $resultk49 + $var;
                    $term_count ++;
                }
                $store->final_result  = number_format((float) $resultk49, 2, '.', '');
                $store->final_grade  = $this->getGradeNameFromGPA(number_format((float) $resultk49, 2, '.', ''));
                // return $store->final_grade;
                if ($store->final_grade != 'NotFind') {
                    $store->save();
                    // continue;
                } else {
                    Toastr::warning('Please setup marks grade', 'Warning');
                    // break;
                    return redirect()->back();
                }
            }

            $customresult = SmCustomTemporaryResult::orderBy('final_result', 'DESC')->where('church_id', Auth::user()->church_id)->get();
            $assign_subjects = SmAssignSubject::where('age_group_id', $class->id)->where('mgender_id', $section->id)->get();
            $custom_result_setup = CustomResultSetting::where('church_year', generalSetting()->session_id)->first();

            if (!empty($custom_result_setup)) {

                return view('backEnd.reports.custom_merit_list_report', compact('customresult', 'iid', 'classes', 'section', 'age_group_name', 'assign_subjects', 'InputClassId',  'InputSectionId', 'custom_result_setup'));
            } else {
                Toastr::warning('Please Complete Custom Setup', 'Warning');
                return redirect()->back();
            }
        } catch (Exception $e) {
            Toastr::error('Operation Failed 1', 'Failed');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Operation Failed 2', 'Failed');
            return redirect()->back();
        } catch (\Exception $e) {

            Toastr::error('Operation Failed 3', 'Failed');
            return redirect()->back();
        }
    }
    public function meritListReportPrint(Request $request, $class, $section)
    {


        try {
            $iid = time();
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            if ($request->method() == 'GET') {
                $input = $request->all();

                $InputClassId = $class;
                $InputSectionId = $section;

                $class          = SmClass::find($InputClassId);
                $section        = SmSection::find($InputSectionId);
                $age_group_name =$class ->age_group_name;



                $result_archive = [];

                $assigned_exam  = SmExam::where('age_group_id', $InputClassId)->where('mgender_id', $InputSectionId)->select('exam_type_id')->DISTINCT()->get();
                $classes        = SmClass::get();
                $eligible_students       = SmStudent::where('age_group_id', $InputClassId)->where('mgender_id', $InputSectionId)->where('church_year_id', getAcademicId())->get();
                $eligible_subjects       = SmAssignSubject::where('age_group_id', $InputClassId)->where('mgender_id', $InputSectionId)->where('church_year_id', getAcademicId())->get();
                $student_yearly_result   = [];
                $result_archive = [];


                $student_result = [];
                foreach ($eligible_students as  $student) {
                    $store = SmCustomTemporaryResult::where('member_id',  $student->id)->first();
                    if ($store == null) {
                        $store = new SmCustomTemporaryResult();
                    }

                    $store->member_id = $student->id;
                    $store->registration_no = $student->registration_no;
                    $store->full_name = $student->full_name;
                    $store->church_id = Auth::user()->church_id;

                    $resultk49 = 0;
                    $term_count = 1;
                    foreach ($assigned_exam as $key => $exam_term) {

                        $term_result = CustomResultSetting::termResult($exam_term->exam_type_id, $InputClassId, $InputSectionId, $student->id, $eligible_subjects->count());
                        $custom_term = 'term' . $term_count;
                        $custom_gpa = 'gpa' . $term_count;
                        $store->$custom_term  = $exam_term->exam_type_id;
                        $store->$custom_gpa  = number_format((float) $term_result, 2, '.', '');

                        $resultk49 = $resultk49 + ($term_result * $this->getPercentageFromExam($exam_term->exam_type_id, $key));



                        $term_count++;
                    }
                    $store->final_result  = number_format((float) $resultk49, 2, '.', '');
                    $store->final_grade  = $this->getGradeNameFromGPA(number_format((float) $resultk49, 2, '.', ''));
                    // return $store->final_grade;
                    $store->church_year_id = getAcademicId();
                    $store->save();
                }

                $customresult = SmCustomTemporaryResult::orderBy('final_result', 'DESC')->where('church_id', Auth::user()->church_id)->get();
                $assign_subjects = SmAssignSubject::where('age_group_id', $class->id)->where('mgender_id', $section->id)->get();

                $system_setting = generalSetting()->session_id;
                $custom_result_setup = CustomResultSetting::where('church_year', $system_setting)->first();
                return view('backEnd.reports.custom_merit_list_report_print', compact('customresult', 'iid', 'classes', 'section', 'age_group_name', 'assign_subjects', 'InputClassId',  'InputSectionId', 'custom_result_setup'));



            }
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function progressCardReportIndex(Request $request)
    {
        try {
            $exams = SmExam::get();
            $classes = SmClass::get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['routes'] = $exams->toArray();
                $data['assign_vehicles'] = $classes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.reports.custom_progress_card_report', compact('exams', 'classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function progressCardReport(Request $request)
    {


        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required',
            'student' => 'required'
        ]);

        $input_class = $input['class'];
        $input_section = $input['section'];
        $input_student = $input['student'];
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $exams = SmExam::get();
            $classes = SmClass::get();
            $class = SmClass::findOrfail($request->class);
            $section = SmClass::findOrfail($request->section);
            $studentDetails = SmStudent::where('sm_students.id', '=', $request->student)
                ->join('sm_academic_years', 'sm_academic_years.id', '=', 'sm_students.session_id')
                ->join('sm_classes', 'sm_classes.id', '=', 'sm_students.age_group_id')
                ->join('sm_sections', 'sm_sections.id', '=', 'sm_students.mgender_id')
                ->first();

            $system_setting = generalSetting()->session_id;
            $custom_result_setup = CustomResultSetting::where('church_year', $system_setting)->first();
            $assign_subjects = SmAssignSubject::where('age_group_id', $class->id)->where('mgender_id', $section->id)->where('sm_assign_subjects.church_year_id', getAcademicId())->join('sm_subjects', 'sm_subjects.id', '=', 'sm_assign_subjects.subject_id')->get();
            $assigned_exam = SmExam::where('age_group_id', $class->id)->where('mgender_id', $section->id)->select('exam_type_id', 'title')->join('sm_exam_types', 'sm_exam_types.id', '=', 'sm_exams.exam_type_id')->DISTINCT()->get();

            if ($assigned_exam->count() != 3) {
                Toastr::error('Result not found for this class, At least you have to complete 3 terms results', 'Failed');
                return redirect()->back();
            }

            return view('backEnd.reports.custom_progress_card_report', compact('exams', 'classes', 'studentDetails', 'assign_subjects', 'assigned_exam', 'custom_result_setup', 'input_section', 'input_class', 'input_student'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function progressCardReportPrint(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required',
            'student' => 'required'
        ]);

        $input_class = $input['class'];
        $input_section = $input['section'];
        $input_student = $input['student'];
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $exams = SmExam::get();
            $classes = SmClass::get();
            $class = SmClass::findOrfail($request->class);
            $section = SmSection::findOrfail($request->section);
            $studentDetails = SmStudent::where('sm_students.id', '=', $request->student)
                ->join('sm_academic_years', 'sm_academic_years.id', '=', 'sm_students.session_id')
                ->join('sm_classes', 'sm_classes.id', '=', 'sm_students.age_group_id')
                ->join('sm_sections', 'sm_sections.id', '=', 'sm_students.mgender_id')
                ->first();

            $system_setting = generalSetting()->session_id;
            $custom_result_setup = CustomResultSetting::where('church_year', $system_setting)->first();
            $assign_subjects = SmAssignSubject::where('age_group_id', $class->id)->where('mgender_id', $section->id)->where('sm_assign_subjects.church_year_id', getAcademicId())->join('sm_subjects', 'sm_subjects.id', '=', 'sm_assign_subjects.subject_id')->get();
            $assigned_exam = SmExam::where('age_group_id', $class->id)->where('mgender_id', $section->id)->select('exam_type_id', 'title')->join('sm_exam_types', 'sm_exam_types.id', '=', 'sm_exams.exam_type_id')->DISTINCT()->get();

            if ($assigned_exam->count() != 3) {
                Toastr::error('Result not found for this class', 'Failed');
                return redirect()->back();
            }

            return view('backEnd.reports.custom_progress_card_print', compact('exams', 'classes', 'studentDetails', 'assign_subjects', 'assigned_exam', 'custom_result_setup', 'input_section', 'input_class', 'input_student', 'class', 'section'));

        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }

        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required',
            'student' => 'required'
        ]);

        $input_class = $input['class'];
        $input_section = $input['section'];
        $input_student = $input['student'];
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $exams = SmExam::get();
            $classes = SmClass::get();
            $class = SmClass::findOrfail($request->class);
            $section = SmSection::findOrfail($request->section);
            $studentDetails = SmStudent::where('sm_students.id', '=', $request->student)
                ->join('sm_academic_years', 'sm_academic_years.id', '=', 'sm_students.session_id')
                ->join('sm_classes', 'sm_classes.id', '=', 'sm_students.age_group_id')
                ->join('sm_sections', 'sm_sections.id', '=', 'sm_students.mgender_id')
                ->first();

            $system_setting =generalSetting()->session_id;
            $custom_result_setup = CustomResultSetting::where('church_year', $system_setting)->first();
            $assign_subjects = SmAssignSubject::where('age_group_id', $class->id)->where('mgender_id', $section->id)->where('sm_assign_subjects.church_year_id', getAcademicId())->join('sm_subjects', 'sm_subjects.id', '=', 'sm_assign_subjects.subject_id')->get();
            $assigned_exam = SmExam::where('age_group_id', $class->id)->where('mgender_id', $section->id)->select('exam_type_id', 'title')->join('sm_exam_types', 'sm_exam_types.id', '=', 'sm_exams.exam_type_id')->DISTINCT()->get();

            if ($assigned_exam->count() != 3) {
                Toastr::error('Result not found for this class', 'Failed');
                return redirect()->back();
            }

            return view('backEnd.reports.custom_progress_card_print', compact('exams', 'classes', 'studentDetails', 'assign_subjects', 'assigned_exam', 'custom_result_setup', 'input_section', 'input_class', 'input_student', 'class', 'section'));


        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentFinalResult(Request $request)
    {

        try {
            $exam = $request->exam_term;
            $class = $request->class;
            $section = $request->section;
            $student = $request->student;

            $first_term_percentage = 'percentage1';
            $second_term_percentage = 'percentage2';
            $third_term_percentage = 'percentage3';

            $first_term_result = CustomResultSetting::getFinalResult($exam, $class, $section, $student, $first_term_percentage);
            $second_term_result = CustomResultSetting::getFinalResult($exam, $class, $section, $student, $second_term_percentage);
            $third_term_result = CustomResultSetting::getFinalResult($exam, $class, $section, $student, $third_term_percentage);

            $final_result = $first_term_result + $second_term_result + $third_term_result;
            return $third_term_result;
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function stepSkipUpdate(Request $request)
    {
        try {
           
            $steps = $request->item;
            ExamStepSkip::where('church_id', auth()->user()->church_id)->delete();
            if($steps) {
               
                foreach($steps as $step) {
                    ExamStepSkip::updateOrCreate([
                        'name'=>$step,
                        'church_id'=>auth()->user()->church_id
                    ]);
                }
            }
            Toastr::success('Operation Successfully', 'Success');
            return redirect()->back();
        } catch (\Throwable $th) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public static function isSkip(string $name)
    {
        $data = ExamStepSkip::where('name', $name)->where('church_id', auth()->user()->church_id)->first();
        if($data) {
            return true;
        }
        return false;
    }
}