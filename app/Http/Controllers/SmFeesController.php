<?php


namespace App\Http\Controllers;

use App\User;
use App\SmClass;
use App\SmParent;
use App\SmSection;
use App\SmStudent;
use App\YearCheck;
use App\SmAddIncome;
use App\SmsTemplate;
use App\SmAddExpense;
use App\SmFeesAssign;
use App\SmFeesMaster;
use App\SmSmsGateway;
use App\ApiBaseMethod;
use App\SmBankAccount;
use App\SmFeesPayment;
use App\SmNotification;
use Twilio\Rest\Client;
use App\SmBankStatement;
use App\SmChartOfAccount;
use App\SmPaymentMethhod;
use App\Mail\DuesFeesMail;
use App\SmBankPaymentSlip;
use App\SmGeneralSettings;
use App\SmFeesCarryForward;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\SmFeesAssignDiscount;
use App\SmPaymentGatewaySetting;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Brian2694\Toastr\Facades\Toastr;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Notifications\FeesApprovedNotification;

class SmFeesController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
        // User::checkAuth();
    }

    public function feesForward(Request $request)
    {
        try {
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.feesCollection.fees_forward', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function feesForwardSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required'
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();
            $students = SmStudent::where('age_group_id', $request->class)->where('mgender_id', $request->section)->where('church_id',Auth::user()->church_id)->get();
            if ($students->count() != 0) {
                foreach ($students as $student) {
                    $fees_balance = SmFeesCarryForward::where('member_id', $student->id)->count();
                }

                $age_group_id = $request->class;

                if ($fees_balance == 0) {

                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        $data = [];
                        $data['classes'] = $classes->toArray();
                        $data['students'] = $students->toArray();
                        $data['age_group_id'] = $age_group_id;
                        return ApiBaseMethod::sendResponse($data, null);
                    }
                    return view('backEnd.feesCollection.fees_forward', compact('classes', 'students', 'age_group_id'));
                } else {
                    $update = "";

                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        $data = [];
                        $data['classes'] = $classes->toArray();
                        $data['students'] = $students->toArray();
                        $data['age_group_id'] = $age_group_id;
                        $data['update'] = $update;
                        return ApiBaseMethod::sendResponse($data, null);
                    }
                    return view('backEnd.feesCollection.fees_forward', compact('classes', 'students', 'update', 'age_group_id'));
                }
            } else {

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('No result Found');
                }
                Toastr::error('Operation Failed', 'Failed');
                return redirect('fees-forward');
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function feesForwardStore(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach ($request->id as $student) {

                if ($request->update == 1) {

                    $fees_forward = SmFeesCarryForward::find($student);
                    $fees_forward->balance = $request->balance[$student];
                    $fees_forward->notes = $request->notes[$student];
                    $fees_forward->save();
                } else {
                    $fees_forward = new SmFeesCarryForward();
                    $fees_forward->member_id = $student;
                    $fees_forward->balance = $request->balance[$student];
                    $fees_forward->notes = $request->notes[$student];
                    $fees_forward->church_id = Auth::user()->church_id;
                    $fees_forward->church_year_id = getAcademicId();
                    $fees_forward->save();
                }
            }
            DB::commit();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse(null, 'Fees has been forwarded successfully');
            }
            Toastr::success('Operation successful', 'Success');
            return redirect('fees-forward');
        } catch (\Exception $e) {
            DB::rollback();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Something went wrong, please try again.');
            }
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function collectFees(Request $request)
    {
        try {
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.feesCollection.collect_fees', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function collectFeesSearch(Request $request)
    {
        $input = $request->all();
        // $validator = Validator::make($input, [
        //     'class' => 'required'
        // ]);
        // if ($validator->fails()) {
        //     if (ApiBaseMethod::checkUrl($request->fullUrl())) {
        //         return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
        //     }
        //     return redirect()->back()
        //         ->withErrors($validator)
        //         ->withInput();
        // }
        try {
            $students = SmStudent::query();

            if ($request->class != "") {
                $students->where('age_group_id', $request->class);
            }

            if ($request->section != "") {
                $students->where('mgender_id', $request->section);
            }
            if ($request->keyword != "") {
                $students->where('full_name', 'like', '%' . $request->keyword . '%')->orWhere('registration_no', $request->keyword)->orWhere('roll_no', $request->keyword)->orWhere('national_id_no', $request->keyword)->orWhere('local_id_no', $request->keyword);
            }
            $students = $students->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->where('active_status',1)->get();

            if ($students->isEmpty()) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('No result found');
                }
                Toastr::error('No result found', 'Failed');
                return redirect('collect-fees');
            }
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['students'] = $students->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            $class_info = SmClass::find($request->class);
            $search_info['age_group_name'] = @$class_info->age_group_name;
            if ($request->section != "") {
                $section_info = SmSection::find($request->section);
                $search_info['mgender_name'] = @$section_info->mgender_name;
            }
            if ($request->keyword != "") {
                $search_info['keyword'] = $request->keyword;
            }
            return view('backEnd.feesCollection.collect_fees', compact('classes', 'students', 'search_info'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }






    public function collectFeesStudent(Request $request, $id)
    {
        try {
            // $student = SmStudent::find($id);
            if (checkAdmin()) {
                $student = SmStudent::find($id);
            }else{
                $student = SmStudent::where('id',$id)->where('church_id',Auth::user()->church_id)->first();
            }
            $fees_assigneds = SmFeesAssign::where('member_id', $id)
                            ->orderBy('id', 'desc')
                            ->where('church_id',Auth::user()->church_id)
                            ->get();
            if (count($fees_assigneds) <= 0) {
                Toastr::warning('Fees assign not yet!');
                return redirect('/collect-fees');
            }
            $fees_assigneds2 = DB::table('sm_fees_assigns')
                ->join('sm_fees_masters', 'sm_fees_masters.id', '=', 'sm_fees_assigns.fees_master_id')
                ->join('sm_fees_types', 'sm_fees_types.id', '=', 'sm_fees_masters.fees_type_id')
                ->select('sm_fees_types.id as fees_type_id','sm_fees_assigns.fees_amount','sm_fees_assigns.applied_discount', 'sm_fees_assigns.id', 'sm_fees_assigns.member_id', 'sm_fees_types.name', 'sm_fees_masters.date as due_date', 'sm_fees_masters.amount', 'sm_fees_masters.fees_group_id', 'sm_fees_masters.id as fees_master_id', 'sm_fees_masters.fees_type_id')
                ->where('sm_fees_assigns.member_id', $id)
                ->where('sm_fees_assigns.church_id',Auth::user()->church_id)->get();
            // return $fees_assigneds2;
            $i = 0;
            foreach ($fees_assigneds2 as $row) {
                $d[$i]['fees_type_id'] = $row->fees_type_id;
                $d[$i]['fees_name'] = $row->name;
                $d[$i]['due_date'] = $row->due_date;
                $d[$i]['amount'] = $row->fees_amount;
                $d[$i]['applied_discount'] = $row->applied_discount;
                // $d[$i]['amount'] = $row->amount;
                $d[$i]['paid'] = DB::table('sm_fees_payments')->where('fees_type_id', $row->fees_type_id)->where('member_id', $row->member_id)->sum('amount');
                $d[$i]['fine'] = DB::table('sm_fees_payments')->where('fees_type_id', $row->fees_type_id)->where('member_id', $row->member_id)->sum('fine');
                $d[$i]['discount_amount'] = DB::table('sm_fees_payments')->where('fees_type_id', $row->fees_type_id)->where('member_id', $row->member_id)->sum('discount_amount');
                $d[$i]['balance'] = ((float) $d[$i]['amount'] + (float) $d[$i]['fine'])  - ((float) $d[$i]['paid'] + (float) $d[$i]['discount_amount']);
                $i++;
            }
            $fees_discounts = SmFeesAssignDiscount::where('member_id', $id)
                            ->where('church_id',Auth::user()->church_id)
                            ->get();

            $applied_discount = [];
            foreach ($fees_discounts as $fees_discount) {
                $fees_payment = SmFeesPayment::select('fees_discount_id')->where('active_status',1)->where('fees_discount_id', $fees_discount->id)->where('church_id',Auth::user()->church_id)->first();
                if (isset($fees_payment->fees_discount_id)) {
                    $applied_discount[] = $fees_payment->fees_discount_id;
                }
            }



            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['fees'] = $d;
                return ApiBaseMethod::sendResponse($data, null);
            }
            $fees_assigneds = SmFeesAssign::where('member_id', $id)->orderBy('id', 'desc')->where('church_id', Auth::user()->church_id)->get();
            return view('backEnd.feesCollection.collect_fees_student_wise', compact('student', 'fees_assigneds', 'fees_discounts', 'applied_discount'));
        } catch (\Exception $e) {
            return $e->getMessage();
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function collectFeesStudentApi(Request $request, $id)
    {
        try {
            $student = SmStudent::where('user_id', $id)->where('church_id',Auth::user()->church_id)->first();
            $fees_assigneds = SmFeesAssign::where('member_id', $id)->orderBy('id', 'desc')->where('church_id',Auth::user()->church_id)->get();

            $fees_assigneds2 = DB::table('sm_fees_assigns')
                ->select('sm_fees_types.id as fees_type_id', 'sm_fees_types.name', 'sm_fees_masters.date as due_date', 'sm_fees_masters.amount as amount')
                ->join('sm_fees_masters', 'sm_fees_masters.id', '=', 'sm_fees_assigns.fees_master_id')
                ->join('sm_fees_types', 'sm_fees_types.id', '=', 'sm_fees_masters.fees_type_id')
                // ->join('sm_fees_payments', 'sm_fees_payments.fees_type_id', '=', 'sm_fees_masters.fees_type_id')
                ->where('sm_fees_assigns.member_id', $student->id)
                ->where('sm_fees_assigns.church_id',Auth::user()->church_id)->get();

            // return $fees_assigneds2;
            $i = 0;
            $d = [];
            foreach ($fees_assigneds2 as $row) {
                $d[$i]['fees_type_id'] = $row->fees_type_id;
                $d[$i]['fees_name'] = $row->name;
                $d[$i]['due_date'] = $row->due_date;
                $d[$i]['amount'] = $row->amount;
                $d[$i]['paid'] = DB::table('sm_fees_payments')->where('fees_type_id', $row->fees_type_id)->where('member_id', $student->id)->sum('amount');
                $d[$i]['fine'] = DB::table('sm_fees_payments')->where('fees_type_id', $row->fees_type_id)->where('member_id', $student->id)->sum('fine');
                $d[$i]['discount_amount'] = DB::table('sm_fees_payments')->where('fees_type_id', $row->fees_type_id)->where('member_id', $student->id)->sum('discount_amount');
                $d[$i]['balance'] = ((float) $d[$i]['amount'] + (float) $d[$i]['fine'])  - ((float) $d[$i]['paid'] + (float) $d[$i]['discount_amount']);
                $i++;
            }

            //, DB::raw("SUM(sm_fees_payments.amount) as total_paid where sm_fees_payments.fees_type_id==")
            $fees_discounts = SmFeesAssignDiscount::where('member_id', $id)->where('church_id',Auth::user()->church_id)->get();

            $applied_discount = [];
            foreach ($fees_discounts as $fees_discount) {
                $fees_payment = SmFeesPayment::select('fees_discount_id')->where('active_status',1)->where('fees_discount_id', $fees_discount->id)->where('church_id',Auth::user()->church_id)->first();
                if (isset($fees_payment->fees_discount_id)) {
                    $applied_discount[] = $fees_payment->fees_discount_id;
                }
            }

            $currency_symbol = SmGeneralSettings::select('currency_symbol')->where('church_id',Auth::user()->church_id)->first();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                // $data['student'] = $student;
                $data['fees'] = $d;
                $data['currency_symbol'] = $currency_symbol;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.feesCollection.collect_fees_student_wise', compact('student', 'fees_assigneds', 'fees_discounts', 'applied_discount'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function feesGenerateModal(Request $request, $amount, $member_id, $type,$master,$assign_id)
    {
        try {
            $amount = $amount;
            $master = $master;
            $fees_type_id = $type;
            $member_id = $member_id;

            $banks = SmBankAccount::where('church_id', Auth::user()->church_id)
                ->get();

            $discounts = SmFeesAssignDiscount::where('member_id', $member_id)
                ->where('fees_type_id', $fees_type_id)
                ->where('church_id',Auth::user()->church_id)
                ->first();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['amount'] = $amount;
                $data['discounts'] = $discounts;
                $data['fees_type_id'] = $fees_type_id;
                $data['member_id'] = $member_id;
                return ApiBaseMethod::sendResponse($data, null);
            }

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

            return view('backEnd.feesCollection.fees_generate_modal', compact('amount','assign_id','master', 'discounts', 'fees_type_id', 'member_id', 'data', 'method','banks'));
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


            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['amount'] = $amount;
                $data['discounts'] = $discounts;
                $data['fees_type_id'] = $fees_type_id;
                $data['member_id'] = $member_id;
                $data['applied_discount'] = $applied_discount;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.feesCollection.fees_generate_modal_child', compact('amount', 'discounts', 'fees_type_id', 'member_id', 'applied_discount'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function feesPaymentStore(Request $request)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        try {
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
            $fees_payment->fees_type_id = $request->fees_type_id;
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
            $fees_payment->slip = $fileName;
            $fees_payment->church_year_id = getAcademicid();
            $result = $fees_payment->save();



            $payment_mode_name=ucwords($request->payment_mode);
            $payment_method=SmPaymentMethhod::where('method',$payment_mode_name)->first();
            $income_head=generalSetting();

            $add_income = new SmAddIncome();
            $add_income->name = 'Fees Collect';
            $add_income->date = date('Y-m-d', strtotime($request->date));
            $add_income->amount = !empty($request->amount) ? $request->amount : 0;
            $add_income->fees_collection_id = $fees_payment->id;
            $add_income->active_status = 1;
            $add_income->income_head_id = $income_head->income_head_id;
            $add_income->payment_method_id = $payment_method->id;
            if($payment_method->id==3){
                $add_income->account_id = $request->bank_id;
            }
            $add_income->created_by = Auth()->user()->id;
            $add_income->church_id = Auth::user()->church_id;
            $add_income->church_year_id = getAcademicId();
            $add_income->save();


            if($payment_method->id==3){
                $bank=SmBankAccount::where('id',$request->bank_id)
                    ->where('church_id',Auth::user()->church_id)
                    ->first();
                $after_balance= $bank->current_balance + $request->amount;

                $bank_statement= new SmBankStatement();
                $bank_statement->amount= $request->amount;
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




            // if ($request->discount_group) {
            //     $discount_assign=SmFeesAssignDiscount::where('fees_discount_id',$request->discount_group)->where('member_id',$request->member_id)->first();
            //     $discount_assign->applied_amount+=$request->discount_amount;
            //     $discount_assign->unapplied_amount-=$request->discount_amount;
            //     $discount_assign->save();
            // }

            $fees_assign=SmFeesAssign::where('fees_master_id',$request->master_id)->where('member_id',$request->member_id)->where('church_id',Auth::user()->church_id)->first();
            $fees_assign->fees_amount-=floatval($request->amount);
            $fees_assign->save();
            if (!empty($request->fine)) {
                $fees_assign=SmFeesAssign::where('fees_master_id',$request->master_id)->where('member_id',$request->member_id)->where('church_id',Auth::user()->church_id)->first();
                $fees_assign->fees_amount+=$request->fine;
                $fees_assign->save();
            }


            if ($result) {
                Toastr::success('Operation successful', 'Success');
                return Redirect::route('fees_collect_student_wise', array('id' => $request->member_id));
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return Redirect::route('fees_collect_student_wise', array('id' => $request->member_id));
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
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees payment has been deleted  successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function searchFeesPayment(Request $request)
    {
        try {


            if(auth()->user()->role_id ==1 || auth()->user()->role_id ==5){
                $fees_payments = SmFeesPayment::with('studentInfo')->where('active_status',1)->orderby('id','DESC')->get();

            }else{
                $fees_payments = SmFeesPayment::with('studentInfo')->where('created_by',auth()->user()->id)->where('active_status',1)->orderby('id','DESC')->get();
            }


            $classes = SmClass::where('active_status', 1)->where('church_id',Auth::user()->church_id)->where('church_year_id', getAcademicId())->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($fees_payments, null);
            }
            return view('backEnd.feesCollection.search_fees_payment', compact('classes','fees_payments'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function feesPaymentSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required'
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $classes = SmClass::where('active_status', 1)->where('church_id',Auth::user()->church_id)->where('church_year_id', getAcademicId())->get();
            $old_fees_payments = DB::table('sm_fees_payments')
                ->join('sm_students', 'sm_fees_payments.member_id', '=', 'sm_students.id')
                ->join('sm_fees_masters', 'sm_fees_payments.fees_type_id', '=', 'sm_fees_masters.fees_type_id')
                ->join('sm_fees_groups', 'sm_fees_masters.fees_group_id', '=', 'sm_fees_groups.id')
                ->join('sm_fees_types', 'sm_fees_payments.fees_type_id', '=', 'sm_fees_types.id')
                ->join('sm_classes', 'sm_students.age_group_id', '=', 'sm_classes.id')
                ->join('sm_sections', 'sm_students.mgender_id', '=', 'sm_sections.id')
                ->where('sm_students.age_group_id', $request->class)
                ->where('sm_students.mgender_id', $request->section)
                ->orwhere('sm_students.full_name', '%' . @$request->keyword . '%')
                ->orwhere('sm_students.registration_no', '%' . @$request->keyword . '%')
                ->orwhere('sm_students.roll_no', '%' . @$request->keyword . '%')
                ->select('sm_fees_payments.*', 'sm_students.full_name', 'sm_classes.age_group_name', 'sm_fees_groups.name', 'sm_fees_types.name as fees_type_name')
                ->where('sm_fees_payments.church_id',Auth::user()->church_id)->get();

            $member_ids=[];

            foreach($old_fees_payments as $ids){
                $member_ids[]=$ids->member_id;
            }

            if(auth()->user()->role_id ==1 || auth()->user()->role_id ==5){
                $fees_payments = SmFeesPayment::with('studentInfo')->whereIn('member_id',$member_ids)->where('active_status',1)->orderby('id','DESC')->get();
            }else{
                $fees_payments = SmFeesPayment::with('studentInfo')->whereIn('member_id',$member_ids)->where('created_by',auth()->user()->id)->orderby('id','DESC')->where('active_status',1)->get();
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($fees_payments, null);
            }

            return view('backEnd.feesCollection.search_fees_payment', compact('fees_payments', 'classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function searchFeesDue(Request $request)
    {
        try {
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();
            $fees_masters = SmFeesMaster::select('fees_group_id')->where('active_status', 1)->distinct('fees_group_id')->where('church_id',Auth::user()->church_id)->where('church_year_id', getAcademicId())->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['fees_masters'] = $fees_masters->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            //
            $students = SmStudent::where('active_status', 1)->where('church_id',Auth::user()->church_id)->where('church_year_id', getAcademicId())->get();

            $fees_dues = [];
            foreach ($students as $student) {
                $fees_assigns = SmFeesAssign::where('member_id', $student->id)
                    ->where('church_id',Auth::user()->church_id)
                    ->whereHas('feesGroupMaster', function($q){
                        return $q->whereDate('date', '<', date('Y-m-d'));
                    })
                    ->where('fees_amount', '>', 0)->get();




                foreach($fees_assigns as $fees_assign){
                    $fees_dues[] = $fees_assign;
                }



            }
            return view('backEnd.feesCollection.search_fees_due', compact('classes', 'fees_masters','fees_dues'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function feesDueSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'fees_group' => 'required',
            'class' => 'required'
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $fees_group = explode('-', $request->fees_group);
            $fees_master = SmFeesMaster::select('id', 'amount')->where('fees_group_id', $fees_group[0])->where('fees_type_id', $fees_group[1])->where('church_id',Auth::user()->church_id)->first();
            $fees_master = SmFeesMaster::select('id', 'amount')->where('fees_group_id', $fees_group[0])->where('fees_type_id', $fees_group[1])->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->first();

            if($request->section == ""){
                $students = SmStudent::where('age_group_id', $request->class)->where('church_id',Auth::user()->church_id)->where('church_year_id', getAcademicId())->get();
            }else{
                $students = SmStudent::where('age_group_id', $request->class)->where('mgender_id', $request->section)->where('church_id',Auth::user()->church_id)->where('church_year_id', getAcademicId())->get();
            }



            $fees_dues = [];
            foreach ($students as $student) {


                $fees_assigns = SmFeesAssign::with('feesGroupMaster')->where('member_id', $student->id)
                    ->where('church_id',Auth::user()->church_id)
                    ->whereHas('feesGroupMaster', function($q) use($fees_group){
                        return $q
                            // ->whereDate('date', '<', date('Y-m-d'))
                            ->where('fees_group_id', $fees_group[0])->where('fees_type_id', $fees_group[1]);
                    })
                    ->where('fees_amount', '>', 0)->get();




                foreach($fees_assigns as $fees_assign){
                    $fees_dues[] = $fees_assign;
                }





            }

            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();
            $fees_masters = SmFeesMaster::select('fees_group_id')->where('active_status', 1)->distinct('fees_group_id')->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();

            $age_group_id = $request->class;
            $fees_group_id = $fees_group[1];

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['fees_masters'] = $fees_masters;
                $data['fees_dues'] = $fees_dues;
                $data['age_group_id'] = $age_group_id;
                $data['fees_group_id'] = $fees_group_id;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.search_fees_due', compact('classes', 'fees_masters', 'fees_dues', 'age_group_id', 'fees_group_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function sendDuesFeesEmail(Request $request){
        try{

            if(isset($request->send_email)){

                $systemEmail = SmsTemplate::first();
                foreach($request->student_list as $student){
                    $student_detail = SmStudent::where('id', $student)->first();
                    $fees_info['dues_fees'] = $request->dues_amount[$student];
                    $fees_info['fees_master'] = $request->fees_master;

                    $compact['student_detail']=$student_detail;
                    $compact['fees_info']=$fees_info;

                    if($student_detail->email != ""){


                        send_mail($student_detail->email, $student_detail->full_name, 'Dues Payment' , 'backEnd.feesCollection.dues_fees_email', $compact);


                    }

                    $parent_detail = SmParent::where('id', $student_detail->parent_id)->first();


                    if($parent_detail->guardians_email != ""){
                        send_mail($parent_detail->guardians_email, $parent_detail->guardians_name, 'Dues Payment' , 'backEnd.feesCollection.dues_fees_email', $compact);


                    }
                }


            }elseif(isset($request->send_sms)){


                foreach($request->student_list as $student){

                    $student_detail = SmStudent::find($student);
                    $parent_detail = SmParent::find($student_detail->parent_id);

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

                        if($student_detail->mobile != ""){

                            $result = $message = $client->messages->create($student_detail->mobile, array('from' => $from_phone_number, 'body' => $body));

                        }

                        // guardian sms
                        if($parent_detail->guardians_mobile != ""){

                            $result = $message = $client->messages->create($parent_detail->guardians_mobile, array('from' => $from_phone_number, 'body' => $body));
                        }

                    }
                    else if ($activeSmsGateway->gateway_name == 'Himalayasms') {

                        if($student_detail->mobile != ""){

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


                        if($parent_detail->fathers_mobile != ""){

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

                        if($student_detail->mobile != ""){

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

                        if($parent_detail->guardians_mobile != ""){

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
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();
            $fees_masters = SmFeesMaster::select('fees_group_id')->where('active_status', 1)->distinct('fees_group_id')->where('church_id',Auth::user()->church_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['fees_masters'] = $fees_masters->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.fees_statment', compact('classes', 'fees_masters'));
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
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();
            $fees_masters = SmFeesMaster::select('fees_group_id')->where('active_status', 1)->distinct('fees_group_id')->where('church_id',Auth::user()->church_id)->get();
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
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['fees_masters'] = $fees_masters->toArray();
                $data['fees_assigneds'] = $fees_assigneds->toArray();
                $data['fees_discounts'] = $fees_discounts->toArray();
                $data['applied_discount'] = $applied_discount;
                $data['student'] = $student;
                $data['age_group_id'] = $age_group_id;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.fees_statment', compact('classes', 'fees_masters', 'fees_assigneds', 'fees_discounts', 'applied_discount', 'student', 'age_group_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function balanceFeesReport(Request $request)
    {
        try {
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }
            return view('backEnd.feesCollection.balance_fees_report', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function balanceFeesSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required'
        ]);
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $students = SmStudent::where('age_group_id', $request->class)->where('mgender_id', $request->section)->where('church_id',Auth::user()->church_id)->get();
            $balance_students = [];
            $fees_masters = SmFeesMaster::where('active_status', 1)->where('church_id',Auth::user()->church_id)->get();
            foreach ($students as $student) {
                $total_balance = 0;
                $total_discount = 0;
                $total_amount = 0;
                $master_ids =[];
                foreach ($fees_masters as $fees_master) {

                    $due_date= strtotime($fees_master->date);
                    $now =strtotime(date('Y-m-d'));
                    if ($due_date > $now ) {
                        continue;
                    }
                    $master_ids[]=$fees_master->id;
                    $fees_assign = SmFeesAssign::where('member_id', $student->id)->where('fees_master_id', $fees_master->id)->where('church_id',Auth::user()->church_id)->first();
                    if ($fees_assign != "") {
                        $discount_amount = SmFeesPayment::where('active_status',1)->where('member_id', $student->id)->where('fees_type_id', $fees_master->fees_type_id)->sum('discount_amount');
                        $balance = SmFeesPayment::where('active_status',1)->where('member_id', $student->id)->where('fees_type_id', $fees_master->fees_type_id)->sum('amount');
                        $total_balance += $balance;
                        $total_discount += $discount_amount;
                        $total_amount += $fees_master->amount;

                    }
                }
                $total_paid = $total_balance + $total_discount;
                if ($total_amount > $total_paid) {

                    $balance_students[] = $student;
                }
            }
            // return $master_ids;
            $age_group_id = $request->class;
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['balance_students'] = $balance_students;
                $data['age_group_id'] = $age_group_id;
                return ApiBaseMethod::sendResponse($data, null);
            }
            // return $balance_students;
            $clas = SmClass::find($request->class);
            return view('backEnd.feesCollection.balance_fees_report', compact('classes', 'balance_students', 'age_group_id', 'clas'));
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
            $student = SmStudent::find($s_id);
            foreach ($groups as $group) {
                $fees_assigneds[] = SmFeesAssign::find($group);
            }
            $parent = DB::table('sm_parents')->where('id', $student->parent_id)->where('church_id',Auth::user()->church_id)->first();

            $unapplied_discount_amount = SmFeesAssignDiscount::where('member_id',$s_id)->where('church_id',Auth::user()->church_id)->sum('unapplied_amount');
            return view('backEnd.feesCollection.fees_payment_invoice_print')->with(['fees_assigneds' => $fees_assigneds, 'student' => $student,'unapplied_discount_amount'=>$unapplied_discount_amount, 'parent' => $parent]);
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

    public function transactionReport(Request $request)
    {
        try {
            $classes = SmClass::where('active_status', 1)
                ->where('church_id', Auth::user()->church_id)
                ->where('church_year_id', getAcademicId())
                ->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse(null, null);
            }
            return view('backEnd.feesCollection.transaction_report',compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function transactionReportSearch(Request $request)
    {
        $rangeArr = $request->date_range ? explode('-', $request->date_range) : "".date('m/d/Y')." - ".date('m/d/Y')."";

        try {
            $classes = SmClass::where('active_status', 1)
                ->where('church_id', Auth::user()->church_id)
                ->where('church_year_id', getAcademicId())
                ->get();

            if($request->date_range){
                $date_from = new \DateTime(trim($rangeArr[0]));
                $date_to =  new \DateTime(trim($rangeArr[1]));
            }

            if($request->date_range ){
                if($request->class){
                    $students=SmStudent::where('age_group_id',$request->class)
                        ->where('church_id',Auth::user()->church_id)
                        ->where('church_year_id', getAcademicId())
                        ->get();

                    $fees_payments = SmFeesPayment::where('active_status',1)->whereIn('member_id', $students->pluck('id'))
                        ->where('payment_date', '>=', $date_from)
                        ->where('payment_date', '<=', $date_to)
                        ->where('church_id',Auth::user()->church_id)
                        ->get();

                    $fees_payments = $fees_payments->groupBy('member_id');
                }else{
                    $fees_payments = SmFeesPayment::where('active_status',1)->where('payment_date', '>=', $date_from)
                        ->where('payment_date', '<=', $date_to)
                        ->where('church_id',Auth::user()->church_id)
                        ->get();

                    $fees_payments = $fees_payments->groupBy('member_id');
                }
            }

            if($request->class && $request->section){

                $students=SmStudent::where('age_group_id',$request->class)
                    ->where('mgender_id',$request->section)
                    ->where('church_id',Auth::user()->church_id)
                    ->where('church_year_id', getAcademicId())
                    ->get();

                $fees_payments = SmFeesPayment::where('active_status',1)->whereIn('member_id', $students->pluck('id'))
                    ->where('payment_date', '>=', $date_from)
                    ->where('payment_date', '<=', $date_to)
                    ->where('church_id',Auth::user()->church_id)
                    ->get();
                $fees_payments = $fees_payments->groupBy('member_id');

            }
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['fees_payments'] = $fees_payments->toArray();
                $data['add_incomes'] = $add_incomes->toArray();
                $data['add_expenses'] = $add_expenses->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.transaction_report', compact('fees_payments','classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentFineReport(Request $request)
    {
        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse(null, null);
            }
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
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($fees_payments, null);
            }
            return view('backEnd.reports.student_fine_report', compact('fees_payments'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    //
    public function bankPaymentSlip()
    {
        try {
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();
            $bank_slips = SmBankPaymentSlip::where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->where('approve_status',0)->orderBy('id', 'desc')->get();
            return view('backEnd.feesCollection.bank_payment_slip', compact('classes','bank_slips'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function bankPaymentSlipSearch(Request $request)
    {
        $input = $request->all();

        try {
            $bank_slips = SmBankPaymentSlip::query();
            if ($request->class != "") {
                $bank_slips->where('age_group_id', $request->class);
            }
            if ($request->section != "") {
                $bank_slips->where('mgender_id', $request->section);
            }
            if ($request->payment_date != "") {
                $date = strtotime($request->payment_date);
                $newformat = date('Y-m-d', $date);
                $bank_slips->where('date', $newformat);
            }
            if ($request->approve_status != "") {
                $bank_slips->where('approve_status', $request->approve_status);
            }

            $all_bank_slips = $bank_slips->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->orderBy('id', 'desc')->get();

            $date = $request->payment_date;
            $age_group_id = $request->class;
            $approve_status = $request->approve_status;
            $mgender_id = $request->section;
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();
            $sections = SmSection::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();
            return view('backEnd.feesCollection.bank_payment_slip', compact('all_bank_slips','classes','sections','date','age_group_id','mgender_id','approve_status'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function rejectFeesPayment(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required',
            'payment_reject_reason' => 'required'
        ]);
        if ($validator->fails()) {
            Toastr::warning('Required Fill Missing', 'Failed');
            return redirect()->back();
        }
        try{

            if (checkAdmin()) {
                $bank_payment = SmBankPaymentSlip::find($request->id);
            }else{
                $bank_payment = SmBankPaymentSlip::where('id',$request->id)->where('church_id',Auth::user()->church_id)->first();
            }
            $systemEmail = SmsTemplate::first();
            $student = SmStudent::find($bank_payment->member_id);
            $parent = SmParent::find($student->parent_id);

            if($bank_payment){

                $bank_payment->reason = $request->payment_reject_reason;
                $bank_payment->approve_status = 2;
                $result = $bank_payment->save();

                if($result){
                    $notification = new SmNotification();
                    $notification->role_id = 2;
                    $notification->message ="Bank Payment Rejected -" .'('.@$bank_payment->feesType->name.')';
                    $notification->date = date('Y-m-d');
                    $notification->user_id = $student->user_id;
                    $notification->url = "student-fees";
                    $notification->church_id = Auth::user()->church_id;
                    $notification->church_year_id = getAcademicId();
                    $notification->save();

                    try{
                        $reciver_email =  $student->full_name;
                        $receiver_name =   $student->email;
                        $subject= 'Bank Payment Rejected';
                        $view ="backEnd.feesCollection.bank_payment_reject_student";
                        $compact['data'] =  array(
                            'note' => $bank_payment->reason,
                            'date' =>dateConvert($notification->created_at),
                            'member_name' =>$student->full_name,
                        );
                        send_mail($reciver_email, $receiver_name, $subject , $view , $compact);
                    }catch(\Exception $e){
                        Log::info($e->getMessage());
                    }

                    $notification = new SmNotification();
                    $notification->role_id = 3;
                    $notification->message ="Bank Payment Rejected -" .'('.@$bank_payment->feesType->name.')';
                    $notification->date = date('Y-m-d');
                    $notification->user_id = $parent->user_id;
                    $notification->url = "parent-fees/".$student->id;
                    $notification->church_id = Auth::user()->church_id;
                    $notification->church_year_id = getAcademicId();
                    $notification->save();

                    try{
                        $reciver_email =  $student->email;
                        $receiver_name =   $student->full_name;
                        $subject= 'Bank Payment Rejected';
                        $view ="backEnd.feesCollection.bank_payment_reject_student";
                        $compact['data'] =  array(
                            'note' => $bank_payment->reason,
                            'date' =>dateConvert($notification->created_at),
                            'member_name' =>$student->full_name,
                        );
                        send_mail($reciver_email, $receiver_name, $subject , $view , $compact);
                    }catch(\Exception $e){
                        Log::info($e->getMessage());
                    }

                }

                Toastr::success('Operation successful', 'Success');
                return redirect()->back();

            }

        }
        catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }

    }

    public function approveFeesPayment(Request $request){
        try {

            if (checkAdmin()) {
                $bank_payment = SmBankPaymentSlip::find($request->id);
            }else{
                $bank_payment = SmBankPaymentSlip::where('id',$request->id)->where('church_id',Auth::user()->church_id)->first();
            }
            $get_master_id=SmFeesMaster::join('sm_fees_assigns','sm_fees_assigns.fees_master_id','=','sm_fees_masters.id')
                ->where('sm_fees_masters.fees_type_id',$bank_payment->fees_type_id)
                ->where('sm_fees_assigns.member_id',$bank_payment->member_id)->first();

            $fees_assign=SmFeesAssign::where('fees_master_id',$get_master_id->fees_master_id)->where('member_id',$bank_payment->member_id)->where('church_id',Auth::user()->church_id)->first();

            // return $bank_payment;

            if ($bank_payment->amount > $fees_assign->fees_amount) {
                Toastr::warning('Due amount less than bank payment', 'Warning');
                return redirect()->back();
            }

            $user = Auth::user();
            $fees_payment = new SmFeesPayment();
            $fees_payment->member_id = $bank_payment->member_id;
            $fees_payment->fees_type_id = $bank_payment->fees_type_id;
            $fees_payment->discount_amount = 0;
            $fees_payment->fine = 0;
            $fees_payment->amount = $bank_payment->amount;
            $fees_payment->assign_id = $bank_payment->assign_id;
            $fees_payment->payment_date = date('Y-m-d', strtotime($bank_payment->date));
            $fees_payment->payment_mode = $bank_payment->payment_mode;
            $fees_payment->bank_id= $bank_payment->payment_mode=='bank' ? $bank_payment->bank_id : null;
            $fees_payment->created_by = $user->id;
            $fees_payment->note = $bank_payment->note;
            $fees_payment->church_year_id = getAcademicId();
            $fees_payment->church_id = Auth::user()->church_id;
            $result = $fees_payment->save();
            $bank_payment->approve_status = 1;
            $bank_payment->save();


            $payment_mode_name=ucwords($bank_payment->payment_mode);
            $payment_method=SmPaymentMethhod::where('method',$payment_mode_name)->first();
            $income_head=generalSetting();

            $add_income = new SmAddIncome();
            $add_income->name = 'Fees Collect';
            $add_income->date = date('Y-m-d', strtotime($bank_payment->date));
            $add_income->amount = $bank_payment->amount;
            $add_income->fees_collection_id = $fees_payment->id;
            $add_income->active_status = 1;
            $add_income->income_head_id = $income_head->income_head_id;
            $add_income->payment_method_id = $payment_method->id;
            if($payment_method->id==3){
                $add_income->account_id = $bank_payment->bank_id;
            }
            $add_income->created_by = Auth()->user()->id;
            $add_income->church_id = Auth::user()->church_id;
            $add_income->church_year_id = getAcademicId();
            $add_income->save();


            if($payment_method->id==3){
                $bank=SmBankAccount::where('id',$bank_payment->bank_id)
                    ->where('church_id',Auth::user()->church_id)
                    ->first();
                $after_balance= $bank->current_balance + $bank_payment->amount;

                $bank_statement= new SmBankStatement();
                $bank_statement->amount= $bank_payment->amount;
                $bank_statement->after_balance= $after_balance;
                $bank_statement->type= 1;
                $bank_statement->details= "Fees Payment";
                $bank_statement->payment_date= date('Y-m-d', strtotime($bank_payment->date));
                $bank_statement->bank_id= $bank_payment->bank_id;
                $bank_statement->church_id=Auth::user()->church_id;
                $bank_statement->payment_method= $payment_method->id;
                $bank_statement->fees_payment_id= $fees_payment->id;
                $bank_statement->save();

                $current_balance= SmBankAccount::find($bank_payment->bank_id);
                $current_balance->current_balance=$after_balance;
                $current_balance->update();
            }



            // $fees_assign=SmFeesAssign::where('fees_master_id',$get_master_id->fees_master_id)->where('member_id',$bank_payment->member_id)->first();
            $fees_assign->fees_amount-=$bank_payment->amount;
            $fees_assign->save();

            $bank_slips = SmBankPaymentSlip::query();
            $bank_slips->where('age_group_id', $request->class);
            if ($request->section != "") {
                $bank_slips->where('mgender_id', $request->section);
            }
            if ($request->payment_date != "") {
                $date = strtotime($request->payment_date);
                $newformat = date('Y-m-d', $date);

                $bank_slips->where('date', $newformat);
            }
            $bank_slips = $bank_slips->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->orderBy('id', 'desc')->get();
            $date = $request->payment_date;
            $age_group_id = $request->class;
            $mgender_id = $request->section;
            $classes = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();
            $sections = SmSection::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id',Auth::user()->church_id)->get();

            $student = SmStudent::find($bank_payment->member_id);

            $notification = new SmNotification;
            $notification->user_id = $student->user_id;
            $notification->role_id = 2;
            $notification->date = date('Y-m-d');
            $notification->message = app('translator')->get('lang.fees_approved');
            $notification->church_id = Auth::user()->church_id;
            $notification->church_year_id = getAcademicId();
            $notification->save();

            try{
                $user=User::find($student->user_id);
                Notification::send($user, new FeesApprovedNotification($notification));
            }catch (\Exception $e) {
                Log::info($e->getMessage());
            }

            $parent = SmParent::find($student->parent_id);
            $notification = new SmNotification();
            $notification->role_id = 3;
            $notification->message = app('translator')->get('lang.fees_approved_for_child');
            $notification->date = date('Y-m-d');
            $notification->user_id = $parent->user_id;
            $notification->url = "";
            $notification->church_id = Auth::user()->church_id;
            $notification->church_year_id = getAcademicId();
            $notification->save();

            try{
                $user=User::find($parent->user_id);
                Notification::send($user, new FeesApprovedNotification($notification));
            }catch (\Exception $e) {
                Log::info($e->getMessage());
            }

            Toastr::success('Operation successful', 'Success');
            return redirect('bank-payment-slip');
            // return view('backEnd.feesCollection.bank_payment_slip', compact('bank_slips','classes','sections','date','age_group_id','mgender_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function fineReport(){
        $classes = SmClass::where('active_status', 1)
            ->where('church_year_id', getAcademicId())
            ->where('church_id',Auth::user()->church_id)
            ->get();

        return view('backEnd.accounts.fine_report',compact('classes'));
    }

    public function fineReportSearch(Request $request){
        $rangeArr = $request->date_range ? explode('-', $request->date_range) : "".date('m/d/Y')." - ".date('m/d/Y')."";

        try {
            $classes = SmClass::where('active_status', 1)
                ->where('church_id', Auth::user()->church_id)
                ->where('church_year_id', getAcademicId())
                ->get();

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
                    ->where('church_id',Auth::user()->church_id)
                    ->where('church_year_id', getAcademicId())
                    ->get();

                $fine_info = SmFeesPayment::where('active_status',1)->where('payment_date', '>=', $date_from)
                    ->where('payment_date', '<=', $date_to)
                    ->where('church_id',Auth::user()->church_id)
                    ->whereIn('member_id', $students)
                    ->get();
                $fine_info = $fine_info->groupBy('member_id');

            }

            if($request->class && $request->section){

                $students=StudentRecord::where('age_group_id',$request->class)
                    ->where('mgender_id',$request->section)
                    ->where('church_id',Auth::user()->church_id)
                    ->where('church_year_id', getAcademicId())
                    ->pluck('member_id')->unique()->toArray();

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

//added by nayem fees edit delete

    public function editFeesPayment($id){

        try {
            $fees_payment = SmFeesPayment::find($id);

            if(auth()->user()->role_id !=1){
                if($fees_payment->created_by !=  auth()->user()->id ){
                    Toastr::error('Payment recieved Other person,You Can not Edit', 'Failed');
                    return redirect()->back();
                }
            }
            $data['bank_info'] = SmPaymentGatewaySetting::where('gateway_name', 'Bank')->where('church_id', Auth::user()->church_id)->first();
            $data['cheque_info'] = SmPaymentGatewaySetting::where('gateway_name', 'Cheque')->where('church_id', Auth::user()->church_id)->first();

            $banks = SmBankAccount::where('church_id', Auth::user()->church_id)
                ->get();
            $method['bank_info'] = SmPaymentMethhod::where('method', 'Bank')->where('church_id', Auth::user()->church_id)->first();
            $method['cheque_info'] = SmPaymentMethhod::where('method', 'Cheque')->where('church_id', Auth::user()->church_id)->first();

            return view('backEnd.feesCollection.edit_fees_payment_modal', compact('fees_payment','data','method','banks'));

        } catch (\Throwable $th) {
            // throw $th;
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }

    }


    public function updateFeesPayment(Request $request){

        try {

            $assignCourseFees=SmFeesAssign::find($request->fees_assign_id);
            $fees_master = SmFeesMaster::find($assignCourseFees->fees_master_id);
            $amount_check = $assignCourseFees->fees_amount - $request->amount;

            if( $fees_master->amount <= $request->amount  ){
                Toastr::warning('Payment amount will not greater than fees assign amount', 'Warning');
                return redirect()->back();
            }elseif( $amount_check < 0){
                $payment=SmFeesPayment::find($request->fees_payment_id);
                $payment->payment_mode = $request->payment_mode;
                $payment->bank_id= $request->payment_mode=='bank' ? $request->bank_id : null;
                $payment->save();
                Toastr::warning('Fees Payment already full paid, Can not Change Amount', 'Warning');
                return redirect()->back();

            }

            if($assignCourseFees->fees_amount==0){

                $pre_amount = $assignCourseFees->fees_amount;

            }else{

                $diff_amount=$request->amount-$request->pre_amount;


                if($diff_amount > 0 ){

                    $pre_amount = $assignCourseFees->fees_amount-$diff_amount;


                }else{

                    $pre_amount = $assignCourseFees->fees_amount-($diff_amount);

                }

            }



            $assignCourseFees->fees_amount=$pre_amount;
            $result= $assignCourseFees->save();
            if($result){
                $payment=SmFeesPayment::find($request->fees_payment_id);
                $payment->amount=$request->amount;
                $payment->payment_mode = $request->payment_mode;
                $payment->bank_id= $request->payment_mode=='bank' ? $request->bank_id : null;
                $payment->save();
            }else{
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }



            Toastr::success('Operation successful', 'Success');
            return redirect()->back();

        } catch (\Throwable $th) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }



}