<?php

use App\Http\Controllers\pages\Page2;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\UserController;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\authentications\LoginController;
use App\Helpers\Helpers;
// Main Page Route
Route::redirect('/', '/login')->middleware('guest');
Route::get('/home', [HomePage::class, 'index'])->name('pages-home');
Route::get('/page-2', [Page2::class, 'index'])->name('pages-page-2');

// locale
Route::get('/lang/{locale}', [LanguageController::class, 'swap']);
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');

// User login and forget password and new passsword route
// Or guest routes
Route::middleware('guest')->group(function () {

  Route::get('/login', [LoginController::class, 'index'])->name('auth-login-basic')->middleware('guest');
  Route::post('/login', [LoginController::class, 'login'])->name('login');
  Route::get('/forget-pass', [UserController::class, 'forgetPasswordView'])->name('forget-password');
  Route::post('/send-mail', [UserController::class, 'sendEmail'])->name('send-forget-pass-email');

  Route::get('/reset-password/{token}', function (string $token) {
    return view('reset-password', ['token' => $token]);
  })->name('password.reset');
  Route::post("/reset-password", [UserController::class, 'resetPassword'])->name('password.update');
  Route::get("/password-updated", [UserController::class, 'passwordChangeBackToLogin'])->name('password.passwordChangeBackToLogin');
});

Route::get("/custom-error", [LoginController::class, 'getErrorPage'])->name('custom.error.page');


// Dashboard
Route::middleware('auth')->group(function () {
  Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
  Route::get('dashboard/getCountOfClients', [DashboardController::class, 'totalCountOfClients'])->name('dashboards.totalClient');
  Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

  Route::post('/switch-department', function (Illuminate\Http\Request $request) {
    $departmentId = $request->input('department_ids');

    // Call the helper function to switch departments and sync permissions
    Helpers::switchDepartment($departmentId);

    // Return a response (success or error)
    return response()->json(['status' => 'success']);
  })->name('switch.department');
});
// Route::group(['middleware' => ['auth', 'role:admin']], function() {

