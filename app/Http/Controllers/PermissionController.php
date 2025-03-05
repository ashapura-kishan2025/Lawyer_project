<?php

namespace App\Http\Controllers;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Module;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
class PermissionController extends Controller
{
    //

    public function index()
    {
        $user = auth()->user();

        // Get the user's role_id from the user_departments table
        $userRole = DB::table('users_departments')
            ->where('user_id', $user->id)
            ->first();
        if($userRole->role_id == 3){
            $roles = Role::all();
            return view('role.index', compact('roles'));
        } else{
            return view('error.custom-error');
        }
    }
    public function edit($roleId)
    {
        $user = auth()->user();

        // Get the user's role_id from the user_departments table
        $userRole = DB::table('users_departments')
            ->where('user_id', $user->id)
            ->first();
        if($userRole->role_id == 3){
            $selectedDepartments = session('selected_department');
            $role = Role::findOrFail($roleId);
            $modules = Module::all();  // Get all modules
            $actions = ['CREATE', 'EDIT', 'VIEW', 'LIST', 'DELETE','IMPORT','EXPORT'];  // Define your actions

            $departments = DB::table('users_departments')
            ->where('role_id', $roleId)
            ->where('department_id', $selectedDepartments)->first();
            if($selectedDepartments){
                $rolePermissions = DB::table('department_role_permissions')
                ->where('department_id', $selectedDepartments)  // Use whereIn for arrays
                    ->pluck('permission_id');  // Get the permission IDs for selected departments
            } else {
                $rolePermissions = DB::table('department_role_permissions')
                ->where('department_id', $departments)  // Use whereIn for arrays
                    ->pluck('permission_id');  // Get the permission IDs for the departments this role belongs to
            }
                // dd($rolePermissions);
            // Fetch the permissions for this role, department-wise
            // Fetch permission names from permission IDs
            $rolePermissionNames = Permission::whereIn('id', $rolePermissions)->pluck('name')->toArray();

            // dd($rolePermissionNames);
            return view('permissions.index', compact('role', 'modules', 'actions', 'rolePermissionNames','selectedDepartments'));
        } else{
            return view('error.custom-error');
        }
    }

