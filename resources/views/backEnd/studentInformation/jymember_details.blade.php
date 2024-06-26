@extends('backEnd.master')
@section('title')
    @lang('student.student_list')
@endsection
@section('mainContent')
    <section class="sms-breadcrumb mb-40 up_breadcrumb white-box">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1>@lang('student.manage_student')</h1>
                <div class="bc-pages">
                    <a href="{{route('dashboard')}}">@lang('common.dashboard')</a>
                    <a href="#">@lang('student.student_information')</a>
                    <a href="#">@lang('student.student_list')</a>
                </div>
            </div>
        </div>
    </section>
    <section class="admin-visitor-area up_admin_visitor">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-lg-8 col-md-6 col-sm-6">
                  
                        <a class="btn btn-danger" href="{{ route('updatememberage.route') }}" class="primary-btn small fix-gr-bg">
                        <span  class="bi bi-database-fill-add"></span>
                        @lang('common.update_yeargroup')
                    </a>
                </div>
               
                @if(userPermission(62))
                    <div class="col-lg-4 text-md-right text-left col-md-6 mb-30-lg col-sm-6 text_sm_right">
                        <a href="{{route('student_admission')}}" class="primary-btn small fix-gr-bg">
                            <span class="ti-plus pr-2"></span>
                            @lang('student.add_student')
                        </a>
                    </div>
                @endif
            </div>
            {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'student-list-search', 'method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'infix_form']) }}
            <div class="row">
                <div class="col-lg-12">
                    <div class="white-box">
                        <div class="row">
                            <input type="hidden" name="url" id="url" value="{{URL::to('/')}}">


                            @if(moduleStatusCheck('University'))
                                  @includeIf('university::common.session_faculty_depart_academic_semester_level',['mt'=>'mt-30','hide'=>['USUB'], 'required'=>['USEC']])
                                <div class="col-lg-3 mt-25">
                                    <div class="input-effect sm2_mb_20 md_mb_20">
                                        <input class="primary-input" type="text" name="name" value="{{isset($name)? $name: ''}}">
                                        <label>@lang('student.search_by_name')</label>
                                        <span class="focus-border"></span>
                                    </div>
                                </div>
                                <div class="col-lg-3 mt-25">
                                    <div class="input-effect md_mb_20">
                                        <input class="primary-input" type="text" name="roll_no" value="{{isset($roll_no)? $roll_no: ''}}">
                                        <label>@lang('student.search_by_roll_no')</label>
                                        <span class="focus-border"></span>
                                    </div>
                                </div>
                            @else
                                <div class="col-lg-3">
                                    <div class="input-effect sm2_mb_20 md_mb_20">
                                        <select class="niceSelect w-100 bb form-control{{ $errors->has('church_year') ? ' is-invalid' : '' }}" name="church_year" id="church_year">
                                            <option data-display="@lang('common.church_year') *" value="">@lang('common.church_year') *</option>
                                            @foreach($sessions as $session)
                                                <option value="{{$session->id}}" {{isset($church_year) && $church_year == $session->id? 'selected': ''}}>{{$session->year}}[{{$session->title}}]</option>
                                            @endforeach
                                        </select>
                                        <span class="focus-border"></span>
                                        @if ($errors->has('church_year'))
                                            <span class="invalid-feedback invalid-select" role="alert">
                                        <strong>{{ $errors->first('church_year') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-3 sm_mb_20 sm2_mb_20 md_mb_20" id="class-div">
                                    <select class="niceSelect w-100 bb form-control {{ $errors->has('class') ? ' is-invalid' : '' }}" id="classSelectStudent" name="class">
                                        <option data-display="@lang('student.select_class') *" value="">@lang('student.select_class')</option>
                                        @isset($church_year)
                                            @foreach($classes as $class)
                                                <option value="{{$class->id}}" {{isset($age_group_id) && $age_group_id == $class->id ? 'selected' : ''}}>{{$class->age_group_name}}</option>
                                            @endforeach
                                        @endisset
                                    </select>
                                    <div class="pull-right loader loader_style" id="select_class_loader">
                                        <img class="loader_img_style" src="{{asset('public/backEnd/img/demo_wait.gif')}}" alt="loader">
                                    </div>
                                    @if ($errors->has('class'))
                                        <span class="invalid-feedback invalid-select" role="alert">
                                            <strong>{{ $errors->first('class') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <div class="col-lg-2 col-md-3" id="sectionStudentDiv">
                                    <select class="niceSelect w-100 bb form-control{{ $errors->has('section') ? ' is-invalid' : '' }}" id="sectionSelectStudent" name="section">
                                        <option data-display="@lang('student.select_section')" value="">@lang('student.select_section')</option>
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
                                <div class="col-lg-2">
                                    <div class="input-effect sm_mb_20 sm2_mb_20 md_mb_20">
                                        <input class="primary-input" type="text" name="name" value="{{ isset($name)?$name:old('name')}}">
                                        <label>@lang('student.search_by_name')</label>
                                        <span class="focus-border"></span>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="input-effect sm_mb_20 sm2_mb_20 md_mb_20">
                                        <input class="primary-input" type="text" name="roll_no" value="{{ isset($roll_no)?$roll_no:old('roll_no')}}">
                                        <label>@lang('student.search_by_roll_no')</label>
                                        <span class="focus-border"></span>
                                    </div>
                                </div>
                            @endif
                            <div class="col-lg-12 mt-20 text-right">
                                <button type="submit" class="primary-btn small fix-gr-bg" id="btnsubmit">
                                    <span class="ti-search pr-2"></span>
                                    @lang('common.search')
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" id="church_year_id" value="{{@$church_year}}">
            <input type="hidden" id="class" value="{{@$age_group_id}}">
            <input type="hidden" id="section" value="{{@$section}}">
            <input type="hidden" id="roll" value="{{@$roll_no}}">
            <input type="hidden" id="name" value="{{@$name}}">
                                <input type="hidden" id="un_session" value="{{@$data['un_session_id']}}">
                                <input type="hidden" id="un_academic" value="{{@$data['un_church_year_id']}}">
                                <input type="hidden" id="un_faculty" value="{{@$data['un_faculty_id']}}">
                                <input type="hidden" id="un_department" value="{{@$data['un_department_id']}}">
                                <input type="hidden" id="un_semester_label" value="{{@$data['un_semester_label_id']}}">
                                <input type="hidden" id="un_section" value="{{@$data['un_mgender_id']}}">


                        
            {{ Form::close() }}
            {{-- @if (@$students) --}}
            <div class="row mt-40 full_wide_table">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-4 no-gutters">
                            <div class="main-title">
                                <h3 class="mb-0">@lang('student.student_list')</h3>
                            </div>
                        </div>
                    </div>
                    <div class="row  ">
                        <div class="col-lg-12">
                            <table id="table_id" class="display data-table school-table" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <th>@lang('student.registration_no')</th>
                                    <th>@lang('common.type')</th>
                                    <th>@lang('student.name')</th>
                                   
                                    <th>@lang('student.date_of_birth')</th>
                                   
                                    <th>@lang('student.age')</th>
                                   
                                        <th>@lang('student.class')</th>
                      

                                    <th>@lang('common.gender')</th>
                                    
                                    <th>@lang('common.phone')</th>
                                    <th>@lang('common.actions')</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            {{-- @endif --}}
        </div>
    </section>
    {{-- disable student  --}}
    <div class="modal fade admin-query" id="deleteStudentModal" >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">@lang('student.disable_student')</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <h4>@lang('student.are_you_sure_to_disable')</h4>
                    </div>
                    <div class="mt-40 d-flex justify-content-between">
                        <button type="button" class="primary-btn tr-bg" data-dismiss="modal">@lang('common.cancel')</button>
                        {{ Form::open(['route' => 'student-delete', 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
                        <input type="hidden" name="id" value="{{@$student->id}}" id="student_delete_i">  {{-- using js in main.js --}}
                        <button class="primary-btn fix-gr-bg" type="submit">@lang('common.disable')</button>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
   
    @php
        if(isset($church_year) || isset($age_group_id)){
            $ajax_url=url('student-list-datatable?church_year='.
            $church_year.'&class='.
            $age_group_id.'&section='.
            $section.'&roll_no='.
            $roll_no.'&name='.$name);
        }else{
            $ajax_url = url('student-list-datatable');
        }
    @endphp
@endsection
@section('script')
    @include('backEnd.partials.server_side_datatable')
    <script>
        //
        // DataTables initialisation
        //
        $(document).ready(function() {
            $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                "ajax": $.fn.dataTable.pipeline( {
                    url: "{{url('jymember-list-datatable')}}",
                    data: {
                        church_year: $('#church_year_id').val(),
                        class: $('#class').val(),
                        section: $('#section').val(),
                        roll_no: $('#roll').val(),
                        name: $('#name').val(),
                        un_session_id: $('#un_session').val(),
                        un_church_year_id: $('#un_academic').val(),
                        un_faculty_id: $('#un_faculty').val(),
                        un_department_id: $('#un_department').val(),
                        un_semester_label_id: $('#un_semester_label').val(),
                        un_mgender_id: $('#un_section').val(),
                    },
                    pages: "{{generalSetting()->ss_page_load}}" // number of pages to cache
                } ),
                columns: [
                    {data: 'registration_no', name: 'registration_no'},  
                    {data: 'category.category_name', name: 'category.category_name'},
                            
                    {data: 'full_name', name: 'full_name'},  
                    
                    {data: 'dob', name: 'dob'},
               
                    {data: 'age', name: 'age'},
                        {data: 'class_sec', name: 'class_sec'},
                  
                    {data: 'm_gender', name: 'm_gender'},
                     {data: 'mobile', name: 'sm_students.mobile'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                    {data: 'first_name', name: 'first_name', visible : false},
                    {data: 'last_name', name: 'last_name', visible : false},
                ],
                bLengthChange: false,
                bDestroy: true,
                language: {
                    search: "<i class='ti-search'></i>",
                    searchPlaceholder: window.jsLang('quick_search'),
                    paginate: {
                        next: "<i class='ti-arrow-right'></i>",
                        previous: "<i class='ti-arrow-left'></i>",
                    },
                },
                dom: "Bfrtip",
                buttons: [{
                    extend: "copyHtml5",
                    text: '<i class="fa fa-files-o"></i>',
                    title: $("#logo_title").val(),
                    titleAttr: window.jsLang('copy_table'),
                    exportOptions: {
                        columns: ':visible:not(.not-export-col)'
                    },
                },
                    {
                        extend: "excelHtml5",
                        text: '<i class="fa fa-file-excel-o"></i>',
                        titleAttr: window.jsLang('export_to_excel'),
                        title: $("#logo_title").val(),
                        margin: [10, 10, 10, 0],
                        exportOptions: {
                            columns: ':visible:not(.not-export-col)'
                        },
                    },
                    {
                        extend: "csvHtml5",
                        text: '<i class="fa fa-file-text-o"></i>',
                        titleAttr: window.jsLang('export_to_csv'),
                        exportOptions: {
                            columns: ':visible:not(.not-export-col)'
                        },
                    },
                    {
                        extend: "pdfHtml5",
                        text: '<i class="fa fa-file-pdf-o"></i>',
                        title: $("#logo_title").val(),
                        titleAttr: window.jsLang('export_to_pdf'),
                        exportOptions: {
                            columns: ':visible:not(.not-export-col)'
                        },
                        orientation: "landscape",
                        pageSize: "A4",
                        margin: [0, 0, 0, 12],
                        alignment: "center",
                        header: true,
                        customize: function(doc) {
                            doc.content[1].margin = [100, 0, 100, 0]; //left, top, right, bottom
                            doc.content.splice(1, 0, {
                                margin: [0, 0, 0, 12],
                                alignment: "center",
                                image: "data:image/png;base64," + $("#logo_img").val(),
                            });
                            doc.defaultStyle = {
                                font: 'DejaVuSans'
                            }
                        },
                    },
                    {
                        extend: "print",
                        text: '<i class="fa fa-print"></i>',
                        titleAttr: window.jsLang('print'),
                        title: $("#logo_title").val(),
                        exportOptions: {
                            columns: ':visible:not(.not-export-col)'
                        },
                    },
                    {
                        extend: "colvis",
                        text: '<i class="fa fa-columns"></i>',
                        postfixButtons: ["colvisRestore"],
                    },
                ],
                columnDefs: [{
                    visible: false,
                }, ],
                responsive: true,
            });
        } );
    </script>
@endsection