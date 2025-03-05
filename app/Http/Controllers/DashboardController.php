<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Department;
use App\Models\Quote;
use App\Models\Assignment;
use App\Models\AssignmentUser;
use App\Models\QuoteUser;
class DashboardController extends Controller
{
    //
    public function index()
    {
        // Get the logged-in user
        $user = auth()->user();
        
        // Get the user's role_id from the user_departments table
        // Assuming user has a relationship with departments and role_id is stored in the pivot table
        $userDepartment = $user->departments()->whereNull('users_departments.deleted_at')->first();
        // Check if the user has an admin role (assuming role_id 2 is for admin)
        $isAdmin = ($userDepartment && $userDepartment->pivot->role_id == 2); // Adjust if needed based on how role_id is stored in pivot
        
        // If the user is an admin, show all records; otherwise, filter by user ID
        $userCount = Client::whereNull('deleted_at')->count();
        $departmentCount = Department::whereNull('deleted_at')->count();
        $quoteCount = $isAdmin ? Quote::whereNull('deleted_at')->count() : Quote::whereNull('deleted_at')->where('created_by', $user->id)->count();
        if($quoteCount == 0){
            $quoteCount = QuoteUser::where('user_id',$user->id)->whereNull('deleted_at')->count();
        }
        $assignmentCount = $isAdmin ? Assignment::whereNull('deleted_at')->count() : Assignment::whereNull('deleted_at')->where('created_by', $user->id)->count();
        
        if($assignmentCount == 0){
            $assignmentCount = AssignmentUser::where('user_id',$user->id)->whereNull('deleted_at')->count();
        }
        // Pass the counts to the view
        return view('dashboard', compact('userCount', 'departmentCount', 'quoteCount', 'assignmentCount'));
    }
    
}
