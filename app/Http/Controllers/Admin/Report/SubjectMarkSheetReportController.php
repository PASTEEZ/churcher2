<?php

namespace App\Http\Controllers\Admin\Report;

use App\SmExam;
use App\SmClass;
use App\SmStaff;
use App\SmSection;
use App\SmStudent;
use App\SmSubject;
use App\SmExamType;
use App\SmExamSetup;
use App\SmMarkStore;
use App\SmMarksGrade;
use App\SmResultStore;
use App\SmAssignSubject;
use App\CustomResultSetting;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\SmClassOptionalSubject;
use App\SmOptionalSubjectAssign;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Modules\University\Entities\UnFaculty;
use Modules\University\Entities\UnSession;
use Modules\University\Entities\UnSubject;
use Modules\University\Entities\UnSemester;
use Modules\University\Entities\UnDepartment;
use Modules\University\Entities\UnAcademicYear;
use Modules\University\Entities\UnAssignSubject;
use Modules\University\Entities\UnSemesterLabel;
use App\Http\Requests\Admin\Reports\FinalMarkSheetRequest;
use App\Http\Requests\Admin\Reports\SubjectMarkSheetRequest;
use Modules\University\Http\Controllers\ExamCommonController;

class SubjectMarkSheetReportController extends Controller
{
    public function index(){
        try {
            if (teacherAccess()) {
                $teacher_info=SmStaff::where('user_id',Auth::user()->id)->first();
                $classes= SmAssignSubject::where('teacher_id',$teacher_info->id)->join('sm_classes','sm_classes.id','sm_assign_subjects.age_group_id')
                    ->where('sm_assign_subjects.church_year_id', getAcademicId())
                    ->where('sm_assign_subjects.active_status', 1)
                    ->where('sm_assign_subjects.church_id',Auth::user()->church_id)
                    ->select('sm_classes.id','age_group_name')
                    ->groupBy('sm_classes.id')
                    ->get();
            } else {
                $classes = SmClass::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id',Auth::user()->church_id)
                    ->get();
            }
            return view('backEnd.examination.subjectMarkSheet', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function search(SubjectMarkSheetRequest $request){
       try{
        $class = SmClass::find($request->class);
        $data = [];
        $section = null;
        if(moduleStatusCheck('University')){
            $subject = UnSubject::find($request->un_subject_id);
            $assigned_subject = UnAssignSubject::where('church_id',Auth::user()->church_id)
                                                ->where('un_subject_id',$request->un_subject_id)
                                                ->where('un_semester_label_id',$request->un_semester_label_id)
                                                ->get();
                                                
        }else{
            $subject = SmSubject::find($request->subject);
            if($request->section){
                $section = SmSection::find($request->section);
            }

            $assigned_subject = SmAssignSubject::when($request->class, function ($query) use ($request) {
                $query->where('age_group_id', $request->class);
            })
            ->when($request->section, function ($query) use ($request) {
                $query->where('mgender_id', $request->section);
            })
            ->when($request->subject, function ($query) use ($request) {
                $query->where('subject_id', $request->subject);
            })
            ->where('church_id',Auth()->user()->church_id)
            ->where('church_year_id',getAcademicId())
            ->get();

        }
       
        if($assigned_subject){
            if(moduleStatusCheck('University')){
                $sm_mark_stores = SmResultStore::where('un_semester_label_id',$request->un_semester_label_id)
                ->where('un_session_id',$request->un_session_id)
                ->where('church_id',Auth()->user()->church_id)
                ->where('un_church_year_id',$request->un_church_year_id)
                ->with('studentInfo')
                ->get()
                ->groupBy('member_id');
            }else{
                $sm_mark_stores = SmResultStore::when($request->class, function ($query) use ($request) {
                    $query->where('age_group_id', $request->class);
                })
                ->when($request->section, function ($query) use ($request) {
                    $query->where('mgender_id', $request->section);
                }) 
                ->when($request->subject, function ($query) use ($request) {
                    $query->where('subject_id', $request->subject);
                })
                ->where('church_id',Auth()->user()->church_id)
                ->where('church_year_id',getAcademicId())
                ->with('studentInfo')
                ->get()
                ->groupBy('member_id');
            }

            $students = StudentRecord::query();
            if(moduleStatusCheck('University')){
                $data['session'] = UnSession::find($request->un_session_id)->name;
                $data['church_year'] = UnAcademicYear::find($request->un_church_year_id)->name;
                $data['faculty'] = UnFaculty::find($request->un_faculty_id)->name;
                $data['department'] = UnDepartment::find($request->un_department_id)->name;
                $data['semester'] = UnSemester::find($request->un_semester_id)->name;
                $data['semester_label'] = UnSemesterLabel::find($request->un_semester_label_id)->name;
                $data['requestData'] = $request->all();
                $students = universityFilter($students,$request);
                $result_setting = CustomResultSetting::where('un_church_year_id',getAcademicId())->where('church_id',Auth()->user()->church_id)->get();
            }else{
                $students = $student->when($request->class, function ($query) use ($request) {
                    $query->where('age_group_id', $request->class);
                    })
                    ->when($request->section, function ($query) use ($request) {
                        $query->where('mgender_id', $request->section);
                    })
                    ->where('church_year_id',getAcademicId());
                    $result_setting = CustomResultSetting::where('church_year_id',getAcademicId())->where('church_id',Auth()->user()->church_id)->get();
            }
                 
            $students->where('church_id',Auth()->user()->church_id)->where('is_promote',0)->whereHas('studentDetail', function ($q)  {
                                            $q->where('active_status', 1);
                                        })->with('studentDetail')->get();
                                        
            $students = $students->get();
            
            
            $student_collection = collect();
            foreach($students as $student){
                $item = [
                    'member_name' => $student->studentDetail->full_name,
                    'registration_no' => $student->studentDetail->registration_no,
                    'roll_no' => $student->studentDetail->roll_no,
                    'avg_mark' => 0
                ];
                $examTypeMarks = collect();
                if(count($result_setting) > 0){
                    foreach($result_setting as $exam){
                        $signle_mark = singleSubjectMark($student->id,$subject->id,$exam->exam_type_id)[0];
                        $examTypeMarks->push(collect(['single_avg_mark' => $signle_mark]));     
                    }
                }else{
                    foreach(examTypes() as $exam){
                        $signle_mark = singleSubjectMark($student->id,$subject->id,$exam->id,true)[0];
                        $examTypeMarks->push(collect(['single_avg_mark' => $signle_mark]));     
                    }
                }
                
                $item['avg_mark'] = subjectAverageMark($student->id,$subject->id)[0];;
                $item['examTypeMarks'] = $examTypeMarks;
                $student_collection->push(collect($item));

            }

            $finalMarkSheets =  $student_collection->sortByDesc('avg_mark');
             
            if(is_null($sm_mark_stores)){
                Toastr::error('Mark Register Uncomplete', 'Failed');
                return redirect()->back();
            }

            if (teacherAccess()) {
                $teacher_info=SmStaff::where('user_id',Auth::user()->id)->first();
                $classes= SmAssignSubject::where('teacher_id',$teacher_info->id)->join('sm_classes','sm_classes.id','sm_assign_subjects.age_group_id')
                    ->where('sm_assign_subjects.church_year_id', getAcademicId())
                    ->where('sm_assign_subjects.active_status', 1)
                    ->where('sm_assign_subjects.church_id',Auth::user()->church_id)
                    ->select('sm_classes.id','age_group_name')
                    ->groupBy('sm_classes.id')
                    ->get();
            } else {
                $classes = SmClass::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id',Auth::user()->church_id)
                    ->get();
            }

            return view('backEnd.examination.subjectMarkSheetList',compact('classes','sm_mark_stores','result_setting','students','subject','class','section','finalMarkSheets','data'));
        }

       }
       catch(\Exception $e){
        Toastr::error('Operation Failed', 'Failed');
        return redirect('subject_mark_sheet');
       } 
    }


    public function print(Request $request){
        try{
            
         $class = SmClass::find($request->class);
         $data = [];
         $section = null;
         if(moduleStatusCheck('University')){
             $subject = UnSubject::find($request->un_subject_id);
             $assigned_subject = UnAssignSubject::where('church_id',Auth::user()->church_id)
                                                 ->where('un_subject_id',$request->un_subject_id)
                                                 ->where('un_semester_label_id',$request->un_semester_label_id)
                                                 ->get();
                                                 
         }else{
             $subject = SmSubject::find($request->subject);
             if($request->section){
                 $section = SmSection::find($request->section);
             }
 
             $assigned_subject = SmAssignSubject::when($request->class, function ($query) use ($request) {
                 $query->where('age_group_id', $request->class);
             })
             ->when($request->section, function ($query) use ($request) {
                 $query->where('mgender_id', $request->section);
             })
             ->when($request->subject, function ($query) use ($request) {
                 $query->where('subject_id', $request->subject);
             })
             ->where('church_id',Auth()->user()->church_id)
             ->where('church_year_id',getAcademicId())
             ->get();
         }
        
         if($assigned_subject){
             if(moduleStatusCheck('University')){
                 $sm_mark_stores = SmResultStore::where('un_semester_label_id',$request->un_semester_label_id)
                 ->where('un_session_id',$request->un_session_id)
                 ->where('church_id',Auth()->user()->church_id)
                 ->where('un_church_year_id',$request->un_church_year_id)
                 ->with('studentInfo')
                 ->get()
                 ->groupBy('member_id');
             }else{
                 $sm_mark_stores = SmResultStore::when($request->class, function ($query) use ($request) {
                     $query->where('age_group_id', $request->class);
                 })
                 ->when($request->section, function ($query) use ($request) {
                     $query->where('mgender_id', $request->section);
                 }) 
                 ->when($request->subject, function ($query) use ($request) {
                     $query->where('subject_id', $request->subject);
                 })
                 ->where('church_id',Auth()->user()->church_id)
                 ->where('church_year_id',getAcademicId())
                 ->with('studentInfo')
                 ->get()
                 ->groupBy('member_id');
             }
 
             $students = StudentRecord::query();
             if(moduleStatusCheck('University')){
                 $data['session'] = UnSession::find($request->un_session_id)->name;
                 $data['church_year'] = UnAcademicYear::find($request->un_church_year_id)->name;
                 $data['faculty'] = UnFaculty::find($request->un_faculty_id)->name;
                 $data['department'] = UnDepartment::find($request->un_department_id)->name;
                 $data['semester'] = UnSemester::find($request->un_semester_id)->name;
                 $data['semester_label'] = UnSemesterLabel::find($request->un_semester_label_id)->name;
                 $data['requestData'] = $request->all();
                 $result_setting = CustomResultSetting::where('church_id',Auth()->user()->church_id)->where('un_church_year_id',getAcademicId())->get();
                 $students = universityFilter($students,$request);
             }else{

                $result_setting = CustomResultSetting::where('church_id',Auth()->user()->church_id)->where('church_year_id',getAcademicId())->get();
                 $students = $student->when($request->class, function ($query) use ($request) {
                     $query->where('age_group_id', $request->class);
                     })
                     ->when($request->section, function ($query) use ($request) {
                         $query->where('mgender_id', $request->section);
                     })
                     ->where('church_year_id',getAcademicId());
             }
                  
             $students->where('church_id',Auth()->user()->church_id)->where('is_promote',0)->whereHas('studentDetail', function ($q)  {
                                             $q->where('active_status', 1);
                                         })->with('studentDetail')->get();
                                         
             $students = $students->get();
            
             
             $student_collection = collect();
             foreach($students as $student){
                 $item = [
                     'member_name' => $student->studentDetail->full_name,
                     'registration_no' => $student->studentDetail->registration_no,
                     'roll_no' => $student->studentDetail->roll_no,
                     'avg_mark' => 0
                 ];
                 $examTypeMarks = collect();
                 if(count($result_setting) > 0){
                     foreach($result_setting as $exam){
                         $signle_mark = singleSubjectMark($student->id,$subject->id,$exam->exam_type_id)[0];
                         $examTypeMarks->push(collect(['single_avg_mark' => $signle_mark]));     
                     }
                 }else{
                     foreach(examTypes() as $exam){
                         $signle_mark = singleSubjectMark($student->id,$subject->id,$exam->id,true)[0];
                         $examTypeMarks->push(collect(['single_avg_mark' => $signle_mark]));     
                     }
                 }
                 
                 $item['avg_mark'] = subjectAverageMark($student->id,$subject->id)[0];;
                 $item['examTypeMarks'] = $examTypeMarks;
                 $student_collection->push(collect($item));
 
             }
 
             $finalMarkSheets =  $student_collection->sortByDesc('avg_mark');
              
             if(is_null($sm_mark_stores)){
                 Toastr::error('Mark Register Uncomplete', 'Failed');
                 return redirect()->back();
             }
 
             if (teacherAccess()) {
                 $teacher_info=SmStaff::where('user_id',Auth::user()->id)->first();
                 $classes= SmAssignSubject::where('teacher_id',$teacher_info->id)->join('sm_classes','sm_classes.id','sm_assign_subjects.age_group_id')
                     ->where('sm_assign_subjects.church_year_id', getAcademicId())
                     ->where('sm_assign_subjects.active_status', 1)
                     ->where('sm_assign_subjects.church_id',Auth::user()->church_id)
                     ->select('sm_classes.id','age_group_name')
                     ->groupBy('sm_classes.id')
                     ->get();
             } else {
                 $classes = SmClass::where('active_status', 1)
                     ->where('church_year_id', getAcademicId())
                     ->where('church_id',Auth::user()->church_id)
                     ->get();
             }

            $grades = SmMarksGrade::where('church_id', Auth::user()->church_id)
             ->orderBy('gpa', 'desc')
             ->where('church_year_id',getAcademicId())
             ->get(); 
 
             if(moduleStatusCheck('University')){
                return view('university::exam.un_subject_mark_sheet_print',compact('classes','sm_mark_stores','result_setting','students','subject','class','section','grades','finalMarkSheets','data','grades'));
             }else{
                return view('backEnd.examination.subjectMarkSheetPrint',compact('classes','sm_mark_stores','result_setting','students','subject','class','section','grades','finalMarkSheets','data'));
             }
             
         }
 
        }
        catch(\Exception $e){
           
         Toastr::error('Operation Failed', 'Failed');
         return redirect('subject_mark_sheet');
        } 
     }


    // public function print(Request $request){
    //     try{
           
    //      $class = SmClass::find($request->class);
    //      $subject = SmSubject::find($request->subject);
    //      $section = null;
    //      if($request->section){
    //          $section = SmSection::find($request->section);
    //      }
    //      $assigned_subject = SmAssignSubject::when($request->class, function ($query) use ($request) {
    //          $query->where('age_group_id', $request->class);
    //      })
    //      ->when($request->section, function ($query) use ($request) {
    //          $query->where('mgender_id', $request->section);
    //      })
    //      ->when($request->subject, function ($query) use ($request) {
    //          $query->where('subject_id', $request->subject);
    //      })
    //      ->where('church_id',Auth()->user()->church_id)
    //      ->where('church_year_id',getAcademicId())
    //      ->get();
         
    //      if($assigned_subject){
 
    //          $sm_mark_stores = SmResultStore::when($request->class, function ($query) use ($request) {
    //              $query->where('age_group_id', $request->class);
    //          })
    //          ->when($request->section, function ($query) use ($request) {
    //              $query->where('mgender_id', $request->section);
    //          }) 
    //          ->when($request->subject, function ($query) use ($request) {
    //              $query->where('subject_id', $request->subject);
    //          })
    //          ->where('church_id',Auth()->user()->church_id)
    //          ->where('church_year_id',getAcademicId())
    //          ->with('studentInfo')
    //          ->get()->groupBy('member_id');
 
    //          $students = StudentRecord::when($request->class, function ($query) use ($request) {
    //                                      $query->where('age_group_id', $request->class);
    //                                      })
    //                                      ->when($request->section, function ($query) use ($request) {
    //                                          $query->where('mgender_id', $request->section);
    //                                      })->where('church_id',Auth()->user()->church_id)
    //                                      ->where('church_year_id',getAcademicId())
    //                                      ->where('is_promote',0)
    //                                      ->whereHas('studentDetail', function ($q)  {
    //                                          $q->where('active_status', 1);
    //                                      })->with('studentDetail')->get();  
    //          $result_setting = CustomResultSetting::where('church_id',Auth()->user()->church_id)
    //          ->where('church_year_id',getAcademicId())
    //          ->get();
    //          if(is_null($sm_mark_stores)){
    //              Toastr::error('Mark Register Uncomplete', 'Failed');
    //              return redirect()->back();
    //          }
 
    //          if (teacherAccess()) {
    //              $teacher_info=SmStaff::where('user_id',Auth::user()->id)->first();
    //              $classes= SmAssignSubject::where('teacher_id',$teacher_info->id)->join('sm_classes','sm_classes.id','sm_assign_subjects.age_group_id')
    //                  ->where('sm_assign_subjects.church_year_id', getAcademicId())
    //                  ->where('sm_assign_subjects.active_status', 1)
    //                  ->where('sm_assign_subjects.church_id',Auth::user()->church_id)
    //                  ->select('sm_classes.id','age_group_name')
    //                  ->groupBy('sm_classes.id')
    //                  ->get();
    //          } else {
    //              $classes = SmClass::where('active_status', 1)
    //                  ->where('church_year_id', getAcademicId())
    //                  ->where('church_id',Auth::user()->church_id)
    //                  ->get();
    //          }

    //          $grades = SmMarksGrade::where('church_id', Auth::user()->church_id)
    //          ->where('church_year_id', getAcademicId())
    //          ->orderBy('gpa', 'desc')
    //          ->get(); 
             
    //          $student_collection = collect();
    //          foreach($students as $student){
    //              $item = [
    //                  'member_name' => $student->studentDetail->full_name,
    //                  'registration_no' => $student->studentDetail->registration_no,
    //                  'roll_no' => $student->studentDetail->roll_no,
    //                  'avg_mark' => 0
    //              ];
    //              $examTypeMarks = collect();
                 
    //              if(count($result_setting) > 0){
    //                 foreach($result_setting as $exam){
    //                     $signle_mark = singleSubjectMark($student->id,$subject->id,$exam->exam_type_id)[0];
    //                     $examTypeMarks->push(collect(['single_avg_mark' => $signle_mark]));     
    //                 }
    //             }else{
    //                 foreach(examTypes() as $exam){
    //                     $signle_mark = singleSubjectMark($student->id,$subject->id,$exam->id,true)[0];
    //                     $examTypeMarks->push(collect(['single_avg_mark' => $signle_mark]));     
    //                 }
    //             }
    //              $item['avg_mark'] = subjectAverageMark($student->id,$subject->id)[0];;
    //              $item['examTypeMarks'] = $examTypeMarks;
    //              $student_collection->push(collect($item));
 
    //          }
 
    //          $finalMarkSheets =  $student_collection->sortByDesc('avg_mark');
 
    //          return view('backEnd.examination.subjectMarkSheetPrint',compact('classes','sm_mark_stores','result_setting','students','subject','class','section','grades','finalMarkSheets'));
    //      }
 
    //     }
    //     catch(\Exception $e){
    //         Toastr::error('Operation Failed', 'Failed');
    //         return redirect('subject_mark_sheet');
    //     } 
    //  }

     public function finalMarkSheet(){

        try{
            if (teacherAccess()) {
                $teacher_info=SmStaff::where('user_id',Auth::user()->id)->first();
                $classes= SmAssignSubject::where('teacher_id',$teacher_info->id)->join('sm_classes','sm_classes.id','sm_assign_subjects.age_group_id')
                    ->where('sm_assign_subjects.church_year_id', getAcademicId())
                    ->where('sm_assign_subjects.active_status', 1)
                    ->where('sm_assign_subjects.church_id',Auth::user()->church_id)
                    ->select('sm_classes.id','age_group_name')
                    ->groupBy('sm_classes.id')
                    ->get();
            } else {
                $classes = SmClass::where('active_status', 1)
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id',Auth::user()->church_id)
                    ->get();
            }

            return view('backEnd.examination.finalMarkSheet',compact('classes'));
        }
        catch(\Exception $e){
            Toastr::error('Operation Failed', 'Failed');
            return redirect('final_mark_sheet');
        }
     }

     public function finalMarkSheetSearch(FinalMarkSheetRequest $request){
        try{
            $class = SmClass::find($request->class);
            $section = null;
            if($request->section){
                $section = SmSection::find($request->section);
            }
           
           if(moduleStatusCheck('University')){

                $result_setting = CustomResultSetting::where('church_id',Auth()->user()->church_id)
                ->where('un_church_year_id',getAcademicId())
                ->get();

                if($request->un_mgender_id){
                    $section = SmSection::find($request->un_mgender_id);
                }
                $data['session'] = UnSession::find($request->un_session_id)->name;
                $data['church_year'] = UnAcademicYear::find($request->un_church_year_id)->name;
                $data['faculty'] = UnFaculty::find($request->un_faculty_id)->name;
                $data['department'] = UnDepartment::find($request->un_department_id)->name;
                $data['semester'] = UnSemester::find($request->un_semester_id)->name;
                $data['semester_label'] = UnSemesterLabel::find($request->un_semester_label_id)->name;
                $data['requestData'] = $request->all();
                $assigned_subjects = UnAssignSubject::where('un_semester_label_id',$request->un_semester_label_id)->get()->unique('un_subject_id');
           }else{
                   
                
                $result_setting = CustomResultSetting::where('church_id',Auth()->user()->church_id)
                ->where('church_year_id',getAcademicId())
                ->get();
                    $assigned_subjects = SmAssignSubject::where('church_id',Auth()->user()->church_id)
                                                        ->when($request->class, function ($query) use ($request) {
                                                            $query->where('age_group_id', $request->class);
                                                        })->when($request->section, function ($query) use ($request) {
                                                                    $query->where('mgender_id', $request->section);
                                                        })->where('church_year_id',getAcademicId())->get()->unique('subject_id');    
           }

            if(is_null($assigned_subjects)){
                Toastr::error('Subject Not Assigned', 'Failed');
                return redirect()->back();
            }

            if (teacherAccess()) {
                $teacher_info=SmStaff::where('user_id',Auth::user()->id)->first();
                $classes= SmAssignSubject::where('teacher_id',$teacher_info->id)->join('sm_classes','sm_classes.id','sm_assign_subjects.age_group_id')
                                ->where('sm_assign_subjects.church_year_id', getAcademicId())
                                ->where('sm_assign_subjects.active_status', 1)
                                ->where('sm_assign_subjects.church_id',Auth::user()->church_id)
                                ->select('sm_classes.id','age_group_name')
                                ->groupBy('sm_classes.id')
                                ->get();
            } else {
                $classes = SmClass::where('active_status', 1)
                                ->where('church_year_id', getAcademicId())
                                ->where('church_id',Auth::user()->church_id)
                                ->get();
            }

            $result = SmResultStore::query();
            $result->where('church_id',Auth()->user()->church_id);
            if(moduleStatusCheck('University')){
                $result = universityFilter($result,$request);
            }else{
                $result->when($request->class, function ($query) use ($request) {
                    $query->where('age_group_id', $request->class);
                })
                ->when($request->section, function ($query) use ($request) {
                    $query->where('mgender_id', $request->section);
                })->where('church_year_id',getAcademicId());
            }
            
            $result = $result->with('studentInfo')->get()->groupBy('member_id');

            if($result){
                $students = StudentRecord::query();
                $students->where('church_id',Auth()->user()->church_id);
                if(moduleStatusCheck('University')){
                    $students = universityFilter($students, $request);
                }else{
                    $students = $students->when($request->class, function ($query) use ($request) {
                        $query->where('age_group_id', $request->class);
                        })
                        ->when($request->section, function ($query) use ($request) {
                        $query->where('mgender_id', $request->section);
                        })->where('church_year_id',getAcademicId())->where('is_promote',0);
                }
                
                $students = $students->whereHas('studentDetail', function ($q)  {
                            $q->where('active_status', 1);
                        })->with('studentDetail')->get();
                        
                        $student_collection = collect();
                        foreach($students as $student){
                            $item = [
                                'member_name' => $student->studentDetail->full_name,
                                'registration_no' => $student->studentDetail->registration_no,
                                'roll_no' => $student->studentDetail->roll_no,
                                'avg_mark' => 0
                            ];
                            $subjects = collect();
                            $all_subject_ids = [];
                            foreach($assigned_subjects as $assigned_subject){
                                if(moduleStatusCheck('University')){
                                    $signle_mark = subjectAverageMark($student->id,$assigned_subject->un_subject_id)[0];
                                    $all_subject_ids[] = $assigned_subject->un_subject_id ;
                                }else{
                                    $signle_mark = subjectAverageMark($student->id,$assigned_subject->subject_id)[0];
                                    $all_subject_ids[] = $assigned_subject->subject_id ;
                                }
                                $subjects->push(collect(['exam_mark' => $signle_mark ]));
                                   
                            }
                            $item['avg_mark'] = $subjects->avg('exam_mark');
                            $item['subjects'] = $subjects;
                            $student_collection->push(collect($item));

                        }

                        $finalMarkSheets =  $student_collection->sortByDesc('avg_mark');
                      
                    return view('backEnd.examination.finalMarkSheetList',compact('classes','students','class','section','assigned_subjects','result_setting','finalMarkSheets','all_subject_ids','data'));
                }
    
            else{
                Toastr::error('Mark Register Uncomplete', 'Failed');
                return redirect('final_mark_sheet');
               
            }
        }
            
        catch( \Exception $e){
         
            Toastr::error('Operation Failed', 'Failed');
            return redirect('final_mark_sheet');
        }
     }


    //  $grades = SmMarksGrade::where('church_id', Auth::user()->church_id)
    //                     ->where('church_year_id', getAcademicId())
    //                     ->orderBy('gpa', 'desc')
    //                     ->get(); 
    //return view('backEnd.examination.finalMarkSheetPrint',compact('classes','students','class','section','assigned_subjects','result_setting','finalMarkSheets','grades','all_subject_ids'));

     public function finalMarkSheetPrint(Request $request){
        {
            try{
                $class = SmClass::find($request->class);
                $section = null;
                if($request->section){
                    $section = SmSection::find($request->section);
                }
               
               if(moduleStatusCheck('University')){
                    $result_setting = CustomResultSetting::where('church_id',Auth()->user()->church_id)
                    ->where('un_church_year_id',getAcademicId())
                    ->get();

                    if($request->un_mgender_id){
                        $section = SmSection::find($request->un_mgender_id);
                    }
                    $data['session'] = UnSession::find($request->un_session_id)->name;
                    $data['church_year'] = UnAcademicYear::find($request->un_church_year_id)->name;
                    $data['faculty'] = UnFaculty::find($request->un_faculty_id)->name;
                    $data['department'] = UnDepartment::find($request->un_department_id)->name;
                    $data['semester'] = UnSemester::find($request->un_semester_id)->name;
                    $data['semester_label'] = UnSemesterLabel::find($request->un_semester_label_id)->name;
                    $data['requestData'] = $request->all();
                    $assigned_subjects = UnAssignSubject::where('un_semester_label_id',$request->un_semester_label_id)->get()->unique('un_subject_id');
               }else{
                       
                    
                        $result_setting = CustomResultSetting::where('church_id',Auth()->user()->church_id)
                        ->where('church_year_id',getAcademicId())
                        ->get();
                        $assigned_subjects = SmAssignSubject::where('church_id',Auth()->user()->church_id)
                                                            ->when($request->class, function ($query) use ($request) {
                                                                $query->where('age_group_id', $request->class);
                                                            })->when($request->section, function ($query) use ($request) {
                                                                        $query->where('mgender_id', $request->section);
                                                            })->where('church_year_id',getAcademicId())->get()->unique('subject_id');    
               }
    
                if(is_null($assigned_subjects)){
                    Toastr::error('Subject Not Assigned', 'Failed');
                    return redirect()->back();
                }
    
                if (teacherAccess()) {
                    $teacher_info=SmStaff::where('user_id',Auth::user()->id)->first();
                    $classes= SmAssignSubject::where('teacher_id',$teacher_info->id)->join('sm_classes','sm_classes.id','sm_assign_subjects.age_group_id')
                                    ->where('sm_assign_subjects.church_year_id', getAcademicId())
                                    ->where('sm_assign_subjects.active_status', 1)
                                    ->where('sm_assign_subjects.church_id',Auth::user()->church_id)
                                    ->select('sm_classes.id','age_group_name')
                                    ->groupBy('sm_classes.id')
                                    ->get();
                } else {
                    $classes = SmClass::where('active_status', 1)
                                    ->where('church_year_id', getAcademicId())
                                    ->where('church_id',Auth::user()->church_id)
                                    ->get();
                }
    
                $result = SmResultStore::query();
                $result->where('church_id',Auth()->user()->church_id);
                if(moduleStatusCheck('University')){
                    $result = universityFilter($result,$request);
                }else{
                    $result->when($request->class, function ($query) use ($request) {
                        $query->where('age_group_id', $request->class);
                    })
                    ->when($request->section, function ($query) use ($request) {
                        $query->where('mgender_id', $request->section);
                    })->where('church_year_id',getAcademicId());
                }
                
                $result = $result->with('studentInfo')->get()->groupBy('member_id');
    
                if($result){
                    $students = StudentRecord::query();
                    $students->where('church_id',Auth()->user()->church_id);
                    if(moduleStatusCheck('University')){
                        $students = universityFilter($students, $request);
                    }else{
                        $students = $students->when($request->class, function ($query) use ($request) {
                            $query->where('age_group_id', $request->class);
                            })
                            ->when($request->section, function ($query) use ($request) {
                            $query->where('mgender_id', $request->section);
                            })->where('church_year_id',getAcademicId())->where('is_promote',0);
                    }
                    
                    $students = $students->whereHas('studentDetail', function ($q)  {
                                $q->where('active_status', 1);
                            })->with('studentDetail')->get();
                            
                            $student_collection = collect();
                            foreach($students as $student){
                                $item = [
                                    'member_name' => $student->studentDetail->full_name,
                                    'registration_no' => $student->studentDetail->registration_no,
                                    'roll_no' => $student->studentDetail->roll_no,
                                    'avg_mark' => 0
                                ];
                                $subjects = collect();
                                $all_subject_ids = [];
                                foreach($assigned_subjects as $assigned_subject){
                                    if(moduleStatusCheck('University')){
                                        $signle_mark = subjectAverageMark($student->id,$assigned_subject->un_subject_id)[0];
                                        $all_subject_ids[] = $assigned_subject->un_subject_id ;
                                    }else{
                                        $signle_mark = subjectAverageMark($student->id,$assigned_subject->subject_id)[0];
                                        $all_subject_ids[] = $assigned_subject->subject_id ;
                                    }
                                    $subjects->push(collect(['exam_mark' => $signle_mark ]));
                                       
                                }
                                $item['avg_mark'] = $subjects->avg('exam_mark');
                                $item['subjects'] = $subjects;
                                $student_collection->push(collect($item));
    
                            }
    
                        $finalMarkSheets =  $student_collection->sortByDesc('avg_mark');
                        $grades = SmMarksGrade::where('church_id', Auth::user()->church_id)
                                            ->where('church_year_id', getAcademicId())
                                             ->orderBy('gpa', 'desc')
                                             ->get(); 
                        return view('backEnd.examination.finalMarkSheetPrint',compact('classes','students','class','section','assigned_subjects','result_setting','finalMarkSheets','grades','all_subject_ids','data'));
                    }
        
                else{
                    Toastr::error('Mark Register Uncomplete', 'Failed');
                    return redirect('final_mark_sheet');
                   
                }
            }
                
            catch( \Exception $e){
             
                Toastr::error('Operation Failed', 'Failed');
                return redirect('final_mark_sheet');
            }
         }
     
    }

     public function studentFinalMarkSheet(){
        if (teacherAccess()) {
            $teacher_info=SmStaff::where('user_id',Auth::user()->id)->first();
            $classes= SmAssignSubject::where('teacher_id',$teacher_info->id)->join('sm_classes','sm_classes.id','sm_assign_subjects.age_group_id')
                            ->where('sm_assign_subjects.church_year_id', getAcademicId())
                            ->where('sm_assign_subjects.active_status', 1)
                            ->where('sm_assign_subjects.church_id',Auth::user()->church_id)
                            ->select('sm_classes.id','age_group_name')
                            ->groupBy('sm_classes.id')
                            ->get();
        } else {
            $classes = SmClass::where('active_status', 1)
                            ->where('church_year_id', getAcademicId())
                            ->where('church_id',Auth::user()->church_id)
                            ->get();
        }
        
 
        return view('backEnd.examination.studentFinalMarkSheet',compact('classes'));

     }

     public function studentFinalMarkSheetSearch(Request $request){

        try{
            $data = [];
            if(moduleStatusCheck('University')){
                $data['session'] = UnSession::find($request->un_session_id)->name;
                $data['church_year'] = UnAcademicYear::find($request->un_church_year_id)->name;
                $data['faculty'] = UnFaculty::find($request->un_faculty_id)->name;
                $data['department'] = UnDepartment::find($request->un_department_id)->name;
                $data['semester'] = UnSemester::find($request->un_semester_id)->name;
                $data['semester_label'] = UnSemesterLabel::find($request->un_semester_label_id)->name;
                $data['requestData'] = $request->all();
                $exams = SmExam::where('active_status', 1)
                ->where('un_semester_label_id', $request->un_semester_label_id)
                ->where('un_session_id', $request->un_session_id)
                ->where('church_id',Auth::user()->church_id)
                ->get();

                $exam_types = SmExamType::where('active_status', 1)
                            ->where('un_church_year_id', getAcademicId())
                            ->pluck('id');
                $fail_grade = SmMarksGrade::where('active_status',1)
                            ->where('church_id',Auth::user()->church_id)
                            ->min('gpa');

                $fail_grade_name = SmMarksGrade::where('active_status',1)
                                ->where('church_id',Auth::user()->church_id)
                                ->where('gpa',$fail_grade)
                                ->first();

                $studentDetails = StudentRecord::where('member_id', $request->member_id)
                                                ->where('un_semester_label_id', $request->un_semester_label_id)
                                                ->where('un_church_year_id', $request->un_church_year_id)
                                                ->where('church_id', Auth::user()->church_id)
                                                ->first();
                $marks_grade = SmMarksGrade::where('church_id', Auth::user()->church_id)
                                            ->where('un_church_year_id',getAcademicId())
                                            ->orderBy('gpa', 'desc')
                                            ->get();
                    
                $maxGrade = SmMarksGrade::where('church_id',Auth::user()->church_id)
                                        ->max('gpa');
                $exam_setup = SmExamSetup::where([
                                            ['un_semester_label_id', $request->un_semester_label_id], 
                                            ['un_session_id', $request->un_session_id]])
                                            ->where('church_id',Auth::user()->church_id)
                                            ->get();

                $record_id = @$studentDetails->id;
                $examSubjects = SmExam::where([['un_semester_label_id', $request->un_semester_label_id], ['un_session_id', $request->un_session_id], ['un_mgender_id', $request->un_mgender_id]])
                                        ->where('church_id',Auth::user()->church_id)
                                        ->get();

                $examSubjectIds = [];
                foreach($examSubjects as $examSubject){
                    $examSubjectIds[] = $examSubject->un_subject_id;
                }
            

                $subjects = UnAssignSubject::where('un_semester_label_id', $request->un_semester_label_id)
                            ->where('church_id',Auth::user()->church_id)
                            ->whereIn('un_subject_id', $examSubjectIds)
                            ->get();
                    $assinged_exam_types = [];
                    foreach ($exams as $exam) {
                        $assinged_exam_types[] = $exam->exam_type_id;
                    }
                $assinged_exam_types = array_unique($assinged_exam_types);
            
                $result_setting = CustomResultSetting::where('church_id',Auth()->user()->church_id)
                                    ->where('un_church_year_id',getAcademicId())
                                    ->get();

                foreach ($assinged_exam_types as $assinged_exam_type) {
                    foreach ($subjects as $subject) {
                        $is_mark_available = SmResultStore::where([
                                            ['un_semester_label_id', $request->un_semester_label_id],  
                                            ['member_id', $request->member_id]
                                            ])
                                            ->first();
                                            
                        if ($is_mark_available == "") {
                            Toastr::error('Ops! Your result is not found! Please check mark register.', 'Failed');
                            return redirect('progress-card-report');
                        
                        }
                    }
                }
                $is_result_available = SmResultStore::where([
                    ['un_semester_label_id', $request->un_semester_label_id], ['un_mgender_id', $request->un_mgender_id], 
                    ['member_id', $request->member_id]
                    ])
                    ->get();
                    $member_id = $request->member_id;
                $all_subject_ids = array_unique($examSubjectIds);    
                if ($is_result_available->count() > 0) {
                        return view('university::exam.unStudentFinalMarkSheet', 
                        compact(
                        'exams',
                        'is_result_available', 
                        'subjects', 
                        'data',
                        'member_id', 
                        'studentDetails',
                        'exam_types', 
                        'assinged_exam_types',
                        'marks_grade',
                        'fail_grade_name',
                        'fail_grade',
                        'maxGrade',
                        'result_setting',
                        'record_id',
                        'all_subject_ids'
                    ));
                    } else {
                        Toastr::error('Ops! Your result is not found! Please check mark register.', 'Failed');
                        return redirect('student_mark_sheet_final');
                    }


            }else{
                $result_setting = CustomResultSetting::where('church_id',Auth()->user()->church_id)
                ->where('church_year_id',getAcademicId())
                ->get();
                
                $exams = SmExam::where('active_status', 1)
                ->where('age_group_id', $request->class)
                ->where('mgender_id', $request->section)
                ->where('church_year_id', getAcademicId())
                ->where('church_id',Auth::user()->church_id)
                ->get();

                $exam_types = SmExamType::where('active_status', 1)
                            ->where('church_year_id', getAcademicId())
                            ->where('church_id',Auth::user()->church_id)
                            ->pluck('id');
                
                

                $classes = SmClass::where('active_status', 1)
                            ->where('church_year_id', getAcademicId())
                            ->where('church_id',Auth::user()->church_id)
                            ->get();

                $fail_grade = SmMarksGrade::where('active_status',1)
                            ->where('church_year_id', getAcademicId())
                            ->where('church_id',Auth::user()->church_id)
                            ->min('gpa');

                $fail_grade_name = SmMarksGrade::where('active_status',1)
                                ->where('church_year_id', getAcademicId())
                                ->where('church_id',Auth::user()->church_id)
                                ->where('gpa',$fail_grade)
                                ->first();

                $studentDetails = StudentRecord::where('member_id', $request->student)
                                    ->where('age_group_id', $request->class)
                                    ->where('mgender_id', $request->section)
                                    ->where('church_year_id', getAcademicId())
                                    ->where('church_id', Auth::user()->church_id)
                                    ->first();

                $marks_grade = SmMarksGrade::where('church_id', Auth::user()->church_id)
                            ->where('church_year_id', getAcademicId())
                            ->orderBy('gpa', 'desc')
                            ->get();

                $maxGrade = SmMarksGrade::where('church_year_id', getAcademicId())
                            ->where('church_id',Auth::user()->church_id)
                            ->max('gpa');

                $optional_subject_setup = SmClassOptionalSubject::where('age_group_id','=',$request->class)
                                            ->first();

                $student_optional_subject = SmOptionalSubjectAssign::where('member_id',$request->student)
                                            ->where('session_id','=',$studentDetails->session_id)
                                            ->first();

                $exam_setup = SmExamSetup::where([
                            ['age_group_id', $request->class], 
                            ['mgender_id', $request->section]])
                            ->where('church_id',Auth::user()->church_id)
                            ->get();

                $age_group_id = $request->class;
                $mgender_id = $request->section;
                $member_id = $request->student;
                $record_id = StudentRecord::where('age_group_id',$age_group_id)
                                                ->where('mgender_id',$mgender_id)
                                                ->where('church_id',auth()->user()->church_id)
                                                ->where('church_year_id',getAcademicId())
                                                ->where('member_id',$member_id)
                                                ->value('id');
                $examSubjects = SmExam::where([['mgender_id', $mgender_id], ['age_group_id', $age_group_id]])
                                        ->where('church_id',Auth::user()->church_id)
                                        ->where('church_year_id',getAcademicId())
                                        ->get();

                $examSubjectIds = [];
                foreach($examSubjects as $examSubject){
                    $examSubjectIds[] = $examSubject->subject_id;
                }
                $subjects = SmAssignSubject::where([
                            ['age_group_id', $request->class], 
                            ['mgender_id', $request->section]])
                            ->where('church_id',Auth::user()->church_id)
                            ->whereIn('subject_id', $examSubjectIds)
                            ->get();

                $assinged_exam_types = [];
                foreach ($exams as $exam) {
                    $assinged_exam_types[] = $exam->exam_type_id;
                }
                $assinged_exam_types = array_unique($assinged_exam_types);
            
            
                

                foreach ($assinged_exam_types as $assinged_exam_type) {
                    foreach ($subjects as $subject) {
                        $is_mark_available = SmResultStore::where([
                                            ['age_group_id', $request->class], 
                                            ['mgender_id', $request->section], 
                                            ['member_id', $request->student]
                                            // ['exam_type_id', $assinged_exam_type]]
                                            ])
                                            ->first();
                        if ($is_mark_available == "") {
                            Toastr::error('Ops! Your result is not found! Please check mark register.', 'Failed');
                            return redirect('progress-card-report');
                        
                        }
                    }
                }
                $is_result_available = SmResultStore::where([
                    ['age_group_id', $request->class], 
                    ['mgender_id', $request->section], 
                    ['member_id', $request->student]])
                    ->where('church_id',Auth::user()->church_id)
                    ->get();

                    $all_subject_ids = array_unique($examSubjectIds);
                    

                if ($is_result_available->count() > 0) {
                    return view('backEnd.examination.studentFinalMarkSheet', 
                    compact(
                    'exams',
                    'optional_subject_setup',
                    'student_optional_subject', 
                    'classes', 'studentDetails',
                    'is_result_available', 
                    'subjects', 
                    'age_group_id', 
                    'mgender_id', 
                    'member_id', 
                    'exam_types', 
                    'assinged_exam_types',
                    'marks_grade',
                    'fail_grade_name',
                    'fail_grade',
                    'maxGrade',
                    'result_setting',
                    'record_id',
                    'all_subject_ids'
                ));
                } else {
                    Toastr::error('Ops! Your result is not found! Please check mark register.', 'Failed');
                    return redirect('student_mark_sheet_final');
                }
            }
        }catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect('student_mark_sheet_final');
        }
     }

     public function studentFinalMarkSheetPrint(Request $request){
        try{

            if(moduleStatusCheck('University')){
                $data['session'] = UnSession::find($request->un_session_id)->name;
                $data['church_year'] = UnAcademicYear::find($request->un_church_year_id)->name;
                $data['faculty'] = UnFaculty::find($request->un_faculty_id)->name;
                $data['department'] = UnDepartment::find($request->un_department_id)->name;
                $data['semester'] = UnSemester::find($request->un_semester_id)->name;
                $data['semester_label'] = UnSemesterLabel::find($request->un_semester_label_id)->name;
                $data['requestData'] = $request->all();
                $exams = SmExam::where('active_status', 1)
                ->where('un_semester_label_id', $request->un_semester_label_id)
                ->where('un_session_id', $request->un_session_id)
                ->where('church_id',Auth::user()->church_id)
                ->get();

                $exam_types = SmExamType::where('active_status', 1)
                            ->where('un_church_year_id', getAcademicId())
                            ->pluck('id');
                $fail_grade = SmMarksGrade::where('active_status',1)
                            ->where('church_id',Auth::user()->church_id)
                            ->min('gpa');

                $fail_grade_name = SmMarksGrade::where('active_status',1)
                                ->where('church_id',Auth::user()->church_id)
                                ->where('gpa',$fail_grade)
                                ->first();

                $studentDetails = StudentRecord::where('member_id', $request->member_id)
                                                ->where('un_semester_label_id', $request->un_semester_label_id)
                                                ->where('un_church_year_id', $request->un_church_year_id)
                                                ->where('church_id', Auth::user()->church_id)
                                                ->first();
                $marks_grade = SmMarksGrade::where('church_id', Auth::user()->church_id)
                                            ->where('un_church_year_id',getAcademicId())
                                            ->orderBy('gpa', 'desc')
                                            ->get();
                    
                $maxGrade = SmMarksGrade::where('church_id',Auth::user()->church_id)
                                        ->max('gpa');
                $exam_setup = SmExamSetup::where([
                                            ['un_semester_label_id', $request->un_semester_label_id], 
                                            ['un_session_id', $request->un_session_id]])
                                            ->where('church_id',Auth::user()->church_id)
                                            ->get();

                $record_id = @$studentDetails->id;
                $examSubjects = SmExam::where([['un_semester_label_id', $request->un_semester_label_id], ['un_session_id', $request->un_session_id], ['un_mgender_id', $request->un_mgender_id]])
                                        ->where('church_id',Auth::user()->church_id)
                                        ->get();

                $examSubjectIds = [];
                foreach($examSubjects as $examSubject){
                    $examSubjectIds[] = $examSubject->un_subject_id;
                }
            

                $subjects = UnAssignSubject::where('un_semester_label_id', $request->un_semester_label_id)
                            ->where('church_id',Auth::user()->church_id)
                            ->whereIn('un_subject_id', $examSubjectIds)
                            ->get();
                    $assinged_exam_types = [];
                    foreach ($exams as $exam) {
                        $assinged_exam_types[] = $exam->exam_type_id;
                    }
                $assinged_exam_types = array_unique($assinged_exam_types);
            
                $result_setting = CustomResultSetting::where('church_id',Auth()->user()->church_id)
                                    ->where('un_church_year_id',getAcademicId())
                                    ->get();

                foreach ($assinged_exam_types as $assinged_exam_type) {
                    foreach ($subjects as $subject) {
                        $is_mark_available = SmResultStore::where([
                                            ['un_semester_label_id', $request->un_semester_label_id],  
                                            ['member_id', $request->member_id]
                                            ])
                                            ->first();
                                            
                        if ($is_mark_available == "") {
                            Toastr::error('Ops! Your result is not found! Please check mark register.', 'Failed');
                            return redirect('progress-card-report');
                        
                        }
                    }
                }
                $is_result_available = SmResultStore::where([
                    ['un_semester_label_id', $request->un_semester_label_id], ['un_mgender_id', $request->un_mgender_id], 
                    ['member_id', $request->member_id]
                    ])
                    ->get();
                    $member_id = $request->member_id;
                $all_subject_ids = array_unique($examSubjectIds);    
                if ($is_result_available->count() > 0) {
                        return view('university::exam.unStudentFinalMarkSheetPrint', 
                        compact(
                        'exams',
                        'is_result_available', 
                        'subjects', 
                        'data',
                        'member_id', 
                        'studentDetails',
                        'exam_types', 
                        'assinged_exam_types',
                        'marks_grade',
                        'fail_grade_name',
                        'fail_grade',
                        'maxGrade',
                        'result_setting',
                        'record_id',
                        'all_subject_ids'
                    ));
                    } else {
                        Toastr::error('Ops! Your result is not found! Please check mark register.', 'Failed');
                        return redirect('student_mark_sheet_final');
                    }


            }else{
                    $studentDetails = StudentRecord::where('age_group_id',$request->age_group_id)
                                ->where('mgender_id',$request->mgender_id)
                                ->where('church_year_id',getAcademicId())
                                ->where('church_id',auth()->user()->church_id)
                                ->where('member_id',$request->member_id)
                                ->first();

                        $record = $studentDetails;  
                        $record_id = $record->id; 
                        $result_setting =  CustomResultSetting::where('church_id',Auth()->user()->church_id)
                        ->where('church_year_id',getAcademicId())
                        ->get();  
                        $grades = SmMarksGrade::where('church_id', Auth::user()->church_id)
                        ->where('church_year_id', getAcademicId())
                        ->orderBy('gpa', 'desc')
                        ->get();   
                        if($studentDetails){
                            $subjects = $studentDetails->assign_subject;
                            $all_subject_ids = $subjects->pluck('subject_id')->toArray();
                            $is_result_available = SmResultStore::where([
                                ['age_group_id', $studentDetails->age_group_id], 
                                ['mgender_id', $studentDetails->mgender_id], 
                                ['member_id', $studentDetails->member_id]])
                                ->where('church_id',Auth::user()->church_id)
                                ->get();
                        }
                        $student_detail = SmStudent::find($record->member_id);
                        if ($is_result_available->count() > 0) {
                            return view('backEnd.examination.studentFinalMarkSheetPrint',compact('subjects','studentDetails','all_subject_ids','is_result_available','record','record_id','result_setting','grades','student_detail')); 
                        }
                        else{
                            Toastr::warning('Result Not Completed', 'Failed');
                            return redirect('student_mark_sheet_final');
                        }
        }
        }
        catch(\Exception $e){
            Toastr::error('Operation Failed', 'Failed');
            return redirect('student_mark_sheet_final');
        }
     }
}
