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
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner d-flex align-items-center justify-content-center">
                <!-- Reset password -->
                <div class="card login-card">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-12 col-lg-6 col-md-6 col-sm-12">
                                <img class="login-banner-img"
                                    src="{{ asset('assets/img/images/login-banner/Login_Banner.png') }}"
                                    alt="">
                            </div>
                            <div class="col-12 col-lg-6 col-md-6 col-sm-12 p-5">
                                <h4 class="mb-1 px-3 welcome-txt">Create new password</h4>
                                <p class="mb-6 px-3">Your new password must be different from previous used password.</p>


                                <form id="formAuthentication" class="mb-3 px-3 pe-md-5"
                                    action="{{ route('password.update') }}" method="POST">
                                    @csrf
                                    @error('email')
                                        <div class="alert alert-danger mt-2 mb-2">{{ $message }}</div>
                                    @enderror

                                    <input type="hidden" name="email" value="{{ $_GET['email'] }}" required readonly>
                                    <input type="hidden" name="token" value={{ $token }} required readonly>
                                    <div class="mb-5 form-password-toggle">
                                        <div class="d-flex justify-content-between">
                                            <label class="form-label" for="password">Password</label>
                                        </div>
                                        <div class="input-group input-group-merge">
                                            <input type="password" id="password" class="form-control" name="password"
                                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                                aria-describedby="password" required />
                                            <span class="input-group-text cursor-pointer"><i
                                                    class="ti ti-eye-off"></i></span>
                                        </div>
                                        @error('password')
                                            <div class="alert alert-danger mt-2 mb-2">{{ $message }}</div>
                                        @enderror
                                        <div class="mt-2">
                                            <span>Must be atleast 8 characters</span>
                                        </div>
                                    </div>
                                    <div class="mb-4 form-password-toggle">
                                        <div class="d-flex justify-content-between">
                                            <label class="form-label" for="password">Confirm Password</label>
                                        </div>
                                        <div class="input-group input-group-merge">
                                            <input type="password" id="password" class="form-control"
                                                name="password_confirmation"
                                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                                aria-describedby="password" required />
                                            <span class="input-group-text cursor-pointer"><i
                                                    class="ti ti-eye-off"></i></span>
                                        </div>
                                    </div>
                                    <div class="mb-5">
                                        <div class="form-check mb-0 ms-2">
                                            <input class="form-check-input" type="checkbox" id="remember-me">
                                            <label class="form-check-label" for="remember-me">
                                                Remember Me
                                            </label>
                                        </div>
                                    </div>
                                    <div class="">
                                        <button class="btn d-grid w-100 login-button" type="submit">Reset Password</button>
                                    </div>

                                    <div class="d-flex justify-content-center pt-5">
                                        <p class="">Wait, I remember my password... </p>
                                        <a href="{{ route('auth-login-basic') }}"> Click here</a>
                                    </div>
                                </form>



                            </div>
                        </div>
                    </div>
                </div>
                <!-- Reset password -->
            </div>
        </div>
    </div>
@endsection
