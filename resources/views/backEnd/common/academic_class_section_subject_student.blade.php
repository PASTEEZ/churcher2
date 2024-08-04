@php
    $div = isset($div) ? $div : 'col-lg-3';
    $mt = isset($mt) ? $mt : 'mt-30-md';
    $required = $required ?? [];
    $selected = isset($selected) ? $selected : null;
    $church_year = $selected && isset($selected['church_year']) ? $selected['church_year']: null;
    $age_group_id = $selected && isset($selected['age_group_id']) ? $selected['age_group_id'] : null;
    $mgender_id = $selected && isset($selected['mgender_id']) ? $selected['mgender_id'] : null;
    $subject_id = $selected && isset($selected['subject_id']) ? $selected['subject_id'] : null;
    $member_id = $selected && isset($selected['member_id']) ? $selected['member_id'] : null;
    
    if($church_year) {
        $classes  =  classes($church_year) ?? null;
        $sections = $age_group_id ? sections($age_group_id, $church_year) : null;
        $subjects = $age_group_id && $mgender_id ? subjects($age_group_id, $mgender_id, $church_year) : null;
        $students = $age_group_id && $mgender_id ? students($age_group_id, $mgender_id, $church_year) : null;
    }else {
        $sections = $age_group_id ? sections($age_group_id) : null;
        $subjects = $age_group_id && $mgender_id ? subjects($age_group_id, $mgender_id) : null;
    }
    $visiable = $visiable ?? [];

@endphp
@if(in_array('academic', $visiable))
<div class="{{ $div . ' ' . $mt }}">
    <div class="input-effect sm2_mb_20 md_mb_20">
        <select class="niceSelect w-100 bb form-control{{ $errors->has('church_year') ? ' is-invalid' : '' }} common_church_year"
            name="church_year" id="common_church_year">
            <option data-display="@lang('common.church_year') {{ in_array('academic', $required) ? '*' :'' }}" value="">@lang('common.church_year') {{ in_array('academic', $required) ? '*' :'' }}
            </option>
            @isset($sessions)

                @foreach ($sessions as $session)
                    <option value="{{ $session->id }}"
                        {{ isset($church_year) && $church_year == $session->id ? 'selected' : (getAcademicId() == $session->id ? 'selected':'')}}>
                        {{ $session->year }}[{{ $session->title }}]</option>
                @endforeach
            @endisset

        </select>
        <span class="focus-border"></span>
        @if ($errors->has('church_year'))
            <span class="invalid-feedback invalid-select d-block" role="alert">
                <strong>{{ $errors->first('church_year') }}</strong>
            </span>
        @endif
    </div>
</div>
@endif
@if(in_array('class', $visiable))
<div class="{{ $div . ' ' . $mt }}" id="common_select_class_div">
    <select class="w-100 bb niceSelect form-control {{ $errors->has('class') ? ' is-invalid' : '' }}" id="common_select_class"
        name="age_group_id">
        <option data-display="@lang('common.select_class') {{ in_array('class', $required) ? ' *':'' }}" value="">@lang('common.select_class') {{ in_array('class', $required) ? ' *':'' }}</option>
        @if (isset($classes))
            @foreach ($classes as $class)
                <option value="{{ $class->id }}" {{ isset($age_group_id) ? ($age_group_id == $class->id ? 'selected' : '') : '' }}>
                    {{ $class->age_group_name }}</option>
            @endforeach
        @endif
    </select>
    <div class="pull-right loader loader_style" id="common_select_class_loader">
        <img class="loader_img_style" src="{{asset('public/backEnd/img/demo_wait.gif')}}" alt="loader">
    </div>
    <span class="focus-border"></span>
    @if ($errors->has('age_group_id'))
        <span class="invalid-feedback invalid-select d-block" role="alert">
            <strong>{{ $errors->first('age_group_id') }}</strong>
        </span>
    @endif
