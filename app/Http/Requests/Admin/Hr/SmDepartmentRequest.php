<?php

namespace App\Http\Requests\Admin\Hr;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SmDepartmentRequest extends FormRequest
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
            'name' => ['required', 'max:200', Rule::unique('sm_human_departments')->where('church_id', $church_id)->ignore($this->id) ],
        ];
    }
}
