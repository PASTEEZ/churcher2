<?php

namespace App\Http\Requests\Admin\Accounts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SmChartOfAccountRequest extends FormRequest
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
            'head' => ['required', 'min:2' , Rule::unique('sm_chart_of_accounts', 'head')->where('church_year_id', getAcademicId())->where('church_id', auth()->user()->church_id)->ignore($this->id)],
            'type' => 'required',
        ];
    }
}
