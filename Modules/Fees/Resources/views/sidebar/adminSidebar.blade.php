
@if(userPermission(1130) && menuStatus(1130))
    <li data-position="{{menuPosition(1130)}}" class="sortable_li">
        <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
            <div class="nav_icon_small">
                <span class="flaticon-test"></span>
            </div>
            <div class="nav_title">
                @lang('fees.fees')
            </div>
        </a>
        <ul class="list-unstyled" id="subMenuFees">
            @if(userPermission(1131) && menuStatus(1131))
                <li data-position="{{menuPosition(1131)}}">
                    <a href="{{ route('fees.fees-group') }}">@lang('fees.fees_group')</a>
                </li>
            @endif

            @if(userPermission(1135) && menuStatus(1135))
                <li data-position="{{menuPosition(1135)}}">
                    <a href="{{ route('fees.fees-type') }}">@lang('fees.fees_type')</a>
                </li>
            @endif

            @if(userPermission(1139) && menuStatus(1139))
                <li data-position="{{menuPosition(1139)}}">
                    <a href="{{ route('fees.fees-invoice-list') }}">@lang('fees::feesModule.fees_invoice')</a>
                </li>
            @endif

          
   
                    @if(userPermission(1159) && menuStatus(1159))
                        <li data-position="{{menuPosition(1159)}}">
                            <a href="{{ route('fees.payment-report')}}">
                                @lang('fees::feesModule.payment_report')
                            </a>
                        </li>
                    @endif

           {{--      <li>
                        <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
                            @lang('reports.report')
                        </a>
                        <ul class="list-unstyled">
                        
                        
                            
                            
                        </ul>
                    </li>--}}
        </ul>
    </li>
@endif