@extends('layouts.no-nav')

@section('content')

    <div class="container">
        <div class=" d-flex justify-content-center align-items-center min-vh-65" >
            <div class="error-container d-flex flex-column">
                <div class="d-flex justify-content-center align-items-center">
                </div>
                <div class="text-center">
                    <h3 class="text-bold">{{__("You are currently not connected to any networks.")}}</h3>
                </div>
            </div>
        </div>
    </div>

@endsection
