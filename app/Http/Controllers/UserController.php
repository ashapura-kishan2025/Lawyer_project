<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\forgetPasswordEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use App\Models\Client;
use App\Models\Currency;
use Illuminate\Support\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;
use Illuminate\Routing\Controller;
use App\Mail\WelcomeUserMail;

class UserController extends Controller
{
  //
  public function __construct()
  {
    // Middleware to check if the user has the relevant permissions
    $this->middleware('permission:USER.VIEW', ['only' => ['view']]);
    $this->middleware('permission:USER.LIST', ['only' => ['index']]);
    $this->middleware('permission:USER.CREATE', ['only' => ['create', 'store']]);
    $this->middleware('permission:USER.EDIT', ['only' => ['edit', 'update']]);
    $this->middleware('permission:USER.DELETE', ['only' => ['destroy']]);
  }

  public function forgetPasswordView()
  {
    return view("forget-password");
  }

  public function resetPasswordView()
  {
    return view("reset-password");
  }


  public function sendEmail(Request $request)
  {
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink(
      $request->only('email')
    );

    return $status === Password::ResetLinkSent
      ? back()->with(['status' => __($status)])
      : back()->withErrors(['email' => __($status)]);
  }

  public function resetPassword(Request $request)
  {
    $request->validate([
      'token' => 'required',
      'email' => 'required|email|exists:users,email',
      'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
      $request->only('email', 'password', 'password_confirmation', 'token'),
      function (User $user, string $password) {
        $user->forceFill([
          'password' => Hash::make($password)
        ])->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));
      }
    );

    if ($status !== Password::PASSWORD_RESET) {
      Log::warning('Password reset failed for email: ' . $request->email);
    }

