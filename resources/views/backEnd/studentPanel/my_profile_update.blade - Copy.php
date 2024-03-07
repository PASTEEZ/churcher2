@extends('backEnd.master')
@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/backEnd/') }}/css/croppie.css">
@endsection
@section('title')
    @lang('student.profile_update')
@endsection

@section('mainContent')
    <section class="sms-breadcrumb up_breadcrumb mb-40 white-box">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1>@lang('student.profile_update') </h1>
                <div class="bc-pages">
                    <a href="{{ route('dashboard') }}">@lang('common.dashboard')</a>
                    <a href="{{ route('student_list') }}">@lang('common.student_list')</a>
                    <a href="#">@lang('student.profile_update') </a>
                </div>
            </div>
        </div>
    </section>

    <section class="admin-visitor-area up_st_admin_visitor">
        <div class="container-fluid p-0">
            <div class="row mb-30">
                <div class="col-lg-6">
                    <div class="main-title">
                        <h3>@lang('student.profile_update') </h3>
                    </div>
                </div>
            </div>
            {{             Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'my-profile-update', 'method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'student_form']) }}
            <div class="row">
                <div class="col-lg-12">
                    <div class="white-box">
                        <div class="">
                            <div class="row mb-4">
                                <div class="col-lg-12 text-center">

                                    @if ($errors->any())
                                        @foreach ($errors->all() as $error)
                                            @if ($error == 'The email address has already been taken.')
                                                <div class="error text-danger ">
                                                    {{ 'The email address has already been taken, You can find out in student list or disabled student list' }}
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif

                                    @if ($errors->any())
                                        <div class="error text-danger ">{{ 'Something went wrong, please try again' }}
                                        </div>
                                    @endif
                                </div>
                                <div class="col-lg-12">
                                    <div class="main-title">
                                        <h4 class="stu-sub-head">@lang('student.personal_info')</h4>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="url" id="url" value="{{ URL::to('/') }}">
                            <input type="hidden" name="id" id="id" value="{{ $student->id }}">

                            <div class="row mb-20">
                                @if(requiredOrPermission('region'))
                                <div class="col-lg-2">
                                    <div class="input-effect sm2_mb_20 md_mb_20">

                                        <select
                                            class="niceSelect w-100 bb form-control{{ $errors->has('region') ? ' is-invalid' : '' }}"
                                            name="region">
                                            <option data-display="@lang('student.region')" value="">
                                                @lang('student.region')</option>
                                            @foreach ($blood_groups as $region)
                                                @if (isset($student->bloodgroup_id))
                                                    <option value="{{ $region->id }}"
                                                        {{ $region->id == $student->bloodgroup_id ? 'selected' : '' }}>
                                                        {{ $region->base_setup_name }}</option>
                                                @else
                                                    <option value="{{ $region->id }}">
                                                        {{ $region->base_setup_name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <span class="focus-border"></span>
                                        @if ($errors->has('region'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('region') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @endif
                                @if(requiredOrPermission('marital_status'))
                                <div class="col-lg-2">
                                    <div class="input-effect sm2_mb_20 md_mb_20">

                                        <select
                                            class="niceSelect w-100 bb form-control{{ $errors->has('marital_status') ? ' is-invalid' : '' }}"
                                            name="marital_status">
                                            <option data-display="@lang('student.marital_status')" value="">
                                                @lang('student.marital_status')</option>
                                            @foreach ($religions as $marital_status)
                                                <option value="{{ $marital_status->id }}"
                                                    {{ $student->religion_id != '' ? ($student->religion_id == $marital_status->id ? 'selected' : '') : '' }}>
                                                    {{ $marital_status->base_setup_name }}</option>
                                                }
                                            @endforeach

                                        </select>
                                        <span class="focus-border"></span>
                                        @if ($errors->has('marital_status'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('marital_status') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @endif
                                @if(requiredOrPermission('phone_number'))

                                <div class="col-lg-3">
                                    <div class="input-effect sm2_mb_20 md_mb_20">
                                        <input
                                            class="primary-input form-control{{ $errors->has('phone_number') ? ' is-invalid' : '' }}"
                                            type="text" name="phone_number" value="{{ $student->mobile }}">
                                        <label>@lang('common.phone_number')</label>
                                        <span class="focus-border"></span>
                                        @if ($errors->has('phone_number'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('phone_number') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @endif
                                @if(requiredOrPermission('height'))
                                <div class="col-lg-2">
                                    <div class="input-effect sm2_mb_20 md_mb_20">
                                        <input class="primary-input" type="text" name="height"
                                            value="{{ $student->height }}">
                                        <label>@lang('student.height') (@lang('reports.in')) <span></span> </label>
                                        <span class="focus-border"></span>
                                    </div>
                                </div>
                                @endif
                                @if(requiredOrPermission('weight'))
                                <div class="col-lg-2">
                                    <div class="input-effect sm2_mb_20 md_mb_20">
                                        <input class="primary-input" type="text" name="weight"
                                            value="{{ $student->weight }}">
                                        <label>@lang('student.Weight') (@lang('student.kg')) <span></span> </label>
                                        <span class="focus-border"></span>
                                    </div>
                                </div>
                                @endif
                                @if(requiredOrPermission('registration_date'))
                                <div class="col-lg-2">
                                    <div class="no-gutters input-right-icon">
                                        <div class="col">
                                            <div class="input-effect">
                                                <input class="primary-input date" id="endDate" type="text"
                                                    name="registration_date"
                                                    value="{{ $student->registration_date != '' ? date('m/d/Y', strtotime($student->registration_date)) : date('m/d/Y') }}"
                                                    autocomplete="off">
                                                <label>@lang('student.registration_date')</label>
                                                <span class="focus-border"></span>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <button class="" type="button">
                                                <i class="ti-calendar" id="end-date-icon"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if(requiredOrPermission('student_category_id'))

                                <div class="col-lg-4">
                                    <div class="input-effect">
                                        <div class="input-effect">
                                            <select
                                                class="niceSelect w-100 bb form-control{{ $errors->has('student_category_id') ? ' is-invalid' : '' }}"
                                                name="student_category_id">
                                                <option data-display="@lang('student.category')" value="">
                                                    @lang('student.category')</option>
                                                @foreach ($categories as $category)
                                                    @if (isset($student->student_category_id))
                                                        <option value="{{ $category->id }}"
                                                            {{ $student->student_category_id == $category->id ? 'selected' : '' }}>
                                                            {{ $category->category_name }}</option>
                                                    @else
                                                        <option value="{{ $category->id }}">{{ $category->category_name }}
                                                        </option>
                                                    @endif
                                                @endforeach

                                            </select>
                                            <span class="focus-border"></span>
                                            @if ($errors->has('student_category_id'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('student_category_id') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if(requiredOrPermission('photo'))
                                <div class="col-lg-2">
                                    <div class="input-effect">
                                        <div class="input-effect">
                                            <select
                                                class="niceSelect w-100 bb form-control{{ $errors->has('student_group_id') ? ' is-invalid' : '' }}"
                                                name="student_group_id">
                                                <option data-display="@lang('common.group')" value="">@lang('common.group')
                                                </option>
                                                @foreach ($groups as $group)
                                                    @if (isset($student->student_group_id))
                                                        <option value="{{ $group->id }}"
                                                            {{ $student->student_group_id == $group->id ? 'selected' : '' }}>
                                                            {{ $group->group }}</option>
                                                    @else
                                                        <option value="{{ $group->id }}">{{ $group->group }}</option>
                                                    @endif
                                                @endforeach

                                            </select>
                                            <span class="focus-border"></span>
                                            @if ($errors->has('student_group_id'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('student_group_id') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>




                                    <div class="col-lg-6">
                                        <div class="row no-gutters input-right-icon">
                                            <div class="col">
                                                <div class="input-effect">
                                                    <input class="primary-input" type="text" id="placeholderPhoto"
                                                        placeholder="{{ $student->student_photo != '' ? getFilePath3($student->student_photo) : 'Student Photo' }}"
                                                        disabled>
                                                    <span class="focus-border"></span>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <button class="primary-btn-small-input" type="button">
                                                    <label class="primary-btn small fix-gr-bg"
                                                        for="photo">@lang('common.browse')</label>
                                                    <input type="file" class="d-none" name="photo" id="photo">
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                @endif
                           

                                <div class="col-lg-12">
                                    <div class="main-title">
                                        <h4 class="stu-sub-head">@lang('student.student_address_info')</h4>
                                    </div>
                                </div>
                          
                                @if(requiredOrPermission('current_address'))
                                <div class="col-lg-6">

                                    <div class="input-effect mt-20">
                                        <textarea
                                            class="primary-input form-control{{ $errors->has('current_address') ? ' is-invalid' : '' }}"
                                            cols="0" rows="3" name="current_address"
                                            id="current_address">{{ $student->current_address }}</textarea>
                                        <label>@lang('student.current_address') <span></span> </label>
                                        <span class="focus-border textarea"></span>
                                    </div>
                                </div>
                                @endif
                           
                                <div class="col-lg-12 mt-20">
                                    <div class="main-title">
                                        <h4 class="stu-sub-head">@lang('student.Other_info')</h4>
                                    </div>
                                </div>
                               
                                @if(requiredOrPermission('national_id_number'))
                                <div class="col-lg-3">
                                    <div class="input-effect">
                                        <input
                                            class="primary-input form-control{{ $errors->has('national_id_number') ? ' is-invalid' : '' }}"
                                            type="text" name="national_id_number" value="{{ $student->national_id_no }}">
                                        <label>@lang('student.national_iD_number') <span></span></label>
                                        <span class="focus-border"></span>
                                        @if ($errors->has('national_id_number'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('national_id_number') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @endif
                                @if(requiredOrPermission('local_id_number'))
                                <div class="col-lg-3">
                                    <div class="input-effect">
                                        <input class="primary-input form-control" type="text" name="local_id_number"
                                            value="{{ $student->communicant }}">
                                        <label>@lang('student.local_Id_Number') <span></span> </label>
                                        <span class="focus-border"></span>
                                    </div>
                                </div>
                                @endif
                                @if(requiredOrPermission('bank_account_number'))
                                <div class="col-lg-3">
                                    <div class="input-effect">
                                        <input class="primary-input form-control" type="text" name="bank_account_number"
                                            value="{{ $student->day_born }}">
                                        <label>@lang('student.bank_account_number') <span></span> </label>
                                        <span class="focus-border"></span>
                                    </div>
                                </div>
                                @endif
                                @if(requiredOrPermission('employer_name'))
                                <div class="col-lg-3">
                                    <div class="input-effect">
                                        <input class="primary-input form-control" type="text" name="employer_name"
                                            value="{{ $student->employer_name }}">
                                        <label>@lang('student.employer_name') <span></span> </label>
                                        <span class="focus-border"></span>
                                    </div>
                                </div>
                                @endif
                                @if(requiredOrPermission('previous_school_details'))
                                <div class="col-lg-6">
                                    <div class="input-effect">
                                        <textarea class="primary-input form-control" cols="0" rows="4"
                                            name="previous_school_details">{{ $student->previous_school_details }}</textarea>
                                        <label>@lang('student.previous_school_details')</label>
                                        <span class="focus-border textarea"></span>
                                    </div>
                                </div>
                                @endif
                                @if(requiredOrPermission('additional_notes'))
                                <div class="col-lg-3">
                                    <div class="input-effect">
                                        <textarea class="primary-input form-control" cols="0" rows="4"
                                            name="additional_notes">{{ $student->aditional_notes }}</textarea>
                                        <label>@lang('student.additional_notes')</label>
                                        <span class="focus-border textarea"></span>
                                    </div>
                                </div>
                                @endif
                                @if(requiredOrPermission('baptism_status'))
                                <div class="col-lg-3">
                                    <div class="input-effect mt-50">
                                        <input class="primary-input form-control" type="text" name="baptism_status"
                                            value="{{ old('baptism_status') }}{{ $student->baptism_status }}">
                                        <label>@lang('student.IFSC_Code')</label>
                                        <span class="focus-border"></span>
                                    </div>
                                </div>
                                @endif
                               
                                <div class="col-lg-12">
                                    <div class="main-title">
                                        <h4 class="stu-sub-head">@lang('student.document_info')</h4>
                                    </div>
                                </div>
                              
                                @if(requiredOrPermission('document_file_1'))
                                <div class="col-lg-3">
                                    <div class="input-effect">
                                        <input class="primary-input" type="text" name="group_1"
                                            value="{{ $student->group_1 }}">
                                        <label>@lang('student.document_01_title')</label>
                                        <span class="focus-border"></span>
                                    </div>
                                </div>
                                @endif
                                @if(requiredOrPermission('document_file_2'))
                                <div class="col-lg-3">
                                    <div class="input-effect">
                                        <input class="primary-input" type="text" name="group_2"
                                            value="{{ $student->group_2 }}">
                                        <label>@lang('student.document_02_title')</label>
                                        <span class="focus-border"></span>
                                    </div>
                                </div>
                                @endif
                                @if(requiredOrPermission('document_file_3'))
                                <div class="col-lg-3">
                                    <div class="input-effect">
                                        <input class="primary-input" type="text" name="group_3"
                                            value="{{ $student->group_3 }}">
                                        <label>@lang('student.document_03_title')</label>
                                        <span class="focus-border"></span>
                                    </div>
                                </div>
                                @endif
                                @if(requiredOrPermission('document_file_4'))
                                <div class="col-lg-3">
                                    <div class="input-effect">
                                        <input class="primary-input" type="text" name="group_4"
                                            value="{{ $student->group_4 }}">
                                        <label>@lang('student.document_04_title')</label>
                                        <span class="focus-border"></span>
                                    </div>
                                </div>
                                @endif
                                @if(requiredOrPermission('document_file_1'))
                                <div class="col-lg-3">
                                    <div class="row no-gutters input-right-icon">
                                        <div class="col">
                                            <div class="input-effect">
                                                <input class="primary-input" type="text" id="placeholderFileOneName"
                                                    placeholder="{{ $student->document_file_1 != '' ? showDocument($student->document_file_1) : '01' }}"
                                                    disabled>
                                                <span class="focus-border"></span>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <button class="primary-btn-small-input" type="button">
                                                <label class="primary-btn small fix-gr-bg"
                                                    for="document_file_1">@lang('common.browse')</label>
                                                <input type="file" class="d-none" name="document_file_1"
                                                    id="document_file_1">
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if(requiredOrPermission('document_file_2'))
                                <div class="col-lg-3">
                                    <div class="row no-gutters input-right-icon">
                                        <div class="col">
                                            <div class="input-effect">
                                                <input class="primary-input" type="text" id="placeholderFileTwoName"
                                                    placeholder="{{ isset($student->document_file_2) && $student->document_file_2 != '' ? showDocument($student->document_file_2) : '02' }}"
                                                    disabled>
                                                <span class="focus-border"></span>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <button class="primary-btn-small-input" type="button">
                                                <label class="primary-btn small fix-gr-bg"
                                                    for="document_file_2">@lang('common.browse')</label>
                                                <input type="file" class="d-none" name="document_file_2"
                                                    id="document_file_2">
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if(requiredOrPermission('document_file_3'))
                                <div class="col-lg-3">
                                    <div class="row no-gutters input-right-icon">
                                        <div class="col">
                                            <div class="input-effect">
                                                <input class="primary-input" type="text" id="placeholderFileThreeName"
                                                    placeholder="{{ isset($student->document_file_3) && $student->document_file_3 != '' ? showDocument($student->document_file_3) : '03' }}"
                                                    disabled>
                                                <span class="focus-border"></span>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <button class="primary-btn-small-input" type="button">
                                                <label class="primary-btn small fix-gr-bg"
                                                    for="document_file_3">@lang('common.browse')</label>
                                                <input type="file" class="d-none" name="document_file_3"
                                                    id="document_file_3">
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if(requiredOrPermission('document_file_4'))
                                <div class="col-lg-3">
                                    <div class="row no-gutters input-right-icon">
                                        <div class="col">
                                            <div class="input-effect">
                                                <input class="primary-input" type="text" id="placeholderFileFourName"
                                                    placeholder="{{ isset($student->document_file_4) && $student->document_file_4 != '' ? showDocument($student->document_file_4) : '04' }}"
                                                    disabled>
                                                <span class="focus-border"></span>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <button class="primary-btn-small-input" type="button">
                                                <label class="primary-btn small fix-gr-bg"
                                                    for="document_file_4">@lang('common.browse')</label>
                                                <input type="file" class="d-none" name="document_file_4"
                                                    id="document_file_4">
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            
                                <div class="col-lg-12 text-center mt-40">
                                    <button class="primary-btn fix-gr-bg">
                                        <span class="ti-check"></span>
                                        @lang('student.update_student')
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </section>


    <div class="modal fade admin-query" id="removeSiblingModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">@lang('student.remove')</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="text-center">
                        <h4>@lang('student.are_you')</h4>
                    </div>

                    <div class="mt-40 d-flex justify-content-between">
                        <button type="button" class="primary-btn tr-bg"
                            data-dismiss="modal">@lang('common.cancel')</button>
                        <button type="button" class="primary-btn fix-gr-bg" data-dismiss="modal"
                            id="yesRemoveSibling">@lang('common.delete')</button>

                    </div>
                </div>

            </div>
        </div>
    </div>


    {{-- student photo --}}
    <input type="hidden" id="STurl" value="{{ route('student_update_pic', $student->id) }}">
    <div class="modal" id="LogoPic">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Crop Image And Upload</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <!-- Modal body -->
                <div class="modal-body">
                    <div id="resize"></div>
                    <button class="btn rotate float-lef" data-deg="90">
                        <i class="ti-back-right"></i></button>
                    <button class="btn rotate float-right" data-deg="-90">
                        <i class="ti-back-left"></i></button>
                    <hr>
                    <a href="javascript:;" class="primary-btn fix-gr-bg pull-right" id="upload_logo">Crop</a>
                </div>
            </div>
        </div>
    </div>
    {{-- end student photo --}}



@endsection
@section('script')
    <script src="{{ asset('public/backEnd/') }}/js/croppie.js"></script>
    <script src="{{ asset('public/backEnd/') }}/js/st_addmision.js"></script>
@endsection
