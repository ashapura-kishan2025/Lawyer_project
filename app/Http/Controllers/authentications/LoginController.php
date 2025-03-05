<?php

namespace App\Http\Controllers\authentications;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;

class LoginController extends Controller
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.authentications.auth-login-basic', ['pageConfigs' => $pageConfigs]);
  }
  public function getErrorPage()
  {
    $pageConfigs = ['myLayout' => 'blank'];
    return view('error.custom-error', ['pageConfigs' => $pageConfigs]);
  }
  public function login(Request $request)
  {
    $request->validate([
      'email' => 'required|email',
      'password' => 'required|min:6',
    ]);

    $credentials = $request->only('email', 'password');
    $remember = $request->has('remember');

    if (Auth::attempt($credentials)) {
      $user = Auth::user();
      // Get the authenticated user
      if ($user->status == "inactive") {
        activity('auth')
                ->causedBy($user)
                ->log("Inactive user attempted login: {$user->email}");
        Auth::logout(); // Logout the inactive user
        return redirect()->route('auth-login-basic')->withErrors(['status_inactive' => 'You are no longer active']);
      }
      activity('auth')
            ->causedBy($user)
            ->withProperties(['ip' => $request->ip()])
            ->log("User logged in: {$user->email}");

      $user->last_login_ip = request()->ip();
      $user->last_login_at = now();
      $user->save();
      return redirect()->route('dashboard'); // Redirect active users to dashboard
    }
    activity('auth')
        ->withProperties(['email' => $request->email, 'ip' => $request->ip()])
        ->log("Failed login attempt for email: {$request->email}");

    return redirect()->route('auth-login-basic')->withErrors(['email' => 'Invalid credentials']);
  }

  // Log out function
  public function logout(Request $request)
  {
    $user = Auth::user();

    if ($user) {
        // Log user logout
        activity('auth')
            ->causedBy($user)
            ->withProperties(['ip' => $request->ip()])
            ->log("User logged out: {$user->email}");
    }

    Auth::logout();

    $request->session()->invalidate();

    $request->session()->regenerateToken();

    return redirect('login');
  }
}