    return $status === Password::PASSWORD_RESET
      ? redirect()->route('password.passwordChangeBackToLogin')
      : back()->withErrors(['email' => [__($status)]]);
  }


  public function index(Request $request)
  {
      if ($request->ajax()) {
          // Get the filter values from the request
          $nameEmail = $request->input('name_email');
          $status = $request->input('status');
          $last_login_at = $request->input('last_login_at');
          $departmentIds = $request->input('department');

          // Start building the query
          $query = User::with(['departments', 'roles'])
              ->select(['users.id', 'users.name', 'users.email', 'users.status', 'users.last_login_at'])
              ->join('users_departments', 'users.id', '=', 'users_departments.user_id') // Join with the user_departments table
              ->whereIn('users_departments.role_id', [1, 2]);
          // Apply filters to the query
          if ($nameEmail) {
              $query->where(function ($query) use ($nameEmail) {
                  $query->where('users.name', 'like', "%$nameEmail%")
                        ->orWhere('users.email', 'like', "%$nameEmail%");  // Search in both name and email
              });
          }

          if ($status) {
              $query->where('users.status', $status);
          }

          if ($last_login_at) {
              $query->whereDate('users.last_login_at', '=', $last_login_at);
          }

          if ($departmentIds) {
              // Check if departmentIds is a string (single value), convert it to an array
              if (!is_array($departmentIds)) {
                  $departmentIds = [$departmentIds]; // Convert to array
              }

              // Use whereIn for multiple departments
              $query->whereHas('departments', function ($query) use ($departmentIds) {
                  $query->whereIn('departments.id', $departmentIds);  // Filter by one or multiple department IDs
              });
          }

          // Order the results by user id
          $query->orderBy('users.id', 'desc');

          // Paginate the results for DataTables
          $data = $query->get();

          // Format the `last_login_at` field as per the required format after fetching the data
          $data->map(function ($user) {
              // Format the last_login_at field
              $user->last_login_at = Carbon::parse($user->last_login_at)->format('d M, Y h:i a');
              return $user;
          });

          // Return the DataTables response with the filtered and formatted data
          return DataTables::of($data)
              ->addColumn('action', function ($row) {
                  // Check if the user has role 2 (you can adjust this logic based on your actual role setup)
                  $isRole2 = $row->roles->contains('id', 2);

                  $editButton = '<i class="fa-sharp fa-solid fa-pen edit-user-btn me-2" style="font-size: 14px; color:#192b53;cursor: pointer;" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddUser" data-id="' . $row->id . '"></i>';

                  $deleteButton = $isRole2
                      ? '<i class="ti ti-trash" style="font-size: 20px;color:grey;cursor: not-allowed;" data-id="' . $row->id . '" title="Cannot delete user with role 2"></i>'
                      : '<i class="ti ti-trash delete-user-btn" style="font-size: 20px;color:red;cursor: pointer;" data-id="' . $row->id . '"></i>';

                  return $editButton . ' ' . $deleteButton;
              })
              ->editColumn('status', function ($row) {
                  return $row->status == "active"
                      ? '<span class="badge bg-label-success inlineStatus" onClick="userClickStatusChange(\'' . $row->id . '\', \'' . $row->status . '\')" style="cursor: pointer;">Active</span>'
                      : '<span class="badge bg-label-primary inlineStatus" onClick="userClickStatusChange(\'' . $row->id . '\', \'' . $row->status . '\')" style="cursor: pointer;">Inactive</span>';
              })
              ->addColumn('departments', function ($row) {
                // Get the unique department names and generate a badge for each
                $badges = $row->departments->pluck('name')->unique()->map(function($name) {
                    return '<span class="badge bg-primary mb-2 mx-1">' . $name . '</span>';
                })->implode(' '); // Join badges with a space between them

                return $badges;
            })

              ->editColumn('role', function ($row) {
                  $uniqueRoles = $row->roles->pluck('name')->unique();

                  // Implode the unique roles into a comma-separated string
                  return implode(', ', $uniqueRoles->toArray());
              })
              ->rawColumns(['status', 'action','departments'])
              ->make(true);
      }

      $department = Department::where('status','1')->get();
      $role = Role::all();
      return view('user.index', compact('department', 'role'));
  }

  public function getDepartment()
  {
    $department = Department::where('status','1')->get();
    return response()->json($department);
  }
  public function getRole()
  {
    $role = DB::table('roles')->select('id', 'name')->get();
    return response()->json($role);
  }
  public function store(Request $request)
  {

    if ($request->ajax()) {
      // dd($request->all());
      $validatedData = $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users,email',
        // 'departments' =>  'required',
        // 'rate_per_hours' => 'required|numeric',
        // 'role' => 'required',
        // 'source_other' => 'required',
        // 'linkedin_url' => 'nullable'
      ]);
      // dd($request);
      $user = new User();
      $user->name = $request->name;
      $user->email = $request->email;
      $user->password = Hash::make('12345678'); // You can set a default password or handle dynamically
      $user->status = $request->status;
      $user->linkedin_url = $request->linkedin_url;
      $user->rate = $request->rate_per_hours;
      $user->last_login_ip = $request->ip();
      // $user->role_id = $request->role;
      $user->save();
      // dd($request->role);
      // Sync roles and departments
      $role = $request->role;
      $departments = $request->department;

      // First, sync the departments with the user. This will create the entries in the pivot table.
      $user->departments()->sync($departments);

      // Step 2: Associate roles with each department for the user
      foreach ($departments as $index => $departmentId) {
        $roleId = $role[$index]; // Get the corresponding role for the department

        // Update the pivot table (assuming the pivot table is 'user_department' and has 'role_id')
        $user->departments()->updateExistingPivot($departmentId, [
          'role_id' => $roleId,   // Associate role with department
          'created_at' => now(),   // Set the created_at timestamp
          'updated_at' => now()    // Set the updated_at timestamp
        ]);
      }
      Mail::to($user->email)->send(new WelcomeUserMail($user, '12345678'));
      // $response = Client::create($validatedData);
      if ($user) {
        // return back()->with('success', 'Client added Successfully!');
        return response()->json([
          'success' => TRUE,
          'message' => 'User added Successfully'
        ]);
      };
    }
  }
  public function checkEmail(Request $request)
  {
    $email = $request->input('email');

    // Check if the email exists in the database
    $exists = User::where('email', $email)->exists();

    return response()->json(['exists' => $exists]);
  }
  public function edit($id)
  {
    $user = User::findOrFail($id);

    // Fetch all roles and convert them to an array
    $roles = Role::all()->map(function ($role) {
      return [
        'id' => $role->id,
        'name' => $role->name
      ];
    });

    // Fetch all departments
    $departments = Department::where('status','1')->get();

    // Get the user's departments with associated roles
    $departmentsWithRoles = $user->departments()->withPivot('role_id')->get();

    // Prepare department and role data for the frontend
    $userDepartments = $departmentsWithRoles->map(function ($department) {
      $departmentName = $department->name;
      $roleName = Role::find($department->pivot->role_id)->name ?? 'No Role';

      return [
        'department_id' => $department->id,
        'department_name' => $departmentName,
        'role_id' => $department->pivot->role_id,
        'role_name' => $roleName
      ];
    });

    return response()->json([
      'success' => true,
      'data' => [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'rate' => $user->rate,
        'linkedin_url' => $user->linkedin_url,
        'status' => $user->status,
        'departments' => $userDepartments,
        'departments_all' => $departments,
        'roles' => $roles // Ensure this is an array now
      ]
    ]);
  }

  public function update(Request $request, $id)
  {

      // Validate the incoming data
      $validatedData = $request->validate([
          'name' => 'required',
          'email' => 'required|email|unique:users,email,' . $id, // Ensure unique email except for current user
          'status' => 'required',
          'department' => 'required|array', // Validate departments as an array
          'role' => 'required|array', // Ensure roles is also an array
      ]);

      // Retrieve the user to be updated
      $user = User::findOrFail($id);
      // Update user details
      $user->name = $request->name;
      $user->email = $request->email;
      $user->status = $request->status;
      $user->linkedin_url = $request->linkedin_url;  // Optional field
      $user->last_login_ip = $request->ip();
      $user->rate = $request->rate_per_hours;
      $user->save();  // Save user data

      // Get the department and role arrays from the request
      $departments = $request->department;
      $roles = $request->role;

      // Step 1: Prepare data for sync (department_id => role_id)
      $syncData = [];
      foreach ($departments as $index => $departmentId) {
          $syncData[$departmentId] = ['role_id' => $roles[$index]];  // Map each department to its role
      }
      //data
      // Step 2: Sync existing departments and their roles
      $user->departments()->sync($syncData); // Sync departments and their corresponding roles

      // Step 3: Add new department-role entries if necessary
      foreach ($departments as $index => $departmentId) {
          $roleId = $roles[$index]; // Get the corresponding role for the department

          // Check if the department is already associated with the user
          $existingPivot = $user->departments()->wherePivot('department_id', $departmentId)->first();

          // If the department exists but with a different role, add the new role as a new entry
          if ($existingPivot && $existingPivot->pivot->role_id != $roleId) {
              // Add new entry with the same department but a different role
              $user->departments()->attach($departmentId, [
                  'role_id' => $roleId,  // Assign the corresponding role for this department
                  'created_at' => now(),  // Add the creation timestamp
                  'updated_at' => now()   // Add the update timestamp
              ]);
          }
      }

      // Return success response if user is updated successfully
      return response()->json([
        'success' => true,
        'message' => 'User Updated Successfully'
      ]);
    }


  public function destroy($id)
  {
    // Find the user to be deleted
    $user = User::findOrFail($id);

    // Store the current time to mark the deletion
    $currentTime = now();

    // Update deleted_at in the user_departments pivot table
    $user->departments()->updateExistingPivot($user->departments->pluck('id'), [
      'deleted_at' => $currentTime, // Mark departments as deleted (soft delete)
    ]);

    // Soft delete the user (this will update the deleted_at column in the users table)
    $user->delete(); // The deleted_at column in the users table will be updated

    // Return a JSON response to indicate success
    return response()->json([
      'success' => true,
      'message' => 'User details deleted successfully.',
      'user_id' => $user->id
    ]);
  }
  public function userinlineStatusChange(Request $request, User $user)
  {
    // dd($request);
    if ($request->ajax()) {
      $id = $request->input('id');
      $status = $request->input('status');
      $user = User::findOrFail($id);

      if ($user) {
        $user->update(['status' => $status]);
      }
      return response()->json(['success' => true, 'message' => 'User Status Updated Successfully']);
    } else {
      return "Something is not working";
    }
  }


  public function passwordChangeBackToLogin()
  {
    return view("password-updated-successfully");
  }

  public function changePassword(Request $request)
  {

    // Validate the input
    $validatedData = $request->validate([
      'currentPassword' => ['required'],
      'newPassword' => ['required', 'min:8'],
      'confirmPassword' => ['required', 'same:newPassword']
    ]);

    // Get the logged-in user
    $user = Auth::user();

    // Verify the current password
    if (!Hash::check($request->currentPassword, $user->password)) {
      return back()->withErrors(['currentPassword' => 'Current password is incorrect.']);
    }
    // Update the password
    $user->update([
      'password' => Hash::make($validatedData['newPassword'])
    ]);

    return back()->with('success', 'Password changed successfully!');
  }
}
