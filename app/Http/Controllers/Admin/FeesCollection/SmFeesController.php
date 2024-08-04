<?php


namespace App\Http\Controllers\Admin\FeesCollection;


use App\SmClass;
use App\SmParent;
use App\SmStudent;
use App\SmAddIncome;
use App\SmsTemplate;
use App\SmFeesAssign;
use App\SmFeesMaster;
use App\SmSmsGateway;
use App\SmBankAccount;
use App\SmFeesPayment;
use App\SmFeesDiscount;
use Twilio\Rest\Client;
use App\SmBankStatement;
use App\SmPaymentMethhod;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\SmFeesAssignDiscount;
use App\SmPaymentGatewaySetting;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Models\DirectFeesInstallmentAssign;
use Modules\University\Entities\UnFeesInstallmentAssign;
use App\Http\Requests\Admin\Accounts\SmFineReportSearchRequest;
use App\Models\DirectFeesReminder;
use App\Models\DirectFeesSetting;
use App\Models\DireFeesInstallmentChildPayment;
use App\Models\FeesInvoice;
use Modules\University\Entities\UnFeesInstallAssignChildPayment;

class SmFeesController extends Controller
{
    public function __construct()
	{
        $this->middleware('PM');
        // User::checkAuth();
	}

 


    public function feesGenerateModal(Request $request, $amount, $member_id, $type,$master,$assign_id, $record_id)
    {
        try {
            $amount = $amount;
            $master = $master;
            $fees_type_id = $type;
            $member_id = $member_id;

            $banks = SmBankAccount::where('church_id', Auth::user()->church_id)
                    ->get();

            $discounts = SmFeesAssignDiscount::where('member_id', $member_id)
                        ->where('record_id', $record_id)
                        ->where('fees_type_id', $fees_type_id)
                        ->where('church_id',Auth::user()->church_id)
                        ->first(); 

            $data['bank_info'] = SmPaymentGatewaySetting::where('gateway_name', 'Bank')
                                ->where('church_id', Auth::user()->church_id)
                                ->first();

            $data['cheque_info'] = SmPaymentGatewaySetting::where('gateway_name', 'Cheque')
                                ->where('church_id', Auth::user()->church_id)
                                ->first();

            $method['bank_info'] = SmPaymentMethhod::where('method', 'Bank')
                                ->where('church_id', Auth::user()->church_id)
                                ->first();

            $method['cheque_info'] = SmPaymentMethhod::where('method', 'Cheque')
                                    ->where('church_id', Auth::user()->church_id)
                                    ->first();

            return view('backEnd.feesCollection.fees_generate_modal', compact('amount','assign_id','master', 'discounts', 'fees_type_id', 'member_id', 'data', 'method','banks','record_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function feesGenerateModalChild(Request $request, $amount, $member_id, $type)
    {
        try {
            $amount = $amount;
            $fees_type_id = $type;
            $member_id = $member_id;
            $discounts = SmFeesAssignDiscount::where('member_id', $member_id)->where('church_id',Auth::user()->church_id)->get();

            $applied_discount = [];
            foreach ($discounts as $fees_discount) {
                $fees_payment = SmFeesPayment::select('fees_discount_id')->where('active_status',1)->where('fees_discount_id', $fees_discount->id)->where('church_id',Auth::user()->church_id)->first();
                if (isset($fees_payment->fees_discount_id)) {
                    $applied_discount[] = $fees_payment->fees_discount_id;
                }
            }

            return view('backEnd.feesCollection.fees_generate_modal_child', compact('amount', 'discounts', 'fees_type_id', 'member_id', 'applied_discount'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

   /* public function sendmessage($famount, $fstudent) 
    {
        $endPoint = env('MNOTIFY_SMS');
        $apiKey = env('MNOTIFY_KEY');
        $sender = env('MNOTIFY_SENDER_ID');
        $numbers = '0242724849';
        return  $famount . $fstudent;
          // message to send, must be string
        $message = "Thank you" .$fstudent. " You have paid an amount of GHS".$famount. "as your tithe";
    
        $url = $endPoint  . "?key=" .  $apiKey . "&to=" . $numbers . "&msg=" . $message . "&sender_id=" . $sender;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $result = curl_exec($ch);
        curl_close ($ch);
        return $this->interpret($result);
       }
  */
       
      private function interpret($code)
      {
          $status = '';
          switch ($code) {
              case '1000':
                  $status = 'Messages has been sent successfully';
                  return $status;
                  break;
              case '1002':
                  $status = 'SMS sending failed. Might be due to server error or other reason';
                  return $status;
                  break;
              case '1003':
                  $status = 'Insufficient SMS credit balance';
                  return $status;
                  break;
              case '1004':
                  $status = 'Invalid API Key';
                  return $status;
                  break;
              case '1005':
                  $status = 'Invalid recipient\'s phone number';
                  return $status;
                  break;
              case '1006':
                  $status = 'Invalid sender id. Sender id must not be more than 11 characters. Characters include white space';
                  return $status;
                  break;
              case '1007':
                  $status = 'Message scheduled for later delivery';
                  return $status;
                  break;
              case '1008':
                  $status = 'Empty Message';
                  return $status;
                  break;
              default:
                  return $status;
                  break;
          }
        }

    public function feesPaymentStore(Request $request)
    {
      
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        try {
           

        
            $user = Auth::user();
            $fees_payment = new SmFeesPayment();
            $fees_payment->member_id = $request->member_id;
            $fees_payment->fees_discount_id = !empty($request->fees_discount_id) ? $request->fees_discount_id : "";
            $fees_payment->discount_amount = !empty($request->applied_amount) ? $request->applied_amount : 0;
            $fees_payment->fine = !empty($request->fine) ? $request->fine : 0;
            $fees_payment->assign_id = $request->assign_id;
            $fees_payment->amount = !empty($request->amount) ? $request->amount : 0;
            $fees_payment->assign_id = $request->assign_id;
            $fees_payment->payment_date = date('Y-m-d', strtotime($request->date));
            $fees_payment->payment_mode = $request->payment_mode;
            $fees_payment->created_by = $user->id;
            $fees_payment->note = $request->note;
            $fees_payment->fine_title = $request->fine_title;
            $fees_payment->church_id = Auth::user()->church_id;
          
            $fees_payment->record_id = $request->record_id;
            $fees_payment->church_year_id = getAcademicid();
            
            $fees_payment->fees_type_id = $request->fees_type_id;
            
            $result = $fees_payment->save();
            $payment_mode_name=ucwords($request->payment_mode);
            $payment_method=SmPaymentMethhod::where('method',$payment_mode_name)->first();
            $income_head= generalSetting();

            $add_income = new SmAddIncome();
            $add_income->name = 'Tithe Collection';
            $add_income->date = date('Y-m-d', strtotime($request->date));
            $add_income->amount = $fees_payment->amount;
            $add_income->fees_collection_id = $fees_payment->id;
            $add_income->active_status = 1;
            $add_income->income_head_id = $income_head->income_head_id;
            $add_income->payment_method_id = $payment_method->id;
            $add_income->account_id = $request->bank_id;
            $add_income->created_by = Auth()->user()->id;
            $add_income->church_id = Auth::user()->church_id;
            $endPoint = env('MNOTIFY_SMS');
            $apiKey = env('MNOTIFY_KEY');
            $sender = env('MNOTIFY_SENDER_ID');
            $numbers = '0242724849';
             
              // message to send, must be string
            $message = "Thank you  You have paid an amount of GHS as your tithe";
        
            $url = $endPoint  . "?key=" .  $apiKey . "&to=" . $numbers . "&msg=" . $message . "&sender_id=" . $sender;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            $result = curl_exec($ch);
            curl_close ($ch);
            return $this->interpret($result);

            $add_income->save();
 
          if($payment_method->id==3){
                    $bank=SmBankAccount::where('id',$request->bank_id)
                    ->where('church_id',Auth::user()->church_id)
                    ->first();
                    $after_balance= $bank->current_balance + $request->amount;
                    
                    $bank_statement= new SmBankStatement();
                    $bank_statement->amount = $request->amount;
              $bank_statement->after_balance= $after_balance;
              $bank_statement->type= 1;
                    $bank_statement->details= "Fees Payment";
                    $bank_statement->payment_date= date('Y-m-d', strtotime($request->date));
                    $bank_statement->bank_id= $request->bank_id;
                    $bank_statement->church_id= Auth::user()->church_id;
                    $bank_statement->payment_method= $payment_method->id;
                    $bank_statement->fees_payment_id= $fees_payment->id;
                    $bank_statement->save();
    
                    $current_balance= SmBankAccount::find($request->bank_id);
                    $current_balance->current_balance=$after_balance;
                    $current_balance->update();
            }
           if(directFees()){
                $fees_assign = SmFeesAssign::where('fees_master_id',$request->master_id)
                ->where('member_id',$request->member_id)
                ->where('record_id',$request->record_id)
                ->where('church_id',Auth::user()->church_id)
                ->first();
            }else{
                $fees_assign = SmFeesAssign::where('fees_master_id',$request->master_id)
                ->where('member_id',$request->member_id)
                ->where('record_id',$request->record_id)
                ->where('church_id',Auth::user()->church_id)
                ->first();
                $fees_assign->fees_amount-=floatval($request->amount);
                $fees_assign->save();
             
           if (!empty($request->fine)) {
                    $fees_assign = SmFeesAssign::where('fees_master_id',$request->master_id)
                                ->where('member_id',$request->member_id)
                                ->where('record_id',$request->record_id)
                                ->where('church_id',Auth::user()->church_id)
                                ->first();
                    $fees_assign->fees_amount+=$request->fine;
                    $fees_assign->save();
                }
                
            }
            if ($result) {
                Toastr::success('Operation successful', 'Success');
              
               
                return redirect()->back();

               // return Redirect::route('fees_collect_student_wise', array('id' => $request->record_id));
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return Redirect::route('fees_collect_student_wise', array('id' => $request->record_id));
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    
   
    public function feesPaymentDelete(Request $request)
    {
        try {
            $assignFee=SmFeesAssign::find($request->assign_id);
            if($assignFee){
                $newAmount=$assignFee->fees_amount+$request->amount;
                $assignFee->fees_amount=$newAmount;
                $assignFee->save();
            }
            if (checkAdmin()) {
                $result = SmFeesPayment::destroy($request->id);
            }else{
                $result = SmFeesPayment::where('active_status',1)->where('id',$request->id)->where('church_id',Auth::user()->church_id)->delete();
            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        }catch(\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function searchFeesDueCopy(Request $request)
    {
        try {
            $classes = SmClass::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id',Auth::user()->church_id)
                        ->get();

            $fees_masters = SmFeesMaster::select('fees_group_id')
                            ->where('active_status', 1)
                            ->distinct('fees_group_id')
                            ->where('church_id',Auth::user()->church_id)
                            ->where('church_year_id', getAcademicId())
                            ->get();

            $students = StudentRecord::where('church_id',Auth::user()->church_id)
                        ->where('church_year_id', getAcademicId())
                        ->get();

            $fees_dues = [];
            $dues_fees_master = SmFeesMaster::select('id', 'amount','date')
                    ->where('church_year_id', getAcademicId())
                    ->where('church_id',Auth::user()->church_id)
                    ->get();
            foreach ($students as $student) {
                foreach($dues_fees_master as $fees_master) {
                    $total_amount = @$fees_master->amount;
                    $fees_assign = SmFeesAssign::where('member_id', $student->member_id)
                                ->where('record_id', $student->id)
                                ->where('fees_master_id', @$fees_master->id)
                                ->where('church_id',Auth::user()->church_id)
                                ->where('church_year_id', getAcademicId())
                                ->first();

                    $discount_amount = SmFeesAssign::where('member_id', $student->member_id)
                                    ->where('record_id', $student->id)
                                    ->where('church_year_id', getAcademicId())
                                    ->where('fees_master_id', @$fees_master->id)
                                    ->where('church_id',Auth::user()->church_id)
                                    ->sum('applied_discount');

                    $amount = SmFeesPayment::where('active_status',1)
                            ->where('member_id', $student->member_id)
                            ->where('record_id', $student->id)
                            ->where('church_year_id', getAcademicId())
                            ->sum('amount');

                    $paid = $discount_amount + $amount;

                    if ($fees_assign != "") {
                        if ($total_amount > $paid) {
                            $due_date= strtotime($fees_master->date);
                            $now =strtotime(date('Y-m-d'));
                            if ($due_date > $now ) {
                                continue;
                            }
                            $fees_dues[] = $fees_assign;
                        }
                    }
                }
            }
            return view('backEnd.feesCollection.search_fees_due', compact('classes', 'fees_masters', 'fees_dues'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function searchFeesDue(Request $request)
    {
        try {
            $classes = SmClass::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id',Auth::user()->church_id)
                        ->get();

            $fees_masters = SmFeesMaster::select('fees_group_id')
                            ->where('active_status', 1)
                            ->distinct('fees_group_id')
                            ->where('church_id',Auth::user()->church_id)
                            ->where('church_year_id', getAcademicId())
                            ->get();

            $students = StudentRecord::where('church_id',Auth::user()->church_id)
                        ->where('church_year_id', getAcademicId())
                        ->get();

            $fees_dues = [];
            $fees_due_ids = [];
        
            $fees_assigns = SmFeesAssign::with('feesGroupMaster', 'recordDetail', 'feesGroupMaster.feesTypes')->get();
            foreach ($fees_assigns as $assignFees) {
                $discount_amount = $assignFees->applied_discount;
                $total_amount = $assignFees->feesGroupMaster->amount;
                $amount = $assignFees->totalPaid;
                $paid = $discount_amount + $amount;
                
                if ($total_amount > $paid) {
                    $due_date= strtotime($assignFees->feesGroupMaster->date);
                    $now =strtotime(date('Y-m-d'));
                    if ($due_date > $now ) {
                        continue;
                    }
                    $fees_due_ids[] = $assignFees->id;
                }            
            }
            $fees_dues = $fees_assigns->whereIn('id', $fees_due_ids);
            return view('backEnd.feesCollection.search_fees_due', compact('classes', 'fees_masters', 'fees_dues'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function feesDueSearch(Request $request)
    {
        $input = $request->all();
        if(moduleStatusCheck('University')){
            $validator = Validator::make($input, [
                'un_session_id' => 'required',
                'un_semester_label_id' => 'required'
            ]);
        }
        elseif(directFees()){
            $validator = Validator::make($input, [
                'date_range' => 'required'
            ]);
        }
        else{
            $validator = Validator::make($input, [
                'fees_group' => 'required',
                'class' => 'required'
            ]);
        }


        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            if(moduleStatusCheck('University')){
                $rangeArr = $request->date_range ? explode('-', $request->date_range) : "".date('m/d/Y')." - ".date('m/d/Y')."";

                $date_from = new \DateTime(trim($rangeArr[0]));
                $date_to =  new \DateTime(trim($rangeArr[1]));

                $fees_dues = UnFeesInstallmentAssign::whereHas('recordDetail')->whereIn('active_status',[0,2])
                            ->where('un_semester_label_id',$request->un_semester_label_id)
                            ->when($request->date_range, function ($q) use ($date_from, $date_to) {
                                $q->where('due_date',  '>=', $date_from);
                                $q->where('due_date',  '<=', $date_to);
                            })
                            ->get();
                return view('backEnd.feesCollection.search_fees_due', compact('fees_dues','date_to', 'date_from'));
            }elseif(directFees()){
                $rangeArr = $request->date_range ? explode('-', $request->date_range) : "".date('m/d/Y')." - ".date('m/d/Y')."";

                $date_from = new \DateTime(trim($rangeArr[0]));
                $date_to =  new \DateTime(trim($rangeArr[1]));

                $classes = SmClass::get();

                $allStudent = StudentRecord::when($request->class, function ($q) use ($request) {
                    $q->where('age_group_id', $request->class);
                })
                ->when($request->section, function ($q) use ($request){
                    $q->where('mgender_id',$request->section);
                })
                ->where('church_year_id', getAcademicId())
                ->get();

                $fees_dues = DirectFeesInstallmentAssign::whereHas('recordDetail')->whereIn('active_status',[0,2])
                            ->whereIn('record_id', $allStudent->pluck('id'))
                            ->where('church_id', auth()->user()->church_id)
                            ->when($request->date_range, function ($q) use ($date_from, $date_to) {
                                $q->where('due_date',  '>=', $date_from);
                                $q->where('due_date',  '<=', $date_to);
                            })
                            ->get();
                return view('backEnd.feesCollection.search_fees_due', compact('fees_dues','date_to', 'date_from','classes'));
            }else{
                $fees_group = explode('-', $request->fees_group);
                $fees_master = SmFeesMaster::select('id', 'amount')
                            ->where('fees_group_id', $fees_group[0])
                            ->where('fees_type_id', $fees_group[1])
                            ->where('church_year_id', getAcademicId())
                            ->where('church_id',Auth::user()->church_id)
                            ->first();
    
                $studentRecord = StudentRecord::where('age_group_id', $request->class)
                            ->when($request->section, function($q) use ($request) {
                                $q->where('mgender_id', $request->section);
                            })->where('church_id',Auth::user()->church_id)
                            ->where('church_year_id', getAcademicId())
                            ->get();
    
                $fees_dues = [];
                foreach ($studentRecord as $record) {
                    $fees_master = SmFeesMaster::select('id', 'amount','date')
                                ->where('fees_group_id', $fees_group[0])
                                ->where('fees_type_id', $fees_group[1])
                                ->where('church_year_id', getAcademicId())
                                ->where('church_id',Auth::user()->church_id)
                                ->first();
                    $total_amount = $fees_master->amount;
                  
                    $fees_assign = SmFeesAssign::where('member_id', $record->member_id)
                                ->where('record_id', $record->id)
                                ->where('fees_master_id', $fees_master->id)
                                ->where('church_id',Auth::user()->church_id)
                                ->where('church_year_id', getAcademicId())
                                ->first();
    
                    $discount_amount = SmFeesAssign::where('member_id', $record->member_id)
                                    ->where('record_id', $record->id)
                                    ->where('church_year_id', getAcademicId())
                                    ->where('fees_master_id', $fees_master->id)
                                    ->where('church_id',Auth::user()->church_id)
                                    ->sum('applied_discount');
    
                    $amount = SmFeesPayment::where('active_status',1)
                            ->where('member_id', $record->member_id)
                            ->where('record_id', $record->id)
                            ->where('church_year_id', getAcademicId())
                            ->where('fees_type_id', $fees_group[1])
                            ->sum('amount');
    
                    $paid = $discount_amount + $amount;
                    if ($fees_assign != "") {
                        if ($total_amount > $paid) {
                            $fees_dues[] = $fees_assign;
                        }
                    }
                }
                $classes = SmClass::where('active_status', 1)
                        ->where('church_year_id', getAcademicId())
                        ->where('church_id',Auth::user()->church_id)
                        ->get();
    
                $fees_masters = SmFeesMaster::select('fees_group_id')
                                ->where('active_status', 1)
                                ->distinct('fees_group_id')
                                ->where('church_year_id', getAcademicId())
                                ->where('church_id',Auth::user()->church_id)
                                ->get();
    
                $age_group_id = $request->class;
                $fees_group_id = $fees_group[1];
                return view('backEnd.feesCollection.search_fees_due', compact('classes', 'fees_masters', 'fees_dues', 'age_group_id', 'fees_group_id'));
            }


           
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function sendDuesFeesEmail(Request $request){
        try{
            if(empty($request->student_list)) {
                Toastr::warning('Student Not Found', 'Warning');
                return redirect()->back();
            }
            if(isset($request->send_email)){
              
                $systemEmail = SmsTemplate::first();
                foreach($request->student_list as $student){
                    $student_detail = SmStudent::where('id', $student)->first();                    
                    $fees_info['dues_fees'] = $request->dues_amount[$student];
                    $fees_info['fees_master'] = $request->fees_master;

                    $compact['student_detail']=$student_detail;
                    $compact['fees_info']=$fees_info;

                    if ($student_detail && $student_detail->email) {
                       send_mail($student_detail->email, $student_detail->full_name, 'Dues Payment' , 'backEnd.feesCollection.dues_fees_email', $compact);                      
                    } 
                    if($student_detail) {
                        $parent_detail = SmParent::where('id', $student_detail->parent_id)->first();
                        if($parent_detail && $parent_detail->guardians_email != "" && $parent_detail->guardians_email !=null){
                        send_mail($parent_detail->guardians_email, $parent_detail->guardians_name, 'Dues Payment' , 'backEnd.feesCollection.dues_fees_email', $compact);                     
                        }
                    }
                }
               

            }elseif(isset($request->send_sms)){
                

                foreach ($request->student_list as $student) {

                    $student_detail = SmStudent::find($student);
                    $parent_detail = $student_detail && $student_detail->parent_id ? SmParent::find($student_detail->parent_id) : null;

                    $fees_info['dues_fees'] = $request->dues_amount[$student];
                    $fees_info['fees_master'] = $request->fees_master;

                    $email_template = SmsTemplate::where('id',1)->first();

                    $body = $email_template->dues_fees_message_sms;

                    $chars = preg_split('/[\s,]+/', $body, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

                    foreach($chars as $item){
                        if(strstr($item[0],"[")){
                            $str= str_replace('[','',$item);
                            $str= str_replace(']','',$str);
                            $str= str_replace('.','',$str);
                            $custom_array[$item]= SmsTemplate::getValueByStringDuesFees($student_detail, $str, $fees_info);
                        }

                    }

                    foreach($custom_array as $key=>$value){
                        $body= str_replace($key,$value,$body);
                    }

                    $activeSmsGateway = SmSmsGateway::where('active_status', 1)->first();
                    

                    if($activeSmsGateway->gateway_name == 'Twilio'){
                        
                        $account_id         = $activeSmsGateway->twilio_account_sid; // Your Account SID from www.twilio.com/console
                        $auth_token         = $activeSmsGateway->twilio_authentication_token; // Your Auth Token from www.twilio.com/console
                        $from_phone_number  = $activeSmsGateway->twilio_registered_no;

                        $client = new Client($account_id, $auth_token);


                        // student sms

                        if ($student_detail && $student_detail->mobile != "") {

                            $result = $message = $client->messages->create($student_detail->mobile, array('from' => $from_phone_number, 'body' => $body));

                        }

                        // guardian sms
                        if($parent_detail && $parent_detail->guardians_mobile != ""){

                            $result = $message = $client->messages->create($parent_detail->guardians_mobile, array('from' => $from_phone_number, 'body' => $body));
                        }

                    }
                    else if ($activeSmsGateway->gateway_name == 'Himalayasms') {
                          
                            if($student_detail && $student_detail->mobile != ""){ 
                                
                                $client = new HttpClient();
                                    $request = $client->get( "https://sms.techhimalaya.com/base/smsapi/index.php", [
                                    'query' => [
                                        'key' => $activeSmsGateway->himalayasms_key,
                                        'senderid' => $activeSmsGateway->himalayasms_senderId,
                                        'campaign' => $activeSmsGateway->himalayasms_campaign,
                                        'routeid' => $activeSmsGateway->himalayasms_routeId ,
                                        'contacts' => $student_detail->mobile,
                                        'msg' => $body,
                                        'type' => "text"
                                    ],
                                    'http_errors' => false
                                 ]);
                        
                                    $result = $request->getBody();
                        }
                        

                        if($parent_detail && $parent_detail->fathers_mobile != ""){
                            
                            $client = new HttpClient();
                                $request = $client->get( "https://sms.techhimalaya.com/base/smsapi/index.php", [
                                'query' => [
                                    'key' => $activeSmsGateway->himalayasms_key,
                                    'senderid' => $activeSmsGateway->himalayasms_senderId,
                                    'campaign' => $activeSmsGateway->himalayasms_campaign,
                                    'routeid' => $activeSmsGateway->himalayasms_routeId ,
                                    'contacts' => $parent_detail->fathers_mobile,
                                    'msg' => $body,
                                    'type' => "text"
                                ],
                                'http_errors' => false
                            ]);
                    
                            $result = $request->getBody();
                        }

                       
                    
                        }

                    elseif ($activeSmsGateway->gateway_name == 'Msg91') {
                       
                        $msg91_authentication_key_sid = $activeSmsGateway->msg91_authentication_key_sid;
                        $msg91_sender_id = $activeSmsGateway->msg91_sender_id;
                        $msg91_route = $activeSmsGateway->msg91_route;
                        $msg91_country_code = $activeSmsGateway->msg91_country_code;

                         if($student_detail && $student_detail->mobile != ""){

                            $curl = curl_init();

                            $url = "https://api.msg91.com/api/sendhttp.php?mobiles=" . $student_detail->mobile . "&authkey=" . $msg91_authentication_key_sid . "&route=" . $msg91_route . "&sender=" . $msg91_sender_id . "&message=" . $body . "&country=91";

                            curl_setopt_array($curl, array(
                                CURLOPT_URL => $url,
                                CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 30, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "GET", CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0,
                            ));
                            $response = curl_exec($curl);
                            $err = curl_error($curl);
                            curl_close($curl);

                        }

                       if($parent_detail && $parent_detail->guardians_mobile != ""){

                            $curl = curl_init();

                            $url = "https://api.msg91.com/api/sendhttp.php?mobiles=" . $parent_detail->guardians_mobile . "&authkey=" . $msg91_authentication_key_sid . "&route=" . $msg91_route . "&sender=" . $msg91_sender_id . "&message=" . $body . "&country=91";

                            curl_setopt_array($curl, array(
                                CURLOPT_URL => $url,
                                CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 30, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "GET", CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0,
                            ));
                            $response = curl_exec($curl);
                            $err = curl_error($curl);
                            curl_close($curl);

                        }
                    }

                }

            }

            Toastr::success('Operation successful', 'Success');
            return redirect()->back();


        } catch (\Exception $e) {
           
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }



    }
    public function feesStatemnt(Request $request)
    {
        try {
            $classes = SmClass::get();                  
            return view('backEnd.feesCollection.fees_statment', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function feesStatementSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'student' => 'required',
            'class' => 'required',
            'section' => 'required',
        ]);


        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $classes = SmClass::get();
            $fees_masters = SmFeesMaster::select('fees_group_id')->distinct('fees_group_id')->get();
            $student = SmStudent::find($request->student);
            $fees_assigneds = SmFeesAssign::where('member_id', $request->student)->where('church_id',Auth::user()->church_id)->get();
            if ($fees_assigneds->count() <= 0) {
                Toastr::error('Fees assigned not yet!');
                return redirect()->back();
            }
            else
            $fees_discounts = SmFeesAssignDiscount::where('member_id', $request->student)->where('church_id',Auth::user()->church_id)->get();
            $applied_discount = [];
            foreach ($fees_discounts as $fees_discount) {
                $fees_payment = SmFeesPayment::where('active_status',1)->select('fees_discount_id')->where('fees_discount_id', $fees_discount->id)->where('church_id',Auth::user()->church_id)->first();
                if (isset($fees_payment->fees_discount_id)) {
                    $applied_discount[] = $fees_payment->fees_discount_id;
                }
            }
            $age_group_id = $request->class;
            return view('backEnd.feesCollection.fees_statment', compact('classes', 'fees_masters', 'fees_assigneds', 'fees_discounts', 'applied_discount', 'student', 'age_group_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }



    public function feesInvoice($sid, $pid, $faid)
    {
        try {
            return view('backEnd.feesCollection.fees_collect_invoice');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function feesGroupPrint($id)
    {
        $fees_assigned = SmFeesAssign::find($id);
        $student = SmStudent::find($fees_assigned->member_id);
    }

    public function feesPaymentPrint($id, $group)
    {
        try {
            // $payment = SmFeesPayment::find($id);
             if (checkAdmin()) {
                    $payment = SmFeesPayment::find($id);
                }else{
                    $payment = SmFeesPayment::where('active_status',1)->where('id',$id)->where('church_id',Auth::user()->church_id)->first();
                }
            $group = $group;
            $student = SmStudent::find($payment->member_id);
            $pdf = PDF::loadView('backEnd.feesCollection.fees_payment_print', ['payment' => $payment, 'group' => $group, 'student' => $student]);
            return $pdf->stream(date('d-m-Y') . '-' . $student->full_name . '-fees-payment-details.pdf');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function feesPaymentInvoicePrint($id, $s_id)
    {
        
        try {
            set_time_limit(2700);
            $groups = explode("-", $id);
            $student = StudentRecord::find($s_id);
            if(moduleStatusCheck('University')){
                foreach ($groups as $group) {
                    $fees_assigneds[] = UnFeesInstallmentAssign::find($group);
                }
            }elseif(directFees()){
                foreach ($groups as $group) {
                    $fees_assigneds[] = DirectFeesInstallmentAssign::find($group);
                }
            }
            else{
                foreach ($groups as $group) {
                    $fees_assigneds[] = SmFeesAssign::find($group);
                }
            }
            
            $parent = SmParent::where('id', $student->studentDetail->parent_id)
                    ->where('church_id',Auth::user()->church_id)
                    ->first();

            $unapplied_discount_amount = SmFeesAssignDiscount::where('member_id',$student->member_id)
                                        ->where('record_id',$student->id)
                                        ->where('church_id',Auth::user()->church_id)
                                        ->sum('unapplied_amount');
            return view('backEnd.feesCollection.fees_payment_invoice_print')->with(['fees_assigneds' => $fees_assigneds, 'student' => $student,'unapplied_discount_amount'=>$unapplied_discount_amount, 'parent' => $parent,'id'=>$id]);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function feesGroupsPrint($id, $s_id)
    {
        try {
            $groups = explode("-", $id);
            $student = SmStudent::find($s_id);
            foreach ($groups as $group) {
                $fees_assigneds[] = SmFeesAssign::find($group);
            }
            $pdf = PDF::loadView('backEnd.feesCollection.fees_groups_print', ['fees_assigneds' => $fees_assigneds, 'student' => $student]);
            return $pdf->stream(date('d-m-Y') . '-' . $student->full_name . '-fees-groups-details.pdf');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

 

    public function studentFineReport(Request $request)
    {
        try {
            return view('backEnd.reports.student_fine_report');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentFineReportSearch(Request $request)
    {
        try {
            $date_from = date('Y-m-d', strtotime($request->date_from));
            $date_to = date('Y-m-d', strtotime($request->date_to));
            $fees_payments = SmFeesPayment::where('active_status',1)->where('payment_date', '>=', $date_from)->where('payment_date', '<=', $date_to)->where('fine', '!=', 0)->where('church_id',Auth::user()->church_id)->get();
            return view('backEnd.reports.student_fine_report', compact('fees_payments'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    //


    public function fineReport(){
        $classes = SmClass::get();            
        return view('backEnd.accounts.fine_report',compact('classes'));
    }

    public function fineReportSearch(SmFineReportSearchRequest $request){

        $rangeArr = $request->date_range ? explode('-', $request->date_range) : "".date('m/d/Y')." - ".date('m/d/Y')."";

        try {
            $classes = SmClass::get();

            if($request->date_range){
                $date_from = new \DateTime(trim($rangeArr[0]));
                $date_to =  new \DateTime(trim($rangeArr[1]));
            }

            if($request->date_range ){
                $fine_info = SmFeesPayment::where('active_status',1)->where('payment_date', '>=', $date_from)
                                ->where('payment_date', '<=', $date_to)
                                ->where('church_id',Auth::user()->church_id)
                                ->get();

                $fine_info = $fine_info->groupBy('member_id');
            }

            if($request->class){
                $students=SmStudent::where('age_group_id',$request->class)                        
                        ->get();

                $fine_info = SmFeesPayment::where('active_status',1)->where('payment_date', '>=', $date_from)
                                ->where('payment_date', '<=', $date_to)
                                ->where('church_id',Auth::user()->church_id)
                                ->whereIn('member_id', $students)
                                ->get();
                $fine_info = $fine_info->groupBy('member_id');

            }

            if($request->class && $request->section){

                $students=SmStudent::where('age_group_id',$request->class)
                        ->where('mgender_id',$request->section)                      
                        ->get();

                $fine_info = SmFeesPayment::where('active_status',1)->where('payment_date', '>=', $date_from)
                                ->where('payment_date', '<=', $date_to)
                                ->where('church_id',Auth::user()->church_id)
                                ->whereIn('member_id', $students)
                                ->get();

                $fine_info = $fine_info->groupBy('member_id');
            }
            return view('backEnd.accounts.fine_report',compact('classes','fine_info'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function directFeesGenerateModal(Request $request, $amount , $installment_id, $record_id)
    {
        try {
            $amount = $amount;
            $studentRerod = StudentRecord::find($record_id);
            $member_id =   $studentRerod->member_id; 

            $banks = SmBankAccount::where('church_id', Auth::user()->church_id)
                    ->get();
            $discounts = [];
            $data['bank_info'] = SmPaymentGatewaySetting::where('gateway_name', 'Bank')
                                ->where('church_id', Auth::user()->church_id)
                                ->first();

            $data['cheque_info'] = SmPaymentGatewaySetting::where('gateway_name', 'Cheque')
                                ->where('church_id', Auth::user()->church_id)
                                ->first();

            $method['bank_info'] = SmPaymentMethhod::where('method', 'Bank')
                                ->where('church_id', Auth::user()->church_id)
                                ->first();

            $method['cheque_info'] = SmPaymentMethhod::where('method', 'Cheque')
                                    ->where('church_id', Auth::user()->church_id)
                                    ->first();
            $installment = DirectFeesInstallmentAssign::find($installment_id);
            $balace_amount = discountFees( $installment->id) -  $installment->paid_amount;
            return view('backEnd.feesCollection.directFees.fees_generate_modal', compact('amount','discounts', 'installment_id', 'member_id', 'data', 'method','banks','record_id','balace_amount'));
        }catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function directFeesInstallmentUpdate(Request $request)
    {
        $request->validate([
            'amount' => "required",
            'due_date' => "required"
        ]);

        $installment = DirectFeesInstallmentAssign::find($request->installment_id);
        $installment->amount = $request->amount;
        $installment->due_date = date('Y-m-d', strtotime($request->due_date));
        if($installment->fees_discount_id){
            $fees_discount = SmFeesDiscount::find($installment->fees_discount_id);
            $installment->discount_amount =  ($installment->amount * $fees_discount->amount) / 100; 
        }
        $installment->save();
        Toastr::success('Operation Successfull', 'Success');
        return redirect()->back();
    }


    public function editSubPaymentModal($payment_id,$amount)
    {
        $payment = DireFeesInstallmentChildPayment::find($payment_id);
      
        return view('backEnd.feesCollection.directFees.editSubPaymentModal', compact('amount','payment'));
    }


    public function updateSubPaymentModal(Request $request)
    {
        $payment = DireFeesInstallmentChildPayment::find($request->sub_payment_id);
        $installment = DirectFeesInstallmentAssign::find($payment->direct_fees_installment_assign_id);
        $amount = $request->amount;
        $dif_amount = $request->amount - $payment->paid_amount ;
        if($payment){
            $payment->paid_amount = $request->amount;
            $payment->amount = $request->amount;
            $payment->balance_amount = $payment->balance_amount - $dif_amount ;
            $payment->payment_date = date('Y-m-d', strtotime($request->payment_date));
            $payment->save();
            $sm_fees_payment = SmFeesPayment::where('installment_payment_id',$payment->id)->first();
            if($sm_fees_payment){
                $sm_fees_payment->payment_date = date('Y-m-d', strtotime($request->payment_date));
                $sm_fees_payment->amount = $request->amount;
                $sm_fees_payment->save();
            }
            $installment->paid_amount = ( $installment->paid_amount + $dif_amount);
            if($installment->paid_amount == discountFees($installment->id)){
                $installment->active_status = 1;
            }
            elseif($installment->paid_amount != discountFees($installment->id)){
                $installment->active_status = 2;
            }
            $installment->save();
        }
        Toastr::success('Operation Successfull', 'Success');
        return redirect()->back();
    }

    public function deleteSubPayment(Request $request)
    {
        $payment = DireFeesInstallmentChildPayment::find($request->sub_payment_id);
        if($payment){
            $installment = DirectFeesInstallmentAssign::find($payment->direct_fees_installment_assign_id);
            $installment->paid_amount = $installment->paid_amount - $payment->paid_amount ;            
            if(($installment->paid_amount == 0)){
                $installment->active_status = 0;
            }elseif($installment->paid_amount == discountFees($installment->id)){
                $installment->active_status = 1;
            }else{
                $installment->active_status = 2;
            }
            $installment->save();
            $fees_payment = SmFeesPayment::where('installment_payment_id',$payment->id)->first();
            if($fees_payment){
                $income = SmAddIncome::where('fees_collection_id',$fees_payment->id)->first();
                    $statement = SmBankStatement::where('fees_payment_id',$fees_payment->id)->first();
                    if($statement){
                        $bank=SmBankAccount::where('id',$statement->bank_id)
                                            ->where('church_id',Auth::user()->church_id)
                                            ->first();
                        $after_balance= $bank->current_balance - $statement->amount;
                        $bank->current_balance = $after_balance;
                        $bank->update();
                        $statement->delete();
                    }
                
                if($income){
                    $income->delete();
                }
                $fees_payment->delete();
            }
             $payment->delete();
        }
        Toastr::success('Operation Successfull', 'Success');
        return redirect()->back();
    }

    public function viewPaymentReceipt($id){
        
        $feesInstallment = DireFeesInstallmentChildPayment::find($id);
        $oldPayments = DireFeesInstallmentChildPayment::where('id','<',$id)->where('direct_fees_installment_assign_id',$feesInstallment->direct_fees_installment_assign_id)->where('active_status',1)->sum('paid_amount');
        $student = StudentRecord::find($feesInstallment->record_id);
        return view('backEnd.feesCollection.directFees.viewPaymentReceipt', compact('feesInstallment','student','id','oldPayments'));
    }


    public function directFeesSetting(){

        $data['model'] = DirectFeesSetting::where('church_id', auth()->user()->church_id)
        
        ->first();
        
        $data['feesInvoice'] = FeesInvoice::where('church_id', auth()->user()->church_id)
                        ->first();

        $data['paymentReminder'] = DirectFeesReminder::where('church_id', auth()->user()->church_id)
                        
                        ->first();

        if(is_null($data['model'])){
           $new_model = new DirectFeesSetting();
           $new_model->church_id = Auth::user()->church_id;
           $new_model->save();
           $data['model'] = $new_model; 
        }  
        
        if(is_null($data['feesInvoice'])){
            $new_feesInvoice = new FeesInvoice();
            $new_feesInvoice->church_id = Auth::user()->church_id;
            $new_feesInvoice->prefix = "ABC_";
            $new_feesInvoice->start_form = 1;
            $new_feesInvoice->save();
            $data['feesInvoice'] = $new_feesInvoice; 
         } 

         if(is_null($data['paymentReminder'])){
            $new_paymentReminder = new DirectFeesReminder();
            $new_paymentReminder->church_id = Auth::user()->church_id;
            $new_paymentReminder->due_date_before = 5;
            $new_paymentReminder->notification_types = "";
            $new_paymentReminder->save();
            $data['paymentReminder'] = $new_paymentReminder; 
         } 


        return view('backEnd.feesCollection.directFees.directFeesSetting')->with($data);
    }

    public function feesInvoiceUpdate(Request $request){
        
        try{
            $setting = FeesInvoice::where('church_id', $request->church_id)->first();
            if($setting){
                $new = $setting;
            }else{
                $new = new FeesInvoice();
            }
            $new->prefix = $request->prefix;
            $new->start_form = $request->start_form ;
            $new->church_id= $request->church_id ;
            $new->save() ;

            Toastr::success('Operation Successfull', 'Success');
            return redirect()->back();
        }
        catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function paymentReminder(Request $request){
        
        try{
            $setting = DirectFeesReminder::where('church_id', $request->church_id)->first();
            if($setting){
                $new = $setting;
            }else{
                $new = new DirectFeesReminder();
            }
            $new->due_date_before = $request->due_date_before;
            $new->notification_types = json_encode($request->notification_types) ;
            $new->church_id= $request->church_id ;
            $new->save() ;
            
            Toastr::success('Operation Successfull', 'Success');
            return redirect()->back();
        }
        catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function directFeesTotalPayment($record_id){

        try{
            $studentRerod = StudentRecord::find($record_id);
            $member_id =   $studentRerod->member_id; 
    
            $banks = SmBankAccount::where('church_id', Auth::user()->church_id)
                    ->get();
            $discounts = [];
            $data['bank_info'] = SmPaymentGatewaySetting::where('gateway_name', 'Bank')
                                ->where('church_id', Auth::user()->church_id)
                                ->first();
    
            $data['cheque_info'] = SmPaymentGatewaySetting::where('gateway_name', 'Cheque')
                                ->where('church_id', Auth::user()->church_id)
                                ->first();
    
            $method['bank_info'] = SmPaymentMethhod::where('method', 'Bank')
                                ->where('church_id', Auth::user()->church_id)
                                ->first();
    
            $method['cheque_info'] = SmPaymentMethhod::where('method', 'Cheque')
                                    ->where('church_id', Auth::user()->church_id)
                                    ->first();
            $total_amount = DirectFeesInstallmentAssign::where('record_id', $record_id)->sum('amount');
            $total_discount = DirectFeesInstallmentAssign::where('record_id', $record_id)->sum('discount_amount');
            $total_paid = DirectFeesInstallmentAssign::where('record_id', $record_id)->sum('paid_amount');
            $balace_amount = $total_amount -  ($total_discount +  $total_paid);
            $amount = $balace_amount;
            return view('backEnd.feesCollection.directFees.total_payment_modal', compact('amount','discounts',  'member_id', 'data', 'method','banks','record_id','balace_amount'));

        }
        catch(\Exception $e){
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function directFeesTotalPaymentSubmit(Request $request){
        try {
            $record_id = $request->record_id;
            $member_id = $request->member_id;
            $request_amount = $request->request_amount;
            $after_paid = $request_amount;
            
            $installments = DirectFeesInstallmentAssign::where('record_id', $record_id)->get();
            $total_paid = $installments->sum('paid_amount');
            $total_amount = $installments->sum('amount');
            $total_discount = $installments->sum('discount_amount');
            $balace_amount = $total_amount - ($total_discount +  $total_paid);
            if($balace_amount <  $request_amount){
                Toastr::error('Amount is greater than due', 'Failed');
                return redirect()->back();
            }
            
            foreach($installments as $installment){
                if($after_paid <= 0){
                    break;
                }
                $installment_due = $installment->amount - ($installment->discount_amount +  $installment->paid_amount);
                if($installment_due && $after_paid > 0){
                    if($installment_due >= $after_paid){
                        $paid_amount = $after_paid;
                    }else{
                        $paid_amount  = $installment_due;
                    }
                        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                        $fileName = "";
                        if ($request->file('slip') != "") {
                            $file = $request->file('slip');
                            $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                            $file->move('public/uploads/bankSlip/', $fileName);
                            $fileName = 'public/uploads/bankSlip/' . $fileName;
                        }
            
                        $discount_group = explode('-', $request->discount_group);
                        $user = Auth::user();
                        $fees_payment = new SmFeesPayment();
                        $fees_payment->member_id = $request->member_id;
                        $fees_payment->fees_discount_id = !empty($request->fees_discount_id) ? $request->fees_discount_id : "";
                        $fees_payment->discount_amount = !empty($request->applied_amount) ? $request->applied_amount : 0;
                        $fees_payment->fine = !empty($request->fine) ? $request->fine : 0;
                        $fees_payment->assign_id = $request->assign_id;
                        $fees_payment->amount = $paid_amount;
                        $fees_payment->assign_id = $request->assign_id;
                        $fees_payment->payment_date = date('Y-m-d', strtotime($request->date));
                        $fees_payment->payment_mode = $request->payment_mode;
                        $fees_payment->created_by = $user->id;
                        $fees_payment->note = $request->note;
                        $fees_payment->fine_title = $request->fine_title;
                        $fees_payment->church_id = Auth::user()->church_id;
                        $fees_payment->slip = $fileName;
                        $fees_payment->record_id = $request->record_id;
                        $fees_payment->church_year_id = getAcademicid();
                        if(moduleStatusCheck('University')){
                            $fees_payment->un_church_year_id = getAcademicId();
                            $fees_payment->un_fees_installment_id  = $request->installment_id;
                            $fees_payment->un_semester_label_id = $request->un_semester_label_id;
                    
                            $payable_amount =  discountFeesAmount($installment->id);
                            $sub_payment = $installment->payments->sum('paid_amount');
                            $direct_payment =  $installment->paid_amount;
                            $total_paid =  $sub_payment + $direct_payment;
                            $installment->payment_date = date('Y-m-d', strtotime($request->date));
                    
                            $last_inovoice = UnFeesInstallAssignChildPayment::where('church_id',auth()->user()->church_id)->max('invoice_no');
                            $new_subPayment = new UnFeesInstallAssignChildPayment();
                            $new_subPayment->un_fees_installment_assign_id = $installment->id;
                            $new_subPayment->invoice_no = ( $last_inovoice +1 ) ?? 1;
                            $new_subPayment->amount = $paid_amount;
                            $new_subPayment->paid_amount = $paid_amount;
                            $new_subPayment->payment_date = $fees_payment->payment_date;
                            $new_subPayment->payment_mode =  $fees_payment->payment_mode;
                            $new_subPayment->note = $request->note;
                            $new_subPayment->slip = $fileName;
                            $new_subPayment->active_status = 1;
                            $new_subPayment->bank_id = $request->bank_id;
                            $new_subPayment->discount_amount = 0;
                            $new_subPayment->fees_type_id =  $installment->fees_type_id;
                            $new_subPayment->member_id = $request->member_id;
                            $new_subPayment->record_id = $request->record_id;
                            $new_subPayment->un_semester_label_id = $request->un_semester_label_id;;
                            $new_subPayment->un_church_year_id = getAcademicId();
                            $new_subPayment->created_by = Auth::user()->id;
                            $new_subPayment->updated_by =  Auth::user()->id;
                            $new_subPayment->church_id = Auth::user()->church_id;
                            $new_subPayment->balance_amount = ( $payable_amount - ($sub_payment + $paid_amount) ); 
                            $new_subPayment->save();
                            $fees_payment->installment_payment_id = $new_subPayment->id;
                            if(($sub_payment + $paid_amount) == $payable_amount){
                                $installment->active_status = 1;
                            }else{
                                $installment->active_status = 2;
                            }
                            $installment->paid_amount = $sub_payment + $paid_amount;
                            $installment->save();
                            
                        }elseif(directFees()){
                            $payable_amount =  discountFees($installment->id);
                            $sub_payment = $installment->payments->sum('paid_amount');
                            $direct_payment =  $installment->paid_amount;
                            $total_paid =  $sub_payment + $direct_payment;
                            $fees_payment->direct_fees_installment_assign_id = $installment->id;
                            $fees_payment->church_year_id = getAcademicId();
            
                            $last_inovoice = DireFeesInstallmentChildPayment::where('church_id',auth()->user()->church_id)->max('invoice_no');
                            $new_subPayment = new DireFeesInstallmentChildPayment();
                            $new_subPayment->direct_fees_installment_assign_id = $installment->id;
                            $new_subPayment->invoice_no = ( $last_inovoice +1 ) ?? 1;
                            $new_subPayment->direct_fees_installment_assign_id = $installment->id;
                            $new_subPayment->amount = $paid_amount;
                            $new_subPayment->paid_amount = $paid_amount;
                            $new_subPayment->payment_date = $fees_payment->payment_date;
                            $new_subPayment->payment_mode =  $fees_payment->payment_mode;
                            $new_subPayment->note = $request->note;
                            $new_subPayment->slip = $fileName;
                            $new_subPayment->active_status = 1;
                            $new_subPayment->bank_id = $request->bank_id;
                            $new_subPayment->discount_amount = 0;
                            $new_subPayment->fees_type_id =  $installment->fees_type_id;
                            $new_subPayment->member_id = $request->member_id;
                            $new_subPayment->record_id = $request->record_id;
                                
                            $new_subPayment->created_by = Auth::user()->id;
                            $new_subPayment->updated_by =  Auth::user()->id;
                            $new_subPayment->church_id = Auth::user()->church_id;
                            $new_subPayment->balance_amount = ( $payable_amount - ($sub_payment + $paid_amount) ); 
                            $new_subPayment->save();
                            $fees_payment->installment_payment_id = $new_subPayment->id;
            
                            if(($sub_payment + $paid_amount) == $payable_amount){
                                $installment->active_status = 1;
                            }else{
                                $installment->active_status = 2;
                            }
                            $installment->paid_amount = $sub_payment + $paid_amount;
                            $installment->save();
                        }
                        else{
                        $fees_payment->fees_type_id = $request->fees_type_id;
                        $fees_payment->church_year_id = getAcademicId();
                        }
            
                        $result = $fees_payment->save();
                        $payment_mode_name=ucwords($request->payment_mode);
                        $payment_method=SmPaymentMethhod::where('method',$payment_mode_name)->first();
                        $income_head= generalSetting();
            
                        $add_income = new SmAddIncome();
                        $add_income->name = 'Fees Collect';
                        $add_income->date = date('Y-m-d', strtotime($request->date));
                        $add_income->amount = $fees_payment->amount;
                        $add_income->fees_collection_id = $fees_payment->id;
                        $add_income->active_status = 1;
                        $add_income->income_head_id = $income_head->income_head_id;
                        $add_income->payment_method_id = $payment_method->id;
                        $add_income->account_id = $request->bank_id;
                        $add_income->created_by = Auth()->user()->id;
                        $add_income->church_id = Auth::user()->church_id;
                        if(moduleStatusCheck('University')){
                            $add_income->un_church_year_id = getAcademicId();
                        }
                        $add_income->save();
            
                        if($payment_method->id==3){
                                $bank=SmBankAccount::where('id',$request->bank_id)
                                ->where('church_id',Auth::user()->church_id)
                                ->first();
                                $after_balance= $bank->current_balance + $paid_amount;
                                
                                $bank_statement= new SmBankStatement();
                                $bank_statement->amount = $paid_amount;
                                $bank_statement->after_balance= $after_balance;
                                $bank_statement->type= 1;
                                $bank_statement->details= "Fees Payment";
                                $bank_statement->payment_date= date('Y-m-d', strtotime($request->date));
                                $bank_statement->bank_id= $request->bank_id;
                                $bank_statement->church_id= Auth::user()->church_id;
                                $bank_statement->payment_method= $payment_method->id;
                                $bank_statement->fees_payment_id= $fees_payment->id;
                                $bank_statement->save();
                
                                $current_balance= SmBankAccount::find($request->bank_id);
                                $current_balance->current_balance=$after_balance;
                                $current_balance->update();
                        }
                        $after_paid -= ( $paid_amount);
                }
            }
            Toastr::success('Operation Successfull', 'Success');
            return redirect()->back();
        }
        catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


}

