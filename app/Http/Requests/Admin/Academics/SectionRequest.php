<?php

namespace App\Http\Requests\Admin\Academics;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required',Rule::unique('sm_sections', 'mgender_name')->when(moduleStatusCheck('University'), function ($query) {
                $query->where('un_church_year_id', getAcademicId());
            }, function ($query) {
                $query->where('church_year_id', getAcademicId());
            })->where('church_id', auth()->user()->church_id)->ignore($this->id)],
        ];
    }
}
