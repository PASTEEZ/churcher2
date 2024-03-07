<?php

namespace App\Http\Requests\Admin\Transport;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SmAssignVehicleRequest extends FormRequest
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
            'route' => ['required', Rule::unique('sm_assign_vehicles', 'route_id')->where('church_id', $church_id)->ignore($this->id) ],
            'vehicles' => 'required|array',
        ];
    }
}