</div>
@endif
@if(in_array('section', $visiable))
<div class="{{ $div . ' ' . $mt }}" id="common_select_section_div">
    <select class="w-100 bb niceSelect form-control{{ $errors->has('section') ? ' is-invalid' : '' }} select_section"
        id="common_select_section" name="mgender_id">
        <option data-display="@lang('common.select_section') {{ in_array('section', $required) ? '*' :'' }}" value="">@lang('common.select_section') {{ in_array('section', $required) ? '*' :'' }}</option>
        @isset($sections)
            @foreach ($sections as $section)
                <option value="{{ $section->id }}"
                    {{ isset($mgender_id) ? ($mgender_id == $section->mgender_id ? 'selected' : '') : '' }}>{{ $section->sectionName->mgender_name }}
                </option>
            @endforeach
        @endisset
    </select>
    <div class="pull-right loader" id="common_select_section_loader" style="margin-top: -30px;padding-right: 21px;">
        <img src="{{ asset('public/backEnd/img/demo_wait.gif') }}" alt="" style="width: 28px;height:28px;">
    </div>
    <span class="focus-border"></span>

    @if ($errors->has('mgender_id'))
        <span class="invalid-feedback invalid-select d-block" role="alert">
            <strong>{{ $errors->first('mgender_id') }}</strong>
        </span>
    @endif
</div>
@endif
@if(in_array('subject', $visiable))
<div class="{{ $div . ' ' . $mt }}" id="common_select_subject_div">
    <select class="w-100 bb niceSelect form-control{{ $errors->has('subject') ? ' is-invalid' : '' }} select_subject"
        id="common_select_subject" name="subject_id">
        <option data-display="@lang('common.select_subjects') {{ in_array('subject', $required) ? ' *' :'' }}" value="">@lang('common.select_subjects') {{ in_array('subject', $required) ? ' *' :'' }}</option>
        @isset($subjects)
            @foreach ($subjects as $subject)
                <option value="{{ $subject->subject_id }}"
                    {{ isset($subject_id) ? ($subject_id == $subject->subject_id ? 'selected' : '') : '' }}>
                    {{ $subject->subject->subject_name }}</option>
            @endforeach
        @endisset
    </select>
    <div class="pull-right loader" id="common_select_subject_loader" style="margin-top: -30px;padding-right: 21px;">
        <img src="{{ asset('public/backEnd/img/demo_wait.gif') }}" alt="" style="width: 28px;height:28px;">
    </div>
    <span class="focus-border"></span>
    @if ($errors->has('subject_id'))
        <span class="invalid-feedback invalid-select d-block" role="alert">
            <strong>{{ $errors->first('subject_id') }}</strong>
        </span>
    @endif
</div>
@endif
@if(in_array('student', $visiable))
<div class="{{ $div . ' ' . $mt }}" id="common_select_student_div">
    <select
        class="w-100 bb niceSelect form-control{{ $errors->has('student') ? ' is-invalid' : '' }}"
        id="common_select_student" name="student">
        <option data-display="@lang('reports.select_student') {{ in_array('student', $required) ? '*' :'' }}" value="">@lang('reports.select_student') <span>{{ in_array('student', $required) ? '*' :'' }}</span>
        </option>
        @isset($students)
            @foreach ($students as $student)
                <option value="{{ $student->id }}"
                    {{ isset($member_id) ? ($member_id == $student->id ? 'selected' : '') : '' }}>
                    {{ $student->full_name }}
                </option>
            @endforeach
        @endisset
    </select>
    
    <div class="pull-right loader loader_style" id="common_select_student_loader">
        <img class="loader_img_style" src="{{ asset('public/backEnd/img/demo_wait.gif') }}"
            alt="loader">
    </div>
    <span class="focus-border"></span>
    @if ($errors->has('student'))
        <span class="invalid-feedback invalid-select d-block" role="alert">
            <strong>{{ $errors->first('student') }}</strong>
        </span>
    @endif
</div>
@endif

