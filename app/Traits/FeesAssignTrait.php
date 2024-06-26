<?php

namespace App\Traits;

use App\SmFeesType;
use App\SmFeesGroup;
use App\SmFeesAssign;
use App\SmFeesMaster;
use App\SmFeesDiscount;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\Auth;
use Modules\University\Entities\UnFeesInstallment;
use Modules\University\Entities\UnFeesInstallmentAssign;
use Modules\University\Entities\UnSemesterLabel;
use Modules\University\Entities\UnSubject;

trait FeesAssignTrait
{

    public function assignSubjectFees($student_record, $subject_id, $semester_label_id, $fees_group_id = null, $withOutSubject = null)
    {
        if ($fees_group_id != null) {
            $fees_master = SmFeesMaster::where('fees_group_id', $fees_group_id)
            ->first();
        } else {
            $fees_master = SmFeesMaster::where('un_subject_id', $subject_id)
                ->where('un_semester_label_id',$semester_label_id)
                ->where('un_church_year_id', getAcademicId())
                ->first();

                if(is_null($fees_master)){
                    $subeject = UnSubject::find($subject_id);
                    $sem_label = UnSemesterLabel::find($semester_label_id);
                    $fees_group = new SmFeesGroup();
                    $fees_group->name = $subeject->subject_name;
                    $fees_group->church_id = Auth::user()->church_id;
                    $fees_group->un_church_year_id = getAcademicId();
                    $fees_group->save();
                    $feesGroupId = $fees_group->id;
                    $fees_type = new SmFeesType();
                    $fees_type->name =$subeject->subject_name;
                    $fees_type->fees_group_id = $feesGroupId;
                    $fees_type->church_id = Auth::user()->church_id;
                    $fees_type->un_church_year_id = getAcademicId();
                    $fees_type->save();
                    $feesTypeId = $fees_type->id;
        
                    $year = date('Y');
                    $amount = ($subeject->number_of_hours * $sem_label->fees_per_hour);
                    $fees_master = new SmFeesMaster();
                    $fees_master->fees_group_id = $fees_type->fees_group_id;
                    $fees_master->fees_type_id = $feesTypeId;
                    $fees_master->un_subject_id = $subeject->id;
                    $fees_master->un_semester_label_id = $semester_label_id;
                    $fees_master->date = date('Y-m-d', strtotime( $year . '-01-01'));
                    $fees_master->church_id = Auth::user()->church_id;
                    $fees_master->un_church_year_id = getAcademicId();
                    $fees_master->amount = $amount;
                    $fees_master->save();
                }
        }
        if ($fees_master) {
            $exist = SmFeesAssign::where('fees_master_id', $fees_master->id)
                ->where('un_semester_label_id', $semester_label_id)
                ->where('un_church_year_id', getAcademicId())
                ->where('record_id', $student_record)
                ->first();
               
            if (!$exist) {
                $studentRecord = StudentRecord::find($student_record);
                $assign_fees = new SmFeesAssign();
                $assign_fees->fees_amount = $fees_master->amount;
                $assign_fees->fees_master_id = $fees_master->id;
                $assign_fees->member_id = $studentRecord->member_id;
                $assign_fees->record_id = $studentRecord->id;
                $assign_fees->un_church_year_id = getAcademicId();
                $assign_fees->church_id = auth()->user()->church_id;
                $assign_fees->un_semester_label_id = $semester_label_id;
                $assign_fees->save();

                $installments = UnFeesInstallment::where('un_semester_label_id', $semester_label_id)->get();
                
                if (count($installments)>0) {
                    foreach ($installments as $installment) {
                        $checkExist = UnFeesInstallmentAssign::where('un_semester_label_id', $semester_label_id)
                            ->where('un_church_year_id', getAcademicId())
                            ->where('record_id', $studentRecord->id)
                            ->where('member_id', $studentRecord->member_id)
                            ->where('un_fees_installment_id', $installment->id)
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
                            $assignInstallment = new UnFeesInstallmentAssign();
                            $assignInstallment->fees_master_ids = json_encode([$fees_master->id]);
                            $assignInstallment->assign_ids = json_encode([$assign_fees->id]);
                        }

                        $assignInstallment->un_fees_installment_id = $installment->id;
                        $assignInstallment->amount += (($fees_master->amount * $installment->percentange) / 100);
                        $assignInstallment->due_date = $installment->due_date;
                        $assignInstallment->fees_type_id = $fees_master->fees_type_id;
                        $assignInstallment->member_id = $studentRecord->member_id;
                        $assignInstallment->record_id = $studentRecord->id;
                        $assignInstallment->un_semester_label_id = $semester_label_id;
                        $assignInstallment->un_church_year_id = getAcademicId();
                        $assignInstallment->church_id = auth()->user()->church_id;
                        $assignInstallment->save();
                    }
                }
            }
        }
    }

    public function assignDiscount($discount_id,$record_id){
        
        $fees_discount = SmFeesDiscount::find($discount_id);
        $installments = UnFeesInstallmentAssign::where('record_id',$record_id)->get();

        if($fees_discount && $installments){
            foreach($installments as $feesInstallment){
                if($feesInstallment->active_status == 0){
                    $feesInstallment->fees_discount_id = $discount_id; 
                    $feesInstallment->discount_amount = ($feesInstallment->amount * $fees_discount->amount) / 100; 
                    $feesInstallment->save();
                }
                
            }
        }
    }

    public function feesMasterUnAssign($record_id, $semester_label_id, $fees_group_id){
        $studentRecord = StudentRecord::find($record_id);
        $fees_master = SmFeesMaster::where('fees_group_id', $fees_group_id)->first();
        $installments  = UnFeesInstallmentAssign::where('record_id',$record_id)->where('un_semester_label_id',$semester_label_id)->get();
        $selectedAssignFees = SmFeesAssign::where('un_semester_label_id', $semester_label_id)
                                    ->where('un_church_year_id', getAcademicId())
                                    ->where('record_id', $record_id)
                                    ->where('fees_master_id',$fees_master->id)
                                    ->first();
        $have_payemts  = UnFeesInstallmentAssign::where('record_id',$record_id)->where('un_semester_label_id',$semester_label_id)->where('active_status', '!=', 0)->first();                           
        if($installments){
                if($selectedAssignFees && is_null($have_payemts)){
                    $un_assign_amount = $fees_master->amount;
                    foreach($installments as $ins){
                        $installment = UnFeesInstallment::find($ins->un_fees_installment_id);
                        if($installment){
                            $ins->amount -= (($un_assign_amount * $installment->percentange) / 100);
                            $ins->amount->save();
                        } 
                    } 
                    $selectedAssignFees->delete();                   
          
            }
        }
    }
}
