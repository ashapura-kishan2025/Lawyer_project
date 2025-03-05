<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use App\Models\Log;
use Spatie\Permission\Models\Permission;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class Helpers
{
  public static function appClasses()
  {
   
    $data = config('custom.custom');


    // default data array
    $DefaultData = [
      'myLayout' => 'vertical',
      'myTheme' => 'theme-default',
      'myStyle' => 'light',
      'myRTLSupport' => true,
      'myRTLMode' => true,
      'hasCustomizer' => true,
      'showDropdownOnHover' => true,
      'displayCustomizer' => true,
      'contentLayout' => 'compact',
      'headerType' => 'fixed',
      'navbarType' => 'fixed',
      'menuFixed' => true,
      'menuCollapsed' => false,
      'footerFixed' => false,
      'customizerControls' => [
        'rtl',
        'style',
        'headerType',
        'contentLayout',
        'layoutCollapsed',
        'showDropdownOnHover',
        'layoutNavbarOptions',
        'themes',
      ],
      //   'defaultLanguage'=>'en',
    ];

    // if any key missing of array from custom.php file it will be merge and set a default value from dataDefault array and store in data variable
    $data = array_merge($DefaultData, $data);

    // All options available in the template
    $allOptions = [
      'myLayout' => ['vertical', 'horizontal', 'blank', 'front'],
      'menuCollapsed' => [true, false],
      'hasCustomizer' => [true, false],
      'showDropdownOnHover' => [true, false],
      'displayCustomizer' => [true, false],
      'contentLayout' => ['compact', 'wide'],
      'headerType' => ['fixed', 'static'],
      'navbarType' => ['fixed', 'static', 'hidden'],
      'myStyle' => ['light', 'dark', 'system'],
      'myTheme' => ['theme-default', 'theme-bordered', 'theme-semi-dark'],
      'myRTLSupport' => [true, false],
      'myRTLMode' => [true, false],
      'menuFixed' => [true, false],
      'footerFixed' => [true, false],
      'customizerControls' => [],
      // 'defaultLanguage'=>array('en'=>'en','fr'=>'fr','de'=>'de','ar'=>'ar'),
    ];

    //if myLayout value empty or not match with default options in custom.php config file then set a default value
    foreach ($allOptions as $key => $value) {
      if (array_key_exists($key, $DefaultData)) {
        if (gettype($DefaultData[$key]) === gettype($data[$key])) {
          // data key should be string
          if (is_string($data[$key])) {
            // data key should not be empty
            if (isset($data[$key]) && $data[$key] !== null) {
              // data key should not be exist inside allOptions array's sub array
              if (!array_key_exists($data[$key], $value)) {
                // ensure that passed value should be match with any of allOptions array value
                $result = array_search($data[$key], $value, 'strict');
                if (empty($result) && $result !== 0) {
                  $data[$key] = $DefaultData[$key];
                }
              }
            } else {
              // if data key not set or
              $data[$key] = $DefaultData[$key];
            }
          }
        } else {
          $data[$key] = $DefaultData[$key];
        }
      }
    }
    $styleVal = $data['myStyle'] == "dark" ? "dark" : "light";
    $styleUpdatedVal = $data['myStyle'] == "dark" ? "dark" : $data['myStyle'];
    // Determine if the layout is admin or front based on cookies
    $layoutName = $data['myLayout'];
    $isAdmin = Str::contains($layoutName, 'front') ? false : true;

    $modeCookieName = $isAdmin ? 'admin-mode' : 'front-mode';
    $colorPrefCookieName = $isAdmin ? 'admin-colorPref' : 'front-colorPref';

    // Determine style based on cookies, only if not 'blank-layout'
    if ($layoutName !== 'blank') {
      if (isset($_COOKIE[$modeCookieName])) {
        $styleVal = $_COOKIE[$modeCookieName];
        if ($styleVal === 'system') {
          $styleVal = isset($_COOKIE[$colorPrefCookieName]) ? $_COOKIE[$colorPrefCookieName] : 'light';
          }
        $styleUpdatedVal = $_COOKIE[$modeCookieName];
      }
    }

    isset($_COOKIE['theme']) ? $themeVal = $_COOKIE['theme'] : $themeVal = $data['myTheme'];

    $directionVal = isset($_COOKIE['direction']) ? ($_COOKIE['direction'] === "true" ? 'rtl' : 'ltr') : $data['myRTLMode'];

    //layout classes
    $layoutClasses = [
      'layout' => $data['myLayout'],
      'theme' => $themeVal,
      'themeOpt' => $data['myTheme'],
      'style' => $styleVal,
      'styleOpt' => $data['myStyle'],
      'styleOptVal' => $styleUpdatedVal,
      'rtlSupport' => $data['myRTLSupport'],
      'rtlMode' => $data['myRTLMode'],
      'textDirection' => $directionVal,//$data['myRTLMode'],
      'menuCollapsed' => $data['menuCollapsed'],
      'hasCustomizer' => $data['hasCustomizer'],
      'showDropdownOnHover' => $data['showDropdownOnHover'],
      'displayCustomizer' => $data['displayCustomizer'],
      'contentLayout' => $data['contentLayout'],
      'headerType' => $data['headerType'],
      'navbarType' => $data['navbarType'],
      'menuFixed' => $data['menuFixed'],
      'footerFixed' => $data['footerFixed'],
      'customizerControls' => $data['customizerControls'],
    ];

    // sidebar Collapsed
    if ($layoutClasses['menuCollapsed'] == true) {
      $layoutClasses['menuCollapsed'] = 'layout-menu-collapsed';
    }

    // Header Type
    if ($layoutClasses['headerType'] == 'fixed') {
      $layoutClasses['headerType'] = 'layout-menu-fixed';
    }
    // Navbar Type
    if ($layoutClasses['navbarType'] == 'fixed') {
      $layoutClasses['navbarType'] = 'layout-navbar-fixed';
    } elseif ($layoutClasses['navbarType'] == 'static') {
      $layoutClasses['navbarType'] = '';
    } else {
      $layoutClasses['navbarType'] = 'layout-navbar-hidden';
    }

    // Menu Fixed
    if ($layoutClasses['menuFixed'] == true) {
      $layoutClasses['menuFixed'] = 'layout-menu-fixed';
    }


    // Footer Fixed
    if ($layoutClasses['footerFixed'] == true) {
      $layoutClasses['footerFixed'] = 'layout-footer-fixed';
    }

    // RTL Supported template
    if ($layoutClasses['rtlSupport'] == true) {
      $layoutClasses['rtlSupport'] = '/rtl';
    }

    // RTL Layout/Mode
    if ($layoutClasses['rtlMode'] == true) {
      $layoutClasses['rtlMode'] = 'rtl';
      $layoutClasses['textDirection'] = isset($_COOKIE['direction']) ? ($_COOKIE['direction'] === "true" ? 'rtl' : 'ltr') : 'rtl';

    } else {
      $layoutClasses['rtlMode'] = 'ltr';
      $layoutClasses['textDirection'] = isset($_COOKIE['direction']) && $_COOKIE['direction'] === "true" ? 'rtl' : 'ltr';

    }

    // Show DropdownOnHover for Horizontal Menu
    if ($layoutClasses['showDropdownOnHover'] == true) {
      $layoutClasses['showDropdownOnHover'] = true;
    } else {
      $layoutClasses['showDropdownOnHover'] = false;
    }

    // To hide/show display customizer UI, not js
    if ($layoutClasses['displayCustomizer'] == true) {
      $layoutClasses['displayCustomizer'] = true;
    } else {
      $layoutClasses['displayCustomizer'] = false;
    }

    return $layoutClasses;
  }

  public static function updatePageConfig($pageConfigs)
  {
    $demo = 'custom';
    if (isset($pageConfigs)) {
      if (count($pageConfigs) > 0) {
        foreach ($pageConfigs as $config => $val) {
          Config::set('custom.' . $demo . '.' . $config, $val);
        }
      }
    }
  }
  public static function logAction($module, $client_id, $action, $data, $user_id, $created_date = null, $updated_date = null)
  {
      Log::create([
          'module' => $module,            // The module being logged (e.g., 'Client')
          'client_id' => $client_id,      // The associated client ID
          'action' => $action,            // Action type (create, update, delete)
          'data' => json_encode($data),   // Store data as JSON (old or new data)
          'created_by' => $user_id,
          'updated_by' => $user_id,          // User who performed the action
          'created_date' => $created_date, // Record creation timestamp
          'updated_date' => $updated_date, // Record update timestamp
      ]);
  }
  public static function getDepartmentsNotAssignedToUser()
    {
      $loggedInUser = Auth::user();

      // Fetch all departments
      $allDepartments = Department::all();
      
      // Get the department IDs that the logged-in user is associated with from the 'users_departments' pivot table
      $assignedDepartments = DB::table('users_departments')
          ->where('user_id', $loggedInUser->id) // Filter by logged-in user
          ->pluck('department_id')->toArray(); // Get all department_ids assigned to this user
      
      // Filter departments that are assigned to the logged-in user
      $departmentsAssigned = $allDepartments->filter(function($department) use ($assignedDepartments) {
          return in_array($department->id, $assignedDepartments); // Keep departments that are assigned
      });
      if (!session('selected_department') && $departmentsAssigned->isNotEmpty()) {
        // If no department is selected in the session, set the first department as selected
        session(['selected_department' => $departmentsAssigned->first()->id]);
      }

        return $departmentsAssigned;
    }
  public static function getPermissionsByDepartments($departmentIds)
    {
      if($departmentIds){
        $permissions = DB::table('department_role_permissions')
            ->where('department_id', $departmentIds)
            ->get(); // Assuming permissions are stored with permission_id and department_id
            Session::put('selected_departments', $departmentIds);   
          return $permissions;
      } else{
        return collect();
      }
        // Fetch permissions from department_role_permissions based on the provided department IDs
    }
  public static function switchDepartment($departmentId)
    {
     
      $user = auth()->user();  // Get the authenticated user
        
      // Store the selected department in session
      Session::put('selected_department', $departmentId);

      // Step 1: Fetch the user's roles for the selected department
      $roleIds = DB::table('users_departments')
          ->where('user_id', $user->id)
          ->where('department_id', $departmentId)
          ->pluck('role_id')
          ->toArray();

      // Step 2: Fetch the permissions for those roles in the selected department
      $permissions = DB::table('department_role_permissions')
          ->whereIn('role_id', $roleIds)
          ->where('department_id', $departmentId)
          ->pluck('permission_id')
          ->toArray();

      // Step 3: Sync the permissions to the current user
      $user->syncPermissions(Permission::find($permissions));

    }

}

