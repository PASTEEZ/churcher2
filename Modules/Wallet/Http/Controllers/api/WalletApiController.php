<?php

namespace Modules\Wallet\Http\Controllers\api;

use App\User;
use App\SmBankAccount;
use App\SmNotification;
use App\SmPaymentMethhod;
use App\SmGeneralSettings;
use Illuminate\Http\Request;
use App\SmPaymentGatewaySetting;
use Illuminate\Routing\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Wallet\Entities\WalletTransaction;

class WalletApiController extends Controller
{

    public function myWallet()
    {
        try {

            $myBalance = Auth::user()->wallet_balance != null ? number_format(Auth::user()->wallet_balance, 2, '.', ''): 0.00;
            $currencySymbol = generalSetting()->currency_symbol;
            $paymentMethods = SmPaymentMethhod::whereNotIn('method', ["Cash", "Wallet"])
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $bankAccounts = SmBankAccount::where('active_status', 1)
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $walletTransactions = WalletTransaction::where('user_id', Auth::user()->id)
                ->where('church_id', Auth::user()->church_id)
                ->get();

            $stripe_info = SmPaymentGatewaySetting::where('gateway_name', 'stripe')
                ->where('church_id', Auth::user()->church_id)
                ->first();
            $razorpay_info = null;
            if (moduleStatusCheck('RazorPay')) {
                $razorpay_info = SmPaymentGatewaySetting::where('gateway_name', 'RazorPay')
                    ->where('church_id', Auth::user()->church_id)
                    ->first();
            }

            return response()->json(compact('currencySymbol','myBalance','paymentMethods', 'bankAccounts', 'walletTransactions', 'stripe_info', 'razorpay_info'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function addWalletAmount(Request $request)
    {
        $request->validate([
            'amount' => 'required',
            'payment_method' => 'required',
            'bank' => 'required_if:payment_method,Bank',
            'file' => 'mimes:jpg,jpeg,png,pdf',
        ]);

        try {
            if ($request->payment_method == "Cheque" || $request->payment_method == "Bank") {
                $uploadFile = "";
                if ($request->file('file') != "") {
                    $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
                    $file = $request->file('file');
                    $fileSize = filesize($file);
                    $fileSizeKb = ($fileSize / 1000000);
                    if ($fileSizeKb >= $maxFileSize) {
                        return response()->json(['error'=>'Max upload file size ' . $maxFileSize . ' Mb is set in system',]);
                    }
                    $file = $request->file('file');
                    $uploadFile = 'doc1-' . md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                    $file->move('public/uploads/student/document/', $uploadFile);
                    $uploadFile = 'public/uploads/student/document/' . $uploadFile;
                }

                $addPayment = new WalletTransaction();
                $addPayment->amount = $request->amount;
                $addPayment->payment_method = $request->payment_method;
                $addPayment->bank_id = $request->bank;
                $addPayment->note = $request->note;
                $addPayment->file = $uploadFile;
                $addPayment->type = 'diposit';
                $addPayment->user_id = Auth::user()->id;
                $addPayment->church_id = Auth::user()->church_id;
                $addPayment->church_year_id = getAcademicId();
                $addPayment->save();
                
                
                // Notification Start
                $this->sendNotification(1, 1, "Wallet Request");
    
                $accounts_ids = User::where('role_id', 6)->get();
                foreach ($accounts_ids as $accounts_id) {
                    $this->sendNotification($accounts_id->id, $accounts_id->role_id, "Wallet Request");
                }
                // Notification End
            } else {
                $addPayment = new WalletTransaction();
                $addPayment->amount= $request->amount;
                $addPayment->payment_method= $request->payment_method;
                $addPayment->user_id= Auth::user()->id;
                $addPayment->type= 'diposit';
                $addPayment->church_id= Auth::user()->church_id;
                $addPayment->church_year_id= getAcademicId();
                $addPayment->save();
            }
            return response()->json([
                'sucess'=> "Wallet request submitted",
                "id"=> $addPayment->id,
                "amount"=>$request->amount,
                "transactionId"=> "wallet_request_id_".$addPayment->id,
                "description"=>"Wallet Request",
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error'=> "Error adding wallet"]);
        }
    }
    
    public function confirmWalletPayment(Request $request){
        
        $walletTransaction = WalletTransaction::find($request->id);
        
        if($walletTransaction){
            $walletTransaction->amount = $request->amount;
            $walletTransaction->status = "approve";
            $walletTransaction->updated_at = date('Y-m-d');
            $walletTransaction->update();
        
        
            $user = User::find($walletTransaction->user_id);
            
            $currentBalance = $user->wallet_balance;
            $user->wallet_balance = $currentBalance + $request->amount;
            $user->update();
            $gs = generalSetting();
            $compact['full_name'] =  $user->full_name;
            $compact['method'] =  $walletTransaction->payment_method;
            $compact['create_date'] =  date('Y-m-d');
            $compact['church_name'] =  $gs->church_name;
            $compact['current_balance'] =  $user->wallet_balance;
            $compact['add_balance'] =  $request->amount;

            @send_mail($user->email, $user->full_name, "wallet_approve", $compact);

            return response()->json([
                'sucess'=> "Wallet added",
            ]);
        }
        
    }

    public function walletRefundRequestStore(Request $request)
    {

        $request->validate([
            'refund_note' => 'required',
            'refund_file' => 'mimes:jpg,jpeg,png,pdf',
        ]);

        $existRefund = WalletTransaction::where('type', 'refund')
            ->where('user_id', $request->user_id)
            ->where('status', 'pending')
            ->where('church_id', Auth::user()->church_id)
            ->first();

        if ($existRefund) {
            return response()->json([
                'error'=>'You Already Request For Refund',
            ]);
        }

        try {
            $uploadFile = "";
            if ($request->file('refund_file') != "") {
                $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
                $file = $request->file('refund_file');
                $fileSize = filesize($file);
                $fileSizeKb = ($fileSize / 1000000);
                if ($fileSizeKb >= $maxFileSize) {
                    Toastr::error('Max upload file size ' . $maxFileSize . ' Mb is set in system', 'Failed');
                    return redirect()->back();
                }
                $file = $request->file('refund_file');
                $uploadFile = 'doc1-' . md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                $file->move('public/uploads/student/document/', $uploadFile);
                $uploadFile = 'public/uploads/student/document/' . $uploadFile;
            }

            $WalletRefund = new WalletTransaction();
            $WalletRefund->user_id = $request->user_id;
            $WalletRefund->amount = $request->refund_amount;
            $WalletRefund->type = 'refund';
            $WalletRefund->payment_method = 'Wallet';
            $WalletRefund->note = $request->refund_note;
            $WalletRefund->file = $uploadFile;
            $WalletRefund->church_id = Auth::user()->church_id;
            $WalletRefund->save();

            return response()->json(['success' => 'Refund Request Submitted']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'error submitting refund request']);
        }
    }


    // Private Function

    private function walletAmounts($type, $status)
    {
        $walletAmounts = WalletTransaction::where('type', $type)
            ->where('status', $status)
            ->where('church_id', Auth::user()->church_id)
            ->get();
        return $walletAmounts;
    }

    private function sendNotification($user_id, $role_id, $message)
    {
        $notification = new SmNotification;
        $notification->user_id = $user_id;
        $notification->role_id = $role_id;
        $notification->date = date('Y-m-d');
        $notification->message = $message;
        $notification->church_id = Auth::user()->church_id;
        $notification->church_year_id = getAcademicId();
        $notification->save();
    }
}
