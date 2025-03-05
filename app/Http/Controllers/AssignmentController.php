<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Assignment;
use App\Models\AssignmentTask;
use App\Models\AssignmentTimekeep;
use App\Models\Currency;
use App\Models\Quote;
use App\Models\Client;
use App\Models\User;
use App\Models\AssignmentUser;
use GrahamCampbell\ResultType\Success;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;
use Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use App\Helpers\Helpers;

class AssignmentController extends Controller
{
  public function __construct()
  {
    // Middleware to check if the user has the relevant permissions
    $this->middleware('permission:ASSIGNMENT.LIST', ['only' => ['index']]);
    $this->middleware('permission:ASSIGNMENT.VIEW', ['only' => ['view']]);
    $this->middleware('permission:ASSIGNMENT.CREATE', ['only' => ['create','store']]);
    $this->middleware('permission:ASSIGNMENT.EDIT', ['only' => ['edit', 'update']]);
    $this->middleware('permission:ASSIGNMENT.DELETE', ['only' => ['destroy']]);
  }
  //This is view of assignment(list)
  public function index()
  {

    return view('assignment.index');
  }
  public function create()
  {
    $store_assignment = Assignment::create([
      // 'client_id' => 0,
      // 'quote_id' => 0,
      // 'ledger' => 0,
      'created_by' => 0,
      'updated_by' => 0,
      'updated_at' => now(),
      'created_at' => now(),
    ]);
    $id = $store_assignment['id'] ?? '';
    $department = Department::all()->pluck('name', 'id');
    $client = Client::all()->pluck('client', 'id');
    $user_data = User::whereNull('deleted_at')->get();
    $quote = Quote::leftJoin('assignments', 'quotes.id', '=', 'assignments.quote_id')
    ->whereNull('assignments.quote_id')  // Ensure quote_id is NULL in assignments
    ->pluck('quotes.id');
    $user = auth()->user();
    $user_departments = $user->departments;
    return view('assignment.create', ['id'=>$id,'departments' => $department, 'client' => $client, 'user_data' => $user_data,'quote' => $quote,'user_departments' => $user_departments]);
  }
  public function getAssignmentData(Request $request)
  {
      $user = auth()->user();  // Get the logged-in user

      // Get the user's role_id from the user_departments table
      $userRole = DB::table('users_departments')
          ->where('user_id', $user->id)
          ->first();  // Assuming the user has only one entry in the users_departments table

      // Check if the role_id is available
      if ($userRole) {
          $isAdmin = ($user->role_id == 2); // Assuming role_id 2 is for admin users
      } else {
          // Handle the case where role_id is not found, perhaps default to a role or throw an error
          $isAdmin = null;
      }

      // Start building the query using the DB Query Builder
      $quotes = DB::table('assignments')
          ->join('clients', 'assignments.client_id', '=', 'clients.id')
          ->join('users as created_by', 'assignments.created_by', '=', 'created_by.id')
          ->leftJoin('assignment_tasks', 'assignment_tasks.assignment_id', '=', 'assignments.id')
          ->leftJoin('departments', 'assignment_tasks.department_id', '=', 'departments.id')
          ->leftJoin('quotes', 'quotes.id', '=', 'assignments.quote_id')
          ->leftJoin('currencies', 'clients.currency_id', '=', 'currencies.id')
          // Fetch the latest assignment_users record using subquery
          ->when(!$isAdmin, function($query) use ($user) {
              // For regular users, select the last entry from the assignment_users table for the logged-in user
              $query->leftJoin('assignment_users', function ($join) use ($user) {
                  $join->on('assignments.id', '=', 'assignment_users.assignment_id')
                      ->where('assignment_users.user_id', '=', $user->id)
                      ->whereNull('assignment_users.deleted_at')
                      ->whereRaw('assignment_users.id = (SELECT MAX(id) FROM assignment_users WHERE assignment_id = assignments.id)');
              });
          })
          ->when($isAdmin, function($query) {
              // For admins, join all records in assignment_users
              $query->leftJoin('assignment_users', 'assignments.id', '=', 'assignment_users.assignment_id')
                  ->whereNull('assignment_users.deleted_at');
          })
          ->select(
              'assignments.id',
              'clients.client as client_name',
              'created_by.name as created_by',
              'assignments.created_at as created_date',
              'assignments.quote_id as quote_id',
              'assignments.description',
              'assignments.status',
              'assignments.ledger',
              'assignments.approved_by',
              'currencies.code as currency',
              DB::raw('COALESCE(SUM(assignment_tasks.amount), 0) as amount'),
              DB::raw('GROUP_CONCAT(DISTINCT departments.name SEPARATOR ", ") as department_name'),
              'assignment_users.access_level' // Access level from the latest assignment_users record
          );

      // For non-admin users, filter based on their access level
      if (!$isAdmin) {
          $quotes->where(function ($query) use ($user) {
              $query->where('assignment_users.user_id', '=', $user->id)
                  ->whereIn('assignment_users.access_level', ['edit', 'read'])  // Only show assignments with 'edit' or 'read' access
                  ->orWhere('assignments.created_by', '=', $user->id); // Include assignments created by the logged-in user
          });
      }

      // Apply filters based on request parameters
      if ($request->get('status')) {
          $quotes->where('assignments.status', $request->get('status'));
      }

      if ($request->get('client_name')) {
          $quotes->where('clients.client', 'like', '%' . $request->get('client_name') . '%');
      }

      if ($request->get('department')) {
          $quotes->where('assignment_tasks.department_id', $request->get('department'));
      }

      if ($request->get('start_date') && $request->get('end_date')) {
          // Parse the start and end dates using Carbon
          $startDate = Carbon::parse($request->get('start_date'))->startOfDay();
          $endDate = Carbon::parse($request->get('end_date'))->endOfDay();
          // Apply the date range filter
          $quotes->whereBetween('assignments.created_at', [$startDate, $endDate]);
      }

      // Group by necessary columns to avoid duplicates
      $quotes->groupBy(
          'assignments.id',
          'clients.client',
          'created_by.name',
          'assignments.created_at',
          'assignments.quote_id',
          'assignments.description',
          'assignments.status',
          'assignments.ledger',
          'quotes.id',
          'currencies.code',
          'assignments.approved_by',
          'assignment_users.access_level'
      )
      ->whereNull('assignment_tasks.deleted_at')
        ->whereNull('assignments.deleted_at');;

      // Sorting by creation date
      $quotes->orderBy('assignments.created_at', 'desc')->whereNull('assignments.deleted_at');

      // Get the data and return as a DataTable response
      $data = $quotes->get();

      return DataTables::of($data)
          ->addColumn('action', function ($row) use ($isAdmin, $user) {
              if ($isAdmin) {
                  return '
                      <i class="fa-solid fa-eye view-assignment-btn me-2" style="font-size: 14px;color: #cb9a59; cursor: pointer;" data-id="' . $row->id . '"></i>
                      <i class="fa-sharp fa-solid fa-pen edit-assignment-btn" style="font-size: 14px; color:#192b53;cursor: pointer;" data-id="' . $row->id . '"></i>
                      <i class="ti ti-trash delete-assignment-btn" style="font-size: 20px; color: red; cursor: pointer;" data-id="' . $row->id . '"></i>
                  ';
              }
              if ($row->approved_by == $user->id) {
                  return '
                      <i class="fa-solid fa-eye view-assignment-btn me-2" style="font-size: 14px;color: #cb9a59; cursor: pointer;" data-id="' . $row->id . '"></i>
                      <i class="fa-sharp fa-solid fa-pen edit-assignment-btn" style="font-size: 14px; color:#192b53;cursor: pointer;" data-id="' . $row->id . '"></i>
                      <i class="ti ti-trash delete-assignment-btn" style="font-size: 20px; color: red; cursor: pointer;" data-id="' . $row->id . '"></i>
                  ';
              }

              if (isset($row->access_level) && $row->access_level == 'edit') {
                  return '
                      <i class="fa-solid fa-eye view-assignment-btn me-2" style="font-size: 14px;color: #cb9a59; cursor: pointer;" data-id="' . $row->id . '"></i>
                      <i class="fa-sharp fa-solid fa-pen edit-assignment-btn" style="font-size: 14px; color:#192b53;cursor: pointer;" data-id="' . $row->id . '"></i>
                  ';
              } elseif (isset($row->access_level) && $row->access_level == 'read')  {
                  return ''; // No actions for read-only access
              } else {
                  return ''; // No access
              }
          })
          ->editColumn('status', function ($row) {
              if ($row->status == "completed") {
                  return '<span class="badge bg-label-success inlineStatus" style="cursor: pointer;">Completed</span>';
              } elseif ($row->status == "processing") {
                  return '<span class="badge bg-label-primary inlineStatus" style="cursor: pointer;">Processing</span>';
              } elseif ($row->status == "pending") {
                  return '<span class="badge bg-label-warning inlineStatus" style="cursor: pointer;">Pending</span>';
              }
          })
          ->editColumn('description', function ($row) {
              return Str::limit(strip_tags($row->description), 25, '...');
          })
          ->addColumn('department', function ($row) {
              return $row->department_name ?? ''; // Return department name if exists
          })
          ->addColumn('quote', function ($row) {
              return $row->quote_id ?? ''; // Return quote ID if exists
          })
          ->addColumn('client', function ($row) {
              return $row->client_name ?? ''; // Return client name if exists
          })
          ->addColumn('amount', function ($row) {
              return $row->amount ?? '0'; // Return amount, default to 0 if no data
          })
          ->addColumn('currency', function ($row) {
              return $row->currency ?? ''; // Return currency code if exists
          })
          ->editColumn('ledger', function ($row) {
              return $row->ledger ?? ''; // Return ledger value
          })
          ->editColumn('created_date', function ($row) {
              return Carbon::parse($row->created_date)->format('d M, Y'); // Format the created date
          })
          ->rawColumns(['status', 'action', 'department', 'currency', 'quote', 'client', 'amount'])
          ->make(true);
  }





