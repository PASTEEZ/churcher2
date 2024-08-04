<?php

namespace App\Http\Controllers\Admin\FeesCollection;

use App\SmClass;
use App\SmStudent;
use App\ApiBaseMethod;
use App\SmFeesPayment;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\Http\Controllers\Controller;
use App\Models\DirectFeesInstallmentAssign;
use App\Models\DireFeesInstallmentChildPayment;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Modules\University\Entities\UnFeesInstallmentAssign;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;

class SmCollectionReportController extends Controller
{
 
    public function transactionReport(Request $request)
    {
        try {
            $classes = SmClass::get();
            
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse(null, null);
            }
            return view('backEnd.feesCollection.transaction_report',compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function transactionReportSearch(Request $request)
    {
        $rangeArr = $request->date_range ? explode('-', $request->date_range) : "".date('m/d/Y')." - ".date('m/d/Y')."";
        $date_from = null;
        $date_to = null;
        if($request->date_range){
            $date_from = new \DateTime(trim($rangeArr[0]));
            $date_to =  new \DateTime(trim($rangeArr[1]));
        }
        $classes = [];
        try {
            if(moduleStatusCheck('University')){
                $StudentRecord = StudentRecord::query();
                $students = universityFilter($StudentRecord, $request)->get();

                $fees_payments = UnFeesInstallmentAssign::with('payments')->whereIn('active_status', [1,2])
                            ->whereIn('member_id', $students->pluck('member_id'))
                            ->where('un_semester_label_id',$request->un_semester_label_id)
                            ->where('church_id',auth()->user()->church_id)
                            ->when($request->date_range, function ($q) use ($date_from, $date_to) {
                                $q->where('payment_date',  '>=', $date_from);
                                $q->where('payment_date',  '<=', $date_to);
                            })
                            ->where('paid_amount', '>', 0)
                            ->get();
            }elseif(directFees()){
                $classes = SmClass::get();
                $allStudent = StudentRecord::when($request->class, function ($q) use ($request) {
                    $q->where('age_group_id', $request->class);
                })
                ->when($request->section, function ($q) use ($request){
                    $q->where('mgender_id',$request->section);
                })
                ->where('church_year_id', getAcademicId())
                ->get();
                $fees_payments = DireFeesInstallmentChildPayment::where('active_status', 1)
                            ->whereIn('record_id', $allStudent->pluck('id'))
                            ->when($request->date_range, function ($q) use ($date_from, $date_to) {
                                $q->where('payment_date',  '>=', $date_from);
                                $q->where('payment_date',  '<=', $date_to);
                            })
                            ->where('paid_amount', '>', 0)
                            ->where('church_id',auth()->user()->church_id)
                            ->get();
            }else{
                $classes = SmClass::get();
                if($request->date_range ){
                    if($request->class){
                        $students=StudentRecord::where('age_group_id',$request->class)
                                            ->get();
    
                        $fees_payments = SmFeesPayment::where('active_status',1)
                                        ->whereIn('member_id', $students->pluck('member_id'))
                                        ->where('payment_date', '>=', $date_from)
                                        ->where('payment_date', '<=', $date_to)
                                        ->where('church_id',Auth::user()->church_id)
                                        ->get();
                        $fees_payments = $fees_payments->groupBy('member_id');
                    }else{
                        $fees_payments = SmFeesPayment::where('active_status',1)
                                    ->where('payment_date', '>=', $date_from)
                                    ->where('payment_date', '<=', $date_to)
                                    ->where('church_id',Auth::user()->church_id)
                                    ->get();
                        $fees_payments = $fees_payments->groupBy('member_id');
                    }
                }

                if($request->class && $request->section){
                    $students=StudentRecord::where('age_group_id',$request->class)
                            ->where('mgender_id',$request->section)
                            ->where('church_id',Auth::user()->church_id)
                            ->where('church_year_id', getAcademicId())
                            ->get();
    
                    $fees_payments = SmFeesPayment::where('active_status',1)
                                    ->whereIn('member_id', $students->pluck('member_id'))
                                    ->where('payment_date', '>=', $date_from)
                                    ->where('payment_date', '<=', $date_to)
                                    ->where('church_id',Auth::user()->church_id)
                                    ->get();
                   $fees_payments = $fees_payments->groupBy('member_id');
                   
                }

            }
            if(moduleStatusCheck('University')){
                // $data = $this->unCommonRepository->oldValueSelected($request);
                return view('backEnd.feesCollection.transaction_report', compact('fees_payments', 'date_to', 'date_from'));
            }
            elseif(directFees()){
                // $data = $this->unCommonRepository->oldValueSelected($request);
                return view('backEnd.feesCollection.transaction_report', compact('fees_payments', 'date_to', 'date_from','classes'));
            }
            else{
                return view('backEnd.feesCollection.transaction_report', compact('fees_payments','classes', 'date_to', 'date_from'));
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
