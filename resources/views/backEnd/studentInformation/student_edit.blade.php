@extends('backEnd.master')
@section('title') 
@lang('student.student_edit')
@endsection

@section('css')
<link rel="stylesheet" type="text/css" href="{{asset('public/backEnd/')}}/css/croppie.css">
@endsection
@section('mainContent')

<section class="sms-breadcrumb up_breadcrumb mb-40 white-box">
    <div class="container-fluid">
        <div class="row justify-content-between">
            <h1>@lang('student.student_edit')</h1>
            <div class="bc-pages">
                <a href="{{route('dashboard')}}">@lang('common.dashboard')</a>
                <a href="{{route('student_list')}}">@lang('common.student_list')</a>
                <a href="#">@lang('student.student_edit')</a>
            </div>
        </div>
    </div>
</section>

<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid p-0">
        <div class="row mb-30">
            <div class="col-lg-6">
                <div class="main-title">
                    <h3>@lang('student.student_edit')</h3>
                </div>
            </div>
        </div>
        {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'student_update',
                        'method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'student_form']) }}
        <div class="row">
            <div class="col-lg-12">
                @if(session()->has('message-success'))
                  <div class="alert alert-success">
                      {{ session()->get('message-success') }}
                  </div>
                @elseif(session()->has('message-danger'))
                  <div class="alert alert-danger">
                      {{ session()->get('message-danger') }}
                  </div>
                @endif
                <div class="white-box">
                    <div class="">
                        <div class="row mb-4">
                            <div class="col-lg-12 text-center">

                                @if($errors->any())
                                    @foreach ($errors->all() as $error)
                                    @if($error == "The email address has already been taken.")
                                        <div class="error text-danger ">
                                            {{ 'The email address has already been taken, You can find out in student list or disabled student list' }}
                                        </div>
                                    @endif 
                                    @endforeach
                                @endif

                                @if ($errors->any())
                                     <div class="error text-danger ">{{ 'Something went wrong, please try again' }}</div>
                                @endif
                            </div>
                            <div class="col-lg-12">
                                <div class="main-title">
                                    <h4 class="stu-sub-head">@lang('student.personal_info')</h4>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="url" id="url" value="{{URL::to('/')}}"> 
                        <input type="hidden" name="id" id="id" value="{{$student->id}}">

                      








                        <div class="row mb-40 mt-30">
                            <div class="col-lg-2">
                                <div class="input-effect sm2_mb_20 md_mb_20">
                                    <select class="niceSelect w-100 bb form-control{{ $errors->has('session') ? ' is-invalid' : '' }}" name="session" id="church_year">
                                        <option data-display="@lang('common.church_year') @if(is_required('session')==true) * @endif" value="">@lang('common.church_year') @if(is_required('session')==true) * @endif</option>
                                        @foreach($sessions as $session)
                                        <option value="{{$session->id}}" {{old('session', getAcademicId()) == $session->id? 'selected': ''}}>{{$session->year}}[{{$session->title}}]</option>
                                        @endforeach
                                    </select>
                                    <span class="focus-border"></span>
                                    @if ($errors->has('session'))
                                    <span class="invalid-feedback invalid-select" role="alert">
                                        <strong>{{ $errors->first('session') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            @php
                                $classes = DB::table('sm_classes')->where('church_year_id', '=', old('session', getAcademicId()))
                                ->get();
                            @endphp
                        
                           
                           
                            @if(!empty(old('class')))
                            @php
                                $old_sections = DB::table('sm_class_sections')->where('age_group_id', '=', old('class'))
                                ->join('sm_sections','sm_class_sections.mgender_id','=','sm_sections.id')
                                ->get();
                            @endphp
                     
                            @else

                         
                            @endif

                            @if(is_show('admission_number'))
                            <div class="col-lg-2">
                                <div class="input-effect">
                                        <input class="primary-input form-control{{ $errors->has('admission_number') ? ' is-invalid' : '' }}" type="text" name="admission_number" value="{{$student->registration_no}}" onkeyup="GetAdminUpdate(this.value,{{$student->id}})">
                             
                     
                                   <label>@lang('student.admission_number') @if(is_required('admission_number')==true) * @endif</label>
                                    <span class="focus-border"></span>
                                    <span class="invalid-feedback" id="Admission_Number" role="alert">
                                    </span>
                                    @if ($errors->has('admission_number'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('admission_number') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            @endif

                            @if(is_show('admission_date'))
                            <div class="col-lg-2">
                                <div class="no-gutters input-right-icon">
                                    <div class="col">
                                        <div class="input-effect sm2_mb_20 md_mb_20">
                                            <input class="primary-input date" id="endDate" type="text" name="admission_date" value="{{$student->admission_date != ""? date('m/d/Y', strtotime($student->admission_date)): date('m/d/Y')}}" autocomplete="off">
                                            <label>@lang('student.admission_date')</label>
                                            <span class="focus-border">  @if(is_required('admission_date')==true) <span> *</span> @endif</span>
                                            @if ($errors->has('admission_date'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('admission_date') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <button class="" type="button">
                                            <i class="ti-calendar" id="admission-date-icon"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endif 

                        </div>
                        
                  
                        <div class="col-lg-1">
                            <div class="input-effect sm2_mb_20 md_mb_20">
                                <div class="input-effect sm2_mb_20 md_mb_20">
                                    <select class="niceSelect w-100 bb form-control{{ $errors->has('student_category_id') ? ' is-invalid' : '' }}" name="student_category_id">
                                        <option data-display="@lang('student.category') @if(is_required('student_category_id')==true) * @endif" value="">@lang('student.category') @if(is_required('student_category_id')==true) <span> *</span> @endif</option>
                                        @foreach($categories as $category)
                                        @if(isset($student->student_category_id))
                                        <option value="{{$category->id}}" {{$student->student_category_id == $category->id? 'selected': ''}}>{{$category->category_name}}</option>
                                        @else
                                        <option value="{{$category->id}}">{{$category->category_name}}</option>
                                        @endif
                                        @endforeach

                                    </select>
                                <span class="focus-border"></span>
                                @if ($errors->has('student_category_id'))
                                <span class="invalid-feedback invalid-select" role="alert">
                                    <strong>{{ $errors->first('student_category_id') }}</strong>
                                </span>
                                @endif
                            </div>
                            </div>
                        </div>
                      
                        <div class="row mb-40">
                            @if(is_show('first_name'))
                                <div class="col-lg-3">
                                    <div class="input-effect sm2_mb_20 md_mb_20">
                                        <input class="primary-input form-control{{ $errors->has('first_name') ? ' is-invalid' : '' }}" type="text" name="first_name"  value="{{$student->first_name}}">
                                        <label>@lang('student.first_name')  @if(is_required('first_name')==true) <span> *</span> @endif </label>
                                        <span class="focus-border"></span>
                                        @if ($errors->has('first_name'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('first_name') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                          
                            <div class="col-lg-3">
                                <div class="input-effect sm2_mb_20 md_mb_20">
                                    <input class="primary-input form-control{{ $errors->has('middle_name') ? ' is-invalid' : '' }}" type="text" name="middle_name"  value="{{$student->middle_name}}">
                                    <label>@lang('student.middle_name')  @if(is_required('middle_name')==true) <span> *</span> @endif </label>
                                    <span class="focus-border"></span>
                                    @if ($errors->has('middle_name'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('middle_name') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                 
                            @if(is_show('last_name'))
                                <div class="col-lg-3">
                                    <div class="input-effect sm2_mb_20 md_mb_20">
                                        <input class="primary-input form-control{{ $errors->has('last_name') ? ' is-invalid' : '' }}" type="text" name="last_name"  value="{{$student->last_name}}">
                                        <label>@lang('student.last_name')  @if(is_required('last_name')==true) <span> *</span> @endif</label>
                                        <span class="focus-border"></span>
                                        @if ($errors->has('last_name'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('last_name') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                           
                            @if(is_show('date_of_birth'))
                            <div class="col-lg-3">
                                <div class="no-gutters input-right-icon">
                                    <div class="col">
                                        <div class="input-effect sm2_mb_20 md_mb_20">
                                            <input class="primary-input date form-control{{ $errors->has('date_of_birth') ? ' is-invalid' : '' }}" id="startDate" type="text" name="date_of_birth" value="{{date('m/d/Y', strtotime($student->date_of_birth))}}" autocomplete="off">
                                          
                                                <label>@lang('common.date_of_birth')  @if(is_required('date_of_birth')==true) <span> *</span> @endif</label>
                                               
                                                <span class="focus-border"></span>
                                            @if ($errors->has('date_of_birth'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('date_of_birth') }}</strong>
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
                        </div>
                        <div class="row mb-40">
                          
                            <div class="col-lg-3">
                                <div class="input-effect sm2_mb_20 md_mb_20">
                                    <input  class="primary-input phone_number form-control{{ $errors->has('aka') ? ' is-invalid' : '' }}" type="text" name="aka" id="aka" value="{{$student->aka}}">
                                    
                                    <label>@lang('student.aka')  @if(is_required('aka')==true) <span> *</span> @endif</label>
                                  
                                    <span class="focus-border"></span>
                                    @if ($errors->has('aka'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('aka') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            @if(is_show('religion'))
                            <div class="col-lg-2">
                                <div class="input-effect sm2_mb_20 md_mb_20">
                                    <select class="niceSelect w-100 bb form-control{{ $errors->has('marital_status') ? ' is-invalid' : '' }}" name="marital_status">
                                        <option data-display="@lang('student.marital_status') @if(is_required('marital_status')==true) @endif" value="">@lang('student.marital_status') @if(is_required('marital_status')==true) <span> *</span> @endif</option>
                                        @foreach($religions as $marital_status)
                                      

                                        @if(isset($student->religion_id))
                                        <option value="{{$marital_status->id}}" {{$marital_status->id == $student->religion_id? 'selected': ''}}>{{$marital_status->base_setup_name}}</option>
                                    @else
                                        <option value="{{$marital_status->id}}">{{$marital_status->base_setup_name}}</option>
                                    @endif
                                        @endforeach

                                    </select>
                                    <span class="focus-border"></span>
                                    @if ($errors->has('marital_status'))
                                    <span class="invalid-feedback invalid-select" role="alert">
                                        <strong>{{ $errors->first('marital_status') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            @endif 
                           
                            @if(is_show('caste'))
                            <div class="col-lg-2">
                                <div class="input-effect sm2_mb_20 md_mb_20">
                                    <input class="primary-input" type="text" name="caste" value="{{$student->caste}}">
                                    <label>@lang('student.caste') @if(is_required('caste')==true) <span> *</span> @endif</label>
                                    <span class="focus-border"></span>
                                    @if ($errors->has('caste'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('caste') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            @endif 

                            <div class="col-lg-3">
                                <div class="input-effect sm2_mb_20 md_mb_20">
                                    <input class="primary-input" type="text" name="nationality" value="{{$student->nationality}}">
                                    <label>@lang('common.nationality')  @if(is_required('nationality')==true) <span> *</span> @endif</label>
                                    <span class="focus-border"></span>
                                    @if ($errors->has('nationality'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('nationality') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            @if(is_show('blood_group'))
                            <div class="col-lg-2">
                               <div class="input-effect sm2_mb_20 md_mb_20">
                                <select class="niceSelect w-100 bb form-control{{ $errors->has('blood_group') ? ' is-invalid' : '' }}" name="blood_group">
                                    <option data-display="@lang('student.blood_group') @if(is_required('blood_group')==true)  * @endif" value="">@lang('student.blood_group') @if(is_required('blood_group')==true) <span> *</span> @endif</option>
                                    @foreach($blood_groups as $blood_group)
                                    @if(isset($student->bloodgroup_id))
                                        <option value="{{$blood_group->id}}" {{$blood_group->id == $student->bloodgroup_id? 'selected': ''}}>{{$blood_group->base_setup_name}}</option>
                                    @else
                                        <option value="{{$blood_group->id}}">{{$blood_group->base_setup_name}}</option>
                                    @endif
                                    @endforeach
                                </select>
                                   <span class="focus-border"></span>
                                   @if ($errors->has('blood_group'))
                                   <span class="invalid-feedback invalid-select" role="alert">
                                       <strong>{{ $errors->first('blood_group') }}</strong>
                                   </span>
                                   @endif
                               </div>
                           </div>
                           @endif 
                           
                          
                         
                           
                           
                        </div>
                        <div class="ro mb-40 d-none" id="exitStudent">
                            <div class="col-lg-12">
                                <input type="checkbox" id="edit_info" value="yes" class="common-checkbox" name="edit_info">
                                <label for="edit_info" class="text-danger">@lang('student.student_already_exit_this_phone_number/email_are_you_to_edit_student_parent_info')</label>
                            </div>
                        </div>
                        <div class="row mb-40">

                                     @if(is_show('dormitory_name'))
                                <div class="col-lg-2">
                                    <div class="input-effect sm2_mb_20 md_mb_20">
                                        <select class="niceSelect w-100 bb form-control{{ $errors->has('dormitory_name') ? ' is-invalid' : '' }}" name="dormitory_name" id="SelectDormitory">
                                            <option data-display="@lang('dormitory.dormitory_name')" @if(is_required('dormitory_name')==true) * @endif" value="">@lang('dormitory.dormitory_name') @if(is_required('dormitory_name')==true) <span> *</span> @endif</option >
                                            @foreach($dormitory_lists as $dormitory_list)
                                            <option value="{{$dormitory_list->id}}" {{old('dormitory_name') == $dormitory_list->id? 'selected': ''}}>{{$dormitory_list->dormitory_name}}</option>
                                            @endforeach
                                        </select>
                                        <span class="focus-border"></span>
                                        @if ($errors->has('dormitory_name'))
                                        <span class="invalid-feedback invalid-select" role="alert">
                                            <strong>{{ $errors->first('dormitory_name') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                @endif 
                                @if(is_show('room_number'))
                                <div class="col-lg-2">
                                    <div class="input-effect sm2_mb_20 md_mb_20" id="roomNumberDiv">
                                        <select class="niceSelect w-100 bb form-control" name="room_number" id="selectRoomNumber">
                                            <option data-display="@lang('academics.room_number') @if(('room_number')==true) <span> *</span> @endif" value="">@lang('academics.room_number') @if(('room_number')==true) <span> *</span> @endif</option>
                                        </select>
                                        <div class="pull-right loader loader_style" id="select_dormitory_loader">
                                            <img class="loader_img_style" src="{{asset('public/backEnd/img/demo_wait.gif')}}" alt="loader">
                                        </div>
                                        <span class="focus-border"></span>
                                      
                                    </div>
                                </div>
                                @endif 
                         
                           
                          
                           
                             @if(is_show('gender'))
                             <div class="col-lg-2">
                                 
                                
                                
                                
                                <div class="input-effect sm2_mb_20 md_mb_20">
                                    <select class="niceSelect w-100 bb form-control{{ $errors->has('gender') ? ' is-invalid' : '' }}" name="gender">
                                        <option data-display="@lang('common.gender') @if(is_required('gender')==true)  * @endif" value="">@lang('common.gender') @if(is_required('gender')==true) <span> *</span> @endif</option>
                                        @foreach($genders as $gender)
                                            @if(isset($student->gender_id))
                                                <option value="{{$gender->id}}" {{$student->gender_id == $gender->id? 'selected': ''}}>{{$gender->base_setup_name}}</option>
                                            @else
                                                <option value="{{$gender->id}}">{{$gender->base_setup_name}}</option>
                                            @endif
                                        @endforeach

                                    </select>
                                     <span class="focus-border"></span>
                                     @if ($errors->has('gender'))
                                     <span class="invalid-feedback invalid-select" role="alert">
                                         <strong>{{ $errors->first('gender') }}</strong>
                                     </span>
                                     @endif
                                 </div>
                             </div>
                             @endif 
                       






                            @if(is_show('student_group_id'))
                            <div class="col-lg-2">
                                <div class="input-effect sm2_mb_20 md_mb_20">
                                    <div class="input-effect sm2_mb_20 md_mb_20">
                                 

                                        <select class="niceSelect w-100 bb"
                                         
                                        name="student_group_id">
                                    <option
                                            data-display="@lang('student.group') @if (is_required('student_group_id') == true) * @endif"
                                            value="">@lang('student.group') @if (is_required('student_group_id') == true)
                                            <span class="text-danger"> *</span>
                                        @endif
                                    </option>
                                    @foreach ($groups as $group)
                                        @if (isset($student->student_group_id))
                                            <option value="{{ $group->id }}"
                                                    {{ $student->student_group_id == $group->id ? 'selected' : '' }}>
                                                {{ $group->group }}</option>
                                        @else
                                            <option value="{{ $group->id }}">{{ $group->group }}
                                            </option>
                                        @endif
                                    @endforeach

                                </select>

                                    <span class="focus-border"></span>
                                    @if ($errors->has('student_group_id'))
                                    <span class="invalid-feedback invalid-select" role="alert">
                                        <strong>{{ $errors->first('student_group_id') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                </div>
                            </div>
                            @endif 
                            @if(is_show('height'))
                            <div class="col-lg-2">
                                <div class="input-effect sm2_mb_20 md_mb_20">
                                    <input class="primary-input" type="text" name="height" value="{{$student->height}}">
                                    <label>@lang('student.height_in')  @if(is_required('height')==true) <span> *</span> @endif </label>
                                    <span class="focus-border"></span>
                                    @if ($errors->has('height'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('height') }}</strong>
                                            </span>
                                            @endif
                                </div>
                            </div>
                            @endif 
                            @if(is_show('weight'))
                            <div class="col-lg-2">
                                <div class="input-effect sm2_mb_20 md_mb_20">
                                    <select class="niceSelect w-100 bb" name="weight" value="{{$student->weight}}">
                                        <option value="{{$student->weight}}">{{$student->weight}}</option>

                                           <option value="BECE">BECE</option>
                                        <option value="WASSCE">WASSCE/SSCE</option>
                                        <option value="DIPLOMA">DIPLOMA</option>
                                        <option value="DEGREE">DEGREE</option>
                                        <option value="MASTERS">MASTERS</option>
                                        <option value="PHD">PHD</option>
                                        <option value="NO QUALIFICATION">NO QUALIFICATION</option>
                                    </select>

                                 {{-- <input class="primary-input" type="text" name="weight" value="{{old('weight')}}">
                                    <label>@lang('student.weight_kg')  @if(is_required('weight')==true) <span> *</span> @endif </label>
                                   --}} <span class="focus-border"></span>
                                    @if ($errors->has('weight'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('weight') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>



                            @endif 
                        </div>

                        
                    <div class="row mb-30 mt-30">
                        @if(is_show('national_id_number'))
                        <div class="col-lg-3">
                            <div class="input-effect sm2_mb_20 md_mb_20">
                                <input class="primary-input form-control{{ $errors->has('national_id_number') ? ' is-invalid' : '' }}" type="text" name="national_id_number" value="{{$student->national_id_no}}">
                                <label>@lang('common.national_id_number') @if(is_required('national_id_number')==true) <span> *</span> @endif </label>
                                <span class="focus-border"></span>
                                @if ($errors->has('national_id_number'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('national_id_number') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        @endif 
                        
                         
                     
                        @if(is_show('bank_account_number'))
                        <div class="col-lg-3">
                            <div class="input-effect sm2_mb_20 md_mb_20">
                                
                                   <select class="niceSelect w-100 bb" name="bank_account_number" value="{{$student->bank_account_no}}">
                                     
                            
                                <option value="{{$student->bank_account_no}}">{{$student->bank_account_no}}</option>

                                <option value="SUNDAY">SUNDAY</option>
                                <option value="MONDAY">MONDAY</option>
                                <option value="TUESDAY">TUESDAY</option>
                                <option value="WEDNESDAY">WEDNESDAY</option>
                                <option value="THURSDAY">THURSDAY</option>
                                <option value="FRIDAY">FRIDAY</option>
                                <option value="SATURDAY">SATURDAY</option>
                            </select>
                            <label>@lang('accounts.bank_account_number')@if(is_required('bank_account_number')==true) <span> *</span> @endif </label>
                           
                                <span class="focus-border"></span>
                                 
                            </div>
                        </div>
                        @endif 
                        @if(is_show('bank_name'))
                        <div class="col-lg-3">
                            <div class="input-effect sm2_mb_20 md_mb_20">
                                <input class="primary-input" type="text" name="bank_name" value="{{$student->bank_name}}">
                               
                                <label>@lang('student.bank_name') @if(is_required('bank_name')==true) <span> *</span> @endif </label>
                                <span class="focus-border"></span>
                                @if ($errors->has('bank_name'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('bank_name') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        @endif 


                        @if(is_show('photo'))  
                        <div class="col-lg-3">
                            <div class="row no-gutters input-right-icon">
                                <div class="col">
                                    <div class="input-effect sm2_mb_20 md_mb_20">
                                        <input class="primary-input" type="text" id="placeholderPhoto" placeholder="@lang('common.student_photo')  @if(is_required('photo')==true) * @endif"
                                            readonly="">
                                        <span class="focus-border"></span>

                                        @if ($errors->has('photo'))
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ @$errors->first('photo') }}</strong>
                                            </span>
                                        @endif

                                    </div>
                                </div>
                                <div class="col-auto">
                                    <button class="primary-btn-small-input" type="button">
                                        <label class="primary-btn small fix-gr-bg" for="photo">@lang('common.browse')</label>
                                        <input type="file" class="d-none" value="{{ old('photo') }}" name="photo" id="photo">
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif 

                    </div>
            
                        
                        @if(generalSetting()->with_guardian)
                        @if(is_show('guardians_email') || is_show('guardians_phone'))
                        <div class="col-lg-6 text-right">
                            <div class="row">
                                <div class="col-lg-7 text-left" id="parent_info">
                                    <input type="hidden" name="parent_id" value="">

                                </div>
                                
                            </div>

                        </div>
                        @endif 
                        @endif 
                    </div>
                    @if(generalSetting()->with_guardian)
                    <input type="hidden" name="staff_parent" id="staff_parent">
                    <!-- Start Sibling Add Modal -->
                    <div class="modal fade admin-query" id="editStudent">
                        <div class="modal-dialog small-modal modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">@lang('student.select_sibling')</h4>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>

                                <div class="modal-body">
                                    <div class="container-fluid">
                                        <form action="">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="d-flex radio-btn-flex">
                                                        <div class="mr-30">
                                                            <input type="radio" name="subject_type" id="siblingParentRadio" value="sibling" class="common-radio relationButton addParent" checked>
                                                            <label for="siblingParentRadio">@lang('student.From Sibling')</label>
                                                        </div>
                                                       
                                                        <div class="mr-30">
                                                            <input type="radio" name="subject_type" id="staffParentRadio" value="staff" class="common-radio relationButton addParent">
                                                            <label for="staffParentRadio">@lang('student.From Staff')</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-25" id="siblingParent">
                                                <div class="col-lg-12">

                                                    <div class="row">
                                                        <div class="col-lg-12" id="sibling_required_error">

                                                        </div>
                                                    </div>
                                                    <div class="row mt-25">
                                                        <div class="col-lg-12" id="sibling_class_div">
                                                            <select class="niceSelect w-100 bb" name="sibling_class" id="select_sibling_class">
                                                                <option data-display="@lang('student.class') *" value="">@lang('student.class') *</option>
                                                                @foreach($classes as $class)
                                                                <option value="{{$class->id}}" {{old('sibling_class') == $class->id? 'selected': '' }} >{{$class->age_group_name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row mt-25">
                                                        <div class="col-lg-12" id="sibling_section_div">
                                                            <select class="niceSelect w-100 bb" name="sibling_section" id="select_sibling_section">
                                                                <option data-display="@lang('common.section') *" value="">@lang('common.section') *</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-25">
                                                        <div class="col-lg-12" id="sibling_name_div">
                                                            <select class="niceSelect w-100 bb" name="select_sibling_name" id="select_sibling_name">
                                                                <option data-display="@lang('student.sibling') *" value="">@lang('student.sibling') *</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row mt-25 d-none" id="staffParent">
                                                <div class="col-lg-12">
                                                    <div class="row">
                                                        <div class="col-lg-12" id="staff_class_div">
                                                            <select class="niceSelect w-100 bb"  id="select_staff_parent">
                                                                <option data-display="@lang('hr.select_staff') *" value="">@lang('hr.select_staff') *</option>
                                                                @foreach($staffs as $staff)
                                                                <option value="{{$staff->id}}" >{{$staff->full_name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12 text-center mt-40">
                                                    <div class="mt-40 d-flex justify-content-between">
                                                        <button type="button" class="primary-btn tr-bg" data-dismiss="modal">@lang('common.cancel')</button>

                                                        <button class="primary-btn fix-gr-bg" id="save_button_parent" data-dismiss="modal" type="button">@lang('common.save_information')</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- End Sibling Add Modal -->
                    <div class="parent_details" id="parent_details">
                        
                        <div class="row mt-40">
                            <div class="col-lg-12">
                                <div class="main-title">
                                    <h4 class="stu-sub-head">@lang('student.parents_and_guardian_info') </h4>
                                </div>
                            </div>
                        </div>

            

                    
                  
                        <div class="row mb-30 mt-30">
                        
                           
                     
                            <div class="col-lg-3">
                                <div class="input-effect sm2_mb_20 md_mb_20">
                                    <input class="primary-input" type="text" name="guardians_name" value="{{$student->guardians_name}}">
                               
                                        <label>@lang('student.guardian_name')  @if(is_required('guardians_name')==true) <span> *</span> @endif </label>
                                    <span class="focus-border"></span>
                                    @if ($errors->has('guardians_name'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('guardians_name') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
 
 

                                <div class="col-lg-4">
                                    <div class="input-effect sm2_mb_20 md_mb_20">
                                         
                                        
                                           <select class="niceSelect w-100 bb" name="guardians_relation" value="{{$student->guardians_relation}}">
                                            <option value="{{$student->guardians_relation}}">{{$student->guardians_relation}}</option>
        
                                        <option value="FATHER">FATHER</option>
                                        <option value="MOTHER">MOTHER</option>
                                        <option value="BROTHER">BROTHER</option>
                                        <option value="SISTER">SISTER</option>
                                    
                                    </select>
                                    <label>@lang('student.relation_with_guardian') @if(is_required('relation')==true) <span> *</span> @endif </label> 
                                        <span class="focus-border"></span>
                                         
                                    </div>
                          
                                </div>
                            
              
                            @if(is_show('guardians_photo')) 
                            <div class="col-lg-3">
                                <div class="row no-gutters input-right-icon">
                                    <div class="col">
                                        <div class="input-effect sm2_mb_20 md_mb_20">
                                            <input class="primary-input" type="text" id="placeholderGuardiansName" placeholder="@lang('student.photo') @if(is_required('guardians_photo')==true) * @endif"
                                                readonly="">
                                            <span class="focus-border"></span>
                                            @if ($errors->has('guardians_photo'))
                                                    <span class="invalid-feedback d-block" role="alert">
                                                        <strong>{{ @$errors->first('guardians_photo') }}</strong>
                                                    </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <button class="primary-btn-small-input" type="button">
                                            <label class="primary-btn small fix-gr-bg" for="guardians_photo">@lang('common.browse')</label>
                                            <input type="file" class="d-none" name="guardians_photo" id="guardians_photo">
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endif 
                        </div>
                        <div class="row mb-30">
                            @if(is_show('guardians_phone')) 
                            <div class="col-lg-3">

                          
                                    <div class="input-effect sm2_mb_20 md_mb_20">
                                        <input class="primary-input" type="text" name="guardians_phone" value="{{$student->guardians_phone}}">
                                   
                                            <label>@lang('student.guardians_phone')  @if(is_required('guardians_phone')==true) <span> *</span> @endif </label>
                                        <span class="focus-border"></span>
                                        @if ($errors->has('guardians_phone'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('guardians_phone') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                          
     


                            </div>
                            @endif 
                            @if(is_show('guardians_occupation')) 
                            <div class="col-lg-3">
                                <div class="input-effect sm2_mb_20 md_mb_20">
                                    <input class="primary-input" type="text" name="guardians_occupation" id="guardians_occupation"  value="{{$student->guardians_occupation}}">
                                    <label>@lang('student.guardian_occupation') @if(is_required('guardians_occupation')==true) <span> *</span> @endif</label>
                                    <span class="focus-border"></span>
                                    @if ($errors->has('guardians_occupation'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ @$errors->first('guardians_occupation') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            @endif 
                       
                       
                            @if(is_show('guardians_address'))
                            <div class="col-lg-4">
                                <div class="input-effect sm2_mb_20 md_mb_20">
                                    <input class="primary-input" type="text" name="guardians_address" id="guardians_address"  value="{{$student->guardians_address}}"> 
                             
                                      <label>@lang('student.guardian_address') @if(is_required('guardians_address')==true) <span> *</span> @endif </label>
                                    <span class="focus-border textarea"></span>
                                   @if ($errors->has('guardians_address'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('guardians_address') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            @endif 
                        </div>
                    </div>
                    @endif 


                    <div class="row mt-40">
                        <div class="col-lg-12">
                            <div class="main-title">
                                <h4 class="stu-sub-head">@lang('student.student_address_info')</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-30 mt-30">
                        
                        @if(is_show('phone_number'))
                        <div class="col-lg-3">
                            <div class="input-effect sm2_mb_20 md_mb_20">
                                <input oninput="phoneCheck(this)" class="primary-input form-control{{ $errors->has('phone_number') ? ' is-invalid' : '' }}" type="text" name="phone_number" value="{{$student->mobile}}">
                                   
                                <label>@lang('student.phone_number')  @if(is_required('phone_number')==true) <span> *</span> @endif</label>
                              
                                <span class="focus-border"></span>
                                @if ($errors->has('phone_number'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('phone_number') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        @endif 
                        @if(is_show('roll_number'))
                        <div class="col-lg-3">
                            <div class="input-effect sm2_mb_20 md_mb_20">
                                <input oninput="numberCheck(this)" class="primary-input" type="text" id="roll_number" name="phone_work"   value="{{$student->phone_work}}">
                                <label> {{ moduleStatusCheck('Lead')==true ? __('lead::lead.id_number') : __('student.roll') }}
                                     @if(is_required('roll_number')==true) <span> *</span> @endif</label>
                                <span class="focus-border"></span>
                                <span class="text-danger" id="roll-error" role="alert">
                                    <strong></strong>
                                </span>
                                @if ($errors->has('roll_number'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('roll_number') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                         @endif 
                          @if(is_show('email_address'))
                            <div class="col-lg-3">
                                <div class="input-effect sm2_mb_20 md_mb_20">
                                    <input oninput="emailCheck(this)" class="primary-input form-control{{ $errors->has('email_address') ? ' is-invalid' : '' }}" type="text" name="email_address" value="{{$student->email}}">
                                   <label>@lang('common.email_address')  @if(is_required('email_address')==true) <span> *</span> @endif</label>
                                    <span class="focus-border"></span>
                                    @if ($errors->has('email_address'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email_address') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            @endif 
                           
                        @if(is_show('current_address'))
                        <div class="col-lg-3">
                            <div class="input-effect sm2_mb_20 md_mb_20">
                                   <input class="primary-input form-control{{ $errors->has('current_address') ? ' is-invalid' : '' }}" type="text" name="current_address" value="{{$student->current_address}}">
                                <label>@lang('student.current_address') @if(is_required('current_address')==true) <span> *</span> @endif </label>
                                <span class="focus-border"></span>
                               @if ($errors->has('current_address'))
                                <span class="invalid-feedback">
                                    <strong>{{ $errors->first('current_address') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        @endif 
                      
                       
                       
                    </div>
                    <div class="row mb-30 mt-30">
                      
                        <div class="col-lg-3">
                           <div class="input-effect sm2_mb_20 md_mb_20">
                                 <input class="primary-input form-control{{ $errors->has('permanent_address') ? ' is-invalid' : '' }}" type="text" name="permanent_address" value="{{$student->permanent_address}}">
                         
                                  <label>@lang('student.permanent_address')  @if(is_required('permanent_address')==true) <span> *</span> @endif </label>
                               <span class="focus-border"></span>
                              @if ($errors->has('permanent_address'))
                               <span class="invalid-feedback">
                                   <strong>{{ $errors->first('permanent_address') }}</strong>
                               </span>
                               @endif
                           </div>
                       </div>
                       
                 
                    <div class="col-lg-3">
                        <div class="input-effect sm2_mb_20 md_mb_20">
                               <input class="primary-input form-control{{ $errors->has('othercontact') ? ' is-invalid' : '' }}" type="text" name="othercontact" value="{{$student->othercontact}}"> 
                            <label>@lang('student.othercontact') @if(is_required('othercontact')==true) <span> *</span> @endif </label>
                            <span class="focus-border"></span>
                           @if ($errors->has('othercontact'))
                            <span class="invalid-feedback">
                                <strong>{{ $errors->first('othercontact') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                  
              
                     <div class="col-lg-3">
                        <div class="input-effect sm2_mb_20 md_mb_20">
                              <input class="primary-input form-control{{ $errors->has('landmark') ? ' is-invalid' : '' }}" type="text" name="landmark" value="{{$student->landmark}}"> 
                      
                               <label>@lang('student.area')  @if(is_required('landmark')==true) <span> *</span> @endif </label>
                            <span class="focus-border"></span>
                           @if ($errors->has('landmark'))
                            <span class="invalid-feedback">
                                <strong>{{ $errors->first('landmark') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
             
                   
                   
                </div> 
             
                 
              

                <script type="text/javascript">
                    function changeFunc() {
                        var selectBox = document.getElementById("selectBox");
                        var selectedValue = selectBox.options[selectBox.selectedIndex].value;
                        if (selectedValue == "YES") {
                            $('#textboxes').show();
                        } else {
                           
                            $('#textboxes').hide();
                        }
                    }


                    function changeConfirmationFunc() {
                        var selectBox = document.getElementById("selectConfirmationBox");
                        var selectedValue = selectBox.options[selectBox.selectedIndex].value;
                        if (selectedValue == "YES") {
                            $('#confirmationtextboxes').show();
                        } else {
                           
                            $('#confirmationtextboxes').hide();
                        }
                    }


                    function changemarriageFunc() {
                        var selectBox = document.getElementById("selectMarriageBox");
                        var selectedValue = selectBox.options[selectBox.selectedIndex].value;
                        if (selectedValue == "YES") {
                            $('#marriagetextboxes').show();
                        } else {
                           
                            $('#marriagetextboxes').hide();
                        }
                    }

                    
                    function changefamilyFunc() {
                        var selectBox = document.getElementById("selectFamilyBox");
                        var selectedValue = selectBox.options[selectBox.selectedIndex].value;
                        if (selectedValue == "YES") {
                            $('#familytextboxes').show();
                        } else {
                           
                            $('#familytextboxes').hide();
                        }
                    }

                    function changestudentFunc() {
                        var selectBox = document.getElementById("selectStudentBox");
                        var selectedValue = selectBox.options[selectBox.selectedIndex].value;
                        if (selectedValue == "YES") {
                            $('#studenttextboxes').show();
                        } else {
                           
                            $('#studenttextboxes').hide();
                        }
                    }
                </script>
                <div class="row mb-40 mt-40">
                               
                    <div class="col-lg-2">
                        <div class="input-effect sm2_mb_20 md_mb_20">
                            <label>@lang('student.baptism') @if(is_required('ifsc_code')==true) <span> *</span> @endif</label>
                      
                            <select class="niceSelect w-100 bb" name="baptism_status" id="selectBox" onchange="changeFunc();">
                                <option value="{{$student->baptism_status}}">{{$student->baptism_status}}</option>
                                <option value="NO">NO</option>
                                <option value="YES">YES</option>
                            

                            </select>
                               <span class="focus-border"></span>
                            @if ($errors->has('ifsc_code'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('ifsc_code') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div> 


                               
                    <div class="col-lg-2">
                        <div class="input-effect sm2_mb_20 md_mb_20">
                            <label>@lang('student.confirmation') @if(is_required('confirm')==true) <span> *</span> @endif</label>
                      
                            <select class="niceSelect w-100 bb" name="confirmation_status" id="selectConfirmationBox" onchange="changeConfirmationFunc();">
                                <option value="{{$student->confirmation_status}}">{{$student->confirmation_status}}</option>
                                <option value="NO">NO</option>
                                <option value="YES">YES</option>
                           

                            </select>
                               <span class="focus-border"></span>
                            @if ($errors->has('confirmation'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('confirmation') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div> 


                    <div class="col-lg-3">
                        <div class="input-effect sm2_mb_20 md_mb_20">
                            <label>@lang('student.family') @if(is_required('family')==true) <span> *</span> @endif</label>
                      
                            <select class="niceSelect w-100 bb" name="family_status" id="selectFamilyBox" onchange="changefamilyFunc();">
                                <option value="{{$student->family_status}}">{{$student->family_status}}</option>
                                <option value="NO">NO</option>
                                <option value="YES">YES</option>
                             

                            </select>
                               <span class="focus-border"></span>
                            @if ($errors->has('family'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('family') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div> 


                    <div class="col-lg-3">
                        <div class="input-effect sm2_mb_20 md_mb_20">
                            <label>@lang('student.marriage') @if(is_required('marriage')==true) <span> *</span> @endif</label>
                      
                            <select class="niceSelect w-100 bb" name="marriage_status" id="selectMarriageBox" onchange="changemarriageFunc();">
                                <option value="{{$student->marriage_status}}">{{$student->marriage_status}}</option>
                                <option value="NO">NO</option>
                                <option value="YES">YES</option>
                            

                            </select>
                               <span class="focus-border"></span>
                            @if ($errors->has('marriage'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('marriage') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div> 
                    <div class="col-lg-2">
                        <div class="input-effect sm2_mb_20 md_mb_20">
                            <label>@lang('student.student') @if(is_required('student')==true) <span> *</span> @endif</label>
                      
                            <select class="niceSelect w-100 bb" name="student_status" id="selectStudentBox" onchange="changestudentFunc();">
                                <option value="{{$student->student_status}}">{{$student->student_status}}</option>
                                <option value="NO">NO</option>
                                <option value="YES">YES</option>
                              

                            </select>
                               <span class="focus-border"></span>
                            @if ($errors->has('student'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('student') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div> 
                <div>

                    
                    <div class="row  mt-40" style="display: none" id="textboxes">
                 
                        <div class="col-lg-12">
                            <div class="main-title">
                                <h4 class="stu-sub-head">@lang('student.baptism_details')</h4>
                            </div>
                        </div>
                   
                   
                    <div class="col-lg-2">
                        <div class="input-effect sm2_mb_20 md_mb_20">

                         <input class="primary-input date form-control{{ $errors->has('date_of_baptism') ? ' is-invalid' : '' }}" id="startDate" type="text"
                             autocomplete="off" name="date_of_baptism" id="date_of_baptism"  value="{{$student->date_of_baptism}}">
                            <label>@lang('student.date_of_baptism')  @if(is_required('date_of_baptism')==true) <span> *</span> @endif</label>
                          
                            <span class="focus-border"></span>
                            @if ($errors->has('date_of_baptism'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('date_of_baptism') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                  
                 
                    <div class="col-lg-2">
                       
                            <div class="input-effect sm2_mb_20 md_mb_20">
                                <input class="primary-input" type="text" name="place_of_baptism" id="place_of_baptism"  value="{{$student->place_of_baptism}}">
                                <label>@lang('student.place_of_baptism') @if(is_required('place_of_baptism')==true) <span> *</span> @endif</label>
                                <span class="focus-border"></span>
                                @if ($errors->has('place_of_baptism'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ @$errors->first('place_of_baptism') }}</strong>
                                </span>
                                @endif
                            </div>
                   </div>
    
                    <div class="col-lg-3">
                        <div class="input-effect sm2_mb_20 md_mb_20">
                              
                            
                                <select class="niceSelect w-100 bb" name="baptism_type" value="{{$student->type_of_baptism}}">
                                    <option value="{{$student->type_of_baptism}}">{{$student->type_of_baptism}}</option>

                                    <OPTION VALUE="SPRINKLING">ASPERSION(SPRINKLING)</OPTION>
                                    <OPTION VALUE="IMMERSION(SUBMERGING)">IMMERSION(SUBMERGING)</OPTION>
                                    <OPTION VALUE="AFFUSION(POURING WATER)">AFFUSION(POURING WATER)</OPTION>
                                    <OPTION VALUE="OTHER">OTHER</OPTION>
    
                            </select>
                            <label>@lang('student.baptism_type')  @if(is_required('baptism_type')==true) <span> *</span> @endif</label>
                       
                            <span class="focus-border"></span>
                            <span class="text-danger" id="roll-error" role="alert">
                                <strong></strong>
                            </span>
                            @if ($errors->has('baptism_type'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('baptism_type') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                       
                    <div class="col-lg-2">
                       <div class="input-effect sm2_mb_20 md_mb_20">
                            <input class="primary-input" type="text" name="baptism_cert_no" id="baptism_cert_no"  value="{{$student->baptism_cert_no}}">
                            <label>@lang('student.baptism_cert_no') @if(is_required('baptism_cert_no')==true) <span> *</span> @endif</label>
                            <span class="focus-border"></span>
                            @if ($errors->has('baptism_cert_no'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ @$errors->first('baptism_cert_no') }}</strong>
                            </span>
                            @endif
                        </div>
                       
                            
                    
                    </div>
                    <div class="col-lg-3">


                        <div class="input-effect sm2_mb_20 md_mb_20">
                            <input class="primary-input" type="text" name="baptism_off_minister" id="baptism_off_minister"  value="{{$student->baptism_off_minister}}">
                            <label>@lang('student.baptism_off_minister') @if(is_required('baptism_off_minister')==true) <span> *</span> @endif</label>
                            <span class="focus-border"></span>
                            @if ($errors->has('baptism_off_minister'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ @$errors->first('baptism_off_minister') }}</strong>
                            </span>
                            @endif
                        </div>
                       
 
                         
                    </div>
                   
                </div>

      
                <div class="row  mt-40"  style="display: none" id="confirmationtextboxes">
                    <div class="col-lg-12">
                        <div class="main-title">
                            <h4 class="stu-sub-head">@lang('student.confirmation_details')</h4>
                        </div>
                    </div>
              
                <div class="row mb-30 mt-40">
                        
                                   
                    <div class="col-lg-2">
                        <div class="input-effect sm2_mb_20 md_mb_20">
                              <input class="primary-input date form-control{{ $errors->has('date_of_confirmation') ? ' is-invalid' : '' }}" id="startDate" type="text"
                            name="confirmation_date"  value="{{$student->confirmation_date}}">
                            <label>@lang('student.date_of_confirmation')  @if(is_required('date_of_confirmation')==true) <span> *</span> @endif</label>
                          
                            <span class="focus-border"></span>
                            @if ($errors->has('date_of_confirmation'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('date_of_confirmation') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>

                    <div class="col-lg-2">
                        <div class="input-effect sm2_mb_20 md_mb_20">
                            <input   class="primary-input" type="text" id="ageconfirmed" name="ageconfirmed"  value="{{$student->ageconfirmed}}">
                            <label>@lang('student.ageconfirmed')  @if(is_required('ageconfirmed')==true) <span> *</span> @endif</label>
                          
                            <span class="focus-border"></span>
                            @if ($errors->has('ageconfirmed'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('ageconfirmed') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                  
                 
                    <div class="col-lg-2">
                        <div class="input-effect sm2_mb_20 md_mb_20">
                            <input   class="primary-input" type="text" id="place_of_confirmation" name="place_of_confirmation"  value="{{$student->place_of_confirmation}}">
                            <label>@lang('student.place_of_confirmation')  @if(is_required('place_of_confirmation')==true) <span> *</span> @endif</label>
                       
                            <span class="focus-border"></span>
                            <span class="text-danger" id="roll-error" role="alert">
                                <strong></strong>
                            </span>
                            @if ($errors->has('place_of_confirmation'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('place_of_confirmation') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
    
                    <div class="col-lg-2">
                        <div class="input-effect sm2_mb_20 md_mb_20">
                              
                            <input   class="primary-input" type="text" id="bibleverseused" name="bibleverseused"  value="{{$student->bibleverseused}}">
                       
                            <label>@lang('student.bibleverseused')  @if(is_required('bibleverseused')==true) <span> *</span> @endif</label>
                       
                            <span class="focus-border"></span>
                            <span class="text-danger" id="roll-error" role="alert">
                                <strong></strong>
                            </span>
                            @if ($errors->has('bibleverseused'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('bibleverseused') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                       
                    <div class="col-lg-2">
                        <div class="input-effect sm2_mb_20 md_mb_20">
                            <input   class="primary-input" type="text" id="confirmation_cert_no" name="confirmation_cert_no"  value="{{$student->confirmation_cert_no}}">
                            <label>@lang('student.confirmation_cert_no')  @if(is_required('confirmation_cert_no')==true) <span> *</span> @endif</label>
                       
                            <span class="focus-border"></span>
                            <span class="text-danger" id="roll-error" role="alert">
                                <strong></strong>
                            </span>
                            @if ($errors->has('confirmation_cert_no'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('confirmation_cert_no') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="input-effect sm2_mb_20 md_mb_20">
                            <input   class="primary-input" type="text" id="confirmation_off_minister" name="confirmation_off_minister"  value="{{$student->confirmation_off_minister}}">
                            <label>@lang('student.confirmation_off_minister')  @if(is_required('confirmation_off_minister')==true) <span> *</span> @endif</label>
                       
                            <span class="focus-border"></span>
                            <span class="text-danger" id="roll-error" role="alert">
                                <strong></strong>
                            </span>
                            @if ($errors->has('confirmation_off_minister'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('confirmation_off_minister') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                   
                </div>
            </div>
            <div class="row  mt-40" style="display: none" id="marriagetextboxes">
          
                    <div class="col-lg-12">
                        <div class="main-title">
                            <h4 class="stu-sub-head">@lang('student.marriage_details')</h4>
                        </div>
                    </div>
            
                <div class="row mb-30 mt-30">
                        
                  
                    <div class="col-lg-2">
                        <div class="input-effect sm2_mb_20 md_mb_20">
                              <input class="primary-input date form-control{{ $errors->has('date_of_marriage') ? ' is-invalid' : '' }}" id="startDate" type="text"
                            name="date_of_marriage"   value="{{$student->date_of_marriage}}">
                            <label>@lang('student.date_of_marriage')  @if(is_required('date_of_marriage')==true) <span> *</span> @endif</label>
                          
                            <span class="focus-border"></span>
                            @if ($errors->has('date_of_marriage'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('date_of_marriage') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                  
                 
                    <div class="col-lg-3">
                        <div class="input-effect sm2_mb_20 md_mb_20">
                            <input   class="primary-input" type="text" id="place_of_marriage" name="place_of_marriage"   value="{{$student->place_of_marriage}}">
                            <label>@lang('student.place_of_marriage')  @if(is_required('place_of_marriage')==true) <span> *</span> @endif</label>
                       
                            <span class="focus-border"></span>
                            <span class="text-danger" id="roll-error" role="alert">
                                <strong></strong>
                            </span>
                            @if ($errors->has('place_of_marriage'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('place_of_marriage') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
    
                    <div class="col-lg-2">
                        <div class="input-effect sm2_mb_20 md_mb_20">
                              
                  
                                <select class="niceSelect w-100 bb" name="marriage_type"  value="{{$student->marriage_type}}">
                                    <option value="{{$student->marriage_type}}">{{$student->marriage_type}}</option>
                                 
                                <option value="TRADITIONAL/CUSTOMARY">TRADITIONAL/CUSTOMARY</option>
                                <option value="ORDINANCE">ORDINANCE</option>
                                <option value="Other">Other</option>
                            </select>
                            <label>@lang('student.marriage_type')  @if(is_required('marriage_type')==true) <span> *</span> @endif</label>
                       
                            <span class="focus-border"></span>
                            <span class="text-danger" id="roll-error" role="alert">
                                <strong></strong>
                            </span>
                            @if ($errors->has('marriage_type'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('marriage_type') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                       
                    <div class="col-lg-3">
                        <div class="input-effect sm2_mb_20 md_mb_20">
                            <input   class="primary-input" type="text" id="marriage_cert_no" name="marriage_cert_no"  value="{{$student->marriage_cert_no}}">
                            <label>@lang('student.marriage_cert_no')  @if(is_required('marriage_cert_no')==true) <span> *</span> @endif</label>
                       
                            <span class="focus-border"></span>
                            <span class="text-danger" id="roll-error" role="alert">
                                <strong></strong>
                            </span>
                            @if ($errors->has('marriage_cert_no'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('marriage_cert_no') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="input-effect sm2_mb_20 md_mb_20">
                            <input   class="primary-input" type="text" id="marriage_off_minister" name="marriage_off_minister"   value="{{$student->marriage_off_minister}}">
                            <label>@lang('student.marriage_off_minister')  @if(is_required('marriage_off_minister')==true) <span> *</span> @endif</label>
                       
                            <span class="focus-border"></span>
                            <span class="text-danger" id="roll-error" role="alert">
                                <strong></strong>
                            </span>
                            @if ($errors->has('marriage_off_minister'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('marriage_off_minister') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                   
                </div>
                

            </div>

            <div class="row  mt-40" style="display: none" id="familytextboxes">
                    <div class="col-lg-12">
                        <div class="main-title">
                            <h4 class="stu-sub-head">@lang('student.family_details')</h4>
                        </div>
                    </div>
            

                <div class="row mb-30 mt-30">
                 
                    <div class="col-lg-3">
                       <div class="input-effect sm2_mb_20 md_mb_20">
                        <input   class="primary-input" type="text" id="spouse_name" name="spouse_name" value="{{$student->spouse_name}}">
                            <label>@lang('student.spouse_name')@if(is_required('spouse_name')==true) <span> *</span> @endif</label>
                            <span class="focus-border textarea"></span>
                            @if ($errors->has('spouse_name'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('spouse_name') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
            
                  
                    <div class="col-lg-2">
                        <div class="input-effect sm2_mb_20 md_mb_20">
                                  <input class="primary-input date form-control{{ $errors->has('spouse_date_of_birth') ? ' is-invalid' : '' }}" id="startDate" type="text"
                            name="spouse_date_of_birth" value="{{$student->spouse_date_of_birth}}">
                            <label>@lang('student.spouse_date_of_birth') @if(is_required('spouse_date_of_birth')==true) <span> *</span> @endif</label>
                            <span class="focus-border textarea"></span>
                            @if ($errors->has('spouse_date_of_birth'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('spouse_date_of_birth') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="input-effect sm2_mb_20 md_mb_20">
                            <input   class="primary-input" type="text" id="spouse_chucrh" name="spouse_chucrh"  value="{{$student->spouse_chucrh}}">
                   
                            <label>@lang('student.spouse_chucrh') @if(is_required('spouse_chucrh')==true) <span> *</span> @endif</label>
                            <span class="focus-border textarea"></span>
                            @if ($errors->has('spouse_chucrh'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('spouse_chucrh') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>

                  
                    <div class="col-lg-2">
                        <div class="input-effect sm2_mb_20 md_mb_20">
                            <input   class="primary-input" type="text" id="child_name1" name="child_name1"  value="{{$student->child_name1}}">
                   
                                 <label>@lang('student.child_name1') @if(is_required('child_name1')==true) <span> *</span> @endif</label>
                            <span class="focus-border textarea"></span>
                            @if ($errors->has('child_name1'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('child_name1') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                 

                    <div class="col-lg-2">
                        <div class="input-effect sm2_mb_20 md_mb_20">
                            <input   class="primary-input" type="text" id="child_name2" name="child_name2"  value="{{$student->child_name2}}">
                               <label>@lang('student.child_name2') @if(is_required('child_name2')==true) <span> *</span> @endif</label>
                            <span class="focus-border textarea"></span>
                            @if ($errors->has('child_name2'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('child_name2') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>

  
                </div>
            </div>


                    <div class="row mt-40" style="display: none" id="studenttextboxes">
                        <div class="col-lg-12">
                            <div class="main-title">
                                <h4 class="stu-sub-head">@lang('student.school_details')</h4>
                            </div>
                        </div>
                  

                    <div class="row mb-30 mt-30">
                        @if(is_show('previous_school_details'))
                        <div class="col-lg-3">
                           <div class="input-effect sm2_mb_20 md_mb_20">
                            <input   class="primary-input" type="text" id="previous_school_details" name="student_church_name"  value="{{old('previous_school_details')}}">
                                <label>@lang('student.previous_school_details')@if(is_required('previous_school_details')==true) <span> *</span> @endif</label>
                                <span class="focus-border textarea"></span>
                                @if ($errors->has('previous_school_details'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('previous_school_details') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        @endif
                      
                        <div class="col-lg-2">
                            <div class="input-effect sm2_mb_20 md_mb_20">
                                      <input class="primary-input date form-control{{ $errors->has('school_admission_date') ? ' is-invalid' : '' }}" id="startDate" type="text"
                                name="school_admission_date" value="{{old('school_admission_date')}}" autocomplete="off">
                                <label>@lang('student.school_admission_date') @if(is_required('school_admission_date')==true) <span> *</span> @endif</label>
                                <span class="focus-border textarea"></span>
                                @if ($errors->has('school_admission_date'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('school_admission_date') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="input-effect sm2_mb_20 md_mb_20">
                                      <input class="primary-input date form-control{{ $errors->has('school_completion_date') ? ' is-invalid' : '' }}" id="startDate" type="text"
                                name="school_completion_date" value="{{old('school_completion_date')}}" autocomplete="off">
                                <label>@lang('student.school_completion_date') @if(is_required('school_completion_date')==true) <span> *</span> @endif</label>
                                <span class="focus-border textarea"></span>
                                @if ($errors->has('school_completion_date'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('school_completion_date') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                      
                        <div class="col-lg-2">
                            <div class="input-effect sm2_mb_20 md_mb_20">
                                <input   class="primary-input" type="text" id="school_telephone" name="school_telephone"  value="{{old('school_telephone')}}">
                       
                                     <label>@lang('student.school_telephone') @if(is_required('school_telephone')==true) <span> *</span> @endif</label>
                                <span class="focus-border textarea"></span>
                                @if ($errors->has('school_telephone'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('school_telephone') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                     

                        <div class="col-lg-2">
                            <div class="input-effect sm2_mb_20 md_mb_20">
                                <input   class="primary-input" type="text" id="school_location" name="school_location"  value="{{old('school_location')}}">
                                   <label>@lang('student.school_location') @if(is_required('school_location')==true) <span> *</span> @endif</label>
                                <span class="focus-border textarea"></span>
                                @if ($errors->has('school_location'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('school_location') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                    </div>
                   

                </div>



                  
                  <div class="row  mt-30">
                        <div class="col-lg-12">
                            <div class="main-title">
                                <h4 class="stu-sub-head">@lang('student.group_details')</h4>
                            </div>
                        </div>
                       
                    </div>

 


                     <div class="row mt-30">
                        @if(is_show('document_file_1'))
                        <div class="col-lg-3">
                            <div class="input-effect sm2_mb_20 md_mb_20">
                                <select class="niceSelect w-100 bb" name="document_title_1"  value="{{$student->document_title_1}}">
                                    <option value="{{$student->document_title_1}}">{{$student->document_title_1}}</option>
                                    <option value="CHOIR UNION">CHOIR UNION</option>
                                    <option value="SHEKINA">SHEKINA</option>
                                    <option value="BSPG">BIBLE STUDIES AND PRAYER GROUP</option>
                                    <option value="SINGING BAND">SINGING BAND</option>
                                    <option value="MEDIA TEAM">MEDIA TEAM</option>
                                </select>
                                <label>@lang('student.document_01_title') @if(is_required('document_file_1')==true) <span> *</span> @endif</label>
                                <span class="focus-border"></span>
                                @if ($errors->has('document_title_1'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('document_title_1') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        @endif
                        @if(is_show('document_file_2'))
                        <div class="col-lg-3">
                            <div class="input-effect sm2_mb_20 md_mb_20">
                                <select class="niceSelect w-100 bb" name="document_title_2"  value="{{$student->document_title_2}}">
                                    <option value="{{$student->document_title_2}}">{{$student->document_title_2}}</option>
                              
                                          <option value="CHOIR UNION">CHOIR UNION</option>
                                    <option value="SHEKINA">SHEKINA</option>
                                    <option value="BSPG">BIBLE STUDIES AND PRAYER GROUP</option>
                                    <option value="SINGING BAND">SINGING BAND</option>
                                    <option value="MEDIA TEAM">MEDIA TEAM</option>
                                </select>
                                <label>@lang('student.document_02_title') @if(is_required('document_file_2')==true) <span> *</span> @endif</label>
                                <span class="focus-border"></span>
                                @if ($errors->has('document_title_2'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('document_title_2') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        @endif
                        @if(is_show('document_file_3'))
                        <div class="col-lg-3">
                            <div class="input-effect sm2_mb_20 md_mb_20">
                                <select class="niceSelect w-100 bb" name="document_title_3"  value="{{$student->document_title_3}}">
                                    <option value="{{$student->document_title_3}}">{{$student->document_title_3}}</option>
                                             <option value="CHOIR UNION">CHOIR UNION</option>
                                    <option value="SHEKINA">SHEKINA</option>
                                    <option value="BSPG">BIBLE STUDIES AND PRAYER GROUP</option>
                                    <option value="SINGING BAND">SINGING BAND</option>
                                    <option value="MEDIA TEAM">MEDIA TEAM</option>
                                </select>
                                <label>@lang('student.document_03_title') @if(is_required('document_file_3')==true) <span> *</span> @endif</label>
                                <span class="focus-border"></span>
                                @if ($errors->has('document_title_3'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('document_title_3') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        @endif
                     
                        <div class="col-lg-3">
                            <div class="input-effect sm2_mb_20 md_mb_20">
                                <select class="niceSelect w-100 bb" name="document_title_4"  value="{{$student->document_title_4}}">
                                    <option value="{{$student->document_title_4}}">{{$student->document_title_4}}</option>
                              
                                    <option value="CHOIR UNION">CHOIR UNION</option>
                                    <option value="SHEKINA">SHEKINA</option>
                                    <option value="BSPG">BIBLE STUDIES AND PRAYER GROUP</option>
                                    <option value="SINGING BAND">SINGING BAND</option>
                                    <option value="MEDIA TEAM">MEDIA TEAM</option>
                                </select>
                                <label>@lang('student.document_04_title') @if(is_required('document_file_4')==true) <span> *</span> @endif</label>
                                <span class="focus-border"></span>
                                @if ($errors->has('document_title_4'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('document_title_4') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                    
                    </div>
              
                </div>
            </div>










 
                        
                        <div class="row mt-5">
                            <div class="col-lg-12 text-center">
                                <button class="primary-btn fix-gr-bg submit">
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
                        <button type="button" class="primary-btn tr-bg" data-dismiss="modal">@lang('common.cancel')</button>
                        <button type="button" class="primary-btn fix-gr-bg" data-dismiss="modal" id="yesRemoveSibling">@lang('common.delete')</button>
                        
                    </div>
                </div>

            </div>
        </div>
    </div>


 {{-- student photo --}}
 <input type="text" id="STurl" value="{{ route('student_update_pic',$student->id)}}" hidden>
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
                <button class="btn rotate float-lef" data-deg="90" > 
                <i class="ti-back-right"></i></button>
                <button class="btn rotate float-right" data-deg="-90" > 
                <i class="ti-back-left"></i></button>
                <hr>                
                <a href="javascript:;" class="primary-btn fix-gr-bg pull-right" id="upload_logo">Crop</a>
            </div>
        </div>
    </div>
</div>
{{-- end student photo --}}

 {{-- father photo --}}

 <div class="modal" id="FatherPic">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Crop Image And Upload</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <!-- Modal body -->
            <div class="modal-body">
                <div id="fa_resize"></div>
                <button class="btn rotate float-lef" data-deg="90" > 
                <i class="ti-back-right"></i></button>
                <button class="btn rotate float-right" data-deg="-90" > 
                <i class="ti-back-left"></i></button>
                <hr>                
                <a href="javascript:;" class="primary-btn fix-gr-bg pull-right" id="FatherPic_logo">Crop</a>
            </div>
        </div>
    </div>
</div>
{{-- end father photo --}}
 {{-- mother photo --}}

 <div class="modal" id="MotherPic">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Crop Image And Upload</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <!-- Modal body -->
            <div class="modal-body">
                <div id="ma_resize"></div>
                <button class="btn rotate float-lef" data-deg="90" > 
                <i class="ti-back-right"></i></button>
                <button class="btn rotate float-right" data-deg="-90" > 
                <i class="ti-back-left"></i></button>
                <hr>                
                <a href="javascript:;" class="primary-btn fix-gr-bg pull-right" id="Mother_logo">Crop</a>
            </div>
        </div>
    </div>
</div>
{{-- end mother photo --}}
 {{-- mother photo --}}

 <div class="modal" id="GurdianPic">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Crop Image And Upload</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <!-- Modal body -->
            <div class="modal-body">
                <div id="Gu_resize"></div>
                <button class="btn rotate float-lef" data-deg="90" > 
                <i class="ti-back-right"></i></button>
                <button class="btn rotate float-right" data-deg="-90" > 
                <i class="ti-back-left"></i></button>
                <hr>
                
                <a href="javascript:;" class="primary-btn fix-gr-bg pull-right" id="Gurdian_logo">Crop</a>
            </div>
        </div>
    </div>
</div>
{{-- end mother photo --}}

@endsection
@section('script')
<script src="{{asset('public/backEnd/')}}/js/croppie.js"></script>
<script src="{{asset('public/backEnd/')}}/js/st_addmision.js"></script>
<script>
    $(document).ready(function(){
        
        $(document).on('change','.cutom-photo',function(){
            let v = $(this).val();
            let v1 = $(this).data("id");
            console.log(v,v1);
            getFileName(v, v1);

        });

        function getFileName(value, placeholder){
            if (value) {
                var startIndex = (value.indexOf('\\') >= 0 ? value.lastIndexOf('\\') : value.lastIndexOf('/'));
                var filename = value.substring(startIndex);
                if (filename.indexOf('\\') === 0 || filename.indexOf('/') === 0) {
                    filename = filename.substring(1);
                }
                $(placeholder).attr('placeholder', '');
                $(placeholder).attr('placeholder', filename);
            }
        }

        
    })
</script>
@endsection