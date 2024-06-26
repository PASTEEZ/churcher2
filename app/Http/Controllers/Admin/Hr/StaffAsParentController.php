<?php

namespace App\Http\Controllers\Admin\Hr;

use App\User;
use App\SmStaff;
use App\SmParent;
use App\SmStudent;
use App\SmUserLog;
use App\SmLanguage;
use App\SmsTemplate;
use App\SmDateFormat;
use App\SmAcademicYear;
use App\InfixModuleManager;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Modules\University\Entities\UnAcademicYear;

class StaffAsParentController extends Controller
{  
   public function loginAsRole()
   {
        $user_id = auth()->user()->id;
        $user = User::find($user_id);
        $staff = $user->staff; 
        $previous_role_id = $staff->previous_role_id;      
        $staff->update([           
            'role_id'=>$previous_role_id, 
        ]);
        $user->role_id= $previous_role_id; 
        $user->save();       
        $this->loginSession($user->id);
        return redirect()->route('admin-dashboard');
   }
   public function loginAsParent()
   {
        $user_id = auth()->user()->id;
        $user = User::find($user_id);
        $staff = $user->staff;       
        $staff->update([
            'previous_role_id'=>$user->role_id, 
            'role_id'=>3,
        ]);
        $user->role_id= 3;
        $user->save();
        $this->loginSession($user->id);
        return redirect()->route('parent-dashboard');
    
   }

