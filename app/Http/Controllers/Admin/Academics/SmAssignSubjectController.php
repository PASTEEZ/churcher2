<?php

namespace App\Http\Controllers\Admin\Academics;

use App\SmClass;
use App\SmStaff;
use App\SmSubject;
use App\YearCheck;
use App\ApiBaseMethod;
use App\SmClassSection;
use App\SmAssignSubject;
use Illuminate\Http\Request;
use App\Events\CreateClassGroupChat;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class SmAssignSubjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
     
    }
    public function index(Request $request)
    {

        try {
            $classes = SmClass::get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.academics.assign_subject', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function create(Request $request)
    {
        try {
            $classes = SmClass::get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.academics.assign_subject_create', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function ajaxSubjectDropdown(Request $request)
    {
        try {
            $staff_info = SmStaff::where('user_id', Auth::user()->id)->first();
            if (teacherAccess()) {
                $age_group_id = $request->class;
                $allSubjects = SmAssignSubject::where([['mgender_id', '=', $request->id], ['age_group_id', $age_group_id], ['teacher_id', $staff_info->id]])->where('church_id', Auth::user()->church_id)->get();
                $subjectsName = [];
                foreach ($allSubjects as $allSubject) {
                    $subjectsName[] = SmSubject::find($allSubject->subject_id);
                }
            } else {
                $age_group_id = $request->class;
                $allSubjects = SmAssignSubject::where([['mgender_id', '=', $request->id], ['age_group_id', $age_group_id]])->where('church_id', Auth::user()->church_id)->get();

                $subjectsName = [];
                foreach ($allSubjects as $allSubject) {
                    $subjectsName[] = SmSubject::find($allSubject->subject_id);
                }
            }
            return response()->json([$subjectsName]);
        } catch (\Exception $e) {
            return Response::json(['error' => 'Error msg'], 404);
        }
    }

    public function search(Request $request)
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

             $assign_subjects=SmAssignSubject::query();
            $assign_subjects= $assign_subjects->where('age_group_id',$request->class);

            if($request->section !=null){
                $assign_subjects= $assign_subjects->where('mgender_id',$request->section);
            }

             $assign_subjects=$assign_subjects->where('church_id',Auth::user()->church_id)->get();
           

            $subjects = SmSubject::where('active_status', 1)->where('church_id', Auth::user()->church_id)->where('church_year_id', getAcademicId())->get();
            $teachers = SmStaff::where('active_status', 1)
            ->where(function($q)  {                
                $q->where('role_id', 4)->orWhere('previous_role_id', 4);             
            })->where('church_id', Auth::user()->church_id)->get();
         
            $age_group_id = $request->class;
            $mgender_id = $request->section;

            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();

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
            $subjects = SmSubject::get();
            $teachers = SmStaff::status()->where(function($q)  {
	$q->where('role_id', 4)->orWhere('previous_role_id', 4);})->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['subjects'] = $subjects->toArray();
                $data['teachers'] = $teachers->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return response()->json([$subjects, $teachers]);
        } catch (\Exception $e) {
            return Response::json(['error' => 'Error msg'], 404);
        }
    }

    public function assignSubjectStore(Request $request)
    {
        try {
            if(empty($request->all())) {
                Toastr::error('Operation failed', 'Error');
                return redirect()->back();
            }
            if ($request->update == 0) {
                $i = 0;
                //  $k = 0;
                if (isset($request->subjects)) {
                    foreach ($request->subjects as $key=>$subject) {
                        if ($subject != "") {                            
                            if($request->mgender_id==null){
                                $k = 0;
                                $all_section=SmClassSection::where('age_group_id',$request->age_group_id)->get();
                               $t_teacher=count($request->teachers);
                                foreach($all_section as $section){                                        
                                    $assign_subject = new SmAssignSubject();
                                    $assign_subject->age_group_id = $request->age_group_id;
                                    $assign_subject->church_id = Auth::user()->church_id;
                                    $assign_subject->mgender_id = $section->mgender_id;
                                    $assign_subject->subject_id = $subject;                            
                                    $assign_subject->teacher_id = $request->teachers[$key];                                
                                    $assign_subject->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                                    $assign_subject->church_year_id = getAcademicId();
                                    $assign_subject->save();
                                    event(new CreateClassGroupChat($assign_subject));
                                    $k++;
                                }

                            }else{
                            $assign_subject = new SmAssignSubject();
                            $assign_subject->age_group_id = $request->age_group_id;
                            $assign_subject->church_id = Auth::user()->church_id;
                            $assign_subject->mgender_id = $request->mgender_id;
                            $assign_subject->subject_id = $subject;
                            $assign_subject->teacher_id = $request->teachers[$i];
                            $assign_subject->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                            $assign_subject->church_year_id = getAcademicId();
                            $assign_subject->save();
                            event(new CreateClassGroupChat($assign_subject));
                            $i++;
                            }
                        }
                    }
                }
            } elseif ($request->update == 1) {
                if($request->mgender_id ==null){
                    $assign_subjects = SmAssignSubject::where('age_group_id', $request->age_group_id)->delete();

                    $i = 0;
                    if (! empty($request->subjects)) {
            
                        foreach ($request->subjects as $key=>$subject) {
                            $k = 0;
                            if (!empty($subject)) {

                                $all_section=SmClassSection::where('age_group_id',$request->age_group_id)->get();
                                foreach($all_section as $section){
                         
                                $assign_subject = new SmAssignSubject();
                                $assign_subject->age_group_id = $request->age_group_id;
                                $assign_subject->mgender_id = $section->mgender_id;
                                $assign_subject->subject_id = $subject;
                                $assign_subject->teacher_id = $request->teachers[$key];
                                $assign_subject->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                                $assign_subject->church_year_id = getAcademicId();
                                $assign_subject->church_id = Auth::user()->church_id;

                                
                                $assign_subject->save();
                                event(new CreateClassGroupChat($assign_subject));
                                $k++;
                                }
                            }
                        }
                    }

                }else{
                    SmAssignSubject::where('age_group_id', $request->age_group_id)->where('mgender_id', $request->mgender_id)->delete();
               
                    $i = 0;
                    if (! empty($request->subjects)) {
            
                        foreach ($request->subjects as $subject) {
                                
                            if (!empty($subject)) {
                                $assign_subject = new SmAssignSubject();
                                $assign_subject->age_group_id = $request->age_group_id;
                                $assign_subject->mgender_id = $request->mgender_id;
                                $assign_subject->subject_id = $subject;
                                $assign_subject->teacher_id = $request->teachers[$i];
                                $assign_subject->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                                $assign_subject->church_year_id = getAcademicId();
                                $assign_subject->church_id = Auth::user()->church_id;
                                $result =  $assign_subject->save();
                                event(new CreateClassGroupChat($assign_subject));
                                $i++;
                            }
                        }
                    }
             }
            }
            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
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
        try {
            $assign_subjects = SmAssignSubject::where('age_group_id', $request->class)->where('mgender_id', $request->section)->get();
            $subjects = SmSubject::get();
            $teachers = SmStaff::status()->where(function($q)  {
	            $q->where('role_id', 4)->orWhere('previous_role_id', 4);
            })->get();
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();

            if ($assign_subjects->count() == 0) {
                Toastr::error('No Result Found', 'Failed');
                return redirect()->back();
                // return redirect()->back()->with('message-danger', 'No Result Found');
            } else {
                $age_group_id = $request->class;
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
            $subject_all = SmAssignSubject::where('age_group_id', '=', $request->class)->where('mgender_id', $request->section)->distinct('subject_id')->where('church_id', Auth::user()->church_id)->get();
            $students = [];
            foreach ($subject_all as $allSubject) {
                $students[] = SmSubject::find($allSubject->subject_id);
            }
            return response()->json([$students]);
        } catch (\Exception $e) {
            return Response::json(['error' => 'Error msg'], 404);
        }
    }
}