// });
// Departments, Countries, Currencies, Client
// Route::middleware('auth')->group(function () {
Route::group(['middleware' => ['auth']], function () {
  Route::get('/permissions', [PermissionController::class, 'index'])->name('role.index');
  Route::post('/permissions/create-role', [PermissionController::class, 'createRole'])->name('permissions.createRole');
  Route::post('/permissions/create-permission', [PermissionController::class, 'createPermission'])->name('permissions.createPermission');
  Route::post('/permissions/assign-role-to-user', [PermissionController::class, 'assignRoleToUser'])->name('permissions.assignRoleToUser');
  Route::post('/permissions/revoke-role-from-user', [PermissionController::class, 'revokeRoleFromUser'])->name('permissions.revokeRoleFromUser');
  Route::post('/permissions/assign-permission-to-role', [PermissionController::class, 'assignPermissionToRole'])->name('permissions.assignPermissionToRole');
  Route::post('/permissions/revoke-permission-from-role', [PermissionController::class, 'revokePermissionFromRole'])->name('permissions.revokePermissionFromRole');
  Route::get('/permission-edit/{id}', [PermissionController::class, 'edit'])->name('permissions.edit');
  Route::put('/permission-update/{id}', [PermissionController::class, 'update'])->name('permissions.update');

  // Department Conrtoller
  Route::resource('departments', DepartmentController::class);
  Route::POST('departments/statusUpdate', [DepartmentController::class, 'inlineStatusChange'])->name('inlineStatusChangeOfDepartment');

  // Currency Conrtoller
  Route::resource('currencies', CurrencyController::class);
  Route::POST('currencies/statusUpdate', [CurrencyController::class, 'inlineStatusChange'])->name('inlineStatusChangeOfCurrency');

  // Country Conrtoller
  Route::resource('country', CountryController::class);
  Route::POST('country/statusUpdate', [CountryController::class, 'inlineStatusChange'])->name('inlineStatusChangeOfCountry');

  // Client Conrtoller
  // Route::get('/client/getCurrency', [ClientController::class, 'getCurrency'])->name('getCurrency');
  Route::get('client/getCurrency', [ClientController::class, 'getCurrency'])->name('getCurrency');
  Route::get('client/getcountry', [ClientController::class, 'getCountry'])->name('getCountry');
  Route::get('client/getsources', [ClientController::class, 'getSource'])->name('getSource');
  Route::get('client/getfiledsouces', [ClientController::class, 'getSourceFiled'])->name('getSource.field');
  Route::resource('client', ClientController::class);
  Route::GET('/view/{id}', [ClientController::class, 'view'])->name('client.view');
  Route::GET('/getquoteview', [ClientController::class, 'getQuotesforView'])->name('client.quote.view');
  Route::GET('/getassignmentview', [ClientController::class, 'getAssignmentDataforclient'])->name('client.assignment.view');
  Route::GET('/getclientwisedepartment', [ClientController::class, 'getDepartmentClientWise'])->name('get.client.department');

  // Quote Section
  Route::prefix('quotes')->group(function () {
    Route::get('/', [QuoteController::class, 'index'])->name('quotes.index');
    Route::get('/data', [QuoteController::class, 'getQuotes'])->name('quotes.getData');
    Route::get('/create', [QuoteController::class, 'create'])->name('quotes.create');
    Route::POST('/store', [QuoteController::class, 'store'])->name('quotes.store');
    Route::GET('/getDepartmentData', [QuoteController::class, 'getDepartment'])->name('quotes.getDepartmentData');
    Route::GET('/getCurrencyData', [QuoteController::class, 'getCurrency'])->name('quotes.getCurrency');
    Route::GET('/getClientData', [QuoteController::class, 'getClient'])->name('quotes.getClient');
    Route::DELETE('/destroy/{id}', [QuoteController::class, 'destroy'])->name('quotes.destroy');
    Route::GET('/edit/{id}', [QuoteController::class, 'edit'])->name('quotes.edit');
    Route::PUT('/update', [QuoteController::class, 'update'])->name('quotes.update');
    Route::GET('/getusercurrency', [QuoteController::class, 'getUserCurrency'])->name('get.users.currency');
    Route::GET('/view/{id}', [QuoteController::class, 'view'])->name('quotes.view');
    Route::get('/get-user-department', [QuoteController::class, 'getUserDepartment'])->name('get.users.departments');
    Route::post('/quote-users', [QuoteController::class, 'userstore'])->name('quote_users.store');
  });

  //Assignment
  Route::prefix('assignment')->group(function () {
    Route::get('/', [AssignmentController::class, 'index'])->name('assignment.index');
    Route::get('/data', [AssignmentController::class, 'getassignmentData'])->name('assignment.getData');
    Route::get('/create', [AssignmentController::class, 'create'])->name('assignment.create');
    Route::POST('/store', [AssignmentController::class, 'store'])->name('assignment.store');
    Route::get('getquotesforassignment', [AssignmentController::class, 'getQuotesForAssignment'])->name('get.assignment.quote');
    Route::GET('/getDepartmentData', [AssignmentController::class, 'getDepartment'])->name('assignment.getDepartmentData');
    Route::GET('/getCurrencyData', [AssignmentController::class, 'getCurrency'])->name('assignment.getCurrency');
    Route::GET('/getClientData', [AssignmentController::class, 'getClient'])->name('assignment.getClient');
    Route::DELETE('/destroy/{id}', [AssignmentController::class, 'destroy'])->name('assignment.destroy');
    Route::GET('/edit/{id}', [AssignmentController::class, 'edit'])->name('assignment.edit');
    Route::PUT('/update', [AssignmentController::class, 'update'])->name('assignment.update');
    Route::get('/getusersforassignment', [AssignmentController::class, 'getusersForAssignment'])->name('get.users.assignment');
    Route::get('/getusersrate', [AssignmentController::class, 'getUsersRate'])->name('get.users.rate');
    Route::GET('/view/{id}', [AssignmentController::class, 'view'])->name('assignment.view');
    Route::get('/get-user-department', [AssignmentController::class, 'getUserDepartment'])->name('assignment.get.users.departments');
    Route::post('/quote-users', [AssignmentController::class, 'userstore'])->name('assignment_users.store');
    Route::post('/assignment-memo', [AssignmentController::class, 'getAssignedTasksMemo'])->name('assignment.memo');
    Route::post('/timekeep-memo', [AssignmentController::class, 'getAssignedTimekeepMemo'])->name('assignment.memo.timkeep');
  });

  Route::prefix('user')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('user.index');
    Route::get('/getusers', [UserController::class, 'getUsers'])->name('user.getData');
    Route::get('/create', [UserController::class, 'create'])->name('user.create');
    Route::POST('/store', [UserController::class, 'store'])->name('user.store');
    Route::GET('/getDepartmentDataUser', [UserController::class, 'getDepartment'])->name('user.getDepartmentData');
    Route::GET('/getCurrencyDataUser', [UserController::class, 'getCurrency'])->name('user.getCurrency');
    Route::GET('/getroleuser', [UserController::class, 'getRole'])->name('user.getRole');
    Route::DELETE('/destroy/{id}', [UserController::class, 'destroy'])->name('user.destroy');
    Route::GET('/edit/{id}', [UserController::class, 'edit'])->name('user.edit');
    Route::PUT('/update/{id}', [UserController::class, 'update'])->name('user.update');
    Route::get('/user/check-email', [UserController::class, 'checkEmail'])->name('user.checkEmail');
    Route::POST('users/statusUpdate', [UserController::class, 'userinlineStatusChange'])->name('user.status');
  });

  // Change Password
  Route::POST('/change-password', [UserController::class, "changePassword"])->name("changePassword");
  Route::view('/change-password', 'change-password')->name('changePasswordView');

  //changes chat
  Route::prefix('chat')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/users', [ChatController::class, 'getAllUserList'])->name('chat.usersList');
    Route::get('/{receiverId}', [ChatController::class, 'fetchMessages'])->name('chat.fetchMessage');
    Route::get('/receiver/{receiverId}', [ChatController::class, 'getSpecificUserDetail'])->name('chat.getSpecificUserDetail');
    Route::post('/send', [ChatController::class, 'sendMessage'])->name('chat.sendMessage');
  });
});
