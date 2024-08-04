<?php

namespace App\Http\Requests\Admin\Leave;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SmLeaveTypeRequest extends FormRequest
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
            'type' => ['required', 'max:200', Rule::unique('sm_leave_types')->where('church_id', $church_id)->ignore($this->id) ],
        ];
    }
}
