<?php

namespace App\Http\Controllers;

use App\SmClass;
use App\SmStaff;
use App\SmSection;
use App\SmStudent;
use App\SmSubject;
use App\YearCheck;
use App\SmFeesAssign;
use App\SmFeesMaster;
use App\ApiBaseMethod;
use App\Http\Controllers\Admin\StudentInfo\SmStudentReportController;
use App\SmFeesPayment;
use App\SmClassRoutine;
use App\SmAssignSubject;
use Illuminate\Http\Request;
use App\SmAssignClassTeacher;
use App\SmFeesAssignDiscount;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SmAcademicsController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
        // User::checkAuth();
    }

    public function classRoutine()
    {

        try {
            $classes = SmClass::where('active_status', 1)->where('church_id', Auth::user()->church_id)->get();
            return view('backEnd.academics.class_routine', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function classRoutineCreate()
    {

        try {
            $classes = SmClass::where('active_status', 1)->where('church_id', Auth::user()->church_id)->get();
            return view('backEnd.academics.class_routine_create', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function assignSubject(Request $request)
    {

        try {
            $classes = SmClass::where('active_status', 1)->where('church_id', Auth::user()->church_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.academics.assign_subject', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function assigSubjectCreate(Request $request)
    {

        try {
            $classes = SmClass::where('active_status', 1)->where('church_id', Auth::user()->church_id)->where('church_year_id', getAcademicId())->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.academics.assign_subject_create', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function assignSubjectSearch(Request $request)
    {
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
        try {
            $assign_subjects = SmAssignSubject::where('age_group_id', $request->class)->where('mgender_id', $request->section)->get();
            $subjects = SmSubject::where('active_status', 1)->where('church_id', Auth::user()->church_id)->where('church_year_id', getAcademicId())->get();
            $teachers = SmStaff::where('active_status', 1)->where('church_id', Auth::user()->church_id)->where(function($q) {
                        $q->where('role_id', 4)->orWhere('previous_role_id', 4);
                    })->get();
            $age_group_id = $request->class;
            $mgender_id = $request->section;
            $classes = SmClass::where('active_status', 1)->where('church_id', Auth::user()->church_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['assign_subjects'] = $assign_subjects->toArray();
                $data['teachers'] = $teachers->toArray();
                $data['subjects'] = $subjects->toArray();
                $data['age_group_id'] = $age_group_id;
                $data['mgender_id'] = $mgender_id;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.academics.assign_subject_create', compact('classes', 'assign_subjects', 'teachers', 'subjects', 'age_group_id', 'mgender_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function assignSubjectAjax(Request $request)
    {

        try {
            $subjects = SmSubject::where('active_status', 1)->where('church_id', Auth::user()->church_id)->where('church_year_id', getAcademicId())->get();
            $teachers = SmStaff::where('active_status', 1)->where('church_id', Auth::user()->church_id)->where(function($q)  {
                        $q->where('role_id', 4)->orWhere('previous_role_id', 4);
                })->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['subjects'] = $subjects->toArray();
                $data['teachers'] = $teachers->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return response()->json([$subjects, $teachers]);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function assignSubjectStore(Request $request)
    {


        try {
            if ($request->update == 0) {
                $i = 0;
                if (isset($request->subjects)) {
                    foreach ($request->subjects as $subject) {
                        if ($subject != "") {
                            $assign_subject = new SmAssignSubject();
                            $assign_subject->age_group_id = $request->age_group_id;
                            $assign_subject->mgender_id = $request->mgender_id;
                            $assign_subject->subject_id = $subject;
                            $assign_subject->teacher_id = $request->teachers[$i];
                            $assign_subject->church_id = Auth::user()->church_id;
                            $assign_subject->church_year_id = getAcademicId();

                            $assign_subject->save();
                            $i++;
                        }
                    }
                }
            } elseif ($request->update == 1) {
                $assign_subjects = SmAssignSubject::where('age_group_id', $request->age_group_id)->where('mgender_id', $request->mgender_id)->delete();

                $i = 0;
                if (isset($request->subjects)) {
                    foreach ($request->subjects as $subject) {

                        if ($subject != "") {
                            $assign_subject = new SmAssignSubject();
                            $assign_subject->age_group_id = $request->age_group_id;
                            $assign_subject->mgender_id = $request->mgender_id;
                            $assign_subject->subject_id = $subject;
                            $assign_subject->teacher_id = $request->teachers[$i];
                            $assign_subject->church_year_id = getAcademicId();
                            $assign_subject->church_id = Auth::user()->church_id;
                            $assign_subject->save();
                            $i++;
                        }
                    }
                }
            }


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse(null, 'Record Updated Successfully');
            }
            Toastr::success('Operation successful', 'Success');
            return redirect()->back();

            // return redirect()->back()->with('message-success', 'Record Updated Successfully');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function assignSubjectFind(Request $request)
    {
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
        try {
            $assign_subjects = SmAssignSubject::where('age_group_id', $request->class)->where('mgender_id', $request->section)->get();
            $subjects = SmSubject::where('active_status', 1)->where('church_id', Auth::user()->church_id)->get();
            $teachers = SmStaff::where('active_status', 1)->where(function($q)  {               
                        $q->where('role_id', 4)->orWhere('previous_role_id', 4);
                })->where('church_id', Auth::user()->church_id)->get();
            $classes = SmClass::where('active_status', 1)->where('church_id', Auth::user()->church_id)->get();
            if ($assign_subjects->count() == 0) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('No Result Found');
                }
                Toastr::error('No Result Found', 'Failed');
                return redirect()->back();
                // return redirect()->back()->with('message-danger', 'No Result Found');
            } else {
                $age_group_id = $request->class;

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    $data = [];
                    $data['classes'] = $classes->toArray();
                    $data['assign_subjects'] = $assign_subjects->toArray();
                    $data['teachers'] = $teachers->toArray();
                    $data['subjects'] = $subjects->toArray();
                    $data['age_group_id'] = $age_group_id;
                    return ApiBaseMethod::sendResponse($data, null);
                }
                return view('backEnd.academics.assign_subject', compact('classes', 'assign_subjects', 'teachers', 'subjects', 'age_group_id'));
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function ajaxSelectSubject(Request $request)
    {
        try {
            $staff_info = SmStaff::where('user_id', Auth::user()->id)->first();
            // return $staff_info;
            if (teacherAccess()) {
                $subject_all = SmAssignSubject::where('age_group_id', '=', $request->class)
                    ->where('mgender_id', $request->section)
                    ->where('teacher_id', $staff_info->id)
                    ->distinct('subject_id')
                    ->get();
            } else {
                $subject_all = SmAssignSubject::where('age_group_id', '=', $request->class)
                    ->where('mgender_id', $request->section)
                    ->distinct('subject_id')
                    ->get();
            }
            $students = [];
            foreach ($subject_all as $allSubject) {
                $students[] = SmSubject::where('id', $allSubject->subject_id)->first(['id','subject_name','subject_type']);
            }
            return response()->json([$students]);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function assignRoutineSearch(Request $request)
    {
        $request->validate([
            'class' => 'required',
            'section' => 'required',
            'subject' => 'required'
        ]);

        try {
            $age_group_id = $request->class;
            $mgender_id = $request->section;
            $subject_id = $request->subject;
            $classes = SmClass::where('active_status', 1)->get();
            $class_routine = SmClassRoutine::where('age_group_id', $request->class)->where('mgender_id', $request->section)->where('subject_id', $request->subject)->first();
            if ($class_routine == "") {
                $class_routine = "hello";
            }
            return view('backEnd.academics.class_routine_create', compact('class_routine', 'age_group_id', 'mgender_id', 'subject_id', 'classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function assignRoutineStore(Request $request)
    {

        try {
            $check_assigned = $class_routine = SmClassRoutine::where('age_group_id', $request->age_group_id)->where('mgender_id', $request->mgender_id)->where('subject_id', $request->subject_id)->delete();
            // if($check_assigned != ""){
            $class_routine = new SmClassRoutine();
            $class_routine->age_group_id = $request->age_group_id;
            $class_routine->mgender_id = $request->mgender_id;
            $class_routine->subject_id = $request->subject_id;

            $class_routine->monday_start_from = $request->monday_start_from;
            $class_routine->monday_end_to = $request->monday_end_to;
            $class_routine->monday_room_id = $request->monday_room;

            $class_routine->tuesday_start_from = $request->tuesday_start_from;
            $class_routine->tuesday_end_to = $request->tuesday_end_to;
            $class_routine->tuesday_room_id = $request->tuesday_room;

            $class_routine->wednesday_start_from = $request->wednesday_start_from;
            $class_routine->wednesday_end_to = $request->wednesday_end_to;
            $class_routine->wednesday_room_id = $request->wednesday_room;

            $class_routine->thursday_start_from = $request->thursday_start_from;
            $class_routine->thursday_end_to = $request->thursday_end_to;
            $class_routine->thursday_room_id = $request->thursday_room;

            $class_routine->friday_start_from = $request->friday_start_from;
            $class_routine->friday_end_to = $request->friday_end_to;
            $class_routine->friday_room_id = $request->friday_room;

            $class_routine->saturday_start_from = $request->saturday_start_from;
            $class_routine->saturday_end_to = $request->saturday_end_to;
            $class_routine->saturday_room_id = $request->saturday_room;

            $class_routine->sunday_start_from = $request->sunday_start_from;
            $class_routine->sunday_end_to = $request->sunday_end_to;
            $class_routine->sunday_room_id = $request->sunday_room;
            $class_routine->church_id = Auth::user()->church_id;
            $class_routine->church_year_id = getAcademicId();
            $class_routine->save();
            // }else{

            // }
            Toastr::success('Operation successful', 'Success');
            return redirect('class-routine');
            // return redirect('class-routine')->with('message-success', 'Class Routine has been Inserted successfully');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function classRoutineReportSearch(Request $request)
    {
        $request->validate([
            'class' => 'required',
            'section' => 'required'
        ]);
        try {
            $classes = SmClass::where('active_status', 1)->where('church_id', Auth::user()->church_id)->get();
            $class_routines = SmClassRoutine::where('age_group_id', $request->class)->where('mgender_id', $request->section)->get();
            $age_group_id = $request->class;
            return view('backEnd.academics.class_routine', compact('class_routines', 'classes', 'age_group_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function classReport(Request $request)
    {
        try {
            $classes = SmClass::where('church_year_id', getAcademicId())->where('active_status', 1)->where('church_id', Auth::user()->church_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.reports.class_report', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function classReportSearch(Request $request)
    {
        // return $request;
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
            $search_class = SmClass::where('church_year_id', getAcademicId())->where('id', $request->class)->where('church_id', Auth::user()->church_id)->first();

            if ($request->section != "") {
                $sectionInfo = SmSection::where('church_year_id', getAcademicId())->where('id', $request->section)->where('church_id', Auth::user()->church_id)->first();
            } else {
                $sectionInfo = '';
            }
            
            $member_ids_from_record = SmStudentReportController::classSectionStudent($request);
            $students = SmStudent::query()->whereIn('id', $member_ids_from_record)->where('church_id', auth()->user()->church_id);
           
            $students = $students->get();
            $member_ids= $students->pluck('id')->toArray();


            $assign_subjects = SmAssignSubject::query()->with('teacher');

            if ($request->section != "") {
                $assign_subjects->where('mgender_id', $request->section);
            }
            $assign_subjects->where('age_group_id', $request->class);
            $assign_subjects = $assign_subjects->get();



            $assign_class_teacher = SmAssignClassTeacher::query();
            $assign_class_teacher->where('church_year_id', getAcademicId())->where('active_status', 1);
            if ($request->section != "") {
                $assign_class_teacher->where('mgender_id', $request->section);
            }
            $assign_class_teacher->where('age_group_id', $request->class);
            $assign_class_teacher = $assign_class_teacher->first();


            if ($assign_class_teacher != "") {
                $assign_class_teachers = $assign_class_teacher->classTeachers->first();
            } else {
                $assign_class_teachers = '';
            }
            $fees_assigns = SmFeesAssign::whereIn("member_id", $member_ids)->get();
            $fees_master_ids=$fees_assigns->pluck('fees_master_id')->toArray();

            $fees_masters = SmFeesMaster::whereIn('id', $fees_master_ids)->pluck('fees_type_id')->toArray();

            $total_collection = SmFeesPayment::where('active_status',1)->where('church_year_id', getAcademicId())->whereIn('member_id', $member_ids)->whereIn('fees_type_id', $fees_masters)->sum('amount');
            $total_assign = 0;
            $total_due = 0;
            $applied_discount = 0;
            foreach ($students as $student) {
                $fees_assigns = SmFeesAssign::where("member_id", $student->id)->get();
                $fees_dues_master_ids=[];
                foreach ($fees_assigns as $fees_assign) {
                    $fees_master = SmFeesMaster::where('church_year_id', getAcademicId())->where('id', $fees_assign->fees_master_id)->first();


                    $due_date= strtotime($fees_assign->feesGroupMaster->date);
                    $now =strtotime(date('Y-m-d'));
                    if ($due_date > $now ) {
                        continue;
                    }
                    $total_assign = $total_assign + $fees_master->amount;
                    $total_due += $fees_assign->fees_amount;
                    $fees_dues_master_ids[]=$fees_assign->fees_master_id;

                }
                foreach ($fees_dues_master_ids as $key => $master) {
                    $applied_discount+=SmFeesAssign::where('member_id',$student->id)->where('fees_master_id',$master)->sum('applied_discount');
                }
            }
            $classes = SmClass::where('church_year_id', getAcademicId())->where('active_status', 1)->where('church_id', Auth::user()->church_id)->get();
            $mgender_id = $request->section;
            $age_group_id = $request->class;

            return view('backEnd.reports.class_report', compact('total_due','classes', 'students','applied_discount', 'assign_subjects', 'assign_class_teachers', 'total_collection', 'total_assign', 'search_class', 'sectionInfo', 'mgender_id','age_group_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}