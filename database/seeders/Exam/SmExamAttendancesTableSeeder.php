<?php

namespace Database\Seeders\Exam;

use App\SmExam;
use App\YearCheck;
use App\SmExamType;
use App\SmAssignSubject;
use App\SmExamAttendance;
use App\Models\StudentRecord;
use App\SmExamAttendanceChild;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class SmExamAttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id)
    {     
        $smExamTypes = SmExam::where('church_id', $church_id)->where('church_year_id', $church_year_id)->get();
        $assignSubjects = SmAssignSubject::where('church_id', $church_id)->where('church_year_id', $church_year_id)->get();
        foreach ($smExamTypes as $exam) {
            foreach ($assignSubjects as $subject) {
                $studentRecord = StudentRecord::where('church_id', $church_id)->where('church_year_id', $church_year_id)->where('age_group_id', $subject->age_group_id)->where('mgender_id', $subject->mgender_id)->get();
                $store = new SmExamAttendance();
                $store->exam_id = $exam->id;
                $store->subject_id = $subject->subject_id;
                $store->age_group_id = $subject->age_group_id;
                $store->mgender_id = $subject->mgender_id;
                $store->created_by = 1;
                $store->created_at = date('Y-m-d h:i:s');
                $store->save();
                foreach ($studentRecord as $record) {
                    $exam_attendance_child = new SmExamAttendanceChild();
                    $exam_attendance_child->exam_attendance_id = $store->id;
                    $exam_attendance_child->member_id = $record->member_id;
                    $exam_attendance_child->student_record_id = $record->id;
                    $exam_attendance_child->age_group_id = $record->age_group_id;
                    $exam_attendance_child->mgender_id = $record->mgender_id;
                    $exam_attendance_child->attendance_type = 'P';
                    $exam_attendance_child->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                    $exam_attendance_child->church_id = $church_id;
                    $exam_attendance_child->church_year_id = $church_year_id;
                    $exam_attendance_child->save();
                }
            }

        }
    }
}
