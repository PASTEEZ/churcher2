<?php 

namespace App\PaymentGateway;

use App\User;
use App\SmParent;
use App\SmSchool;
use App\SmStudent;
use App\SmAddIncome;
use App\SmFeesPayment;
use App\SmPaymentMethhod;
use App\SmPaymentGatewaySetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Unicodeveloper\Paystack\Paystack;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use App\Models\DirectFeesInstallmentAssign;
use Modules\Lms\Entities\CoursePurchaseLog;
use Modules\Fees\Entities\FmFeesTransaction;
use Illuminate\Validation\ValidationException;
use Modules\Wallet\Entities\WalletTransaction;
use App\Models\DireFeesInstallmentChildPayment;
use Modules\Saas\Entities\SmSubscriptionPayment;
use Modules\Fees\Http\Controllers\FeesExtendedController;

class PaystackPayment{

    public $paystack;

    public function __construct()
    {
        $this->paystack = new Paystack();
    }

    public function handle($data)
    {      
      
        try {
            $payStackData = [];
            $email = "";
            $amount = $data['amount'];
            $student = SmStudent::find($data['member_id']);
                if(!($student->email)){
                    $parent = SmParent::find($student->parent_id);
                    $email =  $parent->guardians_email;
                }else{
                    $email =   $student->email;
                }
          

            $paystack_info = SmPaymentGatewaySetting::where('gateway_name', 'Paystack')
                            ->where('church_id', Auth::user()->church_id)
                            ->first();

            if(!$paystack_info || !$paystack_info->gateway_secret_key){
                Toastr::warning('Paystack Credentials Can Not Be Blank', 'Warning');
                return redirect()->send()->back();
            }

            Config::set('paystack.publicKey', $paystack_info->gateway_publisher_key);
            Config::set('paystack.secretKey', $paystack_info->gateway_secret_key);
            Config::set('paystack.merchantEmail', $paystack_info->gateway_username);
            
            if($data['type'] == "Wallet") {              
                $amount = $data['amount'];
                if(array_key_exists('service_charge', $data)) {
                    $amount = $data['amount'] + $data['service_charge'];
                }
                Session::put('payment_type', "Wallet");
                Session::put('amount',  $amount);               
                Session::put('service_charge',  gv($data, 'service_charge', 0));
                Session::put('payment_mode', "Paystack");
                Session::put('wallet_type', $data['wallet_type']);
                
                // (generalSetting()->currency != ""  ? generalSetting()->currency : "ZAR")

            }elseif($data['type'] == "Fees"){
                Session::forget('amount');
                Session::put('payment_type', $data['type']);
                Session::put('invoice_id', $data['invoice_id']);
                Session::put('amount', $data['amount']);
                Session::put('payment_method',  $data['payment_method']);
                Session::put('transcation_id',  $data['transcationId']);
               
                
            }elseif($data['type'] == "Lms"){
                Session::put('member_id', $data['member_id']);
                Session::put('payment_type', "Lms");
                Session::put('amount',  $data['amount']);
                Session::put('payment_method', "Paystack");
                Session::put('purchase_log_id', $data['purchase_log_id']);
            }

            elseif($data['type'] == "direct_fees"){
                Session::put('payment_type', $data['type']);
                Session::put('sub_payment_id', $data['sub_payment_id']);
                Session::put('installment_id', $data['installment_id']);
            }

            elseif($data['type'] == "direct_fees_total"){
                Session::put('payment_type', $data['type']);
                Session::put('record_id', $data['record_id']);
                Session::put('member_id', $data['member_id']);
                Session::put('request_amount', $data['request_amount']);
            }

            $payStackData= [
                "amount" => (int)round($amount*100),
                "email" => $email,
                "callback_url" => '/payment_gateway_success_callback/Paystack',
                "currency" => (generalSetting()->currency != ""  ? generalSetting()->currency : "ZAR")
            ];
           
            $this->paystack = new Paystack();
            $url = $this->paystack->getAuthorizationResponse($payStackData)['data']['authorization_url'];

            if(request()->wantsJson()){
                return $url;
            }else{
                return redirect()->to($url)->send();
            }
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            if(request()->wantsJson()){
                throw ValidationException::withMessages(['amount' => $e->getMessage()]);
            }else{
                Toastr::error($e->getMessage(), 'Failed');
                return redirect()->back()->send();
            }

        }
    }

