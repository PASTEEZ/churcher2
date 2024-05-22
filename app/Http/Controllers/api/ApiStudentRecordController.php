<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiStudentRecordController extends Controller
{
    //
    public function getRecord($member_id)
    {
        $records = studentRecords(null, $member_id)->get()->map(function ($record) {
            return[
                'id'=>$record->id,
                'member_id'=>$record->member_id,
                'full_name'=>$record->student->full_name,
                'class'=>$record->class->age_group_name,
                'section'=>$record->section->mgender_name,
                'age_group_id'=>$record->age_group_id,
                'mgender_id'=>$record->mgender_id,
                'is_default'=>$record->is_default,
                'is_promote'=>$record->is_promote,
                'roll_no'=>$record->roll_no,
                'session_id'=>$record->session_id,
                'church_year_id'=>$record->church_year_id,
                'church_id'=>$record->church_id,
            ];
        });
        return response()->json(compact('records'));
    }
    public function getRecordSaas($church_id, $member_id)
    {
        $records = studentRecords(null, $member_id, $church_id)->get()->map(function ($record) {
            return[
                'id'=>$record->id,
                'member_id'=>$record->member_id,
                'full_name'=>$record->student->full_name,
                'class'=>$record->class->age_group_name,
                'section'=>$record->section->mgender_name,
                'is_default'=>$record->is_default,
                'is_promote'=>$record->is_promote,
                'roll_no'=>$record->roll_no,
                'session_id'=>$record->session_id,
                'church_year_id'=>$record->church_year_id,
                'church_id'=>$record->church_id,
            ];
        });
        return response()->json(compact('records'));
    }
}
