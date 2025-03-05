@php
    $customizerHidden = 'customizer-hide';
@endphp

{{-- @extends('layouts/contentNavbarLayout') --}}
@extends('layouts.horizontalLayout')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/apex-charts/apex-charts.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/apex-charts/apexcharts.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/dashboards-crm.js'])
@endsection
@section('title')
    Lawyer - Dashboard
@endsection
@section('content')
    {{-- START : Top 4 cards  --}}
    <div class="row  mb-6">
        <!-- Card Border Shadow -->
        <div class="col-lg-3 col-sm-6 my-2">
            <a href="{{ route('client.index') }}">
                <div class="card card-border-shadow-primary h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar me-4">
                                <span class="avatar-initial rounded "
                                    style="color: #7367f0;background-color:rgba(115, 103, 240, 0.16) "><i
                                        class="fa-solid fa-user-group"></i></span>
                            </div>
                            <h4 class="mb-0">{{ $userCount ?? 0 }}</h4>
                        </div>
                        <p class="mb-1">Clients</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-sm-6 my-2">
            <a href="{{ route('departments.index') }}">
                <div class="card card-border-shadow-danger h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar me-4">
                                <span class="avatar-initial rounded bg-label-danger"><i
                                        class="fa fa-thin fa-sitemap"></i></span>
                            </div>
                            <h4 class="mb-0">{{ $departmentCount ?? 0 }}</h4>
                        </div>
                        <p class="mb-1">Departments</p>

                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-sm-6 my-2">
            <a href="{{ route('quotes.index') }}">
                <div class="card card-border-shadow-info h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar me-4">
                                <span class="avatar-initial rounded bg-label-info"><i
                                        class="fa fa-regular fa-file-lines"></i></span>
                            </div>
                            <h4 class="mb-0">{{ $quoteCount ?? 0 }}</h4>
                        </div>
                        <p class="mb-1">Quotations</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-sm-6 my-2">
            <a href="{{ route('assignment.index') }}">
                <div class="card card-border-shadow-warning h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar me-4">
                                <span class="avatar-initial rounded bg-label-warning"><i class="fa fa-briefcase"></i></span>
                            </div>
                            <h4 class="mb-0">{{$assignmentCount ?? ''}}</h4>
                        </div>
                        <p class="mb-1">Assignments</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
    {{-- END : Top 4 cards  --}}

    {{-- START : Earning reports  --}}
    <!-- Earning Reports Tabs-->
    <div class="col-xl-12 col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <div class="card-title m-0">
                    <h5 class="mb-1">Earning Reports</h5>
                    <p class="card-subtitle">Yearly Earnings Overview</p>
                </div>
                <div class="dropdown">
                    <button class="btn btn-text-secondary rounded-pill text-muted border-0 p-2 me-n1" type="button"
                        id="earningReportsTabsId" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ti ti-dots-vertical ti-md text-muted"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="earningReportsTabsId">
                        <a class="dropdown-item" href="javascript:void(0);">View More</a>
                        <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs widget-nav-tabs pb-8 gap-4 mx-1 d-flex flex-nowrap" role="tablist">
                    <li class="nav-item">
                        <a href="javascript:void(0);"
                            class="nav-link btn active d-flex flex-column align-items-center justify-content-center"
                            role="tab" data-bs-toggle="tab" data-bs-target="#navs-orders-id"
                            aria-controls="navs-orders-id" aria-selected="true">
                            <div class="badge bg-label-secondary rounded p-2"><i class="ti ti-shopping-cart ti-md"></i>
                            </div>
                            <h6 class="tab-widget-title mb-0 mt-2">All</h6>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="javascript:void(0);"
                            class="nav-link btn d-flex flex-column align-items-center justify-content-center" role="tab"
                            data-bs-toggle="tab" data-bs-target="#navs-sales-id" aria-controls="navs-sales-id"
                            aria-selected="false">
                            <div class="badge bg-label-secondary rounded p-2"><i class="ti ti-chart-bar ti-md"></i></div>
                            <h6 class="tab-widget-title mb-0 mt-2"> Not Sent</h6>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="javascript:void(0);"
                            class="nav-link btn d-flex flex-column align-items-center justify-content-center" role="tab"
                            data-bs-toggle="tab" data-bs-target="#navs-profit-id" aria-controls="navs-profit-id"
                            aria-selected="false">
                            <div class="badge bg-label-secondary rounded p-2"><i class="ti ti-currency-dollar ti-md"></i>
                            </div>
                            <h6 class="tab-widget-title mb-0 mt-2">Unpaid</h6>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="javascript:void(0);"
                            class="nav-link btn d-flex flex-column align-items-center justify-content-center"
                            role="tab" data-bs-toggle="tab" data-bs-target="#navs-income-id"
                            aria-controls="navs-income-id" aria-selected="false">
                            <div class="badge bg-label-secondary rounded p-2"><i class="ti ti-chart-pie-2 ti-md"></i>
                            </div>
                            <h6 class="tab-widget-title mb-0 mt-2">Paid</h6>
                        </a>
                    </li>
                    {{-- <li class="nav-item">
                        <a href="javascript:void(0);"
                            class="nav-link btn d-flex align-items-center justify-content-center disabled" role="tab"
                            data-bs-toggle="tab" aria-selected="false">
                            <div class="badge bg-label-secondary rounded p-2"><i class="ti ti-plus ti-md"></i></div>
                        </a>
                    </li> --}}
                </ul>
                <div class="tab-content p-0 ms-0 ms-sm-2">
                    <div class="tab-pane fade show active" id="navs-orders-id" role="tabpanel">
                        <div id="earningReportsTabsOrders"></div>
                    </div>
                    <div class="tab-pane fade" id="navs-sales-id" role="tabpanel">
                        <div id="earningReportsTabsSales"></div>
                    </div>
                    <div class="tab-pane fade" id="navs-profit-id" role="tabpanel">
                        <div id="earningReportsTabsProfit"></div>
                    </div>
                    <div class="tab-pane fade" id="navs-income-id" role="tabpanel">
                        <div id="earningReportsTabsIncome"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- END : Earning reports  --}}
@endsection
