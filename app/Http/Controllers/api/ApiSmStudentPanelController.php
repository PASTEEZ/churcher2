<?php

namespace App\Http\Controllers\api;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\Models\StudentRecord;
use App\SmStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiSmStudentPanelController extends Controller
{
    public function studentTeacherApi(Request $request, $user_id, $record_id)
    {

        $member_id = SmStudent::where('user_id', $user_id)->value('id');
        $record = StudentRecord::where('id', $record_id)->where('member_id', $member_id)->first();
        $assignTeacher = DB::table('sm_assign_subjects')
            ->leftjoin('sm_subjects', 'sm_subjects.id', '=', 'sm_assign_subjects.subject_id')
            ->leftjoin('sm_staffs', 'sm_staffs.id', '=', 'sm_assign_subjects.teacher_id')

            ->distinct()
            ->select('sm_staffs.full_name', 'sm_staffs.email', 'sm_staffs.mobile')
            ->where('sm_assign_subjects.age_group_id', '=', $record->age_group_id)
            ->where('sm_assign_subjects.mgender_id', '=', $record->mgender_id)
            ->get();

        $class_teacher = DB::table('sm_class_teachers')
            ->join('sm_assign_class_teachers', 'sm_assign_class_teachers.id', '=', 'sm_class_teachers.assign_class_teacher_id')
            ->join('sm_staffs', 'sm_class_teachers.teacher_id', '=', 'sm_staffs.id')
            ->where('sm_assign_class_teachers.age_group_id', '=', $record->age_group_id)
            ->where('sm_assign_class_teachers.mgender_id', '=', $record->mgender_id)
            ->where('sm_assign_class_teachers.active_status', '=', 1)
            ->select('full_name')
            ->first();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['teacher_list'] = $assignTeacher->toArray();
            $data['class_teacher'] = $class_teacher;
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
    public function saas_studentTeacherApi(Request $request, $church_id, $user_id, $record_id)
    {
        $member_id = SmStudent::where('user_id', $user_id)->where('church_id', $church_id)->value('id');
        $record = StudentRecord::where('id', $record_id)
        ->where('member_id', $member_id)
        ->where('church_id', $church_id)
        ->first();
        $assignTeacher = DB::table('sm_assign_subjects')
            ->leftjoin('sm_subjects', 'sm_subjects.id', '=', 'sm_assign_subjects.subject_id')
            ->leftjoin('sm_staffs', 'sm_staffs.id', '=', 'sm_assign_subjects.teacher_id')
            ->distinct()
            ->select('sm_staffs.full_name', 'sm_staffs.email', 'sm_staffs.mobile')
            ->where('sm_assign_subjects.age_group_id', '=', @$record->age_group_id)
            ->where('sm_assign_subjects.mgender_id', '=', @$record->mgender_id)
            ->get();

        $class_teacher = DB::table('sm_class_teachers')
            ->join('sm_assign_class_teachers', 'sm_assign_class_teachers.id', '=', 'sm_class_teachers.assign_class_teacher_id')
            ->join('sm_staffs', 'sm_class_teachers.teacher_id', '=', 'sm_staffs.id')
            ->where('sm_assign_class_teachers.age_group_id', '=', @$record->age_group_id)
            ->where('sm_assign_class_teachers.mgender_id', '=', @$record->mgender_id)
            ->where('sm_assign_class_teachers.active_status', '=', 1)
            ->select('full_name')
            ->first();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['teacher_list'] = $assignTeacher->toArray();
            $data['class_teacher'] = $class_teacher;
            return ApiBaseMethod::sendResponse($data, null);
        }
    }
}
