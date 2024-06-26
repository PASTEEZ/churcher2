@extends('backEnd.master')
@section('title') 
@lang('reports.student_history')
@endsection

@section('mainContent')
<input type="text" hidden value="{{ @$clas->age_group_name }}" id="cls">
<input type="text" hidden value="{{ @$clas->mgender_name->sectionName->mgender_name }}" id="sec">
<section class="sms-breadcrumb mb-40 up_breadcrumb white-box">
    <div class="container-fluid">
        <div class="row justify-content-between">
            <h1>@lang('reports.student_history')</h1>
            <div class="bc-pages">
                <a href="{{route('dashboard')}}">@lang('common.dashboard')</a>
                <a href="#">@lang('reports.reports')</a>
                <a href="#">@lang('reports.student_history')</a>
            </div>
        </div>
    </div>
</section>
<section class="admin-visitor-area up_admin_visitor">
    <div class="container-fluid p-0">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="main-title">
                        <h3 class="mb-30">@lang('common.select_criteria') </h3>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                  
                    <div class="white-box">
                        {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'student_history_search', 'method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'search_student']) }}
                            <div class="row">
                                <input type="hidden" name="url" id="url" value="{{URL::to('/')}}">
                                <div class="col-lg-6 mt-30-md col-md-6">
                                    <select class="w-100 niceSelect bb form-control {{ $errors->has('class') ? ' is-invalid' : '' }}" name="class">
                                        <option data-display="@lang('common.select_class') *" value="">@lang('common.select_class') *</option>
                                        @foreach($classes as $class)
                                        <option value="{{$class->id}}"  {{isset($age_group_id)? ($age_group_id == $class->id? 'selected': ''):''}}>{{$class->age_group_name}}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('class'))
                                    <span class="invalid-feedback invalid-select" role="alert">
                                        <strong>{{ $errors->first('class') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="col-lg-6 mt-30-md col-md-6">
                                    <select class="w-100 niceSelect bb form-control{{ $errors->has('current_section') ? ' is-invalid' : '' }}" name="admission_year">
                                        <option data-display="@lang('reports.select_admission_year')" value="">@lang('reports.select_admission_year')</option>
                                        @foreach($years as $key => $value)
                                        <option value="{{$key}}" {{isset($year)? ($year == $key? 'selected': ''):''}}>{{$key}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                </div>
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
            
          @if(isset($students))
            <div class="row mt-40">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-6 no-gutters">
                            <div class="main-title">
                                <h3 class="mb-0">@lang('reports.student_report')</h3>
                            </div>
                        </div>
                    </div>

                

                    <!-- <div class="d-flex justify-content-between mb-20"> -->
                        <!-- <button type="submit" class="primary-btn fix-gr-bg mr-20" onclick="javascript: form.action='{{url('student-attendance-holiday')}}'">
                            <span class="ti-hand-point-right pr"></span>
                            mark as holiday
                        </button> -->

                        
                    <!-- </div> -->
                    <div class="row">
                        <div class="col-lg-12">
                            <table id="table_ids" class="display school-table" cellspacing="0" width="100%">
                                <thead>
                                    
                                    <tr>
                                        <th>@lang('student.registration_no')</th>
                                        <th>@lang('common.name')</th>
                                        <th>@lang('student.admission_date')</th>
                                        <th>@lang('reports.class_start_end')</th>
                                        <th>@lang('reports.session_start_end')</th>
                                        <th>@lang('common.mobile')</th>
                                        <th>@lang('student.guardian_name')</th>
                                        <th>@lang('student.guardian_phone')</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($students as $student)
                                    <tr>
                                        <td>{{$student->registration_no}}</td>
                                        <td>{{$student->first_name.' '.$student->last_name}}</td>
                                        <td  data-sort="{{strtotime($student->admission_date)}}">
                                        {{$student->admission_date != ""? dateConvert($student->admission_date):''}}

                                        </td>
                                        

                                        <td>{{$student->recordClass !="" ?$student->recordClass->class->age_group_name : ''}}</td>

                                        <td>{{$student->sessions}}</td>
                                        <td>{{$student->mobile}}</td>
                                        <td>{{$student->parents !=""?$student->parents->guardians_name:""}}</td>
                                        <td>{{$student->parents !=""?$student->parents->guardians_mobile:""}}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
         @endif

    </div>
</section>


@endsection
