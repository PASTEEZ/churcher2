<?php

namespace App\Http\Controllers\Admin\Examination;

use App\SmExam;
use App\SmClass;
use App\SmStaff;
use App\SmSection;
use App\SmSubject;
use App\YearCheck;
use App\SmExamType;
use App\SmClassRoom;
use App\SmExamSetup;
use App\SmMarkStore;
use App\ApiBaseMethod;
use App\SmResultStore;
use App\SmClassSection;
use App\SmExamSchedule;
use App\SmAssignSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Admin\Examination\SmExamSetupRequest;
use App\SmClassTeacher;
use Modules\University\Entities\UnAssignSubject;
use Modules\University\Entities\UnSemesterLabelAssignSection;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;


class SmExamController extends Controller
{
    public function __construct()
	{
        $this->middleware('PM');
        // User::checkAuth();
	}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
                $exam_query = SmExam::query();
                $exam_query->with('class', 'section', 'subject', 'GetExamTitle', 'markDistributions')
                ->where('church_id', Auth::user()->church_id);
            if (teacherAccess()) {
                $teacher_info=SmStaff::where('user_id', Auth::user()->id)->first(); 
                $classes = $teacher_info->classes;           
                if(moduleStatusCheck('University')){
                    $subject_ids = UnAssignSubject::where('un_teacher_id',$teacher_info->id)->where('church_id',auth()->user()->church_id)->get(['un_subject_id'])->toArray();
                    $exams = $exam_query->whereIn('un_subject_id',$subject_ids)->get();
                }else{
                    $teacher_class = SmClassTeacher::where('teacher_id',$teacher_info->id)->with('teacherClass')->get();
                    if($teacher_class){
                        $age_group_ids = [] ;
                        $mgender_ids = [];
                        foreach($teacher_class as $class){
                            $age_group_ids[] = $class->teacherClass->age_group_id; 
                            $mgender_ids[] =  $class->teacherClass->mgender_id;
                        }
                        $exams = $exam_query->whereIn('age_group_id', $age_group_ids )->whereIn('mgender_id', $mgender_ids)->get();
                    }else{
                        $exams = collect();
                    }
                    $subjects = SmAssignSubject::where('teacher_id',$teacher_info->id)->where('church_id',auth()->user()->church_id)->get(['subject_id','age_group_id','mgender_id']);
                    if($subjects){
                        $c_id = [];
                        $se_id = [];
                        $su_id = [];
                        foreach( $subjects as $subject){
                            $c_id[] = $subject->age_group_id;
                            $se_id[] = $subject->mgender_id;
                            $su_id[] = $subject->subject_id;
                        }
                        $subjectAssignedExams  = SmExam::whereIn('age_group_id',$c_id)->whereIn('mgender_id',$se_id)->whereIn('subject_id',$su_id)->get();
                        $exams= $exams->merge($subjectAssignedExams)->unique('id');
                    }   
                }
            }
            else {
                $classes = SmClass::get();
                $exams = $exam_query->get();
            }
           
            $exams_types = SmExamType::where('church_id', Auth::user()->church_id)
            ->where('active_status', 1)->get();
            
