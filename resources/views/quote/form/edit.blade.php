@php
    $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts.horizontalLayout')

@section('title', 'Edit - Quotation')

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

    @vite(['resources/assets/js/forms-selects.js'])
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $(document).on('change', '.user', function() {
                var userId = $(this).val();  // Get selected user ID
                var departmentSelect = $(this).closest('.row').find('.user_department');  // Get the department dropdown within the same row

                if (userId) {
                    // Make an AJAX request to fetch the department for the selected user
                    $.ajax({
                        url: "{{ route('get.users.departments') }}",
                        data: {id: userId},
                        type: 'GET',
                        success: function(data) {
                            departmentSelect.empty(); // Clear any existing options
                            departmentSelect.append('<option value="" disabled>Please Select</option>'); // Default option

                            // Loop through the department list and append options
                            $.each(data, function(index, department) {
                                departmentSelect.append('<option value="' + department.id + '">' + department.name + '</option>');
                            });

                            // Reinitialize select2 for the department dropdown
                            // departmentSelect.select2();
                        },
                        error: function(error) {
                            departmentSelect.html('<p>Error fetching department data.</p>');
                            console.log(error);
                        }
                    });
                } else {
                    departmentSelect.empty();  // Clear department dropdown if no user is selected
                }
            });
            // Add another user section
            $('#addUserButton').click(function() {
                // Create the new user form fields dynamically
                var newUserForm = `
                    <div class="row add_another_user_section">
                        <div class="col-md-4 mt-2">
                            <label for="dropdown1" class="form-label">User</label>
                            <select class="select2 form-select user" name="user[]">
                                <option value="" disabled selected>Please Select</option>
                                @foreach ($users_data_quote as $users_data_quotes)
                                                <option value="{{ $users_data_quotes->id }}">
                                                    {{ $users_data_quotes->name }}
                                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mt-2">
                            <label for="dropdown1" class="form-label">Department</label>
                            <select class="select2 form-select user_department" name="user_department[]">
                                <option value="" disabled>Please Select</option>
                            </select>
                        </div>

                        <div class="col-md-4 mt-2">
                            <label for="dropdown1" class="form-label">Access</label>
                            <select class="select2 form-select user_access" name="user_access[]">
                            <option value="" disabled selected>Please Select</option>
                                <option value="read">Read Only</option>
                                <option value="edit">Edit</option>
                            </select>
                        </div>
                        <div class="col-12 mt-2">
                        <i class="ti ti-x ti-lg cursor-pointer remove-user-section" type="button"></i>
                        </div>
                    </div>
                `;

                // Append the new form to the container
                $('.add_another_user').append(newUserForm);

                // Reinitialize select2 for all select elements, both new and existing
                // $('.select2').select2();
            });
            // Remove user section
            $(document).on('click', '.remove-user-section', function() {
                $(this).closest('.add_another_user_section').remove();
            });
            //add in db
            $(document).on('click', '#add_user_quote', function() {
            // Get the selected user_id, department_id, and access level
                var userId = $('.user').map(function() {
                    return $(this).val(); // Get the value of each input
                }).get();
                var quote_id =  $('#quote_id').val();
                var departmentId = $('.user_department').map(function() {
                    return $(this).val(); // Get the value of each input
                }).get();

                var accessLevel = $('.user_access').map(function() {
                    return $(this).val(); // Get the value of each input
                }).get();

                // Check if values are selected
                if (userId.length != 0 && departmentId.length != 0 && accessLevel.length != 0 && quote_id.length !=0) {
                    // Make AJAX request to insert data into quote_users table
                    $.ajax({
                        url: "{{ route('quote_users.store') }}", // Backend route to handle the insertion
                        method: 'POST',
                        data: {
                            user_id: userId,
                            department_id: departmentId,
                            access_level: accessLevel,
                            quote_id:quote_id,
                            _token: "{{ csrf_token() }}" // CSRF token for security
                        },
                        success: function(response) {
                            if (response.success) {

                                $('#shareModal').find('.modal-body').prepend('<div class="alert alert-success">' + response.message  + '</div>');
                                $('.user').val('').trigger('change'); // Reset user field and trigger change for select2
                                $('.user_department').val('').trigger('change'); // Reset department field and trigger change for select2
                                $('.user_access').val('').trigger('change'); // Reset access field and trigger change for select2

                                // Remove any success or error messages
                                $('#shareModal .alert-danger').remove();
                                // Remove any error or success messages
                                setTimeout(function() {
                                $('#shareModal .alert-success').remove();
                            }, 4000);
                                setTimeout(function() {
                                $('#shareModal').modal('hide');
                            }, 5000);
                            // $('#shareModal').find('.add_another_user').remove();

                            } else {
                            var errorMessage = 'Please select all fields (User, Department, and Access Level).';
                            $('#shareModal').find('.modal-body').prepend('<div class="alert alert-danger">' + errorMessage + '</div>');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', error);
                            var errorMessage = 'Please select all fields (User, Department, and Access Level).';
                            $('#shareModal').find('.modal-body').prepend('<div class="alert alert-danger">' + errorMessage + '</div>');
                            // alert('An error occurred while adding the user.');
                            // Do not hide the modal on error, keep it open
                        }
                    });
                } else {
                    // Show error message and keep modal open
                    var errorMessage = 'Please select all fields (User, Department, and Access Level).';

                    // Check if error message is already in the modal to avoid duplicates
                    if ($('#shareModal .alert-danger').length === 0) {
                        $('#shareModal').find('.modal-body').prepend('<div class="alert alert-danger">' + errorMessage + '</div>');
                    }
                    $('#shareModal .alert-success').remove();
                    // Ensure the modal stays open, even if it's hidden or closed earlier
                    $('#shareModal').modal('show');
                }
            });


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

            $('.add-item').on('click', function() {
            // HTML structure for the new task section
            var newItem = `
               <div class="repeater-wrapper px-3 px-sm-0 pt-0 pt-md-9 quation_task_section">
                                <div class="d-flex border rounded position-relative pe-0">
                                    <div class="row w-100 p-6">
                                    <input type="hidden" class="task_id[]" id="task_id" name="task_id[]" value="">
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

            function showExpiryDate() {
                const maxDateSelectionOption = new Date();
                maxDateSelectionOption.setDate(maxDateSelectionOption.getDate() + 60);

                // Initialize Flatpickr
                $("#expiry-on").flatpickr({
                    dateFormat: "Y-m-d", // Format as YYYY-MM-DD
                    minDate: "today", // Disable past dates
                    maxDate: maxDateSelectionOption
                    // Set default date to 30 days from today
                });
            }
            $('#quotationForm').on('submit', function(e) {
                e.preventDefault();
                var quote_id = $('#quote_id').val();
                expiryOn = $("#expiry-on").val();
                reference = $("#reference").val();
                var client = $("#client").val();
                description = $("#description").val();
                status = $("#status").val();
                var task_id = $('.task_id').map(function() {
                    var value = $(this).val();
                    return value === "" ? null : value; // If the value is empty, return null
                }).get();

                // Check for an empty array and set it to null
                if (task_id.every(value => value === null)) {
                    task_id = null;
                }

                // console.log(task_id,'task_id');return false;
                // If all values are null or empty, set amounts to null
                // if (task_id.every(value => value === null)) {
                //     task_id = null;  // Set amounts to null if all values are null
                // }
                // console.log(task_id,'task_id');return false;
                var amounts = $('.amount').map(function() {
                    var value = $(this).val();
                    return value === "" ? null : value; // Return null if the value is empty
                }).get();
               var one_task_id =$('.one_task_id').map(function() {
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
                    one_task_id:one_task_id,
                    quote_id:quote_id,
                    task_id:task_id,
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
                $.ajax({
                    url: '{{ route('quotes.update') }}',
                    method: 'PUT',
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
                        alert('An error occurred.');
                        console.log(xhr.responseText);
                    }
                });
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
            <form class="source-item" id="quotationForm" method="POST" action="{{ route('quotes.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="id"  id="quote_id" value="{{ $quote->id }}">
                <div class="ms-auto d-flex justify-content-end mb-2">
                    <button type="submit" class="btn btn-outline-primary waves-effect">Save</button>
                    <a type="reset" class=" mx-2 btn btn-outline-primary waves-effect d-grid"
                    href="#" data-bs-toggle="modal" data-bs-target="#shareModal">Share</a>
                    <a type="reset" class="mx-2 btn btn-outline-primary waves-effect d-grid"
                        href="{{ route('quotes.index') }}">Cancel</a>
                </div>
                <div class="card invoice-preview-card p-sm-12 p-md-6 p-lg-6">

                    <div class="card-header p-lg-0 p-md-0 p-sm-0">
                        <h3 class="mb-0 mb-sm-3">Edit Quote</h3>
                    </div>
                    <div class="card-body pt-0 px-0">

                        <div class="row w-100 p-6 p-lg-0 p-md-0 p-sm-0 mb-5 pt-0">
                            <div class="col-lg-3 col-md-6 col-sm-12 col-12 mb-md-0 mb-4">
                                <span class="h6">Expiry On</span>
                                <input type="text" class="form-control invoice-date  flatpickr-input" id="expiry-on" name="expiry-on"
                                    placeholder="YYYY-MM-DD" value="{{ $quote->expiry_at }}" />
                            </div>
                            <div class="col-lg-3 col-md-6 col-12 mb-md-0 mb-4">
                                <span class="h6">Reference</span>
                                <input type="text" class="form-control mb-0 mb-md-5" id="reference" name="reference"
                                    placeholder="Reference" value="{{ $quote->reference }}"  maxlength="6" />
                            </div>
                            <input type="hidden" id="client_id" value="{{ $quote->client_id }}">
                            <div class="col-lg-3 col-md-6 col-12 mb-md-0 mb-4">
                                <span class="form-label h6" for="client">Client</span>
                                <select id="client" class="select2 form-select" name="client">
                                    <option disabled selected>Please Select</option>
                                        @foreach ($client as $clientId => $clientName)
                                            <option value={{ $clientId }}
                                                {{ $clientId == $quote->client_id ? 'selected' : '' }}>
                                                {{ $clientName }}</option>
                                        @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6 col-12 mb-md-0">
                                <div class="mb-6">
                                    <span class="form-label h6" for="status">Status</span>
                                    <select id="status" class="select2 form-select" name="status">
                                        <option value="">Select Status
                                        </option>
                                        <option value="quoted" {{ $quote->status == 'quoted' ? 'selected' : '' }}>Quoted
                                        </option>
                                        <option value="awarded" {{ $quote->status == 'awarded' ? 'selected' : '' }}>Awarded
                                        </option>
                                        <option value="lost" {{ $quote->status == 'lost' ? 'selected' : '' }}>Lost
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 w-100 mb-md-0 mb-4">
                                <label class="form-label h6" for="description">Description</label>
                                <textarea class="form-control" id="description" rows="5" id="description" name="description"
                                    placeholder="Description">{{ $quote->description }}</textarea>
                            </div>
                        </div>

                        <div class="mb-4 group-a">
                        <div class="card-header p-lg-0 p-md-0 p-sm-0 py-0">
                                    <h3>Tasks</h3>
                        </div>
                            @foreach ($quote_task as $task)
                            <input type="hidden" class="one_task_id" id="one_task_id" name="one_task_id[]" value="{{ $task->id }}">
                                <div class="repeater-wrapper px-3 px-sm-0 pt-0 pt-md-9 quation_task_section">
                                    <div class="d-flex border rounded position-relative pe-0">

                                        <div class="row w-100 p-6">
                                            <input type="hidden" class="task_id[]" id="task_id" name="task_id[]" value="{{ $task->id }}">
                                            <div class="col-md-4 col-12 mb-md-0 mb-4">
                                                <p class="h6 repeater-title">Amount</p>
                                                <input type="text" class="amount form-control invoice-item-price mb-5"
                                                    placeholder="Amount" name="amount" value="{{ $task->amount }}" />
                                            </div>
                                            <div class="col-md-4 col-12 mb-md-0 mb-4">
                                                <label class="h6 repeater-title" for="currency">Currency</label>
                                                <input type="text" class="currency form-control invoice-item-price mb-5"
                                                    placeholder="Currency" name="currency[]" value="{{ $task->currency_name ?? '' }}" readonly/>
                                                <input type="hidden" id="currency_data" class="currency_data" name="currency_data[]" value="{{ $task->currency_id ?? '' }}">
                                            </div>
                                            {{-- dcsdcs --}}
                                            <div class="department-container col-md-4 col-12 mb-md-0 mb-4">
                                                <label class="h6 repeater-title">Department</label>
                                                <select class=" form-select department" name="department">
                                                    <option value="" disabled selected>Please Select</option>
                                                    @foreach ($departments as $departmentName)
                                                        <option value={{ $task->department_id }}
                                                            {{ $departmentName['id'] == $task->department_id ? 'selected' : '' }}>
                                                            {{ $departmentName['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 w-100 mb-md-0 mb-4">
                                                <textarea class="description-2 form-control" name="description" rows="5" placeholder="Description">{{ $task->description }}</textarea>
                                            </div>

                                        </div>

                                        <div
                                            class="d-flex flex-column align-items-center justify-content-between border-start p-2">
                                            <i class="ti ti-x ti-lg cursor-pointer delete_icon"></i>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            @if(count($quote_task) == 0)

                            <div class="repeater-wrapper pt-0 pt-md-9 quation_task_section">
                                <div class="d-flex border rounded position-relative pe-0">
                                    <div class="row w-100 p-6">
                                    <input type="hidden" class="task_id[]" id="task_id" name="task_id[]" value="">
                                        <div class="col-md-4 col-12 mb-md-0 mb-4">
                                            <p class="h6 repeater-title">Amount</p>
                                            <input type="text" class="amount form-control invoice-item-price mb-5"
                                                placeholder="Amount" name="amount[]" />
                                        </div>
                                        <div class="col-md-4 col-12 mb-md-0 mb-4">
                                            <label class="h6 repeater-title" for="currency">Currency</label>
                                            <input type="text" class="currency form-control invoice-item-price mb-5"
                                            placeholder="currency" name="currency[]"/>
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
                            </div>
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <button type="button" id="add-item" class="btn btn-sm btn-primary add-item ms-3 ms-sm-0"><i class='ti ti-plus ti-14px me-1_5'></i>Add Item</button>
                            </div>

                        </div>
            </form>
        </div>
        {{-- START :  Modal for share  --}}
         <!-- Modal -->
        <div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="shareModalLabel">Share Quote with Users</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body add_another_user">
                    <div class="row">
                           @if(count($quote_users) > 0)
                                @foreach ($quote_users as $quote_users_data)
                                    <div class="col-md-4">
                                        <label for="dropdown1" class="form-label">User</label>
                                        <select class="select2 form-select user" id="userDropdown_{{ $loop->index }}" name="user[]">
                                            <option value="" disabled>Please Select</option>
                                            @foreach ($users_data_quote as $users_data_quotes)
                                                <option value="{{ $users_data_quotes->id }}"
                                                    {{ $quote_users_data->user_id == $users_data_quotes->id ? 'selected' : '' }}>
                                                    {{ $users_data_quotes->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="dropdown1" class="form-label">Department</label>
                                        <select class="select2 form-select user_department" id="user_department_{{ $loop->index }}" name="user_department[]">
                                            <option value="" disabled>Please Select</option>
                                            @foreach ($departments as $department)
                                                <option value="{{ $department->id }}"
                                                    {{ $quote_users_data->department_id == $department->id ? 'selected' : '' }}>
                                                    {{ $department->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="dropdown1" class="form-label">Access</label>
                                        <select class="select2 form-select user_access" name="user_access[]">
                                            <option value="" disabled selected>Please Select</option>
                                            <option value="read" {{ $quote_users_data->access_level == 'read' ? 'selected' : '' }}>Read Only</option>
                                            <option value="edit" {{ $quote_users_data->access_level == 'edit' ? 'selected' : '' }}>Edit</option>
                                        </select>
                                    </div>
                                @endforeach
                            @else
                                <!-- For when quote_users is empty -->
                                <div class="col-md-4">
                                    <label for="dropdown1" class="form-label">User</label>
                                    <select class="select2 form-select user" id="userDropdown" name="user[]">
                                        <option value="" disabled selected>Please Select</option>
                                        @foreach ($users_data_quote as $users_data_quotes)
                                                <option value="{{ $users_data_quotes->id }}">
                                                    {{ $users_data_quotes->name }}
                                                </option>
                                            @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="dropdown1" class="form-label">Department</label>
                                    <select class="select2 form-select user_department" id="user_department" name="user_department[]">
                                        <option value="" disabled>Please Select</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="dropdown1" class="form-label">Access</label>
                                    <select class="select2 form-select user_access" name="user_access[]">
                                        <option value="" disabled selected>Please Select</option>
                                        <option value="read">Read Only</option>
                                        <option value="edit">Edit</option>
                                    </select>
                                </div>
                            @endif


                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"  id="add_user_quote">Save</button>
                        <button type="button" class="btn btn-primary" id="addUserButton">Add Another User</button>
                    </div>
                </div>
            </div>
        </div>
        {{-- END :  Modal for share confirmation --}}

    @endsection
