@php


    $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Lawyer - Forgot Password')

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/pages-auth.js'])
@endsection

@section('content')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner d-flex align-items-center justify-content-center">
                <!-- Login -->
                <div class="card login-card">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-12 col-lg-6 col-md-6 col-sm-12">
                                <img class="login-banner-img"
                                    src="{{ asset('assets/img/images/login-banner/Login_Banner.png') }}"
                                    alt="">
                            </div>
                            <div class="col-12 col-lg-6 col-md-6 col-sm-12 p-5">
                                <h4 class="mb-1 px-3 welcome-txt">Forgot Password ?</h4>
                                <p class="mb-6 px-3">Reset password with Lawyer Dashboard</p>
                                <form id="formAuthentication" class="mb-3 px-3 pe-md-5"
                                    action="{{ route('send-forget-pass-email') }}" method="POST">
                                    @csrf
                                    @if (session('status'))
                                        <div class="alert alert-success">
                                            {{ session('status') }}
                                        </div>
                                    @else
                                        <p class="mb-4 px-3 p-2  alert-warning  ">Enter your email and instructions
                                            will
                                            be
                                            sent to
                                            you!
                                        </p>
                                    @endif
                                    <div class="mb-6">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="text" class="form-control" id="email" name="email"
                                            placeholder="Enter email address" autofocus required>
                                        @error('email')
                                            <div class="alert alert-danger mt-2 mb-2">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-6">
                                        <button class="btn d-grid w-100 login-button" type="submit">Send Reset
                                            Link</button>
                                    </div>

                                    <div class="mt-6 d-flex justify-content-center pt-5">
                                        <p class="">Wait, I remember my password... </p>
                                        <a href="{{ route('auth-login-basic') }}"> Click here</a>
                                    </div>
                                </form>



                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
