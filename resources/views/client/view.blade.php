@php
    $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts.horizontalLayout')

@section('title', 'View - Client')


@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js'])
@endsection
@section('content')
    <style>
        .tab-content::after {
            content: "";
            display: block;
            clear: both;
        }
    </style>

    <script>
        $(document).ready(function() {
            function getDepartments() {
                $.ajax({
                    url: "{{ route('get.client.department') }}", // Route to fetch departments
                    method: 'GET',
                    success: function(departments) {
                        var departmentSelect = $('#departmentSearch');
                        departmentSelect.empty(); // Clear any existing options
                        departmentSelect.append(
                        '<option value="">Select Department</option>'); // Default option

                        // Loop through the department list and append options
                        $.each(departments, function(id, name) {
                            departmentSelect.append('<option value="' + id + '">' + name +
                                '</option>');
                        });
                    }
                });
            }
            // $('.datatables-quote').DataTable().ajax.reload();
            flatpickr("#flatpickr-range", {
                mode: "range", // Enable range selection
                dateFormat: "Y-m-d", // Specify the date format (you can change this if needed)
                onChange: function(selectedDates) {
                    // Check if two dates are selected (start and end)
                    if (selectedDates.length == 2) {
                        // Trigger DataTable reload with the selected date range
                        $('#quotes-table').DataTable().ajax.reload();
                    }
                }
            });


            // Initialize flatpickr for the Quotation and Assignment tables
            flatpickr("#flatpickr-range", {
                mode: "range", // Enable range selection
                dateFormat: "Y-m-d", // Specify the date format
                onChange: function(selectedDates) {
                    // Check if two dates are selected (start and end)
                    if (selectedDates.length == 2) {
                        // Trigger DataTable reload with the selected date range
                        $('#quotes-table').DataTable().ajax.reload();
                        $('#assignment-table').DataTable().ajax.reload();
                    }
                }
            });

            // Initialize Quotation DataTable
            $('#quotes-table').DataTable({
                order: [
                    [2, 'desc']
                ],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('client.quote.view') }}",
                    data: function(d) {
                        d.status = $('#statusSearch').val();
                        d.reference = $('#nameSearch').val();
                        d.client_id = $('#client_id').val();
                        // Handle the date range filter
                        var dateRange = $('#flatpickr-range').val();
                        if (dateRange) {
                            var dates = dateRange.split(' to ');
                            d.start_date = dates[0];
                            d.end_date = dates[1];
                        }
                    }
                },
                columns: [{
                        data: 'reference'
                    },
                    {
                        data: 'created_by'
                    },
                    {
                        data: 'created_date',
                        searchable: false
                    },
                    {
                        data: 'expiry_date',
                        searchable: false
                    },
                    {
                        data: 'assignment',
                        searchable: false
                    },
                    {
                        data: 'amount'
                    },
                    {
                        data: 'description'
                    },
                    {
                        data: 'currency'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                initComplete: function() {
                    var table = this.api();

                    // Add common search box dynamically for quotation table
                    this.api().columns(0).every(function() {
                        var column = this;
                        $('<input type="text" class="form-control" id="search_name" placeholder="Search by name" ">')
                            .appendTo('.name')
                            .on('keyup change', function() {
                                column.search($(this).val()).draw();
                            });
                    });
                    this.api().columns(9).every(function() {
                        var column = this;
                        $('<select id="statusSearch" class="form-select"><option value="">Select Status</option><option value="quoted">Quoted</option><option value="awarded">Awarded</option><option value="lost">Lost</option></select>')
                            .appendTo('.status')
                            .on('change', function() {
                                column.search($(this).val()).draw();
                            });
                    });
                    // Reset filters button for quotation table
                    $('#resetFiltersBtn').on('click', function() {
                        $('#commonSearch').val('');
                        $('#nameSearch').val('');
                        $('#flatpickr-range').val('');
                        $('#statusSearch').val('');
                        table.search('').draw(); // Reset DataTable search
                    });
                }
            });

            // Initialize Assignment DataTable
            $('#assignment-table').DataTable({
                order: [
                    [5, 'desc']
                ],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('client.assignment.view') }}",
                    data: function(d) {
                        d.department = $('#departmentSearch_assignment').val();
                        d.status = $('#statusSearch').val();
                        d.client_name = $('#nameSearch').val();
                        d.client_id = $('#client_id').val();
                        // Handle the date range filter
                        var dateRange = $('#flatpickr-range').val();
                        if (dateRange) {
                            var dates = dateRange.split(' to ');
                            d.start_date = dates[0];
                            d.end_date = dates[1];
                        }
                    }
                },
                lengthChange: false,
                columns: [{
                        data: 'department',
                        name: 'department'
                    },
                    {
                        data: 'quote'
                    },
                    {
                        data: 'client',
                        name: 'client_name'
                    },
                    {
                        data: 'ledger'
                    },
                    {
                        data: 'created_by'
                    },
                    {
                        data: 'created_date',
                        searchable: false
                    },
                    {
                        data: 'amount',
                        searchable: false
                    },
                    {
                        data: 'description'
                    },
                    {
                        data: 'currency'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                initComplete: function() {
                    // Add name search for assignment table


                    // Add department filter for assignment table
                    var departmentColumn = this.api().columns(0); // Column index for 'department'
                    $('<select id="departmentSearch_assignment" class="form-select"><option value="">Select Department</option></select>')
                        .appendTo('.department')
                        .on('change', function() {
                            $('#assignment-table').DataTable().ajax.reload();
                        });

                    // Populate department dropdown dynamically
                    getDepartments();
                }
            });

            // Handle date range application for assignment table
            $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
                var startDate = picker.startDate.format('YYYY-MM-DD');
                var endDate = picker.endDate.format('YYYY-MM-DD');
                $('#assignment-table').DataTable().ajax.reload();
            });

            // Handle status change for assignment table
            $('#statusSearch').on('change', function() {
                $('#assignment-table').DataTable().ajax.reload();
            });

            // Handle department change for assignment table
            $('#departmentSearch_assignment').on('change', function() {
                $('#assignment-table').DataTable().ajax.reload();
            });
            $(document).on('click', '.edit-assignment-btn', function() {
                const assignmentId = $(this).data('id');
                if (assignmentId) {
                    window.location.href = '{{ route('assignment.edit', ':id') }}'.replace(':id',
                        assignmentId);
                }
            });
            $(document).on('click', '.view-assignment-btn', function() {
                const assignmentId = $(this).data('id');
                if (assignmentId) {
                    window.location.href = '{{ route('assignment.view', ':id') }}'.replace(':id',
                        assignmentId);
                }
            });
            // START : Delete functionality
            $(document).on('click', '.delete-assignment-btn', function() {
                const assignmentId = $(this).data('id');
                if (assignmentId) {
                    $('.modal-body').html();
                    $('.modal-body').text(" Are you sure you want to delete this assignment ?");
                    $('#modalToggle').data('quotation-id', assignmentId).modal('show');
                }
            });
            $(document).on('click', '.edit-quote-btn', function() {
                const quoteId = $(this).data('id');
                if (quoteId) {
                    window.location.href = '{{ route('quotes.edit', ':id') }}'.replace(':id', quoteId);;
                }
            });
            $(document).on('click', '.view-quote-btn', function() {
                const quoteId = $(this).data('id');
                if (quoteId) {
                    window.location.href = '{{ route('quotes.view', ':id') }}'.replace(':id', quoteId);;
                }
            });
            // START : Delete functionality
            $(document).on('click', '.delete-quote-btn', function() {
                const quotationId = $(this).data('id');
                if (quotationId) {
                    $('.modal-body').html();
                    $('.modal-body').text(" Are you sure you want to delete this quotation ?");
                    $('#modalToggle').data('quotation-id', quotationId).modal('show');
                }
            });

        });
    </script>

    </script>
    <input type="hidden" id="client_id" value="{{ $client->id }}">



    <div class="row mt-5">
        <div class="col-12">
            <div class="card mb-6">
                <div
                    class="user-profile-header d-flex flex-column flex-lg-row flex-md-row text-sm-start text-center my-5 align-items-center ps-3">
                    <div class="flex-shrink-0 mx-sm-0 mx-auto d-block h-auto rounded user-profile-img "
                        style="background-color: #192b53; color: white; width: 100px; height: 100px !important; display: flex !important; justify-content: center; align-items: center; font-size: 42px; font-weight: bold; line-height: 1;">
                        {{ $initials }}
                    </div>

                    <div class="flex-grow-1">
                        <div
                            class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-5 flex-md-row flex-column gap-4">
                            <div class="user-profile-info">
                                <h4 class="mb-2 ">{{ $client->client ?? '' }}</h4>
                                <ul
                                    class="list-inline mb-0 d-sm-flex d-grid align-items-center flex-wrap justify-content-sm-start justify-content-center gap-4 my-2">
                                    <li class="list-inline-item d-flex gap-2 align-items-start"><i
                                            class="mt-1 mt-sm-0 fa-regular fa-envelope"></i><span
                                            class="fw-medium text-start">{{ $client->email ?? '' }}</span></li>
                                    <li class="list-inline-item d-flex gap-2 align-items-start"><i
                                            class="mt-1 mt-sm-0 fa-regular fa-user"></i><span
                                            class="fw-medium text-start">{{ $client->type ?? '' }}</span></li>
                                    <li class="list-inline-item d-flex gap-2 align-items-start"><i
                                            class="mt-1 mt-sm-0 fa-regular fa-calendar"></i><span
                                            class="fw-medium text-start">
                                            {{ \Carbon\Carbon::parse($client->created_at)->format('d M, Y') ?? '' }}
                                        </span></li>
                                    <li class="list-inline-item d-flex gap-2 align-items-start"><i
                                            class="mt-1 mt-sm-0 fa-solid fa-phone"></i><span class="fw-medium text-start">
                                            {{ $client->mobile ?? '' }}
                                        </span></li>
                                    <li class="list-inline-item d-flex gap-2 align-items-start"><i
                                            class="mt-1 mt-sm-0 fa-solid fa-location-dot"></i><span
                                            class="fw-medium text-start"> {{ $client->billing_address ?? '' }}
                                        </span></li>
                                    <li class="list-inline-item d-flex gap-2 align-items-start"><i
                                            class="mt-1 mt-sm-0 fa-solid fa-earth-europe"></i><span
                                            class="fw-medium text-start"> {{ $country_name->country ?? '' }}
                                        </span></li>
                                    <li class="list-inline-item d-flex gap-2 align-items-start"><i
                                            class="mt-1 mt-sm-0 fa-solid fa-dollar-sign"></i><span
                                            class="fw-medium text-start"> {{ $currency_name->code ?? '' }}
                                        </span></li>
                                    @if ($client->linkedin_url)
                                        <li class="list-inline-item d-flex gap-2 align-items-start"><i
                                                class="mt-1 mt-sm-0 fa-brands fa-linkedin"></i><span
                                                class="fw-medium text-start"> {{ $client->linkedin_url ?? '' }}
                                            </span></li>
                                    @endif
                                    @if ($source_name)
                                        @if ($source_name->title)
                                            <li class="list-inline-item d-flex gap-2 align-items-start"><i
                                                    class="mt-1 mt-sm-0 icon-base ti tabler-calendar  icon-lg"></i><span
                                                    class="fw-medium text-start"> {{ $source_name->title ?? '' }}
                                                </span></li>
                                        @endif
                                    @endif

                                </ul>
                            </div>
                            <!-- <a href="javascript:void(0)" class="btn mb-1 waves-effect waves-light"><i class="ti ti-edit edit-client-btn"></i></a>
                  <a href="javascript:void(0)" class="btn mb-1 waves-effect waves-light"><i class="ti ti-trash delete-client-btn" ></i></a> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <!-- Mark the Tasks tab as 'active' initially -->
            <a class="nav-link active" id="quatation-tab" data-bs-toggle="tab" href="#quatation" role="tab"
                aria-controls="quatation" aria-selected="true">Quatation</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="assignment-tab" data-bs-toggle="tab" href="#assignment" role="tab"
                aria-controls="assignment" aria-selected="false">Assignment</a>
        </li>
    </ul>

    <div class="tab-content mt-4 p-0">
        <!-- Tasks Tab Content -->
        <div class="tab-pane fade show active" id="quatation" role="tabpanel" aria-labelledby="quatation-tab">
            <div class="card">
                <div class="card-header border-bottom">
                    <div class="d-flex justify-content-between">
                        <p class="fs-5">Quotations</p>
                    </div>
                    <div class="row pt-4 gap-4 gap-md-0 mb-5">
                        <div class="col-md-2">
                            <input type="text" class="form-control flatpickr-input"
                                placeholder="YYYY-MM-DD to YYYY-MM-DD" id="flatpickr-range" readonly="readonly">
                        </div>
                        <div class="col-md-2 status"></div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-secondary" id="resetFiltersBtn"
                                style="background: #192b53;">Reset Filters</button>
                        </div>
                    </div>
                    <div class="card-datatable table-responsive">
                        <table class="datatables-quote table" id="quotes-table">
                            <thead class="border-top">
                                <tr>
                                    <th>Reference</th>
                                    <th>Created By</th>
                                    <th>Created Date</th>
                                    <th>Expiry Date</th>
                                    <th>Assignment</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Currency</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <!-- Timekeep Tab Content -->
        <div class="tab-pane fade" id="assignment" role="tabpanel" aria-labelledby="assignment-tab">
            <div class="card">
                <div class="card-header border-bottom">
                    <div class="d-flex justify-content-between">
                        <p class="fs-5">Assignment</p>
                    </div>

                    <div class="card-datatable table-responsive">
                        <table class="datatables-assignment table" id="assignment-table">
                            <thead class="border-top">
                                <tr>
                                    <th>Department</th>
                                    <th>Quote NUmber</th>
                                    <th>Client Name</th>
                                    <th>Ledger</th>
                                    <th>Created By</th>
                                    <th>Created Date</th>
                                    <th>Amount</th>
                                    {{-- <th>Assignment</th> --}}
                                    {{-- <th>Amount</th> --}}
                                    <th>Description</th>
                                    <th>Currency</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).on('click', '.edit-client-btn', function() {
            const clientId = $(this).data('id');
            $('.offcanvas-title').text('Edit Client');
            $('#currency').prop('selectedIndex', 0);
            $('#country').prop('selectedIndex', 0);
            $('#source').prop('selectedIndex', 0);
            $('#clientForm')[0].reset(); // Assuming the form has an id of 'userForm'
            $('.is-invalid').removeClass('is-invalid'); // Remove any invalid class
            $('.invalid-feedback').empty(); // Clear any error messages
            $('#error-container').removeClass('alert alert-danger p-1')
            $('#error-container').html('');
            if (clientId) {
                loadCurrencyDropdown().then(function() {
                    // Proceed with the edit call
                    $.ajax({
                        url: "{{ route('client.edit', ':id') }}".replace(':id',
                            clientId),
                        type: 'GET',
                        success: function(response) {
                            if (response.success) {
                                const data = response.data;
                                console.log(data, 'data');
                                $('#id').val(data.id);
                                $('#client').val(data.client);
                                $('#email').val(data.email);
                                $('#billing-address').val(data.billing_address);
                                $('#mobile').val(data.mobile);
                                $('#website-url').val(data.linkedin_url);

                                // Set currency dropdown
                                $('#currency').val(data.currency_id).trigger(
                                    'change');
                                $('#country').val(data.country_id).trigger('change');
                                $('#source').val(data.source_id).trigger('change');
                                $('#source_value').val(data.source_other);
                                if (data.type === 'individual') {
                                    $('#individual').prop('checked', true);
                                } else if (data.type === 'company') {
                                    $('#company').prop('checked', true);
                                }
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("An error occurred:", error);
                        }
                    });
                });
            }
        });
    </script>
@endsection
