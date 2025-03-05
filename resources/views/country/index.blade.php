@php
    $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts.horizontalLayout')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js'])
@endsection

@section('page-script')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // START : Datatable Handle for country
            $('.datatables-country').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('country.index') }}'
                },
                columns: [{
                        data: 'country',
                        name: 'country'
                    },
                    {
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [3, 'asc']
                ],
                initComplete: function() {
                    // Add Country filter
                    this.api().columns(0).every(function() {
                        var column = this;
                        $('<input id="filterCountry" type="text" class="form-control" placeholder="Search Country">')
                            .appendTo('.country')
                            .on('keyup change', function() {
                                column.search($(this).val()).draw();
                            });
                    });

                    // Add Code filter
                    this.api().columns(1).every(function() {
                        var column = this;
                        $('<input id="filterCode" type="text" class="form-control" placeholder="Search Code">')
                            .appendTo('.code')
                            .on('keyup change', function() {
                                column.search($(this).val()).draw();
                            });
                    });

                    // Add Status filter
                    this.api().columns(2).every(function() {
                        var column = this;
                        $('<select id="statusFilter" class="form-select"><option value="">Select Status</option><option value="1">Active</option><option value="0">Inactive</option></select>')
                            .appendTo('.status')
                            .on('change', function() {
                                column.search($(this).val()).draw();
                            });
                    });
                    // Add reated_date filter
                    this.api().columns(3).every(function() {
                        var column = this;
                        $('<input id="filterDate" type="date" class="form-control" ">')
                            .appendTo('.created_date')
                            .on('change', function() {
                                column.search($(this).val()).draw();
                            });
                    });
                }
            });
            // END : Datatable Handle for country

            // Reset filters
            $('#resetFilters').on('click', function() {
                $('#filterCountry').val('');
                $('#filterCode').val('');
                $('#statusFilter').val('');
                $('#filterDate').val('');
                $('.datatables-country').DataTable().search('').columns().search('').draw();
            });

            // Reset form on Add New country button click
            $('#addNewCountry').click(function() {
                $('#countryForm')[0].reset(); // Assuming the form has an id of 'userForm'
                $('.offcanvas-title').text('Add Country');
                $('.is-invalid').removeClass('is-invalid'); // Remove any invalid class
                $('.invalid-feedback').empty(); // Clear any error messages
                $('#error-container').removeClass('alert alert-danger p-1')
                $('#error-container').html('');
            });
            //when click on cancel
            $('#cancel_form').click(function() {
                $('#countryForm')[0].reset();
                $('.is-invalid').removeClass('is-invalid'); // Remove any invalid class
                $('.invalid-feedback').empty(); // Clear any error messages
                $('#error-container').removeClass('alert alert-danger p-1')
                $('#error-container').html('');
            });

            // Function to handle field validation
            function validateField(fieldId, feedbackId, errorMessage) {
                const value = $(fieldId).val().trim();
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

            // Blur event handlers for form fields
            $('#country').on('blur', function() {
                validateField('#country', '#country-feedback', 'Please Fill the Country');
            });
            $('#code').on('blur', function() {
                validateField('#code', '#code-feedback', 'Please Fill the Code');
            });

            // Form submission handler
            $('#countryForm').on('submit', function(e) {
                e.preventDefault();
                const countryId = $('#id').val();
                const countryValid = validateField('#country', '#country-feedback',
                    'Please Fill the Country');
                const codeValid = validateField('#code', '#code-feedback', 'Please Fill the Code');

                if (!countryValid || !codeValid) {
                    return;
                }

                const country = $('#country').val();
                const code = $('#code').val();
                const status = $('#status').val();

                const ajaxUrl = countryId ?
                    "{{ route('country.update', ':id') }}".replace(':id', countryId) :
                    "{{ route('country.store') }}";

                const ajaxType = countryId ? 'PUT' : 'POST';

                $.ajax({
                    url: ajaxUrl,
                    type: ajaxType,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        id: countryId,
                        country: country,
                        code: code,
                        status: status
                    },
                    success: function(response) {
                        $('.btn.btn-label-danger[data-bs-dismiss="offcanvas"]').click();
                        $('#ajaxSuccess').addClass('alert alert-success mt-2').text(response
                            .message);
                        $('.datatables-country').DataTable().ajax.reload();
                    }
                });
            });

            // START : Edit functionality
            $(document).on('click', '.edit-country-btn', function() {
                $('.offcanvas-title').text('Edit Country');
                const countryId = $(this).data('id');
                $('#countryForm')[0].reset();
                $('.is-invalid').removeClass('is-invalid'); // Remove any invalid class
                $('.invalid-feedback').empty(); // Clear any error messages
                $('#error-container').removeClass('alert alert-danger p-1')
                $('#error-container').html('');

                if (countryId) {
                    $.ajax({
                        url: "{{ route('country.edit', ':id') }}".replace(':id', countryId),
                        type: 'GET',
                        success: function(response) {
                            $('#id').val(response.data.id);
                            $('#country').val(response.data.country);
                            $('#code').val(response.data.code);
                            $('#status').val(response.data.status);
                        }
                    });
                }
            });
            // END : Edit functionality

            // START : Delete functionality
            $(document).on('click', '.delete-country-btn', function() {
                const countryId = $(this).data('id');
                if (countryId) {
                    $('.modal-body').html();
                    $('.modal-body').text(" Are you sure you want to delete this country ?");
                    $('#modalToggle').data('country-id', countryId).modal('show');
                }
            });

            $(document).on('click', '#modalToggle #confirmed', function() {
                const countryId = $('#modalToggle').data('country-id');
                let status = $('#modalToggle').data('status');

                if (countryId && status) {
                    if (status == 1) {
                        status = 0;
                    } else if (status == 0) {
                        status = 1;
                    }

                    // Create an ajax request for update the status
                    $.ajax({
                        url: "{{ route('inlineStatusChangeOfCountry') }}",
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            id: countryId,
                            status: status
                        },
                        success: function(response) {
                            $('.datatables-country').DataTable().ajax.reload();
                            $('#ajaxSuccess').addClass('mt-2 alert alert-success').text(
                                response
                                .message);
                        }
                    });
                } else {
                    $.ajax({
                        url: "{{ route('country.destroy', ':id') }}".replace(':id', countryId),
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $('#ajaxSuccess').addClass('mt-2 alert alert-danger').text(
                                response
                                .message);
                            $('.datatables-country').DataTable().ajax.reload();
                        }
                    });
                }
            });
            // END : Delete functionality

            // START : Change the status of Country
            window.inlineClickStatusChange = function(id, status) {
                $('.modal-body').html();
                $('.modal-body').text(" Are you sure you want to Change the status  ?");
                $('#modalToggle').data('country-id', id).data('status', status).modal('show');
            }
        });
    </script>
