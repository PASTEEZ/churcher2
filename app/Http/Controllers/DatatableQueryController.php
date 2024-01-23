<?php

namespace App\Http\Controllers;

use App\Models\JyMemberRecord;
use DataTables;
use App\SmClass;
use App\SmStaff;
use App\SmSection;
use App\SmStudent;
use App\SmUserLog;
use App\SmAddIncome;
use App\SmEmailSmsLog;
use App\SmLeaveDefine;
use App\SmAcademicYear;
use App\SmNotification;
use App\SmAssignSubject;
use App\SmBankPaymentSlip;
use App\SmStudentAttendance;
use Illuminate\Http\Request;
use App\Models\StudentRecord;

use Illuminate\Support\Carbon;
use App\SmTeacherUploadContent;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Scopes\StatusAcademicSchoolScope;
use App\SmJymember;
use Illuminate\Support\Facades\Validator;

class DatatableQueryController extends Controller
{
    public function studentDetailsDatatable(Request $request)
    {
         
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->withoutGlobalScope(StatusAcademicSchoolScope::class)->get();
            $sessions = SmAcademicYear::where('school_id', Auth::user()->school_id)->get();
            $academic_year = $request->academic_year;
            $class_id = $request->class;
            $name = $request->name;
            $roll_no = $request->roll_no;
            $section = $request->section;
            $data['un_session_id']= $request->un_session_id ;
            $data['un_academic_id']= $request->un_academic_id ;
            $data['un_faculty_id']= $request->un_faculty_id;
            $data['un_department_id']= $request->un_department_id;
            $data['un_semester_id']= $request->un_semester_id;
            $data['un_semester_label_id']= $request->un_semester_label_id;
            $data['un_section_id']= $request->un_section_id;
           
            return view('backEnd.studentInformation.student_details', compact('classes', 'class_id', 'name', 'roll_no', 'sessions', 'section', 'academic_year','data'));
        }

