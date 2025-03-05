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
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">


    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        $(document).ready(function() {
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
            $('#quotes-table').DataTable({
                order: [
                    [2, 'desc']
                ],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('quotes.getData') }}",
                    data: function(d) {
                        // console.log(d);
                        // Custom search filters
                        console.log('Common Search Value:', $('#commonSearch')
                            .val()); // Log the value of the common search field
                        console.log('Client Name Value:', $('#nameSearch').val()); //
                        d.status = $('#statusSearch').val();
                        d.client_name = $('#nameSearch').val();

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
                        data: 'client_name'
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
                    this.api().columns(0).every(function() {
                        var column = this;
                        $('<input type="text" class="form-control" id="search_name" placeholder="Search by name" ">')
                            .appendTo('.name')
                            .on('keyup change', function() {
                                column.search($(this).val()).draw();
                            });
                    });


                    // Reset filters button
                    $('#resetFiltersBtn').on('click', function() {
                        // Clear all the filter inputs
                        $('#commonSearch').val('');
                        $('#nameSearch').val('');
                        $('#flatpickr-range').val('');
                        $('#statusSearch').val('');

                        // Reset DataTable search and redraw
                        table.search('').draw();
                    });
                }
            });


            $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
                // Get the selected start date and end date
                var startDate = picker.startDate.format('YYYY-MM-DD');
                var endDate = picker.endDate.format('YYYY-MM-DD');

                // Pass the date range to the DataTable and trigger a redraw
                $('#assignment-table').DataTable().ajax.reload();
            });
            $('#statusSearch').on('change', function() {
                $('#quotes-table').DataTable().ajax.reload();
            });

            // Edit client after currency dropdown is loaded
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

            $(document).on('click', '#modalToggle #confirmed', function() {
                const quotationId = $('#modalToggle').data('quotation-id');
                let status = $('#modalToggle').data('status');

                if (quotationId) {
                    // Create an ajax request for update the status
                    $.ajax({
                        url: "{{ route('quotes.destroy', ':id') }}".replace(':id', quotationId),
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
                            }, 4000);
                            $('.datatables-quote').DataTable().ajax.reload();
                        }
                    });
                }
            });
            // END : Delete functionality
            $('#resetFiltersBtn').click(function() {
                // // Reset the date range picker
                $('#flatpickr-range').val(''); // Clear date range input
                // Reset the status search dropdown
                $('#search_name').val('');
                $('#statusSearch').val('').change(); // Reset the dropdown to "Select Status"
                $('#nameSearch').val('').change();
                // Optional: Trigger a DataTable reload if needed
                $("#quotes-table").DataTable().search('').columns().search('').draw();
            });
        });
    </script>
@endsection



@section('title')
    Lawyer - Quotation
@endsection


@section('content')
    <div class="card">
        <div class="card-header border-bottom mb-0">
            <div class="d-flex justify-content-between align-items-center">
                <p class="fs-5">Quotations</p>
                <a href="{{ route('quotes.create') }}" class="px-3 btn btn-success mb-4">
                    <i class="ti ti-plus me-0 me-sm-1 ti-xs"></i>
                    Add Quotation
                </a>
            </div>

            <div class=" row pt-4 gap-4 gap-md-0 mb-5">
                <div class="col-md-2 name"></div>
                <div class="col-md-2">
                    <input type="text" class="form-control flatpickr-input" placeholder="YYYY-MM-DD to YYYY-MM-DD"
                        id="flatpickr-range" readonly="readonly">
                </div>
                <div class="col-md-3">
                    <select id="statusSearch" class="form-select">
                        <option value="">Select Status</option>
                        <option value="quoted">Quoted</option>
                        <option value="awarded">Awarded</option>
                        <option value="lost">Lost</option>
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
                <table class="datatables-quote table" id="quotes-table">
                    <thead class="border-top">
                        <tr>
                            <th>Client Name</th>
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
