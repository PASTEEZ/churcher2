@extends('backEnd.master')
@section('title') 
@lang('fees.balance_fees_report')
@endsection
@section('mainContent')
<input type="text" hidden value="{{ @$clas->age_group_name }}" id="cls">
<input type="text" hidden value="{{ @$clas->mgender_name->sectionName->mgender_name }}" id="sec">
<section class="sms-breadcrumb mb-40 white-box">
    <div class="container-fluid">
        <div class="row justify-content-between">
            <h1>@lang('fees.balance_fees_report')</h1>
            <div class="bc-pages">
                <a href="{{route('dashboard')}}">@lang('common.dashboard')</a>
                <a href="#">@lang('fees.reports')</a>
                <a href="#">@lang('fees.balance_fees_report')</a>
            </div>
        </div>
    </div>
</section>
<section class="admin-visitor-area up_admin_visitor">
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
                        {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'balance_fees_search', 'method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'search_student']) }}
                            <div class="row">
                                <input type="hidden" name="url" id="url" value="{{URL::to('/')}}">
                                @if(moduleStatusCheck('University'))
                                @includeIf('university::common.session_faculty_depart_academic_semester_level',['mt'=>'mt-30','hide'=>['USUB'], 'required'=>['USEC']])
                                @else
                                <div class="col-lg-6 mt-30-md col-md-6">
                                    <select class="w-100 bb niceSelect form-control {{ $errors->has('class') ? ' is-invalid' : '' }}" id="select_class" name="class">
                                        <option data-display="@lang('common.select_class') *" value="">@lang('common.select_class') *</option>
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
                                <div class="col-lg-6 mt-30-md col-md-6" id="select_section_div">
                                    <select class="w-100 bb niceSelect form-control{{ $errors->has('section') ? ' is-invalid' : '' }}" id="select_section" name="section">
                                        <option data-display="@lang('common.select_section')*" value="">@lang('common.select_section') *</option>
                                        @if(isset($age_group_id))
                                        @foreach ($class->classSection as $section)
                                        <option value="{{ $section->sectionName->id }}" {{ old('section')==$section->sectionName->id ? 'selected' : '' }} >
                                            {{ $section->sectionName->mgender_name }}</option>
                                        @endforeach
                                    @endif
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

            @php
                $grand_total = 0;
                $grand_discount = 0;
                $grand_fine = 0;
                $grand_deposit = 0;
                $grand_balance = 0;
            @endphp

            
            @if(isset($balance_students))

            <div class="row mt-40">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-6 no-gutters">
                            <div class="main-title">
                                <h3 class="mb-0">@lang('fees.student_fees_report')</h3>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">

                            <table id="table_ids" class="display school-table balance-custom-table" cellspacing="0" width="100%">

                                <thead>
                                    <tr>
                                        <th>@lang('common.name')</th>
                                        <th>@lang('student.registration_no')</th>
                                        <th>@lang('student.roll_no')</th>
                                        <th>@lang('student.father_name')</th>
                                        <th>@lang('accounts.amount')</th>
                                        <th>@lang('fees.discount')</th>
                                        <th>@lang('fees.fine')</th>
                                        <th>@lang('fees.paid_fees')</th>
                                        <th>@lang('fees.balance')</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    
                                    @foreach($data as $key=> $value)
                                    @php
                                        $student = $value['student'];
                                    @endphp
                                    <tr>
                                        <td>{{$student->full_name}}</td>
                                        <td>{{$student->registration_no}}</td>
                                        <td>{{$student->roll_no}}</td>
                                        <td>{{$student->parents!=""?$student->parents->fathers_name:""}}</td>
                                        <td>
                                            @php
                                            $grand_total += $value['totalFees'];
                                            echo $value['totalFees'];
                                            @endphp
                                        </td>
                                        <td>
                                            @php
                                            $grand_discount += $value['totalDiscount'];
                                            echo $value['totalDiscount'];
                                            @endphp
                                        </td>
                                        <td>
                                            @php
                                            $grand_fine += $value['totalFine'];
                                            echo $value['totalFine'];
                                            @endphp
                                        </td>
                                        <td>
                                            @php
                                            $grand_deposit += $value['totalDeposit'];
                                            echo $value['totalDeposit'];
                                            @endphp
                                        </td>
                                        <td>
                                            @php
                                            $balance =  $value['totalFees'] - $value['totalDiscount'] - $value['totalDeposit'] +$value['totalFine'] ;
                                            $grand_balance += $balance;
                                            echo $balance;
                                            @endphp
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th>@lang('fees.grand_total')</th>
                                    <th>{{$grand_total}} </th>
                                    <th>{{$grand_discount}}</th>
                                    <th>{{$grand_fine}}</th>
                                    <th>{{$grand_deposit}}</th>
                                    <th>{{$grand_balance}}</th>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    
                </div>
            </div>

            @endif  


            @if(isset($student_records))

            <div class="row mt-40">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-6 no-gutters">
                            <div class="main-title">
                                <h3 class="mb-0">@lang('fees.student_fees_report')</h3>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">

                            <table id="table_ids" class="display school-table balance-custom-table" cellspacing="0" width="100%">

                                <thead>
                                    <tr>
                                        <th>@lang('common.name')</th>
                                        <th>@lang('student.registration_no')</th>
                                        <th>@lang('student.roll_no')</th>
                                        <th>@lang('student.father_name')</th>
                                        <th>@lang('accounts.amount')</th>
                                        <th>@lang('fees.discount')</th>
                                        <th>@lang('fees.paid_fees')</th>
                                        <th>@lang('fees.balance')</th>
                                    </tr>
                                </thead>

                                <tbody>
                                   @foreach ($student_records as  $record)
                                    
                                    <tr>
                                        <td>{{@$record->student->full_name}}</td>
                                        <td>{{@$record->student->registration_no}}</td>
                                        <td>{{@$record->student->roll_no}}</td>
                                        <td>{{@$record->student->parents!=""?$record->student->parents->fathers_name:""}}</td>
                                        <td>{{@$record->feesInstallments->sum('amount')}}</td>
                                        <td>{{@$record->feesInstallments->sum('discount_amount')}}</td>
                                        <td>{{@$record->feesInstallments->sum('paid_amount')}}</td>
                                        <td>{{ (@$record->feesInstallments->sum('amount')) - (@$record->feesInstallments->sum('paid_amount') + @$record->feesInstallments->sum('discount_amount'))}}</td>
                                     
                                        @php
                                        $grand_total += @$record->feesInstallments->sum('amount');
                                        $grand_discount += @$record->feesInstallments->sum('discount_amount');
                                        $grand_deposit +=  @$record->feesInstallments->sum('paid_amount');
                                        $grand_balance += ($grand_total - $grand_deposit ) ;
                                        @endphp 
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th>@lang('fees.grand_total')</th>
                                    <th>{{@$grand_total}} </th>
                                    <th>{{@$grand_discount}}</th>
                                    <th>{{@$grand_deposit}}</th>
                                    <th>{{@$grand_balance}}</th>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    
                </div>
            </div>

            @endif  


    </div>
</section>


@endsection
