<?php

namespace App\Imports;

use App\SmStudent;
use App\StudentAttendanceBulk;
use App\SmStudentAttendanceImport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use DB;

class StudentAttendanceImport implements ToModel, WithStartRow, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public $class;
    public $section;
    public function __construct($class,$section)
    {
        $this->class = $class;
        $this->section = $section;
    }
    public function model(array $row)
    {

        $student = SmStudent::select('id')->where('registration_no', $row['registration_no'])->where('church_id', Auth::user()->church_id)->first();
        if ($student != "") {
            return new StudentAttendanceBulk([
            "attendance_date" => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['attendance_date'])->format('Y-m-d'),
            "attendance_type" => $row['attendance_type'],
            "note" => $row['note'],
            "member_id" => $student->id,
            "age_group_id" => $this->class,
            "mgender_id" => $this->section,
            "church_id" => Auth::user()->church_id,
        ]);
        }
        
    }

    public function startRow(): int
    {
        return 2;
    }

    public function headingRow(): int
    {
        return 1;
    }

}