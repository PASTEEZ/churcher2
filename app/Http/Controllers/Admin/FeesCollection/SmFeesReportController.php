<?php

namespace App\Http\Controllers\Admin\FeesCollection;

use App\SmClass;
use App\SmStudent;
use App\SmFeesAssign;
use App\SmFeesMaster;
use App\ApiBaseMethod;
use App\SmFeesPayment;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use App\Http\Controllers\Admin\StudentInfo\SmStudentReportController;

class SmFeesReportController extends Controller
{
    public function balanceFeesReport(Request $request)
    {
        try {
            $classes = SmClass::get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.feesCollection.balance_fees_report', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function balanceFeesSearch(Request $request)
    {
    
       if(!moduleStatusCheck('University')){
            $request->validate([
                'class' => 'required',
                'section' => 'required'
            ]);
       }
        
     
        try {

            if(moduleStatusCheck('University')){
                $records = StudentRecord::query();
                $records->where('church_id',auth()->user()->church_id);
                $records = universityFilter( $records,$request);
                $student_records = $records->whereHas('student')->with('student')->get()->unique('member_id');
                if($student_records){
                    return view('backEnd.feesCollection.balance_fees_report', compact('student_records'));
                }else{
                    Toastr::error('No Data Found', 'Failed');
                    return redirect()->back();
                }
                
            }
            $member_ids = SmStudentReportController::classSectionStudent($request);
            $students = SmStudent::with('parents', 'feesAssign', 'feesAssign.feesGroupMaster', 'feesAssign.feesPayments', 'feesPayment')->whereIn('id', $member_ids)->get();
            $balance_students = [];

            $data = [];
            $fees_masters = SmFeesMaster::where('active_status', 1)->where('church_id', Auth::user()->church_id)->get();
            foreach ($students as $key => $student) {
                $total_balance = 0;
                $total_discount = 0;
                $total_amount = 0;
                foreach ($fees_masters as $fees_master) {

                    $due_date = strtotime($fees_master->date);
                    $now = strtotime(date('Y-m-d'));
                    if ($due_date > $now) {
                        continue;
                    }
                    
                    $total_discount += $student->feesPayment->where('active_status',1)->where('fees_type_id', $fees_master->fees_type_id)->sum('discount_amount');
                    $total_balance += $student->feesPayment->where('active_status',1)->where('fees_type_id', $fees_master->fees_type_id)->sum('amount');
                    $total_amount += $fees_master->amount;

                }
                $total_paid = $total_balance + $total_discount;
                if ($total_amount > $total_paid) {
                    $balance_students[] = $student;
                    $data[$key]['student'] = $student;
                    
                    $data[$key]['totalDiscount'] = $student->feesAssign->sum('applied_discount');
                    

                    $totalFine = 0;
                    $totalDeposit = 0;
                    $totalFees = 0;
                    foreach ($student->feesAssign as $feesAssign) {
                        $totalFees += $feesAssign->feesGroupMaster->amount;
                        $totalFine += $feesAssign->feesPayments->where('active_status',1)->sum('fine');
                        $totalDeposit += $feesAssign->feesPayments->where('active_status',1)->sum('amount');
                    }

                    $data[$key]['totalFine'] = $totalFine;
                    $data[$key]['totalDeposit'] = $totalDeposit;
                    $data[$key]['totalFees'] = $totalFees;

                }


            }

         
            // return $master_ids;
            $age_group_id = $request->class;
            $mgender_id = $request->section;
            $classes = SmClass::get();
            //  return $balance_students;
            $clas = $classes->find($request->class);
            return view('backEnd.feesCollection.balance_fees_report', compact('classes', 'balance_students', 'age_group_id', 'clas', 'data', 'mgender_id'));

         
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
