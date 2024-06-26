<?php

namespace App\Http\Requests\Admin\Academics;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SmClassRoomRequest extends FormRequest
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
            'room_no' => ['required', 'max:100' , Rule::unique('sm_class_rooms', 'room_no')->where('church_year_id', getAcademicId())->where('church_id', auth()->user()->church_id)->ignore($this->id)],
            'capacity' => 'required'
        ];
    }
}