@endsection



@section('title')
    Lawyer - Country
@endsection


@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="z-3 toast align-items-center text-bg-success border-0 ms-auto  " role="alert" id="successToast">
        <div class="d-flex">
            <div class="toast-body"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <p class="fs-5">Countries</p>
                <button class="btn btn-success mb-4" id="addNewCountry" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasAddUser">
                    <i class="ti ti-plus me-0 me-sm-1 ti-xs"></i>
                    Add Country
                </button>
            </div>
            <div class="row  gap-4 gap-md-0">
                <div class="col-md-3 country "></div>
                <div class="col-md-3 code "></div>
                <div class="col-md-3 status "></div>
                <div class="col-md-3 created_date "></div>
                <div class="col-md-3 mt-0 mt-md-2">
                    <button id="resetFilters" class="btn btn-primary">Reset Filters</button>
                </div>
                {{-- <div class="col-12 col-sm-6 col-lg-3">
                    <div class="">
                        <input type="text" class="form-control dt-date flatpickr-range dt-input" data-column="5"
                            placeholder="StartDate to EndDate" data-column-index="4" name="dt_date" />
                        <input type="hidden" class="form-control dt-date start_date dt-input" data-column="5"
                            data-column-index="4" name="value_from_start_date" />
                        <input type="hidden" class="form-control dt-date end_date dt-input" name="value_from_end_date"
                            data-column="5" data-column-index="4" />
                    </div>
                </div> --}}
            </div>
        </div>
        @if (session('success'))
            <div class="p-2">
                <p class="alert alert-success"> {{ session('success') }}</p>
            </div>
        @endif
        <span id="ajaxSuccess"></span>
        <div class="card-datatable table-responsive">
            <table class="datatables-country table">
                <thead class="border-top">
                    <tr>
                        <th>Country</th>
                        <th>Code</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>


    <!-- START : Offcanvas to add new user -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddUser" aria-labelledby="offcanvasAddUserLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasAddUserLabel" class="offcanvas-title">Add Country</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
            <form class="add-new-user pt-0" id="countryForm">

                <input type="hidden" name="id" id="id">
                <div class="mb-6">
                    <label class="form-label" for="country">Country</label>
                    <input type="text" class="form-control" id="country" placeholder="Country" name="country"
                        aria-label="Country" />
                    <div id="country-feedback">
                    </div>
                </div>
                <div class="mb-6">
                    <label class="form-label" for="code">Code</label>
                    <input type="text" class="form-control" id="code" placeholder="Code" name="code"
                        aria-label="Code" />
                    <div id="code-feedback">
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
                <button type="submit" class="btn btn-primary me-3 data-submit">Save</button>
                <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas"
                    id="cancel_form">Cancel</button>
            </form>

        </div>
    </div>
    <!-- END : Offcanvas to add new user -->

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
