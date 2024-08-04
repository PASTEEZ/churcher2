<?php

namespace App\Http\Requests\Admin\StudentInfo;

use App\SmStudentCategory;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SmStudentCategoryRequest extends FormRequest
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
        $church_id=auth()->user()->church_id;
        return [
            'category' => ['required', Rule::unique('sm_student_categories', 'category_name')->where('church_id', $church_id)->ignore($this->id) ],
        ];
    }

}
