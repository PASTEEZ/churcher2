<?php

namespace App\Rules;

use App\LibrarySubject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Rule;

class UniqueSubject implements Rule
{
    public $id;
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function passes($attribute, $value)
    {
        
        $isExist= LibrarySubject::where('id','!=', $this->id)->where('church_id', Auth::user()->church_id)->where('subject_name', $value)->exists();
        
        if ($isExist) {
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'subject name has already been taken';
    }
}
