                                {{-- multi exam div --}}
                              
                                    @if(moduleStatusCheck('University'))
                                    <div class="row  mt-25">
                                        <div class="col-lg-12">
                                            <label>@lang('common.select_exam_type') *</label>
                                            @foreach($exams_types as $exams_type)
                                                <div class="input-effect">
                                                    <input type="checkbox" id="exams_types_{{@$exams_type->id}}" class="common-checkbox exam-checkbox" name="exams_types[]" value="{{@$exams_type->id}}" {{isset($selected_exam_type_id)? ($exams_type->id == $selected_exam_type_id? 'checked':''):''}}>
                                                    <label for="exams_types_{{@$exams_type->id}}">{{@$exams_type->title}}</label>
                                                </div>
                                            @endforeach
                                            <div class="input-effect">
                                                <input type="checkbox" id="all_exams" class="common-checkbox" name="all_exams[]" value="0" {{ (is_array(old('age_group_ids')) and in_array($class->id, old('age_group_ids'))) ? ' checked' : '' }}>
                                                <label for="all_exams">@lang('exam.all_select')</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            @if($errors->has('exams_types'))
                                                <span class="text-danger validate-textarea-checkbox" role="alert">
                                                    <strong>{{ $errors->first('exams_types') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    @includeIf('university::common.session_faculty_depart_academic_semester_level',
                                    ['required' => 
                                        ['USN', 'UD', 'UA', 'US', 'USL'],
                                        'div'=>'col-lg-12','row'=>1,'hide'=> ['USUB'],'mt'=>'mt-0'
                                    ])

                                    <label class="mt-30">@lang('university::un.select_subject') *</label>
                                    <div class="row" id="universityExamSubejct"></div>
                                        <div class="text-center loader loader_style" id="unSubjectLoader">
                                            <img src="{{asset('public/backEnd/img/demo_wait.gif')}}" alt="loader" height="60px" width="60px">
                                        </div>
                                @else
                                    <div class="row mt-25">
                                        <div class="col-lg-12">
                                            <label>@lang('common.select_exam_type') *</label>
                                            @foreach($exams_types as $exams_type)
                                                <div class="input-effect">
                                                    <input type="checkbox" id="exams_types_{{@$exams_type->id}}" class="common-checkbox exam-checkbox" name="exams_types[]" value="{{@$exams_type->id}}" {{isset($selected_exam_type_id)? ($exams_type->id == $selected_exam_type_id? 'checked':''):''}}>
                                                    <label for="exams_types_{{@$exams_type->id}}">{{@$exams_type->title}}</label>
                                                </div>
                                            @endforeach
                                            <div class="input-effect">
                                                <input type="checkbox" id="all_exams" class="common-checkbox" name="all_exams[]" value="0" {{ (is_array(old('age_group_ids')) and in_array($class->id, old('age_group_ids'))) ? ' checked' : '' }}>
                                                <label for="all_exams">@lang('exam.all_select')</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            @if($errors->has('exams_types'))
                                                <span class="text-danger validate-textarea-checkbox" role="alert">
                                                    <strong>{{ $errors->first('exams_types') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mt-25">
                                        <div class="col-lg-12">
                                            <select class="w-100 bb niceSelect form-control {{ $errors->has('age_group_ids') ? ' is-invalid' : '' }}" id="exam_class" name="age_group_ids">
                                                <option data-display="@lang('common.select_class') *" value="">@lang('common.select_class') *</option>
                                                @foreach($classes as $class)
                                                <option value="{{@$class->id}}">{{@$class->age_group_name}}</option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('age_group_ids'))
                                                <span class="invalid-feedback invalid-select" role="alert">
                                                    <strong>{{ $errors->first('age_group_ids') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mt-25" id="exam_subejct">
                                    </div>
                                    @endif 

<script>
      if ($(".niceSelect").length) {
      $(".niceSelect").niceSelect();
      }
</script>
@if(moduleStatusCheck('University'))
<script src="{{ asset('Modules/University/Resources/assets/js/app.js') }}"></script>
@else 
<script src="{{ asset('public/backEnd/js/custom.js') }}"></script>
<script src="{{ asset('public/backEnd/js/developer.js') }}"></script>
@endif 

                               
                                  {{-- multi exam end --}}