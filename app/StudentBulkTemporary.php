<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentBulkTemporary extends Model
{
    protected $fillable  = ['registration_number', 'member_id_no', 'first_name', 'last_name', 'date_of_birth', 'marital_status', 'gender', 'home_town', 'mobile', 'email', 'registration_date', 'region', 'height', 'weight', 'father_name', 'father_phone', 'father_occupation', 'mother_name', 'mother_phone', 'mother_occupation', 'guardian_name', 'guardian_relation', 'guardian_email', 'guardian_phone', 'guardian_occupation', 'guardian_address', 'current_address', 'permanent_address', 'day_born', 'employer_name', 'national_identification_no', 'local_identification_no', 'previous_school_details', 'note', 'user_id'];
}