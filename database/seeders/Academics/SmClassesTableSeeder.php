<?php

namespace Database\Seeders\Academics;

use App\Models\StudentRecord;
use App\SmAdmissionQuery;
use App\SmAdmissionQueryFollowup;
use App\SmAssignClassTeacher;
use App\SmClass;
use App\SmParent;
use App\SmSection;
use App\SmStaff;
use App\SmStudent;
use App\SmSubject;
use App\User;
use Illuminate\Database\Seeder;

class SmClassesTableSeeder extends Seeder
{
    public $sections;
    public $subjects;

    public function __construct()
    {
        $this->sections = SmSection::all();
        $this->subjects = SmSubject::all();
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id = 1, $church_year_id = 1, $count = 10)
    {
        $sections = SmSection::where('church_id', $church_id)->where('church_year_id', $church_year_id)->get();
        $subjects = SmSubject::where('church_id', $church_id)->where('church_year_id', $church_year_id)->get();

        SmClass::factory()->times($count)->create([
            'church_id' => $church_id,
            'church_year_id' => $church_year_id
        ])->each(function ($class) use ($sections) {
            $class_sections = [];
            foreach ($sections as $section) {
                $class_sections[] = [
                    'mgender_id' => $section->id,
                    'church_id' => $class->church_id,
                    'church_year_id' => $class->church_year_id,
                ];
                $i = 0;
                SmStudent::factory()->times(5)->create()->each(function ($student) use ($class, $section) {

                    User::factory()->times(1)->create([
                        'role_id' => 2,
                        'email' => $student->email,
                        'username' => $student->email,
                        'church_id' => $class->church_id,
                    ])->each(function ($user) use ($student) {
                        $student->user_id = $user->id;
                        $student->save();
                    });

                    SmParent::factory()->times(1)->create([
                        'church_id' => $class->church_id,
                        'guardians_email' => 'guardian_' . $student->id . '@infixedu.com',
                    ])->each(function ($parent) use ($student) {
                        $student->parent_id = $parent->id;
                        $student->save();
                        User::factory()->times(1)->create([
                            'role_id' => 3,
                            'email' => $parent->guardians_email,
                            'username' => $parent->guardians_email,
                            'church_id' => $parent->church_id,
                        ])->each(function ($user) use ($parent) {
                            $parent->user_id = $user->id;
                            $parent->save();
                        });
                    });

                    StudentRecord::create([
                        'age_group_id' => $class->id,
                        'mgender_id' => $section->id,
                        'church_id' => $class->church_id,
                        'church_year_id' => $class->church_year_id,
                        'roll_no' => $student->id,
                        'session_id' => $class->church_year_id,
                        'is_default' => 1,
                        'member_id' => $student->id,
                    ]);
                });
            }
            $class_sections = $class->classSection()->createMany($class_sections);
            $assign_class_teachers = [];
            foreach ($class_sections as $class_section) {
                $assign_class_teachers[] = [
                    'age_group_id' => $class_section->age_group_id,
                    'mgender_id' => $class_section->mgender_id,
                    'church_year_id' => $class_section->church_year_id,
                    'church_id' => $class_section->church_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                SmStaff::factory()->times(1)->create([
                    'email' => 'staff_'.$class_section->id.'@infixedu.com',
                    'church_id' => $class_section->church_id,
                ])->each(function($staff){

                });
            }

            SmAdmissionQuery::factory()->times(10)->create([
                'class' => $class->id,
                'church_id' => $class->church_id,
                'church_year_id' => $class->church_year_id,
            ])->each(function ($admission_query) {
                SmAdmissionQueryFollowup::factory()->times(random_int(5, 10))->create([
                    'admission_query_id' => $admission_query->id,
                    'church_id' => $admission_query->church_id,
                    'church_year_id' => $admission_query->church_year_id,
                ]);
            });
        });
    }
}
