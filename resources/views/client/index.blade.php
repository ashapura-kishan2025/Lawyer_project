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
    @vite(['resources/assets/js/forms-selects.js'])
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            loadCurrencyDropdown();
            loadCountryDropdown();
            loadsourceDropdown();
            // START : Datatable Handle for client
            $('.datatables-client').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('client.index') }}',
                },
                columns: [{
                        data: 'client', // Field name in your data
                        name: 'client', // Column name in your backend (optional but good for server-side processing)
                    },
                    {
                        data: 'email', // Field name in your data
                        name: 'email', // Column name in your backend (optional but good for server-side processing
                    },
                    {
                        data: 'type', // Field name in your data
                        name: 'type', // Column name in your backend (optional but good for server-side processing)
                    },
                    {
                        data: 'created_at', // Field name in your data
                        name: 'created_at', // Column name in your backend (optional but good for server-side processing)
                    },
                    {
                        data: 'contact_person', // Assuming 'contact_person' is the field for the contact person
                        name: 'contact_person', // Column name in your backend (optional but good for server-side processing)
                    },
                    {
                        data: 'currency_id', // Assuming 'currency' is the field for the currency
                        name: 'currency_id', // Column name in your backend (optional but good for server-side processing)
                    },
                    {
                        data: 'mobile', // Assuming 'mobile_number' is the field for the mobile number
                        name: 'mobile', // Column name in your backend (optional but good for server-side processing)
                    },
                    {
                        data: 'action', // Assuming 'action' is the field for any action buttons (like edit or delete)
                        name: 'action', // Column name in your backend (optional but good for server-side processing)
                        orderable: false, // Disables sorting for this column
                        searchable: false, // Disables searching for this column
                    }
                ],
                lengthChange: false,
                order: [
                    [3, 'asc']
                ],
                initComplete: function() {
                    // Add Status filter
                    this.api().columns(1).every(function() {
                        var column = this;
                        $('<input  id="search_name" type="text" class="form-control" placeholder="Search by name" ">')
                            .appendTo('.name')
                            .on('keyup change', function() {
                                column.search($(this).val()).draw();
                            });
                    });
                    this.api().columns(1).every(function() {
                        var column = this;
                        $('<input id="search_email" type="text" class="form-control"  placeholder="Search by email" ">')
                            .appendTo('.email')
                            .on('keyup change', function() {
                                column.search($(this).val()).draw();
                            });
                    });
                    this.api().columns(2).every(function() {
                        var column = this;
                        $('<select id="select_type" class="form-select" ><option value="">All Types</option><option value="individual">Individual</option><option value="company">Company</option></select>')
                            .appendTo('.type')
                            .on('change', function() {
                                column.search($(this).val()).draw();
                            });
                    });
                    this.api().columns(3).every(function() {
                        var column = this;
                        $('<input id="select_date" type="date" class="form-control" ">')
                            .appendTo('.created_date')
                            .on('change', function() {
                                column.search($(this).val()).draw();
                            });
                    });
                },
            });
            // END : Datatable Handle for client

            // Reset form on Add New client button click
            $('#addNewClient').on('click', function() {
                $('#clientForm')[0].reset();
                $('.offcanvas-title').text('Add Client');
            });
            $('#resetFilters').on('click', function() {
                // Reset Name filter
                $('#search_name').val(''); // Clear the name search field

                // Reset Email filter
                $('#search_email').val(''); // Clear the email search field

                // Reset Type filter
                $('#select_type').prop('selectedIndex',
                    0); // Reset the select dropdown to default (All Types)

                // Reset Date filter
                $('#select_date').val(''); // Clear the date filter field

                // Reset DataTable search
                $('.datatables-client').DataTable().search('').columns().search('')
                    .draw(); // Clears all search and redraws the table
            });

            // Function to handle field validation
            function validateField(fieldId, feedbackId, errorMessage) {
                const value = $(fieldId).val();
                console.log(value, 'sdcds'); // Debugging line

                // Check if the value is empty or null
                if (!value || value === null || value === "") {
                    $(fieldId).addClass('is-invalid'); // Add invalid class to the field
                    $(feedbackId)
                        .text(errorMessage) // Set error message text
                        .css('display', 'block') // Ensure the feedback is displayed
                        .css('color', 'red') // Optional: change color to red
                        .css('font-size', '0.875em'); // Optional: adjust font size for better readability
                    return false;
                } else {
                    $(fieldId).removeClass('is-invalid'); // Remove invalid class from the field
                    $(feedbackId).text('').css('display', 'none'); // Hide error message
                    return true;
                }
            }


            $('#client').on('blur', function() {
                validateField('#client', '#client-feedback', 'Please Fill the client name');
            });
            $('#email').on('blur', function() {
                validateField('#email', '#email-feedback', 'Please Fill the email address');
            });
            $('.client-type').on('change', function() {
                // Check if any radio button in the group is selected
                const isClientTypeSelected = $('input[name="client-type"]:checked').val();
                if (!isClientTypeSelected) {
                    $('#client-type-feedback').text('Please select client type').css('display', 'block')
                        .css('color', 'red');
                    $('.client-type').addClass('is-invalid');
                } else {
                    $('#client-type-feedback').text('').css('display', 'none');
                    $('.client-type').removeClass('is-invalid');
                }
            });
            $('#mobile').on('blur', function() {
                validateField('#mobile', '#mobile-feedback', 'Please Fill the phone number');
            });
            $('#billing-address').on('blur', function() {
                validateField('#billing-address', '#billing-address-feedback',
                    'Please Fill the billing address');
            });
            $('#country').on('change', function() {
                validateField('#country', '#country-feedback',
                    'Please select country');
            });
            $('#source').on('change', function() {
                validateField('#source', '#source-feedback',
                    'Please select the source');
            });
            $('#currency').on('change', function() {
                validateField('#currency', '#currency-feedback', 'Please select currency');
                // $(this).closest('.select2-container').toggleClass('invalid-feedback', !$(this).val()); // Applies invalid class to the select2 container
            });


            // Form submission handler
            $('#clientForm').on('submit', function(e) {
                e.preventDefault();
                const clientIdValid = $('#id').val();
                const source_value = $('#source_value').val();
                const clientNameValid = validateField('#client', '#client-feedback',
                    'Please Fill the client name');
                const emailaddressValid = validateField('#email', '#email-feedback',
                    'Please Fill the email address');
                const phoneNumberValid = validateField('#mobile', '#mobile-feedback',
                    'Please Fill the Phone Number');
                const billingAddressValid = validateField('#billing-address', '#billing-address-feedback',
                    'Please Fill the billing address');
                const countryValid = validateField('#country', '#country-feedback',
                    'Please select country');
                const sourceValid = validateField('#source', '#source-feedback',
                    'Please select the source');
                const currencyValid = validateField('#currency', '#currency-feedback',
                    'Please select currency');
                const clienttypeValid = $('input[name="client-type"]:checked').val();
                if (!clienttypeValid) {
                    $('#client-type-feedback').text('Please select client type').css('display', 'block')
                        .css('color', 'red');
                    $('.client-type').addClass('is-invalid');
                    return false;
                } else {
                    $('#client-type-feedback').text('').css('display', 'none');
                    $('.client-type').removeClass('is-invalid');
                }

                // const codeValid = validateField('#code', '#code-feedback', 'Please Fill the Code');

                // if (!countryValid || !codeValid) {
                //     return;
                // }
                clientId = $("#id").val();
                var formData = {
                    id: $("#id").val(),
                    type: $("input[name='client-type']:checked")
                        .val(), // Get selected radio button value
                    client: $("#client").val(),
                    company_name:$('#company_name').val(),
                    source_value: source_value,
                    email: $("#email").val(),
                    currency_id: $("#currency").val(),
                    mobile: $("#mobile").val(),
                    billing_address: $("#billing-address").val(),
                    country: $("#country").val(),
                    source_other: $("#source").val(),
                    linkedin_url: $("#website-url").val()
                };
                const ajaxUrl = clientId ?
                    "{{ route('client.update', ':id') }}".replace(':id', clientId) :
                    "{{ route('client.store') }}";

                const ajaxType = clientId ? 'PUT' : 'POST';

                $.ajax({
                    url: ajaxUrl,
                    type: ajaxType,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: formData,
                    success: function(response) {
                        console.log(response);
                        if (response.errors) {
                            console.log("Helllo");
                        }
                        $('.btn.btn-label-danger[data-bs-dismiss="offcanvas"]').click();
                        $('#ajaxSuccess').addClass('alert alert-success mt-2').text(response
                            .message);
                        // $('#clientForm')[0].reset();
                        // Set a timeout to remove the message after 4 seconds
                        setTimeout(function() {
                            $('#ajaxSuccess').removeClass('alert alert-success mt-2')
                                .text('');
                        }, 4000); // 4000 milliseconds = 4 seconds

                        $('.datatables-client').DataTable().ajax.reload();
                    },
                    error: function(xhr, formData) {
                        if (xhr.status === 422) {
                            console.log(formData);
                            // const errors = xhr.responseJSON.errors;

                            const errors = xhr.responseJSON.errors;

                            // Create an empty list to show errors
                            let errorList = '';

                            // Iterate over the errors object
                            $.each(errors, function(field, messages) {
                                // For each field, loop through its messages
                                $.each(messages, function(index, message) {
                                    errorList += `<li>${message}</li>`;
                                });
                            });

                            // Append the error list to a div or any container you have
                            $('#error-container').addClass('alert alert-danger p-1')
                            $('#error-container').html(`<ul>${errorList}</ul>`);
                            // if (errors.email) {
                            //     // Display the email validation error
                            //     $('#email-feedback').addClass('invalid-feedback').text(errors
                            //         .email[
                            //             0
                            //         ]
                            //     ); // Assuming you're displaying error under #email-feedback
                            //     $('#email').addClass('is-invalid');

                            // }
                        }
                    }
                });
            });


            // Load the currency dropdown
            function loadCurrencyDropdown() {
                return $.ajax({
                    url: "{{ route('getCurrency') }}",
                    method: 'GET',
                    success: function(response) {
                        const $currencySelect = $("#currency");
                        $currencySelect.empty().append(
                            $("<option>", {
                                value: "",
                                text: "Please Select",
                                disabled: true,
                                selected: true
                            })
                        );

                        if (typeof response === 'object') {
                            $.each(response, function(id, value) {
                                $currencySelect.append(
                                    $("<option>", {
                                        value: id,
                                        text: value
                                    })
                                );
                            });
                        }
                    }
                });
            }
            //load country
            function loadCountryDropdown() {
                return $.ajax({
                    url: "{{ route('getCountry') }}",
                    method: 'GET',
                    success: function(response) {
                        const $countrySelect = $("#country");
                        $countrySelect.empty().append(
                            $("<option>", {
                                value: "",
                                text: "Please Select",
                                disabled: true,
                                selected: true
                            })
                        );

                        if (typeof response === 'object') {
                            $.each(response, function(id, value) {
                                // Create the option element
                                const $option = $("<option>", {
                                    value: id,
                                    text: value
                                });

                                // Check if the current id matches Sri Lanka's ID (211 in your case)
                                if (id ==
                                    211
                                ) { // Adjust the ID as per your data (211 is used here as an example)
                                    $option.prop('selected', true); // Mark it as selected
                                }

                                // Append the option to the select dropdown
                                $countrySelect.append($option);
                            });
                        }
                    }
                });
            }

            //for source
            function loadsourceDropdown() {
                return $.ajax({
                    url: "{{ route('getSource') }}",
                    method: 'GET',
                    success: function(response) {
                        const $currencySelect = $("#source");
                        $currencySelect.empty().append(
                            $("<option>", {
                                value: "",
                                text: "Please Select",
                                disabled: true,
                                selected: true
                            })
                        );

                        if (typeof response === 'object') {
                            $.each(response, function(id, value) {
                                $currencySelect.append(
                                    $("<option>", {
                                        value: id,
                                        text: value
                                    })
                                );
                            });
                        }
                    }
                });
            }
            $(document).on('change', '#source', function() {
                const source = $(this).val(); // Get client ID if necessary

                // Perform AJAX request
                $.ajax({
                    url: "{{ route('getSource.field') }}",
                    type: 'GET', // Use POST if needed
                    data: {
                        source: source
                    }, // Send the clientId as data if needed
                    success: function(response) {
                        console.log(response, 'response');
                        if (response.value == 1) {
                            console.log('done');
                            // Append a new textbox after the #source select element
                            $('.source_fields').removeClass('d-none');
                        } else {
                            $('.source_fields').addClass('d-none');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX request failed:', error);
                    }
                });
            });

            $(document).on('click', '.view-client-btn', function() {
                const clientId = $(this).data('id');
                if (clientId) {
                    window.location.href = '{{ route('client.view', ':id') }}'.replace(':id', clientId);
                }
            });
            $('#addNewClient').click(function() {
                $('#clientForm')[0].reset(); // Assuming the form has an id of 'userForm'
                $('.is-invalid').removeClass('is-invalid'); // Remove any invalid class
                $('#currency').prop('selectedIndex', 0);
                $('#country').prop('selectedIndex', 0);
                $('#source').prop('selectedIndex', 0);
                $('.invalid-feedback').empty(); // Clear any error messages
                $('#error-container').removeClass('alert alert-danger p-1')
                $('#error-container').html('');
            });
            //when click on cancel
            $('#cancel_form').click(function() {
                $('#clientForm')[0].reset();
                $('#currency').prop('selectedIndex', 0);
                $('#country').prop('selectedIndex', 0);
                $('#source').prop('selectedIndex', 0);
                $('.is-invalid').removeClass('is-invalid'); // Remove any invalid class
                $('.invalid-feedback').empty(); // Clear any error messages
                $('#error-container').removeClass('alert alert-danger p-1')
                $('#error-container').html('');
            });
            // Edit client after currency dropdown is loaded
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
                                    $('#company_name').val(data.contact_person);
                                    // Set currency dropdown
                                    $('#currency').val(data.currency_id).trigger(
                                        'change');
                                    $('#country').val(data.country_id).trigger(
                                        'change');
                                    $('#source').val(data.source_id).trigger('change');
                                    $('#source_value').val(data.source_other);
                                    if (data.type === 'individual') {
                                        $('#individual').prop('checked', true);
                                        $('#company-section').hide();  // Hide the company section using jQuery
                                    } else if (data.type === 'company') {
                                        $('#company').prop('checked', true);
                                        $('#company-section').show();  // Show the company section using jQuery
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
            //reset filter
            $(document).on('click', '#resetFilters', function() {
                const clientId = $(this).data('id');
                if (clientId) {
                    $('.modal-body').html();
                    $('.modal-body').text(" Are you sure you want to delete this client ?");
                    $('#modalToggle').data('client-id', clientId).modal('show');
                }
            });
            //START : Delete functionality
            $(document).on('click', '.delete-client-btn', function() {
                const clientId = $(this).data('id');
                if (clientId) {
                    $('.modal-body').html();
                    $('.modal-body').text(" Are you sure you want to delete this client ?");
                    $('#modalToggle').data('client-id', clientId).modal('show');
                }
            });

            $(document).on('click', '#modalToggle #confirmed', function() {
                const clientId = $('#modalToggle').data('client-id');


                if (clientId) {
                    $.ajax({
                        url: "{{ route('client.destroy', ':id') }}".replace(':id', clientId),
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#ajaxSuccess').addClass('mt-2 alert alert-danger').text(
                                    response
                                    .message);
                                $('.datatables-client').DataTable().ajax.reload();
                            } else {
                                $('.modal-body_assignment').html();
                                $('.modal-body_assignment').text(response.message);
                                $('#modalToggle_assignment').modal('show');
                            }
                        }
                    });
                }
            });
            const companyRadioButton = document.getElementById("company");
            const companySection = document.getElementById("company-section");
            const individual = document.getElementById("individual");
            // Listen for the change event on the radio button
            individual.addEventListener("change", function () {
                if (individual.checked) {
                    // If "Company" radio button is checked, show the company name input
                    companySection.style.display = "none";
                } 
            });
       
            companyRadioButton.addEventListener("change", function () {
                if (companyRadioButton.checked) {
                    // If "Company" radio button is checked, show the company name input
                    companySection.style.display = "block";
                } else {
                    // Otherwise, hide it
                    companySection.style.display = "none";
                }
            });
        });
    </script>
