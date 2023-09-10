@extends('backEnd.master')
    @section('title') 
        @lang('fees::feesModule.fees_invoice')
    @endsection
@section('mainContent')
    @include('fees::_allMonthlyTitheList',['role'=>'admin'])
@endsection
