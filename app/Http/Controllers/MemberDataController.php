<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\MemberData;
use Illuminate\Support\Facades\DB;


use Illuminate\Http\Request;

class MemberDataController extends Controller
{
    public function updateAges() {
       
        $memberData = MemberData::all();
   
        foreach ($memberData as $member) {
            // Calculate age using Carbon
            $age = Carbon::parse($member->date_of_birth)->age;
    

            if ($age < 12) {
                $newStatus = '1'; // 1 for Children's Service
            } else if ($age >= 12 && $age <= 18) {
                $newStatus = '2'; // 2 for Junior Youth (J.Y.)
            } 
            else if ($age >= 18 && $age <= 30) {
                $newStatus = '3'; // 3 for Young People's Guild (Y.P.G.)
            } 
            else if ($age >= 31 && $age <= 40) {
                $newStatus = '4';  // 4 for Young Adults Fellowship
            } else {
                $newStatus = '5'; // 5 for Men's and Women's Fellowship
            }

            DB::table('student_records')
            ->where('student_id', $member->id) // Adjust the condition based on table structure
            ->update(['class_id' => $newStatus], );


           // DB::table('student_records')
            //->where('id', $member->id) // Adjust the condition based on table structure
           // ->update(['ages' => $age] );
         
           //return view('your.view.name', ['memberData' => $memberData]);
    
           
        }
       
 
        echo "Ages updated successfully"; 
    }




}