@endsection


@section('title')
    Lawyer - Client
@endsection


@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- <div class="z-3 toast align-items-center text-bg-success border-0 ms-auto  " role="alert" id="successToast">
        <div class="d-flex">
            <div class="toast-body"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
        </div>
    </div> --}}

    <div class="card">
        <div class="card-header border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <p class="fs-5">Clients</p>
                <button class="btn btn-success mb-4" id="addNewClient" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasAddUser">
                    <i class="ti ti-plus me-0 me-sm-1 ti-xs"></i>
                    Add Client
                </button>
            </div>
            <div class="row pt-4 gap-4 gap-md-0">
                <div class="col-md-2 name"></div>
                <div class="col-md-2 email"></div>
                <div class="col-md-2 type"></div>
                <div class="col-md-2 created_date"></div>
                <div class="col-md-4">
                    <button type="button" id="resetFilters" class="btn btn-primary">Reset Filters</button>
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
            <table class="datatables-client table">
                <thead class="border-top">
                    <tr>
                        <th>Client Name</th>
                        <th>Email</th>
                        <th>Type</th>
                        <th>Added Date</th>
                        <th>Contact Person</th>
                        <th>Currency</th>
                        <th>Mobile Number</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>


    <!-- START : Offcanvas to add new user -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddUser" aria-labelledby="offcanvasAddUserLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasAddUserLabel" class="offcanvas-title">Add Client</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
            <div id="error-container"></div>
            <form class="add-new-user pt-0" id="clientForm">
                <input type="hidden" name="id" id="id">

                <div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input client-type" type="radio" name="client-type" id="individual"
                            value="individual" checked>
                        <label class="form-check-label" for="individual">Individual</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input client-type" type="radio" name="client-type" id="company"
                            value="company">
                        <label class="form-check-label" for="company">Company</label>
                    </div>
                    <div id="client-type-feedback" class="invalid-feedback">
                    </div>
                </div>
                <div class="mb-6" id="company-section" style="display: none;">
                    <label class="form-label" for="company_name">Company Name</label>
                    <input type="text" class="form-control" id="company_name" placeholder="Company Name" name="company_name" aria-label="Company" />
                    <div id="company-feedback" class="invalid-feedback"></div>
                </div>
                <div class="mb-6">
                    <label class="form-label" for="client">Client Name</label>
                    <input type="text" class="form-control" id="client" placeholder="Client" name="client"
                        aria-label="Client" />
                    <div id="client-feedback" class="invalid-feedback">
                    </div>
                </div>
                <div class="mb-6">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" class="form-control" id="email" placeholder="Email" name="email"
                        aria-label="Email" />
                    <div id="email-feedback" class="invalid-feedback">
                    </div>
                    {{-- @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror --}}
                </div>

                <div class="mb-6">
                    <label class="form-label" for="currency">Currency</label>
                    <select id="currency" class="select2 form-control" name="currency">
                        <option value="" disabled selected>Please Select</option>
                        {{-- <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                        <option value="INR">INR</option> --}}
                    </select>
                    <div id="currency-feedback" class="invalid-feedback">
                    </div>
                </div>


                <div class="mb-6">
                    <label class="form-label" for="mobile">Phone Number</label>
                    <input type="text" class="form-control" id="mobile" placeholder="Phone Number" name="mobile"
                        aria-label="Phone Number" />
                    <div id="mobile-feedback" class="invalid-feedback">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="form-label" for="billing-address">Billing Address</label>
                    <input type="text" class="form-control" id="billing-address" placeholder="Billing Address"
                        name="billing-address" aria-label="Billing Address" />
                    <div id="billing-address-feedback" class="invalid-feedback">
                    </div>
                </div>


                <div class="mb-6">
                    <label class="form-label" for="country">Country</label>
                    <select id="country" class="select2 form-control" name="country">
                        <option value="" disabled selected>Please Select</option>
                    </select>
                    <div id="country-feedback" class="invalid-feedback">
                    </div>
                </div>


                <div class="mb-6">
                    <label class="form-label" for="source">Source</label>
                    <select id="source" class="select2 form-control" name="source">
                        <option value="" disabled selected>Please Select</option>
                    </select>
                    <div id="source-feedback" class="invalid-feedback">
                    </div>
                </div>
                <div class="mb-2 d-none source_fields">
                    <div class="form-group">
                        <label for="extra-textbox">Source Name</label>
                        <input type="text" id="source_value" name="source_value" class="form-control" />
                    </div>
                </div>


                <div class="mb-6">
                    <label class="form-label" for="website-url">Linkdin(URL)</label>
                    <input type="text" class="form-control" id="website-url" placeholder="linkedin-url"
                        name="website-url" aria-label="Website-url" />
                    <div id="website-url-feedback" class="invalid-feedback">
                    </div>
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
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
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

            <!-- this is for if client have assignment -->

        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="mt-4">
            <div class="modal fade" id="modalToggle_assignment" aria-labelledby="modalToggleLabel_assignment"
                tabindex="-1" style="display: none;" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalToggleLabel_assignment">Confirmation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body modal-body_assignment">
                            hello
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary" id="confirmed_assignment" data-bs-toggle="modal"
                                data-bs-dismiss="modal">Ok</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- END :  Modal for delete confirmation --}}
@endsection
