<?php

namespace Database\Seeders\Leave;

use App\User;
use App\SmStaff;
use Carbon\Carbon;
use App\SmLeaveType;
use App\SmLeaveDefine;
use Illuminate\Database\Seeder;
use Modules\RolePermission\Entities\InfixRole;
use App\Http\Requests\Admin\Leave\SmLeaveRequest;

class SmLeaveTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=4)
    {
        $school_academic= [
            'church_id'=>$church_id,
            'church_year_id'=>$church_year_id,
        ];
        $roles =InfixRole::get();
        $staffs = SmStaff::where('church_id', $church_id)->get(['id', 'full_name']);
        SmLeaveType::factory()->times($count)->create($school_academic)->each(function ($leaveTypes) use ($roles, $church_id, $church_year_id, $staffs) {
            foreach ($roles as $key => $value) {
                $users = User::where('role_id', $value->id)->get();
                foreach ($users as $user) {
                    $store = new SmLeaveDefine();
                    $store->role_id = $value->id;
                    $store->user_id = $user->id;
                    $store->type_id = $leaveTypes->id;
                    $store->days = $leaveTypes->total_days;
                    $store->church_id = $church_id;
                    $store->church_year_id = $church_year_id;
                    $store->save();
                }
            }
            foreach ($staffs as $staff) {

                $storeRequest = new SmLeaveRequest();
                $storeRequest->type_id = $leaveTypes->id;
                $storeRequest->leave_define_id = 1;
                $storeRequest->staff_id = $staff->id;
                $storeRequest->role_id = 4;
                $storeRequest->apply_date = Carbon::now()->format('Y-m-d');
                $storeRequest->leave_from = Carbon::now()->format('Y-m-d');
                $storeRequest->leave_to = Carbon::now()->addDays(2)->format('Y-m-d');
                $storeRequest->reason = 'Seeder Leave';
                $storeRequest->note = 'Seeder Leave';
                $storeRequest->file = "public/uploads/leave_request/sample.pdf";
                $storeRequest->approve_status = "P";
                $storeRequest->church_id = $church_id;
                $storeRequest->church_year_id = $church_year_id;
                // $storeRequest->save();
            }

        });
    }
}
