@php
    $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts.horizontalLayout')

@section('title', 'View - Quotation')

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
@endsection
@section('content')
<script>
$(document).ready(function() {
    // Event listener for user dropdown change
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
        var quote_id =  $('.quote_id').val(); 
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
                       location.reload();
                     
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


    
});

</script>
    <div class="row invoice-add">
        <!-- Invoice Add-->
    <div>
            <div id="success-message"></div>
        </div>
        <div class="col-lg-12 col-12 mb-lg-0 mb-6">
                <div class="d-md-flex justify-content-between mb-2 align-items-center">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <p class="mb-0 text-primary me-3">
                            Previously generated PDFs (Click to download)
                        </p>
                        <div class="d-flex">
                            <button type="submit" class="mx-2 btn btn-xs btn-primary waves-effect waves-light d-grid">PDF</button>
                            <a type="reset" class="mx-2 btn btn-xs btn-outline-primary waves-effect d-grid"
                                href="#">Share</a>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="mx-2 btn btn-primary waves-effect waves-light d-grid">PDF</button>
                        <a type="reset" class=" mx-2 btn btn-outline-primary waves-effect d-grid"
                            href="#" data-bs-toggle="modal" data-bs-target="#shareModal">Share</a>
                            <a type="reset" class=" mx-2 btn btn-outline-primary waves-effect d-grid"
                            href="{{ route('quotes.edit', $quote->id) }}">Edit</a>
                    </div>
                </div>
                <div class="card invoice-preview-card p-sm-3 p-md-3 p-lg-3">
                    <input type="hidden" value="{{$quote->id}}" class="quote_id">
                    <div class="card-header p-lg-0 p-md-0 p-sm-0 d-flex justify-content-between align-items-center pb-0 pb-sm-3">
                        <h4 class="mb-0">View Quote</h4>
                        <div>
                            <span class="badge rounded-pill bg-label-primary"> {{$quote->status ?? ''}}</span>
                        </div>
                    </div>
                    <div class="p-3 rounded bg-light my-3 mx-3 mx-sm-0">
                        <div class="row">
                            <div class="col-12 col-sm-4 col-md-3 d-flex justify-content-between align-items-center">
                                <p class="text-muted">
                                    Expiry On
                                </p>
                                <p class="text-dark">
                                    {{$formattedDate ?? ''}}
                                </p>
                            </div>
                            <div class="col-1 d-none d-md-block">

                            </div>
                            <div class="col-12 col-sm-4 col-md-3 d-flex justify-content-between  align-items-center">
                            <p class="text-muted">
                                    Reference
                                </p>
                                <p class="text-dark">
                                {{$quote->reference ?? ''}}
                                </p>
                            </div>
                            <div class="col-1 d-none d-md-block">

                            </div>
                            <div class="col-12 col-sm-4 col-md-3 d-flex justify-content-between align-items-center">
                                <p class="text-muted">
                                    Client
                                </p>
                                <p class="text-dark">
                                {{$client->client ?? ''}}
                                </p>
                            </div>
                        </div>
                        <div class="d-sm-grid d-flex justify-content-between row">
                            <p class="text-muted col-6 col-sm-12">
                                Description
                            </p>
                            <p class="text-dark text-wrap text-end text-sm-start col-6 col-sm-12">
                            {{$quote->description ?? ''}}
                            </p>
                        </div>
                    </div>
                    <div class="card-body pt-0 px-0">
                        <div class="mb-4 group-a">
                            <div class="card-header p-lg-0 p-md-0 p-sm-0 pb-0 pb-sm-3">
                                <h4 class="mb-0">Tasks</h4>
                            </div>
                            @foreach ($quote_task as $task)
                                <div class="repeater-wrapper pt-0 pt-md-9 mt-4 px-3 px-sm-0">
                                    <div class="d-flex border rounded position-relative pe-0">
                                        <div class="row w-100 p-6">
                                            <div class="col-md-4 col-12 mb-md-0 mb-4">
                                                <p class="h6 repeater-title">Amount</p>
                                                <input type="text" class="amount form-control invoice-item-price mb-5"
                                                    placeholder="Amount" name="amount" value="{{ $task->amount }}"  readonly/>
                                            </div>
                                            <div class="col-md-4 col-12 mb-md-0 mb-4">
                                                <label class="h6 repeater-title" for="currency">Currency</label>
                                                <input type="text" class="amount form-control invoice-item-price mb-5"
                                                    placeholder="Currency" value="{{ $task->currency_name ?? '' }}" readonly />
                                            </div>
                                            <div class="department-container col-md-4 col-12 mb-md-0 mb-4">
                                                <label class="h6 repeater-title">Department</label>
                                                <select class="form-select" disabled>
                                                    <option value="" disabled>Please Select</option>
                                                    @foreach ($department as $departmentId => $departmentName)
                                                        <option value="{{ $task->department_id }}"
                                                            {{ $departmentId == $task->department_id ? 'selected' : '' }}>
                                                            {{ $departmentName }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 w-100 mb-md-0 mb-4">
                                                <textarea class="description-2 form-control" name="description" rows="5" placeholder="Description" readonly>{{ $task->description }}</textarea>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
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
