@php
    $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Lawyer - Reset Password')

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/pages-auth.js'])
@endsection

@section('content')
    <div class="container-xxl">
        <div class="authentication-wrapper ">
            <div class="authentication-inner d-flex align-items-center justify-content-center ">
                <div class="card login-card">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-12 col-md-6 ">
                                <img class="login-banner-img "
                                    src="{{ asset('assets/img/images/login-banner/Login_Banner.png') }}" alt="">
                            </div>
                            <div
                                class="col-12 col-lg-6 col-md-6 col-sm-12 p-3  d-flex justify-content-center align-items-center flex-column">
                                <span class=" rounded-circle bg-primary my-5" style="padding:inherit ">
                                    <i class="fa fa-regular fa-check text-white p-1"
                                        style=" font-size: x-large;font-weight:1000;"></i>
                                </span>
                                <p class="mb-1 px-3 text-primary fs-5">Well done!</p>
                                <p class="mb-6 px-3">Aww yeah, Password is successfully changed</p>
                                <a class="btn btn-primary w-75 rounded-0 " href="{{ route('auth-login-basic') }}">Back to
                                    login
                                    page</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
