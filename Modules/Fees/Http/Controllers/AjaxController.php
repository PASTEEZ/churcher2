<?php

namespace Modules\Fees\Http\Controllers;

use App\SmSection;
use App\SmAddIncome;
use App\SmBankAccount;
use App\SmClassSection;
use App\SmAssignSubject;
use App\SmPaymentMethhod;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use Illuminate\Routing\Controller;
use Modules\Fees\Entities\FmFeesType;
use Illuminate\Support\Facades\Artisan;
use Modules\Fees\Entities\FmFeesInvoice;
use App\Scopes\StatusAcademicSchoolScope;
use App\SmPaymentGatewaySetting;
use Illuminate\Contracts\Support\Renderable;
use Modules\Fees\Entities\FmFeesTransaction;

class AjaxController extends Controller
{
    public function feesViewPayment(Request $request)
    {
        $feesinvoice = FmFeesInvoice::find($request->invoiceId);
        $feesTranscations = FmFeesTransaction::where('fees_invoice_id', $request->invoiceId)
                        ->where('paid_status', 'approve')
                        ->where('church_id', auth()->user()->church_id)
                        ->get();
        $paymentMethods = SmPaymentMethhod::whereIn('method', ['Cash','Cheque','Bank'])->get();
        $banks = SmBankAccount::where('church_id', auth()->user()->church_id)->get();
        return view('fees::feesInvoice.viewPayment', compact('feesinvoice', 'feesTranscations','paymentMethods','banks'));
    }

    public function ajaxSelectStudent(Request $request)
    {
        try {
            $allStudents = StudentRecord::with('studentDetail','section')
                        ->where('age_group_id', $request->classId)
                        ->where('church_id', auth()->user()->church_id)
                        ->where('church_year_id', getAcademicId())
                        ->get();
            return response()->json([$allStudents]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

    public function ajaxSelectFeesType(Request $request)
    {
        try{
            $type = substr($request->type,0,3);
            if($type == "grp"){
                $groupId = substr($request->type,3);
                $feesGroups = FmFeesType::where('fees_group_id',$groupId)
                            ->where('type','fees')
                            ->get();
                return view('fees::_allFeesType',compact('feesGroups'));
            }else{
                $typeId = substr($request->type,3);
                $feesType = FmFeesType::where('id',$typeId)
                            ->where('type','fees')
                            ->first();
            return view('fees::_allFeesType',compact('feesType'));
            }   
        }catch(\Exception $e) {
            return response()->json("", 404);
        }
    }

    public function ajaxGetAllSection(Request $request)
    {
        try{
            if (teacherAccess()) {
                $sectionIds = SmAssignSubject::where('age_group_id', '=', $request->age_group_id)
                            ->where('teacher_id', auth()->user()->staff->id)
                            ->where('church_id', auth()->user()->church_id)
                            ->where('church_year_id', getAcademicId())
                            ->groupby(['age_group_id','mgender_id'])
                            ->withoutGlobalScope(StatusAcademicSchoolScope::class)
                            ->get();
            } else {
                $sectionIds = SmClassSection::where('age_group_id', '=', $request->age_group_id)
                                ->where('church_id', auth()->user()->church_id)
                                ->withoutGlobalScope(StatusAcademicSchoolScope::class)
                                ->get();
            }
            $promote_sections = [];
            foreach ($sectionIds as $sectionId) {
                $promote_sections[] = SmSection::where('id', $sectionId->mgender_id)
                                    ->withoutGlobalScope(StatusAcademicSchoolScope::class)
                                    ->first(['id','mgender_name']);
            }
            return response()->json([$promote_sections]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

    public function ajaxSectionAllStudent(Request $request)
    {
        try{
            $allStudents = StudentRecord::with('studentDetail','section')
                            ->where('age_group_id', $request->age_group_id)
                            ->where('mgender_id', $request->mgender_id)
                            ->where('church_id', auth()->user()->church_id)
                            ->where('church_year_id', getAcademicId())
                            ->get();
            return response()->json([$allStudents]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

    public function ajaxGetAllStudent(Request $request)
    {
        try{
            $allStudents = StudentRecord::with('studentDetail','section')
                            ->where('age_group_id', $request->age_group_id)
                            ->where('church_id', auth()->user()->church_id)
                            ->where('church_year_id', getAcademicId())
                            ->get();
            return response()->json([$allStudents]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }

    public function changeMethod(Request $request)
    {
        try{
            $transcation = FmFeesTransaction::find($request->feesInvoiceId);
            $transcation->payment_method= $request->change_method;
            $transcation->update();

            $payment_method = SmPaymentMethhod::where('method', $request->change_method)->first();
            
            $incomes = SmAddIncome::where('fees_collection_id', $request->feesInvoiceId)->get();

            foreach($incomes as $income){
                $updateIncome = SmAddIncome::find($income->id);
                $updateIncome->payment_method = $payment_method->id;
                $updateIncome->update();
            }
            return response()->json(['sucess']);
        } catch (\Exception $e) {
            return response()->json('Error', $e->getMessage());
        }
    }

    public function serviceCharge(Request $request)
    {     
        $service_charge = serviceCharge($request->gateway) ;
        $service_charge_amount =  number_format(chargeAmount($request->gateway, $request->amount), 2, '.', '') ;
   
        return response()->json([
            'service_charge'=>$service_charge,         
            'service_charge_amount'=>$service_charge_amount,                  
        ]);
    }
    public function migration()
    {
        Artisan::call('migrate');
        return "Sucess";
    }
}
