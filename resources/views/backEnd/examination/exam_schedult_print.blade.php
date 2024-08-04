<!DOCTYPE html>
<html lang="en">
<head>
  <title>@lang('exam.exam_schedule')</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1"> 
  <link rel="stylesheet" href="{{asset('public/backEnd/')}}/vendors/css/print/bootstrap.min.css"/>
  <script type="text/javascript" src="{{asset('public/backEnd/')}}/vendors/js/print/jquery.min.js"></script>
  <script type="text/javascript" src="{{asset('public/backEnd/')}}/vendors/js/print/bootstrap.min.js"></script>
</head>
<style>
 table,th,tr,td{
     font-size: 11px !important;
 }
 
</style>
<body style="font-family: 'dejavu sans', sans-serif;">
 

@php 
    $generalSetting= generalSetting();
    if(!empty($generalSetting)){
        $church_name =$generalSetting->church_name;
        $site_title =$generalSetting->site_title;
        $church_code =$generalSetting->church_code;
        $address =$generalSetting->address;
        $phone =$generalSetting->phone; 
    } 
    $exam=App\SmExamType::find(@$exam_id);
    $class=App\SmClass::find(@$age_group_id);
    
    if($mgender_id==0){
        $section='All Sections';
    }else{
        $section=App\SmSection::find(@$mgender_id);
        $section=$section->mgender_name;
    }
@endphp
<div class="container-fluid" id="pdf">
                    
                    <table  cellspacing="0" width="100%">
                        <tr>
                            <td> 
                                <img class="logo-img" src="{{ url('/')}}/{{@generalSetting()->logo }}" alt=""> 
                            </td>
                            <td> 
                                <h3 style="font-size:22px !important" class="text-white"> {{isset(generalSetting()->church_name)?generalSetting()->church_name:'Infix School Management ERP'}} </h3> 
                                <p style="font-size:18px !important" class="text-white mb-0"> {{isset(generalSetting()->address)?generalSetting()->address:'Infix School Address'}} </p> 
                                <p style="font-size:15px !important" class="text-white mb-0"> @lang('exam.exam_schedule') </p> 
                          </td>
                            <td style="text-aligh:center"> 
                                <p style="font-size:14px !important; border-bottom:1px solid gray;" align="left" class="text-white">@lang('exam.exam') :  {{ @$exam->title}} </p> 
                                <p style="font-size:14px !important; border-bottom:1px solid gray" align="left" class="text-white">@lang('common.class'): {{ @$class->age_group_name}} </p> 
                                <p style="font-size:14px !important; border-bottom:1px solid gray" align="left" class="text-white">@lang('common.section'): {{ @$section}} </p> 
                                <p style="font-size:14px !important; border-bottom:1px solid gray" align="left" class="text-white">@lang('common.church_year'): {{ @$church_year->title}} ({{ @$church_year->year}}) </p> 
                               
                          </td>
                        </tr>
                    </table>

                    <hr>
                <table class="table table-bordered table-striped" cellspacing="0" width="100%">
                    
                         
                        <tr>
                            <th width="10%">@lang('common.subject')</th>
                            @if($mgender_id==0)
                            <th width="10%">@lang('common.class_Sec')</th>
                            @endif
                            @foreach($exam_periods as $exam_period)
                            <th style="text-align:center" nowrap>{{ @$exam_period->period}}<br>{{date('h:i A', strtotime(@$exam_period->start_time))}}<br>To<br>{{ date('h:i A', strtotime(@$exam_period->end_time))}}</th>
                            @endforeach
                        </tr>
                        @foreach($assign_subjects as $assign_subject)
                        <tr>
                            <td >{{ @$assign_subject->subject !=""?@$assign_subject->subject->subject_name:""}}</td>
                            @if($mgender_id==0)
                            <td>{{$assign_subject->class->age_group_name.'('.$assign_subject->section->mgender_name.')'}}</td>
                            @endif
                            @foreach($exam_periods as $exam_period)
                                @php
                                $assigned_routine = App\SmExamSchedule::assignedRoutine($age_group_id, $assign_subject->mgender_id, $exam_id, $assign_subject->subject_id, $exam_period->id);
                                    // $assigned_routine = getScheduleSubject($age_group_id, $mgender_id, $exam_id, $exam_period->id, $exam_date);
                                @endphp
                            <td nowrap>
                                @if(@$assigned_routine == "")
                                    
                                @else
                                    <div class="col-lg-6">
                                        <span class="">{{@$assigned_routine->classRoom->room_no}}</span>
                                        <br>
                                        <span class="">
                                            
                                            {{@$assigned_routine->date != ""? dateConvert(@$assigned_routine->date):''}}

                                        </span></br>
                                        
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                        
                </table>
        </div>
<script src="{{ asset('public/vendor/spondonit/js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('public/backEnd/js/pdf/html2pdf.bundle.min.js') }}"></script>
<script src="{{ asset('public/backEnd/js/pdf/html2canvas.min.js') }}"></script>

<script>
    function generatePDF() {
        const element = document.getElementById('pdf');
        var opt = {
            margin:       0.5,
            pagebreak: { mode: ['avoid-all', 'css', 'legacy'], before: '#page2el' },
            filename:     'exam-schedule.pdf',
            image:        { type: 'jpeg', quality: 100 },
            html2canvas:  { scale: 5 },
            jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };

        html2pdf().set(opt).from(element).save().then(function(){
            // window.close();
        });
    }



    $(document).ready(function(){
        @if($print)
        window.print();
        setTimeout(function () {
            window.close()
        }, 3000);
        @else
        generatePDF();
        @endif

    })
</script>

</body>
</html>
    