        if ($request->ajax()) {
             
        
            $records = StudentRecord::query();
            $records->where('school_id',auth()->user()->school_id)->whereIn('class_id', [3, 4, 5]);
            $records->when(moduleStatusCheck('University') && $request->filled('un_academic_id'), function ($u_query) use ($request) {
                $u_query->where('un_academic_id', $request->un_academic_id);
                }, function ($query) use ($request) {
                    $query->when($request->academic_year, function ($query) use ($request) {
                    $query->where('academic_id', $request->academic_year);
                    });
            });
            
           $student_records = $records->where('is_promote', 0)->whereHas('student')->get(['student_id'])->unique('student_id')->toArray();

          $all_students =  SmStudent::whereIn('id',$student_records)
                                ->where('active_status', 1)
                                                              
                                ->with(array('parents' => function ($query) {
                                    $query->select('id', 'fathers_name');
                                }))
                                ->with(array('gender' => function ($query) {
                                    $query->select('id', 'base_setup_name');
                                }))
                                ->with(array('category' => function ($query) {
                                    $query->select('id', 'category_name');
                                }))
                                
                                ->when($request->name, function ($query) use ($request) {
                                    $query->where('full_name', 'like', '%' . $request->name . '%');
                                });

                             

            $students = SmStudent::with(['gender', 'studentRecords' => function ($q) use ($request) {
                return $q->when(moduleStatusCheck('University') && $request->filled('un_academic_id'), function ($u_query) use ($request) {
                        $u_query->where('un_academic_id', $request->un_academic_id);
                    }, function ($query) use ($request) {
                       $query->when($request->academic_year, function ($query) use ($request) {
                            $query->where('academic_id', $request->academic_year);
                        });
                    })
                    ->when(moduleStatusCheck('University') && $request->filled('un_faculty_id'), function ($u_query) use ($request) {
                        $u_query->where('un_faculty_id', $request->un_faculty_id);
                    }, function ($query) use ($request) {
                        $query->when($request->class, function ($query) use ($request) {
                            $query->where('class_id', $request->class);
                        });
                    })

                    ->when(moduleStatusCheck('University') && $request->filled('un_department_id'), function ($u_query) use ($request) {
                        $u_query->where('un_department_id', $request->un_department_id);
                    }, function ($query) use ($request) {
                        $query->when($request->section, function ($query) use ($request) {
                            $query->where('section_id', $request->section);
                        });
                    })
                    ->where('is_promote', 0)
                    ->when(!$request->academic_year && moduleStatusCheck('University')==false, function ($query) use ($request) {
                        $query->where('academic_id', getAcademicId());
                    });

            }])->select('sm_students.*');
            $students->where('sm_students.active_status', 1);

             
            $students = $students->where('sm_students.school_id', Auth::user()->school_id)
                ->with(array('parents' => function ($query) {
                    $query->select('id', 'fathers_name');
                }))
                ->with(array('sm_sections' => function ($query) {
                    $query->select('id', 'section_name');
                }))
                ->with(array('category' => function ($query) {
                    $query->select('id', 'category_name');
                }));

            return Datatables::of($all_students)
                ->addIndexColumn()
                ->addColumn('dob', function ($row) {

                    $dob = dateConvert(@$row->date_of_birth);

                    return $dob;
                })
               

                ->addColumn('full_name', function ($row) {
                    $full_name_link = '<a target="_blank" href="'. route('student_view', [$row->id]) . '">' . $row->first_name .' '. $row->last_name . '</a>';
                    return $full_name_link;
                })
                
                ->addColumn('mobile', function ($row) {
                    $mobile = '<a href="tel:'.$row->mobile.'">' .$row->mobile. '</a>';
                    return $mobile;
                })

 

                ->addColumn('class_sec', function ($row) use ($request) {
                    $class_sec=[];
                    foreach ($row->studentRecords as $classSec) {
                      
                            $class_sec[] = $classSec->class->class_name;
                       
                    }

                    return implode(', ', $class_sec);
                })


                ->addColumn('m_gender', function ($row) use ($request) {
                    $m_gender=[];
                    foreach ($row->studentRecords as $classSec) {
                        
                            $m_gender[] =  $classSec->section->section_name;
                      
                    }

                    return implode(', ', $m_gender);
                })

                

                ->addColumn('action', function ($row) {
                    $langName = (moduleStatusCheck('University')) ? app('translator')->get('university::un.assign_faculty_department') : app('translator')->get('student.assign_class') ;
                    $btn = '<div class="dropdown">
                                    <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>

                                    <div class="dropdown-menu dropdown-menu-right">'
                        .(userPermission(1201) === true ? '<a class="dropdown-item" target="_blank" href="' . route('student.assign-class', [$row->id]) . '">' . $langName . '</a>' :'')

                        .((userPermission(1201) === true && moduleStatusCheck('University')) ?
                        '<a class="dropdown-item" target="_blank" href="' . route('student_view', [$row->id,'assign_subject']) . '">' .  app('translator')->get('university::un.assign_subject') . '</a>' :'')

                        .'<a class="dropdown-item" target="_blank" href="' . route('student_view', [$row->id]) . '">' . app('translator')->get('common.view') . '</a>' .
                        (userPermission(66) === true ? '<a class="dropdown-item" href="' . route('student_edit', [$row->id]) . '">' . app('translator')->get('common.edit') . '</a>' : '') .

                        (userPermission(67) === true ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >' . app('translator')->get('common.disable') . '</a></span>' :
                            '<a onclick="deleteId(' . $row->id . ');" class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteStudentModal" data-id="' . $row->id . '"  >' . app('translator')->get('common.disable') . '</a>') : '') .

                        '</div>
                                </div>';

                    return $btn;
                })
                ->rawColumns(['action','full_name', 'mobile', 'dob','class_sec','full_name', 'mobile', 'dob','class_sec'])
                ->make(true);
        }
        return view('backEnd.studentInformation.students');

    }

   






    public function csmemberDetailsDatatable(Request $request)
    {
         
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->withoutGlobalScope(StatusAcademicSchoolScope::class)->get();
            $sessions = SmAcademicYear::where('school_id', Auth::user()->school_id)->get();
            $academic_year = $request->academic_year;
            $class_id = $request->class;
            $name = $request->name;
            $roll_no = $request->roll_no;
            $section = $request->section;
            $data['un_session_id']= $request->un_session_id ;
            $data['un_academic_id']= $request->un_academic_id ;
            $data['un_faculty_id']= $request->un_faculty_id;
            $data['un_department_id']= $request->un_department_id;
            $data['un_semester_id']= $request->un_semester_id;
            $data['un_semester_label_id']= $request->un_semester_label_id;
            $data['un_section_id']= $request->un_section_id;
           
            return view('backEnd.studentInformation.csmember_details', compact('classes', 'class_id', 'name', 'roll_no', 'sessions', 'section', 'academic_year','data'));
        }

        if ($request->ajax()) {
             //$students = DB::table('sm_jymembers')
               // ->where('academic_id', getAcademicId())
           
               $records = StudentRecord::query();
            $records->where('school_id',auth()->user()->school_id)->where('class_id',1);
            $records->when(moduleStatusCheck('University') && $request->filled('un_academic_id'), function ($u_query) use ($request) {
                $u_query->where('un_academic_id', $request->un_academic_id);
                }, function ($query) use ($request) {
                    $query->when($request->academic_year, function ($query) use ($request) {
                    $query->where('academic_id', $request->academic_year);
                    });
            })
            ->when(moduleStatusCheck('University') && $request->filled('un_faculty_id'), function ($u_query) use ($request) {
                $u_query->where('un_faculty_id', $request->un_faculty_id);
            }, function ($query) use ($request) {
                $query->when($request->class, function ($query) use ($request) {
                    $query->where('class_id', $request->class);
                });
            })
            
            ->when(moduleStatusCheck('University') && $request->filled('un_department_id'), function ($u_query) use ($request) {
                $u_query->where('un_department_id', $request->un_department_id);
            }, function ($query) use ($request) {
                $query->when($request->section, function ($query) use ($request) {
                    $query->where('section_id', $request->section);
                });
            })
            ->when(!$request->academic_year && moduleStatusCheck('University')==false, function ($query) use ($request) {
                $query->where('academic_id', getAcademicId());
            })
            
            ->when( moduleStatusCheck('University') && $request->filled('un_session_id'), function ($query) use ($request) {
                $query->where('un_session_id', $request->un_session_id);
            })
            
            ->when( moduleStatusCheck('University') && $request->filled('un_semester_label_id'), function ($query) use ($request) {
                $query->where('un_semester_label_id', $request->un_semester_label_id);
            });
            
           $student_records = $records->where('is_promote', 0)->whereHas('student')->get(['student_id'])->unique('student_id')->toArray();

          $all_students =  SmStudent::whereIn('id',$student_records)
                                ->where('active_status', 1)
                                
                                ->with(array('parents' => function ($query) {
                                    $query->select('id', 'fathers_name');
                                }))
                                ->with(array('gender' => function ($query) {
                                    $query->select('id', 'base_setup_name');
                                }))
                                ->with(array('category' => function ($query) {
                                    $query->select('id', 'category_name');
                                }))
                                ->when($request->name, function ($query) use ($request) {
                                    $query->where('full_name', 'like', '%' . $request->name . '%');
                                });

                             

            $students = SmStudent::with(['gender', 'studentRecords' => function ($q) use ($request) {
                return $q->when(moduleStatusCheck('University') && $request->filled('un_academic_id'), function ($u_query) use ($request) {
                        $u_query->where('un_academic_id', $request->un_academic_id);
                    }, function ($query) use ($request) {
                       $query->when($request->academic_year, function ($query) use ($request) {
                            $query->where('academic_id', $request->academic_year);
                        });
                    })
                    ->when(moduleStatusCheck('University') && $request->filled('un_faculty_id'), function ($u_query) use ($request) {
                        $u_query->where('un_faculty_id', $request->un_faculty_id);
                    }, function ($query) use ($request) {
                        $query->when($request->class, function ($query) use ($request) {
                            $query->where('class_id', $request->class);
                        });
                    })

                    ->when(moduleStatusCheck('University') && $request->filled('un_department_id'), function ($u_query) use ($request) {
                        $u_query->where('un_department_id', $request->un_department_id);
                    }, function ($query) use ($request) {
                        $query->when($request->section, function ($query) use ($request) {
                            $query->where('section_id', $request->section);
                        });
                    })
                    ->where('is_promote', 0)
                    ->when(!$request->academic_year && moduleStatusCheck('University')==false, function ($query) use ($request) {
                        $query->where('academic_id', getAcademicId());
                    });

            }])->select('sm_students.*');
            $students->where('sm_students.active_status', 1);

         
            $students = $students->where('sm_students.school_id', Auth::user()->school_id)
                ->with(array('parents' => function ($query) {
                    $query->select('id', 'fathers_name');
                }))
                ->with(array('gender' => function ($query) {
                    $query->select('id', 'base_setup_name');
                }))
                ->with(array('category' => function ($query) {
                    $query->select('id', 'category_name');
                }));

            return Datatables::of($all_students)
                ->addIndexColumn()
                ->addColumn('dob', function ($row) {

                    $dob = dateConvert(@$row->date_of_birth);

                    return $dob;
                })
               

                ->addColumn('full_name', function ($row) {
                    $full_name_link = '<a target="_blank" href="'. route('student_view', [$row->id]) . '">' . $row->first_name .' '. $row->last_name . '</a>';
                    return $full_name_link;
                })
                
                ->addColumn('mobile', function ($row) {
                    $mobile = '<a href="tel:'.$row->mobile.'">' .$row->mobile. '</a>';
                    return $mobile;
                })


                ->addColumn('semester_label', function ($row) use ($request) {
                    $semester_label=[];
                    foreach ($row->studentRecords as $label) {
                        if (moduleStatusCheck('University')) {
                            $semester_label[] = $label->unSemesterLabel->name;
                        }
                    }
                    return $semester_label;
                })

                ->addColumn('class_sec', function ($row) use ($request) {
                    $class_sec=[];
                    foreach ($row->studentRecords as $classSec) {
                        if (moduleStatusCheck('University')) {
                            $class_sec[] = $classSec->unFaculty->name.'('. $classSec->unDepartment->name .')';
                        } else {
                            $class_sec[] = $classSec->class->class_name.'('. $classSec->section->section_name .')';
                        }
                    }

                    return implode(', ', $class_sec);
                })

                ->addColumn('action', function ($row) {
                    $langName = (moduleStatusCheck('University')) ? app('translator')->get('university::un.assign_faculty_department') : app('translator')->get('student.assign_class') ;
                    $btn = '<div class="dropdown">
                                    <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>

                                    <div class="dropdown-menu dropdown-menu-right">'
                        .(userPermission(1201) === true ? '<a class="dropdown-item" target="_blank" href="' . route('student.assign-class', [$row->id]) . '">' . $langName . '</a>' :'')

                        .((userPermission(1201) === true && moduleStatusCheck('University')) ?
                        '<a class="dropdown-item" target="_blank" href="' . route('student_view', [$row->id,'assign_subject']) . '">' .  app('translator')->get('university::un.assign_subject') . '</a>' :'')

                        .'<a class="dropdown-item" target="_blank" href="' . route('student_view', [$row->id]) . '">' . app('translator')->get('common.view') . '</a>' .
                        (userPermission(66) === true ? '<a class="dropdown-item" href="' . route('student_edit', [$row->id]) . '">' . app('translator')->get('common.edit') . '</a>' : '') .

                        (userPermission(67) === true ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >' . app('translator')->get('common.disable') . '</a></span>' :
                            '<a onclick="deleteId(' . $row->id . ');" class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteStudentModal" data-id="' . $row->id . '"  >' . app('translator')->get('common.disable') . '</a>') : '') .

                        '</div>
                                </div>';

                    return $btn;
                })
                ->rawColumns(['action','full_name', 'mobile', 'dob','class_sec','full_name', 'mobile', 'dob','class_sec'])
                ->make(true);
        }
        return view('backEnd.studentInformation.csmembers');

    }






    public function jymemberDetailsDatatable(Request $request)
    {
         
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->withoutGlobalScope(StatusAcademicSchoolScope::class)->get();
            $sessions = SmAcademicYear::where('school_id', Auth::user()->school_id)->get();
            $academic_year = $request->academic_year;
            $class_id = $request->class;
            $name = $request->name;
            $roll_no = $request->roll_no;
            $section = $request->section;
            $data['un_session_id']= $request->un_session_id ;
            $data['un_academic_id']= $request->un_academic_id ;
            $data['un_faculty_id']= $request->un_faculty_id;
            $data['un_department_id']= $request->un_department_id;
            $data['un_semester_id']= $request->un_semester_id;
            $data['un_semester_label_id']= $request->un_semester_label_id;
            $data['un_section_id']= $request->un_section_id;
           
            return view('backEnd.studentInformation.jymember_details', compact('classes', 'class_id', 'name', 'roll_no', 'sessions', 'section', 'academic_year','data'));
        }

        if ($request->ajax()) {
             //$students = DB::table('sm_jymembers')
               // ->where('academic_id', getAcademicId())
           
               $records = StudentRecord::query();
            $records->where('school_id',auth()->user()->school_id)->where('class_id',2);
            $records->when(moduleStatusCheck('University') && $request->filled('un_academic_id'), function ($u_query) use ($request) {
                $u_query->where('un_academic_id', $request->un_academic_id);
                }, function ($query) use ($request) {
                    $query->when($request->academic_year, function ($query) use ($request) {
                    $query->where('academic_id', $request->academic_year);
                    });
            })
            ->when(moduleStatusCheck('University') && $request->filled('un_faculty_id'), function ($u_query) use ($request) {
                $u_query->where('un_faculty_id', $request->un_faculty_id);
            }, function ($query) use ($request) {
                $query->when($request->class, function ($query) use ($request) {
                    $query->where('class_id', $request->class);
                });
            })
            
            ->when(moduleStatusCheck('University') && $request->filled('un_department_id'), function ($u_query) use ($request) {
                $u_query->where('un_department_id', $request->un_department_id);
            }, function ($query) use ($request) {
                $query->when($request->section, function ($query) use ($request) {
                    $query->where('section_id', $request->section);
                });
            })
            ->when(!$request->academic_year && moduleStatusCheck('University')==false, function ($query) use ($request) {
                $query->where('academic_id', getAcademicId());
            })
            
            ->when( moduleStatusCheck('University') && $request->filled('un_session_id'), function ($query) use ($request) {
                $query->where('un_session_id', $request->un_session_id);
            })
            
            ->when( moduleStatusCheck('University') && $request->filled('un_semester_label_id'), function ($query) use ($request) {
                $query->where('un_semester_label_id', $request->un_semester_label_id);
            });
            
           $student_records = $records->where('is_promote', 0)->whereHas('student')->get(['student_id'])->unique('student_id')->toArray();

          $all_students =  SmStudent::whereIn('id',$student_records)
                                ->where('active_status', 1)
                                
                                ->with(array('parents' => function ($query) {
                                    $query->select('id', 'fathers_name');
                                }))
                                ->with(array('gender' => function ($query) {
                                    $query->select('id', 'base_setup_name');
                                }))
                                ->with(array('category' => function ($query) {
                                    $query->select('id', 'category_name');
                                }))
                                ->when($request->name, function ($query) use ($request) {
                                    $query->where('full_name', 'like', '%' . $request->name . '%');
                                });

                             

            $students = SmStudent::with(['gender', 'studentRecords' => function ($q) use ($request) {
                return $q->when(moduleStatusCheck('University') && $request->filled('un_academic_id'), function ($u_query) use ($request) {
                        $u_query->where('un_academic_id', $request->un_academic_id);
                    }, function ($query) use ($request) {
                       $query->when($request->academic_year, function ($query) use ($request) {
                            $query->where('academic_id', $request->academic_year);
                        });
                    })
                    ->when(moduleStatusCheck('University') && $request->filled('un_faculty_id'), function ($u_query) use ($request) {
                        $u_query->where('un_faculty_id', $request->un_faculty_id);
                    }, function ($query) use ($request) {
                        $query->when($request->class, function ($query) use ($request) {
                            $query->where('class_id', $request->class);
                        });
                    })

                    ->when(moduleStatusCheck('University') && $request->filled('un_department_id'), function ($u_query) use ($request) {
                        $u_query->where('un_department_id', $request->un_department_id);
                    }, function ($query) use ($request) {
                        $query->when($request->section, function ($query) use ($request) {
                            $query->where('section_id', $request->section);
                        });
                    })
                    ->where('is_promote', 0)
                    ->when(!$request->academic_year && moduleStatusCheck('University')==false, function ($query) use ($request) {
                        $query->where('academic_id', getAcademicId());
                    });

            }])->select('sm_students.*');
            $students->where('sm_students.active_status', 1);

         
            $students = $students->where('sm_students.school_id', Auth::user()->school_id)
                ->with(array('parents' => function ($query) {
                    $query->select('id', 'fathers_name');
                }))
                ->with(array('gender' => function ($query) {
                    $query->select('id', 'base_setup_name');
                }))
                ->with(array('category' => function ($query) {
                    $query->select('id', 'category_name');
                }));

            return Datatables::of($all_students)
                ->addIndexColumn()
                ->addColumn('dob', function ($row) {

                    $dob = dateConvert(@$row->date_of_birth);

                    return $dob;
                })
               

                ->addColumn('full_name', function ($row) {
                    $full_name_link = '<a target="_blank" href="'. route('student_view', [$row->id]) . '">' . $row->first_name .' '. $row->last_name . '</a>';
                    return $full_name_link;
                })
                
                ->addColumn('mobile', function ($row) {
                    $mobile = '<a href="tel:'.$row->mobile.'">' .$row->mobile. '</a>';
                    return $mobile;
                })


                ->addColumn('semester_label', function ($row) use ($request) {
                    $semester_label=[];
                    foreach ($row->studentRecords as $label) {
                        if (moduleStatusCheck('University')) {
                            $semester_label[] = $label->unSemesterLabel->name;
                        }
                    }
                    return $semester_label;
                })

                ->addColumn('class_sec', function ($row) use ($request) {
                    $class_sec=[];
                    foreach ($row->studentRecords as $classSec) {
                        if (moduleStatusCheck('University')) {
                            $class_sec[] = $classSec->unFaculty->name.'('. $classSec->unDepartment->name .')';
                        } else {
                            $class_sec[] = $classSec->class->class_name.'('. $classSec->section->section_name .')';
                        }
                    }

                    return implode(', ', $class_sec);
                })

                ->addColumn('action', function ($row) {
                    $langName = (moduleStatusCheck('University')) ? app('translator')->get('university::un.assign_faculty_department') : app('translator')->get('student.assign_class') ;
                    $btn = '<div class="dropdown">
                                    <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>

                                    <div class="dropdown-menu dropdown-menu-right">'
                        .(userPermission(1201) === true ? '<a class="dropdown-item" target="_blank" href="' . route('student.assign-class', [$row->id]) . '">' . $langName . '</a>' :'')

                        .((userPermission(1201) === true && moduleStatusCheck('University')) ?
                        '<a class="dropdown-item" target="_blank" href="' . route('student_view', [$row->id,'assign_subject']) . '">' .  app('translator')->get('university::un.assign_subject') . '</a>' :'')

                        .'<a class="dropdown-item" target="_blank" href="' . route('student_view', [$row->id]) . '">' . app('translator')->get('common.view') . '</a>' .
                        (userPermission(66) === true ? '<a class="dropdown-item" href="' . route('student_edit', [$row->id]) . '">' . app('translator')->get('common.edit') . '</a>' : '') .

                        (userPermission(67) === true ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >' . app('translator')->get('common.disable') . '</a></span>' :
                            '<a onclick="deleteId(' . $row->id . ');" class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteStudentModal" data-id="' . $row->id . '"  >' . app('translator')->get('common.disable') . '</a>') : '') .

                        '</div>
                                </div>';

                    return $btn;
                })
                ->rawColumns(['action','full_name', 'mobile', 'dob','class_sec','full_name', 'mobile', 'dob','class_sec'])
                ->make(true);
        }
        return view('backEnd.studentInformation.jymembers');

    }



    public function incomeList(Request $request)
    {
        $add_incomes = SmAddIncome::with('incomeHeads', 'paymentMethod')->where('active_status', '=', 1)->where('school_id', Auth::user()->school_id)->get();
        return Datatables::of($add_incomes)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {

                $date = dateConvert(@$row->created_at);

                return $date;
            })
            ->rawColumns(['date'])
            ->addColumn('action', function ($row) {
                $btn = '<div class="dropdown">
                                    <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>

                                    <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" target="_blank" href="' . route('student_view', [$row->id]) . '">' . app('translator')->get('common.view') . '</a>' .
                    (userPermission(66) === true ? '<a class="dropdown-item" href="' . route('student_edit', [$row->id]) . '">' . app('translator')->get('common.edit') . '</a>' : '') .

                    (userPermission(67) === true ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >' . app('translator')->get('common.disable') . '</a></span>' :
                        '<a onclick="deleteId(' . $row->id . ');" class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteStudentModal" data-id="' . $row->id . '"  >' . app('translator')->get('common.disable') . '</a>') : '') .

                    '</div>
                                </div>';

                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);

    }


    public function emailSmsLogAjax()
    {
        $emailSmsLogs = SmEmailSmsLog::where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->latest()->get();
        return Datatables::of($emailSmsLogs)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {
                $date = dateConvert(@$row->created_at);
                return $date;
            })
            ->addColumn('send_via', function ($row) {
                if ($row->send_through == "E") {
                    $type = "Email";
                } else {
                    $type = "Sms";
                }
                return $type;
            })
            ->rawColumns(['date'])
            ->make(true);
    }

    public function userLogAjax(Request $request)
    {

        $user_logs = SmUserLog::where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->orderBy('id', 'desc')
            ->with(array('role' => function ($query) {
                $query->select('id', 'name');
            }))
            ->with(array('user' => function ($query) {
                $query->select('id', 'full_name');
            }))
            ->get();

        return Datatables::of($user_logs)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {
                $date = dateConvert(@$row->created_at);
                return $date;
            })
            ->rawColumns(['date'])
            ->addColumn('login_time', function ($row) {
                $login_time = $row->created_at->toDayDateTimeString();
                return $login_time;
            })
            ->rawColumns(['login_time'])
            ->make(true);
    }

    public function bankPaymentSlipAjax()
    {
        $bank_slips = SmBankPaymentSlip::with('studentInfo', 'feesType')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->orderBy('approve_status', 'asc')->latest()->get();

        return Datatables::of($bank_slips)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {
                $date = dateConvert(@$row->created_at);
                return $date;
            })
            ->rawColumns(['date'])
            ->addColumn('status', function ($row) {
                if ($row->approve_status == 0) {
                    $btn = '<button class="primary-btn small bg-warning text-white border-0">' . app('translator')->get('common.pending') . '</button>';
                } elseif ($row->approve_status == 1) {
                    $btn = '<button class="primary-btn small bg-success text-white border-0  tr-bg">' . app('translator')->get('common.approved') . '</button>';
                } elseif ($row->approve_status == 2) {
                    $btn = '<button class="primary-btn small bg-danger text-white border-0  tr-bg">' . app('translator')->get('common.rejected') . '</button>';
                }
                return $btn;
            })
            ->addColumn('slip', function ($row) {
                if ((!empty($row->slip))) {
                    $btn = '<a class="text-color" data-toggle="modal" data-target="#showCertificateModal(' . $row->id . ');" href="#">' . app('translator')->get('common.approve') . '</a>';
                } else {
                    $btn = "";
                }
                return $btn;
            })
            ->addColumn('action', function ($row) {
                if ($row->approve_status == 0) {
                    $btn = '<div class="dropdown">
                                <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>

                                <div class="dropdown-menu dropdown-menu-right">
                                        <a onclick="enableId(' . $row->id . ');" class="dropdown-item" href="#" data-toggle="modal" data-target="#enableStudentModal" data-id="' . $row->id . '"  >' . app('translator')->get('common.approve') . '</a>' .
                        '<a onclick="rejectPayment(' . $row->id . ');" class="dropdown-item" href="#" data-toggle="modal" data-id="' . $row->id . '"  >' . app('translator')->get('common.reject') . '</a>' .
                        '</div>
                                </div>';
                } elseif ($row->approve_status == 1) {
                    $btn = '<div class="dropdown">
                                <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>

                                <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" href="#">' . app('translator')->get('common.approved') . '</a>' .
                        '</div>
                                </div>';
                } elseif ($row->approve_status == 2) {
                    $btn = '<div class="dropdown">
                                <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>

                                <div class="dropdown-menu dropdown-menu-right">
                                        <a onclick="viewReason(' . $row->id . ');" class="dropdown-item ' . "reason" . $row->id . '" href="#" data-reason="' . $row->reason . '"  >' . app('translator')->get('common.view') . '</a>' .
                        '</div>
                                </div>';
                }

                return $btn;
            })
            ->rawColumns(['status', 'action', 'slip'])
            ->make(true);

    }


    public function assignmentList()
    {

        $user = Auth()->user();

        if (teacherAccess()) {
            SmNotification::where('user_id', $user->id)->where('role_id', 1)->update(['is_read' => 1]);
        }

        if (!teacherAccess()) {
            $uploadContents = SmTeacherUploadContent::where('content_type', 'as')
                            ->where('academic_id', getAcademicId())
                            ->where('school_id', Auth::user()->school_id)
                            ->where('course_id', '=', null)
                            ->where('chapter_id', '=', null)
                            ->where('lesson_id', '=', null)
                            ->get();
        } else {
            $uploadContents = SmTeacherUploadContent::where(function ($q) {
                $q->where('created_by', Auth::user()->id)->orWhere('available_for_admin', 1);
            })->where('content_type', 'as')

            ->where('course_id', '=', null)
            ->where('chapter_id', '=', null)
            ->where('lesson_id', '=', null)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->get();
        }
        return Datatables::of($uploadContents)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {

                $date = dateConvert(@$row->created_at);

                return $date;
            })
            ->addColumn('type', function ($row) {
                if ($row->content_type == 'as') {
                    $type = "assignment";
                } elseif ($row->content_type == 'st') {
                    $type = "study_material";
                } elseif ($row->content_type == 'sy') {
                    $type = "syllabus";
                } else {
                    $type = "others";
                }

                return __('study.' . $type);

            })
            ->addColumn('avaiable', function ($row) {
                $avaiable = '';
                if ($row->available_for_admin == 1) {
                    $avaiable .= app('translator')->get('study.all_admins') .', ';
                }
                if ($row->available_for_all_classes == 1) {
                    $avaiable .= app('translator')->get('study.all_classes_student').', ';
                }
                if ($row->classes != "" && $row->sections != "") {
                    $avaiable .= (app('translator')->get('study.all_students_of') . " " . $row->classes->class_name . '->' . @$row->sections->section_name).', ';
                }

                if ($row->classes != "" && $row->section == null) {
                    $avaiable .= (app('translator')->get('study.all_students_of') . " " . $row->classes->class_name . '->' . app('translator')->get('study.all_sections')) .', ';
                }

                if(moduleStatusCheck('University')){
                    $avaiable .= app('translator')->get('study.all_students_of') . " " . @$row->semesterLabel->name  . '(' . @$row->unSection->section_name .'-' . @$row->undepartment->name . ')';
                }

                return $avaiable;

            })
            ->addColumn('class_sections', function ($row) {
                if(moduleStatusCheck('University')){
                    $semLabel =  $row->semesterLabel->name ;
                    $academ = $row->unAcademic->name;
                    return $semLabel . '(' .$academ. ')';
                }else{
                    if (($row->class != "") && ($row->section != "")) {
                        $classes = $row->classes->class_name;
                        $sections = $row->sections->section_name;
                        return $classes . '(' . $sections . ')';
                    } elseif (($row->class != "") && ($row->section == null)) {
                        $classes = $row->classes->class_name;
                        $nullsections = app('translator')->get('common.all_sections');
                        return $classes . '(' . $nullsections . ')';
                    } elseif ($row->section != "") {
                        return $sections = $row->sections->section_name;
                    } elseif ($row->class != "") {
                        return $classes = $row->classes->class_name;
                    }

                }
            })
            ->rawColumns(['date'])
            ->addColumn('action', function ($row) {
                $btn = '<div class="dropdown">
                                    <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>

                                    <div class="dropdown-menu dropdown-menu-right">
                                            <a data-modal-size="modal-lg" title="' . __('study.view_content_details') . '" class="dropdown-item modalLink" href="' . route('upload-content-view', [$row->id]) . '">' . app('translator')->get('common.view') . '</a>' .
                    (userPermission(587) === true ? '<a class="dropdown-item" href="' . route('upload-content-edit', [$row->id]) . '">' . app('translator')->get('common.edit') . '</a>' : '') .

                    (userPermission(95) === true ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >' . app('translator')->get('common.disable') . '</a></span>' :
                        '<a onclick="deleteAssignMent(' . $row->id . ');"  class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteApplyLeaveModal" data-id="' . $row->id . '"  >' . app('translator')->get('common.delete') . '</a>') : '') .


                    '</div>
                                </div>';

                return $btn;
            })
            ->rawColumns(['action', 'date'])
            ->make(true);
    }


    public function leaveDefineList()
    {

        $leave_defines = SmLeaveDefine::with('role', 'user')->where('active_status', 1)
            ->where('school_id', Auth::user()->school_id)
            ->where('academic_id', getAcademicId())
            ->with(array('role' => function ($query) {
                $query->select('id', 'name');
            }))
            ->with(array('user' => function ($query) {
                $query->select('id', 'full_name');
            }))
            ->with(array('leaveType' => function ($query) {
                $query->select('id', 'type');
            }));


        return Datatables::of($leave_defines)
            ->addColumn('userName', function ($row) {
                return $row->user->full_name;
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="dropdown">
                                        <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>

                                        <div class="dropdown-menu dropdown-menu-right">'
                    . (userPermission(201) === true ? '<a class="dropdown-item" href="' . route('leave-define-edit', [$row->id]) . '">' . app('translator')->get('common.edit') . '</a>' : '') .

                    (userPermission(201) === true ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >' . app('translator')->get('common.disable') . '</a></span>' :
                        '<a onclick="addLeaveDay(' . $row->id . ');"  class="dropdown-item ' . "reason" . $row->id . '" href="#" data-toggle="modal" data-target="#addLeaveDayModal" data-total_days="' . $row->days . '"  >' . app('translator')->get('common.add_days') . '</a>') : '') .

                    (userPermission(202) === true ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >' . app('translator')->get('common.disable') . '</a></span>' :
                        '<a onclick="deleteLeaveDefine(' . $row->id . ');"  class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteLeaveDefineModal" data-id="' . $row->id . '"  >' . app('translator')->get('common.delete') . '</a>') : '') .


                    '</div>
                                    </div>';

                return $btn;
            })
            ->rawColumns(['action', 'date'])
            ->make(true);

    }


    public function syllabusList()
    {
        try {
            if (!teacherAccess()) {
                $uploadContents = SmTeacherUploadContent::where('content_type', 'sy')
                    ->where('course_id', '=', null)
                    ->where('chapter_id', '=', null)
                    ->where('lesson_id', '=', null)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()
                        ->school_id)->get();
            } else {
                $uploadContents = SmTeacherUploadContent::where(function ($q) {
                    $q->where('created_by', Auth::user()->id)->orWhere('available_for_admin', 1);
                })->where('content_type', 'sy')
                ->where('course_id', '=', null)
                ->where('chapter_id', '=', null)
                ->where('lesson_id', '=', null)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
            }
            // return  $uploadContents;
            return Datatables::of($uploadContents)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {

                    $date = dateConvert(@$row->created_at);

                    return $date;
                })
                ->addColumn('type', function ($row) {
                    if ($row->content_type == 'as') {
                        $type = "assignment";
                    } elseif ($row->content_type == 'st') {
                        $type = "study_material";
                    } elseif ($row->content_type == 'sy') {
                        $type = "syllabus";
                    } else {
                        $type = "others";
                    }

                    return __('study.' . $type);

                })
                ->addColumn('avaiable', function ($row) {
                    $avaiable = '';
                    if ($row->available_for_admin == 1) {
                        $avaiable .= app('translator')->get('study.all_admins') .', ';
                    }
                    if ($row->available_for_all_classes == 1) {
                        $avaiable .= app('translator')->get('study.all_classes_student').', ';
                    }
                    if ($row->classes != "" && $row->sections != "") {
                        $avaiable .= (app('translator')->get('study.all_students_of') . " " . $row->classes->class_name . '->' . @$row->sections->section_name).', ';
                    }

                    if ($row->classes != "" && $row->section == null) {
                        $avaiable .= (app('translator')->get('study.all_students_of') . " " . $row->classes->class_name . '->' . app('translator')->get('study.all_sections')) .', ';
                    }

                    if(moduleStatusCheck('University')){
                        $avaiable .= app('translator')->get('study.all_students_of') . " " . @$row->semesterLabel->name  . '(' . @$row->unSection->section_name .'-' . @$row->undepartment->name . ')';
                    }

                    return $avaiable;


                })
                ->addColumn('class_sections', function ($row) {

                    if(moduleStatusCheck('University')){
                        $semLabel =  $row->semesterLabel->name ;
                        $academ = $row->unAcademic->name;
                        return $semLabel . '(' .$academ. ')';
                    }else{
                        if (($row->class != "") && ($row->section != "")) {
                            $classes = $row->classes->class_name;
                            $sections = $row->sections->section_name;
                            return $classes . '(' . $sections . ')';
                        } elseif (($row->class != "") && ($row->section == null)) {
                            $classes = $row->classes->class_name;
                            $nullsections = app('translator')->get('study.all_sections');
                            return $classes . '(' . $nullsections . ')';
                        } elseif ($row->section != "") {
                            return $sections = $row->sections->section_name;
                        } elseif ($row->class != "") {
                            return $classes = $row->classes->class_name;;
                        }
                }
                })
                ->rawColumns(['date'])
                ->addColumn('action', function ($row) {
                    $btn = '<div class="dropdown">
                                    <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' . app('translator')->get('common.select') . '</button>

                                    <div class="dropdown-menu dropdown-menu-right">
                                            <a data-modal-size="modal-lg" title="' . __('study.view_content_details') . '" class="dropdown-item modalLink" href="' . route('upload-content-view', [$row->id]) . '">' . app('translator')->get('common.view') . '</a>' .
                        (userPermission(587) === true ? '<a class="dropdown-item" href="' . route('upload-content-edit', [$row->id]) . '">' . app('translator')->get('common.edit') . '</a>' : '') .

                        (userPermission(95) === true ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >' . app('translator')->get('common.disable') . '</a></span>' :
                            '<a onclick="deleteAssignMent(' . $row->id . ');"  class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteApplyLeaveModal" data-id="' . $row->id . '"  >' . app('translator')->get('common.delete') . '</a>') : '') .


                        '</div>
                                </div>';

                    return $btn;
                })
                ->rawColumns(['action', 'date'])
                ->make(true);

        } catch (\Exception $e) {
            return redirect()->back();
        }
    }

}

