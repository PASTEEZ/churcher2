<?php

namespace App\Http\Requests\Admin\GeneralSettings;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SmCurrencyRequest extends FormRequest
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
            'name' => ['required', 'max:25'],
            'code' => ['required', 'max:15' ,Rule::unique('sm_currencies', 'code')->where('church_id', $church_id)->ignore($this->id) ],
            'symbol' => 'required | max:15',
            'currency_type'=>['required', 'in:S,C'],
            'currency_position'=>['required', 'in:S,P'],
            'space'=>['required'],
            'decimal_digit'=>['sometimes', 'nullable', 'max:5'],
            'decimal_separator'=>['sometimes', 'nullable', 'max:1'],
            'thousand_separator'=>['sometimes', 'nullable', 'max:1'],          


        ];
    }
}
