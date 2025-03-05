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

            // START : Datatable Handle for currency
            $('.datatables-currency').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('currencies.index') }}'
                },
                columns: [{
                        data: 'currency',
                        name: 'currency'
                    },
                    {
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'status',
                        name: 'status',
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
                    // Add Currency filter
                    this.api().columns(0).every(function() {
                        var column = this;
                        $('<input type="text" id="filterCurrency" class="form-control" placeholder="Search Currency">')
                            .appendTo('.currency')
                            .on('keyup change', function() {
                                column.search($(this).val()).draw();
                            });
                    });

                    // Add Code filter
                    this.api().columns(1).every(function() {
                        var column = this;
                        $('<input type="text" id="filterCode" class="form-control" placeholder="Search Code">')
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
                }
            });
            // END : Datatable Handle for currency

            // Reset filters
            $('#resetFilters').on('click', function() {
                $('#filterCurrency').val('');
                $('#filterCode').val('');
                $('#statusFilter').val('');
                $('.datatables-currency').DataTable().search('').columns().search('').draw();
            });

            // Reset form on Add New Currency button click
            $('#addNewCurrency').click(function() {
                $('#currencyForm')[0].reset(); // Assuming the form has an id of 'userForm'
                $('.offcanvas-title').text('Add Currency');
                $('.is-invalid').removeClass('is-invalid'); // Remove any invalid class
                $('.invalid-feedback').empty(); // Clear any error messages
                $('#error-container').removeClass('alert alert-danger p-1')
                $('#error-container').html('');
            });
            //when click on cancel
            $('#cancel_form').click(function() {
                $('#currencyForm')[0].reset();
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
            $('#currency').on('blur', function() {
                validateField('#currency', '#currency-feedback', 'Please Fill the Currency');
            });
            $('#code').on('blur', function() {
                validateField('#code', '#code-feedback', 'Please Fill the Code');
            });

            // Form submission handler
            $('#currencyForm').on('submit', function(e) {
                e.preventDefault();
                const currencyId = $('#id').val();
                const currencyValid = validateField('#currency', '#currency-feedback',
                    'Please Fill the Currency');
                const codeValid = validateField('#code', '#code-feedback', 'Please Fill the Code');

                if (!currencyValid || !codeValid) {
                    return;
                }

                const currency = $('#currency').val();
                const code = $('#code').val();
                const status = $('#status').val();

                const ajaxUrl = currencyId ?
                    "{{ route('currencies.update', ':id') }}".replace(':id', currencyId) :
                    "{{ route('currencies.store') }}";

                const ajaxType = currencyId ? 'PUT' : 'POST';

                $.ajax({
                    url: ajaxUrl,
                    type: ajaxType,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        id: currencyId,
                        currency: currency,
                        code: code,
                        status: status
                    },
                    success: function(response) {
                        $('.btn.btn-label-danger[data-bs-dismiss="offcanvas"]').click();
                        // $('.toast-body').text(currencyId ? 'Currency Updated Successfully' :
                        //     'Currency Created Successfully');
                        // toast.show();
                        $('#ajaxSuccess').addClass('alert alert-success mt-2').text(response
                            .message);
                        $('.datatables-currency').DataTable().ajax.reload();
                    }
                });
            });

            // START : Edit functionality
            $(document).on('click', '.edit-currency-btn', function() {
                $('.offcanvas-title').text('Edit Currency');
                const currencyId = $(this).data('id');
                $('#currencyForm')[0].reset();
                $('.is-invalid').removeClass('is-invalid'); // Remove any invalid class
                $('.invalid-feedback').empty(); // Clear any error messages
                $('#error-container').removeClass('alert alert-danger p-1')
                $('#error-container').html('');
                if (currencyId) {
                    $.ajax({
                        url: "{{ route('currencies.edit', ':id') }}".replace(':id', currencyId),
                        type: 'GET',
                        success: function(response) {
                            $('#id').val(response.data.id);
                            $('#currency').val(response.data.currency);
                            $('#code').val(response.data.code);
                            $('#status').val(response.data.status);
                        }
                    });
                }
            });
            // END : Edit functionality

            // START : Delete functionality
            $(document).on('click', '.delete-currency-btn', function() {
                const currencyId = $(this).data('id');
                if (currencyId) {
                    $('.modal-body').html();
                    $('.modal-body').text(" Are you sure you want to delete this currency ?");
                    $('#modalToggle').data('currency-id', currencyId).modal('show');
                }
            });

            $(document).on('click', '#modalToggle #confirmed', function() {
                const currencyId = $('#modalToggle').data('currency-id');
                let status = $('#modalToggle').data('status');

                if (currencyId && status) {
                    if (status == 1) {
                        status = 0;
                    } else if (status == 0) {
                        status = 1;
                    }

                    // Create an ajax request for update the status
                    $.ajax({
                        url: "{{ route('inlineStatusChangeOfCurrency') }}",
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            id: currencyId,
                            status: status
                        },
                        success: function(response) {
                            $('.datatables-currency').DataTable().ajax.reload();
                            $('#ajaxSuccess').addClass('mt-2 alert alert-success').text(response
                                .message);
                        }
                    });
                } else {
                    $.ajax({
                        url: "{{ route('currencies.destroy', ':id') }}".replace(':id', currencyId),
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $('#ajaxSuccess').addClass('mt-2 alert alert-danger').text(response
                                .message);
                            $('.datatables-currency').DataTable().ajax.reload();
                        }
                    });
                }
            });
            // END : Delete functionality

            // START : Change the status of Currency
            window.inlineClickStatusChange = function(id, status) {
                $('.modal-body').html();
                $('.modal-body').text(" Are you sure you want to Change the status  ?");
                $('#modalToggle').data('currency-id', id).data('status', status).modal('show');
            }
        });
    </script>
@endsection

@section('title')
    Lawyer - Currency
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif



    <div class="card">
        <div class="card-header border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <p class="fs-5">Currencies</p>
                <button class="btn btn-success mb-4" id="addNewCurrency" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasAddUser">
                    <i class="ti ti-plus me-0 me-sm-1 ti-xs"></i>
                    Add Currency
                </button>
            </div>
            <div class="row pt-4 gap-4 gap-md-0">
                <div class="col-md-4 currency "></div>
                <div class="col-md-4 code "></div>
                <div class="col-md-4 status"></div>
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
            <table class="datatables-currency table">
                <thead class="border-top">
                    <tr>
                        <th>Currency</th>
                        <th>Code</th>
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
            <h5 id="offcanvasAddUserLabel" class="offcanvas-title">Add Currency</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
            <form class="add-new-user pt-0" id="currencyForm">
                @csrf
                <input type="hidden" name="id" id="id">
                <div class="mb-6">
                    <label class="form-label" for="currency">Currency</label>
                    <input type="text" class="form-control" id="currency" placeholder="Currency" name="currency"
                        aria-label="Currency" />
                    <div id="currency-feedback">
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
