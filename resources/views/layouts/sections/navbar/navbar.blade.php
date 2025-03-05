@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;
    use App\Models\Department;
    use App\Models\User;
    use Illuminate\Support\Facades\DB;

    $containerNav = $configData['contentLayout'] === 'compact' ? 'container-xxl' : 'container-fluid';
    $navbarDetached = $navbarDetached ?? '';

    $departmentsAssigned = Helper::getDepartmentsNotAssignedToUser();

    // Get the currently logged-in user changes
    $loggedInUser = Auth::user();

    // Fetch all departments
    $allDepartments = Department::all();

    // Get the department IDs that the logged-in user is associated with from the 'users_departments' pivot table
    $assignedDepartments = DB::table('users_departments')
        ->where('user_id', $loggedInUser->id) // Filter by logged-in user
        ->pluck('department_id')
        ->toArray(); // Get all department_ids assigned to this user

    // Filter departments that are assigned to the logged-in user
    $departmentsAssigned = $allDepartments->filter(function ($department) use ($assignedDepartments) {
        return in_array($department->id, $assignedDepartments); // Keep departments that are assigned
    });
    $dashboard_url = Route::currentRouteName();
    $department_value = session('selected_department');
@endphp
<style>
    #change-password:hover {
        background: #d7d6d63b;
    }

    @media (max-width: 330px) {
        * {
            font-size: 13px !important;
        }
    }

    @media (min-width: 331px) and (max-width:425px) {
        * {
            font-size: 14px !important;
        }
    }

    /* Slide Down Effect */
    .modal.fade .modal-dialog {
        transform: translateY(-100%);
        transition: transform 0.3s ease-out;
    }

    .modal.show .modal-dialog {
        transform: translateY(0);
    }
</style>
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<!-- Include Flatpickr JS -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- jQuery Validation Plugin -->

<!-- Navbar -->
@if (isset($navbarDetached) && $navbarDetached == 'navbar-detached')
    <nav class="layout-navbar {{ $containerNav }} navbar navbar-expand-xl {{ $navbarDetached }} align-items-center bg-navbar-theme"
        id="layout-navbar">
@endif

@if (isset($navbarDetached) && $navbarDetached == '')
    <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
        <div class="{{ $containerNav }}">
@endif

<!--  Brand demo (display only for navbar-full and hide on below xl) -->
@if (isset($navbarFull))
    <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
        <a href="{{ url('dashboard') }}" class="app-brand-link">
            <img width="95%" src="{{ asset('assets/img/logo/logo.svg') }}" alt="">
        </a>
    </div>
@endif

<!-- ! Not required for layout-without-menu -->
@if (!isset($navbarHideToggle))
    <div
        class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="ti ti-menu-2 ti-md"></i>
        </a>
    </div>
@endif
<input type="hidden" value="{{$dashboard_url}}" id="dashboard_url">
<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    <ul class="navbar-nav flex-row align-items-center ms-auto">
        <!-- User -->
        <li class="nav-item navbar-dropdown dropdown-user dropdown">
            <div class="d-flex align-items-center">
                <a class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow "
                    href="{{ route('chat.index') }}">
                    <span class="position-relative">
                        <i class="fas fa-comment text-primary fw-bold fs-5"></i>
                        {{-- <span class="badge rounded-pill bg-danger badge-dot badge-notifications border"></span> --}}
                    </span>
                </a>

                {{-- <a class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow "
                    href="">
                    <span class="position-relative">
                        <i class="fa fa-light fa-bell text-primary fw-bold fs-5"></i>
                        <span class="badge rounded-pill bg-danger badge-dot badge-notifications border"></span>
                    </span>
                </a> --}}

                <a class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow me-2"
                    data-bs-toggle="modal" data-bs-target="#swithdepartment">
                    <i class="fa fa-repeat text-primary fw-bold fs-5" data-bs-toggle="tooltip"
                        data-bs-placement="bottom" data-bs-title="Switch Department"></i>
                </a>
                {{-- <a href="" class="mx-2"> --}}
                {{-- <i class="ti ti-bell ti-md"></i> --}}
                {{-- <i class="fa fa-light fa-bell text-primary fw-bold fs-5"></i>
                    <span class="badge rounded-pill bg-danger badge-dot badge-notifications border"></span>
                </a> --}}
                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img alt="" class="rounded-circle" src="{{ asset('assets/img/avatars/11.png') }}">
                    </div>
                </a>

                <ul class="dropdown-menu dropdown-menu-end w-50 ms-auto me-2">
                    <li>
                        <a class="dropdown-item mt-0"
                            href="{{ Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);' }}">
                            <div class="d-flex align-items-center">
                                <div class=" d-flex flex-wrap me-2">
                                    <div class="avatar avatar-online d-flex" style="align-items: center;">
                                        <img src="{{ asset('assets/img/avatars/11.png') }}" alt class="rounded-circle">
                                        <h5 class="ms-2 mb-0">{{ auth()->user()->name }}
                                        </h5>
                                    </div>
                                </div>
                            </div>

                        </a>
                    </li>

                    @if (Auth::check())
                        <li>
                            {{-- @php
                                // $backgroundColor = Route::has('changePasswordView') ? '#d7d6d63b' : 'white';
                            @endphp --}}

                            <div id="change-password" class="d-grid px-2 pt-2 pb-1">
                                {{-- style="background :{{ $backgroundColor }} !important" --}}
                                <a href="{{ route('changePasswordView') }}">Change
                                    Password</a>
                            </div>
                        </li>
                        <li>
                            <div class="d-grid px-2 pt-2 pb-1">
                                <a class="btn btn-sm btn-danger d-flex" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <small class="align-middle">Logout</small>
                                    <i class="ti ti-logout ms-2 ti-14px"></i>
                                </a>
                            </div>
                        </li>
                        <form method="GET" id="logout-form" action="{{ route('logout') }}">
                            @csrf
                        </form>
                    @else
                        <li>
                            <div class="d-grid px-2 pt-2 pb-1">
                                <a class="btn btn-sm btn-danger d-flex"
                                    href="{{ Route::has('login') ? route('login') : url('auth/login-basic') }}">
                                    <small class="align-middle">Login</small>
                                    <i class="ti ti-login ms-2 ti-14px"></i>
                                </a>
                            </div>
                        </li>
                    @endif
                </ul>
                <div class="flex-grow-1 ms-1">
                    <h6 class="mb-0">
                        @if (Auth::check())
                            {{ Auth::user()->name }}
                        @else
                            John Doe
                        @endif
                    </h6>
                    <small class="text-muted">Admin</small>
                </div>
            </div>
        </li>
        <!--/ User -->
    </ul>
