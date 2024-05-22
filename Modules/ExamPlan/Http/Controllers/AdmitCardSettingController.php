<?php

namespace Modules\ExamPlan\Http\Controllers;

use App\SmExam;
use App\SmClass;
use App\SmStudent;
use App\SmExamType;
use App\SmExamSchedule;
use App\SmAssignSubject;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use Illuminate\Routing\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Session;
use Modules\ExamPlan\Entities\AdmitCard;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Support\Renderable;
use Modules\ExamPlan\Entities\AdmitCardSetting;

class AdmitCardSettingController extends Controller
{
    
    public function setting()
    {
        $setting = AdmitCardSetting::where('church_id', Auth::user()->church_id)->where('church_year_id', getAcademicId())->first();
        if(!$setting){
            $oldSetting = AdmitCardSetting::where('church_id', Auth::user()->church_id)->latest()->first();
            $setting = $oldSetting->replicate();
            $setting->church_year_id = getAcademicId();
            $setting->save();
        }

        return view('examplan::setting.admitCardSetting',compact('setting'));
    }


    public function settingUpdate(Request $request){
        
        try{
            $setting = AdmitCardSetting::where('church_id', Auth::user()->church_id)->where('church_year_id', getAcademicId())->first();
            if(!$setting){
                $oldSetting = AdmitCardSetting::where('church_id', Auth::user()->church_id)->latest()->first();
                $setting = $oldSetting->replicate();
            }
            $setting->student_photo = $request->student_photo ; 
            $setting->member_name = $request->member_name ;
            $setting->registration_no = $request->registration_no ;
            $setting->class_section = $request->class_section ;
            $setting->exam_name = $request->exam_name ;
            $setting->admit_sub_title = $request->admit_sub_title ;
            $setting->description = $request->description;
            $setting->church_year = $request->church_year ;
            $setting->principal_signature = $request->principal_signature;
            $setting->gaurdian_name = $request->gaurdian_name;
            $setting->student_download = $request->student_download ;
            $setting->parent_download = $request->parent_download ;
            $setting->student_notification = $request->student_notification ;
            $setting->parent_notification = $request->parent_notification;
            $setting->class_teacher_signature = $request->class_teacher_signature;
            $setting->principal_signature_photo =  Session::get('principal_sign');
            $setting->teacher_signature_photo = Session::get('class_teacher_sign');
            $setting->save();
            Toastr::success('Update Successfully','success');
            return redirect()->back();
        }
        catch(\Exception $e){
            Toastr::error('Operation Failed','Error');
            return redirect()->back();
        }
    }

