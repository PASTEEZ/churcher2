<?php
namespace App\Http\Controllers\Admin\Transport;
use App\SmClass;
use App\SmRoute;
use App\SmStudent;
use App\SmVehicle;
use App\YearCheck;
use App\ApiBaseMethod;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Admin\StudentInfo\SmStudentReportController;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;

class SmTransportController extends Controller
{
    public function __construct()
	{
        $this->middleware('PM');
        // User::checkAuth();
	}

    public function studentTransportReport(Request $request){
        try{
            $classes = SmClass::get();
            $routes = SmRoute::get();
            $vehicles = SmVehicle::get();
            $students = SmStudent::with('class','section','parents','route','vehicle')->where('vechile_id', '!=', "")->get();
            return view('backEnd.transport.student_transport_report', compact('classes', 'routes', 'vehicles', 'students'));

        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }
    }

    public function studentTransportReportSearch(Request $request){
        $input = $request->all();
        if(moduleStatusCheck('University')){
            $validator = Validator::make($input,[
                'un_session_id' => "required",
                'route' => "required",
                'vehicle' => "required",
            ]);

        }else{
            $validator = Validator::make($input,[
                'class' => "required",
                'section' => "required",
                'route' => "required",
                'vehicle' => "required",
            ]);
        }


        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try{
            $member_ids = [];
            $data = [];
            $students = SmStudent::query();
            $students->where('active_status', 1);
            $student_records = StudentRecord::query();
            $classes = SmClass::where('active_status', 1)->where('church_id',Auth::user()->church_id)->get();
            if(moduleStatusCheck('University')){
                  $member_ids = universityFilter($student_records, $request)
                ->groupBy('member_id')->get('member_id');
                $stdent_ids = [];
                foreach($member_ids as $record){
                    $stdent_ids[]= $record->member_id;
                }
            }
            else
            {
                $member_ids = SmStudentReportController::classSectionStudent($request);
            }

            if($request->route != ""){
                $students->where('route_list_id', $request->route);
            }else{
                $students->where('route_list_id', '!=', '');
            }
            
            if($request->vehicle != ""){
                $students->where('vechile_id', $request->vehicle);
            }else{
                $students->where('vechile_id', '!=', '');
            }

            $students = $students->whereIn('id', $member_ids)->where('church_id',Auth::user()->church_id)->get();
            $routes = SmRoute::where('active_status', 1)->where('church_id',Auth::user()->church_id)->get();
            $vehicles = SmVehicle::where('active_status', 1)->where('church_id',Auth::user()->church_id)->get();

            $data['classes'] = $classes;
            $data['routes'] = $routes;
            $data['vehicles'] = $vehicles;
            $data['students'] = $students;
            $data['age_group_id'] = $request->class;
            $data['mgender_id'] = $request->mgender_id;
            $data['route_id'] =$request->route;
            $data['vechile_id'] =  $request->vehicle;
            if (moduleStatusCheck('University')) {
                $interface = App::make(UnCommonRepositoryInterface::class);
                $data += $interface->getCommonData($request);
            }
            return view('backEnd.transport.student_transport_report',$data);
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }
    }
    public function studentTransportReportApi(Request $request){

        try{
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $transport = DB::table('sm_assign_vehicles')
                ->select('sm_routes.title as route','sm_vehicles.vehicle_no','sm_vehicles.vehicle_model','sm_vehicles.made_year','sm_staffs.full_name as driver_name','sm_staffs.mobile','sm_staffs.driving_license')
                ->join('sm_routes', 'sm_assign_vehicles.route_id', '=', 'sm_routes.id')
                ->join('sm_vehicles', 'sm_assign_vehicles.vehicle_id', '=', 'sm_vehicles.id')
                ->join('sm_staffs', 'sm_vehicles.driver_id', '=', 'sm_staffs.id')
                ->where('church_id',Auth::user()->church_id)->get();

                return ApiBaseMethod::sendResponse($transport, null);
            }
            //return view('backEnd.transport.student_transport_report', compact('classes', 'routes', 'vehicles', 'students'));
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }
    }
}