            $subjects =  SmSubject::get();
            $sections = SmSection::get();
            $teachers = SmStaff::where('role_id', 4)->where('active_status', 1)
            ->where('church_id', Auth::user()->church_id)
            ->get(['id', 'user_id', 'full_name']);
            $rooms = SmClassRoom::where('active_status', 1)
            ->where('church_id',Auth::user()->church_id)
            ->get();
            return view('backEnd.examination.exam', compact('exams', 'classes', 'subjects', 'exams_types', 'sections','teachers','rooms'));
        } catch (\Exception $e) {
         
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function exam_setup($id)
    {
        try {
            $exams = SmExam::get();

            $exams_types = SmExamType::get();

             if (teacherAccess()) {
                $teacher_info=SmStaff::where('user_id', Auth::user()->id)->first();
                $classes= $teacher_info->classes;
            } else {
                $classes = SmClass::get();
            }
            $subjects = SmSubject::get();
            $sections = SmSection::get();
            $selected_exam_type_id = $id;
                
            $teachers = SmStaff::where('role_id', 4)->where('active_status', 1)
            ->where('church_id', Auth::user()->church_id)
            ->get(['id', 'user_id', 'full_name']);
            $rooms = SmClassRoom::where('active_status', 1)
            ->where('church_id',Auth::user()->church_id)
            ->get();
            return view('backEnd.examination.exam', compact('exams', 'classes', 'subjects', 'exams_types', 'sections', 'selected_exam_type_id','teachers','rooms'));
        } catch (\Exception $e) {
           
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function exam_reset()
    {
        try {
            $exams = SmExam::get();
            SmExam::query()->truncate();
            $exams_types = SmExamType::get();
            SmExamType::query()->truncate();
            $exam_mark_stores = SmMarkStore::get();
            SmMarkStore::query()->truncate();
            $exam_results_stores = SmResultStore::where('church_year_id', getAcademicId())
                                ->where('church_id', Auth::user()->church_id)
                                ->get();
            SmResultStore::query()->truncate();
            SmExamSetup::query()->truncate();
            if (teacherAccess()) {
                $teacher_info=SmStaff::where('user_id', Auth::user()->id)->first();
                $classes= $teacher_info->classes;
            } else {
                $classes = SmClass::get();
            }
            $subjects = SmSubject::get();

            $sections = SmSection::get();
            return view('backEnd.examination.exam', compact('exams', 'classes', 'subjects', 'exams_types', 'sections'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

 //SmExamSetupRequest
    public function store(Request $request)
    { 
        $input = $request->all();
        if($request->exam_system == "single"){
            if(moduleStatusCheck('University')){
                $validator = Validator::make($input, [
                    'exams_type' => 'required',
                    'un_semester_label_id' => 'required',
                    'un_subject_id' => 'required',
                    'date' => 'required',
                    'teacher_id' => 'required',
                    'start_time' => 'required',
                    'end_time' => 'required',
                    'room' => 'required',
                ]);
            }else{
                $validator = Validator::make($input, [
                    'exams_type' => 'required',
                    'age_group_id' => 'required',
                    'mgender_ids' => 'required',
                    'subject_id' => 'required',
                    'date' => 'required',
                    'teacher_id' => 'required',
                    'start_time' => 'required',
                    'end_time' => 'required',
                    'room' => 'required',
                ]);
                
            }

        }else{
            $validator = Validator::make($input, [
                'exams_types' => 'required',
                'exam_marks' => 'required|numeric|min:1',
                'subjects_ids' => 'required',
            ]);

        }
    
        if ($validator->fails()) {
            return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
        }
       
        try{
            if($request->exam_system == "single"){
                if (moduleStatusCheck('University')) {
                    $sec = $request->un_mgender_id;
                    if($request->un_mgender_id){
                        $all_sections = UnSemesterLabelAssignSection::where('un_semester_label_id', $request->un_semester_label_id)->where('un_mgender_id',$sec)->get();
                    }else{
                        $all_sections = UnSemesterLabelAssignSection::where('un_semester_label_id', $request->un_semester_label_id)->get();
                    }

                    foreach($all_sections as $section){
                        $checkExitExam = SmExam::where([
                            'exam_type_id' => $request->exams_type,
                            'un_session_id' => $request->un_session_id,
                            'un_faculty_id' => $request->un_faculty_id,
                            'un_department_id' => $request->un_department_id,
                            'un_church_year_id' => $request->un_church_year_id,
                            'un_semester_id' => $request->un_semester_id,
                            'un_semester_label_id' => $request->un_semester_label_id,
                            'un_mgender_id' => $section->un_mgender_id,
                            'un_subject_id' => $request->un_subject_id
                        ])->first();

                        if($checkExitExam) {
                            continue;
                        }
                        $exam = new SmExam();
                        $exam->exam_type_id = $request->exams_type;
                        $exam->un_session_id = $request->un_session_id;
                        $exam->un_faculty_id = $request->un_faculty_id;
                        $exam->un_department_id = $request->un_department_id;
                        $exam->un_church_year_id = $request->un_church_year_id;
                        $exam->un_semester_id = $request->un_semester_id;
                        $exam->un_semester_label_id = $request->un_semester_label_id;
                        $exam->un_mgender_id = $section->un_mgender_id;
                        $exam->un_subject_id = $request->un_subject_id;
                        $exam->exam_mark = $request->exam_marks;
                        $exam->pass_mark = $request->pass_mark;
                        $exam->created_by=auth()->user()->id;
                        $exam->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                        $exam->church_id = Auth::user()->church_id;
                        $exam->save();
                        $exam->toArray();
                       

                        $length = count($request->exam_title);
                        for ($i = 0; $i < $length; $i++) {
                            $ex_title = $request->exam_title[$i];
                            $ex_mark = $request->exam_mark[$i];
                            $newSetupExam = new SmExamSetup();
                            $newSetupExam->exam_id = $exam->id;
                            $newSetupExam->un_session_id = $request->un_session_id;
                            $newSetupExam->un_faculty_id = $request->un_faculty_id;
                            $newSetupExam->un_department_id = $request->un_department_id;
                            $newSetupExam->un_church_year_id = $request->un_church_year_id;
                            $newSetupExam->un_semester_id = $request->un_semester_id;
                            $newSetupExam->un_semester_label_id = $request->un_semester_label_id;
                            $newSetupExam->un_mgender_id = $section->un_mgender_id;
                            $newSetupExam->un_subject_id = $request->un_subject_id;
                            $newSetupExam->exam_term_id = $request->exams_type;
                            $newSetupExam->exam_title = $ex_title;
                            $newSetupExam->exam_mark = $ex_mark;
                            $newSetupExam->created_by=auth()->user()->id;
                            $newSetupExam->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                            $newSetupExam->church_id = Auth::user()->church_id;
                            $result = $newSetupExam->save();

                        }
    
                        $is_exist= SmExamSchedule::where(
                            [
                            'exam_term_id'=>  $exam->exam_type_id,
                            'exam_id'=>  $exam->id,
                            'un_subject_id'=>$exam->un_subject_id ,
                            'date'=> date('Y-m-d', strtotime($request->date)),
                            'start_time' =>  date('H:i:s', strtotime($request->start_time)),
                            'end_time' => date('H:i:s', strtotime($request->end_time)),
                            'room_id' => $request->room,
                            'un_session_id' => $request->un_session_id,
                            'un_faculty_id' => $request->un_faculty_id,
                            'un_department_id' => $request->un_department_id,
                            'un_church_year_id' => $request->un_church_year_id,
                            'un_semester_id' => $request->un_semester_id,
                            'un_semester_label_id' => $request->un_semester_label_id,
                            'un_mgender_id' => $section->un_mgender_id,
                            ]
                        )->where('church_id', Auth::user()->church_id)->first();
                      
                        if($is_exist){
                            Toastr::error('Exam Shedule Already Exist', 'Failed');
                            return redirect()->back();
                        }else{
                            $exam_routine = new SmExamSchedule();
                            $exam_routine->exam_id = $exam->id;
                            $exam_routine->exam_term_id = $exam->exam_type_id;
                            $exam_routine->un_session_id = $request->un_session_id;
                            $exam_routine->un_faculty_id = $request->un_faculty_id;
                            $exam_routine->un_department_id = $request->un_department_id;
                            $exam_routine->un_church_year_id = $request->un_church_year_id;
                            $exam_routine->un_semester_id = $request->un_semester_id;
                            $exam_routine->un_semester_label_id = $request->un_semester_label_id;
                            $exam_routine->un_mgender_id = $section->un_mgender_id;
                            $exam_routine->un_subject_id = $request->un_subject_id;
                            $exam_routine->teacher_id = $request->teacher_id;
                            $exam_routine->date = date('Y-m-d', strtotime($request->date));
                            $exam_routine->start_time = date('H:i:s', strtotime($request->start_time));
                            $exam_routine->end_time = date('H:i:s', strtotime($request->end_time));
                            $exam_routine->room_id = $request->room;
                            $exam_routine->church_id = Auth::user()->church_id;
                            $exam_routine->save();
                           
                        }
                    }

                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();

                }
                $sections = $request->mgender_ids;
                foreach($sections as $section){
                    $checkExitExam = SmExam::where([
                        'exam_type_id' => $request->exams_type,
                        'age_group_id' => $request->age_group_id,
                        'mgender_id' => $section,
                        'subject_id' => $request->subject_id                       
                    ])->first();
                    
                    if($checkExitExam) {
                        continue;
                    }
                    $exam = new SmExam();
                    $exam->exam_type_id = $request->exams_type;
                    $exam->age_group_id = $request->age_group_id;
                    $exam->mgender_id = $section;
                    $exam->subject_id = $request->subject_id;
                    $exam->exam_mark = $request->exam_marks;
                    $exam->pass_mark = $request->pass_mark;
                    $exam->created_by=auth()->user()->id;
                    $exam->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                    $exam->church_id = Auth::user()->church_id;
                    $exam->church_year_id = getAcademicId();
                    $exam->save();
                    $exam->toArray();
                    $length = count($request->exam_title);
                    for ($i = 0; $i < $length; $i++) {
                        $ex_title = $request->exam_title[$i];
                        $ex_mark = $request->exam_mark[$i];
                        $newSetupExam = new SmExamSetup();
                        $newSetupExam->exam_id = $exam->id;
                        $newSetupExam->age_group_id =$request->age_group_id;
                        $newSetupExam->mgender_id = $section;
                        $newSetupExam->subject_id = $request->subject_id;
                        $newSetupExam->exam_term_id = $request->exams_type;
                        $newSetupExam->exam_title = $ex_title;
                        $newSetupExam->exam_mark = $ex_mark;
                        $newSetupExam->created_by=auth()->user()->id;
                        $newSetupExam->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                        $newSetupExam->church_id = Auth::user()->church_id;
                        $newSetupExam->church_year_id = getAcademicId();
                        $result = $newSetupExam->save();
                    }

                    $is_exist= SmExamSchedule::where(
                        [
                            'exam_term_id'=>  $exam->exam_type_id,
                            'exam_id'=>  $exam->id,
                            'subject_id'=>$request->subject_id,
                            'date'=> date('Y-m-d', strtotime($request->date)),
                            'start_time' =>  date('H:i:s', strtotime($request->start_time)),
                            'end_time' => date('H:i:s', strtotime($request->end_time)),
                            'room_id' => $request->room,
                            'age_group_id' => $exam->age_group_id,
                            'mgender_id' =>$exam->mgender_id
                        ]
                    )->where('church_id', Auth::user()->church_id)->first();
                    if($is_exist){
                        Toastr::error('Exam Shedule Already Exist', 'Failed');
                        return redirect()->back();
                    }else{
                        $exam_routine = new SmExamSchedule();
                        $exam_routine->exam_id = $exam->id;
                        $exam_routine->exam_term_id = $exam->exam_type_id;
                        $exam_routine->age_group_id = $exam->age_group_id;
                        $exam_routine->mgender_id = $exam->mgender_id;
                        $exam_routine->subject_id =  $exam->subject_id;
                        $exam_routine->teacher_id = $request->teacher_id;
                        $exam_routine->date = date('Y-m-d', strtotime($request->date));
                        $exam_routine->start_time = date('H:i:s', strtotime($request->start_time));
                        $exam_routine->end_time = date('H:i:s', strtotime($request->end_time));
                        $exam_routine->room_id = $request->room;
                        $exam_routine->church_id = Auth::user()->church_id;
                        $exam_routine->church_year_id = getAcademicId();
                        $exam_routine->save();
                    }
                }
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            }
        }
        catch(\Exception $e){
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }

        try {
            $sections = SmClassSection::where('age_group_id', $request->age_group_ids)->get();
            if (moduleStatusCheck('University')) {
                foreach ($request->exams_types as $exam_type_id) {
                    foreach ($request->subjects_ids as $subject_id) {
                        $checkExitExam = SmExam::where([
                            'exam_type_id' => $request->exams_type,
                            'un_session_id' => $request->un_session_id,
                            'un_faculty_id' => $request->un_faculty_id,
                            'un_department_id' => $request->un_department_id,
                            'un_church_year_id' => $request->un_church_year_id,
                            'un_semester_id' => $request->un_semester_id,
                            'un_semester_label_id' => $request->un_semester_label_id,
                            'un_mgender_id' => $request->un_mgender_id,
                            'un_subject_id' => $subject_id
                        ])->first();

                        if($checkExitExam) {
                            continue;
                        }
                        $exam = new SmExam();
                        $exam->exam_type_id = $exam_type_id;
                        $common = App::make(UnCommonRepositoryInterface::class);
                        $common->storeUniversityData($exam, $request);
                        $exam->un_subject_id = $subject_id;
                        $exam->exam_mark = $request->exam_marks;
                        $exam->created_by=auth()->user()->id;
                        $exam->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                        $exam->church_id = auth()->user()->church_id;
                        // $exam->church_year_id = getAcademicId();
                        $exam->save();
                        $exam->toArray();
                        
                        $length = count($request->exam_title);
                        for ($i = 0; $i < $length; $i++) {
                            $ex_title = $request->exam_title[$i];
                            $ex_mark = $request->exam_mark[$i];
                            $newSetupExam = new SmExamSetup();
                            $newSetupExam->exam_id = $exam->id;

                            $common = App::make(UnCommonRepositoryInterface::class);
                            $common->storeUniversityData($newSetupExam, $request);

                            $newSetupExam->un_subject_id = $subject_id;
                            $newSetupExam->exam_term_id = $exam_type_id;
                            $newSetupExam->exam_title = $ex_title;
                            $newSetupExam->exam_mark = $ex_mark;
                            $newSetupExam->created_by = auth()->user()->id;
                            $newSetupExam->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                            $newSetupExam->church_id = auth()->user()->church_id;
                            // $newSetupExam->church_year_id = getAcademicId();
                            $result = $newSetupExam->save();
                        }
                    }
                }
                DB::commit();
            }else{
                    foreach ($request->exams_types as $exam_type_id) {
                        foreach ($sections as $section) {
                            $subject_for_sections = SmAssignSubject::where('age_group_id', $request->age_group_ids)
                                                    ->where('mgender_id', $section->mgender_id)
                                                    ->get();

                            $eligible_subjects = [];
                            foreach ($subject_for_sections as $subject_for_section) {
                                $eligible_subjects[] = $subject_for_section->subject_id;
                            }

                            foreach ($request->subjects_ids as $subject_id) {
                                if (in_array($subject_id, $eligible_subjects)) {
                                    $checkExitExam = SmExam::where([
                                        'exam_type_id' => $request->exams_type,
                                        'age_group_id' => $request->age_group_ids,
                                        'mgender_id' => $section->mgender_id,
                                        'subject_id' => $request->subject_id                       
                                    ])->first();
                                    
                                    if($checkExitExam) {
                                        continue;
                                    }
                                    $exam = new SmExam();
                                    $exam->exam_type_id = $exam_type_id;
                                    $exam->age_group_id = $request->age_group_ids;
                                    $exam->mgender_id = $section->mgender_id;
                                    $exam->subject_id = $subject_id;
                                    $exam->exam_mark = $request->exam_marks;
                                    $exam->created_by=auth()->user()->id;
                                    $exam->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                                    $exam->church_id = Auth::user()->church_id;
                                    $exam->church_year_id = getAcademicId();
                                    $exam->save();
                                    $exam->toArray();
                                
                                    $length = count($request->exam_title);
                                    for ($i = 0; $i < $length; $i++) {
                                        $ex_title = $request->exam_title[$i];
                                        $ex_mark = $request->exam_mark[$i];
                                        $newSetupExam = new SmExamSetup();
                                        $newSetupExam->exam_id = $exam->id;
                                        $newSetupExam->age_group_id = $request->age_group_ids;
                                        $newSetupExam->mgender_id = $section->mgender_id;
                                        $newSetupExam->subject_id = $subject_id;
                                        $newSetupExam->exam_term_id = $exam_type_id;
                                        $newSetupExam->exam_title = $ex_title;
                                        $newSetupExam->exam_mark = $ex_mark;
                                        $newSetupExam->created_by=auth()->user()->id;
                                        $newSetupExam->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                                        $newSetupExam->church_id = Auth::user()->church_id;
                                        $newSetupExam->church_year_id = getAcademicId();
                                        $result = $newSetupExam->save();
                                    }
                                }
                            }
                        }
                    }
                    // DB::commit();
            }
            
            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function show($id)
    {
        try {
            $data = [];
            $exams_types = SmExamType::get();
            $exam = SmExam::find($id);
            if (teacherAccess()) {
                $teacher_info=SmStaff::where('user_id',Auth::user()->id)->first();
                $classes= $teacher_info->classes;
            } else {
                $classes = SmClass::get();
            }
            $subjects = SmAssignSubject::where('age_group_id', $exam->age_group_id)->where('mgender_id', $exam->mgender_id)->get();
            $sections = SmClassSection::where('age_group_id', $exam->age_group_id)->get();
            $exams = SmExam::get();

            if (moduleStatusCheck('University')) {
                $interface = App::make(UnCommonRepositoryInterface::class);
                $data = $interface->getCommonData($exam);
            }

            return view('backEnd.examination.examEdit', compact('exam', 'exams', 'classes', 'subjects', 'sections', 'exams_types'))->with($data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function update(Request $request, $id)
    {
      
        DB::beginTransaction();
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $exam = SmExam::find($id);
            $exam->exam_mark = $request->exam_marks;
            $exam->pass_mark = $request->pass_mark;
            $exam->updated_by=auth()->user()->id;
            $exam->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
            $exam->save();
            SmExamSetup::where('exam_id', $id)->delete();
            $length = count($request->exam_title);
            for ($i = 0; $i < $length; $i++) {
                $ex_title = $request->exam_title[$i];
                $ex_mark = $request->exam_mark[$i];
                $newSetupExam = new SmExamSetup();
                $newSetupExam->exam_term_id =$exam->exam_type_id;
                $newSetupExam->age_group_id = $exam->age_group_id;
                $newSetupExam->mgender_id = $exam->mgender_id;
                $newSetupExam->subject_id = $exam->subject_id;
                $newSetupExam->exam_id = $exam->id;
                $newSetupExam->exam_title = $ex_title;
                $newSetupExam->exam_mark = $ex_mark;
                $newSetupExam->updated_by=auth()->user()->id;
                $newSetupExam->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                $newSetupExam->church_id = Auth::user()->church_id;
                $newSetupExam->church_year_id = getAcademicId();
                $newSetupExam->save();
            } //end loop exam setup loop
            DB::commit();
            Toastr::success('Operation successful', 'Success');
            return redirect('exam');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function examSetup($id)
    {
        try {
            $exam = SmExam::find($id);
            $exams = SmExam::get();
                if (teacherAccess()) {
                $teacher_info=SmStaff::where('user_id',Auth::user()->id)->first();
                $classes= $teacher_info->classes;
            } else {
                $classes = SmClass::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id',Auth::user()->church_id)
                ->get();
            } 
            $subjects = SmSubject::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $sections = SmSection::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            return view('backEnd.examination.exam_setup', compact('exam', 'exams', 'classes', 'subjects', 'sections'));
        } catch (\Exception $e) {
          
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function examSetupStore(Request $request)
    {
        try {
            $age_group_id = $request->class;
            $mgender_id = $request->section;
            $subject_id = $request->subject;
            $exam_term_id = $request->exam_term_id;

            $total_exam_mark = $request->total_exam_mark;
            $totalMark = $request->totalMark;

            if ($total_exam_mark == $totalMark) {
                $length = count($request->exam_title);
                for ($i = 0; $i < $length; $i++) {
                    $ex_title = $request->exam_title[$i];
                    $ex_mark = $request->exam_mark[$i];

                    $newSetupExam = new SmExamSetup();
                    $newSetupExam->age_group_id = $age_group_id;
                    $newSetupExam->mgender_id = $mgender_id;
                    $newSetupExam->subject_id = $subject_id;
                    $newSetupExam->exam_term_id = $exam_term_id;
                    $newSetupExam->exam_title = $ex_title;
                    $newSetupExam->exam_mark = $ex_mark;
                    $newSetupExam->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                    $newSetupExam->church_id = Auth::user()->church_id;
                    $newSetupExam->church_year_id = getAcademicId();
                    $result = $newSetupExam->save();
                    if ($result) {
                        Toastr::success('Operation successful', 'Success');
                        return redirect('exam');
                    } else {
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
                }
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                SmExamSetup::where('exam_id', $id)->delete();
                $exam = SmExam::find($id);
                $is_exist= SmExamSchedule::where('exam_id',$exam->id)->where('church_id', Auth::user()->church_id)->first();
                if($is_exist){
                    $is_exist->delete();
                }
                $exam->delete();
                DB::commit();
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            } catch (\Illuminate\Database\QueryException $e) {
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function getClassSubjects(Request $request)
    {
        try {
            $subjects = SmAssignSubject::where('age_group_id', $request->id)
            ->where('church_year_id', getAcademicId())
            ->where('church_id', Auth::user()->church_id)
            ->get();

            $subjects = $subjects->groupBy('subject_id');

            $assinged_subjects = [];
            foreach ($subjects as $key => $subject) {
                $assinged_subjects[] = SmSubject::find($key);
            }
            return response()->json($assinged_subjects);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }


    public function subjectAssignCheck(Request $request)
    {
        try {
            $exam = [];
            $assigned_subjects = [];
            foreach ($request->exam_types as $exam_type) {
                $exam = SmExam::where('exam_type_id', $exam_type)->where('age_group_id', $request->age_group_id)->where('subject_id', $request->id)->first();

                if ($exam != "") {
                    $exam_title = SmExamType::find($exam_type);

                    $assigned_subjects[] = $exam_title->title;
                }
            }
            return response()->json($assigned_subjects);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

    public function examView(Request $request){   
             
        $input = $request->only(['code']);
        $exams_types = SmExamType::where('church_id', Auth::user()->church_id)
        ->where('active_status', 1)->get();
        if (teacherAccess()) {
            $teacher_info=SmStaff::where('user_id', Auth::user()->id)->first();
            $classes= $teacher_info->classes;
        } else {
            $classes = SmClass::get();
        }
        $teachers = SmStaff::where('role_id', 4)->where('active_status', 1)
        ->where('church_id', Auth::user()->church_id)
        ->get(['id', 'user_id', 'full_name']);
        $rooms = SmClassRoom::where('active_status', 1)
        ->where('church_id',Auth::user()->church_id)
        ->get();
        if($input['code'] == "single"){
            $view = "backEnd.examination.exam_setup.single_exam_setup";
        }elseif($input['code'] == "multi"){
            $view = "backEnd.examination.exam_setup.multi_exam_setup";
        }
        $html = view($view,compact('exams_types','classes','teachers','rooms'))->render();

        return response()->json([
            'status' => true,
            'html' => $html,
        ]);
    }

    public function customMarksheetReport()
    { 
        try{
            $exams = SmExamType::get();
            $classes = SmClass::get();
            return view('backEnd.examination.report.marksheetReport', compact('exams','classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

}