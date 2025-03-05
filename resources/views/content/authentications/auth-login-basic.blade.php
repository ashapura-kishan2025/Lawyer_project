@php
    $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Lawyer - Login')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
 
    <script>
        $(document).ready(function() {
            setTimeout(function() {
                $("#status_inactive_message").hide("slow");
            }, 3000); // 3 seconds
        });
    </script>
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
                                <h4 class="mb-1 px-3 welcome-txt">Welcome Back! 👋</h4>
                                <p class="mb-6 px-3">Sign-in to continue to Lawyer Dashboard</p>
                                @if (session('status'))
                                    <div class="alert alert-success">
                                        {{ session('status') }}
                                    </div>
                                @endif
                                @error('status_inactive')
                                    <div class="px-3">
                                        <div class="alert alert-danger" id="status_inactive_message">{{ $message }}</div>
                                    </div>
                                @enderror
                                <form id="formAuthentication" class="mb-3 px-3 pe-md-5" action="{{ route('login') }}"
                                    method="POST">
                                    @csrf
                                    <div class="mb-6">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="email" name="email" value="{{ old('email') }}"
                                            placeholder="Enter email" required autofocus>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-5 form-password-toggle">
                                        <div class="d-flex justify-content-between">
                                            <label class="form-label" for="password">Password</label>
                                            <a href="{{ route('forget-password') }}">
                                                <p class="mb-1">Forgot Password?</p>
                                            </a>
                                        </div>
                                        <div class="input-group input-group-merge">
                                            <input type="password" id="password"
                                                class="form-control @error('password') is-invalid @enderror" name="password"
                                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                                required />
                                            <span class="input-group-text cursor-pointer"><i
                                                    class="ti ti-eye-off"></i></span>
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <div class="form-check mb-0 ms-2">
                                            <input class="form-check-input" type="checkbox" id="remember-me"
                                                name="remember">
                                            <label class="form-check-label" for="remember-me">
                                                Remember Me
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-6">
                                        <button class="btn d-grid w-100 login-button" type="submit">Login</button>
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
