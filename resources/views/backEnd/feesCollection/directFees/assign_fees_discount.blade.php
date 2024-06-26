@extends('backEnd.master')
@section('title') 
@lang('fees.assign_fees_discount')
@endsection
@section('mainContent')
    <section class="admin-visitor-area">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="main-title">
                        <h3 class="mb-30">@lang('common.select_criteria')</h3>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">                   
                    <div class="white-box">
                        {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'fees-discount-assign-search', 'method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'search_studentA']) }}
                        <div class="row">
                            <input type="hidden" name="url" id="url" value="{{URL::to('/')}}">
                            <input type="hidden" name="fees_discount_id" id="fees_discount_id" value="{{$fees_discount_id}}">

                            @if(moduleStatusCheck('University'))
                                    @includeIf('university::common.session_faculty_depart_academic_semester_level',['hide'=>['USUB']])
                                @else
                                <div class="col-lg-3 mt-30-md">
                                    <select class="niceSelect w-100 bb form-control{{ $errors->has('class') ? ' is-invalid' : '' }}" id="select_class" name="class">
                                        <option data-display="@lang('common.select_class')" value="">@lang('common.select_class')*</option>
                                        @foreach($classes as $class)
                                            <option value="{{$class->id}}" {{isset($age_group_id)? ($age_group_id == $class->id? 'selected':''):''}}>{{$class->age_group_name}}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('class'))
                                        <span class="invalid-feedback invalid-select" role="alert">
                                            <strong>{{ $errors->first('class') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <div class="col-lg-3 mt-30-md" id="select_section_div">
                                    <select class="niceSelect w-100 bb form-control{{ $errors->has('section') ? ' is-invalid' : '' }}" id="select_section" name="section">
                                        <option data-display="@lang('common.select_section')" value="">@lang('common.select_section')</option>
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
                                <div class="col-lg-3 mt-30-md">
                                    <select class="niceSelect w-100 bb form-control{{ $errors->has('category') ? ' is-invalid' : '' }}" name="category">
                                        <option data-display="@lang('fees.select_category')" value="">@lang('fees.select_category')</option>
                                        @foreach($categories as $category)
                                            <option value="{{$category->id}}")}} {{isset($category_id)? ($category_id == $category->id? 'selected':''):''}}>{{$category->category_name}}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('category'))
                                        <span class="invalid-feedback invalid-select" role="alert">
                                            <strong>{{ $errors->first('category') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <div class="col-lg-3 mt-30-md">
                                    <select class="niceSelect w-100 bb form-control{{ $errors->has('group') ? ' is-invalid' : '' }}" name="group">
                                        <option data-display="@lang('fees.select_group')" value="">@lang('fees.select_group') </option>
                                        @foreach($groups as $group)
                                            <option value="{{$group->id}}" {{isset($group_id)? ($group_id == $group->id? 'selected':''):''}}>{{$group->group}}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('group'))
                                        <span class="invalid-feedback invalid-select" role="alert">
                                            <strong>{{ $errors->first('group') }}</strong>
                                        </span>
                                    @endif
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
            @if(!empty($students))
                {{ Form::open(['class' => 'form-horizontal', 'method' => 'POST', 'route' => 'directFees.fees-discount-assign-store'])}}
                    <div class="row mt-40">
                        <div class="col-lg-12">
                            <div class="row mb-30">
                                <div class="col-lg- no-gutters">
                                    <div class="main-title">
                                        <h3 class="mb-0">@lang('fees.assign_fees_discount') ( @lang('fees.discount_will_be_applied_for_all_unpaid_installment_fees') )</h3> 
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="fees_discount_id" value="{{$fees_discount_id}}" id="fees_discount_id">
                            <div class="row">
                                <div class="col-lg-4">
                                    <table id="table_id_table" class="display school-table" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <tr>
                                                    <th>@lang('fees.fees_discount')</th>
                                                    <th>@lang('fees.amount')</th>
                                                </tr>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{{$fees_discount->name}}</td>
                                                <td>{{$fees_discount->amount}}  </td>
                                            </tr>
                                        </tbody>
                                       
                                    </table>
                                   
                                </div>
                                <div class="col-lg-8">                                   
                                    <div class="table-responsive">
                                        <table  class="display school-table school-table-style" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th width="10%">
                                                        <input type="checkbox" id="checkAll" class="common-checkbox" name="checkAll"  
                                                            @php
                                                                if(count($already_assigned) > 0){
                                                                    if(count($students) == count($already_assigned)){
                                                                        echo 'checked';
                                                                    }
                                                                }
                                                            @endphp>
                                                        <label for="checkAll"> @lang('common.all')</label>
                                                    </th>
                                                    <th width="20%">@lang('student.member_name')</th>
                                                    <th width="10%">@lang('student.registration_no')</th>
                                                    <th width="15%">@lang('common.class_sec')</th>
                                                
                                                    <th width="10%">@lang('student.category')</th>
                                                    <th width="5%">@lang('common.gender')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($students as $student)
                                                    <tr>
                                                        <td>
                                                            
                                                            <input type="checkbox" id="student.{{$student->id}}" {{@$show}} class="common-checkbox" name="data[{{$loop->index}}][checked]" value="1" {{in_array($student->id, $already_assigned) ? 'checked':''}}>
                                                            <label for="student.{{$student->id}}"></label>
                                                        </td>
                                                            <input type="hidden" name="data[{{$loop->index}}][age_group_id]" value="{{@$student->age_group_id}}">
                                                            <input type="hidden" name="data[{{$loop->index}}][mgender_id]" value="{{@$student->mgender_id}}">
                                                            <input type="hidden" name="data[{{$loop->index}}][record_id]" value="{{@$student->id}}">
                                                            <input type="hidden" name="data[{{$loop->index}}][member_id]" value="{{@$student->studentDetail->forwardBalance->id ?? $student->member_id}}">
                                                        <td>{{$student->studentDetail->full_name}} {{in_array($student->id, $already_assigned)}}</td>
                                                        <td>{{$student->studentDetail->registration_no}}</td>
                                                        <td>{{$student->class->age_group_name}}({{$student->section->mgender_name}})</td>
                                                        <td>{{$student->studentDetail->category!=""?$student->studentDetail->category->category_name:""}}</td>
                                                        <td>{{$student->studentDetail->gender!=""?$student->studentDetail->gender->base_setup_name:""}}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            @if($students->count() > 0)
                                                <tr>
                                                    <td colspan="7">
                                                        <div class="text-center">
                                                            <button type="submit" class="primary-btn fix-gr-bg mb-0" id="btn-assign-fees-discount">
                                                                <span class="ti-save pr"></span>
                                                                @lang('fees.assign_discount')
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                {{ Form::close() }}
            @endif
        </div>
    </div>
</section>
@endsection
