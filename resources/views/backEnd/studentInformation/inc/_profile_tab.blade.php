<div role="tabpanel"
     class="tab-pane fade {{ $type == '' && Session::get('studentDocuments') == '' ? 'show active' : '' }}"
     id="studentProfile">
    <div class="white-box">
        {{--        <h4 class="stu-sub-head">@lang('student.personal_info')</h4>--}}
        @if (is_show('admission_date'))
            <div class="single-info">
                <div class="row">
                    <div class="col-lg-5 col-md-5">
                        <div class="">
                            @lang('student.admission_date')
                        </div>
                    </div>

                    <div class="col-lg-7 col-md-6">
                        <div class="">
                            {{ !empty($student_detail->admission_date) ? dateConvert($student_detail->admission_date) : '' }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (is_show('date_of_birth'))
            <div class="single-info">
                <div class="row">
                    <div class="col-lg-5 col-md-6">
                        <div class="">
                            @lang('student.date_of_birth')
                        </div>
                    </div>

                    <div class="col-lg-7 col-md-7">
                        <div class="">
                            {{ !empty($student_detail->date_of_birth) ? dateConvert($student_detail->date_of_birth) : '' }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="single-info">
            <div class="row">
                <div class="col-lg-5 col-md-6">
                    <div class="">
                        @lang('student.age')
                    </div>
                </div>

                <div class="col-lg-7 col-md-7">
                    <div class="">
                        {{ \Carbon\Carbon::parse($student_detail->date_of_birth)->diff(\Carbon\Carbon::now())->format('%y years') }}
                    </div>
                </div>
            </div>
        </div>
        @if (is_show('student_category_id'))
            <div class="single-info">
                <div class="row">
                    <div class="col-lg-5 col-md-6">
                        <div class="">
                            @lang('student.category')
                        </div>
                    </div>

                    <div class="col-lg-7 col-md-7">
                        <div class="">
                            {{ $student_detail->category != '' ? $student_detail->category->category_name : '' }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (is_show('email_address'))
        <div class="single-info">
            <div class="row">
                <div class="col-lg-5 col-md-6">
                    <div class="">
                        @lang('common.aka')
                    </div>
                </div>

                <div class="col-lg-7 col-md-7">
                    <div class="">
                     
                        {{ @$student_detail->aka }} 
                
                    </div>
                </div>
            </div>
        </div>
    @endif

        <div class="single-info">
            <div class="row">
                <div class="col-lg-5 col-md-6">
                    <div class="">
                        @lang('student.group')
                    </div>
                </div>

                <div class="col-lg-7 col-md-7">
                    <div class="">
                        {{ $student_detail->group ? $student_detail->group->group : '' }}
                    </div>
                </div>
            </div>
        </div>
        @if (is_show('religion'))
            <div class="single-info">
                <div class="row">
                    <div class="col-lg-5 col-md-6">
                        <div class="">
                            @lang('student.religion')
                        </div>
                    </div>

                    <div class="col-lg-7 col-md-7">
                        <div class="">
                            {{ $student_detail->religion != '' ? $student_detail->religion->base_setup_name : '' }}
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if (is_show('phone_number'))
            <div class="single-info">
                <div class="row">
                    <div class="col-lg-5 col-md-6">
                        <div class="">
                            @lang('student.phone_number')
                        </div>
                    </div>

                    <div class="col-lg-7 col-md-7">
                        <div class="">
                            @if ($student_detail->mobile)
                                <a href="tel:{{ @$student_detail->mobile }}"> {{ @$student_detail->mobile }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="single-info">
            <div class="row">
                
                <div class="col-lg-5 col-md-6">
                    <div class="">
                        @lang('student.roll')
                    </div>
                </div>

                <div class="col-lg-7 col-md-7">
                    <div class="">
                        @if ($student_detail->phone_work)
                            <a href="tel:{{ @$student_detail->phone_work }}"> {{ @$student_detail->phone_work }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
 
        <div class="single-info">
            <div class="row">

                <div class="col-lg-5 col-md-6">
                    <div class="">
                        @lang('student.othercontact')
                    </div>
                </div>

                <div class="col-lg-7 col-md-7">
                    <div class="">
                        @if ($student_detail->othercontact)
                            <a href="tel:{{ @$student_detail->othercontact }}"> {{ @$student_detail->othercontact }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
 
        @if (is_show('email_address'))
            <div class="single-info">
                <div class="row">
                    <div class="col-lg-5 col-md-6">
                        <div class="">
                            @lang('common.email_address')
                        </div>
                    </div>

                    <div class="col-lg-7 col-md-7">
                        <div class="">
                            @if ($student_detail->email)
                                <a href="mailto:{{ @$student_detail->email }}"> {{ @$student_detail->email }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        
        <div class="single-info">
            <div class="row">
                <div class="col-lg-5 col-md-6">
                    <div class="">
                        @lang('common.nationality')
                    </div>
                </div>

                <div class="col-lg-7 col-md-7">
                    <div class="">
                     
                        {{ @$student_detail->nationality }} 
                
                    </div>
                </div>
            </div>
        </div>
   
    
    <div class="single-info">
        <div class="row">
            <div class="col-lg-5 col-md-6">
                <div class="">
                    @lang('common.hometown')
                </div>
            </div>

            <div class="col-lg-7 col-md-7">
                <div class="">
                 
                    {{ @$student_detail->caste }} 
            
                </div>
            </div>
        </div>
    </div>


      {{-- end --}}
      @if (is_show('current_address'))
      <div class="single-info">
          <div class="row">
              <div class="col-lg-5 col-md-6">
                  <div class="">
                      @lang('student.area')
                  </div>
              </div>

              <div class="col-lg-7 col-md-7">
                  <div class="">
                      {{ @$student_detail->landmark }}
                  </div>
              </div>
          </div>
      </div>
  @endif
      
        {{-- end --}}
        @if (is_show('current_address'))
            <div class="single-info">
                <div class="row">
                    <div class="col-lg-5 col-md-6">
                        <div class="">
                            @lang('student.present_address')
                        </div>
                    </div>

                    <div class="col-lg-7 col-md-7">
                        <div class="">
                            {{ @$student_detail->current_address }}
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if (is_show('permanent_address'))
            <div class="single-info">
                <div class="row">
                    <div class="col-lg-5 col-md-6">
                        <div class="">
                            @lang('student.permanent_address')
                        </div>
                    </div>

                    <div class="col-lg-7 col-md-7">
                        <div class="">
                            {{ @$student_detail->permanent_address }}
                        </div>
                    </div>
                </div>
            </div>
        @endif


        <div class="row  mt-30">
            <div class="col-lg-12">
                <div class="main-title">
                    <h4 class="stu-sub-head">@lang('student.interest_group_details')</h4>
                </div>
            </div>
           
        </div>

      {{-- end --}}
      <div class="single-info">
        <div class="row">
            <div class="col-lg-5 col-md-6">
                <div class="">
                    @lang('student.document_01_title')
                </div>
            </div>

            <div class="col-lg-7 col-md-7">
                <div class="">
                    
                    {{ @$student_detail->document_title_1 }}
                </div>
            </div>
        </div>
    </div>


    <div class="single-info">
        <div class="row">
            <div class="col-lg-5 col-md-6">
                <div class="">
                    @lang('student.document_02_title')
                </div>
            </div>

            <div class="col-lg-7 col-md-7">
                <div class="">
                    {{ @$student_detail->document_title_2 }}
                </div>
            </div>
        </div>
    </div>


      <div class="single-info">
          <div class="row">
              <div class="col-lg-5 col-md-6">
                  <div class="">
                      @lang('student.document_03_title')
                  </div>
              </div>

              <div class="col-lg-7 col-md-7">
                  <div class="">
                      {{ @$student_detail->document_title_3 }}
                  </div>
              </div>
          </div>
      </div>
 
 
      <div class="single-info">
          <div class="row">
              <div class="col-lg-5 col-md-6">
                  <div class="">
                      @lang('student.document_04_title')
                  </div>
              </div>

              <div class="col-lg-7 col-md-7">
                  <div class="">
                      {{ @$student_detail->document_title_4 }}
                  </div>
              </div>
          </div>
      </div>
 



      
        <!-- Start Other Information Part -->
      <div class="row  mt-30">
        <div class="col-lg-12">
            <div class="main-title">
                <h4 class="stu-sub-head">@lang('student.baptism_details')</h4>
            </div>
        </div>
       
    </div>
      <div class="single-info">
        <div class="row">
            <div class="col-lg-5 col-md-6">
                <div class="">
                    @lang('student.baptism_status')
                </div>
            </div>

            <div class="col-lg-7 col-md-7">
                <div class="">
                    {{ @$student_detail->baptism_status }}
                </div>
            </div>
        </div>
    </div>
    <div class="single-info">
        <div class="row">
            <div class="col-lg-5 col-md-6">
                <div class="">
                    @lang('student.baptism_type')
                </div>
            </div>

            <div class="col-lg-7 col-md-7">
                <div class="">
                    {{@$student_detail->type_of_baptism }}
                </div>
            </div>
        </div>
    </div>
    <div class="single-info">
        <div class="row">
            <div class="col-lg-5 col-md-6">
                <div class="">
                    @lang('student.place_of_baptism')
                </div>
            </div>

            <div class="col-lg-7 col-md-7">
                <div class="">
                    {{@$student_detail->place_of_baptism }}
                </div>
            </div>
        </div>
    </div>
    <div class="single-info">
        <div class="row">
            <div class="col-lg-5 col-md-6">
                <div class="">
                    @lang('student.date_of_baptism')
                </div>
            </div>

            <div class="col-lg-7 col-md-7">
                <div class="">
                    {{@$student_detail->date_of_baptism }}
                </div>
            </div>
        </div>
    </div>

    <div class="single-info">
        <div class="row">
            <div class="col-lg-5 col-md-6">
                <div class="">
                    @lang('student.baptism_cert_no')
                </div>
            </div>

            <div class="col-lg-7 col-md-7">
                <div class="">
                    {{ @$student_detail->baptism_cert_no}}
                </div>
            </div>
        </div>
    </div>

      <div class="single-info">
          <div class="row">
              <div class="col-lg-5 col-md-6">
                  <div class="">
                      @lang('student.baptism_off_minister')
                  </div>
              </div>

              <div class="col-lg-7 col-md-7">
                  <div class="">
                      {{ @$student_detail->baptism_off_minister }}
                  </div>
              </div>
          </div>
      </div>
 
 
      <div class="single-info">
          <div class="row">
              <div class="col-lg-5 col-md-6">
                  <div class="">
                      @lang('student.baptism_cert_no')
                  </div>
              </div>

              <div class="col-lg-7 col-md-7">
                  <div class="">
                      {{ @$student_detail->baptism_cert_no }}
                  </div>
              </div>
          </div>
      </div>
 
        <!-- Start Other Information Part -->





      
        <!-- Start confirmation Part -->
        <div class="row  mt-30">
            <div class="col-lg-12">
                <div class="main-title">
                    <h4 class="stu-sub-head">@lang('student.confirmation_details')</h4>
                </div>
            </div>
           
        </div>
          <div class="single-info">
            <div class="row">
                <div class="col-lg-5 col-md-6">
                    <div class="">
                        @lang('student.confirmation_status')
                    </div>
                </div>
    
                <div class="col-lg-7 col-md-7">
                    <div class="">
                        {{ @$student_detail->confirmation_status }}
                    </div>
                </div>
            </div>
        </div>
        <div class="single-info">
            <div class="row">
                <div class="col-lg-5 col-md-6">
                    <div class="">
                        @lang('student.date_of_confirmation')
                    </div>
                </div>
    
                <div class="col-lg-7 col-md-7">
                    <div class="">
                        {{@$student_detail->confirmation_date }}
                    </div>
                </div>
            </div>
        </div>
        <div class="single-info">
            <div class="row">
                <div class="col-lg-5 col-md-6">
                    <div class="">
                        @lang('student.place_of_baptism')
                    </div>
                </div>
    
                <div class="col-lg-7 col-md-7">
                    <div class="">
                        {{@$student_detail->ageconfirmed }}
                    </div>
                </div>
            </div>
        </div>
        <div class="single-info">
            <div class="row">
                <div class="col-lg-5 col-md-6">
                    <div class="">
                        @lang('student.date_of_baptism')
                    </div>
                </div>
    
                <div class="col-lg-7 col-md-7">
                    <div class="">
                        {{@$student_detail->place_of_confirmation }}
                    </div>
                </div>
            </div>
        </div>
    
        <div class="single-info">
            <div class="row">
                <div class="col-lg-5 col-md-6">
                    <div class="">
                        @lang('student.confirmation_cert_no')
                    </div>
                </div>
    
                <div class="col-lg-7 col-md-7">
                    <div class="">
                        {{ @$student_detail->confirmation_cert_no}}
                    </div>
                </div>
            </div>
        </div>
    
          <div class="single-info">
              <div class="row">
                  <div class="col-lg-5 col-md-6">
                      <div class="">
                          @lang('student.confirmation_off_minister')
                      </div>
                  </div>
    
                  <div class="col-lg-7 col-md-7">
                      <div class="">
                          {{ @$student_detail->bibleverseused }}
                      </div>
                  </div>
              </div>
          </div>
     

          
     
          <div class="single-info">
              <div class="row">
                  <div class="col-lg-5 col-md-6">
                      <div class="">
                          @lang('student.baptism_cert_no')
                      </div>
                  </div>
    
                  <div class="col-lg-7 col-md-7">
                      <div class="">
                          {{ @$student_detail->confirmation_off_minister }}
                      </div>
                  </div>
              </div>
          </div>
     
            <!-- Start Other Information Part -->



<!-- Start confirmation Part -->
<div class="row  mt-30">
    <div class="col-lg-12">
        <div class="main-title">
            <h4 class="stu-sub-head">@lang('student.marriage_details')</h4>
        </div>
    </div>
   
</div>
  <div class="single-info">
    <div class="row">
        <div class="col-lg-5 col-md-6">
            <div class="">
                @lang('student.marriage_status')
            </div>
        </div>

        <div class="col-lg-7 col-md-7">
            <div class="">
                {{ @$student_detail->marriage_status }}
            </div>
        </div>
    </div>
</div>
<div class="single-info">
    <div class="row">
        <div class="col-lg-5 col-md-6">
            <div class="">
                @lang('student.date_of_marriage')
            </div>
        </div>

        <div class="col-lg-7 col-md-7">
            <div class="">
                {{@$student_detail->date_of_marriage }}
            </div>
        </div>
    </div>
</div>
<div class="single-info">
    <div class="row">
        <div class="col-lg-5 col-md-6">
            <div class="">
                @lang('student.marriage_type')
            </div>
        </div>

        <div class="col-lg-7 col-md-7">
            <div class="">
                {{@$student_detail->marriage_type }}
            </div>
        </div>
    </div>
</div>
<div class="single-info">
    <div class="row">
        <div class="col-lg-5 col-md-6">
            <div class="">
                @lang('student.place_of_marriage')
            </div>
        </div>

        <div class="col-lg-7 col-md-7">
            <div class="">
                {{@$student_detail->place_of_marriage }}
            </div>
        </div>
    </div>
</div>

<div class="single-info">
    <div class="row">
        <div class="col-lg-5 col-md-6">
            <div class="">
                @lang('student.marriage_cert_no')
            </div>
        </div>

        <div class="col-lg-7 col-md-7">
            <div class="">
                {{ @$student_detail->marriage_cert_no}}
            </div>
        </div>
    </div>
</div>

  <div class="single-info">
      <div class="row">
          <div class="col-lg-5 col-md-6">
              <div class="">
                  @lang('student.marriage_off_minister')
              </div>
          </div>

          <div class="col-lg-7 col-md-7">
              <div class="">
                  {{ @$student_detail->marriage_off_minister }}
              </div>
          </div>
      </div>
  </div>


   
    <!-- Start Other Information Part -->



<!-- Start confirmation Part -->
<div class="row  mt-30">
    <div class="col-lg-12">
        <div class="main-title">
            <h4 class="stu-sub-head">@lang('student.family_details')</h4>
        </div>
    </div>
   
</div>
  <div class="single-info">
    <div class="row">
        <div class="col-lg-5 col-md-6">
            <div class="">
                @lang('student.spouse_name')
            </div>
        </div>

        <div class="col-lg-7 col-md-7">
            <div class="">
                {{ @$student_detail->spouse_name }}
            </div>
        </div>
    </div>
</div>
<div class="single-info">
    <div class="row">
        <div class="col-lg-5 col-md-6">
            <div class="">
                @lang('student.spouse_date_of_birth')
            </div>
        </div>

        <div class="col-lg-7 col-md-7">
            <div class="">
                {{@$student_detail->spouse_date_of_birth }}
            </div>
        </div>
    </div>
</div>
<div class="single-info">
    <div class="row">
        <div class="col-lg-5 col-md-6">
            <div class="">
                @lang('student.spouse_chucrh')
            </div>
        </div>

        <div class="col-lg-7 col-md-7">
            <div class="">
                {{@$student_detail->spouse_chucrh }}
            </div>
        </div>
    </div>
</div>
<div class="single-info">
    <div class="row">
        <div class="col-lg-5 col-md-6">
            <div class="">
                @lang('student.child_name1')
            </div>
        </div>

        <div class="col-lg-7 col-md-7">
            <div class="">
                {{@$student_detail->child_name1 }}
            </div>
        </div>
    </div>
</div>

<div class="single-info">
    <div class="row">
        <div class="col-lg-5 col-md-6">
            <div class="">
                @lang('student.child_name2')
            </div>
        </div>

        <div class="col-lg-7 col-md-7">
            <div class="">
                {{ @$student_detail->child_name2}}
            </div>
        </div>
    </div>
</div>
 


   
    <!-- Start Other Information Part -->






    

<!-- Start confirmation Part -->
<div class="row  mt-30">
    <div class="col-lg-12">
        <div class="main-title">
            <h4 class="stu-sub-head">@lang('student.school_details')</h4>
        </div>
    </div>
   
</div>
  <div class="single-info">
    <div class="row">
        <div class="col-lg-5 col-md-6">
            <div class="">
                @lang('student.school_admission_date')
            </div>
        </div>

        <div class="col-lg-7 col-md-7">
            <div class="">
                {{ @$student_detail->school_admission_date }}
            </div>
        </div>
    </div>
</div>
<div class="single-info">
    <div class="row">
        <div class="col-lg-5 col-md-6">
            <div class="">
                @lang('student.school_completion_date')
            </div>
        </div>

        <div class="col-lg-7 col-md-7">
            <div class="">
                {{@$student_detail->school_completion_date }}
            </div>
        </div>
    </div>
</div>
<div class="single-info">
    <div class="row">
        <div class="col-lg-5 col-md-6">
            <div class="">
                @lang('student.school_telephone')
            </div>
        </div>

        <div class="col-lg-7 col-md-7">
            <div class="">
                {{@$student_detail->school_telephone }}
            </div>
        </div>
    </div>
</div>
<div class="single-info">
    <div class="row">
        <div class="col-lg-5 col-md-6">
            <div class="">
                @lang('student.school_location')
            </div>
        </div>

        <div class="col-lg-7 col-md-7">
            <div class="">
                {{@$student_detail->school_location }}
            </div>
        </div>
    </div>
</div> 
 


   
    <!-- Start Other Information Part -->








    
        <!-- Start Other Information Part -->
        <h4 class="stu-sub-head mt-40">@lang('student.other_information')</h4>
        @if (is_show('blood_group'))
            <div class="single-info">
                <div class="row">
                    <div class="col-lg-5 col-md-5">
                        <div class="">
                            @lang('common.blood_group')
                        </div>
                    </div>

                    <div class="col-lg-7 col-md-6">
                        <div class="">
                            {{ isset($student_detail->bloodgroup_id) ? @$student_detail->bloodGroup->base_setup_name : '' }}
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if (is_show('student_group_id'))
            <div class="single-info">
                <div class="row">
                    <div class="col-lg-5 col-md-5">
                        <div class="">
                            @lang('student.student_group')
                        </div>
                    </div>

                    <div class="col-lg-7 col-md-6">
                        <div class="">
                            {{ isset($student_detail->student_group_id) ? @$student_detail->group->group : '' }}
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if (is_show('height'))
            <div class="single-info">
                <div class="row">
                    <div class="col-lg-5 col-md-5">
                        <div class="">
                            @lang('student.height')
                        </div>
                    </div>

                    <div class="col-lg-7 col-md-6">
                        <div class="">
                            {{ isset($student_detail->height) ? @$student_detail->height : '' }}
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if (is_show('weight'))
            <div class="single-info">
                <div class="row">
                    <div class="col-lg-5 col-md-5">
                        <div class="">
                            @lang('student.weight')
                        </div>
                    </div>

                    <div class="col-lg-7 col-md-6">
                        <div class="">
                            {{ isset($student_detail->weight) ? @$student_detail->weight : '' }}
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if (is_show('previous_school_details'))
            <div class="single-info">
                <div class="row">
                    <div class="col-lg-5 col-md-5">
                        <div class="">
                            @lang('student.previous_school_details')
                        </div>
                    </div>

                    <div class="col-lg-7 col-md-6">
                        <div class="">
                            {{ isset($student_detail->previous_school_details) ? @$student_detail->previous_school_details : '' }}
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if (is_show('national_id_number'))
            <div class="single-info">
                <div class="row">
                    <div class="col-lg-5 col-md-5">
                        <div class="">
                            @lang('student.national_id_number')
                        </div>
                    </div>

                    <div class="col-lg-7 col-md-6">
                        <div class="">
                            {{ isset($student_detail->national_id_no) ? @$student_detail->national_id_no : '' }}
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if (is_show('local_id_number'))
            <div class="single-info">
                <div class="row">
                    <div class="col-lg-5 col-md-5">
                        <div class="">
                            @lang('student.local_id_number')
                        </div>
                    </div>


                    <div class="col-lg-7 col-md-6">
                        <div class="">
                            {{ isset($student_detail->local_id_no) ? @$student_detail->local_id_no : '' }}
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if (is_show('bank_account_number'))
            <div class="single-info">
                <div class="row">
                    <div class="col-lg-5 col-md-5">
                        <div class="">
                            @lang('accounts.bank_account_number')
                        </div>
                    </div>

                    <div class="col-lg-7 col-md-6">
                        <div class="">
                            {{ isset($student_detail->bank_account_no) ? @$student_detail->bank_account_no : '' }}
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if (is_show('bank_name'))
            <div class="single-info">
                <div class="row">
                    <div class="col-lg-5 col-md-5">
                        <div class="">
                            @lang('student.bank_name')
                        </div>
                    </div>
                    <div class="col-lg-7 col-md-6">
                        <div class="">
                            {{ isset($student_detail->bank_name) ? @$student_detail->bank_name : '' }}
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if (is_show('ifsc_code'))
            <div class="single-info">
                <div class="row">
                    <div class="col-lg-5 col-md-5">
                        <div class="">
                            @lang('student.ifsc_code')
                        </div>
                    </div>

                    <div class="col-lg-7 col-md-6">
                        <div class="">
                            {{ isset($student_detail->ifsc_code) ? @$student_detail->ifsc_code : '' }}
                        </div>
                    </div>
                </div>
            </div>
        @endif



        <!-- Start Parent Part -->
        <h4 class="stu-sub-head mt-40">@lang('student.Parent_Guardian_Details')</h4>
         

         

        <div class="d-flex">
            @if (is_show('guardians_photo'))
                <div class="mr-20 mt-20">
                    <img class="student-meta-img img-100"
                         src="{{ file_exists(@$student_detail->guardians_photo) ? asset($student_detail->guardians_photo) : asset('public/uploads/staff/demo/guardian.jpg') }}"
                         alt="">

                </div>
            @endif
            <div class="w-100">
                @if (is_show('guardians_name'))
                    <div class="single-info">
                        <div class="row">
                            <div class="col-lg-4 col-md-6">
                                <div class="">
                                    @lang('student.guardian_name')
                                </div>
                            </div>

                            <div class="col-lg-8 col-md-7">
                                <div class="">
                                    {{ $student_detail->parents != '' ? @$student_detail->guardians_name : '' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
 
                @if (is_show('guardians_phone'))
                    <div class="single-info">
                        <div class="row">
                            <div class="col-lg-4 col-md-6">
                                <div class="">
                                    @lang('student.phone_number')
                                </div>
                            </div>

                            <div class="col-lg-8 col-md-7">
                                <div class="">
                                    {{ $student_detail->parents != '' ? @$student_detail->guardians_phone : '' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="single-info">
                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <div class="">
                                @lang('student.relation_with_guardian')
                            </div>
                        </div>

                        <div class="col-lg-8 col-md-7">
                            <div class="">
                                {{ $student_detail->parents != '' ? @$student_detail->guardians_relation : '' }}
                            </div>
                        </div>
                    </div>
                </div>
                @if (is_show('guardians_occupation'))
                    <div class="single-info">
                        <div class="row">
                            <div class="col-lg-4 col-md-6">
                                <div class="">
                                    @lang('student.occupation')
                                </div>
                            </div>

                            <div class="col-lg-8 col-md-7">
                                <div class="">
                                    {{ $student_detail->parents != '' ? @$student_detail->guardians_occupation : '' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @if (is_show('guardians_address'))
                    <div class="single-info">
                        <div class="row">
                            <div class="col-lg-4 col-md-6">
                                <div class="">
                                    @lang('student.guardian_address')
                                </div>
                            </div>

                            <div class="col-lg-8 col-md-7">
                                <div class="">
                                    {{ $student_detail->parents != '' ? @$student_detail->guardians_address : '' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <!-- End Parent Part -->





        
    
    </div>



    
</div>
