<?php

namespace App\Traits;

use App\SmFeesAssign;
use App\SmFeesMaster;
use App\SmFeesDiscount;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\Auth;
use App\Models\DirectFeesInstallment;
use App\Models\DirectFeesInstallmentAssign;

use function PHPUnit\Framework\isNull;

trait DirectFeesAssignTrait{

    public function assignDirectFees($record_id = null, $age_group_id = null,  $mgender_id = null, $master_id = null){
        if(is_null($record_id)){
            $fees_master = SmFeesMaster::find($master_id);
            $age_group_id = $fees_master->age_group_id;
            $mgender_id = $fees_master->mgender_id;
            $student_records = StudentRecord::query();
            $student_records = $student_records->where('is_promote',0)->where('age_group_id', $age_group_id);
            if($mgender_id != null){
                $student_records = $student_records->where('mgender_id', $mgender_id);
            }
            $student_records = $student_records->where('church_year_id',getAcademicId())->where('church_id',Auth::user()->church_id)->where('is_promote',0)->get();
        }
        else{
            $student_records = StudentRecord::where('id',$record_id)->get();
            $fees_master = SmFeesMaster::where('church_id',Auth::user()->church_id)
                                        ->where('church_year_id', getAcademicId())
                                        ->where('age_group_id',$age_group_id)
                                        ->when(!is_null($mgender_id), function ($q) use ($mgender_id) {
                                            $q->where('mgender_id', $mgender_id);
                                        })->latest()
                                        ->first();

        if(! $fees_master){
            $fees_master = SmFeesMaster::where('church_id',Auth::user()->church_id)
                                        ->where('church_year_id', getAcademicId())
                                        ->where('age_group_id',$age_group_id)
                                        ->latest()
                                        ->first();
            }
        }
        
        if(!$fees_master){
            return;
        }

        foreach($student_records as $studentRecord){
            $old_assign = SmFeesAssign::where('record_id',$studentRecord->id)->where('fees_master_id',$fees_master->id)->first();
           
            if($old_assign){
                $assign_fees = $old_assign;
            }else{
                $assign_fees = new SmFeesAssign();
            }
            $assign_fees->member_id = $studentRecord->member_id;
            $assign_fees->fees_amount = $fees_master->amount;
            $assign_fees->fees_master_id = $fees_master->id;
            $assign_fees->age_group_id = $studentRecord->age_group_id;
            $assign_fees->mgender_id = $studentRecord->mgender_id;
            $assign_fees->record_id = $studentRecord->id;
            $assign_fees->church_id = Auth::user()->church_id;
            $assign_fees->church_year_id = getAcademicId();
            $assign_fees->save();

            $installments = DirectFeesInstallment::where('fees_master_id', $fees_master->id)->get();
                if (count($installments)>0) {
                    foreach ($installments as $installment) {
                        $checkExist = DirectFeesInstallmentAssign::where('church_year_id', getAcademicId())
                            ->where('record_id', $studentRecord->id)
                            ->where('member_id', $studentRecord->member_id)
                            ->where('fees_installment_id', $installment->id)
                            ->first();
                           
                        if ($checkExist) {
                            $old_master = json_decode($checkExist->fees_master_ids);
                            $old_assign = json_decode($checkExist->assign_ids);

                            array_push($old_assign, $assign_fees->id);
                            array_push($old_master, $fees_master->id);

                            $assignInstallment = $checkExist;
                            $assignInstallment->fees_master_ids = json_encode($old_master);
                            $assignInstallment->assign_ids = json_encode($old_assign);
                        } else {
                            $assignInstallment = new DirectFeesInstallmentAssign();
                            $assignInstallment->fees_master_ids = json_encode([$fees_master->id]);
                            $assignInstallment->assign_ids = json_encode([$assign_fees->id]);
                        }
                        
                        $assignInstallment->fees_installment_id = $installment->id;
                        if(($installment->amount != null)){
                            $assignInstallment->amount =  $installment->amount;
                        }else{
                            $assignInstallment->amount = (($fees_master->amount * $installment->percentange) / 100);
                        }
                        // $assignInstallment->amount = $installment->amount;
                        $assignInstallment->due_date = $installment->due_date;
                        $assignInstallment->fees_type_id = $fees_master->fees_type_id;
                        $assignInstallment->member_id = $studentRecord->member_id;
                        $assignInstallment->record_id = $studentRecord->id;
                        $assignInstallment->church_year_id = getAcademicId();
                        $assignInstallment->church_id = auth()->user()->church_id;
                        $assignInstallment->save();
                    }
                }
        }
    }

    public function assignFeesDiscount($discount_id,$record_id){
        
        $fees_discount = SmFeesDiscount::find($discount_id);
        $installments = DirectFeesInstallmentAssign::where('active_status',0)->where('record_id',$record_id)->get();

        if($fees_discount && count($installments) > 0){
            $total_discount = $fees_discount->amount;
            $num_of_installments = count($installments);
            $average =  $total_discount / $num_of_installments;
            $avg_disc = round($average);
            $differnt = $total_discount - ($avg_disc * $num_of_installments);

            foreach($installments as $key=> $feesInstallment){
                $feesInstallment->fees_discount_id = $discount_id; 
                  $feesInstallment->discount_amount = $avg_disc ?? null;
                    if( ($avg_disc + $differnt) <=  $feesInstallment->amount){
                        if( $differnt){
                            if($key == 0){
                                $feesInstallment->discount_amount = ($avg_disc + $differnt) ?? null ;
                            }else{
                                $feesInstallment->discount_amount = $avg_disc ?? null ;
                            }  
                        }
                         
                    }else{
                       
                        $feesInstallment->discount_amount = $feesInstallment->amount ?? null;
                        $feesInstallment->active_status = 1;
                    }
                     
                  
                $feesInstallment->save();
            }
        }
    }

}