@extends('layouts.generic')
@section('page_title', __('Forbidden'))

@section('content')
    <div class="container">
        <div class=" d-flex justify-content-center align-items-center min-vh-65" >
            <div class="error-container d-flex flex-column">
                <div class="d-flex justify-content-center align-items-center">
                    <img src="{{asset('/img/'.($exception->getMessage() ? '403-profile.svg' : '500.svg'))}}">
                </div>
                <div class="text-center">
                    <h3 class="text-bold"> 403 | {{__($exception->getMessage() ?: 'Forbidden')}}</h3>
                    @if($exception->getMessage())<p>{{__("This content is not available for you at the moment.")}}</p>@endif
                    <div class="d-flex justify-content-center mt-2">
                        <a href="{{route('home')}}" class="right">{{__('Go home')}} »</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
