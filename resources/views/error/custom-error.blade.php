@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Not Authorized - Pages')

@section('page-style')
<!-- Page -->
<link rel="stylesheet" href="{{ asset('assets/css/page-misc.css') }}">
@endsection

@section('content')
<!-- Not Authorized -->
<div class="container-xxl container-p-y">
  <div class="misc-wrapper">
    <h2 class="mb-1 mx-2">You are not authorized!</h2>
    <p class="mb-4 mx-2">You do not have permission to view this page.</p>
  <a  href="{{ session()->get('intended_url', route('dashboard')) }}" class="btn btn-primary mb-4">Back to Previous</a>

    <div class="mt-4">
      <img src="{{ asset('assets/img/illustrations/page-misc-you-are-not-authorized.png') }}" alt="page-misc-not-authorized" width="170" class="img-fluid">
    </div>
  </div>
</div>
<div class="container-fluid misc-bg-wrapper">
  <img src="{{ asset('assets/img/illustrations/bg-shape-image-'.$configData['style'].'.png') }}" alt="page-misc-not-authorized" data-app-light-img="illustrations/bg-shape-image-light.png" data-app-dark-img="illustrations/bg-shape-image-dark.png">
</div>
<!-- /Not Authorized -->
@endsection
