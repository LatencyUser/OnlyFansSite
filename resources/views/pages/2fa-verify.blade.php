@extends('layouts.generic')
@section('page_title', __('Verify your new device'))

@section('content')
    <div class="container py-5 my-5">
        <div class="col-12 col-md-8 offset-md-2 mt-5">
            <div class="d-flex justify-content-center">
                <div class="col-12 col-md-6 d-none d-md-flex justify-content-center align-items-center">
                    <img src="{{asset("/img/device-verify.svg")}}" class="img-fluid ">
                </div>
                <div class="col-12 col-md-7 d-flex align-items-center">
                    <div class="d-flex justify-content-center align-items-center">
                        <div class="col">
                            <h3 class="h1s text-bold">{{__("Verify your new device")}}</h3>
                            @if (session('resent'))
                                <div class="alert alert-success text-white my-3" role="alert">
                                    {{ __('A fresh verification code has been sent to your email address.') }}
                                </div>
                            @endif
                            <p class="my-3">
                                {{ __('For security reasons, we’ve sent you a code to your email to validate your account.') }}
                            </p>
                            @include('elements/message-alert', ['classes' =>'mb-3'])


                            <form method="POST" action="{{ route('2fa.post') }}">
                                @csrf

                                <div class="form-group mb-0">
                                    <div class="">
                                        <input id="code" type="code" class="form-control @error('code') is-invalid @enderror"  name="code" value="{{ old('code') }}" autofocus placeholder="123456">
                                        @error('code')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row mb-0 mt-3">
                                    <div class="col d-flex justify-content-between align-items-center">
                                        <a class="" href="{{ route('2fa.resend') }}">{{__("Resend Code")}}</a>
                                        <button type="submit" class="btn btn-grow btn-primary bg-gradient-primary ">
                                            {{__('Next')}}
                                        </button>
                                    </div>
                                </div>

                            </form>

                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