    public function staff($email = null, $mobile = null, $staff_id = null)
    {
        if(!$email && !$mobile && !$staff_id) {
            return null;
        }
        if ($staff_id) {
            return  $staff = SmStaff::find($staff_id);
        }
       $staff = SmStaff::when($mobile && !$email, function ($q) use ($mobile){
            $q->where('mobile', $mobile);
        })
        ->when($email && !$mobile, function ($q) use ($email){
            $q->where('email', $email);
        })
        ->when($email && $mobile, function ($q) use ($mobile){
            $q->where('mobile', $mobile);
        })        
        ->first(['id', 'parent_id', 'user_id']);
        if (!$staff) {
            if ($email && $mobile) {
                $staff = SmStaff::where('email', $email)->first(['id', 'parent_id', 'user_id']);
            }
        }
        return $staff;
    }
    public function parent($email = null, $mobile = null)
    {
        if(!$email && !$mobile) {
            return null;
        }
        $parent = SmParent::when($mobile && !$email, function ($q) use ($mobile){
            $q->where('guardians_mobile', $mobile);
        })
        ->when($email && !$mobile, function ($q) use ($email){
            $q->where('guardians_email', $email);
        })
        ->when($email && $mobile, function ($q) use ($mobile){
            $q->where('guardians_mobile', $mobile);
        })
        ->first();

        return $parent;
    }
    public function staffParentStore($staff, $request, $church_year)
    {        
        $guardians_relation = $request->guardians_relation;
        $relation = $request->relation;
        
        if(!$staff && $request->staff_parent) {
            $staff = SmStaff::find($request->staff_parent);
        }
        if($staff && !$guardians_relation) {
            if($staff->gender_id ==1) {
                $guardians_relation = 'Father';
                $relation = 'F';
            }elseif($staff->gender_id ==2) {
                $guardians_relation = 'Mother';
                $relation = 'M';
            }else {
                $guardians_relation = 'Other';
                $relation = 'O';
            }            
        }        
        
        $exit = SmParent::where('user_id', $staff->user_id)->first();
        if(!$exit) {
            $parent = new SmParent();
            $parent->user_id = $staff->user_id;
            if($relation = 'F') {
                $parent->fathers_name =   $staff->full_name ?? $request->fathers_name;
                $parent->fathers_mobile = $staff->mobile ?? $request->fathers_phone;
                $parent->fathers_occupation = $request->fathers_occupation;
                $parent->fathers_photo = $staff->staff_photo;
            } 
            if($relation = 'M') {
                $parent->mothers_name =  $staff->full_name ?? $request->mothers_name;
                $parent->mothers_mobile = $staff->mobile ?? $request->mothers_phone;
                $parent->mothers_occupation = $request->mothers_occupation;
                $parent->mothers_photo = $staff->staff_photo;
            }
            $parent->guardians_name =  $staff->full_name ?? $request->guardians_name;
            $parent->guardians_mobile =  $staff->mobile ?? $request->guardians_phone;
            $parent->guardians_email =  $staff->email ?? $request->guardians_email;
            $parent->guardians_occupation = $request->guardians_occupation;
            $parent->guardians_relation = $guardians_relation;
            $parent->relation = $relation;
            $parent->guardians_photo = $staff->staff_photo;
            $parent->guardians_address = $request->guardians_address;
            $parent->church_id = Auth::user()->church_id;
            $parent->church_year_id = $request->session;
            $parent->created_at = $church_year->year . '-01-01 12:00:00';
            $parent->save();

            return $parent->id;
        }
        return $exit->id;
    }
    private function loginSession($user_id)
    {
        userStatusChange($user_id, 0);
        Session::flush();    
        Auth::logout();

        $logged_in = Auth::loginUsingId($user_id);
        if ($logged_in) {           

            if (!Auth::user()->access_status) {
                $this->guard()->logout();
                Toastr::error('You are not allowed, Please contact with administrator.', 'Failed');
                return redirect()->route('login');
            }

            // System date format save in session
            $date_format_id = generalSetting()->date_format_id;
            $system_date_format = 'jS M, Y';
            if($date_format_id){
                $system_date_format = SmDateFormat::where('id', $date_format_id)->first(['format'])->format;
            }

            session()->put('system_date_format', $system_date_format);

            // System academic session id in session

            $all_modules = [];
            $modules = InfixModuleManager::select('name')->get();
            foreach ($modules as $module) {
                $all_modules[] = $module->name;
            }

            session()->put('all_module', $all_modules);

            //Session put text decoration
            $ttl_rtl = generalSetting()->ttl_rtl;
            session()->put('text_direction', $ttl_rtl);


            //Session put activeLanguage
            $systemLanguage = SmLanguage::where('church_id', Auth::user()->church_id)->get();
            session()->put('systemLanguage', $systemLanguage);
            //session put academic years
            
            if(moduleStatusCheck('University')){
                $church_years = Auth::check() ? UnAcademicYear::where('active_status', 1)->where('church_id', Auth::user()->church_id)->get() : '';
            }else{
                $church_years = Auth::check() ? SmAcademicYear::where('active_status', 1)->where('church_id', Auth::user()->church_id)->get() : '';
            }
            session()->put('church_years', $church_years);
            //session put sessions and selected language


          
            $profile = SmStaff::where('user_id', Auth::id())->first();
            if ($profile) {
                session()->put('profile', $profile->staff_photo);
            }
            $session_id = $profile && $profile->church_year_id ? $profile->church_year_id : generalSetting()->session_id;
           

            if(moduleStatusCheck('University')){
                $session_id = generalSetting()->un_church_year_id;
                if(!$session_id){
                    $session = UnAcademicYear::where('church_id', Auth::user()->church_id)->where('active_status', 1)->first();
                } else{
                    $session = UnAcademicYear::find($session_id);
                }
             
                session()->put('sessionId', $session->id);
                session()->put('session', $session);
            }
            else{
                if(!$session_id){
                    $session = SmAcademicYear::where('church_id', Auth::user()->church_id)->where('active_status', 1)->first();
                } else{
                    $session = SmAcademicYear::find($session_id);
                }
                session()->put('sessionId', $session->id);
                session()->put('session', $session); 
            }

            if(!$session){
                $session = SmAcademicYear::where('church_id', Auth::user()->church_id)->first();
            }



            session()->put('sessionId', $session->id);
            session()->put('session', $session);
            session()->put('school_config', generalSetting());

            $dashboard_background = DB::table('sm_background_settings')->where([['is_default', 1], ['title', 'Dashboard Background']])->first();
            session()->put('dashboard_background', $dashboard_background);

            $email_template = SmsTemplate::where('church_id',Auth::user()->church_id)->first();
            session()->put('email_template', $email_template);

            session(['role_id' => Auth::user()->role_id]);
            $agent = new Agent();
            $user_log = new SmUserLog();
            $user_log->user_id = Auth::user()->id;
            $user_log->role_id = Auth::user()->role_id;
            $user_log->church_id = Auth::user()->church_id;
            $user_log->ip_address = \Request::ip();
            if(moduleStatusCheck('University')){
                $user_log->un_church_year_id = getAcademicid();
            }else{
                $user_log->church_year_id = getAcademicid() ?? 1;
            }
            $user_log->user_agent = $agent->browser() . ', ' . $agent->platform();
            $user_log->save();

            userStatusChange(auth()->user()->id, 1);           

        }
    }
}
