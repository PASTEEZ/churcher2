<?php

namespace Database\Seeders\Student;

use App\SmClassSection;
use App\SmStudentAttendance;
use App\Models\StudentRecord;
use Illuminate\Database\Seeder;

class SmStudentAttendanceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=1)
    {
        $days = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
        $classSection = SmClassSection::where('church_id',$church_id)->where('church_year_id', $church_year_id)->first();
        $students = StudentRecord::where('age_group_id', $classSection->age_group_id)
                                ->where('mgender_id', $classSection->mgender_id)
                                ->where('church_id',$church_id)
                                ->where('church_year_id', $church_year_id)
                                ->get();
        for ($i = 1; $i <= $days; $i++) {
            foreach ($students as $record) {
                if ($i <= 9) {
                    $d = '0' . $i;
                } else{
                    $d = $i;
                }
                $date = date('Y') . '-' . date('m') . '-' . $d;
                $sa = new SmStudentAttendance();
                $sa->member_id = $record->member_id;
                $sa->student_record_id = $record->member_id;
                $sa->age_group_id = $record->age_group_id;
                $sa->mgender_id = $record->mgender_id;
                $sa->attendance_type = 'P';
                $sa->notes = 'Sample Attendance for Student';
                $sa->attendance_date = $date;
                $sa->church_id = $church_id;
                $sa->church_year_id = $church_year_id;
                $sa->save();
            }
        }
    }
}
