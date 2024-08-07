<div class="col-lg-12">
    {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'university.request-subject', 'method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'request_subject']) }}
    @foreach ($student_detail->orderByStudentRecords as $record)
    @if ($record->is_promote == 0  && !$record->unSemesterLabel->unExamAttendance)
    <button class="primary-btn-small-input primary-btn small fix-gr-bg pull-right" type="button" data-toggle="modal"
        data-target="#requestSubject"> <span class="ti-plus pr-2"></span> @lang('university::un.request_subject')</button>
    @endif
<div class="main-title">
    <h3 class="mb-2">{{ @$record->unSemesterLabel->title }}</h3>
  
</div>

<table id="" class="display school-table school-table-style" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>@lang('exam.subject') </th>
            <th>@lang('university::un.subject_type')</th>
            <th>@lang('common.teacher')</th>
            <th>@lang('university::un.pass_mark')</th>
            <th>@lang('university::un.hours')</th>
            <th>@lang('university::un.cost_hours')</th>
            <th>@lang('exam.total')</th>
            <th>@lang('common.status')</th>
            <th>@lang('common.action') </th>
        </tr>
    </thead>
    <tbody>

        @foreach ($record->unStudentSemesterWiseSubjects as $subject)
            <tr>
                @php
                    $result = labelWiseStudentResult($record, $subject->un_subject_id);
                    $assignDetail = Modules\University\Entities\UnSubjectAssignStudent::assignDetail($subject->un_subject_id, $subject->un_semester_label_id)
                @endphp
                <td> {{ @$subject->subject->subject_name . '[' . $subject->subject->subject_code . ']' }}</td>
                <td> {{ $subject->subject->subject_type }}</td>
                <td> {{ $assignDetail['teacher']->teacher->full_name }}</td>
                <td> {{ $subject->subject->pass_mark ? $subject->subject->pass_mark .'%' :'' }}</td>
                <td> {{ $subject->subject->number_of_hours }}</td>
                <td> {{ currency_format($assignDetail['amount']) }} </td>
                <td> {{ currency_format($subject->subject->number_of_hours * $assignDetail['amount']) }}</td>
                <td>{{ $record->is_promote == 0 ? __('common.ongoing') : __('common.completed') }}</td>
                <td> 
                     
                  
                </td>
            </tr>
            @if ($record->is_promote == 0  && !$record->unSemesterLabel->unExamAttendance)
            <div class="modal fade admin-query" id="deleteSubject_{{ $subject->id }}" >
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">@lang('common.delete')</h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>

                        <div class="modal-body">
                            <div class="text-center">
                                <h4>@lang('common.are_you_sure_to_delete')</h4>
                            </div>

                            <div class="mt-40 d-flex justify-content-between">
                                <button type="button" class="primary-btn tr-bg" data-dismiss="modal">@lang('common.cancel')</button>

                                <form action="{{route('university.subject.assign.delete')}}" method="POST">
                                    @csrf
                                    <input type="hidden" name="un_subject_id" value="{{ $subject->subject->id }}">
                                    <input type="hidden" name="member_id" value="{{ $record->member_id }}">
                                    <input type="hidden" name="record_id" value="{{ $record->id }}">
                                    <input type="hidden" name="un_semester_label_id" value="{{ $subject->un_semester_label_id }}">
                                  
                                    <button type="submit" class="primary-btn fix-gr-bg">@lang('common.delete')</button>

                                </form>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
            @endif
        @endforeach

        @foreach ($record->unStudentRequestSubjects as $reqSubject)
            <tr>
                @php
                    $result = labelWiseStudentResult($record, $reqSubject->un_subject_id);
                    $assignDetail = Modules\University\Entities\UnSubjectAssignStudent::assignDetail($reqSubject->un_subject_id)
                @endphp
                <td> {{ @$reqSubject->unSubject->subject_name . '[' . $reqSubject->unSubject->subject_code . ']' }}</td>
                <td> {{ $reqSubject->unSubject->subject_type }}</td>
                <td> {{ $assignDetail['teacher'] ? $assignDetail['teacher']->teacher->full_name : '' }}</td>
                <td> {{ $reqSubject->unSubject->pass_mark ? $reqSubject->unSubject->pass_mark .'%' :'' }}</td>
                <td> {{ $reqSubject->unSubject->number_of_hours }}</td>
                <td> {{ currency_format($assignDetail['amount']) }} </td>
                <td> {{ currency_format($reqSubject->unSubject->number_of_hours * $assignDetail['amount'])}}</td>
                <td>{{ $record->is_promote == 0 ? __('common.pending') : __('common.completed') }}</td>
                <td>                    
                    <a href="#" class="primary-btn icon-only fix-gr-bg" data-toggle="modal" data-target="#deleteReqSubject_{{ $reqSubject->id }}">
                        <span class="ti-trash"></span>
                    </a>  
                </td>
            </tr>
          
            <div class="modal fade admin-query" id="deleteReqSubject_{{ $reqSubject->id }}" >
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">@lang('common.delete')</h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>

                        <div class="modal-body">
                            <div class="text-center">
                                <h4>@lang('common.are_you_sure_to_delete')</h4>
                            </div>

                            <div class="mt-40 d-flex justify-content-between">
                                <button type="button" class="primary-btn tr-bg" data-dismiss="modal">@lang('common.cancel')</button>

                                <form action="{{route('university.subject.request.delete')}}" method="POST">
                                    @csrf
                                    <input type="hidden" name="req_subject_id" value="{{ $reqSubject->id }}">
                                  
                                    <button type="submit" class="primary-btn fix-gr-bg">@lang('common.delete')</button>

                                </form>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
          
        @endforeach

    </tbody>

</table>

@if ($record->is_promote == 0  && !$record->unSemesterLabel->unExamAttendance)
<div class="modal fade admin-query" id="requestSubject">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    @lang('university::un.request_subject')
                </h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
               
                {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'university.request-subject', 'method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'request_subject']) }}


                <input type="hidden" name="record_id" value="{{ $record->id }}">
                <input type="hidden" name="member_id" value="{{ $record->member_id }}">
                <input type="hidden" name="request_label" id="" value="{{ $record->un_semester_label_id }}">
               
                <div class="col-lg-12 mt-25 pl-0">
                    <div class="col-lg-12 " id="selectSectionsDiv" style="margin-top: -25px;">
                        <label for="checkbox"
                            class="mb-2">@lang('university::un.assign_more_subject_for_this_student')</label>
                        <select multiple id="selectSectionss" name="subject[]" style="width:300px">
                            @foreach ($record->withOutPreSubject as $subject)
                                <option value="{{ $subject->id }}" >
                                {{ $subject->subject_name 
                                . '[' . $subject->subject_code . ']' 
                                .'['. $subject->subject_type.']' 
                                .'['. $subject->number_of_hours.']' }} </option>
                            @endforeach
                        </select>

                        @if ($errors->has('subject'))
                            <span class="invalid-feedback invalid-select" role="alert">
                                <strong>{{ $errors->first('subject') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>
                <div class="col-lg-12 mt-5 text-center">
                    <button type="submit" class="primary-btn fix-gr-bg" id="student_promote_submit">
                        <span class="ti-check"></span>
                        @lang('common.assign')
                    </button>
                </div>

                {{ Form::close() }}
               
            </div>

        </div>
    </div>
</div>
@endif
@endforeach
    {{ Form::close() }}
</div>
