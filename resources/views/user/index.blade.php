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
    <script src="https://cdn.jsdelivr.net/npm/validate.js@0.13.1/validate.min.js"></script>

    <script>
        $(document).ready(function() {
            let isDepartmentValid = false;

            getDepartments(selectedDepartments = []);
            getRoles();
            getDepartmentsfilter();

            //this is for update and add
            function getDepartments(selectedDepartments = []) {
                $.ajax({
                    url: "{{ route('user.getDepartmentData') }}", // Route to fetch departments
                    method: 'GET',
                    success: function(departments) {
                        var departmentSelect = $('#department');
                        departmentSelect.empty(); // Clear any existing options
                        departmentSelect.append(
                            '<option value="">Select Department</option>'); // Default option

                        // Loop through the department list and append options
                        $.each(departments, function(index, department) {
                            // Check if the department is in the selectedDepartments array
                            var selected = selectedDepartments.includes(department.id) ?
                                'selected' : '';
                            departmentSelect.append('<option value="' + department.id + '" ' +
                                selected + '>' + department.name + '</option>');
                        });

                        // If using Select2, trigger the change event to reinitialize it
                        departmentSelect.trigger('change');
                    }
                });
            }
            //this is for filter
            function getDepartmentsfilter() {
                $.ajax({
                    url: "{{ route('user.getDepartmentData') }}", // Ensure this route is working
                    method: 'GET',
                    success: function(departments) {
                        var departmentSelect = $('#departmentSearch');
                        departmentSelect.empty(); // Clear existing options
                        departmentSelect.append(
                            '<option value="">Select Department</option>'); // Default option

                        // Loop through and append departments
                        $.each(departments, function(index, department) {
                            departmentSelect.append('<option value="' + department.id + '">' +
                                department.name + '</option>');
                        });

                        // Call DataTable draw once departments are loaded
                        $('.datatables-user').DataTable().draw();
                    }
                });
            }



            // START : Datatable Handle for client
            $('.datatables-user').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('user.index') }}',
                    data: function(d) {
                        // Log values to check if department filter is captured correctly
                        console.log('Department ID:', $('#departmentSearch').val());

                        // Adding custom filters to the DataTable ajax request
                        d.name_email = $('#filterNameEmail').val();
                        d.status = $('#filterStatus').val();
                        d.last_login_at = $('#filterDate').val();
                        d.department = $('#departmentSearch').val(); // Get department value
                    }
                },
                columns: [{
                        data: 'name',
                        name: 'name',
                        title: 'Name'
                    },
                    {
                        data: 'email',
                        name: 'email',
                        title: 'Email'
                    },
                    {
                        data: 'departments',
                        name: 'departments',
                        title: 'Departments'
                    },
                    {
                        data: 'role',
                        name: 'role',
                        title: 'Role'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        title: 'Status'
                    },
                    {
                        data: 'last_login_at',
                        name: 'last_login_at',
                        title: 'Last Login'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        title: 'Action'
                    }
                ],
                order: [
                    [0, 'asc']
                ],
                rowId: 'id',
                initComplete: function() {
                    // Apply filters and redraw the DataTable
                    $('#filterNameEmail, #filterStatus, #filterDate').on('change keyup', function() {
                        $('.datatables-user').DataTable().draw();
                    });

                    // Reset filters
                    $('#resetFilters').on('click', function() {
                        $('#filterNameEmail').val('');
                        $('#filterStatus').val('');
                        $('#filterDate').val('');
                        $('#departmentSearch').val('').trigger(
                            'change'); // Reset department filter

                        // Redraw the DataTable
                        $('.datatables-user').DataTable().draw();
                    });

                    // Ensure department filter is working
                    $('#departmentSearch').on('change', function() {
                        console.log('Department Filter Changed:', $('#departmentSearch').val());
                        $('.datatables-user').DataTable()
                            .draw(); // Redraw the table when department changes
                    });
                }
            });



            // END : Datatable Handle for client

            // Reset form on Add New client button click
            // Form submission handler
            $(document).ready(function() {
                function validateEmailUniqueness(email, callback) {
                    $.ajax({
                        url: '{{ route('user.checkEmail') }}', // Define a route in your Laravel backend to check if the email exists
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            email: email
                        },
                        success: function(response) {
                            if (response.exists) {
                                callback(false); // Email already exists
                            } else {
                                callback(true); // Email does not exist
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Email check failed: ', error);
                            callback(true); // If there's an error, assume email is valid
                        }
                    });
                }
                $('#addNewUser').on('click', function() {
                    $('#userForm')[0].reset();
                    $('.offcanvas-title').text('Add User');
                });


                function clearValidationErrors() {
                    $('.is-invalid').removeClass('is-invalid');
                    $('.department-feedback, .role-feedback, #name-feedback, #rate-per-hours-feedback,#email-feedback').text('').css('display', 'none');
                }
                function validateDepartment(department = null) {
                    let isValid = true;
                    const fields = department || $('.departments').map(function() {
                        return $(this).val();
                    }).get();

                    $.each(fields, function(index, value) {
                        const departmentField = $('.departments').eq(index);
                        const feedback = departmentField.closest('.department-field').find('.department-feedback');

                        if (!value || value === '') {
                            departmentField.addClass('is-invalid');
                            feedback.text('Please select a department').css('display', 'block').css('color', 'red');
                            isValid = false;
                        } else {
                            departmentField.removeClass('is-invalid');
                            feedback.text('').css('display', 'none');
                        }
                    });

                    return isValid;
                }

                // Function to validate role fields only when needed
                function validateRole(role = null) {
                    let isValid = true;
                    const fields = role || $('.role').map(function() {
                        return $(this).val();
                    }).get();

                    $.each(fields, function(index, value) {
                        const roleField = $('.role').eq(index);
                        const feedback = roleField.closest('.role-field').find('.role-feedback');

                        if (!value || value === '') {
                            roleField.addClass('is-invalid');
                            feedback.text('Please select a role').css('display', 'block').css('color', 'red');
                            isValid = false;
                        } else {
                            roleField.removeClass('is-invalid');
                            feedback.text('').css('display', 'none');
                        }
                    });

                    return isValid;
                }

                // Example of field validation (for name, email, etc.)
                function validateField(fieldId, feedbackId, errorMessage) {
                    const value = $(fieldId).val();

                    if (!value || value === "") {
                        $(fieldId).addClass('is-invalid');
                        $(feedbackId).text(errorMessage).css('display', 'block').css('color', 'red').css('font-size', '0.875em');
                        return false;
                    } else {
                        $(fieldId).removeClass('is-invalid');
                        $(feedbackId).text('').css('display', 'none');
                        return true;
                    }
                }


                    $('#name').on('blur', function() {
                        validateField('#name', '#name-feedback', 'Please Fill the name');
                    });
                    $('#email').on('blur', function() {
                        validateField('#email', '#email-feedback', 'Please Fill the email address');
                    });
                    $('#rate-per-hours').on('blur', function() {
                        validateField('#rate-per-hours', '#rate-per-hours-feedback',
                            'Please Fill the rate per hours');
                    });
                    $('#cancel_form').click(function() {
                        $('#userForm')[0].reset(); // Assuming the form has an id of 'userForm'
                        $('.is-invalid').removeClass('is-invalid'); // Remove any invalid class
                        $('#role').prop('selectedIndex', 0);
                        $('#department').prop('selectedIndex', 0);
                        $('.invalid-feedback').empty(); // Clear any error messages
                        $('#error-container').removeClass('alert alert-danger p-1')
                        $('#error-container').html('');
                    });
                    $(document).on('change', '.departments', function() {
                        const departmentField = $(this);
                        const feedback = departmentField.closest('.department-field').find('.department-feedback');
                        departmentField.removeClass('is-invalid');
                        feedback.text('').css('display', 'none');
                    });

                    $(document).on('change', '.role', function() {
                        const roleField = $(this);
                        const feedback = roleField.closest('.role-field').find('.role-feedback');
                        roleField.removeClass('is-invalid');
                        feedback.text('').css('display', 'none');
                    });
                    //when click on cancel
                    $('#addNewUser').click(function() {
                        $('#userForm')[0].reset();
                        $('.role').prop('selectedIndex', 0);
                        $('.department').prop('selectedIndex', 0);
                        $('.is-invalid').removeClass('is-invalid'); // Remove any invalid class
                        $('.invalid-feedback').empty(); // Clear any error messages
                        $('#error-container').removeClass('alert alert-danger p-1')
                        $('#error-container').html('');
                        clearValidationErrors();
                    });

                    // Form submission handler
                    $('#userForm').on('submit', function(e) {
                        e.preventDefault();
                        const clientIdValid = $('#id').val();
                        const name = validateField('#name', '#name-feedback', 'Please Fill the name');
                        const email = validateField('#email', '#email-feedback',
                            'Please Fill the email address');

                        const status = $('#status').val();
                        const rate_per_hours = validateField('#rate-per-hours',
                            '#rate-per-hours-feedback',
                            'Please Fill the rate per hours');
                            var department = $('.departments').map(function() {
                                return $(this).val(); // Get the value of each input
                            }).get();
                            var role = $('.role').map(function() {
                                return $(this).val(); // Get the value of each input
                            }).get();

                            let departmentValid = validateDepartment(department);
                            let roleValid = validateRole(role);

                            // If any of the validations fail, prevent form submission
                            if (!name || !email || !rate_per_hours || !departmentValid || !roleValid) {
                                return false;
                            }

                        clientId = $("#id").val();
                        console.log(clientId, 'clientId');
                        var formData = {
                            id: $("#id").val(),
                            department: department,
                            // Get selected radio button value
                            name: $("#name").val(),
                            email: $("#email").val(),
                            status: $("#status").val(),
                            rate_per_hours: $("#rate-per-hours").val(),
                            website_url: $("#website-url").val(),
                            role:role,
                        };

                        const ajaxUrl = clientId ?
                            "{{ route('user.update', ':id') }}".replace(':id', clientId) :
                            "{{ route('user.store') }}";

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
                                $('.btn.btn-label-danger[data-bs-dismiss="offcanvas"]')
                                    .click();
                                $('#ajaxSuccess').addClass('alert alert-success mt-2').text(
                                    response.message);
                                // Set a timeout to remove the message after 4 seconds
                                setTimeout(function() {
                                    $('#ajaxSuccess').removeClass(
                                        'alert alert-success mt-2').text('');
                                }, 4000); // 4000 milliseconds = 4 seconds

                                $('.datatables-user').DataTable().ajax.reload();
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
                                            errorList +=
                                                `<li>${message}</li>`;
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
                });

                function getRoles() {
                    return new Promise(function(resolve, reject) {
                        $.ajax({
                            url: "{{ route('user.getRole') }}", // Route to fetch roles
                            method: 'GET',
                            success: function(roles) {
                                var roleSelect = $('#role');
                                roleSelect.empty(); // Clear any existing options
                                roleSelect.append(
                                    '<option value="">Select Role</option>'); // Default option

                                // Loop through the roles and append options to the select dropdown
                                $.each(roles, function(index, role) {
                                    roleSelect.append('<option value="' + role.id + '">' +
                                        role.name + '</option>');
                                });

                                resolve(roles); // Resolve the promise with roles
                            },
                            error: function(xhr, status, error) {
                                reject("Error fetching roles: " +
                                    error); // Reject the promise if there's an error
                            }
                        });
                    });
                }

                //

                // Edit client after currency dropdown is loaded
                $(document).on('click', '.edit-user-btn', function() {
                    const clientId = $(this).data('id');
                    $('.offcanvas-title').text('Edit User');

                    // Reset the form and validation messages when the form is opened again
                    $('#userForm')[0].reset(); // Assuming the form has an id of 'userForm'
                    $('.is-invalid').removeClass('is-invalid'); // Remove any invalid class
                    $('.invalid-feedback').empty(); // Clear any error messages
                    $('#error-container').removeClass('alert alert-danger p-1')
                    $('#error-container').html('');
                    if (clientId) {
                        // getRoles().then(function() {
                        // Proceed with the edit call
                        $.ajax({
                            url: "{{ route('user.edit', ':id') }}".replace(':id',
                                clientId),
                            type: 'GET',
                            success: function(response) {
                                if (response.success) {
                                    console.log(response, 'ascd');
                                    const data = response.data;
                                    $('#id').val(data.id);
                                    $('#name').val(data.name);
                                    $('#email').val(data.email);
                                    $('#rate-per-hours').val(data.rate);
                                    $('#status').val(data.status).trigger('change');
                                    $('#website-url').val(data.linkedin_url);
                                    // $('#role').val(data.role_id).trigger('change');

                                    // Populate departments and roles
                                    getDepartmentsAndRoles(data.departments, data.departments_all, data.roles);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("An error occurred:", error);
                            }
                        });
                        // });
                    }
                });

                //START : Delete functionality
                $(document).on('click', '.delete-user-btn', function() {
                    const userId = $(this).data('id');
                    console.log(userId, 'asdacsd');
                    if (userId) {
                        $('.modal-body').html();
                        $('.modal-body').text(" Are you sure you want to delete this user ?");
                        $('#modalToggle').data('user-id', userId).modal('show');
                    }
                });


                $(document).on('click', '#modalToggle #confirmed', function() {
                    const userId = $('#modalToggle').data('user-id');
                    let status = $('#modalToggle').data('status');

                    if (userId && status) {
                        if (status == "active") {
                            status = "inactive";
                        } else {
                            status = 'active';
                        }

                        // Create an ajax request for update the status
                        $.ajax({
                            url: "{{ route('user.status') }}",
                            type: "POST",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                id: userId,
                                status: status
                            },
                            success: function(response) {
                                $('.datatables-user').DataTable().ajax.reload();
                                $('#ajaxSuccess').addClass('mt-2 alert alert-success').text(response
                                    .message);

                                // Hide the alert after 4 seconds (4000 milliseconds)
                                setTimeout(function() {
                                    $('#ajaxSuccess').fadeOut('slow', function() {
                                        // Optionally, you can remove the class and reset the message
                                        $(this).removeClass(
                                            'mt-2 alert alert-success').text('');
                                    });
                                }, 4000); // 4000 milliseconds = 4 seconds

                            }
                        });
                    } else {
                        $.ajax({
                            url: "{{ route('user.destroy', ':id') }}".replace(':id', userId),
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                $('#ajaxSuccess').addClass('mt-2 alert alert-danger').text(response
                                    .message);

                                // Hide the alert after 4 seconds (4000 milliseconds)
                                setTimeout(function() {
                                    $('#ajaxSuccess').fadeOut('slow', function() {
                                        // Optionally, you can remove the class and reset the message
                                        $(this).removeClass(
                                            'mt-2 alert alert-danger').text('');
                                    });
                                }, 4000); // 4000 milliseconds = 4 seconds

                                $('.datatables-user').DataTable().ajax.reload();
                            }
                        });
                    }
                });
                window.userClickStatusChange = function(id, status) {
                    $('.modal-body').html();
                    $('.modal-body').text(" Are you sure you want to Change the status?");
                    $('#modalToggle').data('user-id', id).data('status', status).modal('show');
                }
                $('#offcanvasAddUser').on('hidden.bs.offcanvas', function() {
                    // Reset form and clear validation errors when offcanvas is closed
                    $('#userForm')[0].reset(); // Reset form fields
                    $('.is-invalid').removeClass('is-invalid'); // Remove any invalid styles
                    $('.invalid-feedback').empty();
                    $('#error-container').removeClass('alert alert-danger p-1')
                    $('#error-container').html('');
                });
                    var sectionCounter = 0; // Initialize the section counter to manage IDs of dynamic fields

                    // Function to remove a specific section based on the unique counter
                    $(document).on('click', '.task_delete_icon', function() {
                        var index = $(this).data('index'); // Get the index of the section to remove

                        // Function to remove a specific section based on the unique index
                        function removeSection(index) {
                            // Remove the department field, role field, and the delete icon for that section
                            $('#department-field-' + index).remove();  // Remove the department field
                            $('#role-field-' + index).remove();  // Remove the role field
                            $('.task_delete_icon[data-index="' + index + '"]').remove();  // Remove the corresponding delete icon
                        }

                        // Call the function to remove the fields and icon
                        removeSection(index);
                    });

                    // Event listener for the "Add More" button
                    $('#add-more-department').on('click', function() {
                        sectionCounter++; // Increment counter for new section

                        // Create the new department and role fields with unique IDs
                        var newItem = `
                            <div class="d-flex justify-content-between align-items-center mb-3" id="department-role-section-${sectionCounter}">
                                <div class="mb-6 department-field" id="department-field-${sectionCounter}" style="flex: 1; margin-right: 10px;">
                                    <label class="form-label" for="department-${sectionCounter}">Department</label>
                                    <select id="department-${sectionCounter}" class="select2 form-select departments" name="departments[]">
                                        <option value="">Select Department</option>
                                        @foreach ($department as $departmentData)
                                            <option value="{{ $departmentData['id'] }}">
                                                {{ $departmentData['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <div id="department-feedback-${sectionCounter}" class="department-feedback"></div>
                                </div>
                                <div class="mb-6 role-field" id="role-field-${sectionCounter}" style="flex: 1;">
                                    <label class="form-label" for="role-${sectionCounter}">Role</label>
                                    <select id="role-${sectionCounter}" name="role[]" class="select2 form-select role">
                                        <option value="">Select Role</option>
                                        @foreach ($role as $role_data)
                                            <option value="{{ $role_data['id'] }}">
                                                {{ $role_data['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <div id="role-feedback-${sectionCounter}" class="role-feedback"></div>
                                </div>
                            </div>
                            <i class="ti ti-x ti-lg cursor-pointer task_delete_icon ms-2" data-index="${sectionCounter}"></i>
                        `;
                        // Append the new item to the task_section container
                        $('.task_section').append(newItem);

                        // Initialize select2 on the new selects
                        // $('#department-' + sectionCounter).select2();
                        // $('#role-' + sectionCounter).select2();
                    });


                // Event delegation for removing a section (using data-index to find the section)
                // $(document).on('click', '.task_delete_icon', function() {
                //     var index = $(this).data('index'); // Get the index of the section to remove
                //     removeSection(index); // Call the function to remove the fields and icon
                // });
                function getDepartmentsAndRoles(departments, allDepartments, allRoles) {
                    var container = $('#dynamic-fields-container');
                    container.empty(); // Clear any existing fields

                    // Loop through the departments (user's departments) and create dynamic fields
                    $.each(departments, function(index, department) {
                        // Check if the current section is the first one
                        var isFirstSection = index === 0;

                        // Create a new department field
                        var departmentField = `
                            <div class="mb-6 department-field" id="department-field-${index}">
                                <label class="form-label" for="department-${index}">Department</label>
                                <select id="department-${index}" class="select2 form-select departments" name="departments[]">
                                    <option value="">Select Department</option>
                                    <!-- Dynamically populate all departments -->
                                    ${allDepartments.map(function(departmentData) {
                                        return `
                                            <option value="${departmentData.id}" ${department.department_id === departmentData.id ? 'selected' : ''}>
                                                ${departmentData.name}
                                            </option>
                                        `;
                                    }).join('')}
                                </select>
                                <div id="department-feedback-${index}" class="department-feedback"></div>
                                ${isFirstSection ? '' : '<i class="ti ti-x ti-lg cursor-pointer task_delete_icon" data-index="' + index + '"></i>'} <!-- No cancel icon for the first section -->
                            </div>
                        `;

                        // Create a new role field
                        var roleField = `
                            <div class="mb-6 role-field" id="role-field-${index}">
                                <label class="form-label" for="role-${index}">Role</label>
                                <select id="role-${index}" name="role[]" class="select2 form-select role">
                                    <option value="">Select Role</option>
                                    <!-- Dynamically populate all roles -->
                                    ${allRoles.map(function(roleData) {
                                        return `
                                            <option value="${roleData.id}" ${department.role_id === roleData.id ? 'selected' : ''}>
                                                ${roleData.name}
                                            </option>
                                        `;
                                    }).join('')}
                                </select>
                                <div id="role-feedback-${index}"></div>
                            </div>
                        `;

                        // Append both department and role fields to the container
                        container.append(departmentField + roleField);
                    });
                    // Reinitialize Select2 (if using Select2)
                    $('.select2').trigger('change');
                }


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

    {{-- <div class="z-3 toast align-items-center text-bg-success border-0 ms-auto" role="alert" id="successToast">
        <div class="d-flex">
            <div class="toast-body"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
        </div>
    </div> --}}

    <div class="card">
        <div class="card-header border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <p class="fs-5">User</p>
                <button class="btn btn-success mb-4" id="addNewUser" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasAddUser">
                    <i class="ti ti-plus me-0 me-sm-1 ti-xs"></i>
                    Add User
                </button>
            </div>
            <div class="row pt-4 gap-4 gap-md-0">
                <div class="filter-container">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <input type="text" id="filterNameEmail" class="form-control" placeholder="Search Name,Email">
                        </div>
                        <div class="col-md-3 mb-2">
                            <select id="departmentSearch" class="form-select">
                                <!-- Add other departments here -->
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <select id="filterStatus" class="form-control">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <input type="date" id="filterDate" class="form-control">
                        </div>
                        <div class="col-md-3 mb-2">
                            <button id="resetFilters" class="btn btn-primary">Reset Filters</button>
                        </div>
                    </div>
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
            <table class="datatables-user table">
                <thead class="border-top">
                </thead>
            </table>
        </div>
    </div>


    <!-- START : Offcanvas to add new user -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddUser" aria-labelledby="offcanvasAddUserLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasAddUserLabel" class="offcanvas-title">Add User</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
            <div id="error-container"></div>
            <form class="add-new-user pt-0" id="userForm">
                <input type="hidden" name="id" id="id">
                <div class="mb-6">
                    <label class="form-label" for="name">Name</label>
                    <input type="text" class="form-control" id="name" placeholder="Name" name="name"
                        aria-label="Name" />
                    <div id="name-feedback">
                    </div>
                </div>
                <div class="mb-6">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" class="form-control" id="email" placeholder="Email" name="email"
                        aria-label="Email" />
                    <div id="email-feedback">
                    </div>
                </div>



                <div id="dynamic-fields-container" class="task_section">
                    <div class="d-flex justify-content-between">
                        <!-- Static Department Field -->
                        <div class="mb-6 department-field flex-fill me-2">
                            <label class="form-label" for="department">Department</label>
                            <select id="department" class="select2 form-select departments" name="departments[]">
                                <option value="">Select Department</option>
                                <!-- Add other departments here -->
                            </select>
                            <div id="department-feedback" class="department-feedback"></div>
                        </div>

                        <!-- Static Role Field -->
                        <div class="mb-6 role-field flex-fill ms-2">
                            <label class="form-label" for="role">Role</label>
                            <select id="role" name="role[]" class="select2 form-select role">
                                <option value="">Select Role</option>
                                <!-- Add other roles here -->
                            </select>
                            <div id="role-feedback" class="role-feedback"></div>
                        </div>
                    </div>
                </div>
                <!-- Button to add more -->
                <button type="button" id="add-more-department" class="btn btn-primary">Add More</button>


                <!-- Button to add more -->


                <div class="mb-6">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="select2 form-select">
                        <option value="active" selected>Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="form-label" for="rate-per-hours">Rate Per Hours</label>
                    <input type="text" class="form-control" id="rate-per-hours" name="rate_per_hours"
                        placeholder="Rate Per Hours" name="rate-per-hours" aria-label="Billing Address" />
                    <div id="rate-per-hours-feedback">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="form-label" for="website-url">Linkdin(URL)</label>
                    <input type="text" class="form-control" id="website-url" placeholder="linkedin-url"
                        name="website-url" aria-label="Website-url" />
                    <div id="website-url-feedback">
                    </div>
                </div>


                <button type="submit" class="btn btn-primary me-3 data-submit">Submit</button>
                <button type="reset" class="btn btn-label-danger" id="cancel_form"
                    data-bs-dismiss="offcanvas">Cancel</button>
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
        </div>
    </div>
    {{-- END :  Modal for delete confirmation --}}
@endsection