    public function successCallBack()
    {
        DB::beginTransaction();
        try {
            $user = auth()->User();
            $walletType = Session::get('wallet_type');
            $amount = Session::get('amount');
            
            if(Session::get('payment_type') == "Wallet") {
                $addPayment = new WalletTransaction();
                $addPayment->amount= session('amount') - session('service_charge', 0);
                $addPayment->payment_method= "Paystack";
                $addPayment->user_id= $user->id;
                $addPayment->type= $walletType;
                $addPayment->church_id= Auth::user()->church_id;
                $addPayment->church_year_id= getAcademicId();
                $addPayment->status = 'approve';
                $result = $addPayment->save();

                if($result){
                    $user = User::find($user->id);
                    $currentBalance = $user->wallet_balance;
                    $user->wallet_balance = $currentBalance + (session('amount') - session('service_charge', 0));
                    $user->update();
                    $gs = generalSetting();
                    $compact['full_name'] =  $user->full_name;
                    $compact['method'] =  $addPayment->payment_method;
                    $compact['create_date'] =  date('Y-m-d');
                    $compact['church_name'] =  $gs->church_name;
                    $compact['current_balance'] =  $user->wallet_balance;
                    $compact['add_balance'] =  session()->get('amount');

                    @send_mail($user->email, $user->full_name, "wallet_approve", $compact);
                }

                DB::commit();

                Session::forget('payment_type');
                Session::forget('amount');
                Session::forget('payment_mode');
                Session::forget('wallet_type');

                return redirect()->route('wallet.my-wallet');
            }elseif(Session::get('payment_type') == "Fees"){
                $transcation= FmFeesTransaction::find(Session::get('transcation_id'));
               
                $extendedController = new FeesExtendedController();
                $extendedController->addFeesAmount(Session::get('transcation_id'), null);
                
                DB::commit();

                Session::forget('amount');
                Session::forget('payment_type');
                Session::forget('invoice_id');
                Session::forget('amount');
                Session::forget('payment_method');
                Session::forget('transcation_id');
             
                Toastr::success('Operation successful', 'Success');
                return redirect()->to(url('fees/student-fees-list',$transcation->member_id));
                
            }elseif(Session::get('payment_type') == "Lms"){
                if(Session::get('purchase_log_id')) {
                    $coursePurchase = CoursePurchaseLog::find(Session::get('purchase_log_id'));
                    $coursePurchase->active_status = 'approve';
                    $coursePurchase->save();

                    lmsProfit($coursePurchase->instructor_id, $coursePurchase->amount);

                    addIncome(Session::get('payment_method'), 'Lms Fees Collect', Session::get('amount'), Session::get('purchase_log_id'), Auth()->user()->id);
                    DB::commit();

                    Session::forget('payment_type');
                    Session::forget('amount');
                    Session::forget('payment_mode');
                    Session::forget('purchase_log_id');

                    Toastr::success('Operation successful', 'Success');
                    return redirect()->to(url('lms/student/purchase-log',$coursePurchase->member_id));
                    Session::forget('member_id');
                }
            } else if(Session::get('payment_type') == "Saas"){
                $paymentId = Session::get('payment_id');
                $payment = SmSubscriptionPayment::find($paymentId);
                $payment->payment_type = 'paid';
                $payment->approve_status = 'approved';
                $payment->payment_date = date('Y-m-d');
                $payment->save();

                $school = SmSchool::find($payment->church_id);
              
                DB::commit();
                Toastr::success('Operation successful', 'Success');
                return redirect('//'.$school->domain.'.'.config('app.short_url').'/home');
            }
            elseif(Session::get('payment_type')== "direct_fees" && Session::get('sub_payment_id')){
                $sub_payment_id = Session::get('sub_payment_id');
                $installment_id = Session::get('installment_id');
                $sub_payment = DireFeesInstallmentChildPayment::find($sub_payment_id);
                $installment = DirectFeesInstallmentAssign::find($installment_id);
               
      
                $payable_amount =  discountFees($installment->id);
                $all_sub_payment = $installment->payments->sum('paid_amount');
                $direct_payment =  $installment->paid_amount;
                $total_paid =  $all_sub_payment + $direct_payment;
                $sub_payment->active_status = 1;
                $sub_payment->balance_amount = ( $payable_amount - ($all_sub_payment + $sub_payment->amount) ); 
                $result = $sub_payment->save();
                if($result && $installment){
                    $fees_payment = new SmFeesPayment();
                    $fees_payment->member_id = $installment->member_id;
                    $fees_payment->amount = $sub_payment->amount;
                    $fees_payment->payment_date = date('Y-m-d', strtotime($sub_payment->payment_date));
                    $fees_payment->payment_mode = $sub_payment->payment_mode;
                    $fees_payment->created_by = Auth::user()->id;
                    $fees_payment->church_id = Auth::user()->church_id;
                    $fees_payment->record_id = $sub_payment->record_id;
                    $fees_payment->church_year_id = getAcademicid();
                    $fees_payment->installment_payment_id = $sub_payment->id;
                    if(($all_sub_payment + $sub_payment->amount) == $payable_amount){
                        $installment->active_status = 1;
                    }else{
                        $installment->active_status = 2;
                    }
                    $installment->paid_amount = $all_sub_payment + $sub_payment->amount;
                    $installment->save();
                    $fees_payment->save();
                   
                    DB::commit();
                    Session::forget('payment_type');
                    Session::forget('sub_payment_id');
                    Session::forget('installment_id');

                    Toastr::success('Operation successful', 'Success');
                    if(Auth::user()->role_id == 2){
                        return redirect()->to(url('student-fees'));
                    }else{
                        return redirect()->to(url('parent-fees'.'/'.$installment->member_id));
                    }
                }

            }
            elseif(Session::get('payment_type') ==  "direct_fees_total")
            {
                $request_amount = Session::get('request_amount');
                $record_id = Session::get('record_id');
                $member_id = Session::get('member_id');
                $after_paid = $request_amount;
                $installments = DirectFeesInstallmentAssign::where('record_id', $record_id)->get();
                $total_paid = $installments->sum('paid_amount');
                $total_amount = $installments->sum('amount');
                $total_discount = $installments->sum('discount_amount');
                $balace_amount = $total_amount - ($total_discount +  $total_paid);
                if($balace_amount <  $request_amount){
                    Toastr::error('Amount is greater than due', 'Failed');
                    if(Auth::user()->role_id == 2){
                        return redirect()->to(url('student-fees'));
                    }else{
                        return redirect()->to(url('parent-fees'.'/'.$member_id));
                    }
                }
                
                $newformat = date('Y-m-d');
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

                       $fees_payment = new SmFeesPayment();
                       $fees_payment->member_id = $installment->member_id;
                       $fees_payment->fees_discount_id = !empty($request->fees_discount_id) ? $request->fees_discount_id : "";
                       $fees_payment->discount_amount = !empty($request->applied_amount) ? $request->applied_amount : 0;
                       $fees_payment->amount = $paid_amount;
                       $fees_payment->payment_date = date('Y-m-d');
                       $fees_payment->payment_mode =  "Paystack";;
                       $fees_payment->created_by = Auth::id();
                       $fees_payment->church_id = Auth::user()->church_id;
                       $fees_payment->record_id = $installment->record_id;
                       $fees_payment->church_year_id = getAcademicid();
                       $fees_payment->direct_fees_installment_assign_id = $installment->id;
                   
                        $payment_mode_name= "Paystack";
                        $payment_method= SmPaymentMethhod::where('method',$payment_mode_name)->first();
                        $installment = DirectFeesInstallmentAssign::find($installment->id);
                        $installment->payment_date =  $newformat;
                        $installment->payment_mode =  "Paystack";
                        
    
                        $payable_amount =  discountFees($installment->id);
                        $sub_payment = $installment->payments->sum('paid_amount');
                        $last_inovoice = DireFeesInstallmentChildPayment::where('church_id',auth()->user()->church_id)->max('invoice_no');
    
                        $new_subPayment = new DireFeesInstallmentChildPayment();
                        $new_subPayment->direct_fees_installment_assign_id = $installment->id;
                        $new_subPayment->invoice_no = ( $last_inovoice +1 ) ?? 1;
                        $new_subPayment->amount = $paid_amount;
                        $new_subPayment->paid_amount = $paid_amount;
                        $new_subPayment->payment_date = $newformat;
                        $new_subPayment->payment_mode =   "Paystack";
                        $new_subPayment->active_status = 1;
                        $new_subPayment->discount_amount = 0;
                        $new_subPayment->fees_type_id =  $installment->fees_type_id;
                        $new_subPayment->member_id = $installment->member_id;
                        $new_subPayment->record_id = $installment->record_id;
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

                       $income_head= generalSetting();
           
                       $add_income = new SmAddIncome();
                       $add_income->name = 'Fees Collect';
                       $add_income->date = date('Y-m-d');
                       $add_income->amount = $fees_payment->amount;
                       $add_income->fees_collection_id = $fees_payment->id;
                       $add_income->active_status = 1;
                       $add_income->income_head_id = $income_head->income_head_id;
                       $add_income->payment_method_id = $payment_method->id;
                       $add_income->created_by = Auth()->user()->id;
                       $add_income->church_id = Auth::user()->church_id;
                       $add_income->church_year_id = getAcademicId();
                       $add_income->save();
                       $after_paid -= ( $paid_amount);
                    }
                }
                DB::commit();
                if(Auth::user()->role_id == 2){
                    return redirect()->to(url('student-fees'));
                }else{
                    return redirect()->to(url('parent-fees'.'/'.$installment->member_id));
                }
            } 


        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back()->send();
        }
    }
}