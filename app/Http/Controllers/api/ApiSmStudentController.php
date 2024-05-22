<?php

namespace App\Http\Controllers\api;

use App\SmStudent;
use App\ApiBaseMethod;
use App\Scopes\SchoolScope;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ApiSmStudentController extends Controller
{
    public function searchStudent(Request $request)
    {

        $age_group_id = $request->class;
        $mgender_id = $request->section;
        $name = $request->name;
        $roll_no = $request->roll_no;

        $member_ids = StudentRecord::when($request->church_year, function ($query) use ($request) {
            $query->where('church_year_id', $request->church_year);
        })
            ->when($request->class, function ($query) use ($request) {
                $query->where('age_group_id', $request->class);
            })
            ->when($request->section, function ($query) use ($request) {
                $query->where('mgender_id', $request->section);
            })
            ->when($request->roll_no, function ($query) use ($request) {
                $query->where('roll_no', $request->roll_no);
            })
            ->when(!$request->church_year, function ($query) use ($request) {
                $query->where('church_year_id', getAcademicId());
            })
            ->where('church_id', auth()->user()->church_id)
            ->groupBy('member_id')->pluck('member_id')->toArray();

        $studentDetails = SmStudent::whereIn('id', $member_ids)
            ->when($request->name, function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->name . '%');
            })->get();
        // ->select('sm_students.id', 'student_photo', 'full_name', 'roll_no', 'user_id');

        $students = [];
        foreach ($studentDetails as $student) {

            $class_sec = [];
            foreach ($student->studentRecords as $classSec) {
                $class_sec[] = $classSec->class->age_group_name . '(' . $classSec->section->mgender_name . '), ';
            }
            if ($request->class) {
                $sections = [];
                $class = $student->recordClass ? $student->recordClass->class->age_group_name : '';
                if ($request->section) {
                    $sections = $student->recordSection != "" ? $student->recordSection->section->mgender_name : "";
                } else {
                    foreach ($student->recordClasses as $section) {
                        $sections[] = $section->section->mgender_name;
                    }

                }
                $class_sec = $class . '(' . $sections . '), ';
            }

            $data['id'] = $student->id;
            $data['photo'] = $student->student_photo;
            $data['full_name'] = $student->full_name;
            $data['user_id'] = $student->user_id;
            $data['class_section'] = $class_sec;

            $students[] = $data;
        }

        if (count($studentDetails)) {
            $msg = "Student Found";
        } else {
            $msg = "Student Not Found";
        }

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['students'] = $students;

            return ApiBaseMethod::sendResponse($data, $msg);
        }
    }
    public function saas_searchStudent(Request $request, $church_id)
    {
        $member_ids = StudentRecord::when($request->church_year, function ($query) use ($request) {
            $query->where('church_year_id', $request->church_year);
        })
            ->when($request->class, function ($query) use ($request) {
                $query->where('age_group_id', $request->class);
            })
            ->when($request->section, function ($query) use ($request) {
                $query->where('mgender_id', $request->section);
            })
            ->when($request->roll_no, function ($query) use ($request) {
                $query->where('roll_no', $request->roll_no);
            })
            ->when(!$request->church_year, function ($query) use ($request) {
                $query->where('church_year_id', getAcademicId());
            })
            ->where('church_id', $church_id)
            ->groupBy('member_id')->pluck('member_id')->toArray();

        $studentDetails = SmStudent::whereIn('id', $member_ids)
            ->when($request->name, function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->name . '%');
            })->withOutGlobalScope(SchoolScope::class)->get();

        $students = [];
        foreach ($studentDetails as $student) {
            $class_sec = [];
            foreach ($student->studentRecords as $classSec) {
                $class_sec[] = $classSec->class->age_group_name . '(' . $classSec->section->mgender_name . '), ';
            }
            if ($request->class) {
                $sections = [];
                $class = $student->recordClass ? $student->recordClass->class->age_group_name : '';
                if ($request->section) {
                    $sections = $student->recordSection != "" ? $student->recordSection->section->mgender_name : "";
                } else {
                    foreach ($student->recordClasses as $section) {
                        $sections[] = $section->section->mgender_name;
                    }

                }
                $class_sec = $class . '(' . $sections . '), ';
            }

            $data['id'] = $student->id;
            $data['photo'] = $student->student_photo;
            $data['full_name'] = $student->full_name;
            $data['user_id'] = $student->user_id;
            $data['class_section'] = $class_sec;

            $students[] = $data;
        }
    
        if (count($studentDetails)) {
            $msg = "Student Found";
        } else {
            $msg = "Student Not Found";
        }

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['students'] = $students;

            return ApiBaseMethod::sendResponse($data, $msg);
        }
    }
}
