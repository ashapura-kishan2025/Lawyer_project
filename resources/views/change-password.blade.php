@php
    //custom
    $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts.horizontalLayout')

@section('title', 'Change Password')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

<!-- Page Styles -->
@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-account-settings.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js'])

@endsection

<!-- Page Scripts -->
@section('page-script')
    {{-- @vite(['resources/assets/js/pages-account-settings-security.js', 'resources/assets/js/modal-enable-otp.js']) --}}
    <script>
        $(document).ready(function() {
            $("#reset-btn").click(function() {
                // Remove 'is-invalid' class from all input fields
                $(".form-control").removeClass("is-invalid");
                // Remove error messages
                $(".invalid-feedback").text("").hide();
                $("#currentPasswordValFeedback").text("");
                $("#newPasswordValFeedback").text("");
                $("#confirmPasswordValFeedback").text("");
            });


            // Clear previous error messages on focus
            $("input").on("focus", function() {
                $(this).removeClass("is-invalid");
                $(this).siblings(".invalid-feedback").text("").removeClass("text-danger");
            });

            $("#formNewPassword").on("submit", function(e) {
                let isValid = true;

                // Get field values
                let currentPassword = $("#currentPassword").val().trim();
                let newPassword = $("#newPassword").val().trim();
                let confirmPassword = $("#confirmPassword").val().trim();

                // Regular expressions for password strength
                let passwordRegex = /^(?=.*[A-Z])(?=.*[!@#$%^&*])(?=.*[0-9]).{8,}$/;

                // Reset previous error messages
                $(".invalid-feedback").text("");
                $(".form-control").removeClass("is-invalid");

                // 1. Validate Current Password
                if (currentPassword === "") {
                    isValid = false;
                    $("#currentPassword").addClass("is-invalid");
                    $("#currentPasswordValFeedback").text("Current password is required.");
                } else {
                    $("#currentPassword").removeClass("is-invalid");
                    $("#currentPasswordValFeedback").text("");
                }

                // 2. Validate New Password
                if (newPassword === "") {
                    isValid = false;
                    $("#newPassword").addClass("is-invalid");
                    $("#newPasswordValFeedback").text("New password is required.");
                } else if (newPassword.length < 8) {
                    isValid = false;
                    $("#newPassword").addClass("is-invalid");
                    $("#newPasswordValFeedback").text("Password must be at least 8 characters.");
                } else if (!passwordRegex.test(newPassword)) {
                    isValid = false;
                    $("#newPassword").addClass("is-invalid");
                    $("#newPasswordValFeedback").text(
                        "Must have one uppercase, one special character, and one number.");
                } else if (newPassword === currentPassword) {
                    isValid = false;
                    $("#newPassword").addClass("is-invalid");
                    $("#newPasswordValFeedback").text(
                        "New password must be different from the current password.");
                } else {
                    $("#newPassword").removeClass("is-invalid");
                    $("#newPasswordValFeedback").text("");
                }

                // 3. Validate Confirm Password
                if (confirmPassword === "") {
                    isValid = false;
                    $("#confirmPassword").addClass("is-invalid");
                    $("#confirmPasswordValFeedback").text("The Confirm Password is required");
                } else if (confirmPassword !== newPassword) {
                    isValid = false;
                    $("#confirmPassword").addClass("is-invalid");
                    $("#confirmPasswordValFeedback").text("Password must match.");
                } else {
                    $("#confirmPassword").removeClass("is-invalid");
                    $("#confirmPasswordValFeedback").text("");
                }

                // Prevent form submission if validation fails
                if (!isValid) {
                    e.preventDefault();
                }
            });

        });
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!-- Change Password -->
            <div class="card mb-6">

                <h5 class="card-header">Change Password</h5>
                @if (session('success'))
                    <div class="alert alert-success w-100">{{ session('success') }}</div>
                @endif
                <div class="card-body pt-1">
                    <form id="formNewPassword" method="POST" action="{{ route('changePassword') }}">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-md-6 form-password-toggle">
                                <label class="form-label" for="currentPassword">Current Password <sup>*</sup> </label>
                                <div class="input-group input-group-merge">
                                    <input class="form-control @error('currentPassword') is-invalid @enderror"
                                        type="password" name="currentPassword" id="currentPassword" required />
                                    <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                                </div>
                                <span id="currentPasswordValFeedback" class="text-danger"></span>
                                @error('currentPassword')
                                    <span class="text-danger">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3 col-md-6 form-password-toggle">
                                <label class="form-label" for="newPassword">New Password <sup>*</sup></label>
                                <div class="input-group input-group-merge">
                                    <input class="form-control  @error('newPassword') is-invalid @enderror" type="password"
                                        id="newPassword" name="newPassword" required />
                                    <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                                </div>
                                <span id="newPasswordValFeedback" class="text-danger"></span>
                                @error('newPassword')
                                    <span class="text-danger">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-3 col-md-6 form-password-toggle">
                                <label class="form-label" for="confirmPassword">Confirm New Password <sup>*</sup></label>
                                <div class="input-group input-group-merge">
                                    <input class="form-control @error('confirmPassword') is-invalid @enderror"
                                        type="password" name="confirmPassword" id="confirmPassword" required />
                                    <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                                </div>
                                <span id="confirmPasswordValFeedback" class="text-danger"></span>
                                @error('confirmPassword')
                                    <span class="text-danger">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary me-3">Save changes</button>
                            <button type="reset" id="reset-btn" class="btn btn-secondary">Reset</button>
                        </div>
                    </form>

                </div>
            </div>
            <!--/ Change Password -->
        </div>
    </div>

@endsection
