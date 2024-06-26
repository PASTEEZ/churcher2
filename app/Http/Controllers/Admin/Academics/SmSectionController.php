<?php

namespace App\Http\Controllers\Admin\Academics;

use App\SmSection;
use App\YearCheck;
use App\ApiBaseMethod;
use App\SmClassSection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\Academics\SectionRequest;
use Modules\University\Entities\UnAcademicYear;

class SmSectionController extends Controller
{
    public function __construct()
	{
        $this->middleware('PM');
        // User::checkAuth();
	}

    public function index(Request $request)
    {
        

        try {
            $sections = SmSection::query();
            if(moduleStatusCheck('University')){
            $data = $sections->where('un_church_year_id',getAcademicId());
            }else{
                $data = $sections->where('church_year_id',getAcademicId());
            }
            $sections = $data->where('church_id',auth()->user()->church_id)->get();

            $unAcademics = null;
            if (moduleStatusCheck('University')) {
                $unAcademics = UnAcademicYear::where('church_id', auth()->user()->church_id)->get()
                ->pluck('name', 'id')
                ->prepend(__('university::un.select_academic'), ' *')
                ->toArray();
            }
            return view('backEnd.academics.section', compact('sections', 'unAcademics'));
        } catch (\Exception $e) {
          
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function store(SectionRequest $request)
    {
       
        $church_year=academicYears();
        if ($church_year==null) {
            Toastr::warning('Create Church yearfirst', 'Warning');
            return redirect()->back();
        }


        // if ($validator->fails()) {
        //     if (ApiBaseMethod::checkUrl($request->fullUrl())) {
        //         return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
        //     }
        //     return redirect()->back()
        //         ->withErrors($validator)
        //         ->withInput();
        // }

        try {
            $section = new SmSection();
            $section->mgender_name = $request->name;
            $section->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
            $section->church_id = Auth::user()->church_id;
            $section->created_at=auth()->user()->id;
            $section->church_year_id = !moduleStatusCheck('University') ? getAcademicId() : null;
            if (moduleStatusCheck('University')) {
                $section->un_church_year_id = getAcademicId();
            }
            $result = $section->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Section has been created successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            }
            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }



    }
    public function edit(Request $request, $id)
    {

        try {
            $section = SmSection::where('id',$id)->where('church_id',auth()->user()->church_id)->first();
            if(is_null($section)){
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
            $sections = SmSection::query();
            if(moduleStatusCheck('University')){
            $data = $sections->where('un_church_year_id',getAcademicId());
            }else{
                $data = $sections->whereNull('un_church_year_id')->where('church_year_id',getAcademicId());
            }
            $sections = $data->where('church_id',auth()->user()->church_id)->get();
            $unAcademics = null;
            if (moduleStatusCheck('University')) {
                $unAcademics = UnAcademicYear::where('church_id', auth()->user()->church_id)->get()
                ->pluck('name', 'id')
                ->prepend(__('university::un.select_academic'), ' *')
                ->toArray();
            }
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['section'] = $section->toArray();
                $data['sections'] = $sections->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.academics.section', compact('section', 'sections', 'unAcademics'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function update(SectionRequest $request)
    {
  
        // if ($validator->fails()) {
        //     if (ApiBaseMethod::checkUrl($request->fullUrl())) {
        //         return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
        //     }
        //     return redirect()->back()
        //         ->withErrors($validator)
        //         ->withInput();
        // }

        try {
          
            $section = SmSection::find($request->id);
            $section->mgender_name = $request->name;
            $result = $section->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Section has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } 
            Toastr::success('Operation successful', 'Success');
            return redirect('section');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function delete(Request $request, $id)
    {
        try {
            $tables = SmClassSection::where('mgender_id', $id)->first();
                if ($tables == null) {
                          SmSection::destroy($request->id);
                          Toastr::success('Operation successful', 'Success');
                          return redirect('section');
                } else {
                    $msg = 'This section already assigned with class .';
                    Toastr::warning($msg, 'Warning');
                    return redirect()->back();
                }

        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}