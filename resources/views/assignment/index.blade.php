@php
    $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts.horizontalLayout')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js','resources/assets/js/tables-datatables-basic.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js'])
@endsection

@section('page-script')
    <!-- Include Flatpickr CSS -->

    <script>
        $(document).ready(function() {

            flatpickr("#flatpickr-range", {
                mode: "range", // Enable range selection
                dateFormat: "Y-m-d", // Specify the date format (you can change this if needed)
                onChange: function(selectedDates) {
                    // Check if two dates are selected (start and end)
                    if (selectedDates.length == 2) {
                        // Trigger DataTable reload with the selected date range
                        $('#assignment-table').DataTable().ajax.reload();
                    }
                }
            });

            $('#assignment-table').DataTable({
                order: [
                    [5, 'desc']
                ],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('assignment.getData') }}",
                    data: function(d) {
                        d.department = $('#departmentSearch').val();
                        d.status = $('#statusSearch').val();
                        d.client_name = $('#nameSearch').val();
                        var dateRange = $('#flatpickr-range').val();
                        if (dateRange) {
                            var dates = dateRange.split(' to '); // Split the date range (start and end)
                            d.start_date = dates[0]; // Start date
                            d.end_date = dates[1]; // End date
                        }
                    }
                },
                searching: false,
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
                    // {
                    //     data: 'assignment',
                    //     searchable: false
                    // },
                    // {
                    //     data: 'amount'
                    // },
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
                    }, // Action column
                ],
                initComplete: function() {
                    // Add Status filter
                    var column = this.api().columns(2); // Column index 2 for 'client'
                    $('<div class="input-group">' +
                            '<span class="input-group-text">' +
                            '<i class="ti ti-search" style="font-size: 20px; cursor: pointer;"></i>' +
                            '</span>' +
                            '<input type="text" id="nameSearch" class="form-control" placeholder="Search by name">' +
                            '</div>')
                        .appendTo('.name')
                        .on('keyup change', function() {
                            column.search($(this).val())
                                .draw(); // Trigger DataTable search on keyup/change
                        });
                    var departmentColumn = this.api().columns(0); // Column index for 'department'
                    $('<select id="departmentSearch" class="form-select"><option value="">Select Department</option></select>')
                        .appendTo('.department')
                        .on('change', function() {
                            // Trigger DataTable reload and update department filter when changed
                            $('#assignment-table').DataTable().ajax.reload();
                        });

                    // Call getDepartments function to populate department dropdown
                    getDepartments();
                },
            });

            $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
                // Get the selected start date and end date
                var startDate = picker.startDate.format('YYYY-MM-DD');
                var endDate = picker.endDate.format('YYYY-MM-DD');

                // Pass the date range to the DataTable and trigger a redraw
                $('#assignment-table').DataTable().ajax.reload();
            });
            $('#statusSearch').on('change', function() {
                $('#assignment-table').DataTable().ajax.reload();
            });
            $('#emailSearch').on('change', function() {
                $('#assignment-table').DataTable().ajax.reload();
            });
            $('#departmentSearch').on('change', function() {
                var selectedDepartment = $(this).val(); // This should be a single department ID
                $('#assignment-table').DataTable().ajax.reload();
            });
            // Edit client after currency dropdown is loaded
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

            $(document).on('click', '#modalToggle #confirmed', function() {
                const assignmentId = $('#modalToggle').data('quotation-id');
                let status = $('#modalToggle').data('status');

                if (assignmentId) {
                    // Create an ajax request for update the status
                    $.ajax({
                        url: "{{ route('assignment.destroy', ':id') }}".replace(':id',
                            assignmentId),
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $('#ajaxSuccess').addClass('mt-2 alert alert-danger').text(response
                                .success);
                            setTimeout(function() {
                                $('#ajaxSuccess').html('').removeClass(
                                    'mt-2 alert alert-danger');
                            }, 2000);
                            $('.datatables-assignment').DataTable().ajax.reload();
                        }
                    });
                }
            });
            // Fetch and populate the departments dropdown dynamically
            function getDepartments() {
                $.ajax({
                    url: "{{ route('assignment.getDepartmentData') }}", // Route to fetch departments
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

            // END : Delete functionality
        });
        $('#resetFiltersBtn').on('click', function() {
            // Clear all the filter inputs
            $('#nameSearch').val('');
            $('#statusSearch').val('');
            $('#flatpickr-range').val('');
            $('#departmentSearch').val('');

            // Reset DataTable search and redraw
            $("#assignment-table").DataTable().search('').columns().search('').draw();
        });
    </script>
@endsection



@section('title')
    Lawyer - Assignment
@endsection


@section('content')
    <div class="card">
        <div class="card-header border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <p class="fs-5">Assignment</p>
                <a href="{{ route('assignment.create') }}" class="px-3 btn btn-success mb-4">
                    <i class="ti ti-plus me-0 me-sm-1 ti-xs"></i>
                    Add Assignment
                </a>
            </div>
            <div class="row pt-4 gap-4 gap-md-0 mb-5">
                <div class="col-md-2">
                    <div class="name"></div>
                </div>
                <div class="col-md-2">
                    <!-- Custom search for department -->
                    <select id="departmentSearch" class="form-select">
                        <!-- Add other departments here -->
                    </select>
                </div>
                <div class="col-md-2">
                    <!-- <label for="dateRange">Select Date Range:</label> -->
                    <input type="text" class="form-control flatpickr-input" placeholder="YYYY-MM-DD to YYYY-MM-DD"
                        id="flatpickr-range" readonly="readonly">
                </div>

                <div class="col-md-2">
                    <!-- Custom search for status -->
                    <select id="statusSearch" class="form-select">
                        <option value="">Select Status</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-primary" id="resetFiltersBtn">Reset Filters</button>
                </div>
            </div>
            {{-- <span id="ajaxSuccess"></span> --}}
            <div id="ajaxSuccess"></div>
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

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

    {{-- START :  Modal for delete confirmation --}}
    <div class="col-lg-4 col-md-6">
        <div class="mt-4">
            <div class="modal fade" id="modalToggle" aria-labelledby="modalToggleLabel" tabindex="-1"
                style="display: none;" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalToggleLabel">Confirmation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary" id="confirmed" data-bs-toggle="modal"
                                data-bs-dismiss="modal">Yes</button>
                            <button class="btn btn-secondary" id="notConfirmed" data-bs-dismiss="modal">No</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- END :  Modal for delete confirmation --}}
@endsection
