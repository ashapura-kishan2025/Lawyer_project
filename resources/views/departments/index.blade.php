@php
    $customizerHidden = 'customizer-hide';
@endphp

{{-- @extends('layouts/contentNavbarLayout') --}}
@extends('layouts.horizontalLayout')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js'])
@endsection

@section('page-script')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    {{-- @vite(['resources/assets/js/app-user-list.js']) --}}

    <script>
        $(document).ready(function() {

            // START : Datatable Handle for department
            $('.datatables-department').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('departments.index') }}', // AJAX route
                    data: function(d) {
                        // Attach the date range to the AJAX request parameters
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    }
                },
                columns: [{
                        data: 'department',
                        name: 'name'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                    },
                    {
                        data: 'status',
                        name: 'status',

                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                    },
                ],

                initComplete: function() {
                    // Add Department filter
                    this.api()
                        .columns(0)
                        .every(function() {
                            var column = this;
                            $('<input id="filterDepartment" type="text" class="form-control" placeholder="Search Department">')
                                .appendTo('.department')
                                .on('keyup change', function() {
                                    column.search($(this).val()).draw();
                                    console.log(column.search($(this).val()).draw());
                                });
                        });

                    // Add Description filter
                    this.api()
                        .columns(1)
                        .every(function() {
                            var column = this;
                            $('<input id="filterDesc" type="text" class="form-control" placeholder="Search Description">')
                                .appendTo('.description')
                                .on('keyup change', function() {
                                    column.search($(this).val()).draw();
                                });
                        });

                    // Add Status filter
                    this.api()
                        .columns(3)
                        .every(function() {
                            var column = this;
                            $('<select id="statusFilter" class="form-select"><option value="">Select Status</option><option value="1">Active</option><option value="0">Inactive</option></select>')
                                .appendTo('.status')
                                .on('change', function() {
                                    column.search($(this).val()).draw();
                                });
                        });
                },
            });
            //  END : Datatable Handle for department

            // Reset filters
            $('#resetFilters').on('click', function() {
                $('#filterDepartment').val('');
                $('#statusFilter').val('');
                $('#filterDesc').val('');
                $('.datatables-department').DataTable().search('').columns().search('').draw();
            });
            // START : During add new department it get last values so reset the  form first on click of Add new Department
            $('#addNewDepatment').click(function() {
                $('#departmentForm')[0].reset(); // Assuming the form has an id of 'userForm'
                $('.offcanvas-title').text('Add Department');
                $('.is-invalid').removeClass('is-invalid'); // Remove any invalid class
                $('.invalid-feedback').empty(); // Clear any error messages
                $('#error-container').removeClass('alert alert-danger p-1')
                $('#error-container').html('');
            });
            //when click on cancel
            $('#cancel_form').click(function() {
                $('#departmentForm')[0].reset();
                $('.is-invalid').removeClass('is-invalid'); // Remove any invalid class
                $('.invalid-feedback').empty(); // Clear any error messages
                $('#error-container').removeClass('alert alert-danger p-1')
                $('#error-container').html('');
            });
            // END : During add new department it get last values so reset the  form first on click of Add new Department

            // START : function to handle field validation
            function validateField(fieldId, feedbackId, errorMessage) {
                const value = $(fieldId).val().trim(); // Trim to avoid leading/trailing spaces
                if (!value) {
                    $(fieldId).addClass('is-invalid');
                    $(feedbackId).addClass('invalid-feedback').text(errorMessage);
                    return false;
                } else {
                    $(fieldId).removeClass('is-invalid');
                    $(feedbackId).removeClass('invalid-feedback').text('');
                    return true;
                }
            }
            // END : function to handle field validation

            // START : Department Form Field blur event handlers
            $('#departmentName').on('blur', function() {
                validateField('#departmentName', '#departmentName-feedback',
                    'Please Fill the Department Name');
            });
            $('#description').on('blur', function() {
                validateField('#description', '#description-feedback', 'Please Fill the Description');
            });
            // END : Department Form Field blur event handlers

            // Form submission handler
            $('#departmentForm').on('submit', function(e) {
                e.preventDefault();
                const departmentId = $('#id').val();
                const departmentNameValid = validateField('#departmentName', '#departmentName-feedback',
                    'Please Fill the Department Name');
                const descriptionValid = validateField('#description', '#description-feedback',
                    'Please Fill the Description');

                // Stop submission if validation fails
                if (!departmentNameValid || !descriptionValid) {
                    return;
                }
                const departmentName = $('#departmentName').val();
                const description = $('#description').val();
                const status = $('#status').val();

                // Check if ID exists for update or new department creation
                if (departmentId) {
                    // Update AJAX
                    $.ajax({
                        url: "{{ route('departments.update', ':id') }}".replace(':id',
                            departmentId),
                        type: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            id: departmentId,
                            name: departmentName,
                            description: description,
                            status: status,
                        },
                        success: function(response) {
                            if (response.message) {
                                // $('#ajaxSuccess').addClass('alert alert-success').text(
                                //     response
                                //     .message);
                                $('.btn.btn-label-danger[data-bs-dismiss="offcanvas"]')
                                    .click();
                                $('.toast-body').html();
                                $('.toast-body').html("Department Updated Successfully");

                                // var toastEl = document.getElementById('successToast');
                                // var toast = new bootstrap.Toast(
                                //     toastEl); // Initialize the toast
                                // toast.show(); // Show the toast
                                $('.datatables-department').DataTable().ajax.reload();
                            }
                        }
                    })
                } else {
                    // AJAX For Create Department
                    $.ajax({
                        url: "{{ route('departments.store') }}",
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            name: departmentName,
                            description: description,
                            status: status,
                        },
                        success: function(response) {
                            if (response.message) {
                                $('#ajaxSuccess').addClass('alert alert-success').text(
                                    response
                                    .message);
                                $('.btn.btn-label-danger[data-bs-dismiss="offcanvas"]')
                                    .click();
                                // $('.toast-body').html();
                                // $('.toast-body').html("Department Created Successfully");

                                // var toastEl = document.getElementById('successToast');
                                // var toast = new bootstrap.Toast(
                                //     toastEl); // Initialize the toast
                                // toast.show(); // Show the toast

                                $('.datatables-department').DataTable().ajax.reload();
                            }
                        }
                    })
                }
            });
            //  END :  Fom Submission handle

            // START : Handle Edit functionality and show data when edit the department values
            $(document).on('click', '.edit-department-btn', function() {

                $('.offcanvas-title').html('');
                $('.offcanvas-title').html('Edit Department');

                $('#departmentForm')[0].reset(); // Assuming the form has an id of 'userForm'
                $('.is-invalid').removeClass('is-invalid'); // Remove any invalid class
                $('.invalid-feedback').empty(); // Clear any error messages
                $('#error-container').removeClass('alert alert-danger p-1')
                $('#error-container').html('');
                var departmentId = $(this).data(
                    'id'); // Get department ID from data-id attribute

                if (departmentId) {
                    $.ajax({
                        url: "{{ route('departments.edit', ':id') }}".replace(':id',
                            departmentId),
                        type: 'GET',
                        success: function(response) {
                            // Populate form fields with department data
                            $('#id').val(response.data.id);
                            $('#departmentName').val(response.data.name);
                            $('#description').val(response.data.description);
                            $('#status').val(response.data.status);
                        },
                        error: function() {
                            alert("Error fetching department data");
                        }
                    });
                }
            });
            // END : Handle Edit functionality and show data when edit the department values

            // START : Handle Delete department
            // On click of delete icon it opens confirmation box
            $(document).on('click', '.delete-department-btn', function() {
                var departmentId = $(this).data('id'); // Get department ID from data-id attribute

                if (departmentId) {
                    // Store the department ID in the modal
                    $('#modalToggle').data('department-id', departmentId);
                    // Open the modal
                    $('#modalToggle').modal('show');
                }
            });
            // Confirmed And delete
            $(document).on('click', '#modalToggle #confirmed', function() {
                const departmentId = $('#modalToggle').data('department-id');
                let status = $('#modalToggle').data('status');

                if (departmentId && status) {
                    if (status == 1) {
                        status = 0;
                    } else if (status == 0) {
                        status = 1;
                    }

                    // Create an ajax request for update the status
                    $.ajax({
                        url: "{{ route('inlineStatusChangeOfDepartment') }}",
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            id: departmentId,
                            status: status
                        },
                        success: function(response) {
                            $('.datatables-department').DataTable().ajax.reload();
                            $('#ajaxSuccess').addClass('mt-2 alert alert-success').text(
                                response
                                .message);
                        }
                    });
                } else {
                    $.ajax({
                        url: "{{ route('departments.destroy', ':id') }}".replace(':id',
                            departmentId),
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $('#ajaxSuccess').addClass('mt-2 alert alert-danger').text(
                                response
                                .message);
                            $('.datatables-department').DataTable().ajax.reload();
                        }
                    });
                }
            });
            // END : Delete functionality

            // START : Change the status of Country
            window.inlineClickStatusChange = function(id, status) {
                $('.modal-body').html();
                $('.modal-body').text(" Are you sure you want to Change the status  ?");
                $('#modalToggle').data('department-id', id).data('status', status).modal('show');
            }
            // END : Handle Delete department
        });
    </script>
