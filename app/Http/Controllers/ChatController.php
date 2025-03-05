<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
  public function index()
  {
    $users = $this->getAllUserList();
    return view("chat.index", compact('users'));
  }



  // Fetch messages between two users
  public function fetchMessages($receiverId)
  {

    $messages = Chat::where(function ($query) use ($receiverId) {
      $query->where('sender_id', Auth::id())->where('receiver_id', $receiverId);
    })
      ->orWhere(function ($query) use ($receiverId) {
        $query->where('sender_id', $receiverId)->where('receiver_id', Auth::id());
      })
      ->orderBy('created_at', 'asc')
      ->get();
    return response()->json(['messages' => $messages, 'loggedInUserID' => Auth::id()]);
  }

  // Store a new message
  public function sendMessage(Request $request)
  {

    $request->validate([
      'receiver_id' => 'required|exists:users,id',
      'message' => 'required|string',
    ]);

    $chat = Chat::create([
      'sender_id' => Auth::id(),
      'receiver_id' => $request->receiver_id,
      'message' => $request->message,
    ]);

    return response()->json($chat);
  }

  //  Get all Users
  public function getAllUserList()
  {
    $users = User::whereNull('deleted_at')
      ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
      ->get(['users.id', 'users.name', 'users.profile_photo_path', 'roles.name as role_name'])
      ->sortBy('name');

    return $users;
  }

  // When Click on user's chat it fetch the receiver user's details
  public function getSpecificUserDetail($receiverId)
  {
    $user = User::find($receiverId);
    if (!$user) {
      return response()->json(['error' => 'User not found'], 404);
    } else {
      $name = explode(' ', $user->name);
      $initials = strtoupper($name[0][0] . (isset($name[1]) ? $name[1][0] : ''));
      $role_id = $user->role_id;
      if ($role_id != null) {
        $userRole = Role::find($role_id);
        return response()->json(['user' => $user, 'usernameimg' => $initials, 'role' => $userRole->name], 200);
      }
      return response()->json(['user' => $user, 'usernameimg' => $initials], 200);
    }
  }
}
