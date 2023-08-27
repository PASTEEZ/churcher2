@php
    $school_config = schoolConfig();
    $isSchoolAdmin = Session::get('isSchoolAdmin');
@endphp
        <!-- sidebar part here -->
<nav id="sidebar" class="sidebar">

    <div class="sidebar-header update_sidebar">
        @if (Auth::user()->role_id != 2 && Auth::user()->role_id != 3)
            @if (userPermission(1))
                @if (moduleStatusCheck('Saas') == true && Auth::user()->is_administrator == 'yes' && Session::get('isSchoolAdmin') == false && Auth::user()->role_id == 1)
                    <a href="{{ route('superadmin-dashboard') }}" id="superadmin-dashboard">
                        @else
                            <a href="{{ route('admin-dashboard') }}" id="admin-dashboard">
                                @endif
                                @else
                                    <a href="{{ url('/') }}" id="admin-dashboard">
                                        @endif
                                        @else
                                            <a href="{{ url('/') }}" id="admin-dashboard">
                                                @endif
                                                @if (!is_null($school_config->logo))
                                                    <img src="{{ asset($school_config->logo) }}" alt="logo">
                                                @else
                                                    <img src="{{ asset('public/uploads/settings/logo.png') }}"
                                                         alt="logo">
                                                @endif
                                            </a>
                                            <a id="close_sidebar" class="d-lg-none">
                                                <i class="ti-close"></i>
                                            </a>

    </div>
    @if (Auth::user()->is_saas == 0)

        <ul class="list-unstyled components" id="sidebar_menu">
            <input type="hidden" name="" id="default_position" value="{{ menuPosition('is_submit') }}">
            @if (Auth::user()->role_id != 2 && Auth::user()->role_id != 3)
                @if (userPermission(1))
                    <li>
                        @if (moduleStatusCheck('Saas') == true && Auth::user()->is_administrator == 'yes' && Session::get('isSchoolAdmin') == false && Auth::user()->role_id == 1)
                            <a href="{{ route('superadmin-dashboard') }}" id="superadmin-dashboard">
                                @else
                                    <a href="{{ route('admin-dashboard') }}" id="admin-dashboard">
                                        @endif
                                        <div class="nav_icon_small">
                                            <span class="flaticon-speedometer"></span>
                                        </div>
                                        <div class="nav_title">
                                            @lang('common.dashboard')
                                        </div>

                                    </a>
                    </li>
                @endif
            @endif

            @if (moduleStatusCheck('InfixBiometrics') == true && Auth::user()->role_id == 1)
                @include('infixbiometrics::menu.InfixBiometrics')
            @endif

            {{-- Parent Registration Menu --}}




            {{-- Saas Subscription Menu --}}
            @if (isSubscriptionEnabled() && Auth::user()->is_administrator != 'yes' && Auth::user()->role_id == 1)
                @include('saas::menu.SaasSubscriptionSchool')
            @endif

            {{-- Saas Module Menu --}}
            @if (moduleStatusCheck('Saas') == true && Auth::user()->is_administrator == 'yes' && Session::get('isSchoolAdmin') == false && Auth::user()->role_id == 1)
                @include('saas::menu.Saas')
            @else
                

                @if (Auth::user()->role_id != 2 && Auth::user()->role_id != 3)

                    {{-- admin_section --}}
                    @if (moduleStatusCheck('ParentRegistration'))

                        @includeIf('parentregistration::menu.ParentRegistration')
                    @endif
                    @if (userPermission(11) && menuStatus(11) && isMenuAllowToShow('admin_section'))
                        <li data-position="{{ menuPosition(11) }}" class="sortable_li">
                            <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
                                <div class="nav_icon_small">
                                    <span class="flaticon-analytics"></span>
                                </div>
                                <div class="nav_title">
                                    @lang('admin.admin_section')
                                </div>
                            </a>
                            <ul class="list-unstyled">
                              
                                @if (userPermission(16) && menuStatus(16))
                                    <li data-position="{{ menuPosition(16) }}">
                                        <a href="{{ route('visitor') }}">@lang('admin.visitor_book') </a>
                                    </li>
                                @endif

                                @if (userPermission(21) && menuStatus(21))
                                    <li data-position="{{ menuPosition(21) }}">
                                        <a href="{{ route('complaint') }}">@lang('admin.complaint')</a>
                                    </li>
                                @endif
                                @if (userPermission(27) && menuStatus(27))
                                    <li data-position="{{ menuPosition(27) }}">
                                        <a href="{{ route('postal-receive') }}">@lang('admin.postal_receive')</a>
                                    </li>
                                @endif
                                @if (userPermission(32) && menuStatus(32))
                                    <li data-position="{{ menuPosition(32) }}">
                                        <a href="{{ route('postal-dispatch') }}">@lang('admin.postal_dispatch')</a>
                                    </li>
                                @endif
                                @if (userPermission(36) && menuStatus(36))
                                    <li data-position="{{ menuPosition(36) }}">
                                        <a href="{{ route('phone-call') }}">@lang('admin.phone_call_log')</a>
                                    </li>
                                @endif
                                
                          
                                
                            </ul>
                        </li>
                    @endif


      {{-- student_information --}}
      @if (userPermission(61) && menuStatus(61) && isMenuAllowToShow('student_info'))
      <li data-position="{{ menuPosition(61) }}" class="sortable_li">
          <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
              <div class="nav_icon_small">
                  <span class="flaticon-reading"></span>
              </div>
              <div class="nav_title">
                  @lang('student.student_information')
              </div>
          </a>
          <ul class="list-unstyled" id="subMenuStudent">
          
              @if (userPermission(62) && menuStatus(62))
                  <li data-position="{{ menuPosition(62) }}">
                      <a href="{{ route('student_admission') }}">@lang('student.add_student')</a>
                  </li>
              @endif

             
              @if (userPermission(64) && menuStatus(64))
                  <li data-position="{{ menuPosition(64) }}">
                      <a href="{{ route('student_list') }}"> @lang('student.student_list')</a>
                  </li>
              @endif
          
                 
            

                
              
           
              
            
              
              @if (userPermission(15209) && menuStatus(15209))
                  <li data-position="{{ menuPosition(15209) }}">
                      <a href="{{ route('unassigned_student') }}">@lang('student.unassigned_student')</a>
                  </li>
              @endif

              @if (userPermission(83) && menuStatus(83))
              <li data-position="{{ menuPosition(83) }}">
                  <a href="{{ route('disabled_student') }}">@lang('student.disabled_student')</a>
              </li>
              @endif

              @if (userPermission(663) && menuStatus(663))
                  <li data-position="{{ menuPosition(663) }}">
                      <a href="{{ route('all-student-export') }}">@lang('student.student_export')</a>
                  </li>
              @endif

              @if (userPermission(950) && menuStatus(950))
                  <li data-position="{{ menuPosition(950) }}">
                      <a href="{{ route('notification_time_setup') }}">@lang('student.sms_sending_time')</a>
                  </li>
              @endif

              
          </ul>
      </li>
  @endif

 



             {{-- Junior Youth _information --}}
             @if (userPermission(61) && menuStatus(61) && isMenuAllowToShow('student_info'))
             <li data-position="{{ menuPosition(61) }}" class="sortable_li">
                 <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
                     <div class="nav_icon_small">
                         <span class="flaticon-reading"></span>
                     </div>
                     <div class="nav_title">
                         @lang('member.jy_information')
                     </div>
                 </a>
                 <ul class="list-unstyled" id="subMenuStudent">
                 
                     @if (userPermission(62) && menuStatus(62))
                         <li data-position="{{ menuPosition(62) }}">
                             <a href="{{ route('jy_registration_form') }}">@lang('student.add_student')</a>
                         </li>
                     @endif

                    
                     @if (userPermission(64) && menuStatus(64))
                         <li data-position="{{ menuPosition(64) }}">
                             <a href="{{ route('student_list') }}"> @lang('student.student_list')</a>
                         </li>
                     @endif
                      
                     @if (userPermission(15209) && menuStatus(15209))
                         <li data-position="{{ menuPosition(15209) }}">
                             <a href="{{ route('unassigned_student') }}">@lang('student.unassigned_student')</a>
                         </li>
                     @endif

                     

                 

                      

                     
                 </ul>
             </li>
         @endif

                    {{-- Children_information --}}
                    @if (userPermission(61) && menuStatus(61) && isMenuAllowToShow('student_info'))
                        <li data-position="{{ menuPosition(61) }}" class="sortable_li">
                            <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
                                <div class="nav_icon_small">
                                    <span class="flaticon-reading"></span>
                                </div>
                                <div class="nav_title">
                                    @lang('member.cs_information')
                                </div>
                            </a>
                            <ul class="list-unstyled" id="subMenuStudent">
                            
                                @if (userPermission(62) && menuStatus(62))
                                    <li data-position="{{ menuPosition(62) }}">
                                        <a href="{{ route('student_admission') }}">@lang('student.add_student')</a>
                                    </li>
                                @endif

                               
                                @if (userPermission(64) && menuStatus(64))
                                    <li data-position="{{ menuPosition(64) }}">
                                        <a href="{{ route('student_list') }}"> @lang('student.student_list')</a>
                                    </li>
                                @endif
                                
                                @if (userPermission(15209) && menuStatus(15209))
                                    <li data-position="{{ menuPosition(15209) }}">
                                        <a href="{{ route('unassigned_student') }}">@lang('student.unassigned_student')</a>
                                    </li>
                                @endif

                               

                                @if (userPermission(663) && menuStatus(663))
                                    <li data-position="{{ menuPosition(663) }}">
                                        <a href="{{ route('all-student-export') }}">@lang('student.student_export')</a>
                                    </li>
                                @endif

                              

                                
                            </ul>
                        </li>
                    @endif

                   
                    {{-- academics --}}
                    @if (userPermission(245) && menuStatus(245) && isMenuAllowToShow('academics'))
                        <li data-position="{{ menuPosition(245) }}" class="sortable_li">
                            <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
                                <div class="nav_icon_small">
                                    <span class="flaticon-book"></span>
                                </div>
                                <div class="nav_title">
                                    @lang('academics.academics')
                                </div>
                            </a>
                            <ul class="list-unstyled" id="subMenuAcademic">
                                @if (moduleStatusCheck('University') == false)
                              
                                    @if (userPermission(265) && menuStatus(265))
                                        <li data-position="{{ menuPosition(265) }}">
                                            <a href="{{ route('section') }}"> @lang('common.section')</a>
                                        </li>
                                    @endif
                                    @if (userPermission(261) && menuStatus(261))
                                        <li data-position="{{ menuPosition(261) }}">
                                            <a href="{{ route('class') }}"> @lang('common.class')</a>
                                        </li>
                                    @endif
                                
                                    @if (userPermission(71) && menuStatus(71))
                                    <li data-position="{{ menuPosition(71) }}">
                                        <a href="{{ route('student_category') }}">
                                            @lang('student.student_category')</a>
                                    </li>
                                     @endif
                                     @if (userPermission(76) && menuStatus(76))
                                     <li data-position="{{ menuPosition(76) }}">
                                         <a href="{{ route('student_group') }}">@lang('student.student_group')</a>
                                     </li>
                                 @endif
                             

                                @endif


                

                            </ul>
                        </li>
                    @endif

                    {{-- study_material --}}
                    @if (userPermission(87) && menuStatus(87) && isMenuAllowToShow('study_material'))
                        <li data-position="{{ menuPosition(87) }}" class="sortable_li">
                            <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
                                <div class="nav_icon_small">
                                    <span class="flaticon-professor"></span>
                                </div>
                                <div class="nav_title">
                                    @lang('study.study_material')
                                </div>
                            </a>
                            <ul class="list-unstyled" id="subMenuTeacher">
                                @if (userPermission(88) && menuStatus(87))
                                    <li data-position="{{ menuPosition(88) }}">
                                        <a href="{{ route('upload-content') }}"> @lang('study.upload_content')</a>
                                    </li>
                                @endif
                          
                            
                                @if (userPermission(105) && menuStatus(105))
                                    <li data-position="{{ menuPosition(105) }}">
                                        <a href="{{ route('other-download-list') }}">@lang('study.other_download')</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif

         
                    {{-- FeesCollection --}}


                    @if (moduleStatusCheck('FeesCollection') == true && isMenuAllowToShow('fees'))
                        @include('feescollection::menu.FeesCollection')
                    @else
                        @if ((generalSetting()->fees_status == 0 || !moduleStatusCheck('Fees')) && isMenuAllowToShow('fees'))
                            @if (userPermission(108) && menuStatus(108))
                                <li data-position="{{ menuPosition(108) }}" class="sortable_li">
                                    <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
                                        <div class="nav_icon_small">
                                            <span class="flaticon-wallet"></span>
                                        </div>
                                        <div class="nav_title">
                                            @lang('fees.fees_collection')
                                        </div>
                                    </a>
                                    <ul class="list-unstyled" id="subMenuFeesCollection">

                                        @if (!moduleStatusCheck('University') && directFees() == false)
                                            @if (userPermission(123) && menuStatus(123))
                                                <li data-position="{{ menuPosition(123) }}">
                                                    <a href="{{ route('fees_group') }}"> @lang('fees.fees_group')</a>
                                                </li>
                                            @endif
                                            @if (userPermission(127) && menuStatus(127))
                                                <li data-position="{{ menuPosition(127) }}">
                                                    <a href="{{ route('fees_type') }}"> @lang('fees.fees_type')</a>
                                                </li>
                                            @endif
                                        @endif
                                        @if (userPermission(131) && menuStatus(131))
                                            <li data-position="{{ menuPosition(131) }}">
                                                <a href="{{ route('fees-master') }}"> @lang('fees.fees_master')</a>
                                            </li>
                                        @endif


                                        @if (userPermission(118) && menuStatus(118))
                                            <li data-position="{{ menuPosition(118) }}">
                                                <a href="{{ route('fees_discount') }}"> @lang('fees.fees_discount')</a>
                                            </li>
                                        @endif
                                        @if (userPermission(109) && menuStatus(109))
                                            <li data-position="{{ menuPosition(109) }}">
                                                <a href="{{ route('collect_fees') }}"> @lang('fees.collect_fees')</a>
                                            </li>
                                        @endif

                                        @if (userPermission(113) && menuStatus(113))
                                            <li data-position="{{ menuPosition(113) }}">
                                                <a href="{{ route('search_fees_payment') }}">
                                                    @lang('fees.search_fees_payment')</a>
                                            </li>
                                        @endif
                                        @if (userPermission(116) && menuStatus(116))
                                            <li data-position="{{ menuPosition(116) }}">
                                                <a href="{{ route('search_fees_due') }}">
                                                    @lang('fees.search_fees_due')</a>
                                            </li>
                                        @endif
                                                                            

                                        @if(!moduleStatusCheck('University') && directFees() == false)
                                            @if (userPermission(136) && menuStatus(136))
                                                <li data-position="{{ menuPosition(136) }}">
                                                    <a href="{{ route('fees_forward') }}"> @lang('fees.fees_forward')</a>
                                                </li>
                                            @endif
                                        @endif

                                        @if (userPermission(383) && menuStatus(383))
                                            <li data-position="{{ menuPosition(383) }}">
                                                <a
                                                        href="{{ route('transaction_report') }}">@lang('fees.collection_report')</a>
                                            </li>
                                        @endif


                                        {{-- @if (userPermission(840))
                                            <li data-position="{{menuPosition(840)}}" class="sortable_li">
                                                <a href="#subMenuFeesReport" data-toggle="collapse" aria-expanded="false"
                                                class="dropdown-toggle">
                                                    @lang('lang.report')
                                                </a>
                                                <ul class="list-unstyled" id="subMenuFeesReport">
                                                    @if (userPermission(383))
                                                        <li data-position="{{menuPosition(383)}}">
                                                            <a href="{{route('transaction_report')}}">@lang('lang.collection_report')</a>
                                                        </li>
                                                @endif

                                                </ul>
                                            </li>
                                            @endif --}}
                                    </ul>
                                </li>
                            @endif
                        @endif
                    @endif

                    @if (generalSetting()->fees_status == 1 && moduleStatusCheck('Fees') && isMenuAllowToShow('fees'))
                        @includeIf('fees::sidebar.adminSidebar')
                    @endif

                    {{-- check module link permission --}}
                    @if (moduleStatusCheck('University'))
                        @include('university::un_menu')
                    @endif
                    @if(isMenuAllowToShow('wallet'))
                        @includeIf('wallet::menu.sidebar')
                    @endif
                    @if (moduleStatusCheck('Lms') == true)
                        @include('lms::menu.lms_sidebar')
                    @endif





                    {{-- accounts --}}
                    @if (userPermission(137) && menuStatus(137) && isMenuAllowToShow('accounts'))
                        <li data-position="{{ menuPosition(137) }}" class="sortable_li">
                            <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
                                <div class="nav_icon_small">
                                    <span class="flaticon-accounting"></span>
                                </div>
                                <div class="nav_title">
                                    @lang('accounts.accounts')
                                </div>
                            </a>
                            <ul class="list-unstyled" id="subMenuAccount">
                                @if (userPermission(148) && menuStatus(148))
                                    <li data-position="{{ menuPosition(148) }}">
                                        <a href="{{ route('chart-of-account') }}">
                                            @lang('accounts.chart_of_account')</a>
                                    </li>
                                @endif
                                @if (userPermission(156) && menuStatus(156))
                                    <li data-position="{{ menuPosition(156) }}">
                                        <a href="{{ route('bank-account') }}"> @lang('accounts.bank_account')</a>
                                    </li>
                                @endif
                                @if (userPermission(139) && menuStatus(139))
                                    <li data-position="{{ menuPosition(139) }}">
                                        <a href="{{ route('add_income') }}"> @lang('accounts.income')</a>
                                    </li>
                                @endif
                                @if (userPermission(138) && menuStatus(138))
                                    <li data-position="{{ menuPosition(138) }}">
                                        <a href="{{ route('profit') }}"> @lang('accounts.profit_&_loss')</a>
                                    </li>
                                @endif
                                @if (userPermission(143) && menuStatus(143))
                                    <li data-position="{{ menuPosition(143) }}">
                                        <a href="{{ route('add-expense') }}"> @lang('accounts.expense')</a>
                                    </li>
                                @endif
                                {{-- @if (userPermission(147))
                                    <li>
                                        <a href="{{route('search_account')}}"> @lang('common.search')</a>
                                    </li>
                                @endif --}}
                                @if (userPermission(704) && menuStatus(704))
                                    <li data-position="{{ menuPosition(704) }}">
                                        <a href="{{ route('fund-transfer') }}">@lang('accounts.fund_transfer')</a>
                                    </li>
                                @endif
                                @if (userPermission(700) && menuStatus(700))
                                    @php
                                        $subMenuAccountReport = ['fine-report', 'accounts-payroll-report', 'transaction'];
                                        $subMenuAccount = array_merge(['lead.index'], $subMenuAccountReport);
                                    @endphp
                                    <li data-position="{{ menuPosition(700) }}">
                                        <a href="javascript:void(0)"
                                           class="has-arrow {{ spn_nav_item_open($subMenuAccount, 'active') }}"
                                           aria-expanded="false">
                                            @lang('reports.report')
                                        </a>
                                        <ul class="list-unstyled {{ spn_nav_item_open($subMenuAccount, 'show') }}"
                                            id="subMenuAccountReport">
                                            @if (generalSetting()->fees_status == 0 && userPermission(701) && menuStatus(701))
                                                <li>
                                                    <a href="{{ route('fine-report') }}">
                                                        @lang('accounts.fine_report')</a>
                                                </li>
                                            @endif
                                            @if (userPermission(702) && menuStatus(702))
                                                <li>
                                                    <a href="{{ route('accounts-payroll-report') }}">
                                                        @lang('accounts.payroll_report')</a>
                                                </li>
                                            @endif
                                            @if (userPermission(703) && menuStatus(703))
                                                <li>
                                                    <a href="{{ route('transaction') }}">
                                                        @lang('accounts.transaction')</a>
                                                </li>
                                            @endif
                                        </ul>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif
                    {{-- human_resource --}}
                    @if (userPermission(160) && menuStatus(160) && isMenuAllowToShow('human_resource'))
                        <li data-position="{{ menuPosition(160) }}" class="sortable_li">
                            <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
                                <div class="nav_icon_small">
                                    <span class="flaticon-consultation"></span>
                                </div>
                                <div class="nav_title">
                                    @lang('hr.human_resource')
                                </div>
                            </a>
                            <ul class="list-unstyled" id="subMenuHumanResource">
                                @if (userPermission(180) && menuStatus(180))
                                    <li data-position="{{ menuPosition(180) }}">
                                        <a href="{{ route('designation') }}"> @lang('hr.designation')</a>
                                    </li>
                                @endif
                                @if (userPermission(184) && menuStatus(184))
                                    <li data-position="{{ menuPosition(184) }}">
                                        <a href="{{ route('department') }}"> @lang('hr.department')</a>
                                    </li>
                                @endif
                                @if (userPermission(162) && menuStatus(162))
                                    <li data-position="{{ menuPosition(162) }}">
                                        <a href="{{ route('addStaff') }}"> @lang('common.add_staff') </a>
                                    </li>
                                @endif
                                @if (userPermission(161) && menuStatus(161))
                                    <li data-position="{{ menuPosition(161) }}">
                                        <a href="{{ route('staff_directory') }}"> @lang('hr.staff_directory')</a>
                                    </li>
                                @endif
                                @if (userPermission(165) && menuStatus(162))
                                    <li data-position="{{ menuPosition(165) }}">
                                        <a href="{{ route('staff_attendance') }}"> @lang('hr.staff_attendance')</a>
                                    </li>
                                @endif
                                @if (userPermission(169) && menuStatus(169))
                                    <li data-position="{{ menuPosition(169) }}">
                                        <a href="{{ route('staff_attendance_report') }}">
                                            @lang('hr.staff_attendance_report')</a>
                                    </li>
                                @endif
                                @if (userPermission(170) && menuStatus(170))
                                    <li data-position="{{ menuPosition(170) }}">
                                        <a href="{{ route('payroll') }}"> @lang('hr.payroll')</a>
                                    </li>
                                @endif
                                @if (userPermission(178) && menuStatus(178))
                                    <li data-position="{{ menuPosition(178) }}">
                                        <a href="{{ route('payroll-report') }}"> @lang('hr.payroll_report')</a>
                                    </li>
                                @endif

                                @if (userPermission(952) && menuStatus(952))
                                    <li data-position="{{ menuPosition(951) }}">
                                        <a href="{{ route('staff_settings') }}">@lang('student.settings')</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                  
                    {{-- Chat --}}

                    @if (moduleStatusCheck('Chat') == true && isMenuAllowToShow('chat'))
                        @include('chat::menu')
                    @endif      
            
                
            
                    {{-- Communicate --}}
                    @if (userPermission(286) && menuStatus(286) && isMenuAllowToShow('communicate'))
                        <li data-position="{{ menuPosition(286) }}" class="sortable_li">
                            <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
                                <div class="nav_icon_small">
                                    <span class="flaticon-email"></span>
                                </div>
                                <div class="nav_title">
                                    @lang('communicate.communicate')
                                </div>
                            </a>
                            <ul class="list-unstyled" id="subMenuCommunicate">
                                @if (userPermission(287) && menuStatus(287))
                                    <li data-position="{{ menuPosition(287) }}">
                                        <a href="{{ route('notice-list') }}">@lang('communicate.notice_board')</a>
                                    </li>
                                @endif
                                @if (moduleStatusCheck('Saas') == true && Auth::user()->is_administrator != 'yes')
                                    <li>
                                        <a href="{{ route('administrator-notice') }}">@lang('communicate.administrator_notice')</a>
                                    </li>
                                @endif
                                @if (userPermission(291) && menuStatus(291))
                                    <li data-position="{{ menuPosition(291) }}">
                                        <a href="{{ route('send-email-sms-view') }}">@lang('communicate.send_email_\/_sms')</a>
                                    </li>
                                @endif
                                @if (userPermission(293) && menuStatus(293))
                                    <li data-position="{{ menuPosition(293) }}">
                                        <a href="{{ route('email-sms-log') }}">@lang('communicate.email_sms_log')</a>
                                    </li>
                                @endif
                                
                                {{-- @if (moduleStatusCheck('Saas') == false) --}}
                                @if (userPermission(710) && menuStatus(710))
                                    <li data-position="{{ menuPosition(710) }}">
                                        <a
                                                href="{{ route('templatesettings.sms-template') }}">@lang('communicate.sms_template')</a>
                                    </li>
                                @endif
                                @if (userPermission(480) && menuStatus(480))
                                    <li data-position="{{ menuPosition(480) }}">
                                        <a href="{{ route('templatesettings.email-template') }}">
                                            @lang('communicate.email_template')
                                        </a>
                                    </li>
                                    {{-- @endif --}}
                                @endif
                            </ul>
                        </li>
                    @endif

                   

                    {{-- Inventory --}}
                    @if (userPermission(315) && menuStatus(315) && isMenuAllowToShow('inventory'))
                        <li data-position="{{ menuPosition(315) }}" class="sortable_li">
                            <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
                                <div class="nav_icon_small">
                                    <span class="flaticon-inventory"></span>
                                </div>
                                <div class="nav_title">
                                    @lang('inventory.inventory')
                                </div>
                            </a>
                            <ul class="list-unstyled" id="subMenuInventory">
                                @if (userPermission(316) && menuStatus(316))
                                    <li data-position="{{ menuPosition(316) }}">
                                        <a href="{{ route('item-category') }}"> @lang('inventory.item_category')</a>
                                    </li>
                                @endif
                                @if (userPermission(320) && menuStatus(320))
                                    <li data-position="{{ menuPosition(320) }}">
                                        <a href="{{ route('item-list') }}"> @lang('inventory.item_list')</a>
                                    </li>
                                @endif
                                @if (userPermission(324) && menuStatus(324))
                                    <li data-position="{{ menuPosition(324) }}">
                                        <a href="{{ route('item-store') }}"> @lang('inventory.item_store')</a>
                                    </li>
                                @endif
                                @if (userPermission(328) && menuStatus(328))
                                    <li data-position="{{ menuPosition(328) }}">
                                        <a href="{{ route('suppliers') }}"> @lang('inventory.supplier')</a>
                                    </li>
                                @endif
                                @if (userPermission(332) && menuStatus(332))
                                    <li data-position="{{ menuPosition(332) }}">
                                        <a href="{{ route('item-receive') }}"> @lang('inventory.item_receive')</a>
                                    </li>
                                @endif
                                @if (userPermission(334) && menuStatus(334))
                                    <li data-position="{{ menuPosition(334) }}">
                                        <a href="{{ route('item-receive-list') }}">
                                            @lang('inventory.item_receive_list')</a>
                                    </li>
                                @endif
                                @if (userPermission(339) && menuStatus(339))
                                    <li data-position="{{ menuPosition(339) }}">
                                        <a href="{{ route('item-sell-list') }}"> @lang('inventory.item_sell')</a>
                                    </li>
                                @endif
                                @if (userPermission(345) && menuStatus(345))
                                    <li data-position="{{ menuPosition(345) }}">
                                        <a href="{{ route('item-issue') }}"> @lang('inventory.item_issue')</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif

           
                     
                    {{-- Reports --}}
                    @if (userPermission(376) && menuStatus(376) && isMenuAllowToShow('reports'))
                        <li data-position="{{ menuPosition(376) }}" class="sortable_li">
                            <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
                                <div class="nav_icon_small">
                                    <span class="flaticon-analysis"></span>
                                </div>
                                <div class="nav_title">
                                    @lang('reports.reports')
                                </div>
                            </a>
                            <ul class="list-unstyled" id="subMenusystemReports">
                                @if (userPermission(538) && menuStatus(538))
                                    <li data-position="{{ menuPosition(538) }}">
                                        <a href="{{ route('student_report') }}">@lang('reports.student_report')</a>
                                    </li>
                                @endif
                                @if (userPermission(377) && menuStatus(377))
                                    <li data-position="{{ menuPosition(377) }}">
                                        <a href="{{ route('guardian_report') }}">@lang('reports.guardian_report')</a>
                                    </li>
                                @endif
                                @if (userPermission(378) && menuStatus(378))
                                    <li data-position="{{ menuPosition(378) }}">
                                        <a href="{{ route('student_history') }}">@lang('reports.student_history')</a>
                                    </li>
                                @endif
                                @if (userPermission(379) && menuStatus(379))
                                    <li data-position="{{ menuPosition(379) }}">
                                        <a href="{{ route('student_login_report') }}">@lang('reports.student_login_report')</a>
                                    </li>
                                @endif
                                @if (generalSetting()->fees_status == 0)
                                    @if (userPermission(381) && menuStatus(381))
                                        <li data-position="{{ menuPosition(381) }}">
                                            <a href="{{ route('fees_statement') }}">@lang('reports.fees_statement')</a>
                                        </li>
                                    @endif
                                    @if (userPermission(382) && menuStatus(382))
                                        <li data-position="{{ menuPosition(382) }}">
                                            <a href="{{ route('balance_fees_report') }}">@lang('reports.balance_fees_report')</a>
                                        </li>
                                    @endif
                                @endif

                                @if (!moduleStatusCheck('University'))
                                    @if (userPermission(384) && menuStatus(384))
                                        <li data-position="{{ menuPosition(384) }}">
                                            <a href="{{ route('class_report') }}">@lang('reports.class_report')</a>
                                        </li>
                                    @endif
                                @endif
                               
                             


                          

                                
                                {{-- @if (userPermission(584))
                                    <li>
                                        <a href="{{route('custom-progress-card')}}"> @lang('reports.custom_progress_card_report')</a>
                                    </li>
                                @endif --}}
                                {{-- @if (userPermission(393))
                                    <li>
                                        <a href="{{route('student_fine_report')}}">@lang('reports.student_fine_report')</a>
                                    </li>
                                @endif --}}
                                @if (userPermission(394) && menuStatus(394))
                                    <li data-position="{{ menuPosition(394) }}">
                                        <a href="{{ route('user_log') }}">@lang('reports.user_log')</a>
                                    </li>
                                @endif
                              
                                {{-- New Client report start --}}
                                @if (Auth::user()->role_id == 1)
                                    @if (moduleStatusCheck('ResultReports') == true)
                                        {{-- ResultReports --}}
                                        <li>
                                            <a
                                                    href="{{ route('resultreports/cumulative-sheet-report') }}">@lang('reports.cumulative_sheet_report')</a>
                                        </li>
                                        <li>
                                            <a
                                                    href="{{ route('resultreports/continuous-assessment-report') }}">@lang('lang.contonuous_assessment_report')</a>
                                        </li>
                                        <li>
                                            <a
                                                    href="{{ route('resultreports/termly-academic-report') }}">@lang('lang.termly_academic_report')</a>
                                        </li>
                                        <li>
                                            <a
                                                    href="{{ route('resultreports/academic-performance-report') }}">@lang('lang.academic_performance_report')</a>
                                        </li>
                                        <li>
                                            <a
                                                    href="{{ route('resultreports/terminal-report-sheet') }}">@lang('lang.terminal_report_sheet')</a>
                                        </li>
                                        <li>
                                            <a
                                                    href="{{ route('resultreports/continuous-assessment-sheet') }}">@lang('lang.continuous_assessment_sheet')</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('resultreports/result-version-two') }}">@lang('lang.result_version')
                                                V2</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('resultreports/result-version-three') }}">@lang('lang.result_version')
                                                V3
                                            </a>
                                        </li>
                                        {{-- End New result result report --}}
                                    @endif
                                @endif
                            </ul>
                        </li>
                    @endif

                    {{-- UserManagement --}}
                    @if (userPermission(417) && menuStatus(417) && isMenuAllowToShow('role_permission'))
                        <li data-position="{{ menuPosition(417) }}" class="sortable_li">
                            <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
                                <div class="nav_icon_small">
                                    <span class="flaticon-authentication"></span>
                                </div>
                                <div class="nav_title">
                                    @lang('rolepermission::role.role_&_permission')
                                </div>
                            </a>
                            <ul class="list-unstyled" id="subMenuUserManagement">
                             
                                @if (userPermission(421) && menuStatus(421))
                                    <li data-position="{{ menuPosition(421) }}">
                                        <a href="{{ route('login-access-control') }}">@lang('rolepermission::role.login_permission')</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    {{-- System Settings --}}
                    @if (userPermission(398) && menuStatus(398) && isMenuAllowToShow('system_settings'))
                        <li data-position="{{ menuPosition(398) }}" class="sortable_li metis_submenu_up_collaspe">
                            <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
                                <div class="nav_icon_small">
                                    <span class="flaticon-settings"></span>
                                </div>
                                <div class="nav_title">
                                    @lang('system_settings.system_settings')
                                </div>
                            </a>
                            <ul class="list-unstyled" id="subMenusystemSettings">

                                @if (moduleStatusCheck('Saas') == true && auth()->user()->is_administrator == 'yes')
                                    @if (userPermission(405) && menuStatus(405))
                                        <li data-position="{{ menuPosition(405) }}">
                                            <a href="{{ route('school-general-settings') }}">
                                                @lang('system_settings.general_settings')</a>
                                        </li>
                                    @endif
                                @else
                                    @if (userPermission(405) && menuStatus(405))
                                        <li data-position="{{ menuPosition(405) }}">
                                            <a href="{{ route('general-settings') }}">
                                                @lang('system_settings.general_settings')</a>
                                        </li>
                                    @endif
                                @endif
                                {{-- @if (userPermission(417))
                                    <li>
                                        <a href="{{route('rolepermission/role')}}">@lang('system_settings.role')</a>
                                    </li>
                                @endif
                                @if (userPermission(421))
                                    <li>
                                        <a href="{{route('login-access-control')}}">@lang('system_settings.login_permission')</a>
                                    </li>
                                @endif --}}

                                

                               

                               
 

                        
 
 

                                @if (userPermission(412) && menuStatus(412))
                                    <li data-position="{{ menuPosition(412) }}">
                                        <a href="{{ route('payment-method-settings') }}">@lang('system_settings.payment_settings')</a>
                                    </li>
                                @endif

                                @if (userPermission(410) && menuStatus(410))
                                    <li data-position="{{ menuPosition(410) }}">
                                        <a href="{{ route('email-settings') }}">@lang('system_settings.email_settings')</a>
                                    </li>
                                @endif

                                @if (userPermission(444) && menuStatus(444))
                                    <li data-position="{{ menuPosition(444) }}">
                                        <a href="{{ route('sms-settings') }}">@lang('system_settings.sms_settings')</a>
                                    </li>
                                @endif
                                
                                

                                {{-- SAAS DISABLE --}}
                                @if (moduleStatusCheck('Saas') == false)
                                    @include('backEnd.partials.without_saas_school_admin_menu')
                                @endif
                                @if (userPermission(2200) && menuStatus(2200))
                                    <li data-position="{{ menuPosition(2200) }}">
                                        <a href="{{ route('setting.preloader') }}">@lang('system_settings.Preloader Settings') </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    {{-- style --}}
                    {{--                 @if (moduleStatusCheck('Saas') == false)--}}
                    @if (userPermission(485) && menuStatus(485) && isMenuAllowToShow('style'))
                        <li data-position="{{ menuPosition(485) }}" class="sortable_li metis_submenu_up_collaspe">
                            <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
                                <div class="nav_icon_small">
                                    <span class="flaticon-consultation"></span>
                                </div>
                                <div class="nav_title">
                                    @lang('style.style')
                                </div>
                            </a>
                            <ul class="list-unstyled" id="subMenusystemStyle">
                                @if (userPermission(486) && menuStatus(486))
                                    <li data-position="{{ menuPosition(486) }}">
                                        <a href="{{ route('background-setting') }}">@lang('style.background_settings')</a>
                                    </li>
                                @endif
                                @if (userPermission(490) && menuStatus(490))
                                    <li data-position="{{ menuPosition(490) }}">
                                        <a href="{{ route('color-style') }}">@lang('style.color_theme')</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif
                    {{-- @endif --}}

      

                    {{-- Ticket --}}
                    @if (moduleStatusCheck('Saas') == true && Auth::user()->is_administrator != 'yes')
                        <li>
                            <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
                                <div class="nav_icon_small">
                                    <span class="flaticon-settings"></span>
                                </div>
                                <div class="nav_title">
                                    @lang('saas::saas.ticket_system')
                                </div>
                            </a>
                            <ul class="list-unstyled" id="Ticket">
                                @if(SaasDomain() == 'school' || userPermission(1935))
                                <li>
                                    <a href="{{ route('school/ticket-unassign-list') }}">@lang('saas::saas.un_assign_ticket_list')</a>
                                </li>
                                @endif
                                <li>
                                    <a href="{{ route('school/ticket-view') }}">@lang('saas::saas.ticket_list')</a>
                                </li>
                            </ul>
                        </li>
                    @endif

                   

                @endif

                <!-- Student Panel -->
                @if (Auth::user()->role_id == 2)
                    @include('backEnd/partials/student_sidebar')
                @endif

                <!-- Parents Panel Menu -->
                @if (Auth::user()->role_id == 3)
                    @include('backEnd/partials/parents_sidebar')
                @endif
            @endif
        </ul>
    @endif
</nav>
<!-- sidebar part end -->