@endsection


@section('title')
    Lawyer - Departments
@endsection


@section('content')
    @if (session('success'))
        {{ session('success') }}
    @endif

    <!-- Users List Table -->
    <div class="card">
        <div class="card-header border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <p class="fs-5">Departments</p>
                <button class="px-2 mb-4 ms-auto add-new btn btn-success waves-effect waves-light" id="addNewDepatment"
                    data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddUser">
                    <i class="ti ti-plus me-0 me-sm-1 ti-xs"></i>
                    Add Department
                </button>
            </div>
            <div class="d-flex justify-content-between align-items-center row pt-4 gap-4 gap-md-0">
                <div class="col-md-4 department "></div>
                <div class="col-md-4 description "></div>
                <div class="col-md-4 status "></div>
                <div class="col-md-3 mt-0 mt-md-2">
                    <button id="resetFilters" class="btn btn-primary">Reset Filters</button>
                </div>
            </div>


        </div>
        @if (session('success'))
            <div class="p-2">
                <p class="alert alert-success"> {{ session('success') }}</p>
            </div>
        @endif
        <span id="ajaxSuccess"></span>
        <div class="card-datatable table-responsive">
            <table class="datatables-department table">
                <thead class="border-top">
                    <tr>
                        <th>Department Name</th>
                        <th>Description</th>
                        <th>Created Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- START : Offcanvas to add new user -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddUser" aria-labelledby="offcanvasAddUserLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasAddUserLabel" class="offcanvas-title">Add Department</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
            <form class="add-new-user pt-0" id="departmentForm">
                @csrf
                <input type="hidden" name="id" id="id">
                <div class="mb-6">
                    <label class="form-label" for="departmentName">Department Name</label>
                    <input type="text" class="form-control" id="departmentName" placeholder="Department Name"
                        name="name" aria-label="Department Name" />
                    <div id="departmentName-feedback">
                    </div>
                </div>
                <div class="mb-6">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    <div id="description-feedback">
                    </div>
                </div>
                <div class="mb-6">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" class="select2 form-select" name="status">
                        <option value="1">
                            Active</option>
                        <option value="0">
                            Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary me-3 data-submit">Submit</button>
                <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas"
                    id="cancel_form">Cancel</button>
            </form>

        </div>
    </div>
    <!-- END : Offcanvas to add new user -->
    {{-- START :  Modal for confirmation --}}
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
                            Are you sure you want to delete this department ?
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
    {{-- END :  Modal for confirmation --}}

    {{-- <button class="btn btn-primary" id="showToast">Show Toast</button> --}}
@endsection
