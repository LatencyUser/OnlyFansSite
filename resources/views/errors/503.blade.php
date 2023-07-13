@extends('layouts.generic')
@section('page_title', __($exception->getMessage() ?: 'Service Unavailable'))

@section('content')
    <div class="container">
        <div class=" d-flex justify-content-center align-items-center min-vh-65" >
            <div class="error-container d-flex flex-column">
                <div class="d-flex justify-content-center align-items-center">
                    <img src="{{asset('/img/500.svg')}}">
                </div>
                <div class="text-center">
                    <h3 class="text-bold"> 503 | {{__($exception->getMessage() ?: 'Service Unavailable')}}</h3>
                    <div class="d-flex justify-content-center mt-2">
                        <a href="{{route('home')}}" class="right">{{__('Go home')}} »</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
