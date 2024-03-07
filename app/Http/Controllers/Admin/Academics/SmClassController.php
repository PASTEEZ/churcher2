<?php

namespace App\Http\Controllers\Admin\Academics;

 

 



use App\SmClass;
use App\SmSection;
use App\tableList;
use App\YearCheck;
use App\ApiBaseMethod;
use App\SmClassSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\Academics\ClassRequest;

class SmClassController extends Controller
{
    public $date;

    public function __construct()
	{
        $this->middleware('PM');

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
            $classes = SmClass::with('groupclassSections')->withCount('records')->get();
        
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['sections'] = $sections->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.academics.class', compact('classes', 'sections'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }









    public function store(ClassRequest $request)
    {
        
        DB::beginTransaction();

            try {
                        $class = new SmClass();
                        $class->age_group_name = $request->name;
                        $class->pass_mark = $request->pass_mark;
                        $class->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                        $class->created_by=auth()->user()->id;
                        $class->church_id = Auth::user()->church_id;
                        $class->church_year_id = getAcademicId();
                        $class->save();
                        $class->toArray();

                        foreach ($request->section as $section) {
                            $smClassSection = new SmClassSection();
                            $smClassSection->age_group_id = $class->id;
                            $smClassSection->mgender_id = $section;
                            $smClassSection->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                            $smClassSection->church_id = Auth::user()->church_id;
                            $smClassSection->church_year_id = getAcademicId();
                            $smClassSection->save();
                        }
                    
                    DB::commit();

                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        return ApiBaseMethod::sendResponse(null, 'Class has been created successfully');
                    }
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
           
            } catch (\Exception $e) {
                DB::rollBack();                
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }

    }

    public function edit(Request $request, $id)
    {


        try {
            $classById = SmCLass::find($id);
            $sectionByNames = SmClassSection::select('mgender_id')->where('age_group_id', '=', $classById->id)->get();
            $sectionId = array();
            foreach ($sectionByNames as $sectionByName) {
                $sectionId[] = $sectionByName->mgender_id;
            }

            $sections = SmSection::where('active_status', '=', 1)->where('created_at', 'LIKE', '%' . $this->date . '%')->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();

            $classes = SmClass::where('active_status', '=', 1)->orderBy('id', 'desc')->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['sections'] = $sections->toArray();
                $data['classes'] = $classes->toArray();
                $data['classById'] = $classById;
                $data['sectionId'] = $sectionId;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.academics.class', compact('classById', 'classes', 'sections', 'sectionId'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function update(ClassRequest $request)
    {
     
        
        // if ($validator->fails()) {
        //     if (ApiBaseMethod::checkUrl($request->fullUrl())) {
        //         return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
        //     }
        //     return redirect()->back()
        //         ->withErrors($validator)
        //         ->withInput();
        // }


        SmCLassSection::where('age_group_id', $request->id)->delete();



        DB::beginTransaction();

        try {
            $class = SmCLass::find($request->id);
            $class->age_group_name = $request->name;
            $class->pass_mark = $request->pass_mark;
            $class->save();
            $class->toArray();
            try {
                foreach ($request->section as $section) {
                    $smClassSection = new SmClassSection();
                    $smClassSection->age_group_id = $class->id;
                    $smClassSection->mgender_id = $section;
                    $smClassSection->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                    $smClassSection->church_id = Auth::user()->church_id;
                    $smClassSection->church_year_id = getAcademicId();
                    $smClassSection->save();
                }

                DB::commit();

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendResponse(null, 'Class has been updated successfully');
                }
                Toastr::success('Operation successful', 'Success');
                return redirect('class');
            } catch (\Exception $e) {
                DB::rollBack();
            }
        } catch (\Exception $e) {
            DB::rollBack();
        }

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendError('Something went wrong, please try again.');
        }
        Toastr::error('Operation Failed', 'Failed');
        return redirect()->back();
    }


    public function delete(Request $request, $id)
    {
        try {
            $tables = tableList::getTableList('age_group_id', $id);

            if($tables == null || $tables == "Class sections, ") {
                
                DB::beginTransaction();

                // $class_sections = SmClassSection::where('age_group_id', $id)->get();
                  $class_sections = SmClassSection::where('age_group_id', $id)->get();
                    foreach ($class_sections as $key => $class_section) {
                        SmClassSection::destroy($class_section->id);
                    }
                   $section = SmClass::destroy($id);
                DB::commit();
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($section) {
                        return ApiBaseMethod::sendResponse(null, 'Class has been deleted successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again.');
                    }
                }  
                
                Toastr::success('Operation successful', 'Success');
                return redirect('class');
            } else{
                DB::rollback();
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error($msg, 'Failed');
                return redirect()->back();
            }

        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}