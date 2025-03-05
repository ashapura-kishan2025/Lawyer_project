@php
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts.horizontalLayout')

@section('title', 'Update Assignment')

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
<style>

    </style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
    $(document).ready(function() {
    // Event listener for user dropdown change
    $(document).on('change', '.user', function() {
        var userId = $(this).val();  // Get selected user ID
        var departmentSelect = $(this).closest('.row').find('.user_department');  // Get the department dropdown within the same row

        if (userId) {
            // Make an AJAX request to fetch the department for the selected user
            $.ajax({
                url: "{{ route('assignment.get.users.departments') }}",
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
                         @foreach ($users_data_assignment as $users_data_assignments)
                                                        <option value="{{ $users_data_assignments->id }}">
                                                            {{ $users_data_assignments->name }}
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
        var assignment_id =  $('#assignment_id').val();
        var departmentId = $('.user_department').map(function() {
            return $(this).val(); // Get the value of each input
        }).get();

        var accessLevel = $('.user_access').map(function() {
            return $(this).val(); // Get the value of each input
        }).get();

        // Check if values are selected
        if (userId.length != 0 && departmentId.length != 0 && accessLevel.length != 0 && assignment_id.length !=0) {
            // Make AJAX request to insert data into quote_users table
            $.ajax({
                url: "{{ route('assignment_users.store') }}", // Backend route to handle the insertion
                method: 'POST',
                data: {
                    user_id: userId,
                    department_id: departmentId,
                    access_level: accessLevel,
                    assignment_id:assignment_id,
                    _token: "{{ csrf_token() }}" // CSRF token for security
                },
                success: function(response) {
                    if (response.success) {

                        $('#shareModal_assignment').find('.modal-body').prepend('<div class="alert alert-success">' + response.message  + '</div>');
                        $('.user').val('').trigger('change'); // Reset user field and trigger change for select2
                        $('.user_department').val('').trigger('change'); // Reset department field and trigger change for select2
                        $('.user_access').val('').trigger('change'); // Reset access field and trigger change for select2

                        // Remove any success or error messages
                        $('#shareModal_assignment .alert-danger').remove();
                        // Remove any error or success messages
                        setTimeout(function() {
                        $('#shareModal_assignment .alert-success').remove();
                    }, 4000);
                        setTimeout(function() {
                        $('#shareModal_assignment').modal('hide');
                    }, 5000);


                    } else {
                        var errorMessage = 'Please select all fields (User, Department, and Access Level).';
                        $('#shareModal_assignment').find('.modal-body').prepend('<div class="alert alert-danger">' + errorMessage + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = 'Please select all fields (User, Department, and Access Level).';
                    $('#shareModal_assignment').find('.modal-body').prepend('<div class="alert alert-danger">' + errorMessage + '</div>');
                    // Do not hide the modal on error, keep it open
                }
            });
        } else {
            // Show error message and keep modal open
            var errorMessage = 'Please select all fields (User, Department, and Access Level).';

            // Check if error message is already in the modal to avoid duplicates
            if ($('#shareModal_assignment .alert-danger').length === 0) {
                $('#shareModal_assignment').find('.modal-body').prepend('<div class="alert alert-danger">' + errorMessage + '</div>');
            }
            $('#shareModal_assignment .alert-success').remove();
            // Ensure the modal stays open, even if it's hidden or closed earlier
            $('#shareModal_assignment').modal('show');
        }
    });

       // Delete the section when the delete icon is clicked
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
                                             <input type="hidden" id="timekeep_id" class="timekeep_id" name="timekeep_id[]" value="">
                                                <div class="row w-100 p-6">
                                                <div class="col-md-3 col-12 mb-md-0 mb-4">
                                                    <label class="h6 repeater-title">Person</label>
                                                    <select class="select2 form-select person" name="person[]">
                                                        <option value="" disabled selected>Please Select</option>
                                                            @foreach ($user as $userId => $userName)
                                                                    <option value="{{ $userId }}">
                                                                        {{ $userName }}</option>
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
                                        </div>  `;

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
                            <input type="hidden" class="task_id" name="task_id[]" value=""> <!-- Empty value for new task -->
                            <div class="col-md-3 col-12 mb-md-0 mb-4">
                                <p class="h6 repeater-title">Amount</p>
                                <input type="text" class="amount form-control invoice-item-price mb-5" placeholder="Amount" name="amount[]" />
                            </div>
                            <div class="col-md-3 col-12 mb-md-0 mb-4">
                                <label class="h6 repeater-title" for="received_amount">Received</label>
                                <input type="text" class="received_amount form-control invoice-item-price mb-5" placeholder="0" name="received_amount[]" />
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

        //ajax for get amount based on users
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
                    alert('An error occurred.');
                    console.log(xhr.responseText);
                }
            });
        });
        $(document).on('keyup', '.timekeep_qty', function() {
            var section = $(this).closest('.timekeep_section');
            var qty = parseFloat($(this).val()); // Ensure that the quantity is a number
            var rate = parseFloat(section.find('.user_rate').val()); // Ensure that the rate is a number
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
            // console.log(quote,'quote');return false;
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
           // Collect the task IDs
            var task_id = $('.task_id').map(function() {
                return $(this).val();  // Get the value of each input
            }).get();
            var timekeep_id = $('.timekeep_id').map(function() {
                    var value = $(this).val();
                    return value === "" ? null : value; // Return null if the value is empty
                }).get();

                // If all values are null or empty, set amounts to null
                if (timekeep_id.every(value => value === null)) {
                    timekeep_id = null;  // Set amounts to null if all values are null
                }
            // console.log(timekeep_id,'asdcasdc');return false;
            var departments = $('.department').map(function() {
                    var value = $(this).val();
                    return value === "" ? null : value; // Return null if the value is empty
                }).get();

                // If all values are null or empty, set amounts to null
                if (departments.every(value => value === null)) {
                    departments = null;  // Set amounts to null if all values are null
                }
            // console.log(departments,'departments');return false;
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
            console.log(userValue,'dcsdcsdcsd');
            if(client == '' || client == null){
                alert("Please select a client.");
                return; // Stop form submission
            } if(status == '' || status == null){
                alert("Please select a status.");
                return; // Stop form submission
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
                userValue:userValue,
                task_id:task_id,
                timekeep_id:timekeep_id
            };
            // Send AJAX request
            $.ajax({
                url: '{{ route('assignment.update') }}',
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
            // Show Task tab and activate the task tab
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
    //this is for task_memo
    $('#add_memo_task').click(function() {
        var selectedTasks = [];

        // Loop through all checked checkboxes
            $('input[name="task_assignment_check[]"]:checked').each(function() {
                var taskId = $(this).data('task-id'); // Get the task_id
                var assignmentId = $(this).data('assignment-id');
                var department_id = $(this).data('department-id');
                // Store the task_id and assignment_id in an array
                selectedTasks.push({
                    task_id: taskId,
                    assignment_id: assignmentId,
                    department_id:department_id
                });
            });
        let isValid = true;
        var memo_number = $('#memo_number').val();
        if (!memo_number) {  // If "Please Select" option is selected or nothing is selected
            $('#memo_number_error').text('Please enter a memo amount.').show();
            $('#memo_number').addClass('is-invalid');
            isValid = false;
        }
        if (isValid) {
            if (selectedTasks.length > 0) {
                // Send an Ajax request with the selected task and assignment IDs
                $.ajax({
                    url: '{{ route("assignment.memo") }}', // Replace with your server URL
                    method: 'POST',
                    data: {
                        memo_number:memo_number,
                        tasks: selectedTasks, // Send the array of selected tasks with task_id and assignment_id
                        _token: '{{ csrf_token() }}' // Include CSRF token for security
                    },
                    success: function(response) {
                        if(response.success){
                            $('#shareModal').find('.modal-body').prepend('<div class="alert alert-success">' + response.message  + '</div>');
                            $('#memo_number').val('');

                            if (response.updated_tasks && response.updated_tasks.length > 0) {
                                // Loop through each updated task and update the memo number
                                response.updated_tasks.forEach(function(task) {
                                    // Find the task by matching the task_id (adjust selector if needed)
                                    var taskId = task.id;
                                    $('.updated_memo_number_task_' + taskId).text(task.memo_number);
                                    if(task.received_amount == 0 || task.received_amount == '0') {
                                        $('.task_status_'+ taskId).text('notsent');
                                    }
                                    // If amount equals received amount, set to 'paid'
                                    else if(task.amount == task.received_amount) {
                                        $('.task_status_'+ taskId).text('paid');
                                    }
                                    // If received amount is less than the amount, set to 'unpaid'
                                    else if(task.received_amount < task.amount && task.received_amount != 0) {
                                        $('.task_status_'+ taskId).text('unpaid');
                                    }
                                });
                            }
                            setTimeout(function() {
                                $('#shareModal .alert-success').remove();
                            }, 4000);
                        } else{
                            $('#shareModal').find('.modal-body').prepend('<div class="alert alert-danger">' + response.message  + '</div>');
                            $('#memo_number').val('');
                            setTimeout(function() {
                                $('#shareModal .alert-danger').remove();
                            }, 4000);
                        }
                        setTimeout(function() {
                            $('#shareModal').modal('hide');
                        }, 4000);
                        $('input[name="task_assignment_check[]"]:checked').prop('checked', false);
                        $('#memo_number_error').text('').hide();
                        $('#memo_number').removeClass('is-invalid');
                    },
                    error: function(error) {
                        console.log('Error:', error);
                        // Handle error
                    }
                });
            } else {
                var message = "Please select atleast one task from the Task list";
                $('#shareModal').find('.modal-body').prepend('<div class="alert alert-danger">' + message  + '</div>');
                setTimeout(function() {
                    $('#shareModal .alert-danger').remove();
                }, 4000);
                $('#memo_number_error').text('').hide();
                $('#memo_number').removeClass('is-invalid');
            }
        }
    });
    //this is for timeekeep memo
    $('#add_memo_timkeep').click(function() {
        var selectedTasks = [];

        // Loop through all checked checkboxes
            $('input[name="task_timekeep_check[]"]:checked').each(function() {
                var taskId = $(this).data('task-id'); // Get the task_id
                var assignmentId = $(this).data('assignment-id'); // Get the assignment_id
                // Store the task_id and assignment_id in an array
                selectedTasks.push({
                    task_id: taskId,
                    assignment_id: assignmentId
                });
            });
        let isValid = true;
        var memo_number = $('#memo_number_timekeep').val();
        if (!memo_number) {  // If "Please Select" option is selected or nothing is selected
            $('#memo_number_timekeep_error').text('Please enter a memo amount.').show();
            $('#memo_number_timekeep').addClass('is-invalid');
            isValid = false;
        }

        if (isValid) {
            if (selectedTasks.length > 0) {
                // Send an Ajax request with the selected task and assignment IDs
                $.ajax({
                    url: '{{ route("assignment.memo.timkeep") }}', // Replace with your server URL
                    method: 'POST',
                    data: {
                        memo_number:memo_number,
                        tasks: selectedTasks, // Send the array of selected tasks with task_id and assignment_id
                        _token: '{{ csrf_token() }}' // Include CSRF token for security
                    },
                    success: function(response) {
                        if(response.success){
                            $('#shareModal_timekeep').find('.modal-body').prepend('<div class="alert alert-success">' + response.message  + '</div>');
                            $('#memo_number_timekeep').val('');
                            if (response.updated_tasks && response.updated_tasks.length > 0) {
                                // Loop through each updated task and update the memo number
                                response.updated_tasks.forEach(function(task) {
                                    // Find the task by matching the task_id (adjust selector if needed)
                                    var taskId = task.id;
                                    $('.updated_memo_number_timekeep_' + taskId).text(task.memo_number);  // Update the memo number in the span
                                });
                            }
                            setTimeout(function() {
                                $('#shareModal_timekeep .alert-success').remove();
                            }, 4000);
                        } else{
                            $('#shareModal_timekeep').find('.modal-body').prepend('<div class="alert alert-danger">' + response.message  + '</div>');
                            $('#memo_number_timekeep').val('');
                            setTimeout(function() {
                                $('#shareModal_timekeep .alert-danger').remove();
                            }, 4000);
                        }
                        // $('#memo_number').html(response.);
                        setTimeout(function() {
                            $('#shareModal_timekeep').modal('hide');
                        }, 4000);
                        $('input[name="task_timekeep_check[]"]:checked').prop('checked', false);
                        $('#memo_number_timekeep_error').text('').hide();
                        $('#memo_number_timekeep').removeClass('is-invalid');
                    },
                    error: function(error) {
                        console.log('Error:', error);
                        // Handle error
                    }
                });
            } else {
                var message = "Please select atleast one Timekeep from the Timekeep list";
                $('#shareModal_timekeep').find('.modal-body').prepend('<div class="alert alert-danger">' + message  + '</div>');
                setTimeout(function() {
                    $('#shareModal_timekeep .alert-danger').remove();
                }, 4000);
                $('#memo_number_timekeep_error').text('').hide();
                $('#memo_number_timekeep').removeClass('is-invalid');
            }
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
        <form class="source-item" id="assignmentForm" method="POST" action="{{ route('assignment.update') }}">
        @csrf
        @method('PUT')
            <input type="hidden" id="assignment_id" value="{{$assignment->id ?? ''}}">
            <div class="ms-auto d-flex justify-content-end mb-2">
                <button type="submit" class="btn btn-outline-primary waves-effect">Save</button>
                <a type="reset" class="mx-2 btn btn-outline-primary waves-effect"
                href="#"  data-bs-toggle="modal" data-bs-target="#shareModal_assignment">Share</a>
                <a type="reset" class="mx-2 btn btn-outline-primary waves-effect d-grid"
                    href="{{ route('assignment.index') }}">Cancel</a>
            </div>
            <div class="card invoice-preview-card p-sm-12 p-md-6 p-lg-6">

                <div class="card-header pb-0 p-lg-0 p-md-0 p-sm-0">
                    <h3>Update Assignment</h3>
                </div>
                <div class="card-body pt-0 px-0">
                    <div class="row w-100 p-6 p-lg-0 p-md-0 p-sm-0 mb-2 mb-md-4 pt-0">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-12 mb-md-0">
                            <div class="d-flex align-items-center row mb-sm-2">
                                <div class="col-12 col-md-6 mb-2">
                                    <span class="h6 mb-0 me-2 ">Select Assignment Type:</span>
                                </div>
                                <div class="col-12">
                                    <!-- Radio Buttons -->
                                    <div class="form-check form-check-inline
                                    mb-0">
                                        <input class="form-check-input" type="radio" name="assignment_type" id="regular" value="regular" {{ $assignment->assignment_type == 'regular' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="regular">Regular</label>
                                    </div>
                                    <div class="form-check form-check-inline mb-0">
                                        <input class="form-check-input" type="radio" name="assignment_type" id="timekeep" value="timekeep" {{ $assignment->assignment_type == 'timekeep' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="timekeep">Timekeep</label>
                                    </div>
                                    <div class="form-check form-check-inline mb-0">
                                        <input class="form-check-input" type="radio" name="assignment_type" id="both" value="both" {{ $assignment->assignment_type == 'both' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="both">Both</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row w-100 p-6 p-lg-0 p-md-0 p-sm-0 mb-0 pt-0">
                        <div class="col-lg-3 col-md-6 col-sm-12 col-12  mb-4">
                            <span class="h6">Quote</span>
                            @if($assignment->quote_id)
                            <input type="text" class="form-control quote" id="quote" name="quote"
                            placeholder="quote"  value="{{ $assignment->quote_id ?? '' }}" readonly/>
                            @else
                                <select class="select2 form-select quote" name="quote" id="quote">
                                <option value="" disabled selected>Please Select</option>
                                        @foreach ($quote as $quoteid => $quoteId)
                                        <option value="{{$quoteId}}">{{ $quoteId }}</option>
                                        @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-12 col-12  mb-4">
                            <span class="h6">Ledger</span>
                            <input type="text" class="form-control ledger" id="ledger" name="ledger"
                                placeholder="5230"  value="{{ $assignment->ledger ?? '' }}" />
                        </div>
                        <div class="col-lg-3 col-md-6 col-12  mb-4">
                            <span class="form-label h6" for="client">Client</span>
                            <select id="client" class="select2 form-select" name="client">
                                <option disabled selected>Please Select</option>
                                @foreach ($client as $clientId => $client_name)
                                <option value="{{ $assignment->client_id }}"
                                    {{ $clientId == $assignment->client_id ? 'selected' : '' }}>
                                    {{ $client_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6 col-12  mb-4">
                                <span class="form-label h6" for="status">Status</span>
                                <select id="status" class="select2 form-select" name="status">
                                    <option value="">Select Status</option>
                                    <option value="pending" {{ $assignment->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="processing" {{ $assignment->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="completed" {{ $assignment->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                        </div>
                        <div class="col-12 w-100 mb-4">
                            <label class="form-label" for="description">Description</label>
                            <textarea class="form-control" id="description" rows="5" id="description" name="description"
                                placeholder="Description">{{ $assignment->description ?? '' }}</textarea>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-12 d-flex justify-content-start align-items-start flex-wrap  mx-5 mx-sm-0">
                            <div class="d-flex px-3 py-2 rounded me-6" style="background: #80808021;" >
                                <p class="mb-0 me-2" style="font-size: 14px;">Total Amount: </p><p class="mb-0" style="font-size: 14px;">{{$totalAmount ?? ''}} {{$currency_data['code'] ?? ''}}</p>
                                </div>
                                <div class="d-flex px-3 py-2 rounded me-6" style="background: #80808021;">
                                <p class="mb-0 me-2" style="font-size: 14px;">Received Amount: </p><p class="mb-0" style="font-size: 14px;">{{$received_amount ?? ''}} {{$currency_data['code'] ?? ''}}</p>
                                </div>
                                <div class="d-flex px-3 py-2 rounded " style="background: #80808021;" >
                                <p class="mb-0 me-2" style="font-size: 14px;">Balance: </p><p class="mb-0" style="font-size: 14px;">{{$amountDifference ?? ''}} {{$currency_data['code'] ?? ''}}</p>
                                </div>
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

                <div class="tab-content mt-4">
                    <!-- Tasks Tab Content -->
                        <div class="tab-pane fade show active" id="tasks" role="tabpanel" aria-labelledby="tasks-tab">

                            <div class="mb-4 group-a">
                                <div class="card-header p-lg-0 p-md-0 p-sm-0">
                                    <h3>Tasks</h3>
                                </div>

                                @foreach ($assignment_task as $task)
                                <div class="pt-0 pt-md-9 task_section mt-4">
                                    <div class="d-flex border rounded position-relative pe-0">
                                        <input type="checkbox"
                                            class="form-check-input" name="task_assignment_check[]"
                                            id="task_assignment_check_{{ $task->id }}"
                                            data-task-id="{{ $task->id }}"
                                            data-assignment-id="{{ $task->assignment_id }}" data-department-id="{{$task->department_id}}" style="position: absolute;top: -2.4rem;">
                                        <div class="row w-100 p-6">
                                            <input type="hidden" id="task_id" class="task_id" name="task_id[]" value="{{$task->id}}">
                                            <div class="col-md-3 col-12 mb-md-0 mb-4">
                                                <p class="h6 repeater-title ms-3">Amount</p>
                                                <input type="text" class="amount form-control invoice-item-price mb-5" placeholder="Amount" name="amount[]" value="{{ $task->amount ?? '' }}" />
                                            </div>
                                            <div class="col-md-3 col-12 mb-md-0 mb-4">
                                                <label class="h6 repeater-title" for="received_amount">Received</label>
                                                <input type="text" class="received_amount form-control invoice-item-price mb-5" placeholder="0" name="received_amount" id="received_amount" value="{{ $task->received_amount ?? '' }}"/>
                                            </div>

                                            <div class="col-md-3 col-12 mb-md-0 mb-4">
                                                <label class="h6 repeater-title">Department</label>
                                                <select class="select2 form-select department" name="department[]" value="one_user">
                                                    <option value="" disabled selected>Please Select</option>
                                                    @foreach ($departments as  $departmentName)
                                                            <option value={{ $task->department_id }}
                                                                {{ $departmentName['id'] == $task->department_id ? 'selected' : '' }}>
                                                                {{ $departmentName['name'] }}</option>
                                                        @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3 col-12 mb-md-0 mb-4">
                                                <label class="h6 repeater-title">Memo</label>
                                                <span id="updated_memo_number_task_{{$task->id}}" class="updated_memo_number_task_{{$task->id}}">{{$task['memo_number'] ?? ''}}</span>
                                                @if($task->memo_number || $task->memo_number != 0)
                                                    @if($task->status == 'notsent')
                                                    <span class="task_status_{{$task->id}}">Not Sent</span>
                                                    @endif
                                                    @if($task->status == 'unpaid')
                                                    <span class="task_status_{{$task->id}}">Unpaid</span>
                                                    @endif
                                                    @if($task->status == 'paid')
                                                    <span class="task_status_{{$task->id}}">Paid</span>
                                                    @endif
                                                @endif
                                            </div>
                                            <div class="col-12 w-100 mb-md-0 mb-4">
                                                <textarea class="description-2 form-control" name="task_description[]" rows="5" placeholder="Description">{{ $task->description ?? '' }}</textarea>
                                            </div>
                                        </div>
                                        @if($task->memo_number || $task->memo_number != 0)
                                            <div class="d-flex flex-column align-items-center justify-content-between border-start p-2">
                                                <i class="ti ti-x ti-lg cursor-pointer task_delete_icon"></i>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                                @if(count($assignment_task) == 0)
                                <div class="pt-0 pt-md-9 task_section mt-4">
                                        <div class="d-flex border rounded position-relative pe-0">
                                            <div class="row w-100 p-6">
                                                <div class="col-md-3 col-12 mb-md-0 mb-4">
                                                <input type="hidden" id="task_id" class="task_id" name="task_id[]" value="">
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
                                                            <option value="{{ $departmentId }}">
                                                                {{ $departmentName }}</option>
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
                                </div>
                                @endif
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <button type="button" id="add-task" class="btn btn-sm btn-primary add-task" data-repeater-create><i class="ti ti-plus ti-14px me-1_5"></i>Add Task</button>
                                </div>
                                @if(count($assignment_task) != 0)
                                    <div class="col-6 mb-3 text-end">
                                        <button type="button" id="add_memo_assignment"  data-bs-toggle="modal" data-bs-target="#shareModal" class="btn btn-sm btn-primary add_memo_assignment" data-repeater-create><i class="ti ti-plus ti-14px me-1_5"></i>Add Memo</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    <!-- Timekeep Tab Content -->
                    <div class="tab-pane fade" id="timekeeptab" role="tabpanel" aria-labelledby="timekeep-tab">
                        <div class="mb-4 group-b">
                                    @foreach ($timekeeps as $timekeeps_data)
                                    <div class="timekeep_section pt-0 pt-md-9 mt-4">
                                        <div class="d-flex border rounded position-relative pe-0">
                                            <div class="row w-100 p-6">
                                                <input type="checkbox"
                                                class="form-check-input" name="task_timekeep_check[]"
                                                id="task_timekeep_check_{{ $timekeeps_data->id }}"
                                                data-task-id="{{ $timekeeps_data->id }}"
                                                data-assignment-id="{{ $timekeeps_data->assignment_id }}"style="position: absolute;top: -2.4rem;">

                                                    <input type="hidden" id="timekeep_id" class="timekeep_id" name="timekeep_id[]" value="{{$timekeeps_data->id}}">
                                                    <div class="col-md-2 col-12 mb-md-0 mb-4">
                                                        <p class="h6 repeater-title ms-3">Person</p>
                                                        <select class="select2 form-select person" id="person" name="person[]">
                                                            <option value="" disabled selected>Please Select</option>
                                                            @foreach ($user as $userId => $userName)
                                                                    <option value={{ $timekeeps_data->user_id }}
                                                                        {{ $userId == $timekeeps_data->user_id ? 'selected' : '' }}>
                                                                        {{ $userName }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 col-12 mb-md-0 mb-4">
                                                        <label class="h6 repeater-title" for="quantity">Qty</label>
                                                        <input type="text" class="timekeep_qty form-control invoice-item-price mb-5" placeholder="0" name="quantity[]" value="{{ $timekeeps_data->quantity ?? '' }}" />
                                                    </div>
                                                    <div class="col-md-2 col-12 mb-md-0 mb-4">
                                                        <label class="h6 repeater-title">Rate</label>
                                                        <span class="h6 user_rate">{{ $timekeeps_data->rate ?? '' }}</span>
                                                    </div>
                                                    <div class="col-md-2 col-12 mb-md-0 mb-4">
                                                        <label class="h6 repeater-title">Total</label>
                                                        <span class="user_total" >{{ $timekeeps_data->amount ?? '' }}</span>
                                                    </div>
                                                    <div class="col-md-2 col-12 mb-md-0 mb-4">
                                                        <label class="h6 repeater-title">Memo</label>
                                                        <span id="updated_memo_number_timekeep_{{$timekeeps_data->id}}" class="updated_memo_number_timekeep_{{$timekeeps_data->id}}">{{ $timekeeps_data->memo_number ?? '' }}</span>
                                                        <span></span>
                                                    </div>
                                                    <div class="col-12 w-100 mb-md-0 mb-4">
                                                        <textarea class="description-3 form-control" name="timekeep_description[]" rows="5" placeholder="Description">{{ $timekeeps_data->description ?? '' }}</textarea>
                                                    </div>
                                                </div>
                                                @if($timekeeps_data->memo_number || $timekeeps_data->memo_number != 0)
                                                    <div class="d-flex flex-column align-items-center justify-content-between border-start p-2">
                                                        <i class="ti ti-x ti-lg cursor-pointer delete_icon"></i>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                    @if(count($timekeeps) == 0)
                                        <div class="timekeep_section pt-0 pt-md-9 mt-4">
                                            <div class="d-flex border rounded position-relative pe-0">
                                                <div class="row w-100 p-6">
                                                <div class="col-md-3 col-12 mb-md-0 mb-4">
                                                <input type="hidden" id="timekeep_id" class="timekeep_id" name="timekeep_id[]" value="">
                                                    <label class="h6 repeater-title">Person</label>
                                                    <select class="select2 form-select person" name="person[]">
                                                        <option value="" disabled selected>Please Select</option>
                                                            @foreach ($user as $userId => $userName)
                                                                    <option value="{{ $userId }}">
                                                                        {{ $userName }}</option>
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
                                        </div>
                                    @endif
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <button type="button" id="add-timekeep" class="btn btn-sm btn-primary add-timekeep">
                                    <i class="ti ti-plus ti-14px me-1_5"></i>Add Amount
                                </button>
                            </div>
                            @if(count($timekeeps) != 0)
                                <div class="col-6 mb-3 text-end">
                                    <button type="button" id="add_memo_timekeep" data-bs-toggle="modal" data-bs-target="#shareModal_timekeep" class="btn btn-sm btn-primary add_memo_timekeep" data-repeater-create><i class="ti ti-plus ti-14px me-1_5"></i>Add Memo</button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>


        </form>
    </div>
    <!-- this is for Task memo -->
        <div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="shareModalLabel">Add Memo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body add_another_user">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="memo_number" class="form-label">Memo Number</label>
                                <input type="text" class="memo_number form-control invoice-item-price"
                                placeholder="0" name="memo_number" id="memo_number" />
                                <small class="text-danger" id="memo_number_error" style="display:none;"></small>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"  id="add_memo_task">Save</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- this is for timekeep memo -->
        <div class="modal fade" id="shareModal_timekeep" tabindex="-1" aria-labelledby="shareModalLabel_timekeep" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="shareModalLabel_timekeep">Add Memo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="memo_number_timekeep" class="form-label">Memo Number</label>
                                <input type="text" class="memo_number_timekeep form-control invoice-item-price"
                                placeholder="0" name="memo_number_timekeep" id="memo_number_timekeep" />
                                <small class="text-danger" id="memo_number_timekeep_error" style="display:none;"></small>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"  id="add_memo_timkeep">Save</button>
                    </div>
                </div>
            </div>
        </div>
        {{-- START :  Modal for share  --}}
         <!-- Modal -->
        <div class="modal fade" id="shareModal_assignment" tabindex="-1" aria-labelledby="shareModal_assignmentLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="shareModal_assignmentLabel">Share Assignment with Users</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body add_another_user">
                        <div class="row">
                                @if(count($assignment_users) > 0)
                                    @foreach ($assignment_users as $assignment_users_data)
                                        <div class="col-md-4">
                                            <label for="dropdown1" class="form-label">User</label>
                                            <select class="select2 form-select user" id="userDropdown_{{ $loop->index }}" name="user[]">
                                                <option value="" disabled>Please Select</option>
                                                @foreach ($users_data_assignment as $users_data_assignments)
                                                    <option value="{{ $users_data_assignments->id }}"
                                                        {{ $assignment_users_data->user_id == $users_data_assignments->id ? 'selected' : '' }}>
                                                        {{ $users_data_assignments->name }}
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
                                                        {{ $assignment_users_data->department_id == $department->id ? 'selected' : '' }}>
                                                        {{ $department->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="dropdown1" class="form-label">Access</label>
                                            <select class="select2 form-select user_access" name="user_access[]">
                                                <option value="" disabled selected>Please Select</option>
                                                <option value="read" {{ $assignment_users_data->access_level == 'read' ? 'selected' : '' }}>Read Only</option>
                                                <option value="edit" {{ $assignment_users_data->access_level == 'edit' ? 'selected' : '' }}>Edit</option>
                                            </select>
                                        </div>
                                    @endforeach
                                @else
                                    <!-- For when quote_users is empty -->
                                    <div class="col-md-4">
                                        <label for="dropdown1" class="form-label">User</label>
                                        <select class="select2 form-select user" id="userDropdown" name="user[]">
                                            <option value="" disabled selected>Please Select</option>
                                                @foreach ($users_data_assignment as $users_data_assignments)
                                                        <option value="{{ $users_data_assignments->id }}">
                                                            {{ $users_data_assignments->name }}
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
