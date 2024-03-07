<?php

namespace App\Http\Controllers\Api;

use App\SmClass;
use App\SmStaff;
use App\SmSection;
use App\SmStudent;
use App\SmSubject;
use App\SmExamType;
use App\SmClassRoom;
use App\SmExamSetup;
use App\ApiBaseMethod;
use App\SmExamSchedule;
use App\SmAssignSubject;
use App\SmAcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\StudentRecord;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ApiSmExamRoutineController extends Controller
{
    public function examRoutine()
    {
        try {
            $church_id = auth()->user()->church_id;
            $exam_types = SmExamType::where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)->get();
            if (teacherAccess()) {
                $teacher_info = SmStaff::where('user_id', $church_id)->first();
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
            return response()->json(compact('classes', 'exam_types'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function examScheduleSearch(Request $request)
    {
        // return $request->all();
        $request->validate([
            'exam_type' => 'required',
            'class' => 'required',
            'section' => 'sometimes|nullable',
        ]);

        try {
            $church_id = auth()->user()->church_id;
            $subject_ids = SmExamSetup::query();
            $assign_subjects = SmAssignSubject::query();

            if ($request->class != null) {
                $assign_subjects->where('age_group_id', $request->class);
                $subject_ids->where('age_group_id', $request->class);
            }

            if ($request->section != null) {
                $assign_subjects->where('mgender_id', $request->section);
                $subject_ids->where('mgender_id', $request->section);
            }

            $assign_subjects = $assign_subjects->where('church_year_id', getAcademicId())
                ->where('church_id', $church_id)
                ->get();
            $subject_ids = $subject_ids->where('church_year_id', getAcademicId())
                ->where('church_id', $church_id)
                ->pluck('subject_id')->toArray();

            if ($assign_subjects->count() == 0) {
                return response()->json(['message' => 'No Subject Assigned. Please assign subjects in this class']);
            }

            if (teacherAccess()) {
                $teacher_info = SmStaff::where('user_id', $church_id)->first();
                $classes = $teacher_info->classes;
            } else {
                $classes = SmClass::get();
            }

            $age_group_id = $request->class;
            $mgender_id = $request->section != null ? $request->section : 0;
            $exam_type_id = $request->exam_type;
            $exam_types = SmExamType::where('church_year_id', getAcademicId())
                ->where('church_id', $church_id)
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
                ->where('church_id', $church_id)
                ->get();

            $subjects = SmSubject::whereIn('id', $subject_ids)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', $church_id)
                ->get(['id', 'subject_name']);

            $teachers = SmStaff::where('role_id', 4)
                ->where('active_status', 1)
                ->where('church_id', $church_id)
                ->get(['id', 'user_id', 'full_name']);

            $rooms = SmClassRoom::where('active_status', 1)
                ->where('church_id', $church_id)
                ->get(['id', 'room_no']);

            $examName = SmExamType::where('id', $request->exam_type)->where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', $church_id)->first()->title;

            $search_current_class = SmClass::find($request->class);
            $search_current_section = SmSection::find($request->section);

            return response()->json(compact('classes', 'subjects', 'exam_schedule', 'age_group_id', 'mgender_id', 'exam_type_id', 'exam_types', 'teachers', 'rooms', 'examName', 'search_current_class', 'search_current_section'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }

    // add exam routine
    // {

    //     "age_group_id": "1",
    //     "mgender_id": "0",
    //     "exam_type_id": "1",
    //     "routine": {
    //                 "1": {
    //                 "subject": "1",
    //                 "section": "1",
    //                 "teacher_id": "4",
    //                 "date": "11/18/2021",
    //                 "start_time": "5:08 PM",
    //                 "end_time": "6:08 PM",
    //                 "room": "1"
    //                 }
    //             }
    // }
    public function addExamRoutineStore(Request $request)
    {
        // return   $request->all();
        $input = $request->all();
        $validator = Validator::make($input, [
            // 'subject' => 'required',
            'age_group_id' => 'required',
            'mgender_id' => 'required',
            // 'room' => 'required',
            // 'date' => 'required',
            // 'start_time' => 'required',
            // 'end_time' => 'required',
            'exam_type_id' => 'required',
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            $age_group_id = $request->age_group_id;
            $mgender_id = $request->mgender_id == 0 ? 0 : $request->mgender_id;
            $exam_term_id = $request->exam_type_id;
            $church_id = auth()->user()->church_id;
            $exam_schedule = SmExamSchedule::query();
            if ($request->age_group_id) {
                $exam_schedule->where('age_group_id', $request->age_group_id);
            }
            if ($request->mgender_id != 0) {
                $exam_schedule->where('mgender_id', $request->section);
            }
            $exam_schedule = $exam_schedule->where('exam_term_id', $request->exam_type_id)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', $church_id)
                ->delete();

            foreach ($request->routine as $routine_data) {
                if (gv($routine_data, 'subject') == "Select Subject *") {
                    Toastr::error('Subject Can not Be Empty', 'Failed');
                    return redirect('exam-routine-view/' . $age_group_id . '/' . $mgender_id . '/' . $exam_term_id);
                }
                if (!gv($routine_data, 'subject') || gv($routine_data, 'subject') == "Select Subject *" || !gv($routine_data, 'start_time') || !gv($routine_data, 'end_time')) {
                    continue;
                }
                $is_exist = SmExamSchedule::where(
                    [
                        'exam_term_id' => $request->exam_type_id,
                        'subject_id' => gv($routine_data, 'subject'),
                        'date' => date('Y-m-d', strtotime(gv($routine_data, 'date'))),
                        'start_time' => date('H:i:s', strtotime(gv($routine_data, 'start_time'))),
                        'end_time' => date('H:i:s', strtotime(gv($routine_data, 'end_time'))),
                        'room_id' => gv($routine_data, 'room'),
                        'age_group_id' => $request->age_group_id,
                        'mgender_id' => gv($routine_data, 'section'),
                    ]
                )->where('church_id', $church_id)->first();

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
                $exam_routine->church_id = $church_id;
                $exam_routine->church_year_id = getAcademicId();
                $exam_routine->save();
            }

            return response()->json(['success' => 'Exam routine has been Created successfully']);

            // return redirect('exam-routine-view/' . $age_group_id . '/' . $mgender_id . '/' . $exam_term_id);
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function studentRoutine($user_id)
    {
        try {
            $student_detail = SmStudent::with('studentRecords')->select('id', 'full_name', 'user_id')
                ->where('user_id', $user_id)
                ->first();

            $records = $student_detail->studentRecords;


            $exam_types = SmExamType::where('church_id', Auth::user()->church_id)
                ->where('church_year_id', getAcademicId())

                ->where('active_status', 1)->get(['id', 'title']);
            return response()->json(compact('exam_types', 'student_detail'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }

    public function studentExamRoutineSearch(Request $request)
    {
        try {
            $input = $request->all();
            $validator = Validator::make($input, [
                'exam' => 'required',
                'member_id'=>'required',
            ]);

            if ($validator->fails()) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
                }
            }

            $student_detail = SmStudent::select('id', 'full_name')
                ->where('user_id', $request->member_id)
                ->first();
            $record = StudentRecord::where('member_id', $request->member_id)->first();
            $age_group_id = $record->age_group_id;
            $mgender_id = $record->mgender_id;
            $church_id = $record->church_id;
            $church_year_id = $record->church_year_id;
            $routines = SmExamSchedule::where('exam_term_id', $request->exam)
                ->where('age_group_id', $age_group_id)->where('mgender_id', $mgender_id)
                ->where('church_id', $church_id)->where('church_year_id', $church_year_id)
                ->get();

            $exam_routines =[];
            foreach ($routines as $routine) {
                $exam_routines[] = [
                    'id' => $routine->id,
                    'class' => $routine->class ? $routine->class->age_group_name :'',
                    'section' => $routine->section ? $routine->section->mgender_name :'',
                    'room' => $routine->classRoom ? $routine->classRoom->room_no :'',
                    'subject' => $routine->subject ? $routine->subject->subject_name :'',
                    'teacher' => $routine->teacher ? $routine->teacher->full_name :'',
                    'start_time'=> date('h:i A', strtotime($routine->start_time)),
                    'end_time'=> date('h:i A', strtotime($routine->end_time)),
                ];
            }

            return response()->json(compact('exam_routines'));
        } catch (\Throwable $th) {
            return ApiBaseMethod::sendError('Error.', $th->getMessage());

        }
    }
    public function examRoutineReportSearch(Request $request)
    {
        try {
            $input = $request->all();
            $validator = Validator::make($input, [
                'exam' => 'required',
            ]);

            if ($validator->fails()) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
                }
            }

            $student_detail = SmStudent::with('studentRecords')->select('id', 'full_name', 'user_id')
                ->where('user_id', Auth::id())
                ->first();


            $examType = SmExamType::select('id', 'title')->find($request->exam);
            // $routines = SmExamSchedule::where('exam_term_id', $request->exam)->get();
            $exa_routines = SmExamSchedule::when($student_detail, function ($q) use($student_detail){
                $records = $student_detail->studentRecords;
                $q->whereIn('age_group_id', $records->pluck('age_group_id'))
                    ->whereIn('mgender_id', $records->pluck('mgender_id'));
            })
                ->where('exam_term_id', $request->exam)
                ->orderBy('date', 'ASC')->get();
            $exa_routines = $exa_routines->groupBy('date');
            $exam_term_id  = $request->exam;
            $exam_routines =[];

            foreach ($exa_routines as $date => $routines) {
                foreach($routines as $routine){
                    $exam_routines[$date][] = [
                        'id' => $routine->id,
                        'date' => $date,
                        'class' => $routine->class ? $routine->class->age_group_name :'',
                        'section' => $routine->section ? $routine->section->mgender_name :'',
                        'room' => $routine->classRoom ? $routine->classRoom->room_no :'',
                        'subject' => $routine->subject ? $routine->subject->subject_name :'',
                        'teacher' => $routine->teacher ? $routine->teacher->full_name :'',
                        'exam_type'=> $examType->title,
                        'start_time'=> date('h:i A', strtotime($routine->start_time)),
                        'end_time'=> date('h:i A', strtotime($routine->end_time)),
                    ];
                }
            }

            return response()->json(compact('examType', 'exam_routines'));
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
}
