<?php

namespace App\Http\Controllers;

use App\SmClass;
use App\SmSection;
use App\SmStudent;
use App\SmExamType;
use App\SmAcademicYear;
use App\SmStudentPromotion;
use App\CustomResultSetting;
use App\Http\Controllers\Admin\StudentInfo\SmStudentAdmissionController;
use Illuminate\Http\Request;
use App\SmTemporaryMeritlist;
use App\Http\Controllers\Controller;
use App\Models\StudentRecord;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Scopes\StatusAcademicSchoolScope;
use App\SmClassSection;
use Illuminate\Validation\ValidationException;

class SmStudentPromoteController extends Controller
{
    //

    public function index()
    {
        try {
            $generalSetting = generalSetting();
            $sessions = SmAcademicYear::where('active_status', 1)->where('church_id', Auth::user()->church_id)->get();
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)->get();
            $exams = SmExamType::where('active_status', 1)->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)->get();


            if ($generalSetting->promotionSetting == 0) {
                return view('backEnd.studentInformation.student_promote_new', compact('sessions', 'classes'));
            } else {
                return view('backEnd.studentInformation.student_promote_with_exam', compact('sessions', 'classes', 'exams'));

            }

        } catch (\Throwable $th) {
            //throw $th;
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentCurrentSearch(Request $request)
    {

        //  return $request->all();
        $request->validate([
            'current_session' => 'required',
            'promote_session' => 'required',
            'current_class' => 'required',
            'current_section' => 'required',
        ]);

        try {
            $member_ids = StudentRecord::when($request->current_session, function ($query) use ($request) {
                $query->where('church_year_id', $request->current_session);
            })
            ->when($request->current_class, function ($query) use ($request) {
                $query->where('age_group_id', $request->current_class);
            })
            ->when($request->current_section, function ($query) use ($request) {
                $query->where('mgender_id', $request->current_section);
            })
            ->where('is_promote', 0)->where('church_id', Auth::user()->church_id)
            ->pluck('member_id')->unique();

            $students = SmStudent::query()->with('class', 'section');
            $students = $students->whereIn('id', $member_ids)->where('active_status', 1)
                ->where('church_id', Auth::user()->church_id)->where('type_of_member', 1)
                ->withOutGlobalScope(StatusAcademicSchoolScope::class)
                ->get();

            $current_session = $request->current_session;
            $current_class = $request->current_class;
            $current_section = $request->current_section;
            $promote_session = $request->promote_session;
            $sessions = SmAcademicYear::where('active_status', 1)
                ->where('church_id', Auth::user()->church_id)
                ->get();
            $currrent_academic_class = SmClass::where('active_status', 1)
                ->where('church_year_id', $request->current_session)
                ->withOutGlobalScope(StatusAcademicSchoolScope::class)
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $classes = SmClass::with('classSection')->where('active_status', 1)
                ->where('church_year_id', $request->promote_session)
                ->withOutGlobalScope(StatusAcademicSchoolScope::class)
                ->where('church_id', Auth::user()->church_id)
                ->get();


            // return $classes;
            if (empty($classes)) {
                Toastr::error('No Class found For Next Academic Year', 'Failed');
                return redirect('student-promote');
            }

            $next_class = $classes->except($current_class)->first();

            $next_sections = collect();
            if($next_class){
                $next_sections = SmClassSection::with('sectionWithoutGlobal')->where('age_group_id', '=', $next_class->id)->where('church_year_id', $request->promote_session)
                    ->where('church_id', Auth::user()->church_id)->withoutGlobalScope(StatusAcademicSchoolScope::class)->get();
            }

            $search_current_class = SmClass::withoutGlobalScope(StatusAcademicSchoolScope::class)->findOrFail($request->current_class);
            $search_current_section = SmSection::withoutGlobalScope(StatusAcademicSchoolScope::class)->find($request->current_section);
            $search_current_church_year = SmAcademicYear::find($request->current_session);
            $search_promote_church_year = SmAcademicYear::find($request->promote_session);
            $sections = $search_current_class ? $search_current_class->classSection : [];

            // return $search_info;
            if (empty($students)) {
                Toastr::error('No result found', 'Failed');
                return redirect('student-promote');
            }

            return view('backEnd.studentInformation.student_promote_new', compact('currrent_academic_class', 'next_class', 'sessions', 'classes', 'students', 'current_session', 'current_class', 'current_section', 'promote_session', 'search_current_class', 'search_current_section', 'search_current_church_year', 'search_promote_church_year', 'sections', 'next_sections'));

        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function rollCheck(Request $request)
    {

        $exist_roll_number = SmStudent::where('age_group_id', $request->age_group_id)
            ->where('mgender_id', $request->mgender_id)
            ->where('roll_no', $request->promote_roll_number)
            ->where('church_year_id', getAcademicId())
            ->where('church_id', Auth::user()->church_id)
            ->count();

        return response()->json($exist_roll_number);
    }

    public function promote(Request $request)
    {

        //   return $request->all();
        $request->validate([
            'promote_session' => 'required',
            'promote.*.class' => 'required',
            'promote.*.section' => 'required_with:promote.*.student',
            'promote.*.roll_number' => 'sometimes|nullable|integer',
        ]);

        // $validator=Validator::make()

        try {
            //code...
            if (empty($request->promote)) {
                Toastr::error('Please Select Student', 'Failed');
                return back();
            }
            foreach ($request->promote as $member_id => $student_data) {
                if (!gv($student_data, 'student') || !gv($student_data, 'class') || !gv($student_data, 'section')) {
                    continue;
                }
              
                $roll_number = gv($student_data, 'roll_number');
                $student_record = StudentRecord::where('member_id', $member_id)
                    ->where('age_group_id', gv($student_data, 'class'))
                    ->where('mgender_id', gv($student_data, 'section'))
                    ->where('church_year_id', $request->promote_session)
                    ->where('church_id', Auth::user()->church_id)
                    ->where('is_promote', 0)
                    ->first();

                $exit_record = $student_record;
                if ($roll_number) {
                    $exist_roll_number = $exit_record;

                    if ($exist_roll_number) {
                        throw ValidationException::withMessages(['promote.' . $member_id . '.roll_number' => 'Roll no already exist']);
                    }
                } else {
                    $roll_number = StudentRecord::where('age_group_id', (int)gv($student_data, 'class'))
                            ->where('mgender_id', (int)gv($student_data, 'section'))->where('church_year_id', $request->promote_session)
                            ->where('church_id', Auth::user()->church_id)->max('id') + 1;
                }        

                $current_student = SmStudent::where('id', $member_id)->first();
                $pre_record = StudentRecord::where('member_id', $member_id)
                    ->where('age_group_id', $request->pre_class)
                    ->where('mgender_id', $request->pre_section)
                    ->where('church_year_id', $request->current_session)
                    ->where('church_id', Auth::user()->church_id)
                    ->first();


                if (!$exit_record) {

                    $student_promote = new SmStudentPromotion();
                    $student_promote->member_id = $member_id;

                    $student_promote->previous_age_group_id = $request->pre_class;
                    $student_promote->current_age_group_id = gv($student_data, 'class');

                    $student_promote->previous_session_id = $request->current_session;
                    $student_promote->current_session_id = $request->promote_session;

                    $student_promote->previous_mgender_id = $request->pre_section;
                    $student_promote->current_mgender_id = gv($student_data, 'section');

                    $student_promote->admission_number = $current_student->registration_no;
                    $student_promote->student_info = $current_student->toJson();
                    $student_promote->merit_student_info = $current_student->toJson();
                    $student_promote->previous_roll_number = $pre_record->roll_no;
                    $student_promote->current_roll_number = $roll_number;
                    $student_promote->church_year_id = $request->promote_session;
                    $student_promote->result_status = gv($student_data, 'result') ? gv($student_data, 'result') : 'F';
                    $student_promote->save();
                 

                    $insertStudentRecord = new SmStudentAdmissionController;
                    $result = $insertStudentRecord->insertStudentRecord($request->merge([
                            'member_id'=>gv($student_data, 'student'),
                            'roll_number'=>$roll_number,
                            'class'=>gv($student_data, 'class'),
                            'section'=>gv($student_data, 'section'),
                            'session'=>$request->promote_session,
                            
                        ]));

                        $groups = \Modules\Chat\Entities\Group::where([
                            'age_group_id' => $request->pre_class,
                            'mgender_id' => $request->pre_section,
                            'church_year_id' => $request->current_session,
                            'church_id' => auth()->user()->church_id
                            ])->get();
                        if($current_student){
                            $user = $current_student->user;
                            foreach($groups as $group){
                                removeGroupUser($group, $user->id);
                            }
                        }
                   
                        $pre_record->is_promote=1;
                        $pre_record->save();
                      

                }

                $compact['user_email'] = $pre_record->studentDetail->email;
                @send_sms($pre_record->studentDetail->mobile, 'student_promote', $compact);
            }

            Toastr::success('Operation successful', 'Success');
            return back();

        } catch (\Throwable $th) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentSearchWithExam(Request $request)
    {

        // return $request->all();
        $request->validate([
            'current_session' => 'required',
            'promote_session' => 'required',
            'current_class' => 'required',
            'current_section' => 'sometimes',
            'exam' => 'required',
        ]);

        try {

            $meritListSettings = CustomResultSetting::first('merit_list_setting')->merit_list_setting;

            // $merit_list = $this->meritList($request);

            $member_ids = StudentRecord::query()->with('class', 'section');

            if ($request->current_session) {
                $member_ids->where('church_year_id', $request->current_session);
            }
            if ($request->current_class) {
                $member_ids->where('age_group_id', '=', $request->current_class);
            }
            if ($request->current_section) {
                $member_ids->where('mgender_id', $request->current_section);
            }
            $member_ids = $member_ids->where('is_promote', 0)
                ->orderBy('roll_no', 'ASC')
                ->where('church_id', Auth::user()->church_id)
                ->get()->pluck('member_id')->toArray();

            $students = SmTemporaryMeritlist::query()->with('class', 'studentinfo', 'section');


            if ($request->current_session) {
                $students->where('church_year_id', $request->current_session);
            }
            if ($request->current_class) {
                $students->where('age_group_id', $request->current_class);
            }
            if ($request->current_section) {
                $students->Where('mgender_id', $request->current_section);
            }
            if ($meritListSettings == "total_grade") {
                $students->orderBy('gpa_point', 'DESC');
            } else {
                $students->orderBy('total_marks', 'DESC');
            }
            $students = $students->whereIn('member_id', $member_ids)
                ->where('church_id', Auth::user()->church_id)
                ->get();


            if (count($students) == 0) {
                Toastr::error('Please Check Your Merit List First', 'Failed');
                return redirect('student-promote');
            }

            $current_session = $request->current_session;
            $current_class = $request->current_class;
            $current_section = $request->current_section;
            $promote_session = $request->promote_session;
            $exam_id = $request->exam;
            $sessions = SmAcademicYear::where('active_status', 1)
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $classes = SmClass::with('classSection')->where('active_status', 1)
                ->where('church_year_id', $request->promote_session)
                ->where('church_id', Auth::user()->church_id)
                ->get();

            // return $classes;
            if (empty($classes)) {
                Toastr::error('No Class found For Next Church Year', 'Failed');
                return redirect('student-promote');
            }

            $next_class = $classes->except($current_class)->first();
            $search_current_class = SmClass::findOrFail($request->current_class);
            $search_current_section = SmSection::find($request->current_section);
            $search_current_church_year = SmAcademicYear::find($request->current_session);
            $search_promote_church_year = SmAcademicYear::find($request->promote_session);
            $search_exams = SmExamType::find($request->exam)->title;
            $sections = $search_current_class ? $search_current_class->classSection : [];
            $exams = SmExamType::where('active_status', 1)->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)->get();

            // return $search_info;
            if (empty($students)) {
                Toastr::error('No result found', 'Failed');
                return redirect('student-promote');
            }
            return view('backEnd.studentInformation.student_promote_with_exam', compact('next_class', 'sessions', 'classes', 'students', 'current_session', 'current_class', 'current_section', 'promote_session', 'search_current_class', 'search_current_section', 'search_current_church_year', 'search_promote_church_year', 'sections', 'exams', 'exam_id', 'search_exams'));

        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

}
