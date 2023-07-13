@extends('layouts.generic')
@section('page_title', __('Verify your email'))

@section('content')
    <div class="container py-5 my-5">
        <div class="col-12 col-md-8 offset-md-2 mt-5">
            <div class="d-flex justify-content-center">
                <div class="col-12 col-md-6 d-none d-md-flex justify-content-center align-items-center">
                    <img src="{{asset("/img/verify-email.svg")}}" class="img-fluid ">
                </div>
                <div class="col-12 col-md-7 d-flex align-items-center">
                    <div class="d-flex justify-content-center align-items-center">
                        <div class="col">
                            <h3 class="h1s text-bold">{{__("Verify your email address")}}</h3>
                            @if (session('resent'))
                                <div class="alert alert-success text-white my-3" role="alert">
                                    {{ __('A fresh verification link has been sent to your email address.') }}
                                </div>
                            @endif
                            <p class="my-3">
                                {{ __('Before proceeding, please check your email for a verification link.') }}
                                {{ __('If you did not receive the email') }}.
                            </p>
                            <form class="d-flex w-100 flex-row flex-row " method="POST" action="{{ route('verification.resend') }}">
                                @csrf
                                <button type="submit" class="btn btn-primary">{{ __('click here to request another') }}</button>
                            </form>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
