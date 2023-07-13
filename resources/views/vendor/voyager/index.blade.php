@extends('voyager::master')

@section('page_header')
    <h1 class="page-title">
        <i class="voyager-dashboard"></i> {{__("Platform Statistics")}}
    </h1>
@stop

@section('content')
    <div class="page-content">
        @include('voyager::alerts')
        @include('voyager::dimmers')
        <div class="analytics-container">
            @include('elements.admin.metrics')

        </div>
    </div>
@stop
