<?php

namespace Database\Seeders\HumanResources;

use App\SmStaff;
use App\SmStaffAttendence;
use Illuminate\Database\Seeder;

class SmStaffAttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id)
    {
        $staffs = SmStaff::where('church_id',$church_id)->get(['id','user_id']);
        $days = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
        $status = ['P','L','A'];
        for ($i = 1; $i <= $days; $i++) {
            foreach ($staffs as $staff) {
                if ($i <= 9) {
                    $d = '0' . $i;
                }
                $date = date('Y') . '-' . date('m') . '-' . $d;                    

                $sa = new SmStaffAttendence;
                $sa->staff_id = $staff->id;
                $sa->attendence_type = array_rand($status);
                $sa->notes = 'Sample Attendance for Staff';
                $sa->attendence_date = $date;
                $sa->church_id = $church_id;
                $sa->church_year_id = $church_year_id;
                $sa->save();
            }
        }
    }
}
