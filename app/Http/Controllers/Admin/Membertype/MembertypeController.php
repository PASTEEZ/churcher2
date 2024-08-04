<?php

namespace App\Http\Controllers\admin\Membertype;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\SmSection;
use App\SmClass;
class MembertypeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $sections = SmSection::query();
            
              
            $data = $sections->where('church_year_id',getAcademicId());
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

}
