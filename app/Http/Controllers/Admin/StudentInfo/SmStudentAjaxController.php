<?php

namespace App\Http\Controllers\Admin\StudentInfo;

use App\SmClass;
use App\SmStaff;
use App\SmSection;
use App\SmStudent;
use App\SmSubject;
use App\SmVehicle;
use App\SmRoomList;
use App\SmExamSetup;
use App\SmClassSection;
use App\SmAssignSubject;
use App\SmAssignVehicle;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Scopes\StatusAcademicSchoolScope;

class SmStudentAjaxController extends Controller
{

    public function __construct()
    {
        $this->middleware('PM');
     
    }
    public function ajaxSectionSibling(Request $request)
    {
        try {
            $sectionIds = SmClassSection::where('age_group_id', '=', $request->id)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();

            $sibling_sections = [];
            foreach ($sectionIds as $sectionId) {
                $sibling_sections[] = SmSection::find($sectionId->mgender_id);
            }
            return response()->json([$sibling_sections]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }
    public function ajaxSiblingInfo(Request $request)
    {
        try {

            $siblings=SmStudent::query();
            
            if ($request->id != "") {
                $siblings->where('id', '!=', $request->id);
            } 
            $siblings = $siblings->status()->withoutGlobalScope(StatusAcademicSchoolScope::class)->get();
            
            return response()->json($siblings);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }


    public function ajaxSiblingInfoDetail(Request $request)
    {
        try {
            $staff = $request->staff_id ?  SmStaff::with('roles')->find($request->staff_id) : null;
            $sibling_detail = $request->id ? SmStudent::find($request->id) : null;
            $parent_detail =  $sibling_detail ? $sibling_detail->parents : null;
            $type = $staff ?  'staff' : 'sibling';
            return response()->json([$sibling_detail, $parent_detail, $staff, $type]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

    public function ajaxGetVehicle(Request $request)
    {
        try {
            $church_id = 1;
            if(Auth::check()){
                $church_id = Auth::user()->church_id;
            } else if(app()->bound('school')){
                $church_id = app('school')->id;
            }
            $vehicle_detail = SmAssignVehicle::where('route_id', $request->id)->where('church_id', $church_id)->first();
            $vehicles = explode(',', $vehicle_detail->vehicle_id);
            $vehicle_info = SmVehicle::whereIn('id', $vehicles)->get();
           
            return response()->json([$vehicle_info]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

    public function ajaxVehicleInfo(Request $request)
    {
        try {
            $vehivle_detail = SmVehicle::find($request->id);
            return response()->json([$vehivle_detail]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

    public function ajaxRoomDetails(Request $request)
    {
        try {
            $church_id = 1;
            if(Auth::check()){
                $church_id = Auth::user()->church_id;
            } else if(app()->bound('school')){
                $church_id = app('school')->id;
            }

            $room_details = SmRoomList::where('dormitory_id', '=', $request->id)->where('church_id', $church_id)->get();
            $rest_rooms = [];
            foreach ($room_details as $room_detail) {
                $count_room = SmStudent::where('room_id', $room_detail->id)->count();
                if ($count_room < $room_detail->number_of_bed) {
                    $rest_rooms[] = $room_detail;
                }
            }
            return response()->json([$rest_rooms]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

    public function ajaxGetRollId(Request $request)
    {

        try {
            $max_roll = SmStudent::where('age_group_id', $request->class)
                        ->where('mgender_id', $request->section)
                        ->where('church_id', Auth::user()->church_id)
                        ->max('roll_no');
            // return $max_roll;
            if ($max_roll == "") {
                $max_roll = 1;
            } else {
                $max_roll = $max_roll + 1;
            }
            return response()->json([$max_roll]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

    public function ajaxGetRollIdCheck(Request $request)
    {
        try {
            $roll_no = SmStudent::where('age_group_id', $request->class)
                    ->where('mgender_id', $request->section)
                    ->where('roll_no', $request->roll_no)
                    ->where('church_id', Auth::user()->church_id)
                    ->get();
            return response()->json($roll_no);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }
    
    public function ajaxSubjectClass(Request $request)
    {
        try {
    
            $subjects = SmAssignSubject::query();
            if (teacherAccess()) {
                $subjects->where('teacher_id', Auth::user()->staff->id) ;
            }
            if ($request->id !="all_class") {
                $subjects->where('age_group_id', '=', $request->id);
            } else {
                $subjects->groupBy('age_group_id');
            }
            $subjectIds=$subjects->groupBy('subject_id')->get()->pluck(['subject_id'])->toArray();
            
 
            $subjects=SmSubject::whereIn('id', $subjectIds)->get(['id','subject_name']);
            
            return response()->json([$subjects]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

    
    public function ajaxStudentPromoteSection(Request $request)
    {
        // $sectionIds = SmClassSection::where('age_group_id', '=', $request->id)->get();
        if (teacherAccess()) {
            $sectionIds = SmAssignSubject::where('age_group_id', '=', $request->id)
            ->where('teacher_id', Auth::user()->staff->id)
            ->where('church_id', Auth::user()->church_id)
            ->where('church_year_id', getAcademicId())
            ->groupby(['age_group_id','mgender_id'])
            ->withoutGlobalScope(StatusAcademicSchoolScope::class)
            ->get();
        } else {
            $sectionIds = SmClassSection::where('age_group_id', '=', $request->id)
            ->where('church_id', Auth::user()->church_id)->withoutGlobalScope(StatusAcademicSchoolScope::class)->get();
        }
        $promote_sections = [];
        foreach ($sectionIds as $sectionId) {
            $promote_sections[] = SmSection::where('id', $sectionId->mgender_id)->withoutGlobalScope(StatusAcademicSchoolScope::class)->first(['id','mgender_name']);
        }

        return response()->json([$promote_sections]);
    }

    public function ajaxGetClass(Request $request)
    {
        $classes = SmClass::where('created_at', 'LIKE', $request->year . '%')->get();

        return response()->json([$classes]);
    }

    
    public function ajaxSelectStudent(Request $request)
    {
       
        $member_ids = SmStudentReportController::classSectionStudent($request);
        $students = SmStudent::whereIn('id', $member_ids)->where('active_status', 1)->where('church_id', Auth::user()->church_id)->get()->map(function($student){
            return [
                'id' => $student->id,
                'full_name' => $student->first_name. ' '. $student->last_name,
                'user_id' => $student->user_id
                ];
        })->toArray();
        return response()->json([$students]);
    }
    public function ajaxPromoteYear(Request $request)
    {
        $classes = SmClass::where('church_year_id', $request->year)
                ->where('church_id',Auth::user()->church_id)
                ->withOutGlobalScope(StatusAcademicSchoolScope::class)
                ->get();

        return response()->json([$classes]);
    }

    public function ajaxSectionStudent(Request $request)
    {
        try {
            $class = SmClass::withOutGlobalScope(StatusAcademicSchoolScope::class)->find($request->id);
            if (teacherAccess()) {
                $sectionIds = SmAssignSubject::withOutGlobalScope(StatusAcademicSchoolScope::class)->where('age_group_id', '=', $request->id)
                            ->where('teacher_id',Auth::user()->staff->id)               
                            ->where('church_id', Auth::user()->church_id)
                            ->when($class, function ($q) use ($class) {
                                $q->where('church_year_id', $class->church_year_id);
                            })
                            ->groupby(['age_group_id','mgender_id'])
                            ->get();
            } else {
                $sectionIds = SmClassSection::withOutGlobalScope(StatusAcademicSchoolScope::class)->where('age_group_id', '=', $request->id) 
                                    ->when($class, function ($q) use ($class) {
                                        $q->where('church_year_id', $class->church_year_id);
                                    })              
                            ->where('church_id', Auth::user()->church_id)
                            ->get();
            }
            $sections = [];
            foreach ($sectionIds as $sectionId) {
                $section = SmSection::withOutGlobalScope(StatusAcademicSchoolScope::class)->where('id',$sectionId->mgender_id)->select('id','mgender_name')->first();
                if($section){
                    $sections[] = $section;
                }
            }
            return response()->json([$sections]);
        } catch (\Exception $e) {

            return response()->json("", 404);
        }
    }

    public function ajaxSubjectSection(Request $request)
    {
        if (teacherAccess()) {
            $sectionIds = SmAssignSubject::where('age_group_id', '=', $request->age_group_id)
            ->where('subject_id', '=', $request->subject_id)
            ->where('teacher_id',Auth::user()->staff->id)               
            ->where('church_id', Auth::user()->church_id)
            ->groupby(['age_group_id','mgender_id'])
            ->get();
        } else {
            $sectionIds = SmAssignSubject::where('age_group_id', '=', $request->age_group_id)
            ->where('subject_id', '=', $request->subject_id)               
            ->where('church_id', Auth::user()->church_id)
            ->groupby(['age_group_id','mgender_id'])
            ->get();
        }
        $promote_sections = [];
        foreach ($sectionIds as $sectionId) {
            $promote_sections[] = SmSection::where('id',$sectionId->mgender_id)->first(['id','mgender_name']);
        }

        return response()->json([$promote_sections]);
    }

    public function ajaxSubjectFromExamType()
    {
        try {
            $subjects = SmExamSetup::with('subjectDetails')
                    ->where('exam_term_id', request()->id)
                    ->groupBy('subject_id')->get();
            return response()->json([$subjects]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

}
