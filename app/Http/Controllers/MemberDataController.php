<?php

namespace App\Http\Controllers;

use App\SmClass;
use Carbon\Carbon;
use App\Models\MemberData;
use App\SmStudent;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\SmAcademicYear;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
class MemberDataController extends Controller
{
    public function updateAges()
    {
        
        $memberData = MemberData::all();

        foreach ($memberData as $member) {
            // Calculate age using Carbon
            $age = Carbon::parse($member->date_of_birth)->age;

            // Find the corresponding SmStudent record
            $change_member_id = SmStudent::where('id', $member->id)->first();

            // Determine the new status and prefix based on age
            if ($age < 12) {
                $newStatus = '1'; // 1 for Children's Service
                $prefix = 'PCGA';
            } elseif ($age >= 12 && $age <= 18) {
                $newStatus = '2'; // 2 for Junior Youth (J.Y.)
                $prefix = 'PCGA';
            } elseif ($age >= 18 && $age <= 30) {
                $newStatus = '3'; // 3 for Young People's Guild (Y.P.G.)
                $prefix = 'PCGA';
            } elseif ($age >= 31 && $age <= 40) {
                $newStatus = '4'; // 4 for Young Adults Fellowship
                $prefix = 'PCGA';
            } else {
                $newStatus = '5'; // 5 for Men's and Women's Fellowship
                $prefix = 'PCGA';
            }

            // Update registration_no in SmStudent model
            $newMemberId = $prefix . substr($change_member_id->registration_no, 4);
            $change_member_id->registration_no = $newMemberId;
            $change_member_id->save(); // Save the changes


                // Debugging: Log the values for verification
             //   info("Member ID: {$member->id}, Age: $age, New Status: $newStatus, New Admission No: $newMemberId");

                // Update age_group_id in student_records table
                $updatedRecords = DB::table('student_records')
                    ->where('member_id', $member->id)
                    ->update(['age_group_id' => $newStatus]);
    
                // Debugging: Log the number of updated records
               // info("Updated student_records: $updatedRecords");
            // Update age_group_id in student_records table
 

            // Update registration_no in sm_students table
            DB::table('sm_students')
                ->where('id', $member->id)
                ->update(['registration_no' => $newMemberId]);
        }

        try {
            $classes = SmClass::where('active_status', 1)
                ->where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $students = SmStudent::where('church_year_id', getAcademicId())
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $sessions = SmAcademicYear::where('active_status', 1)
                ->where('church_id', Auth::user()->church_id)
                ->get();

            return view('backEnd.studentInformation.student_details', compact('classes', 'sessions'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }


        return view('backEnd.studentInformation.student_details', compact('classes', 'sessions'));
    }
}