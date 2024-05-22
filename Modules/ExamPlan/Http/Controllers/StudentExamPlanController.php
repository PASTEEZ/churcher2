<?php

namespace Modules\ExamPlan\Http\Controllers;

use App\SmExam;
use App\SmStudent;
use App\SmExamSchedule;
use App\SmAssignSubject;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use Illuminate\Routing\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Modules\ExamPlan\Entities\AdmitCard;
use Illuminate\Contracts\Support\Renderable;
use Modules\ExamPlan\Entities\AdmitCardSetting;

class StudentExamPlanController extends Controller
{
    public function admitCard()
    {
        try{
            $student = Auth::user()->student;
            $records = StudentRecord::where('is_promote',0)
                                    ->where('member_id',$student->id)
                                    ->where('church_year_id',getAcademicId())
                                    ->where('church_id',Auth::user()->church_id)
                                    ->get();
            return view('examplan::studentAdmitCard',compact('records'));
        }
        catch(\Exception $e){
            Toastr::error('Operation Failed','Error');
            return redirect()->back();
        }

    }

    public function admitCardSearch(Request $request)
    {
        try{
            $smExam = SmExam::findOrFail($request->exam);
            if(auth()->user()->role_id == 3){
                $student = SmStudent::find($request->member_id);
            }else{
                $student = Auth::user()->student;
            }
            $studentRecord =StudentRecord::where('member_id',$student->id)
                                            ->where('age_group_id',$smExam->age_group_id)
                                            ->where('mgender_id',$smExam->mgender_id)
                                            ->where('church_id',Auth::user()->church_id)
                                            ->where('church_year_id',getAcademicId())
                                            ->first();

            $exam_routines = SmExamSchedule::where('age_group_id', $smExam->age_group_id)
                                            ->where('mgender_id', $smExam->mgender_id)
                                            ->where('exam_term_id', $smExam->exam_type_id)
                                            ->orderBy('date', 'ASC')
                                            ->get();
            if($exam_routines){
                
                $admit = AdmitCard::where('church_year_id',getAcademicId())
                                    ->where('student_record_id', $studentRecord->id)
                                    ->where('exam_type_id', $smExam->exam_type_id)
                                    ->first();
                if($admit){
                return redirect()->route('examplan.admitCardDownload',$admit->id);
                }else{
                    Toastr::warning('Admit Card Not Pulished Yet','Warning');
                    return redirect()->back();
                }                    
            }else{
                Toastr::warning('Exam Routine Not Pulished Yet','Warning');
                return redirect()->back();
            }

        }
        catch( \Exception $e){
            Toastr::error('Operation Failed','Error');
            return redirect()->back();
        }

    }

    public function admitCardDownload($id)
    {
        try{

            $admit = AdmitCard::find($id);
            $studentRecord = StudentRecord::find($admit->student_record_id);
            $student = SmStudent::find($studentRecord->member_id);
            $setting = AdmitCardSetting::where('church_id',Auth::user()->church_id)
                                         ->where('church_year_id',getAcademicId())   
                                        ->first();
            $assign_subjects = SmAssignSubject::where('age_group_id', $studentRecord->age_group_id)->where('mgender_id', $studentRecord->mgender_id)
                                        ->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $exam_routines = SmExamSchedule::where('age_group_id', $studentRecord->age_group_id)
                                        ->where('mgender_id', $studentRecord->mgender_id)
                                        ->where('exam_term_id', $admit->exam_type_id)->orderBy('date', 'ASC')->get();
           
            if($setting->admit_layout == 1){
                return view('examplan::studentAdmitCardDownload',compact('setting','assign_subjects','exam_routines','studentRecord','student','admit'));
            }else{
                return view('examplan::studentAdmitCardDownload_two',compact('setting','assign_subjects','exam_routines','studentRecord','student','admit'));
            }
        }
        catch(\Exception $e){
            Toastr::error('Operation Failed','Error');
            return redirect()->back();
        }
    }

    public function admitCardParent($member_id){
        try{
            $records = StudentRecord::where('is_promote',0)
            ->where('member_id',$member_id)
            ->where('church_year_id',getAcademicId())
            ->where('church_id',Auth::user()->church_id)
            ->get();
            return view('examplan::studentAdmitCard',compact('records' ,'member_id'));
        }
        catch(\Exception $e){
            Toastr::error('Operation Failed','Error');
            return redirect()->back();
        }
    }




}
