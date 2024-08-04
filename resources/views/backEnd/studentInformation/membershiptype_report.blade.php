@extends('backEnd.master')
    @section('title')
    MEMBERSHIP REPORT
    @endsection
@section('mainContent')
<input type="text" hidden value="{{ @$clas->age_group_name }}" id="cls">
<input type="text" hidden value="{{ @$clas->mgender_name->sectionName->mgender_name }}" id="sec">
<section class="sms-breadcrumb mb-40 up_breadcrumb white-box">
    <div class="container-fluid">
        <div class="row justify-content-between">
            <h1>MEMBERSHIP REPORT</h1>
            <div class="bc-pages">
                <a href="{{route('dashboard')}}">@lang('common.dashboard')</a>
                <a href="#">@lang('reports.reports')</a>
                <a href="#">@lang('reports.guardian_report')</a>
            </div>
        </div>
    </div>
</section>
<section class="admin-visitor-area up_admin_visitor">
    <div class="container-fluid p-0">
            <div class="row">
                <div class="col-lg-8 col-md-6">
                    <div class="main-title">
                        <h3 class="mb-30"></h3>
                    </div>
                </div>
            </div>
            {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'membershiptype_report_search', 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
            <div class="row">
                <div class="col-lg-12">
                <div class="white-box">
                    <div class="row">
                                <input type="hidden" name="url" id="url" value="{{URL::to('/')}}">
                               
                                <div class="col-lg-6 mt-30-md">
                                    <select class="niceSelect w-100 bb form-control {{ $errors->has('class') ? ' is-invalid' : '' }}" id="select_class" name="class">
                                        <option data-display="@lang('common.select_class')*" value="">@lang('common.select_class') *</option>
                                        @foreach($memberstypes as $memberstype)
                                        <option value="{{$memberstype->id}}" > {{$memberstype->membertype_name}}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('class'))
                                    <span class="invalid-feedback invalid-select" role="alert">
                                        <strong>{{ $errors->first('class') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                 

 

                                <div class="col-lg-6 mt-30-md" id="select_section_div">
                                    <select class="niceSelect w-100 bb form-control{{ $errors->has('section') ? ' is-invalid' : '' }}" id="select_section" name="section">
                                        <option data-display="@lang('common.select_section')" value="">@lang('common.select_section')</option>
                                        @if(isset($age_group_id))
                                            @foreach ($gender->classSection as $gender)
                                            <option value="{{ $gender->sectionName->id }}" {{ old('section')==$gender->sectionName->id ? 'selected' : '' }} >
                                                {{ $gender->gender_name }}</option>
                                            @endforeach
                                         @endif
                                    </select>
                                    <div class="pull-right loader loader_style" id="select_section_loader">
                                        <img class="loader_img_style" src="{{asset('public/backEnd/img/demo_wait.gif')}}" alt="loader">
                                    </div>
                                     
                                </div>



                                
                                <div class="col-lg-12 mt-20 text-right">
                                    <button type="submit" class="primary-btn small fix-gr-bg">
                                        <span class="ti-search pr-2"></span>
                                        @lang('common.search')
                                    </button>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
            {{ Form::close() }}
            @if(isset($student_records))
            <div class="row mt-40"> 
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-4 no-gutters">
                            <div class="main-title">
                                <h3 class="mb-0">@lang('reports.guardian_report')</h3>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <table id="table_ids" class="display school-table" cellspacing="0" width="100%">
                                <thead>
                                   
                                    <tr>
                                         
                                        <th>@lang('common.class')</th>
                                        <th>@lang('common.section')</th>
 

                                        <th>@lang('student.registration_no')</th>
                                        <th>@lang('common.name')</th>
                                        <th>@lang('common.mobile')</th>
                                        <th>@lang('student.guardian_name')</th>
                                        <th>@lang('reports.relation_with_guardian')</th>
                                        <th>@lang('student.guardian_phone') </th>
                                        <th>@lang('student.father_name') </th>
                                        <th>@lang('student.father_phone') </th>
                                        <th>@lang('student.mother_name') </th>
                                        <th>@lang('student.mother_phone') </th>
                                    </tr>
                                </thead>

                                <tbody>
                                 
                                    @foreach($student_records as $record)
                                    <tr>
                                
                                        <td>{{@$record->class->age_group_name}}</td>
                                        <td> {{@$record->section->mgender_name}}</td>
                                       
                                        <td>{{@$record->student->registration_no}}</td>
                                        <td>{{@$record->student->full_name}}</td>
                                        <td>{{@$record->student->mobile}}</td>
                                        <td>{{@$record->student->parents!=""?@$record->student->parents->guardians_name:""}}</td>
                                        <td>{{@$record->student->parents!=""?@$record->student->parents->guardians_relation:""}}</td>
                                        <td>{{@$record->student->parents!=""?@$record->student->parents->guardians_mobile:""}}</td>
                                        <td>{{@$record->student->parents!=""?@$record->student->parents->fathers_name:""}}</td>
                                        <td>{{@$record->student->parents!=""?@$record->student->parents->fathers_mobile:""}}</td>
                                        <td>{{@$record->student->parents!=""?@$record->student->parents->mothers_name:""}}</td>
                                        <td>{{@$record->student->parents!=""?@$record->student->parents->mothers_mobile:""}}</td>
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
