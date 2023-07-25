 

{{-- @if (userPermission(401) && menuStatus(401))
    <li data-position="{{ menuPosition(401) }}">
        <a href="{{ route('manage-currency') }}">@lang('system_settings.manage_currency')</a>
    </li>
@endif --}}

{{-- @if (userPermission(428) && menuStatus(428))

    <li data-position="{{ menuPosition(428) }}">
        <a href="{{ route('base_setup') }}">@lang('system_settings.base_setup')</a>
    </li>
@endif --}}

 

@if (userPermission(456) && menuStatus(465))

    <li data-position="{{ menuPosition(465) }}">
        <a href="{{ route('backup-settings') }}">@lang('system_settings.backup_settings')</a>
    </li>
@endif




 
@if (userPermission(4000) && menuStatus(4000))

    <li data-position="{{ menuPosition(4000) }}">
        <a href="{{ route('utility') }}">@lang('system_settings.utilities')</a>
    </li>
@endif

 


