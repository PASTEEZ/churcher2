<?php

namespace App\Http\Controllers\Admin\Academics;

use App\SmClass;
use App\SmStaff;
use App\SmSection;
use App\ApiBaseMethod;
use App\SmClassTeacher;
use Illuminate\Http\Request;
use App\SmAssignClassTeacher;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Events\ClassTeacherGetAllStudent;
use App\Http\Requests\Admin\Academics\SmAssignClassTeacherRequest;

class SmAssignClassTeacherController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
    }

    public function index(Request $request)
    {
        try {
            $classes = SmClass::get();
            $teachers = SmStaff::status()->where(function($q)  {                
                $q->where('role_id', 4)->orWhere('previous_role_id', 4);             
            })->get();
            $assign_class_teachers = SmAssignClassTeacher::with('class', 'section', 'classTeachers')->where('church_year_id', getAcademicId())->status()->orderBy('age_group_id', 'ASC')->orderBy('mgender_id', 'ASC')->get();

            return view('backEnd.academics.assign_class_teacher', compact('classes', 'teachers', 'assign_class_teachers'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function store(SmAssignClassTeacherRequest $request)
    {
        DB::beginTransaction();
        try {
            $assigned_class_teacher = SmAssignClassTeacher::where('active_status', 1)
                ->where('age_group_id', $request->class)->where('mgender_id', $request->section)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->first();

            if (empty($assigned_class_teacher)) {
                $assign_class_teacher = new SmAssignClassTeacher();
                $assign_class_teacher->age_group_id = $request->class;
                $assign_class_teacher->mgender_id = $request->section;
                $assign_class_teacher->church_id = Auth::user()->church_id;
                $assign_class_teacher->church_year_id = getAcademicId();
                $assign_class_teacher->save();
               
                foreach ($request->teacher as $teacher) {
                    $class_teacher = new SmClassTeacher();
                    $class_teacher->assign_class_teacher_id = $assign_class_teacher->id;
                    $class_teacher->teacher_id = $teacher;
                    $class_teacher->church_id = Auth::user()->church_id;
                    $class_teacher->church_year_id = getAcademicId();
                    $class_teacher->save();
                    event(new ClassTeacherGetAllStudent($assign_class_teacher, $class_teacher));

                }

                DB::commit();

                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            } else {
                Toastr::warning('Class Teacher already assigned.', 'Warning');
                return redirect()->back();
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function edit(Request $request, $id)
    {

        try {
            $classes = SmClass::get();
            $teachers = SmStaff::status()->where(function($q)  {                
                $q->where('role_id', 4)->orWhere('previous_role_id', 4);             
            })->get();
            $assign_class_teachers = SmAssignClassTeacher::with('class', 'section', 'classTeachers')->where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $assign_class_teacher = SmAssignClassTeacher::find($id);
            $sections = SmSection::get();

            $teacherId = array();
            foreach ($assign_class_teacher->classTeachers as $classTeacher) {
                $teacherId[] = $classTeacher->teacher_id;
            }

            return view('backEnd.academics.assign_class_teacher', compact('assign_class_teacher', 'classes', 'teachers', 'assign_class_teachers', 'sections', 'teacherId'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function update(SmAssignClassTeacherRequest $request, $id)
    {

        $is_duplicate = SmAssignClassTeacher::where('church_id', Auth::user()->church_id)->where('church_year_id', getAcademicId())->where('age_group_id', $request->class)->where('mgender_id', $request->section)->where('id', '!=', $request->id)->first();
        if ($is_duplicate) {
            Toastr::warning('Duplicate entry found!', 'Warning');
            return redirect()->back();
        }
        DB::beginTransaction();

        try {
            SmClassTeacher::where('assign_class_teacher_id', $request->id)->delete();

            $assign_class_teacher = SmAssignClassTeacher::find($request->id);
            $assign_class_teacher->age_group_id = $request->class;
            $assign_class_teacher->church_year_id = getAcademicId();
            $assign_class_teacher->mgender_id = $request->section;
            $assign_class_teacher->save();
            $assign_class_teacher_collection = $assign_class_teacher;
            $assign_class_teacher->toArray();

            foreach ($request->teacher as $teacher) {
                $class_teacher = new SmClassTeacher();
                $class_teacher->assign_class_teacher_id = $assign_class_teacher->id;
                $class_teacher->teacher_id = $teacher;
                $class_teacher->church_id = Auth::user()->church_id;
                $class_teacher->church_year_id = getAcademicId();
                $class_teacher->save();
                event(new ClassTeacherGetAllStudent($assign_class_teacher_collection, $class_teacher, 'update'));

            }

            DB::commit();
            Toastr::success('Operation successful', 'Success');
            return redirect('assign-class-teacher');
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }

        Toastr::error('Operation Failed', 'Failed');
        return redirect()->back();
    }

    public function destroy(Request $request, $id)
    {
        try {
            $id_key = 'assign_class_teacher_id';
            $tables = \App\tableList::getTableList($id_key, $id);

            try {
                DB::beginTransaction();

                $delete_query = SmClassTeacher::where('assign_class_teacher_id', $id)->delete();
                $delete_query = SmAssignClassTeacher::destroy($id);

                DB::commit();
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollback();
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error($msg, 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
