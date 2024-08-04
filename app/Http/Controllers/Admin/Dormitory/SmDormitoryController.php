<?php

namespace App\Http\Controllers\Admin\Dormitory;

use App\SmClass;
use App\SmStudent;
use App\YearCheck;
use App\ApiBaseMethod;
use App\SmDormitoryList;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\StudentInfo\SmStudentReportController;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;

class SmDormitoryController extends Controller
{
    public function __construct()
	{
        $this->middleware('PM');
        // User::checkAuth();
	}


    public function studentDormitoryReport(Request $request)
    {
        try{
            $classes = SmClass::get();
            $dormitories = SmDormitoryList::get();
            $students = SmStudent::with('class','section','parents','dormitory','room')
                          ->where('dormitory_id', '!=', "")->limit(100)->get();
                      
            return view('backEnd.dormitory.student_dormitory_report', compact('classes', 'students', 'dormitories'));
        }catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }

    }


    public function studentDormitoryReportSearch(Request $request)
    {
        try{
            $data = [];
            $stdent_ids = [];
            $students = SmStudent::query();
            $student_records = StudentRecord::query();
            if(moduleStatusCheck('University')){
                $member_ids = universityFilter($student_records, $request)
                            ->groupBy('member_id')->get('member_id');
              foreach($member_ids as $record){
                  $stdent_ids[]= $record->member_id;
              }
            }else{
                $member_ids = SmStudentReportController::classSectionStudent($request);
            }
           
            if ($request->dormitory != "") {
                $students->where('dormitory_id', $request->dormitory);
            } else {
                $students->where('dormitory_id', '!=', '');
            }
            $students = $students->whereIn('id', $member_ids)->with('class','section','parents','dormitory','room')->where('church_id',Auth::user()->church_id)->get();

            $data['classes'] =SmClass::get();
            $data['dormitories'] =SmDormitoryList::get();
            $data['students'] = $students;
            $data['age_group_id'] = $request->class;
            $data['mgender_id'] =$request->section;
            $data['dormitory_id'] = $request->dormitory;
            if (moduleStatusCheck('University')) {
                $interface = App::make(UnCommonRepositoryInterface::class);
                $data += $interface->getCommonData($request);
            }
            return view('backEnd.dormitory.student_dormitory_report',$data);
        }catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
