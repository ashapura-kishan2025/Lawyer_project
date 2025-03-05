@php
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts.horizontalLayout')

@section('title', 'Add - Assignment')

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

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        showExpiryDate();
        loadClientDropdown();
        getQuoteForAssignment();// Function to load user data into the dropdown
        // Function to load user data into the dropdown
       // Function to load user data into the dropdown
        function loadUserDropdown(newItem) {
            return $.ajax({
                url: "{{ route('get.users.assignment') }}",  // Replace this with your actual route
                method: 'GET',
                success: function(response) {
                    const $currencySelect = newItem.find(".person");  // Find the dropdown in the new item

                    // Empty the dropdown and add the default "Please Select" option
                    $currencySelect.empty().append(
                        $("<option>", {
                            value: "",
                            text: "Please Select",
                            disabled: true,
                            selected: true
                        })
                    );

                    // If response is an object, populate the dropdown with user data
                    if (typeof response === 'object') {
                        $.each(response, function(id, value) {
                            $currencySelect.append(
                                $("<option>", {
                                    value: value.id,
                                    text: value.name
                                })
                            );
                        });
                    }
                    if ($currencySelect.hasClass('select2-hidden-accessible')) {
                        $currencySelect.select2('destroy');  // Destroy the previous instance
                    }

                    // Remove any existing Select2 container (span element)
                    $currencySelect.next('.select2-container').remove(); // Remove the Select2 container

                    // Ensure the dropdown is enabled before initializing Select2
                    $currencySelect.prop('disabled', false);  // Enable the dropdown

                    // Re-initialize Select2 for the dropdown
                    $currencySelect.select2();  // Initialize Select2
                }
            });
        }
        // Initialize Select2 for the first dropdown only
        const firstDropdown = $('.group-b .timekeep_section .person').first();
        if (firstDropdown.length && !firstDropdown.hasClass('select2-hidden-accessible')) {
            loadUserDropdown(firstDropdown.closest('.timekeep_section'));
        }

        const secondDropdown = $('.group-a .task_section .department').first();
        if (secondDropdown.length && !secondDropdown.hasClass('select2-hidden-accessible')) {
            loadDepartmentDropdown(secondDropdown.closest('.task_section'));
        }

        $(document).on('click', '.delete_icon', function() {
            var sectionToDelete = $(this).closest('.timekeep_section');
            
            // Slide up the section and remove it after animation
            sectionToDelete.slideUp(function() {
                sectionToDelete.remove();
            });
        });
        $('.add-timekeep').on('click', function() {
            var newItem = `
                <div class="timekeep_section pt-0 pt-md-9 mt-4">
                                <div class="d-flex border rounded position-relative pe-0">
                                    <div class="row w-100 p-6">
                                        <div class="col-md-2 col-12 mb-md-0 mb-4">
                                            <p class="h6 repeater-title">Person</p>
                                            <select class="select2 form-select person" id="person" name="person[]">
                                                 <option value="" disabled selected>Please Select</option>
                                                 @foreach ($user_data as $users)
                                                    <option value="{{ $users['id'] }}">
                                                    {{ $users['name'] ?? '' }}</option>
                                                  @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 col-12 mb-md-0 mb-4">
                                            <label class="h6 repeater-title" for="quantity">Qty</label>
                                            <input type="text" class="timekeep_qty form-control invoice-item-price mb-5" placeholder="0" name="quantity[]" />
                                        </div>
                                        <div class="col-md-2 col-12 mb-md-0 mb-4">
                                            <label class="h6 repeater-title">Rate</label>
                                            <span class="h6 user_rate" id="user_rate">0</span>
                                        </div>
                                        <div class="col-md-2 col-12 mb-md-0 mb-4">
                                            <label class="h6 repeater-title">Total</label>
                                            <span class="user_total" id="user_total">0</span>
                                        </div>
                                        <div class="col-md-2 col-12 mb-md-0 mb-4">
                                            <label class="h6 repeater-title">Memo</label>
                                            <span>-</span>
                                        </div>
                                        <div class="col-12 w-100 mb-md-0 mb-4">
                                            <textarea class="description-3 form-control" name="timekeep_description[]" rows="5" placeholder="Description"></textarea>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column align-items-center justify-content-between border-start p-2">
                                        <i class="ti ti-x ti-lg cursor-pointer delete_icon"></i>
                                    </div>
                                </div>
                            </div>`;

            // Append the new section to the container
            $('.group-b').append(newItem);

            // Reinitialize select2 for the new department dropdown
            var select2Dropdown = $('.group-b').find('.person').last();
            select2Dropdown.select2();  // Initialize select2 for the new dropdown

            // Slide the new section down to show it
            var newSection = $('.group-b').find('.timekeep_section').last();
            newSection.hide();
            newSection.slideDown();
        });

        $('.add-task').on('click', function() {
            // HTML structure for the new task section
            var newItem = `
               <div class="pt-0 pt-md-9 task_section mt-4">
                                <div class="d-flex border rounded position-relative pe-0">
                                    <div class="row w-100 p-6">
                                        <div class="col-md-3 col-12 mb-md-0 mb-4">
                                            <p class="h6 repeater-title">Amount</p>
                                            <input type="text" class="amount form-control invoice-item-price mb-5" placeholder="Amount" name="amount[]" />
                                        </div>
                                        <div class="col-md-3 col-12 mb-md-0 mb-4">
                                            <label class="h6 repeater-title" for="received_amount">Received</label>
                                            <input type="text" class="received_amount form-control invoice-item-price mb-5" placeholder="0" name="received_amount" id="received_amount"/>
                                        </div>
                                        <div class="col-md-3 col-12 mb-md-0 mb-4">
                                            <label class="h6 repeater-title">Department</label>
                                            <select class="select2 form-select department" name="department[]">
                                                 <option value="" disabled selected>Please Select</option>
                                                    @foreach ($departments as $departmentId => $departmentName)
                                                        <option value="{{ $departmentId }}">{{ $departmentName }}</option>
                                                    @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-12 mb-md-0 mb-4">
                                            <label class="h6 repeater-title">Memo</label>
                                            -
                                        </div>
                                        <div class="col-12 w-100 mb-md-0 mb-4">
                                            <textarea class="description-2 form-control" name="task_description[]" rows="5" placeholder="Description"></textarea>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column align-items-center justify-content-between border-start p-2">
                                        <i class="ti ti-x ti-lg cursor-pointer task_delete_icon"></i>
                                    </div>
                                </div>
                            </div>`;

            // Append the new section to the container
            $('.group-a').append(newItem);

            // Reinitialize select2 for the new department dropdown
            var select2Dropdown = $('.group-a').find('.department').last();
            select2Dropdown.select2();  // Initialize select2 for the new dropdown

            // Slide the new section down to show it
            var newSection = $('.group-a').find('.task_section').last();
            newSection.hide();
            newSection.slideDown();
        });
        // Handle task deletion
        $(document).on('click', '.task_delete_icon', function() {
            var sectionToDelete = $(this).closest('.task_section');
            
            // Slide up the section and remove it after animation
            sectionToDelete.slideUp(function() {
                sectionToDelete.remove();
            });
        });
        
        $(document).on('change', '.person', function() {
        // Get the selected value
        var selectedValue = $(this).val();
        var section = $(this).closest('.timekeep_section');
            $.ajax({
                url: '{{ route('get.users.rate') }}',
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: { id: selectedValue },
                success: function(response) {
                    section.find('.user_rate').html(response.rate);  // Set the rate in the span
                    section.find('.user_rate').val(response.rate);   // Set the rate value for calculations

                    // Now trigger the recalculation of the total for this section, if qty is set
                    var qty = section.find('.timekeep_qty').val(); // Get the quantity input value
                    var rate = response.rate; // Use the rate fetched via AJAX
                    
                    // Recalculate total if qty is set
                    if (qty && !isNaN(rate) && qty > 0) {
                        var total = rate * qty;
                        section.find('.user_total').html(total); // Set total in the section
                        section.find('.user_total').val(total);  // Set the value of total for the section
                    }
                },
                error: function(xhr) {
                    // alert('An error occurred.');
                    console.log(xhr.responseText);
                }
            });
        });
        $(document).on('keyup', '.timekeep_qty', function() {
            var section = $(this).closest('.timekeep_section');
            var qty = parseFloat($(this).val()); // Ensure that the quantity is a number
            var rate = parseFloat(section.find('.user_rate').val()); // Ensure that the rate is a number
            console.log(qty,'sdsdcds');
            // Check if rate and qty are valid numbers before calculating
            if (!isNaN(rate) && !isNaN(qty) && qty > 0) {
                // Calculate the total price
                var total = rate * qty;
                
                // Update the total in the current section
                section.find('.user_total').html(total);
                section.find('.user_total').val(total);
            } else {
                // If the input is not valid, set total to 0
                section.find('.user_total').html(0);
                section.find('.user_total').val(0);
            }
        });
        function loadClientDropdown() {
            return $.ajax({
                url: "{{ route('assignment.getClient') }}",
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
      
        function loadDepartmentDropdown(newItem) {
            return $.ajax({
                url: "{{ route('assignment.getDepartmentData') }}",
                method: 'GET',
                success: function(response) {
                    const $departmentSelect =  newItem.find(".department");
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
                    
                    if ($departmentSelect.hasClass('select2-hidden-accessible')) {
                        $departmentSelect.select2('destroy');  // Destroy the previous instance
                    }

                    // Remove any existing Select2 container (span element)
                    $departmentSelect.next('.select2-container').remove(); // Remove the Select2 container

                    // Ensure the dropdown is enabled before initializing Select2
                    $departmentSelect.prop('disabled', false);  // Enable the dropdown

                    // Re-initialize Select2 for the dropdown
                    $departmentSelect.select2();  // Initialize Select2
                }
            });
            
        }

        function showExpiryDate() {
            const thirtyDaysFromToday = new Date();
            thirtyDaysFromToday.setDate(thirtyDaysFromToday.getDate() + 30);
            const maxDateSelectionOption = new Date();
            maxDateSelectionOption.setDate(thirtyDaysFromToday.getDate() + 60);

            // Initialize Flatpickr
            $("#expiry-on").flatpickr({
                dateFormat: "Y-m-d", // Format as YYYY-MM-DD
                minDate: "today", // Disable past dates
                maxDate: maxDateSelectionOption,
                defaultDate: thirtyDaysFromToday, // Set default date to 30 days from today
            });
        }
        function getQuoteForAssignment() {
            return $.ajax({
                url: "{{ route('get.assignment.quote') }}",
                method: 'GET',
                success: function(response) {
                    const $departmentSelect = $(".quote:last");
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
       
        // client
        $('#client').on('change', function() {
                // Check if any radio button in the group is selected
                const isClientTypeSelected = $(this).val();
                if (!isClientTypeSelected) {
                    $('#client-error').text('Please select a valid client.').show();
                    $('#client').addClass('is-invalid');
                } else {
                    $('#client-error').text('').show();
                    $('#client').removeClass('is-invalid');
                }
            });
        // Form Submission
        $('#assignmentForm').on('submit', function(e) {
            e.preventDefault();
            var id = $('#assignment_id').val();
            const radios = document.getElementsByName('assignment_type');
            let selectedValue = '';
            for (let radio of radios) {
                if (radio.checked) {
                    selectedValue = radio.value;
                    break; // Exit once a checked radio button is found
                }
            }
            var quote = $('#quote').val();
            var ledger = $('#ledger').val();
            var reference = $("#reference").val();
            var client = $("#client").val();
            var description = $("#description").val();
            var status = $("#status").val();
            var amounts = $('.amount').map(function() {
                return $(this).val(); // Get the value of each input
            }).get(); 
            var timekeep_qty =  $('.timekeep_qty').map(function() {
                return $(this).val(); // Get the value of each input
            }).get(); // Convert to an array
            var received_amount =  $('.received_amount').map(function() {
                return $(this).val(); // Get the value of each input
            }).get(); 
            var departments = $('.department').map(function() {
                return $(this).val(); // Get the value of each input
            }).get(); // Convert to an array
            var descriptions2 = $('.description-2').map(function() {
                return $(this).val(); // Get the value of each input
            }).get(); // Convert to an array
            var descriptions3 = $('.description-3').map(function() {
                return $(this).val(); // Get the value of each input
            }).get(); // 
            var user_rate = $('.user_rate').map(function() {
                return $(this).val(); // Get the value of each input
            }).get();
            var user_total = $('.user_total').map(function() {
                return $(this).val(); // Get the value of each input
            }).get();  
            var userValue = $('.person').map(function() {
                return $(this).val(); // Get the value of each input
            }).get();
            if (userValue.length === 0 || userValue.some(function(val) { return val === ""; })) {
                userValue = null;
            }
            // console.log(userValue,'dcsdcsdcsd');
            // if(client == '' || client == null){
            //     alert("Please select a client.");
            //     return; // Stop form submission
            // } if(status == '' || status == null){
            //     alert("Please select a status.");
            //     return; // Stop form submission
            // }
                let isValid = true;
                if (!client) {  // If "Please Select" option is selected or nothing is selected
                    $('#client-error').text('Please select a valid client.').show();
                    $('#client').addClass('is-invalid');
                    isValid = false;
                } 
            let requestData = {
                id:id,
                selectedValue : selectedValue,
                quote:quote,
                ledger:ledger,
                received_amount:received_amount,
                timekeep_qty:timekeep_qty,
                reference: reference,
                client_id: client,
                status: status,
                description: description,
                amounts: amounts,
                department_ids: departments,
                descriptions: descriptions2,
                timekeep_descriptions :descriptions3,
                user_rate:user_rate,
                user_total:user_total,
                userValue:userValue
            };
            // Send AJAX request
            if (isValid) {
                $.ajax({
                    url: '{{ route('assignment.store') }}',
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
                    error: function(xhr) {
                        // alert('An error occurred.');
                        console.log(xhr.responseText);
                    }
                });
            }    
        });
        $('input[type="radio"][name="assignment_type"]').on('change', function() {
        // Get the selected value of the radio button
        var assignmentType = $('input[name="assignment_type"]:checked').val();
        console.log(assignmentType, 'assignmentType');

        // Hide all tabs initially
        $('#tasks').removeClass('show active');
        $('#timekeeptab').removeClass('show active');
        $('#tasks-tab').removeClass('active');
        $('#timekeep-tab').removeClass('active');

        // Based on the selected assignment type, show the appropriate tab
        if (assignmentType === 'regular') {
            $('#tasks').addClass('show active');
            $('#tasks-tab').addClass('active');
            // Hide the Timekeep tab and its content
            $('#timekeeptab').removeClass('show active');
            $('#timekeep-tab').removeClass('active');
            $('#tasks-tab').show();
            $('#timekeep-tab').hide();
        } else if (assignmentType === 'timekeep') {
            // Show Timekeep tab and activate the timekeep tab
            $('#timekeeptab').addClass('show active');
            $('#timekeep-tab').addClass('active');
            // Hide the Task tab and its content
            $('#tasks').removeClass('show active');
            $('#tasks-tab').removeClass('active');
            $('#tasks-tab').hide();
            $('#timekeep-tab').show();
        } else if (assignmentType === 'both') {
            // Show both Task and Timekeep tabs and activate them
            $('#tasks').addClass('show active');
            $('#tasks-tab').addClass('active');
            $('#tasks-tab').show();
            $('#timekeep-tab').show();
        }
    });

    // Trigger change event on page load to apply default selected tab (Regular)
    $('input[name="assignment_type"]:checked').trigger('change');
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
        <form class="source-item" id="assignmentForm">
            <input type="hidden" id="assignment_id" value="{{$id ?? ''}}">
            <div class="ms-auto d-flex justify-content-end mb-2">
                <button type="submit" class="btn btn-outline-primary waves-effect">Save</button>
                <a type="reset" class="mx-2 btn btn-outline-primary waves-effect d-grid"
                    href="{{ route('assignment.index') }}">Cancel</a>
            </div>
            <div class="card invoice-preview-card p-sm-12 p-md-6 p-lg-6">

                <div class="card-header p-lg-0 p-md-0 p-sm-0">
                    <h3>Create Assignment</h3>
                </div>
                <div class="card-body pt-0 px-0">
                    <div class="row w-100 p-6 p-lg-0 p-md-0 p-sm-0 mb-2 mb-md-4 pt-0">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-12 mb-md-0 ">
                            <div class="d-flex align-items-center row mb-sm-2  ">
                                <div class="col-12 col-md-6 mb-2">
                                    <span class="h6 mb-0 me-2 ">Select Assignment Type:</span>
                                </div>
                               <div class="col-12">
                                <div class="form-check form-check-inline
                                    mb-0">
                                        <input class="form-check-input" type="radio" name="assignment_type" id="regular" value="regular" checked>
                                        <label class="form-check-label" for="regular">Regular</label>
                                    </div>
                                    <div class="form-check form-check-inline mb-0">
                                        <input class="form-check-input" type="radio" name="assignment_type" id="timekeep" value="timekeep">
                                        <label class="form-check-label" for="timekeep">Timekeep</label>
                                    </div>
                                    <div class="form-check form-check-inline mb-0">
                                        <input class="form-check-input" type="radio" name="assignment_type" id="both" value="both">
                                        <label class="form-check-label" for="both">Both</label>
                                    </div>
                               </div>
                                <!-- Radio Buttons -->
                              
                            </div>
                        </div>
                    </div>
                    @php
                        $isFinance = false;
                        foreach ($user_departments as $department) {
                            if ($department->name == 'Finance') { // Check if the department name is "Finance"
                                $isFinance = true;
                                break;
                            }
                        }
                    @endphp
                    <div class="row w-100 p-6 p-lg-0 p-md-0 p-sm-0 mb-0 pt-0">
                        <div class="col-lg-3 col-md-6 col-sm-12 col-12 mb-md-0 mb-4">
                            <span class="h6">Quote</span>
                                <select class="select2 form-select quote" name="quote">
                                    <option value="" disabled selected>Please Select</option>
                                    @foreach ($quote as $quoteid => $quoteId)
                                    <option value="{{$quoteId}}">
                                        {{ $quoteId }}</option>
                                    @endforeach
                                </select>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-12 col-12 mb-md-0 mb-4">
                            <span class="h6">Ledger</span>
                            <input type="text" class="form-control quote" id="ledger" name="ledger" placeholder="5230" 
                            @if ($isFinance) 
                               enabled
                            @else
                                disabled
                            @endif />

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
                                    <option value="">Select Status</option>
                                    <option value="pending" selected>Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 w-100 mb-md-0 mb-4">
                            <label class="form-label h6" for="description">Description</label>
                            <textarea class="form-control" id="description" rows="5" id="description" name="description"
                                placeholder="Description"></textarea>
                        </div>
                    </div>
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <!-- Mark the Tasks tab as 'active' initially -->
                            <a class="nav-link active" id="tasks-tab" data-bs-toggle="tab" href="#tasks" role="tab" aria-controls="tasks" aria-selected="true">Tasks</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="timekeep-tab" data-bs-toggle="tab" href="#timekeeptab" role="tab" aria-controls="timekeeptab" aria-selected="false">Timekeep</a>
                        </li>
                    </ul>

                <div class="tab-content">
                    <!-- Tasks Tab Content -->
                    <div class="tab-pane fade show active" id="tasks" role="tabpanel" aria-labelledby="tasks-tab">
                        <div class="mb-4 group-a">
                            <div class="card-header p-0 p-lg-0 p-md-0 p-sm-0">
                                <h3>Tasks</h3>
                            </div>
                            <div class="pt-0 pt-md-9 task_section">
                                <div class="d-flex border rounded position-relative pe-0">
                                    <div class="row w-100 p-6">
                                        <div class="col-md-3 col-12 mb-md-0 mb-4">
                                            <p class="h6 repeater-title">Amount</p>
                                            <input type="text" class="amount form-control invoice-item-price mb-5" placeholder="Amount" name="amount[]" />
                                        </div>
                                        <div class="col-md-3 col-12 mb-md-0 mb-4">
                                            <label class="h6 repeater-title" for="received_amount">Received</label>
                                            <input type="text" class="received_amount form-control invoice-item-price mb-5" placeholder="0" name="received_amount" id="received_amount"/>
                                        </div>
                                        <div class="col-md-3 col-12 mb-md-0 mb-4">
                                            <label class="h6 repeater-title">Department</label>
                                            <select class="select2 form-select department" name="department[]">
                                                <option value="" disabled selected>Please Select</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-12 mb-md-0 mb-4">
                                            <label class="h6 repeater-title">Memo</label>
                                            -
                                        </div>
                                        <div class="col-12 w-100 mb-md-0 mb-4">
                                            <textarea class="description-2 form-control" name="task_description[]" rows="5" placeholder="Description"></textarea>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column align-items-center justify-content-between border-start p-2">
                                        <i class="ti ti-x ti-lg cursor-pointer task_delete_icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <button type="button" id="add-task" class="btn btn-sm btn-primary add-task" data-repeater-create><i class="ti ti-plus ti-14px me-1_5"></i>Add Task</button>
                            </div>
                        </div>
                    </div>

                    <!-- Timekeep Tab Content -->
                    <div class="tab-pane fade" id="timekeeptab" role="tabpanel" aria-labelledby="timekeep-tab">
                        <div class="mb-4 group-b">
                        <div class="card-header p-0 p-lg-0 p-md-0 p-sm-0">
                            <h3>Timekeep</h3>
                        </div>
                            <div class="timekeep_section pt-0 pt-md-9">
                                <div class="d-flex border rounded position-relative pe-0">
                                    <div class="row w-100 p-6">
                                        <div class="col-md-2 col-12 mb-md-0 mb-4">
                                            <p class="h6 repeater-title">Person</p>
                                            <select class="select2 form-select person" id="person" name="person[]">
                                                <option value="" disabled selected>Please Select</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 col-12 mb-md-0 mb-4">
                                            <label class="h6 repeater-title" for="quantity">Qty</label>
                                            <input type="text" class="timekeep_qty form-control invoice-item-price mb-5" placeholder="0" name="quantity[]" />
                                        </div>
                                        <div class="col-md-2 col-12 mb-md-0 mb-4">
                                            <label class="h6 repeater-title">Rate</label>
                                            <span class="h6 user_rate" id="user_rate">0</span>
                                        </div>
                                        <div class="col-md-2 col-12 mb-md-0 mb-4">
                                            <label class="h6 repeater-title">Total</label>
                                            <span class="user_total" id="user_total">0</span>
                                        </div>
                                        <div class="col-md-2 col-12 mb-md-0 mb-4">
                                            <label class="h6 repeater-title">Memo</label>
                                            <span>-</span>
                                        </div>
                                        <div class="col-12 w-100 mb-md-0 mb-4">
                                            <textarea class="description-3 form-control" name="timekeep_description[]" rows="5" placeholder="Description"></textarea>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column align-items-center justify-content-between border-start p-2">
                                        <i class="ti ti-x ti-lg cursor-pointer delete_icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <button type="button" id="add-timekeep" class="btn btn-sm btn-primary add-timekeep">
                                    <i class="ti ti-plus ti-14px me-1_5"></i>Add Amount
                                </button>
                            </div>
                        </div>
                    </div>
                </div>


        </form>
    </div>




    @endsection