  public function getDepartment()
  {
    $department = Department::whereNull('deleted_at')->pluck('name', 'id');
    return response()->json($department);
  }
  public function getClient()
  {
    $client = Client::whereNull('deleted_at')->pluck('client', 'id');
    return response()->json($client);
  }
  public function getQuotesForAssignment()
  {
    $getQuotes = Quote::where('status', 'quoted')->whereNull('deleted_at')->get();
    return response()->json($getQuotes);
  }
  public function getusersForAssignment()
  {
    $getQuotes = User::whereNull('deleted_at')->get();
    return response()->json($getQuotes);
  }
  public function getUsersRate(Request $request)
  {
    $id = $request->id ?? '';
    $user = User::find($id);
    $rate = $user->rate ?? '';
    return response()->json(['rate' => $rate]);
  }
  public function store(Request $request)
  {
    if ($request->ajax()) {
      $data = $request->all();
      // Validate the input data
      $validated = $request->validate([
        'client_id' => 'required|exists:clients,id',
        // 'ledger' => 'required|unique:ledger',
        // 'status' => 'required|in:quoted,awarded,lost',
        // 'description' => 'required',
        // 'amounts' => 'array',
        // 'amounts.*' => 'nullable|numeric',
        // 'descriptions' => 'array',
        // 'descriptions.*' => 'nullable|string',
      ]);

      // Ensure amounts, currencies, and descriptions are not null
      // $hasValidTasks = !empty(array_filter($data['amounts'] ?? []))  &&
      //   !empty(array_filter($data['descriptions'] ?? []));

      // if (!$hasValidTasks) {
      //   return response()->json(['error' => 'Amounts and descriptions cannot all be null'], 400);
      // }

      try {
        // Start database transaction

        $user = Auth::user();
        $id = $request->id ?? '';
        $assignmentAdd = Assignment::find($id);
        // Insert into quotes table
        if ($assignmentAdd) {
          $assignmentAdd->assignment_type = $request['selectedValue'];
          $assignmentAdd->description = $request['description'] ?? null;
          $assignmentAdd->status = $request['status'];
          $assignmentAdd->client_id = $request['client_id'];
          $assignmentAdd->quote_id = $request['quote'];
          $assignmentAdd->ledger = $request['ledger'];
          $assignmentAdd->created_by = auth()->user()->id;
          $assignmentAdd->approved_by = auth()->user()->id;
          $assignmentAdd->updated_at = now();
          $assignmentAdd->created_at = now();
          // Save the updated record
          $assignmentAdd->save();
          $assignment_log_data = $assignmentAdd->toArray(); // This converts the assignment model to an array
          $user_id = auth()->user()->id; // The currently logged-in user
          $created_date = $assignmentAdd->created_at; // You can use the assignment's created_at field
          $updated_date = $assignmentAdd->updated_at; // You can use the assignment's updated_at field

          // Log the action
          // Helpers::logAction(
          //     'Assignment',                // module
          //     $request['client_id'],       // client_id
          //     'store',                     // action (store, update, delete, etc.)
          //     $assignment_log_data,                       // data (the assignment data, or any relevant data you want to log)
          //     $user_id,
          //     $user_id,                    // user_id (currently logged-in user)
          //     $created_date,               // created_date (optional)
          //     $updated_date                // updated_date (optional)
          // );
        }

        $data = $request->all();
        $client = Client::find($request->client_id);

        $amounts = $data['amounts'] ?? [];
        $descriptions = $data['descriptions'] ?? [];
        $departmentIds = $data['department_ids'] ?? [];
        $receivedAmounts = $data['received_amount'] ?? [];

        foreach ($amounts as $index => $amount) {
            // Ensure that all arrays have data for this index
            if (
                isset($amounts[$index]) && !is_null($amounts[$index]) &&
                isset($descriptions[$index]) && !is_null($descriptions[$index]) &&
                isset($departmentIds[$index]) && !is_null($departmentIds[$index]) &&
                isset($receivedAmounts[$index]) && !is_null($receivedAmounts[$index])
            ) {
                // Create the AssignmentTask instance
                $assignmentTask = new AssignmentTask();
                $assignmentTask->assignment_id = $id;
                $assignmentTask->description = isset($descriptions[$index]) ? $descriptions[$index] : null;
                $assignmentTask->amount = isset($amounts[$index]) ? $amounts[$index] : null;
                $assignmentTask->currency_id = $client->currency_id ?? null;
                $assignmentTask->received_amount = isset($receivedAmounts[$index]) ? $receivedAmounts[$index] : null;
                $assignmentTask->department_id = isset($departmentIds[$index]) ? $departmentIds[$index] : null;
                $assignmentTask->created_by = auth()->user()->id;
                $assignmentTask->updated_by = auth()->user()->id;
                $assignmentTask->updated_at = now();
                $assignmentTask->created_at = now();

                // Save the new assignment task
                $assignmentTask->save();
                $assignment_log_data = $assignmentTask->toArray(); // Convert the task model to an array
                $user_id = auth()->user()->id; // The currently logged-in user
                $created_date = $assignmentTask->created_at; // Use the created_at field of the task
                $updated_date = $assignmentTask->updated_at; // Use the updated_at field of the task

                // Log the action for this AssignmentTask
                // Helpers::logAction(
                //     'Assignment Task',           // module
                //     $request['client_id'],       // client_id
                //     'store',                     // action (store, update, delete, etc.)
                //     $assignment_log_data,        // data (assignment task data)
                //     $user_id,
                //     $user_id,                    // user_id (currently logged-in user)
                //     $created_date,               // created_date (optional)
                //     $updated_date                // updated_date (optional)
                // );
            }
        }

        // dd($data['userValue']);
        // Insert into assignment_timekeep table
        if ($data['userValue']) {
          foreach ($data['userValue'] as $index => $userId) {
            if (
              !is_null($userId) && !is_null($data['timekeep_descriptions'][$index]) && !is_null($data['user_rate'][$index])
              && !is_null($data['user_total'][$index]) && !is_null($data['timekeep_qty'][$index])
            ) {
              // !is_null($data['descriptions'][$index]
              $assignmentTimeKeep = new AssignmentTimekeep();
              $assignmentTimeKeep->assignment_id = $id;
              $assignmentTimeKeep->user_id = $userId;
              $assignmentTimeKeep->description =  isset($data['timekeep_descriptions'][$index]) ? $data['timekeep_descriptions'][$index] : null;
              $assignmentTimeKeep->quantity =  isset($data['timekeep_qty'][$index]) ? $data['timekeep_qty'][$index] : null;
              $assignmentTimeKeep->rate =  isset($data['user_rate'][$index]) ? $data['user_rate'][$index] : null;
              $assignmentTimeKeep->amount =isset($data['user_total'][$index]) ? $data['user_total'][$index] : null;
              // $assignmentTask->status = $request['status'] ?? '';
              // $assignmentTimeKeep->created_by = auth()->user()->id;
              $assignmentTimeKeep->updated_by = auth()->user()->id;
              $assignmentTimeKeep->updated_at = now();
              $assignmentTimeKeep->created_at = now();
              // Save the updated record
              $assignmentTimeKeep->save();

              $assignment_log_data = $assignmentTimeKeep->toArray(); // Convert the task model to an array
              $user_id = auth()->user()->id; // The currently logged-in user
              $created_date = $assignmentTimeKeep->created_at; // Use the created_at field of the task
              $updated_date = $assignmentTimeKeep->updated_at; // Use the updated_at field of the task

                // Log the action for this AssignmentTask
                // Helpers::logAction(
                //   'Assignment Timekeep',           // module
                //   $request['client_id'],       // client_id
                //   'store',                     // action (store, update, delete, etc.)
                //   $assignment_log_data,        // data (assignment task data)
                //   $user_id,
                //   $user_id,                    // user_id (currently logged-in user)
                //   $created_date,               // created_date (optional)
                //   $updated_date                // updated_date (optional)
                // );
            }
          }
        }
        return response()->json(['success' => 'Assignment stored successfully', 'redirect' => route('assignment.index')]);
      } catch (\Exception $e) {
        // Rollback on error
        DB::rollBack();
        return response()->json(['error' => 'Failed to store quote: ' . $e->getMessage()], 500);
      }
    }

    // return response()->json(['error' => 'Invalid request'], 400);
  }
  public function edit($id)
  {

    // $id = base64_decode($id);
    $user = User::select('users.id', 'users.name', 'users.email')
    ->join('users_departments', 'users.id', '=', 'users_departments.user_id')  // Join with the user_departments table
    ->where('users_departments.role_id', 1)  // Filter based on the role_id in user_departments
    ->distinct()  // Ensures unique users
    ->get();

    // Fetch all department ids the user belongs to
    // $userDepartmentIds = $user->departments->pluck('id'); // Get an array of department IDs

    // // Fetch the most recent access level for the logged-in user based on quote_id, user_id, and department_id
    // $quoteUserAccess = DB::table('assignment_users')
    //     ->where('assignment_id', $id)
    //     ->where('user_id', $user->id)
    //     ->whereIn('department_id', $userDepartmentIds) // Check if any of the user's departments match
    //     ->orderBy('assignment_users.updated_at', 'desc') // Get the most recent access level
    //     ->first(); // Get the latest record
        $access_user = User::select('id', 'name', 'email')->where('role_id', 1)->get();
    // If the user is neither an admin nor has the edit access, deny access to the edit page

    $department = Department::all()->pluck('name', 'id');
    $client = Client::all()->pluck('client', 'id');
    $user = User::all()->pluck('name', 'id');
    $assignment = Assignment::findOrFail($id);
    $quote_id = '';
    if($assignment->quote_id)
    {
      $quote_id = $assignment->quote_id;
    }

    $quote = Quote::leftJoin('assignments', 'quotes.id', '=', 'assignments.quote_id')
    ->whereNull('assignments.quote_id')  // Ensure quote_id is NULL in assignments
    ->pluck('quotes.id');
    $totalAmount = $assignment->tasks->sum('amount');
    $received_amount = $assignment->tasks->sum('received_amount');
    $amountDifference = $totalAmount - $received_amount;
    $currency = Client::find($assignment->client_id)->currency_id;
    $currency_data = Currency::find($currency);
    $assignment_users = AssignmentUser::with('user', 'department')->where('assignment_id', $id)->get();
    $users_data_assignment = User::all();  // To load all users
    $departments = Department::all();  // To load all departments
    $user_data = User::with('departments')->whereNull('deleted_at')->get();
    return view('assignment.edit', ['assignment' => $assignment, 'assignment_task' => $assignment->tasks, 'timekeeps' => $assignment->timekeeps, 'departments' => $department, 'client' => $client, 'user' => $user,'quote_id' => $quote_id,'quote'=>$quote,'totalAmount' => $totalAmount,'currency_data' => $currency_data,'received_amount' => $received_amount,'amountDifference' => $amountDifference,'access_user' => $access_user,'assignment_users' => $assignment_users , 'users_data_assignment' => $users_data_assignment,'departments' => $departments, 'user_data' => $user_data]);
  }
  public function update(Request $request)
  {
    if ($request->ajax()) {
      $data = $request->all();
      // Validate the input data
      $validated = $request->validate([
        'client_id' => 'required|exists:clients,id',
        // 'ledger' => 'required|unique:ledger',
        // 'status' => 'required|in:quoted,awarded,lost',
        // 'description' => 'required',
        // 'amounts' => 'array',
        // 'amounts.*' => 'nullable|numeric',
        // 'descriptions' => 'array',
        // 'descriptions.*' => 'nullable|string',
      ]);

      try {
        // Start database transaction
        // dd($request);
        $user = Auth::user();
        $id = $request->id ?? '';
        $assignmentAdd = Assignment::find($id);
        // Insert into quotes table
        if ($assignmentAdd) {
          $assignmentAdd->assignment_type = $request['selectedValue'];
          $assignmentAdd->description = $request['description'];
          $assignmentAdd->status = $request['status'];
          $assignmentAdd->client_id = $request['client_id'];
          $assignmentAdd->quote_id = $request['quote'];
          $assignmentAdd->ledger = $request['ledger'];
          $assignmentAdd->created_by = auth()->user()->id;
          $assignmentAdd->approved_by = auth()->user()->id;
          $assignmentAdd->updated_at = now();
          $assignmentAdd->created_at = now();
          // Save the updated record
          $assignmentAdd->save();
          // dd($assignmentAdd);
          $assignment_log_data = $assignmentAdd->toArray(); // Convert the task model to an array
          $user_id = auth()->user()->id; // The currently logged-in user
          $created_date = $assignmentAdd->created_at; // Use the created_at field of the task
          $updated_date = $assignmentAdd->updated_at; // Use the updated_at field of the task
          // Log the action for this AssignmentTask
          // Helpers::logAction(
          //   'Assignment',           // module
          //   $request['client_id'],       // client_id
          //   'update',                     // action (store, update, delete, etc.)
          //   $assignment_log_data,        // data (assignment task data)
          //   $user_id,
          //   $user_id,                    // user_id (currently logged-in user)
          //   $created_date,               // created_date (optional)
          //   $updated_date                // updated_date (optional)
          //   );
        }
        $data = $request->all();
        // dd($data);
        // Insert into assignment_task table
        $task_ids_data = $data['task_id'] ?? []; // Default to an empty array if 'task_id' doesn't exist in $data

        if (!empty($task_ids_data)) {
            // Filter out null task_ids
            $filtered_task_ids = array_filter($task_ids_data, function ($task_id_remove) {
                return !is_null($task_id_remove); // Keep only non-null task_ids
            });

            // Ensure the array is reindexed after filtering
            $filtered_task_ids = array_values($filtered_task_ids);

            // Check if there are any task IDs left after filtering
            if (!empty($filtered_task_ids)) {
                // Update QuoteTasks based on the filtered task IDs
                AssignmentTask::where('assignment_id', $id)
                    ->whereNull('deleted_at')
                    ->whereNotIn('id', $filtered_task_ids) // Pass the filtered array of task_ids to exclude
                    ->update(['deleted_at' => now()]); // Mark tasks as deleted by setting deleted_at to current time
            }
        }

        $client = Client::find($request->client_id);

        $task_ids = $data['task_id'] ?? ''; // Array of task_ids (e.g., [4, 5, null])

        // dd($data);
        if ($task_ids) {
          foreach ($task_ids as $index => $task_id) {
              if ($task_id) {
                  // Update existing task
                  $existingTask = AssignmentTask::find($task_id);

                  if ($existingTask) {

                    // dd($assignment_task_staus);
                      $existingTask->update([
                          'assignment_id' => $id,
                          'description' => isset($data['descriptions'][$index]) ? $data['descriptions'][$index] : null,
                          'amount' =>  isset($data['amounts'][$index]) ? $data['amounts'][$index] : null,
                          'currency_id' => Client::find($request->client_id)->currency_id,
                          'received_amount' => isset($data['received_amount'][$index]) ? $data['received_amount'][$index] : null,
                          'department_id' => isset($data['department_ids'][$index]) ? $data['department_ids'][$index] : null,
                          // 'status' =>  $assignment_task_status,
                          'updated_by' => auth()->user()->id,
                      ]);
                      $assignment_log_data = $existingTask->toArray(); // Convert the task model to an array
                      $user_id = auth()->user()->id; // The currently logged-in user
                      $created_date = now(); // Use the created_at field of the task
                      $updated_date = now(); // Use the updated_at field of the task
                      // Log the action for this AssignmentTask
                      // Helpers::logAction(
                      //   'Assignment Task',           // module
                      //   $request['client_id'],       // client_id
                      //   'update',                     // action (store, update, delete, etc.)
                      //   $assignment_log_data,        // data (assignment task data)
                      //   $user_id,
                      //   $user_id,                    // user_id (currently logged-in user)
                      //   $created_date,               // created_date (optional)
                      //   $updated_date                // updated_date (optional)
                      //   );
                      $existingTask_recived_amount = AssignmentTask::find($existingTask->id);
                      // if(!empty($existingTask_recived_amount->received_amount)){
                        if($existingTask_recived_amount->received_amount == 0 || $existingTask_recived_amount->received_amount == '0') {
                          $assignment_task_status = 'notsent';
                          // dd($existingTask_recived_amount->received_amount);
                        }
                        // If amount equals received amount, set to 'paid'
                        else if($existingTask_recived_amount->amount == $existingTask_recived_amount->received_amount) {
                            $assignment_task_status = 'paid';
                        }
                        // If received amount is less than the amount, set to 'unpaid'
                        else if($existingTask_recived_amount->received_amount < $existingTask_recived_amount->amount && $existingTask_recived_amount->received_amount != 0) {
                            $assignment_task_status = 'unpaid';
                        }
                        $existingTask_recived_amount->status = $assignment_task_status;
                        $existingTask_recived_amount->save();
                      // }

                  }
              } else {
                  // Create new task for empty task_id
                  $newTask = new AssignmentTask();
                  $newTask->assignment_id = $id;
                  $newTask->description = isset($data['descriptions'][$index]) ? $data['descriptions'][$index] : null;
                  $newTask->amount = isset($data['amounts'][$index]) ? $data['amounts'][$index] : null;
                  $newTask->currency_id = Client::find($request->client_id)->currency_id;
                  $newTask->received_amount = isset($data['received_amount'][$index]) ? $data['received_amount'][$index] : null;
                  $newTask->department_id =  isset($data['department_ids'][$index]) ? $data['department_ids'][$index] : null;
                  $newTask->created_by = auth()->user()->id;
                  $newTask->updated_by = auth()->user()->id;
                  $newTask->save();
                  $assignment_log_data = $newTask->toArray(); // Convert the task model to an array
                  $user_id = auth()->user()->id; // The currently logged-in user
                  $created_date = now(); // Use the created_at field of the task
                  $updated_date = now(); // Use the updated_at field of the task
                  // Log the action for this AssignmentTask
                  // Helpers::logAction(
                  //   'Assignment Task',           // module
                  //   $request['client_id'],       // client_id
                  //   'store',                     // action (store, update, delete, etc.)
                  //   $assignment_log_data,        // data (assignment task data)
                  //   $user_id,
                  //   $user_id,                    // user_id (currently logged-in user)
                  //   $created_date,               // created_date (optional)
                  //   $updated_date                // updated_date (optional)
                  //   );
              }
          }
        } else{
          // dd($data);
          if ($data['department_ids']) {
                  foreach ($data['department_ids'] as $index => $department_id) {
                      if (
                          !is_null($department_id) &&
                          !is_null($data['descriptions'][$index]) &&
                          !is_null($data['amounts'][$index]) &&
                          !is_null($data['received_amount'][$index])
                      ) {
                          // Create a new task if no task_ids were provided
                          $assignmentTask = new AssignmentTask();
                          $assignmentTask->assignment_id = $id; // Assignment ID
                          $assignmentTask->description =  isset($data['descriptions'][$index]) ? $data['descriptions'][$index] : null;
                          $assignmentTask->amount =isset($data['amounts'][$index]) ? $data['amounts'][$index] : null;
                          $assignmentTask->currency_id = Client::find($request->client_id)->currency_id; // Currency ID
                          $assignmentTask->received_amount = isset($data['received_amount'][$index]) ? $data['received_amount'][$index] : null;
                          $assignmentTask->department_id = isset($department_id) ? $department_id : null; // Department ID
                          $assignmentTask->created_by = auth()->user()->id; // Created by
                          $assignmentTask->updated_by = auth()->user()->id; // Updated by
                          $assignmentTask->created_at = now(); // Created timestamp
                          $assignmentTask->updated_at = now(); // Updated timestamp
                          $assignmentTask->save(); // Save the new task

                          $assignment_log_data = $assignmentTask->toArray(); // Convert the task model to an array
                          $user_id = auth()->user()->id; // The currently logged-in user
                          $created_date = now(); // Use the created_at field of the task
                          $updated_date = now(); // Use the updated_at field of the task
                          // Log the action for this AssignmentTask
                          // Helpers::logAction(
                          //   'Assignment Task',           // module
                          //   $request['client_id'],       // client_id
                          //   'store',                     // action (store, update, delete, etc.)
                          //   $assignment_log_data,        // data (assignment task data)
                          //   $user_id,
                          //   $user_id,                    // user_id (currently logged-in user)
                          //   $created_date,               // created_date (optional)
                          //   $updated_date                // updated_date (optional)
                          //   );
                      }
                  }
              }
        }


        $timekeep_ids = $data['timekeep_id'] ?? ''; // Array of timekeep_ids (e.g., [1, 2, null])
        if($timekeep_ids){
          foreach ($timekeep_ids as $index => $timekeepId) {
            // Check if all required data is present
            if (
              !is_null($data['userValue']) && !is_null($data['timekeep_descriptions'][$index])
              && !is_null($data['user_rate'][$index]) && !is_null($data['user_total'][$index])
              && !is_null($data['timekeep_qty'][$index])
            ) {
              if ($timekeepId) {
                // If timekeep_id is not null, update the existing record
                $assignmentTimeKeep = AssignmentTimekeep::find($timekeepId);

                if ($assignmentTimeKeep) {
                  // Update the existing record
                  $assignmentTimeKeep->assignment_id = $id;
                  $assignmentTimeKeep->user_id = isset($data['userValue'][$index]) ? $data['userValue'][$index] : null;
                  $assignmentTimeKeep->description = isset($data['timekeep_descriptions'][$index]) ? $data['timekeep_descriptions'][$index] : null;
                  $assignmentTimeKeep->quantity = isset($data['timekeep_qty'][$index]) ? $data['timekeep_qty'][$index] : null;
                  $assignmentTimeKeep->rate =  isset($data['user_rate'][$index]) ? $data['user_rate'][$index] : null;
                  $assignmentTimeKeep->amount =isset($data['user_total'][$index]) ? $data['user_total'][$index] : null;
                  $assignmentTimeKeep->updated_by = auth()->user()->id; // User updating the record
                  $assignmentTimeKeep->updated_at = now(); // Timestamp of update
                  $assignmentTimeKeep->save(); // Save the updated record
                  $assignment_log_data = $assignmentTimeKeep->toArray(); // Convert the task model to an array
                  $user_id = auth()->user()->id; // The currently logged-in user
                  $created_date = now(); // Use the created_at field of the task
                  $updated_date = now(); // Use the updated_at field of the task
                  // Log the action for this AssignmentTask
                  // Helpers::logAction(
                  //   'Assignment Timekeep',           // module
                  //   $request['client_id'],       // client_id
                  //   'update',                     // action (store, update, delete, etc.)
                  //   $assignment_log_data,        // data (assignment task data)
                  //   $user_id,
                  //   $user_id,                    // user_id (currently logged-in user)
                  //   $created_date,               // created_date (optional)
                  //   $updated_date                // updated_date (optional)
                  //   );
                }
              } else {
                // If timekeep_id is null, create a new record
                $assignmentTimeKeep = new AssignmentTimekeep();
                $assignmentTimeKeep->assignment_id = $id;
                $assignmentTimeKeep->user_id = isset($data['userValue'][$index]) ? $data['userValue'][$index] : null;
                $assignmentTimeKeep->description = isset($data['timekeep_descriptions'][$index]) ? $data['timekeep_descriptions'][$index] : null;
                $assignmentTimeKeep->quantity = isset($data['timekeep_qty'][$index]) ? $data['timekeep_qty'][$index] : null;
                $assignmentTimeKeep->rate = isset($data['user_rate'][$index]) ? $data['user_rate'][$index] : null;
                $assignmentTimeKeep->amount = isset($data['user_total'][$index]) ? $data['user_total'][$index] : null;
                $assignmentTimeKeep->updated_by = auth()->user()->id; // User updating the record
                $assignmentTimeKeep->created_at = now(); // Created timestamp
                $assignmentTimeKeep->updated_at = now(); // Updated timestamp
                $assignmentTimeKeep->save(); // Save the new record

                $assignment_log_data = $assignmentTimeKeep->toArray(); // Convert the task model to an array
                $user_id = auth()->user()->id; // The currently logged-in user
                $created_date = now(); // Use the created_at field of the task
                $updated_date = now(); // Use the updated_at field of the task
                // Log the action for this AssignmentTask
                // Helpers::logAction(
                //   'Assignment Timekeep',           // module
                //   $request['client_id'],       // client_id
                //   'store',                     // action (store, update, delete, etc.)
                //   $assignment_log_data,        // data (assignment task data)
                //   $user_id,
                //   $user_id,                    // user_id (currently logged-in user)
                //   $created_date,               // created_date (optional)
                //   $updated_date                // updated_date (optional)
                //   );
              }
            }
          }
        } else{
          // dd('dcddfe');
          if($data['userValue']){
            foreach ($data['userValue'] as $index => $userId) {
              if (
                !is_null($userId) && !is_null($data['timekeep_descriptions'][$index]) && !is_null($data['user_rate'][$index])
                && !is_null($data['user_total'][$index]) && !is_null($data['timekeep_qty'][$index])
              ) {
                // !is_null($data['descriptions'][$index]
                $assignmentTimeKeep = new AssignmentTimekeep();
                $assignmentTimeKeep->assignment_id = $id;
                $assignmentTimeKeep->user_id = $userId;
                $assignmentTimeKeep->description =  isset($data['timekeep_descriptions'][$index]) ? $data['timekeep_descriptions'][$index] : null;
                $assignmentTimeKeep->quantity =  isset($data['timekeep_qty'][$index]) ? $data['timekeep_qty'][$index] : null;
                $assignmentTimeKeep->rate =  isset($data['user_rate'][$index]) ? $data['user_rate'][$index] : null;
                $assignmentTimeKeep->amount =isset($data['user_total'][$index]) ? $data['user_total'][$index] : null;
                // $assignmentTask->status = $request['status'] ?? '';
                // $assignmentTimeKeep->created_by = auth()->user()->id;
                $assignmentTimeKeep->updated_by = auth()->user()->id;
                $assignmentTimeKeep->updated_at = now();
                $assignmentTimeKeep->created_at = now();
                // Save the updated record
                $assignmentTimeKeep->save();
                $assignment_log_data = $assignmentTimeKeep->toArray(); // Convert the task model to an array
                $user_id = auth()->user()->id; // The currently logged-in user
                $created_date = now(); // Use the created_at field of the task
                $updated_date = now(); // Use the updated_at field of the task
                // Log the action for this AssignmentTask
                // Helpers::logAction(
                //   'Assignment Timekeep',           // module
                //   $request['client_id'],       // client_id
                //   'store',                     // action (store, update, delete, etc.)
                //   $assignment_log_data,        // data (assignment task data)
                //   $user_id,
                //   $user_id,                    // user_id (currently logged-in user)
                //   $created_date,               // created_date (optional)
                //   $updated_date                // updated_date (optional)
                //   );

              }
            }
          }
        }

        // Commit the transaction

        return response()->json(['success' => 'Assignment updated successfully', 'redirect' => route('assignment.index')]);
      } catch (\Exception $e) {
        // Rollback on error
        DB::rollBack();
        return response()->json(['error' => 'Failed to store quote: ' . $e->getMessage()], 500);
      }
    }
  }

