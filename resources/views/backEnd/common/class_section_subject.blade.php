@php
    $div = isset($div) ? $div : 'col-lg-3';
    $mt = isset($mt) ? $mt : 'mt-30-md';
    $required = $required ?? [];
    $selected = isset($selected) ? $selected : null;
    
    $age_group_id = $selected && isset($selected['age_group_id']) ? $selected['age_group_id'] : null;
 
    $mgender_id = $selected && isset($selected['mgender_id']) ? $selected['mgender_id'] : null;
    $subject_id = $selected && isset($selected['subject_id']) ? $selected['subject_id'] : null;
    $sections = $age_group_id ? sections($age_group_id) : null;
    $subjects = $age_group_id && $mgender_id ? subjects($age_group_id, $mgender_id) : null;
    $visiable = $visiable ?? [];
@endphp

@if(in_array('class', $visiable))
<div class="{{ $div . ' ' . $mt }}">
    <select class="w-100 bb niceSelect form-control {{ $errors->has('class') ? ' is-invalid' : '' }}" id="select_class"
        name="age_group_id">
        <option data-display="@lang('common.select_class') {{ in_array('class', $required) ? ' *' : '' }}" value="">
            @lang('common.select_class') {{ in_array('class', $required) ? ' *' : '' }}</option>
        @if (isset($classes))
            @foreach ($classes as $class)
                <option value="{{ $class->id }}"
                    {{ isset($age_group_id) ? ($age_group_id == $class->id ? 'selected' : '') : '' }}>
                    {{ $class->age_group_name }}</option>
            @endforeach
        @endif
    </select>

    @if ($errors->has('age_group_id'))
        <span class="invalid-feedback invalid-select d-block" role="alert">
            <strong>{{ $errors->first('age_group_id') }}</strong>
        </span>
    @endif
</div>
@endif
@if(in_array('section', $visiable))
<div class="{{ $div . ' ' . $mt }}" id="select_section_div">
    <select class="w-100 bb niceSelect form-control{{ $errors->has('section') ? ' is-invalid' : '' }} select_section"
        id="select_section" name="mgender_id">
        <option data-display="@lang('common.select_section') {{ in_array('section', $required) ? '*' : '' }}" value="">
            @lang('common.select_section') {{ in_array('section', $required) ? '*' : '' }}</option>
        @isset($sections)
            @foreach ($sections as $section)
                <option value="{{ $section->id }}"
                    {{ isset($mgender_id) ? ($mgender_id == $section->mgender_id ? 'selected' : '') : '' }}>
                    {{ $section->sectionName->mgender_name }}
                </option>
            @endforeach
        @endisset
    </select>
    <div class="pull-right loader" id="select_section_loader" style="margin-top: -30px;padding-right: 21px;">
        <img src="{{ asset('public/backEnd/img/demo_wait.gif') }}" alt="" style="width: 28px;height:28px;">
    </div>
    @if ($errors->has('mgender_id'))
        <span class="invalid-feedback invalid-select d-block" role="alert">
            <strong>{{ $errors->first('mgender_id') }}</strong>
        </span>
    @endif
</div>
@endif

@if(in_array('subject', $visiable))
<div class="{{ $div . ' ' . $mt }}" id="select_subject_div">
    <select class="w-100 bb niceSelect form-control{{ $errors->has('subject') ? ' is-invalid' : '' }} select_subject"
        id="select_subject" name="subject_id">
        <option data-display="@lang('common.select_subjects') {{ in_array('subject', $required) ? ' *' : '' }}" value="">
            @lang('common.select_subjects') {{ in_array('subject', $required) ? ' *' : '' }}</option>
        @isset($subjects)
            @foreach ($subjects as $subject)
                <option value="{{ $subject->subject_id }}"
                    {{ isset($subject_id) ? ($subject_id == $subject->subject_id ? 'selected' : '') : '' }}>
                    {{ $subject->subject->subject_name }}</option>
            @endforeach
        @endisset
    </select>
    <div class="pull-right loader" id="select_subject_loader" style="margin-top: -30px;padding-right: 21px;">
        <img src="{{ asset('public/backEnd/img/demo_wait.gif') }}" alt="" style="width: 28px;height:28px;">
    </div>
    @if ($errors->has('subject_id'))
        <span class="invalid-feedback invalid-select d-block" role="alert">
            <strong>{{ $errors->first('subject_id') }}</strong>
        </span>
    @endif
</div>
@endif