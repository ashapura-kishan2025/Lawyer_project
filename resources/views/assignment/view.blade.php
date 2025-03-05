@php
    $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts.horizontalLayout')

@section('title', 'View - Assignment')

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
        var assignment_id =  $('.assignment_id').val(); 
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
                    var errorMessage = 'Please select all fields (User, Department, and Access Level).';
                    $('#shareModal').find('.modal-body').prepend('<div class="alert alert-danger">' + errorMessage + '</div>');
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
                <div class="d-flex justify-content-between mb-2 align-items-center">
                    <div class="d-flex align-items-center">
                        <p class="mb-0 text-primary me-3">
                            Previously generated PDFs (Click to download)
                        </p>
                        <button type="submit" class="mx-2 btn btn-xs btn-primary waves-effect waves-light d-grid ">PDF</button>
                        <a type="reset" class=" mx-2 btn btn-xs btn-outline-primary waves-effect d-grid "
                            href="#">Share</a>
                    </div>
                    <div class="d-flex justify-content-around">
                        <button type="submit" class="mx-2 btn btn-primary waves-effect waves-light d-grid w-50">PDF</button>
                        <a type="reset" class="mx-2 btn btn-outline-primary waves-effect d-grid w-50"
                            href="#"  data-bs-toggle="modal" data-bs-target="#shareModal">Share</a>
                            <a type="reset" class=" mx-2 btn btn-outline-primary waves-effect d-grid w-50"
                            href="{{ route('assignment.edit', $assignment->id) }}">Edit</a>
                    </div>
                </div>
                <div class="card invoice-preview-card p-sm-12 p-md-6 p-lg-6">
                <input type="hidden" value="{{$assignment->id}}" class="assignment_id">
                    <div class="card-header p-lg-0 p-md-0 p-sm-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">View Assignment</h4>
                    </div>
                    <div class="p-3 rounded bg-light my-3">
                    <div class="row">
                    <div class="col-3 d-flex justify-content-between align-items-center">
                                <p class="text-muted">
                                    Assignment Type
                                </p>
                                <p class="text-dark">
                                    {{$assignment->assignment_type ?? ''}}
                                </p>
                            </div>
                            <div class="col-1">

                            </div>
                            <div class="col-3 d-flex justify-content-between align-items-center">
                                <p class="text-muted">
                                    Status
                                </p>
                                <p class="text-dark">
                                {{$assignment->status ?? ''}}
                                </p>
                            </div>
                            <div class="col-1">

                            </div>
                            <div class="col-3 d-flex justify-content-between  align-items-center">
                            <p class="text-muted">
                                    Quote
                                </p>
                                <p class="text-dark">
                                {{$assignment->quote_id ?? ''}}
                                </p>
                            </div>
                            <div class="col-1">

                            </div>
                            <div class="col-3 d-flex justify-content-between align-items-center">
                                <p class="text-muted">
                                   Ledger
                                </p>
                                <p class="text-dark">
                                {{$assignment->ledger ?? ''}}
                                </p>
                            </div>
                            <div class="col-1">

                            </div>
                            <div class="col-3 d-flex justify-content-between align-items-center">
                                <p class="text-muted">
                                    Client
                                </p>
                                <p class="text-dark">
                                {{$client->client ?? ''}}
                                </p>
                            </div>
                    </div>        
                        <p class="text-muted">
                            Description
                        </p>
                        <p class="text-dark">
                        {{$assignment->description ?? ''}}
                        </p>
                    </div>
                    <div class="row">
                            <div class="col-6 d-flex flex-wrap border-end">
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
                    
                    <div class="card-body pt-0 px-0">

                        <div class="mb-4 group-a">
                        <div class="card-header p-lg-0 p-md-0 p-sm-0">
                            <h4>Tasks</h4>
                        </div>
                            @if($assignment_task)
                                @foreach ($assignment_task as $task)
                                <div class="pt-0 pt-md-9 task_section mt-4">
                                        <div class="d-flex border rounded position-relative pe-0">
                                            <div class="row w-100 p-6">
                                                <div class="col-md-3 col-12 mb-md-0 mb-4">
                                                    <p class="h6 repeater-title">Amount</p>
                                                    <input type="text" class="amount form-control invoice-item-price mb-5" placeholder="Amount" name="amount[]" value="{{ $task->amount ?? '' }}" readonly/>
                                                </div>
                                                <div class="col-md-3 col-12 mb-md-0 mb-4">
                                                    <label class="h6 repeater-title" for="received_amount">Received</label>
                                                    <input type="text" class="received_amount form-control invoice-item-price mb-5" placeholder="0" name="received_amount" id="received_amount" value="{{ $task->received_amount ?? '' }}" readonly/>
                                                </div>
                                                
                                                <div class="col-md-3 col-12 mb-md-0 mb-4">
                                                    <label class="h6 repeater-title">Department</label>
                                                    <select class="select2 form-select department" name="department[]" value="one_user" disabled>
                                                        <option value="" disabled selected>Please Select</option>
                                                        @foreach ($departments as $departmentId => $departmentName)
                                                                <option value={{ $task->department_id }}
                                                                    {{ $departmentId == $task->department_id ? 'selected' : '' }}>
                                                                    {{ $departmentName }}</option>
                                                            @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3 col-12 mb-md-0 mb-4">
                                                    <label class="h6 repeater-title">Memo</label>
                                                    -
                                                </div>
                                                <div class="col-12 w-100 mb-md-0 mb-4">
                                                    <textarea class="description-2 form-control" name="task_description[]" rows="5" placeholder="Description" readonly>{{ $task->description ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>    
                                @endforeach
                            @endif    
                            @if($timekeeps)
                                @foreach ($timekeeps as $timekeeps_data)
                                            <div class="timekeep_section pt-0 pt-md-9 mt-4">
                                                <div class="d-flex border rounded position-relative pe-0">
                                                    <div class="row w-100 p-6">
                                                        <div class="col-md-2 col-12 mb-md-0 mb-4">
                                                            <p class="h6 repeater-title">Person</p>
                                                           
                                                            <select class="select2 form-select person" disabled>
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
                                                            <input type="text" class="timekeep_qty form-control invoice-item-price mb-5" placeholder="0" name="quantity[]" value="{{ $timekeeps_data->quantity ?? '' }}"  readonly/>
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
                                                            <span>{{ $timekeeps_data->memo_number ?? '' }}</span>
                                                        </div>
                                                        <div class="col-12 w-100 mb-md-0 mb-4">
                                                            <textarea class="description-3 form-control" name="timekeep_description[]" rows="5" placeholder="Description" readonly>{{ $timekeeps_data->description ?? '' }}</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>    
                                @endforeach
                            @endif
                        </div>
        </div>
        {{-- START :  Modal for share  --}}
         <!-- Modal -->
        <div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="shareModalLabel">Share Assignment with Users</h5>
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