    public function imageUpload(Request $r)
    {
        try {
            $validator = Validator::make($r->all(), [
                'logo_pic' => 'sometimes|required|mimes:jpg,png,jpeg|max:40000',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => 'error'], 201);
            }
            if ($r->hasFile('logo_pic')) {
                $file = $r->file('logo_pic');
                $images = Image::make($file)->insert($file);
                $pathImage = 'Modules/ExamPlan/Public/images';
                if (!file_exists($pathImage)) {
                    mkdir($pathImage, 0777, true);
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    $images->save('Modules/ExamPlan/Public/images/' . $name);
                    $imageName = 'Modules/ExamPlan/Public/images/' . $name;
                    Session::put('class_teacher_sign', $imageName);
                } else {
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    if (file_exists(Session::get('class_teacher_sign'))) {
                        File::delete(Session::get('class_teacher_sign'));
                    }
                    $images->save('Modules/ExamPlan/Public/images/' . $name);
                    $imageName = 'Modules/ExamPlan/Public/images/' . $name;
                    Session::put('class_teacher_sign', $imageName);
                }
            }
            // parent
            if ($r->hasFile('fathers_photo')) {
                $file = $r->file('fathers_photo');
                $images = Image::make($file)->insert($file);
                $pathImage = 'Modules/ExamPlan/Public/images/';
                if (!file_exists($pathImage)) {
                    mkdir($pathImage, 0777, true);
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    $images->save('Modules/ExamPlan/Public/images/' . $name);
                    $imageName = 'Modules/ExamPlan/Public/images/' . $name;
                    Session::put('principal_sign', $imageName);
                } else {
                    $name = md5($file->getClientOriginalName() . time()) . "." . "png";
                    if (file_exists(Session::get('fathers_photo'))) {
                        File::delete(Session::get('fathers_photo'));
                    }
                    $images->save('Modules/ExamPlan/Public/images/' . $name);
                    $imageName = 'Modules/ExamPlan/Public/images/' . $name;
                    Session::put('principal_sign', $imageName);
                }
            }
            
            return response()->json('success', 200);
        }
        catch (\Exception $e) {
            return response()->json(['error' => 'error'], 201);
        }
    }

    
    public function admitcard()
    {
        try{
            $exams = SmExamType::where('active_status', 1)
            ->where('church_year_id', getAcademicId())
            ->where('church_id', Auth::user()->church_id)
            ->get();
            $classes = SmClass::where('church_year_id',getAcademicId())->where('church_id',auth()->user()->church_id)->get();
            return view('examplan::admitCard',compact('exams','classes'));
        }
        catch(\Exception $e){
            Toastr::error('Operation Failed','Error');
            return redirect()->back();
        }
    }

    public function index()
    {
        return view('examplan::create');
    }


    public function admitcardSearch(Request $request)
    {
       try{
            $input = $request->all();
            $validator = Validator::make($input, [
                'exam' => 'required',
                'class' => 'required',
            ]);
            if ($validator->fails()) {
                return redirect()->route('examplan.admitcard.index')
                    ->withErrors($validator)
                    ->withInput();
            }

            $exam = SmExamSchedule::query();
            $exam_id = $request->exam;
            $age_group_id = $request->class;
            $exam->where('church_id',auth()->user()->church_id)->where('church_year_id',getAcademicId());
            if ($request->exam != "") {
                $exam->where('exam_term_id', $request->exam);
            }
            if ($request->class != "") {
                $exam->where('age_group_id', $request->class);
            }
            if ($request->section != "") {
                $exam->where('mgender_id', $request->section);
            }
            $exam_routine = $exam->get();

            $old_admits = AdmitCard::where('exam_type_id', $request->exam)
                                        ->where('church_id', Auth::user()->church_id)
                                        ->where('church_year_id',getAcademicId())
                                        ->get(['student_record_id']);
                                       
            $old_admit_ids = [];
            foreach($old_admits as $admit){
                $old_admit_ids[] =  $admit->student_record_id;
            }  
                         
            if($exam_routine){
                $student_records = StudentRecord::query();
                $student_records->where('church_id',auth()->user()->church_id)
                ->where('church_year_id',getAcademicId())
                ->where('is_promote',0);
                if ($request->class != "") {
                    $student_records->where('age_group_id', $request->class);
                }
                if ($request->section != "") {
                    $student_records->where('mgender_id', $request->section);
                }
                                        
                $records = $student_records->get();
             
               
            
            $exams = SmExamType::where('active_status', 1)
                                ->where('church_year_id', getAcademicId())
                                ->where('church_id', Auth::user()->church_id)
                                ->get();
            $classes = SmClass::where('church_year_id',getAcademicId())
                                ->where('church_id',auth()->user()->church_id)
                                ->get();
            return view('examplan::admitCard',compact('exams','classes','records','exam_id','age_group_id','old_admit_ids'));
            }else{
                Toastr::warning('Exam shedule is not ready','warning');
                return redirect()->back();
            }
       }

        catch(\Exception $e){
            Toastr::error('Operation Failed','Error');
            return redirect()->back();
        }
    }

    public function admitcardGenerate(Request $request){

       try{
            $student_records = [];
            $studentRecord = null;
           $setting = AdmitCardSetting::where('church_id', Auth::user()->church_id)->where('church_year_id', getAcademicId())->first();
           if(!$setting){
               $oldSetting = AdmitCardSetting::where('church_id', Auth::user()->church_id)->latest()->first();
               $setting = $oldSetting->replicate();
               $setting->church_year_id = getAcademicId();
               $setting->save();
           }
            if($request->data){
                foreach($request->data as $key=> $data){
                    if(count($data) == 2){
                        $student_records[] = $data['student_record_id'];
                    }
                }
                
                foreach($student_records as $record){
                    $admit_card = AdmitCard::where('exam_type_id',$request->exam_type_id)->where('student_record_id', $record)->first();
                    $studentRecord = StudentRecord::find($record);
                    if(! $admit_card){
                        $new_admit = new AdmitCard();
                        $new_admit->student_record_id = $record;
                        $new_admit->exam_type_id = $request->exam_type_id;
                        $new_admit->created_by = Auth::id();
                        $new_admit->church_id =Auth::user()->church_id;
                        $new_admit->church_year_id = getAcademicId();
                        $new_admit->save();
                        $member_id = StudentRecord::find($record)->member_id;
                        $student = SmStudent::find($member_id);
                        $exam_type = SmExamType::find($request->exam_type_id);

                        if($setting->student_notification ){
                            @sendNotification($exam_type->title.' admit download', route('examplan.admitCardDownload',$new_admit->id), $student->user->id, 2);
                        }
                        if($setting->parent_notification){
                            @sendNotification($exam_type->title.' admit download', route('examplan.admitCardDownload',$new_admit->id), $student->parents->parent_user->id, 3);
                        }   
                    }
                }
                    $admitcards = AdmitCard::whereIn('student_record_id',$student_records)->where('exam_type_id',$request->exam_type_id)->with('studentRecord')->get();
                    $assign_subjects = SmAssignSubject::where('age_group_id', $studentRecord->age_group_id)->where('mgender_id', $studentRecord->mgender_id)
                                        ->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
                    $exam_routines = SmExamSchedule::where('age_group_id', $studentRecord->age_group_id)
                                        ->where('mgender_id', $studentRecord->mgender_id)
                                        ->where('exam_term_id', $request->exam_type_id)->orderBy('date', 'ASC')->get();

                    if($setting->admit_layout == 2){
                       
                        return view('examplan::admitcardPrint_2',compact('setting','assign_subjects','exam_routines','admitcards'));
                    }
                    elseif($setting->admit_layout == 1){
                        return view('examplan::admitcardPrint',compact('setting','assign_subjects','exam_routines','admitcards'));
                    }
                   
        }
    }
        catch(\Exception $e){
            Toastr::error('Operation Failed','Error');
            return redirect()->route('admitcard.index');
        }
        
    }

    public function changeAdmitCardLayout(Request $request)
    {
        $setting = AdmitCardSetting::where('church_id', Auth::user()->church_id)->where('church_year_id', getAcademicId())->first();
        if(!$setting){
            $oldSetting = AdmitCardSetting::where('church_id', Auth::user()->church_id)->latest()->first();
            $setting = $oldSetting->replicate();
            $setting->church_year_id = getAcademicId();
        }

        $setting->admit_layout = $request->layout;
        $setting->save();
        return response()->json('success');

    }

}