</div>

@if (!isset($navbarDetached))
    </div>
@endif
</nav>
<div class="modal fade" id="swithdepartment" tabindex="-1" aria-labelledby="swithdepartmentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="swithdepartmentLabel">Switch Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body add_another_user">
                <div class="row">
                    <div class="col-md-12">
                        <label for="not_assigned_department" class="form-label">Department</label>
                        <select class="select2 form-select" id="not_assigned_department"
                            name="not_assigned_department[]">
                            <option value="" disabled selected>Please Select</option>
                            @foreach ($departmentsAssigned as $department_name)
                                <option value="{{ $department_name->id }}"
                                    {{ $department_name->id == session('selected_department') ? 'selected' : '' }}>
                                    {{ $department_name->name }}
                                </option>
                            @endforeach
                           
                        </select>
                        <small class="text-danger" id="department-error" style="display:none;"></small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="users_switch_department">Save</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>


<div class="col-lg-4 col-md-6">
        <div class="mt-4">
            <div class="modal fade" id="modalToggle_dep" aria-labelledby="modalToggleLabel_dep" tabindex="-1"
                style="display: none;" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalToggleLabel_dep">Confirmation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to switch this department ?
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary" id="confirmed_dep" data-bs-toggle="modal"
                                data-bs-dismiss="modal">Yes</button>
                            <button class="btn btn-secondary" id="notConfirmed_dep" data-bs-dismiss="modal">No</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
<script>

$('#users_switch_department').on('click', function() {
    // Hide the first modal
    var departmentId = $('#not_assigned_department').val(); 
    if(!departmentId){
        $('#department-error').text('Please select a Department.').show();
        // $('#client').addClass('is-invalid');
        return;
    }
    $('#swithdepartment').modal('hide');
    
    // Show the confirmation modal
    $('#modalToggle_dep').modal('show');
});
$('#confirmed_dep').on('click', function() {
    // Get the selected department ID or any relevant data
    var departmentId = $('#not_assigned_department').val(); // Get the selected department from the dropdown

    // If no department is selected, show an alert or handle accordingly
   

    // Make an AJAX request to switch the department
    $.ajax({
        url: '{{ route("switch.department") }}',// The route where we want to send the request
        method: 'POST',
        data: {
            department_ids: departmentId, // Send the department ID to the server
            _token: $('meta[name="csrf-token"]').attr('content') // CSRF token for security
        },
        success: function(response) {
            if (response.status === 'success') {
                // Handle success
                window.location.href = "{{route('dashboard')}}";
                if(dashboard_url == "dashboard"){
                    location.reload();
                }
                // Close the confirmation modal
                $('#modalToggle_dep').modal('hide');
            } else {
                // Handle failure (if any)
                alert('Failed to switch department. Please try again.');
            }
        },
        error: function(xhr, status, error) {
            // Handle any errors
            alert('An error occurred. Please try again.');
        }
    });
});

</script>
    {{-- END :  Modal for confirmation --}}

<!-- / Navbar -->
