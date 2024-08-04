<?php

namespace App\Http\Controllers\Admin\SystemSettings;

use App\SmClass;
use App\SmSection;
use App\tableList;
use App\YearCheck;
use App\ApiBaseMethod;
use App\SmAcademicYear;
use App\SmClassSection;
use App\SmGeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Scopes\AcademicSchoolScope;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\isNull;
use App\Scopes\ActiveStatusSchoolScope;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Admin\GeneralSettings\SmAcademicYearRequest;

class SmAcademicYearController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
        // User::checkAuth();
    }
    
    public function index(Request $request)
    {
        try {
            $church_years = SmAcademicYear::where('active_status', 1)->orderBy('year', 'ASC')->where('church_id', Auth::user()->church_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($church_years, null);
            }
            return view('backEnd.systemSettings.church_year', compact('church_years'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function store(SmAcademicYearRequest $request)
    {
        $yr = SmAcademicYear::orderBy('id', 'desc')->where('church_id', Auth::user()->church_id)->first();
        $created_year = $request->starting_date;
       
        DB::beginTransaction();
        $church_year = new SmAcademicYear();
        $church_year->year = $request->year;
        $church_year->title = $request->title;
        $church_year->starting_date = date('Y-m-d', strtotime($request->starting_date));
        $church_year->ending_date = date('Y-m-d', strtotime($request->ending_date));
        if ($request->copy_with_church_year != null) {
                $church_year->copy_with_church_year =implode(",",$request->copy_with_church_year);
            }
        $church_year->created_at = $created_year;
        $church_year->church_id = Auth::user()->church_id;
        $result = $church_year->save();
        if($result){
            session()->forget('church_years');
            $church_years = SmAcademicYear::where('active_status', 1)->where('church_id', Auth::user()->church_id)->get();
            session()->put('church_years',$church_years);   
        }
        $sm_Gs = SmGeneralSettings::where('church_id', Auth::user()->church_id)->first();
        $sm_Gs->session_id = $church_year->id;
        $sm_Gs->church_year_id = $church_year->id;
        $sm_Gs->session_year = $church_year->year;
        $sm_Gs->save();
        session()->forget('sessionId'); 
        session()->put('sessionId', $sm_Gs->session_id); 
        session()->forget('generalSetting');
        $generalSetting = SmGeneralSettings::where('church_id',Auth::user()->church_id)->first();
        session()->put('generalSetting', $generalSetting);

        $data = \App\SmMarksGrade::where('church_year_id', $yr->id)->where('church_id', Auth::user()->church_id)->get();
      
        if (!empty($data)) {
            foreach ($data as $k0ey => $value) {
                $newClient = $value->replicate();
                $newClient->created_at = $created_year;
                $newClient->updated_at = $created_year;
                $newClient->church_year_id = $church_year->id;
                $newClient->save();
            }
        }

        if ($request->copy_with_church_year != null) {
            $tables = $request->copy_with_church_year;
            $tables = array_filter($tables);
            if (!empty($tables)) {
                if ($yr) {
                    foreach ($tables as $table_name) {
                        $data = $table_name::where('church_year_id', $yr->id)->where('church_id', Auth::user()->church_id)->withoutGlobalScopes([
                            StatusAcademicSchoolScope::class,
                            AcademicSchoolScope::class,
                            ActiveStatusSchoolScope::class
                        ])->get();
                       
                        if (!empty($data)) {
                            foreach ($data as $k0ey => $value) {
                                $newClient = $value->replicate();
                                $newClient->created_at = $created_year;
                                $newClient->updated_at = $created_year;
                                $newClient->church_year_id = $church_year->id;
                                $newClient->save();
                            }
                        }
                    }
                }
                $classes = SmClass::where('church_year_id', $church_year->id)->where('church_id', Auth::user()->church_id)->withoutGlobalScope(StatusAcademicSchoolScope::class)->get();
                $sections = SmSection::where('church_year_id', $church_year->id)->where('church_id', Auth::user()->church_id)->withoutGlobalScope(StatusAcademicSchoolScope::class)->get();
                foreach ($classes as $class) {
                    foreach ($sections as $section) {
                        $class_section = new SmClassSection();
                        $class_section->age_group_id = $class->id;
                        $class_section->mgender_id = $section->id;
                        $class_section->created_at = $created_year;
                        $class_section->church_id = Auth::user()->church_id;
                        $class_section->church_year_id = $church_year->id;
                        $class_section->save();
                    }
                }
            }
        }
        


        DB::commit();
        Toastr::success('Operation successful', 'Success');
        return redirect()->back();
    }

    public function show(Request $request, $id)
    {
        try {
             if (checkAdmin()) {
                $church_year = SmAcademicYear::find($id);
            }else{
                $church_year = SmAcademicYear::where('id',$id)->where('church_id',Auth::user()->church_id)->first();
            }
            $church_years = SmAcademicYear::where('church_id', Auth::user()->church_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['church_year'] = $church_year->toArray();
                $data['church_years'] = $church_years->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.systemSettings.church_year', compact('church_year', 'church_years'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function edit($id)
    {
        Toastr::error('Operation Failed', 'Failed');
        return redirect()->back();
    }


    public function update(Request $request, $id)
    {
        $input = $request->all();
        // return $input;
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'year' => 'required|numeric|digits:4',
                'title' => "required|max:150",
                'starting_date' => 'required',
                'ending_date' => 'required',
                'id' => "required"
            ]);
        } else {
            $validator = Validator::make($input, [
                'year' => 'required|numeric|digits:4',
                'title' => "required|max:150",
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
        try {
            $yr = SmAcademicYear::where('id', getAcademicId())->where('church_id', Auth::user()->church_id)->first();
            $created_year = $request->starting_date;
            if ($yr->year == $request->year) {
                Toastr::warning('You cannot copy current Church yearinfo.', 'Warning');
                return redirect('academic-year');
            }

            if (checkAdmin()) {
                $church_year = SmAcademicYear::find($request->id);
            }else{
                $church_year = SmAcademicYear::where('id',$request->id)->where('church_id',Auth::user()->church_id)->first();
            }
            $church_year->year = $request->year;
            $church_year->title = $request->title;
            $church_year->starting_date = date('Y-m-d', strtotime($request->starting_date));
            $church_year->ending_date = date('Y-m-d', strtotime($request->ending_date));
            $church_year->created_at = $created_year;
            if ($yr->year != $request->year) {
                if ($request->copy_with_church_year != null) {
                    $church_year->copy_with_church_year =implode(",",$request->copy_with_church_year);
                }
            }
            $result = $church_year->save();
            if($result){
                session()->forget('church_years');
                $church_years = SmAcademicYear::where('active_status', 1)->where('church_id', Auth::user()->church_id)->get();
                session()->put('church_years',$church_years);
            }
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Year has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('academic-year');
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

    public function destroy(Request $request, $id)
    {
        try {
            // $session_id = 'church_year_id';
            // $tables = tableList::getTableList($session_id, $id);
            try {

                if (getAcademicId() != $id) {
                    if (checkAdmin()) {
                        $delete_query = SmAcademicYear::find($id);
                    }else{
                        $delete_query = SmAcademicYear::where('id',$id)->where('church_id',Auth::user()->church_id)->first();
                    }


                    $del_tables=explode(',',@$delete_query->copy_with_church_year);
               

                    if(!is_null($del_tables)){
                        foreach ($del_tables as $del_table_name) {
                            if($del_table_name){
                                $del_data = new $del_table_name();
                                $del_data = $del_data->where('church_year_id', $id)->delete();
                            }
                        }
                    }

                    SmClassSection::where('church_year_id', $request->id)->where('church_id', Auth::user()->church_id)->delete();
                  
                    $delete_query->delete();

                   
                    if ($delete_query) {
                        session()->forget('church_years');
                        $church_years = SmAcademicYear::where('active_status', 1)->where('church_id', Auth::user()->church_id)->get();
                        session()->put('church_years', $church_years);
                        
                        Toastr::success('Operation successful', 'Success');
                        return redirect()->back();
                    } else {
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
                    
                } else {
                    Toastr::warning('You cannot delete current academic year.', 'Warning');
                    return redirect()->back();
                }
                
                
            } catch (\Illuminate\Database\QueryException $e) {
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}