<?php

namespace App\Http\Requests\Admin\GeneralSettings;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SmLanguageRequest extends FormRequest
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
            'name' => ['required', Rule::unique('languages')->where('church_id', $church_id)->ignore($this->id) ],
            'code' => 'required | max:15',
            'native' => 'required | max:50',
            'rtl' => 'required',
        ];
    }
}