@push('script')
<script>
     $(document).ready(function() {
        let class_required = "{{ in_array('class', $required) ? ' *' :'' }}";
        let section_required = "{{ in_array('section', $required) ? ' *' :'' }}";
        let subject_required = "{{ in_array('subject', $required) ? ' *' :'' }}";
        let student_required = "{{ in_array('student', $required) ? ' *' :'' }}";
        $("#common_church_year").on(
            "change",
            function() {
                var url = $("#url").val();
                var i = 0;
                var formData = {
                    id: $(this).val(),
                };
                
                // get class
                $.ajax({
                    type: "GET",
                    data: formData,
                    dataType: "json",
                    url: url + "/" + "academic-year-get-class",

                    beforeSend: function() {
                        $('#common_select_class_loader').addClass('pre_loader').removeClass('loader');
                    },

                    success: function(data) {
                        $("#common_select_class").empty().append(
                            $("<option>", {
                                value:  '',
                                text: window.jsLang('select_class') + class_required,
                            })
                        );

                        if (data[0].length) {
                            $.each(data[0], function(i, className) {
                                $("#common_select_class").append(
                                    $("<option>", {
                                        value: className.id,
                                        text: className.age_group_name,
                                    })
                                );
                            });
                        } 
                        $('#common_select_class').niceSelect('update');
                        $('#common_select_class').trigger('change');
                    },
                    error: function(data) {
                        console.log('Error:', data);
                    },
                    complete: function() {
                        i--;
                        if (i <= 0) {
                            $('#common_select_class_loader').removeClass('pre_loader').addClass('loader');
                        }
                    }
                });
            }
        );
        
        $("#common_select_class").on("change", function() {

            var url = $("#url").val();
            var i = 0;
            var formData = {
                id: $(this).val(),
            };
            $.ajax({
                type: "GET",
                data: formData,
                dataType: "json",
                url: url + "/" + "ajaxStudentPromoteSection",

                beforeSend: function() {
                    $('#common_select_section_loader').addClass('pre_loader').removeClass('loader');
                },
                success: function(data) {
                    $("#common_select_section").empty().append(
                            $("<option>", {
                                value:  '',
                                text: window.jsLang('select_section') + section_required,
                            })
                        );                 
                                 
                        if (data[0].length) {
                            $.each(data[0], function(i, section) {
                                $("#common_select_section").append(
                                    $("<option>", {
                                        value: section.id,
                                        text: section.mgender_name,
                                    })
                                );
                            });
                        } 
                        $('#common_select_section').niceSelect('update');
                        $('#common_select_section').trigger('change'); 
                   
                },
                error: function(data) {
                    console.log("Error:", data);
                },
                complete: function() {
                    i--;
                    if (i <= 0) {
                        $('#common_select_section_loader').removeClass('pre_loader').addClass('loader');
                    }
                }
            });
        });
        $("#common_select_section").on("change", function() {
            var url = $("#url").val();
            var i = 0;
            var select_class = $("#common_select_class").val();

            var formData = {
                section: $(this).val(),
                class: $("#common_select_class").val(),
            };
            // get section for student
            $.ajax({
                type: "GET",
                data: formData,
                dataType: "json",
                url: url + "/" + "ajaxSelectStudent",

                beforeSend: function() {
                    $('#common_select_student_loader').addClass('pre_loader').removeClass('loader');
                },

                success: function(data) {
                   
                    $("#common_select_student").empty().append(
                            $("<option>", {
                                value:  '',
                                text: window.jsLang('select_student') + student_required,
                            })
                        );                 
                                 
                        if (data[0].length) {
                            $.each(data[0], function(i, student) {
                                $("#common_select_student").append(
                                    $("<option>", {
                                        value: student.id,
                                        text: student.full_name,
                                    })
                                );
                            });
                        } 
                        $('#common_select_student').niceSelect('update');
                        $('#common_select_student').trigger('change'); 
                },
                error: function(data) {
                    console.log("Error:", data);
                },
                complete: function() {
                    i--;
                    if (i <= 0) {
                        $('#common_select_student_loader').removeClass('pre_loader').addClass('loader');
                    }
                }
            });
        });
    });
</script>
    
@endpush