    /**
     * Create a new permission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createPermission(Request $request)
    {
        // Validate input
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        // Create a new permission
        Permission::create(['name' => $request->name]);

        return redirect()->route('permissions.index')->with('success', 'Permission created successfully');
    }

    /**
     * Create a new role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createRole(Request $request)
    {

        // Validate input
        $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);

        // Create a new role
        Role::create(['name' => $request->name]);

        return redirect()->route('permissions.index')->with('success', 'Role created successfully');
    }

    /**
     * Assign a permission to a role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function assignPermissionToRole(Request $request)
    {
        // Validate input
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        // Find the role and permission
        $role = Role::findById($request->role_id);
        $permission = Permission::findById($request->permission_id);

        // Assign permission to role
        $role->givePermissionTo($permission);

        return redirect()->route('permissions.index')->with('success', 'Permission assigned to role successfully');
    }

    /**
     * Revoke a permission from a role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function revokePermissionFromRole(Request $request)
    {
        // Validate input
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        // Find the role and permission
        $role = Role::findById($request->role_id);
        $permission = Permission::findById($request->permission_id);

        // Revoke the permission from the role
        $role->revokePermissionTo($permission);

        return redirect()->route('permissions.index')->with('success', 'Permission revoked from role successfully');
    }

    /**
     * Assign a role to a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function assignRoleToUser(Request $request)
    {
        // Validate input
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        // Find the user and role
        $user = User::find($request->user_id);
        $role = Role::find($request->role_id);

        // Assign role to user
        $user->assignRole($role);

        return redirect()->route('permissions.index')->with('success', 'Role assigned to user successfully');
    }

    /**
     * Revoke a role from a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function revokeRoleFromUser(Request $request)
    {
        // Validate input
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        // Find the user and role
        $user = User::find($request->user_id);
        $role = Role::find($request->role_id);

        // Revoke the role from the user
        $user->removeRole($role);

        return redirect()->route('permissions.index')->with('success', 'Role revoked from user successfully');
    }
    // public function update(Request $request, $id)
    // {
    //     // Step 1: Get the logged-in user
    //     $user = auth()->user();  // Get the logged-in user

    //     // Step 2: Get the user departments and roles from the `users_departments` table
    //     $userDepartments = DB::table('users_departments')
    //         ->where('role_id', $id)  // Fetch departments for the logged-in user
    //         ->get();

    //     // Step 3: Get the role by ID
    //     $role = Role::findOrFail($id);  // Get the role by ID (Role you want to assign permissions to)
    //     $userDepartmentsWithRole = $userDepartments->where('role_id', $role->id);

    //     // If no matching department is found, return an error message
    //     if ($userDepartmentsWithRole->isEmpty()) {
    //         return redirect()->route('role.index')->with('error', 'This role is not assigned to your departments.');
    //     }
    //     // dd($userDepartmentsWithRole);
    //     // Step 5: Fetch permission IDs for the given permission names from the request
    //     if( $request->permissions)
    //     {
    //         $permissionIds = Permission::whereIn('name', $request->permissions)->pluck('id');
    //     } else{
    //         $permissionIds = '';
    //     }
    //     // Check if any permissions are found
    //     if (!$permissionIds) {
    //         return redirect()->route('role.index')->with('error', 'Some permissions are invalid.');
    //     }

    //     // Step 6: Iterate through each department and assign permissions

    //     foreach ($userDepartmentsWithRole as $userDepartment) {
    //         // Get the user_department_id, which is the 'id' field from the users_departments table
    //         $userDepartmentId = $userDepartment->id;
    //         $departmentId = $userDepartment->department_id;
    //         // dd($departmentId);
    //         // Loop through each permission ID and update permissions for this department
    //         foreach ($permissionIds as $permissionId) {

    //             // Step 6a: Check if the permission is already assigned to the user for this department
    //             $existingPermission = DB::table('permissions_users')
    //                 ->where('permission_id', $permissionId)
    //                 ->where('user_departments_id', $userDepartmentId)
    //                 ->first();
    //             // dd($existingPermission);
    //             // If it exists, update it; otherwise, insert a new record in permissions_users
    //             if ($existingPermission) {
    //                 DB::table('permissions_users')
    //                     ->where('permission_id', $permissionId)
    //                     ->where('user_departments_id', $userDepartmentId)
    //                     ->update([
    //                         'updated_at' => now(), // Update timestamp
    //                     ]);
    //             } else {
    //                 DB::table('permissions_users')->insert([
    //                     'permission_id' => $permissionId,
    //                     'user_departments_id' => $userDepartmentId,
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]);
    //             }

    //             // Step 6b: Link the permissions to the role (using Spatie's role-permission system)
    //             $role->givePermissionTo(Permission::find($permissionId)); // Assign permission to role

    //            // Update the `role_has_permissions` table for the department context
    //             foreach ($permissionIds as $permissionId) {
    //                 // Check if the combination of role_id, permission_id, and department_id exists
    //                 $existingEntry = DB::table('role_has_permissions')
    //                     ->where('role_id', $role->id)
    //                     ->where('permission_id', $permissionId)
    //                     ->where('department_id', $departmentId)
    //                     ->first();

    //                 // If the entry exists, update it
    //                 if ($existingEntry) {
    //                     DB::table('role_has_permissions')
    //                         ->where('role_id', $role->id)
    //                         ->where('permission_id', $permissionId)
    //                         ->where('department_id', $departmentId)
    //                         ->update([
    //                             'updated_at' => now(),  // Update the timestamp
    //                         ]);
    //                 } else {
    //                     // If the entry doesn't exist, insert a new record
    //                     DB::table('role_has_permissions')->insert([
    //                         'role_id' => $role->id,
    //                         'permission_id' => $permissionId,
    //                         'department_id' => $departmentId,
    //                         // 'created_at' => now(),
    //                         'updated_at' => now(),
    //                     ]);
    //                 }
    //             }

    //         }
    //     }

    //     // Step 7: Redirect with success message
    //     return redirect()->route('role.index')->with('status', 'Permissions updated successfully!');
    // }
    public function update(Request $request, $id)
    {

        $validated = $request->validate([
            'role_name' => 'required|string|max:255|unique:roles,name,' . $id . ',id,guard_name,web',  // Ensure unique role name for this role, keeping 'web' guard_name intact
        ]);
        // dd($request);
        // Step 1: Get the role by ID
        $role = Role::findOrFail($id);
        if ($request->has('role_name')) {
            $role->name = $request->input('role_name');
            $role->save();
        }
        // Step 2: Fetch departments associated with this role (department_role_permissions)
        $getRoleDepartmentwise = DB::table('users_departments')
            ->where('role_id', $id)
            ->where('department_id',$request->department_id)->get();  // Fetch departments for the role

            // Step 3: Get existing permissions for the role and departments
        $existingPermissions = DB::table('department_role_permissions')
            ->where('role_id', $role->id)
            ->whereIn('department_id', $getRoleDepartmentwise->pluck('department_id'))
            ->pluck('permission_id')
            ->toArray();

        // Step 4: Check the permissions from the request
        $permissions = $request->permissions ?? [];  // If no permissions are selected, default to an empty array

        // Fetch permission IDs based on permission names
        $permissionIds = Permission::whereIn('name', $permissions)->pluck('id')->toArray();

        // Step 5: Sync permissions with Spatie
        $role->syncPermissions($permissions);  // Sync the permissions using Spatie package

        // Step 6: Handle removal of unchecked permissions
        $permissionsToRemove = array_diff($existingPermissions, $permissionIds); // Permissions that need to be removed

        if (!empty($permissionsToRemove)) {
            // Remove these permissions from department_role_permissions and role_has_permissions tables
            DB::table('department_role_permissions')
                ->whereIn('permission_id', $permissionsToRemove)
                ->where('role_id', $role->id)
                ->whereIn('department_id', $getRoleDepartmentwise->pluck('department_id'))
                ->delete();

            DB::table('role_has_permissions')
                ->whereIn('permission_id', $permissionsToRemove)
                ->where('role_id', $role->id)
                ->delete();
        }

        // Step 7: Handle addition of new permissions
        foreach ($getRoleDepartmentwise as $department) {
            $departmentId = $department->department_id;

            foreach ($permissionIds as $permissionId) {
                // Check if permission already exists for the role, department, and permission
                $existingPermission = DB::table('department_role_permissions')
                    ->where('role_id', $role->id)
                    ->where('permission_id', $permissionId)
                    ->where('department_id', $departmentId)
                    ->first();
                // If the permission doesn't exist, insert a new permission record for the department
                if (!$existingPermission) {
                    DB::table('department_role_permissions')->insert([
                        'role_id' => $role->id,
                        'permission_id' => $permissionId,
                        'department_id' => $departmentId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Add the permission to role_has_permissions (Spatie)
                $role->givePermissionTo(Permission::find($permissionId));
            }
        }

        // Step 8: Redirect with success message
        return redirect()->route('role.index')->with('status', 'Permissions updated successfully!');
    }


}
