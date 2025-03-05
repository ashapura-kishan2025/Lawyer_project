@php
    $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts.horizontalLayout')

@section('title', 'Add - Quotation')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('page-style')
    @vite('resources/assets/vendor/scss/pages/app-invoice.scss')
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/jquery-repeater/jquery-repeater.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/app-invoice-add.js', 'resources/assets/js/forms-selects.js'])
    

    <script>
        
        $(document).ready(function() {
            $(document).on('change', '#client', function() {
            // Get the selected value
                var selectedValue = $(this).val();
                console.log(selectedValue,'selectedValue');
                    $.ajax({
                        url: '{{ route('get.users.currency') }}',
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: { id: selectedValue },
                        success: function(response) {
                            // $('.group-a').find('.currency').last().val(response.currency); // Update the most recent .currency field
                            $('.currency').val(response.currency);
                            $('.currency_data').val(response.id);
                            console.log(response.currency, 'Currency updated');
                        },
                        // error: function(xhr) {
                        //     alert('An error occurred.');
                        //     console.log(xhr.responseText);
                        // }
                    });
            });
            showExpiryDate();
            loadDepartmentDropdown();
            loadClientDropdown();

            $('.add-item').on('click', function() {
            // HTML structure for the new task section
            var newItem = `
               <div class="repeater-wrapper px-3 px-sm-0 pt-0 pt-md-9 quation_task_section">
                                <div class="d-flex border rounded position-relative pe-0">
                                    <div class="row w-100 p-6">
                                        <div class="col-md-4 col-12 mb-md-0 mb-4">
                                            <p class="h6 repeater-title">Amount</p>
                                            <input type="text" class="amount form-control invoice-item-price mb-5"
                                                placeholder="Amount" name="amount[]" />
                                        </div>
                                        <div class="col-md-4 col-12 mb-md-0 mb-4">
                                            <label class="h6 repeater-title" for="currency">Currency</label>
                                            <input type="text" class="currency form-control invoice-item-price mb-5"
                                            placeholder="currency" name="currency[]" readonly/>
                                            <input type="hidden" id="currency_data" class="currency_data" name="currency_data[]">
                                        </div>
                                        <div class="col-md-4 col-12 mb-md-0 mb-4">
                                            <label class="h6 repeater-title">Department</label>
                                            <select class="select2 form-select department" name="department[]">
                                             <option value="" disabled selected>Please Select</option>
                                                  @foreach ($departments as $departmentId => $departmentName)
                                                        <option value="{{ $departmentId }}">{{ $departmentName }}</option>
                                                    @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 w-100 mb-md-0 mb-4">
                                            <textarea class="description-2 form-control" name="description[]" rows="5" placeholder="Description"></textarea>
                                        </div>

                                    </div>

                                    <div
                                        class="d-flex flex-column align-items-center justify-content-between border-start p-2">
                                        <i class="ti ti-x ti-lg cursor-pointer delete_icon" ></i>
                                    </div>
                                </div>
                            </div>`;
                var selectedValue = $('#client').val(); // Assuming the selected client value is still the same
                if (selectedValue) {
                    $.ajax({
                        url: '{{ route('get.users.currency') }}',
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: { id: selectedValue },
                        success: function(response) {
                            // Update the most recent .currency field (newly added one)
                            $('.currency').val(response.currency);
                            $('.currency_data').val(response.id);
                            // $('.group-a').find('.currency').last().val(response.currency);
                            console.log(response.currency, 'Currency updated after adding new item');
                        },
                    });
                }
            // Append the new section to the container
            $('.group-a').append(newItem);

            // Reinitialize select2 for the new dropdowns
            // var select2DropdownCurrency = $('.group-a').find('.currency').last();
            // select2DropdownCurrency.select2();  // Initialize select2 for the new currency dropdown

            var select2DropdownDepartment = $('.group-a').find('.department').last();
            select2DropdownDepartment.select2();  // In

            // Slide the new section down to show it
            var newSection = $('.group-a').find('.quation_task_section').last();
            newSection.hide();
            newSection.slideDown();
        });
        $(document).on('click', '.delete_icon', function() {
            var sectionToDelete = $(this).closest('.quation_task_section');
            
            // Slide up the section and remove it after animation
            sectionToDelete.slideUp(function() {
                sectionToDelete.remove();
            });
        });
          
            function loadClientDropdown() {
                return $.ajax({
                    url: "{{ route('quotes.getClient') }}",
                    method: 'GET',
                    success: function(response) {
                        const $currencySelect = $("#client");
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

            function loadDepartmentDropdown() {
                return $.ajax({
                    url: "{{ route('quotes.getDepartmentData') }}",
                    method: 'GET',
                    success: function(response) {
                        const $departmentSelect = $(".department:last");
                        $departmentSelect.empty().append(
                            $("<option>", {
                                value: "",
                                text: "Please Select",
                                disabled: true,
                                selected: true
                            })
                        );

                        if (typeof response === 'object') {
                            $.each(response, function(id, value) {
                                $departmentSelect.append(
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

            function showExpiryDate() {
            // Get today's date
            const today = new Date();

            // Set the default date to 2 months from today
            const twoMonthsFromToday = new Date();
            twoMonthsFromToday.setMonth(today.getMonth() + 2); // Add 2 months to today's date

            // Set the max date (for example, we set max date to 60 days after 2 months from today)
            const maxDateSelectionOption = new Date();
            maxDateSelectionOption.setDate(twoMonthsFromToday.getDate() + 60); // 60 days after 2 months from today

            // Log for debugging
            console.log("Max Date: ", maxDateSelectionOption);
            console.log("Default Date: ", twoMonthsFromToday);

            // Initialize Flatpickr
            $("#expiry-on").flatpickr({
                dateFormat: "Y-m-d", // Format as YYYY-MM-DD
                minDate: "today", // Disable past dates
                maxDate: maxDateSelectionOption, // Allow up to 2 months + 60 days after today (optional)
                defaultDate: twoMonthsFromToday, // Set default date to 2 months from today
                allowInput: true,  // Allow user to input a date manually if they want
                disableMobile: false, // Allow selection on mobile devices
                // Mode is not set or set to "single" by default
                onChange: function(selectedDates) {
                    console.log(selectedDates); // Log the selected date
                }
            });

        }


            // Form Submission
            $('#quotationForm').on('submit', function(e) {
                e.preventDefault();
                $('.text-danger').hide();
                $('.form-control').removeClass('is-invalid');

               
                expiryOn = $("#expiry-on").val();
                reference = $("#reference").val();
                client = $("#client").val();
                let isValid = true;
                if (!client) {  // If "Please Select" option is selected or nothing is selected
                    $('#client-error').text('Please select a valid client.').show();
                    $('#client').addClass('is-invalid');
                    isValid = false;
                } 
                description = $("#description").val();
                status = $("#status").val();
                var amounts = $('.amount').map(function() {
                    var value = $(this).val();
                    return value === "" ? null : value; // Return null if the value is empty
                }).get();

                // If all values are null or empty, set amounts to null
                if (amounts.every(value => value === null)) {
                    amounts = null;  // Set amounts to null if all values are null
                }
                var departments = $('.department').map(function() {
                    return $(this).val(); // Get the value of each input
                }).get(); // Convert to an array
                var currencies = $('.currency_data').map(function() {
                    return $(this).val(); // Get the value of each input
                }).get(); // Convert to an array
                var descriptions2 = $('.description-2').map(function() {
                    return $(this).val(); // Get the value of each input
                }).get(); // Convert to an array
                let requestData = {
                    expiry_on: expiryOn,
                    reference: reference,
                    client_id: client,
                    status: status,
                    description: description,
                    amounts: amounts,
                    department_ids: departments,
                    currencies: currencies,
                    descriptions: descriptions2
                };
                // Send AJAX request
                if (isValid) {
                    $.ajax({
                        url: '{{ route('quotes.store') }}',
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: requestData,
                        success: function(response) {
                            $("#success-message").addClass('alert alert-success').text(response
                                .success);
                            // alert('Quote saved successfully!');
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 3000);
                        },
                        // error: function(xhr) {
                        //     alert('An error occurred.');
                        //     console.log(xhr.responseText);
                        // }
                    });
                }
            });
            $('#client').on('change', function() {
                $(this).removeClass('is-invalid').siblings('.text-danger').hide();
                $('#client-error').text('Please select a valid client.').hide();
            });
        });
    </script>

@endsection

@section('content')
    <div class="row invoice-add">
        <!-- Invoice Add-->
        <div>
            <div id="success-message"></div>
        </div>
        <div class="col-lg-12 col-12 mb-lg-0 mb-6">
            <form class="source-item" id="quotationForm">
                <div class="ms-auto d-flex justify-content-end mb-2">
                    <button type="submit" class="btn btn-outline-primary waves-effect">Save</button>
                    <a type="reset" class="mx-2 btn btn-outline-primary waves-effect d-grid"
                        href="{{ route('quotes.index') }}">Cancel</a>
                </div>
                <div class="card invoice-preview-card p-sm-12 p-md-6 p-lg-6">

                    <div class="card-header p-lg-0 p-md-0 p-sm-0">
                        <h3 class="mb-0 mb-sm-3">Create Quote</h3>
                    </div>
                    <div class="card-body pt-0 px-0">

                        <div class="row w-100 p-6 p-lg-0 p-md-0 p-sm-0 mb-5 pt-0">
                            <div class="col-lg-3 col-md-6 col-sm-12 col-12 mb-md-0 mb-4">
                                <span class="h6">Expiry On</span>
                                <input type="text" class="form-control invoice-date flatpickr-input" id="expiry-on" name="expiry-on"
                                    placeholder="YYYY-MM-DD" />
                            </div>
                            <div class="col-lg-3 col-md-6 col-12 mb-md-0 mb-4">
                                <span class="h6">Reference</span>
                                <input type="text" class="form-control  mb-0 mb-md-5" id="reference" name="reference"
                                    placeholder="Reference" maxlength="6"/>
                            </div>
                            <div class="col-lg-3 col-md-6 col-12 mb-md-0 mb-4">
                                <span class="form-label h6" for="client">Client</span>
                                <select id="client" class="select2 form-select" name="client">
                                    <option disabled selected>Please Select</option>
                                </select>
                                <small class="text-danger" id="client-error" style="display:none;"></small>
                            </div>
                            <div class="col-lg-3 col-md-6 col-12 mb-md-0">
                                <div class="mb-6">
                                    <span class="form-label h6" for="status">Status</span>
                                    <select id="status" class="select2 form-select" name="status">
                                        <option value="quoted" selected>Quoted</option>
                                        <option value="awarded">Awarded</option>
                                        <option value="lost">Lost</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 w-100 mb-md-0 mb-0 mb-sm-4">
                                <label class="form-label h6" for="description">Description</label>
                                <textarea class="form-control" id="description" rows="5" id="description" name="description"
                                    placeholder="Description"></textarea>
                            </div>
                        </div>
                        <div class="mb-4 group-a">
                            <div class="card-header p-lg-0 p-md-0 p-sm-0 py-0">
                                <h3>Tasks</h3>
                            </div>
                            <div class="repeater-wrapper px-3 px-sm-0 pt-0 pt-md-9 quation_task_section">
                                <div class="d-flex border rounded position-relative pe-0">
                                    <div class="row w-100 p-6">
                                        <div class="col-md-4 col-12 mb-md-0 mb-4">
                                            <p class="h6 repeater-title">Amount</p>
                                            <input type="text" class="amount form-control invoice-item-price mb-5"
                                                placeholder="Amount" name="amount[]" />
                                        </div>
                                        <div class="col-md-4 col-12 mb-md-0 mb-4">
                                            <label class="h6 repeater-title" for="currency">Currency</label>
                                            <input type="text" class="currency form-control invoice-item-price mb-5"
                                            placeholder="currency" name="currency[]" readonly/>
                                            <input type="hidden" id="currency_data" class="currency_data" name="currency_data[]">
                                        </div>
                                        <div class="col-md-4 col-12 mb-md-0 mb-4">
                                            <label class="h6 repeater-title">Department</label>
                                            <select class="select2 form-select department" name="department[]">
                                                <option value="" disabled selected>Please Select</option>
                                            </select>
                                        </div>
                                        <div class="col-12 w-100 mb-md-0 mb-4">
                                            <textarea class="description-2 form-control" name="description[]" rows="5" placeholder="Description"></textarea>
                                        </div>

                                    </div>

                                    <div
                                        class="d-flex flex-column align-items-center justify-content-between border-start p-2">
                                        <i class="ti ti-x ti-lg cursor-pointer delete_icon" ></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <button type="button" id="add-item" class="btn btn-sm btn-primary add-item ms-3 ms-sm-0"><i
                                        class='ti ti-plus ti-14px me-1_5'></i>Add Item</button>
                            </div>

                        </div>
                    </div>
                </div>
            </form>
        </div>




    @endsection