  public function destroy($id)
  {
    try {
      // Find the quote or fail if not found
      $assignment = Assignment::findOrFail($id);

      // Start database transaction
      DB::beginTransaction();
      // Delete related quote tasks and quote users
      $assignment->tasks()->delete(); // Deletes all related quote tasks
      $assignment->timekeeps()->delete(); // Deletes all related quote users

      // Delete the quote itself
      $assignment->delete();

      // Commit the transaction
      DB::commit();

      // Return a success response
      return response()->json([
        'success' => 'Assignment and its related data deleted successfully'
      ]);
    } catch (\Exception $e) {
      // Rollback on error
      DB::rollBack();

      // Return an error response
      return response()->json([
        'error' => 'Failed to delete quote: ' . $e->getMessage()
      ], 500);
    }
  }
  public function view($id)
  {
    $department = Department::all()->pluck('name', 'id');
    // dd($department);
    $assignment = Assignment::findOrFail($id);
    // dd($assignment);
    // $formattedDate = Carbon::parse($quote->expiry_at)->format('d-m-Y');
    $currency = Currency::all()->pluck('currency', 'id');
    $assignment_task = $assignment->tasks()->whereNull('deleted_at')->get();
    $timekeeps = $assignment->timekeeps()->whereNull('deleted_at')->get();
    $client = Client::find($assignment->client_id);
    $assignmentTasksWithCurrency = $assignment->tasks->load('currency');
    // $user = User::select('id', 'name', 'email')->where('role_id', 1)->get();
    $user = User::select('users.id', 'users.name', 'users.email')
    ->join('users_departments', 'users.id', '=', 'users_departments.user_id')  // Join with the user_departments table
    ->where('users_departments.role_id', 1)  // Filter based on the role_id in user_departments
    ->distinct()  // Ensures unique users
    ->get();
    $totalAmount = $assignment->tasks->sum('amount');
    $received_amount = $assignment->tasks->sum('received_amount');
    $amountDifference = $totalAmount - $received_amount;
    $currency = Client::find($assignment->client_id)->currency_id;
    $currency_data = Currency::find($currency);
    // Add currency name to each task in the collection
    $assignmentTasksWithCurrency->each(function ($task) {
        // Now you can access the currency name via $task->currency->currency_name
        $task->currency_name = $task->currency;
    });
    $assignment_users = AssignmentUser::with('user', 'department')->where('assignment_id', $id)->get();
    $users_data_assignment = User::all();  // To load all users
    $departments = Department::all();  // To load all departments
    $user_data = User::with('departments')->whereNull('deleted_at')->get();
    return view('assignment.view', ['assignment' => $assignment, 'assignment_task' => $assignment_task, 'departments' => $department, 'currencies' => $currency,'client'=>$client,'assignmentTasksWithCurrency' => $assignmentTasksWithCurrency,'timekeeps' => $timekeeps,'user'=>$user ,'received_amount' => $received_amount,'totalAmount' => $totalAmount,'amountDifference' => $amountDifference,'currency_data' => $currency_data,'assignment_users' => $assignment_users , 'users_data_assignment' => $users_data_assignment,'departments' => $departments, 'user_data' => $user_data]);
  }
  public function getUserDepartment(Request $request)
  {
    $user_id = $request->id ?? '';
    // Find the user by ID and eager load their department
    $user_departments = User::with('departments')->find($user_id);
    if ($user_departments && $user_departments->departments) {
      // Return the department details as a JSON response
      return response()->json($user_departments->departments);
    } else {
      return response()->json(['message' => 'No department found for this user.'], 404);
    }
  }
  public function userstore(Request $request)
  {

        // Validate the incoming data
        // $request->validate([
        //     'user_id' => 'required|exists:users,id',
        //     'department_id' => 'required|exists:departments,id', // Or adjust this based on your database structure
        //     'access_level' => 'required|in:read,edit',
        // ]);
        $quoteUsers = [];
        foreach ($request->user_id as $key => $userId) {
          $existingAssignmentUser = AssignmentUser::where('assignment_id', $request->assignment_id)
          ->where('user_id', $userId)
          ->where('access_level', $request->access_level[$key])
          ->first();
          if(!$existingAssignmentUser){
            $quoteUsers[] = AssignmentUser::create([
                'user_id' => $userId,
                'department_id' => $request->department_id[$key], // Match department_id by index
                'access_level' => $request->access_level[$key],
                'assignment_id' => $request->assignment_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
          } else{

          }
        }
        // Return a success response
        return response()->json([
            'success' => true,
            'message' => 'Assignment User added successfully!',
            'data' => $quoteUsers
        ]);
  }
  public function getAssignedTasksMemo(Request $request)
  {
      // Retrieve the tasks and memo_number from the request
      $tasks = $request->input('tasks');
      $memo_number = $request->input('memo_number'); // Get the memo number from the request

      if (!empty($tasks)) {
          // Initialize an empty array to store the task assignments
          $assignedTasks = [];

          // Loop through each task in the received array
          foreach ($tasks as $task)
          {
            if($task['department_id'] == 4){
              // Fetch the task assignment based on task_id and assignment_id
              $assignedTask = AssignmentTask::where('id', $task['task_id'])
                                            ->where('assignment_id', $task['assignment_id'])
                                            ->first();

              // If the task assignment exists, update the memo_number
              if ($assignedTask) {
                  // Update the memo_number in the Task Assignment
                  $assignedTask->memo_number = $memo_number; // Assuming there's a 'memo_number' column
                  $assignedTask->save(); // Save the updated record

                  // Optionally, you can store the updated tasks for later use
                  $assignedTasks[] = $assignedTask;
              }
            }
          }

          // Return a success message or the updated tasks
        }

        if (!empty($assignedTasks)) {
          // You can return a success response here
          return response()->json([
              'success' => true,
              'message' => 'Tasks updated successfully with memo number.',
              'updated_tasks' => $assignedTasks // Send the updated tasks back if needed
          ]);
      } else {
          // In case no tasks were updated (no tasks in department 4 or no valid tasks to update)
          return response()->json([
              'success' => false,
              'message' => 'Only financer user can add memo number.'
          ]);
      }
      // Return an error if no tasks are selected
      return response()->json(['message' => 'No tasks selected'], 400);
  }
  public function getAssignedTimekeepMemo(Request $request)
  {

      // Retrieve the tasks and memo_number from the request
      $tasks = $request->input('tasks');
      $memo_number = $request->input('memo_number'); // Get the memo number from the request

      if (!empty($tasks)) {
          // Initialize an empty array to store the task assignments
          $assignedTasks = [];

          // Loop through each task in the received array
          foreach ($tasks as $task)
          {
              // Fetch the task assignment based on task_id and assignment_id
              $assignedTask = AssignmentTimekeep::where('id', $task['task_id'])
                                            ->where('assignment_id', $task['assignment_id'])
                                            ->first();

              // If the task assignment exists, update the memo_number
              if ($assignedTask) {
                  // Update the memo_number in the Task Assignment
                  $assignedTask->memo_number = $memo_number; // Assuming there's a 'memo_number' column
                  $assignedTask->save(); // Save the updated record

                  // Optionally, you can store the updated tasks for later use
                  $assignedTasks[] = $assignedTask;
              }
          }

          // Return a success message or the updated tasks
          return response()->json([
              'Success' => true,
              'message' => 'Timekeep updated successfully with memo number.',
              'updated_tasks' => $assignedTasks
          ]);
      }

      // Return an error if no tasks are selected
      return response()->json(['message' => 'No tasks selected'], 400);
  }

}
