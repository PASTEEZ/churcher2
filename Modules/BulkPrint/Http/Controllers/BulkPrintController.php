<?php

namespace Modules\BulkPrint\Http\Controllers;

use App\Role;
use App\SmClass;
use App\SmStaff;
use App\SmParent;
use App\SmStudent;
use App\SmBankAccount;
use App\SmStudentIdCard;
use App\SmGeneralSettings;
use App\SmHrPayrollGenerate;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\SmHrPayrollEarnDeduc;
use App\SmStudentCertificate;
use Modules\Lms\Entities\Course;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Routing\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Modules\Fees\Entities\FmFeesInvoice;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Support\Renderable;
use Modules\BulkPrint\Entities\InvoiceSetting;
use Modules\RolePermission\Entities\InfixRole;
use Modules\BulkPrint\Entities\FeesInvoiceSetting;
use Modules\Fees\Http\Controllers\FeesReportController;

class BulkPrintController extends Controller
{
    //
    public function __construct()
	{
        $this->middleware('PM');
       
	}
    public function getRoleWiseCertificate(Request $request)
    {

    }
    public function studentidBulkPrint(){
        try {
            $id_cards = SmStudentIdCard::where('active_status', 1)->where('church_id', Auth::user()->church_id)->get();
            $roles = InfixRole::where('is_saas',0)->where('active_status', '=', 1)
                ->where(function ($q) {
                    $q->where('church_id', Auth::user()->church_id)->orWhere('type', 'System');
                })
                ->where('id', '!=', 1)->get();
            return view('bulkprint::admin.generate_id_card', compact('id_cards','roles'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function studentidBulkPrintSearch(Request $request){
        try {
        $request->validate([
            'role' => 'required',
            'id_card' => 'required',         
            // 'grid_gap' => 'required',         
        ]);
         
        if($request->role==2){
            $s_students=SmStudent::query()->with('parents', 'bloodGroup');
            
           $s_students = $s_students->status()->get();
       }elseif($request->role==3){
           $studentGuardian = SmStudent::where('church_id', Auth::user()->church_id)->get('parent_id');
           $s_students = SmParent::whereIn('id',$studentGuardian)->get();
       }
       else{
           $s_students=SmStaff::whereRole($request->role)->status()->get();
       }
       $id_card = SmStudentIdCard::status()->find($request->id_card);

       $role_id=$request->role;

       $gridGap = $request->grid_gap !=null ? $request->grid_gap :15;
        return view('bulkprint::admin.id_card_bulk_print', ['id_card' => $id_card, 's_students' => $s_students,'role_id'=>$role_id,'gridGap'=>$gridGap]);
        
    
         $pdf = PDF::loadView('bulkprint::admin.id_card_bulk_print', ['id_card' => $id_card, 's_students' => $s_students,'role_id'=>$role_id]);
        //  return $pdf->stream($id_card->title . '.pdf');
        } catch (\Throwable $th) {
           Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function ajaxIdCard(Request $request){
        try {
            
            $role_id=$request->role_id;
            $id_cards = SmStudentIdCard::where('active_status',1)->get();
            $idCards=[];
            foreach($id_cards as $id_card){
                $role_ids=json_decode($id_card->role_id);
                if(in_array($role_id,$role_ids)){
                    $d['id']=$id_card->id;
                    $d['title']=$id_card->title;
                    $idCards[]=$d;
                }
            }
        
            return response()->json([$idCards]);

        } catch (\Throwable $th) {
          
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function ajaxRoleIdCard(Request $request){
        try {
            //code...
            $id=$request->id;
            $id_card = SmStudentIdCard::status()->find($id);
            $role_ids=json_decode($id_card->role_id);
            $roles=[];
            foreach($role_ids as $role){
                $d['id']=Role::find($role)->id;
                $d['name']=Role::find($role)->name;

                $roles[]=$d;
                
            }
        
            return response()->json([$roles]);
        } catch (\Throwable $th) {
           Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function staffidBulkPrint(){
        try {
            $id_cards = SmStudentIdCard::where('active_status', 1)->where('role_id','!=','["2"]')->where('church_id', Auth::user()->church_id)->get(['id','title']);
            $roles = Role::where('church_id', Auth::user()->church_id)->whereNotIn('id',[1,2,3])->get();
            return view('bulkprint::admin.staff_generate_id_card', compact('id_cards','roles'));
        } catch (\Exception $e) {
        
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function staffidBulkPrintSearch(Request $request){
        try {
         
            $inputs=$request->except('_token');
            $validator = Validator::make($inputs, [
                'role_id' => 'required|array',
                'id_card' => 'required'
            ]);
    
            if ($validator->fails()) {
             
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
    
        
            if($request->role==2){
                $s_students=SmStudent::query();
                if($request->class){
                    $s_students->where('age_group_id',$request->age_group_id);
                }
                if($request->section){
                    $request->where('mgender_id',$request->mgender_id);
                }
               $s_students=$s_students->status()->get();
             
    
           }else{
        //   return  $request->role_id;
               $s_students=SmStaff::whereIn('role_id',$request->role_id)->status()->get();
    
           }
           $id_card = SmStudentIdCard::status()->find($request->id_card);
    
              $role_id=$request->role;
  
         return view('bulkprint::admin.id_card_bulk_print', ['id_card' => $id_card, 's_students' => $s_students,'role_id'=>$role_id]);
    
         $pdf = PDF::loadView('bulkprint::admin.id_card_bulk_print', ['id_card' => $id_card, 's_students' => $s_students]);
         return $pdf->stream($id_card->title . '.pdf');
        } catch (\Throwable $th) {
            //throw $th;
             Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function settings(){
        $invoiceSettings=InvoiceSetting::where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->first();

        if(!$invoiceSettings){
            $invoiceSettings= new InvoiceSetting;
            $invoiceSettings->per_th=2;
            $invoiceSettings->prefix='SPN';
            $invoiceSettings->church_id= Auth()->user()->church_id;
            $invoiceSettings->church_year_id= getAcademicId();
            $invoiceSettings->save();
        }

        return view('bulkprint::feesCollection.invoice_settings',compact('invoiceSettings'));
    }
    
    public function settingsUpdate(Request $request){
        try {
        //  return  $request->all();
            if($request->copy_s_per_th && $request->copy_o_per_th && $request->copy_c_per_th ){
                $per_th=3;
            }elseif(($request->copy_s_per_th && $request->copy_o_per_th) || ($request->copy_s_per_th && $request->copy_c_per_th) || ($request->copy_o_per_th && $request->copy_c_per_th)){
                $per_th=2;
            }elseif($request->copy_s_per_th || $request->copy_o_per_th || $request->copy_c_per_th){
                $per_th=1;
            }else{
                $per_th=null;
               
            }
         
            if($per_th==null){
                Toastr::error('Please Select at least One page', 'Failed');
                return redirect()->back();
            }

            $invoiceSetting=InvoiceSetting::find($request->id);

            $invoiceSetting->per_th=$per_th;
            $invoiceSetting->member_name=$request->member_name;
            $invoiceSetting->member_gender=$request->member_gender;
            $invoiceSetting->member_group=$request->member_group;   
            $invoiceSetting->student_roll=$request->student_roll;
            $invoiceSetting->student_group=$request->student_group;
            $invoiceSetting->member_registration_no=$request->member_registration_no;

            $invoiceSetting->footer_1=$request->footer_1;
            $invoiceSetting->footer_2=$request->footer_2;
            $invoiceSetting->footer_3=$request->footer_3;
            $invoiceSetting->prefix=$request->prefix;
            $invoiceSetting->copy_s=$request->copy_s;
            $invoiceSetting->copy_o=$request->copy_o; 
            $invoiceSetting->copy_c=$request->copy_c;

            $invoiceSetting->c_signature_p=$request->copy_s_per_th=='on'? 1:0;
            $invoiceSetting->c_signature_o=$request->copy_o_per_th=='on'? 1:0; 
            $invoiceSetting->c_signature_c=$request->copy_c_per_th=='on'? 1:0;

            $invoiceSetting->signature_p=$request->signature_p;
            $invoiceSetting->signature_c=$request->signature_c;
            $invoiceSetting->signature_o=$request->signature_o;

            $invoiceSetting->copy_write_msg=$request->copy_write_msg;

            $invoiceSetting->updated_by=Auth::user()->id;
            $invoiceSetting->church_id=Auth::user()->church_id;
            $invoiceSetting->church_year_id= getAcademicId();
            $invoiceSetting->update();
         
           
            Toastr::success('Operation Successfully', 'Success');
            return redirect()->back();

        } catch (\Throwable $th) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
       

    }

    public function feeVoucherPrint(){
        try {
            $classes = SmClass::get();
            return view('bulkprint::feesCollection.fees_bulk_print',compact('classes'));
        } catch (\Exception $e) {
         
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function feeVoucherPrintSearch(Request $request)
    {
        try {
            $inputs=$request->except('_token');
            $validator = Validator::make($inputs, [
                'class' => 'required',
            ]);
    
            if ($validator->fails()) {
             
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            set_time_limit(2700);

            if (moduleStatusCheck('University')) {
                $model = StudentRecord::query();
                $students = universityFilter($model, $request)->get();
            } else {
                 $students = StudentRecord::query()->with('class', 'section', 'studentDetail.feesAssign', 'studentDetail.parents');
                if (!empty($request->section)) {
                    $students->where('mgender_id', $request->section);
                }
                $students = $students->where('age_group_id', $request->class)
                                ->where('church_year_id', getAcademicId())
                                ->where('church_id', Auth::user()->church_id)
                                ->get();
            }
            $invoiceSettings=InvoiceSetting::where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->first();

            return view('bulkprint::feesCollection.fees_payment_invoice_bulk_print')->with(['students' => $students,'invoiceSettings'=>$invoiceSettings]);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function payrollBulkPrint(){
        
		try{
			$roles = InfixRole::where('active_status', '=', '1')->where('id', '!=', 1)->where('id', '!=', 2)->where('id', '!=', 3)->where('id', '!=', 10)->where(function ($q) {
                $q->where('church_id', Auth::user()->church_id)->orWhere('type', 'System');
            })
			->orderBy('name','asc')
			->get();
			return view('bulkprint::humanResource.payroll.payroll_bulk_print', compact('roles'));
		}catch (\Exception $e) {
       
		   Toastr::error('Operation Failed', 'Failed');
		   return redirect()->back();
		}
    }

    public function payrollBulkPrintSearch(Request $request){
        try{
            $inputs=$request->except('_token');
            $validator = Validator::make($inputs, [
                // 'role_id' => "required",
                // 'payroll_month' => "required",
                // 'payroll_year' => "required"              
            ]);
            // return $request->all();

    
            if ($validator->fails()) {
             
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $role_id=$request->role_id;
            $month=$request->payroll_month;
            $year=$request->payroll_year;
             $staff_ids=SmStaff::query();
             if($request->role_id){           
                $staff_ids->whereRole($request->role_id);
             }
           
          $staff_ids= $staff_ids->where('church_id',Auth::user()->church_id)->get('id');

            $payrollDetails=SmHrPayrollGenerate::query()->with('staffDetails','staffDetails.departments','staffDetails.designations');
            if($request->payroll_month){
                $payrollDetails->where('payroll_month',$month);
            }
            if($request->payroll_year){
                $payrollDetails->where('payroll_year',$year);
            }
            if($request->role_id){
                $payrollDetails->whereIn('staff_id',$staff_ids);
            }
            $payrollDetails=$payrollDetails->where('church_id',Auth::user()->church_id)->get();

          if(count($payrollDetails)==0){
              Toastr::error('Not Found ! Generate Payroll', 'Failed');
		     return redirect()->back();
          }

			$schoolDetails = SmGeneralSettings::where('church_id',Auth::user()->church_id)->first();
		

			$payrollEarnDetails = SmHrPayrollEarnDeduc::where('active_status', '=', '1')->where('earn_dedc_type', '=', 'E')->where('church_id',Auth::user()->church_id)->get();

			$payrollDedcDetails = SmHrPayrollEarnDeduc::where('active_status', '=', '1')->where('earn_dedc_type', '=', 'D')->where('church_id',Auth::user()->church_id)->get();

			return view('bulkprint::humanResource.payroll.payroll_bulk_print_invoice', compact('payrollDetails', 'payrollEarnDetails', 'payrollDedcDetails', 'schoolDetails'));
		}catch (\Exception $e) {
         
		   Toastr::error('Operation Failed', 'Failed');
		   return redirect()->back();
		}
    }
    public function certificateBulkPrint(){
        try {
            $roles = InfixRole::where('id', '!=', 1)->Where('type', 'System')->get();
            $classes = SmClass::get();
            $certificates = SmStudentCertificate::get();
            return view('bulkprint::admin.generate_certificate_bulk', compact('roles','classes', 'certificates'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
 
    public function certificateBulkPrintSearch(Request $request)
    {
        try {
            // return $request->all();
            $inputs=$request->except('_token');
            $validator = Validator::make($inputs, [
                'certificate' => 'required'
            ]);
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            if (moduleStatusCheck('University')) {
                $model = StudentRecord::query();
                $member_ids = universityFilter($model, $request)->get()->pluck('member_id')->toArray();
            } else {
                $member_ids = StudentRecord::when($request->church_year, function ($query) use ($request) {
                    $query->where('church_year_id', $request->church_year);
                })
                ->when($request->certificateBulkClass, function ($query) use ($request) {
                    $query->whereIn('age_group_id', $request->certificateBulkClass);
                })
                ->when(!$request->church_year, function ($query) use ($request) {
                    $query->where('church_year_id', getAcademicId());
                })->where('church_id', auth()->user()->church_id)->get()->pluck('member_id')->toArray();
            }
            $data['students'] = SmStudent::whereIn('id', $member_ids)->get();
            $data['users'] =$data['students'] ;
            $data['certificate'] = SmStudentCertificate::find($request->certificate);

            $data['roles'] = InfixRole::where('id', '!=', 1)->Where('type', 'System')->get();
            $data['classes'] = SmClass::get();
            $data['certificates'] = SmStudentCertificate::get();
            $data['type'] = 'school';
            $data['gridGap'] = $request->grid_gap;
            return view('bulkprint::admin.student_certificate_bulk_print', $data);

            $pdf = PDF::loadView('bulkprint::admin.student_certificate_bulk_print', $data);
            $pdf->setPaper('A4', 'landscape');
            return $pdf->stream('certificate.pdf');
        } catch (\Throwable $th) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function lmsCertificateBulkPrint()
    {
        $courses= Course::get();
        return view('bulkprint::admin.lmsCertificate', compact('courses'));
    }

    public function lmsCertificateBulkPrintSeacrh(Request $request)
    {
        try {
            $courses = Course::find($request->course_id);
            $courseLogs= $courses->purchaseLogs;
            $studenId= [];
            foreach ($courseLogs as $courseLog) {
                $studenId []= $courseLog->member_id;
            }
            $users =SmStudent::whereIn('user_id', $studenId)->get();
            
            $certificate = SmStudentCertificate::find($courses->certificate_id);
            $gridGap = $request->grid_gap;

            $type = 'school';
           return view('backEnd.admin.certificate.certificate_print', compact('users', 'certificate', 'gridGap', 'type'));
        } catch(\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function feesInvoiceBulkPrint()
    {
        try {
            $classes = SmClass::where('church_id', auth()->user()->church_id)
                            ->where('church_year_id', getAcademicId())
                            ->get();
            return view('bulkprint::feesInvoice.feesInvoiceBulk', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function feesInvoiceBulkPrintSearch(Request $request)
    {
        try {
            $invoices  = FmFeesInvoice::when($request->class, function ($query) use ($request) {
                        $query->where('age_group_id', $request->class);
                    })
                    ->when($request->section, function ($query) use ($request) {
                        $query->whereHas('recordDetail', function ($q) use ($request) {
                            return $q->where('mgender_id', $request->section);
                        });
                    })
                    ->when($request->student, function ($query) use ($request) {
                        $query->whereHas('recordDetail', function ($q) use ($request) {
                            return $q->where('id', $request->student);
                        });
                    })
                    // if university module true
                    ->when($request->un_session_id, function ($query) use ($request) {
                        $query->whereHas('recordDetail', function ($q) use ($request) {
                            $q->where('un_session_id', $request->un_session_id);
                        });
                    })
                    ->when($request->un_faculty_id, function ($query) use ($request) {
                        
                        $query->whereHas('recordDetail', function ($q) use ($request) {
                            $q->where('un_faculty_id', $request->un_faculty_id);
                        });
                    })
                    ->when($request->un_department_id, function ($query) use ($request) {
                        
                        $query->whereHas('recordDetail', function ($q) use ($request) {
                            $q->where('un_department_id', $request->un_department_id);
                        });
                    })
                    ->when($request->un_church_year_id, function ($query) use ($request) {

                        $query->whereHas('recordDetail', function ($q) use ($request) {
                            $q->where('un_church_year_id', $request->un_church_year_id);
                        });
                    })
                    ->when($request->un_semester_id, function ($query) use ($request) {
                        
                        $query->whereHas('recordDetail', function ($q) use ($request) {
                            $q->where('un_semester_id', $request->un_semester_id);
                        });
                    })
                    ->when($request->un_semester_label_id, function ($query) use ($request) {
                        
                        $query->whereHas('recordDetail', function ($q) use ($request) {
                            $q->where('un_semester_label_id', $request->un_semester_label_id);
                        });
                    })
                    //end 
                    ->with('invoiceDetails')
                    ->where('church_id', auth()->user()->church_id)
                    ->where('church_year_id', getAcademicId())
                    ->get();

            $banks = SmBankAccount::where('active_status', '=', 1)
                    ->where('church_id', Auth::user()->church_id)
                    ->get();

            $invoiceSettings = FeesInvoiceSetting::where('church_id', Auth::user()->church_id)
                    ->first();
            if ($invoiceSettings->invoice_type == 'slip') {
                return view('bulkprint::feesInvoice.feesInvoiceBulkPrintSlip', compact('invoices', 'invoiceSettings'));
            } else {
                return view('bulkprint::feesInvoice.feesInvoiceBulkPrint', compact('banks', 'invoices'));
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function feesInvoiceBulkPrintSettings()
    {
        $feesInvoiceSettings = FeesInvoiceSetting::where('church_year_id', getAcademicId())
                            ->where('church_id', Auth::user()->church_id)
                            ->first();
        if(!$feesInvoiceSettings){
            $feesInvoiceSettings = new FeesInvoiceSetting();
            $feesInvoiceSettings->church_year_id = getAcademicId();
            $feesInvoiceSettings->church_id = Auth::user()->church_id;
            $feesInvoiceSettings->per_th = 2;
            $feesInvoiceSettings->invoice_type = 'invoice';
            $feesInvoiceSettings->save();
        }
        return view('bulkprint::feesInvoice.feesInvoiceSettings', compact('feesInvoiceSettings'));
    }

    public function feesInvoiceSettingsUpdate(Request $request)
    {
        try {
            if ($request->copy_s_per_th && $request->copy_o_per_th && $request->copy_c_per_th ){
                $per_th=3;
            } elseif (($request->copy_s_per_th && $request->copy_o_per_th) || ($request->copy_s_per_th && $request->copy_c_per_th) || ($request->copy_o_per_th && $request->copy_c_per_th)){
                $per_th=2;
            } elseif ($request->copy_s_per_th || $request->copy_o_per_th || $request->copy_c_per_th){
                $per_th=1;
            } else {
                $per_th=null;
            }

            if ($per_th==null) {
                Toastr::error('Please Select at least One page', 'Failed');
                return redirect()->back();
            }

            $invoiceSetting=FeesInvoiceSetting::find($request->id);
            $invoiceSetting->invoice_type=$request->invoice_type;
            $invoiceSetting->per_th=$per_th;
            $invoiceSetting->member_name=$request->member_name;
            $invoiceSetting->member_gender=$request->member_gender;
            $invoiceSetting->member_group=$request->member_group;   
            $invoiceSetting->student_roll=$request->student_roll;
            $invoiceSetting->student_group=$request->student_group;
            $invoiceSetting->member_registration_no=$request->member_registration_no;
            $invoiceSetting->footer_1=$request->footer_1;
            $invoiceSetting->footer_2=$request->footer_2;
            $invoiceSetting->footer_3=$request->footer_3;
            $invoiceSetting->copy_s=$request->copy_s;
            $invoiceSetting->copy_o=$request->copy_o; 
            $invoiceSetting->copy_c=$request->copy_c;
            $invoiceSetting->c_signature_p=$request->copy_s_per_th=='on'? 1:0;
            $invoiceSetting->c_signature_o=$request->copy_o_per_th=='on'? 1:0; 
            $invoiceSetting->c_signature_c=$request->copy_c_per_th=='on'? 1:0;
            $invoiceSetting->signature_p=$request->signature_p;
            $invoiceSetting->signature_c=$request->signature_c;
            $invoiceSetting->signature_o=$request->signature_o;
            $invoiceSetting->copy_write_msg=$request->copy_write_msg;
            $invoiceSetting->updated_by=Auth::user()->id;
            $invoiceSetting->church_id=Auth::user()->church_id;
            $invoiceSetting->church_year_id= getAcademicId();
            $invoiceSetting->update();

            Toastr::success('Operation Successfully', 'Success');
            return redirect()->route('fees-invoice-bulk-print-settings');
        } catch (\Throwable $th) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
