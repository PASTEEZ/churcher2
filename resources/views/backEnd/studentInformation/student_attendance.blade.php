@extends('backEnd.master')
@section('title') 
@lang('student.student_attendance')
@endsection

@section('mainContent')
    <section class="sms-breadcrumb mb-40 up_breadcrumb white-box">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1>@lang('student.student_attendance')</h1>
                <div class="bc-pages">
                    <a href="{{route('dashboard')}}">@lang('common.dashboard')</a>
                    <a href="#">@lang('student.student_information')</a>
                    <a href="#">@lang('student.student_attendance')</a>
                </div>
            </div>
        </div>
    </section>
    <section class="admin-visitor-area up_st_admin_visitor">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-lg-12 d-flex align-items-center justify-content-between flex-wrap">
                    <div class="main-title m-0">
                        <h3 class="mb-30">@lang('common.select_criteria') </h3>
                    </div>
                    <a href="{{route('student-attendance-import')}}"
                       class="primary-btn small fix-gr-bg pull-right mb-20"><span
                                class="ti-plus pr-2"></span>@lang('student.import_attendance')</a>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="white-box">
                        {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'student-search', 'method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'search_studentA']) }}
                        <div class="row">
                            <input type="hidden" name="url" id="url" value="{{URL::to('/')}}">
                            @if(moduleStatusCheck('University'))
                                @includeIf('university::common.session_faculty_depart_academic_semester_level',['mt'=>'mt-30','hide'=>['USUB'], 'required'=>['USEC']])
                                <div class="col-lg-3 col-md-3 mt-30">
                                    <div class="row no-gutters input-right-icon">
                                        <div class="col">
                                            <div class="input-effect">
                                                <input class="primary-input date form-control{{ $errors->has('attendance_date') ? ' is-invalid' : '' }} {{isset($date)? 'read-only-input': ''}}"
                                                       id="startDate" type="text"
                                                       name="attendance_date" autocomplete="off"
                                                       value="{{isset($date)? $date: date('m/d/Y')}}">
                                                <label for="startDate">@lang('hr.attendance_date')*</label>
                                                <span class="focus-border"></span>
    
                                                @if ($errors->has('attendance_date'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('attendance_date') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <button class="" type="button">
                                                <i class="ti-calendar" id="start-date-icon"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @else
                            <div class="col-lg-4 col-md-4 sm_mb_20 sm2_mb_20">
                                <select class="niceSelect w-100 bb form-control{{ $errors->has('class') ? ' is-invalid' : '' }}"
                                        id="select_class" name="class">
                                    <option data-display="@lang('student.select_class') *"
                                            value="">@lang('student.select_class') *
                                    </option>
                                    @foreach($classes as $class)
                                        <option value="{{$class->id}}" {{isset($age_group_id)? ($age_group_id == $class->id? 'selected': ''):'' }}>{{$class->age_group_name}}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('class'))
                                    <span class="invalid-feedback invalid-select" role="alert">
                                        <strong>{{ $errors->first('class') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-lg-4 col-md-4" id="select_section_div">
                                <select class="niceSelect w-100 bb form-control{{ $errors->has('section') ? ' is-invalid' : '' }}"
                                        id="select_section" name="section">
                                    <option data-display="@lang('student.select_section') *"
                                            value="">@lang('student.select_section') *
                                    </option>
                                    @isset($mgender_id)
                                        @foreach($sections as $section)
                                            <option value="{{$section->mgender_id}}" {{isset($mgender_id)? ($mgender_id == $section->mgender_id? 'selected': ''):'' }}>{{$section->sectionName->mgender_name}}</option>
                                        @endforeach
                                    @endisset
                                </select>
                                <div class="pull-right loader loader_style" id="select_section_loader">
                                    <img class="loader_img_style" src="{{asset('public/backEnd/img/demo_wait.gif')}}" alt="loader">
                                </div>
                                @if ($errors->has('section'))
                                    <span class="invalid-feedback invalid-select" role="alert">
                                        <strong>{{ $errors->first('section') }}</strong>
                                    </span>
                                @endif
                            </div>
                            
                            <div class="col-lg-4 col-md-4 mt-30-md">
                                <div class="row no-gutters input-right-icon">
                                    <div class="col">
                                        <div class="input-effect">
                                            <input class="primary-input date form-control{{ $errors->has('attendance_date') ? ' is-invalid' : '' }} {{isset($date)? 'read-only-input': ''}}"
                                                   id="startDate" type="text"
                                                   name="attendance_date" autocomplete="off"
                                                   value="{{isset($date)? $date: date('m/d/Y')}}">
                                            <label for="startDate">@lang('hr.attendance_date')*</label>
                                            <span class="focus-border"></span>

                                            @if ($errors->has('attendance_date'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('attendance_date') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <button class="" type="button">
                                            <i class="ti-calendar" id="start-date-icon"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endif
                            <div class="col-lg-12 mt-20 text-right">
                                <button type="submit" class="primary-btn small fix-gr-bg">
                                    <span class="ti-search pr-2"></span>
                                    @lang('common.search')
                                </button>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
            @isset($students)
                <div class="row mt-40">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-12 no-gutters">
                                <div class="main-title">
                                    @isset($search_info)
                                    <h3 class="mb-30">@lang('student.student_attendance') | <small>
                                        @if(moduleStatusCheck('University'))
                                        @lang('university::un.faculty_department')
                                            : {{ isset($unFaculty) ? $unFaculty->name .'('. (isset($unDepartment) ? $unDepartment->name:'').')':''}}, @lang('university::un.semester(label)')
                                            : {{ isset($unSemester) ? $unSemester->name .'('. (isset($unSemesterLabel) ? $unSemesterLabel->name : '') .')' :''}}, @lang('common.date')
                                        @else
                                        @lang('common.class')
                                            : {{$search_info['age_group_name']}}, @lang('common.section')
                                            : {{$search_info['mgender_name']}}, @lang('common.date')
                                        @endif
                                            : {{dateConvert($search_info['date'])}}</small></h3>
                                    @endisset
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 col-md-12 no-gutters">
                                @if($attendance_type != "" && $attendance_type == "H")
                                    <div class="alert alert-warning">@lang('student.attendance_already_submitted_as_holiday')</div>
                                @elseif($attendance_type != "" && $attendance_type != "H")
                                    <div class="alert alert-success">@lang('student.attendance_already_submitted')</div>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-20">
                            <div class="col-lg-6  col-md-6 no-gutters text-md-left mark-holiday ">
                                @if($attendance_type != "H")
                                    <form action="{{route('student-attendance-holiday')}}" method="POST">
                                        @csrf
                                        <input type="hidden" name="purpose" value="mark">
                                        <input type="hidden" name="age_group_id" value="{{$age_group_id}}">
                                        <input type="hidden" name="mgender_id" value="{{$mgender_id}}">
                                      
                                        <input type="hidden" name="attendance_date" value="{{$date}}">
                                        @if(moduleStatusCheck('University'))
                                        
                                        <input type="hidden" name="un_session_id" value="{{isset($unSession) ? $unSession->id:''}}">
                                        <input type="hidden" name="un_faculty_id" value="{{isset($unFaculty) ? $unFaculty->id:''}}">
                                        <input type="hidden" name="un_department_id" value="{{isset($unDepartment) ? $unDepartment->id:''}}">
                                        <input type="hidden" name="un_church_year_id" value="{{isset($unAcademic) ? $unAcademic->id:''}}">
                                        <input type="hidden" name="un_semester_id" value="{{isset($unSemester) ? $unSemester->id:''}}">
                                        <input type="hidden" name="un_mgender_id" value="{{isset($unSection) ? $unSection->id:''}}">
                                        @endif
                                        <button type="submit" class="primary-btn fix-gr-bg mb-20">
                                            @lang('student.mark_holiday')
                                        </button>
                                    </form>
                                @else
                                    <form action="{{route('student-attendance-holiday')}}" method="POST">
                                        @csrf
                                        <input type="hidden" name="purpose" value="unmark">
                                        <input type="hidden" name="age_group_id" value="{{$age_group_id}}">
                                        <input type="hidden" name="mgender_id" value="{{$mgender_id}}">
                                        <input type="hidden" name="attendance_date" value="{{$date}}">
                                        @if(moduleStatusCheck('University'))
                                        
                                        <input type="hidden" name="un_session_id" value="{{isset($unSession) ? $unSession->id:''}}">
                                        <input type="hidden" name="un_faculty_id" value="{{isset($unFaculty) ? $unFaculty->id:''}}">
                                        <input type="hidden" name="un_department_id" value="{{isset($unDepartment) ? $unDepartment->id:''}}">
                                        <input type="hidden" name="un_church_year_id" value="{{isset($unAcademic) ? $unAcademic->id:''}}">
                                        <input type="hidden" name="un_semester_id" value="{{isset($unSemester) ? $unSemester->id:''}}">
                                        <input type="hidden" name="un_semester_label_id" value="{{isset($unSemesterLabel) ? $unSemesterLabel->id:''}}">
                                        <input type="hidden" name="un_mgender_id" value="{{isset($unSection) ? $unSection->id:''}}">
                                        @endif
                                        <button type="submit" class="primary-btn fix-gr-bg mb-20">
                                            @lang('student.unmark_holiday')
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        {{ Form::open(['class' => 'form-horizontal', 'route'=>'student-attendance-store','files' => true, 'method' => 'POST', 'enctype' => 'multipart/form-data'])}}
                        <input type="hidden" name="date" class="attendance_date" value="{{isset($date)? $date: ''}}">
                        <div class="row">
                            <div class="col-lg-12">
                                <table class="display school-table school-table-style" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>@lang('student.registration_no')</th>
                                        <th>@lang('student.member_name')</th>
                                        <th>@lang('student.roll_number')</th>
                                        <th>@lang('student.attendance')</th>
                                        <th>@lang('common.note')</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($students as $student)
                                        
                                        <tr>
                                            <td>{{$student->studentDetail->registration_no}}
                                                <input type="hidden" name="attendance[{{$student->id}}]" value="{{$student->id}}">
                                                <input type="hidden" name="attendance[{{$student->id}}][student]" value="{{$student->member_id}}">
                                                <input type="hidden" name="attendance[{{$student->id}}][class]" value="{{$student->age_group_id}}">
                                                <input type="hidden" name="attendance[{{$student->id}}][section]" value="{{$student->mgender_id}}">
                                                @if(moduleStatusCheck('University'))
                                        
                                                <input type="hidden" name="attendance[{{$student->id}}][un_session_id]" value="{{$student->un_session_id}}">
                                                <input type="hidden" name="attendance[{{$student->id}}][un_faculty_id]" value="{{$student->un_faculty_id}}">
                                                <input type="hidden" name="attendance[{{$student->id}}][un_department_id]" value="{{$student->un_department_id}}">
                                                <input type="hidden" name="attendance[{{$student->id}}][un_church_year_id]" value="{{$student->un_church_year_id}}">
                                                <input type="hidden" name="attendance[{{$student->id}}][un_semester_id]" value="{{$student->un_semester_id}}">
                                                <input type="hidden" name="attendance[{{$student->id}}][un_semester_label_id]" value="{{$student->un_semester_label_id}}">
                                                <input type="hidden" name="attendance[{{$student->id}}][un_mgender_id]" value="{{$student->un_mgender_id}}">
                                                @endif
                                           
                                            </td>
                                            <td>{{$student->studentDetail->first_name.' '.$student->studentDetail->last_name}}</td>
                                            <td>{{$student->roll_no}}</td>
                                            <td>
                                                <div class="d-flex radio-btn-flex">
                                                    <div class="mr-20">
                                                        <input type="radio" name="attendance[{{$student->id}}][attendance_type]"
                                                               id="attendanceP{{$student->id}}" value="P"
                                                               class="common-radio attendanceP attendance_type" 
                                                                {{ $student->studentDetail->DateWiseAttendances !=null ? ($student->studentDetail->DateWiseAttendances->attendance_type == "P" ? 'checked' :'') : ($attendance_type != "" ? '' :'checked') }}>
                                                        <label for="attendanceP{{$student->id}}">@lang('student.present')</label>
                                                    </div>
                                                    <div class="mr-20">
                                                        <input type="radio" name="attendance[{{$student->id}}][attendance_type]"
                                                               id="attendanceL{{$student->id}}" value="L"
                                                               class="common-radio attendance_type" {{ $student->studentDetail->DateWiseAttendances !=null ? ($student->studentDetail->DateWiseAttendances->attendance_type == "L" ? 'checked' :''):''}}>
                                                        <label for="attendanceL{{$student->id}}">@lang('student.late')</label>
                                                    </div>
                                                    <div class="mr-20">
                                                        <input type="radio" name="attendance[{{$student->id}}][attendance_type]"
                                                               id="attendanceA{{$student->id}}" value="A"
                                                               class="common-radio attendance_type"  {{$student->studentDetail->DateWiseAttendances !=null ? ($student->studentDetail->DateWiseAttendances->attendance_type == "A" ? 'checked' :''):''}}>
                                                        <label for="attendanceA{{$student->id}}">@lang('student.absent')</label>
                                                    </div>
                                                    <div>
                                                        <input type="radio" name="attendance[{{$student->id}}][attendance_type]"
                                                               id="attendanceH{{$student->id}}" value="F"
                                                               class="common-radio attendance_type"  {{$student->studentDetail->DateWiseAttendances !=null ? ($student->studentDetail->DateWiseAttendances->attendance_type == "F" ? 'checked' :'') : ''}}>
                                                        <label for="attendanceH{{$student->id}}">@lang('student.half_day')</label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-effect">
                                                    <textarea class="primary-input form-control note_{{$student->id}}" cols="0" rows="2" name="attendance[{{$student->id}}][note]" id="">{{$student->studentDetail->DateWiseAttendances !=null ? $student->studentDetail->DateWiseAttendances->notes :''}}</textarea>
                                                    <label>@lang('student.add_note_here')</label>
                                                    <span class="focus-border textarea"></span>
                                                    <span class="invalid-feedback">
                                                        <strong>@lang('common.error')</strong>
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td class="text-center">
                                            <button type="submit" class="primary-btn mr-40 fix-gr-bg nowrap submit">
                                                @lang('student.save_attendance')
                                            </button>
                                        </td>
                                        <td></td>
                                    </tr>
                                    </tbody>
                                </table>
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                </div>
           @endisset
        </div>
    </section>
@endsection
