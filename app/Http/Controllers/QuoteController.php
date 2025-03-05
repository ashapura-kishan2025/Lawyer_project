<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quote;
use App\Models\Department;
use App\Models\QuoteUser;
use App\Models\User;
use App\Models\Currency;
use App\Models\QuoteTask;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;
use App\Models\Assignment;
use Illuminate\Support\Facades\Auth;
use App\Models\AssignmentTask;
use function PHPUnit\Framework\isNull;
use Illuminate\Routing\Controller;
use App\Helpers\Helpers;
class QuoteController extends Controller
{
  public function __construct()
  {
    // Middleware to check if the user has the relevant permissions
    $this->middleware('permission:QUOTATIONS.LIST', ['only' => ['index']]);
    $this->middleware('permission:QUOTATIONS.VIEW', ['only' => ['view']]);
    $this->middleware('permission:QUOTATIONS.CREATE', ['only' => ['create','store']]);
    $this->middleware('permission:QUOTATIONS.EDIT', ['only' => ['edit', 'update']]);
    $this->middleware('permission:QUOTATIONS.DELETE', ['only' => ['destroy']]);
  }
  public function index()
  {
    return view('quote.index');
  }

  public function getQuotes(Request $request)
  {
      // Get the logged-in user
      $user = auth()->user();

      // Get the user's role_id from the user_departments table
      $userRole = DB::table('users_departments')
          ->where('user_id', $user->id)
          ->first();  // Assuming the user has only one entry in the users_departments table

      // Check if the role_id is available
      if ($userRole) {
          $isAdmin = ($user->role_id == 3); // Get the role_id from user_departments
      } else {
          // Handle the case where role_id is not found, perhaps default to a role or throw an error
          $isAdmin = null;
      }

      // Start the query for quotes
      $quotes = DB::table('quotes')
          ->join('clients', 'quotes.client_id', '=', 'clients.id')
          ->join('users as created_by', 'quotes.created_by', '=', 'created_by.id')
          ->leftJoin('quote_tasks', 'quote_tasks.quote_id', '=', 'quotes.id') // Left Join to include quotes without tasks
          ->leftJoin('currencies', 'clients.currency_id', '=', 'currencies.id') // Left Join for currencies
          // For admin, join all records in quote_users
          // For user, join only the last entry for each quote_id
          ->when(!$isAdmin, function($query) {
              // For regular users, select the last entry from the quote_users table
              $query->leftJoin('quote_users', function ($join) {
                  $join->on('quotes.id', '=', 'quote_users.quote_id')
                       ->whereRaw('quote_users.id = (SELECT MAX(id) FROM quote_users WHERE quote_id = quotes.id)')->whereNull('quotes.deleted_at');
              });
          })
          ->when($isAdmin, function($query) {
              // For admins, join all records in quote_users
              $query->leftJoin('quote_users', 'quotes.id', '=', 'quote_users.quote_id')->whereNull('quotes.deleted_at');
          })
          ->select(
              'quotes.id as quote_id', // Explicitly alias `quotes.id` as `quote_id`
              'clients.client as client_name', // Selecting client name from the clients table
              'created_by.name as created_by',
              'quotes.created_at as created_date',
              'quotes.expiry_at as expiry_date',
              'quotes.assignment_id as assignment',
              'currencies.code as currency',
              'quotes.description',
              'quotes.status',
              'quotes.approved_by',
              'quote_users.access_level', // Select access_level from quote_users
              DB::raw('COALESCE(SUM(quote_tasks.amount), 0) as amount') // Sum of all task amounts for a quote
          );

      // For non-admin users, filter quotes that they created or were assigned to them
      if (!$isAdmin) {
          $quotes->where('quote_users.user_id', '=', $user->id) // Only show quotes assigned to the logged-in user
              ->whereIn('quote_users.access_level', ['edit', 'read']) // Only quotes with read or edit access
              ->orWhere('quotes.created_by', '=', $user->id); // Include quotes created by the logged-in user

          // Apply GROUP BY for non-admin users to ensure no duplicates
          $quotes->groupBy(
              'quotes.id',
              'clients.client',
              'created_by.name',
              'quotes.created_at',
              'quotes.expiry_at',
              'quotes.assignment_id',
              'quotes.description',
              'quotes.status',
              'currencies.code',
              'quotes.approved_by',
              'quote_users.access_level'
          );
      }

      // Apply filters (status, date range, etc.)
      if ($request->get('status')) {
          $quotes->where('quotes.status', $request->get('status'));
      }

      if ($request->get('start_date') && $request->get('end_date')) {
          $startDate = Carbon::parse($request->get('start_date'))->startOfDay();
          $endDate = Carbon::parse($request->get('end_date'))->endOfDay();
          $quotes->whereBetween('quotes.created_at', [$startDate, $endDate]);
      }

      // Apply search filters
      if ($request->get('client_name')) {
          $quotes->where('clients.client', 'like', '%' . $request->get('client_name') . '%');
      }
      if ($request->get('description')) {
          $quotes->where('quotes.description', 'like', '%' . $request->get('description') . '%');
      }
      if ($request->get('created_by')) {
          $quotes->where('created_by.name', 'like', '%' . $request->get('created_by') . '%');
      }

      // Continue with grouping and filtering for admin
      if ($isAdmin) {
          $quotes = $quotes->groupBy(
              'quotes.id',
              'clients.client',
              'created_by.name',
              'quotes.created_at',
              'quotes.expiry_at',
              'quotes.assignment_id',
              'quotes.description',
              'quotes.status',
              'currencies.code',
              'quotes.approved_by',
              'quote_users.access_level'
          )
          ->whereNull('quote_tasks.deleted_at')
          ->whereNull('quotes.deleted_at');
      }

      // Fetch the quotes
      $quotes = $quotes->orderBy('quotes.created_at', 'desc')->whereNull('quotes.deleted_at')->get();

      return DataTables::of($quotes)
          ->addColumn('action', function ($row) use ($isAdmin, $user) {
              // Fetch the quote_users entries related to the logged-in user
              $quoteUsers = DB::table('quote_users')
                  ->where('quote_id', $row->quote_id)
                  ->where('user_id', $user->id)
                  ->get();

              $actions = '';

              // If the user is an admin, show all actions
              // if ($isAdmin) {
                  $actions = '
                      <i class="fa-solid fa-eye view-quote-btn me-2" style="font-size: 14px;color: #cb9a59; cursor: pointer;"  data-id="' . $row->quote_id . '"></i>
                      <i class="fa-sharp fa-solid fa-pen edit-quote-btn"  style="font-size: 14px; color:#192b53;cursor: pointer;" data-id="' . $row->quote_id . '"></i>
                      <i class="ti ti-trash delete-quote-btn" style="font-size: 20px; color: red; cursor: pointer;" data-id="' . $row->quote_id . '"></i>
                  ';
              // } else {
                  // For normal users, check if they have both "read" and "edit" access levels
                  // $hasEdit = $quoteUsers->contains('access_level', 'edit');
                  // $hasRead = $quoteUsers->contains('access_level', 'read');

                  // if ($hasEdit) {
                  //     $actions = '
                  //         <i class="fa-solid fa-eye view-quote-btn me-2" style="font-size: 14px;color: #cb9a59; cursor: pointer;"  data-id="' . $row->quote_id . '"></i>
                  //         <i class="fa-sharp fa-solid fa-pen edit-quote-btn"  style="font-size: 14px; color:#192b53;cursor: pointer;" data-id="' . $row->quote_id . '"></i>
                  //     ';
                  // } elseif ($hasRead) {
                  //     $actions = '
                  //         <i class="fa-solid fa-eye view-quote-btn me-2" style="font-size: 14px;color: #cb9a59; cursor: pointer;"  data-id="' . $row->quote_id . '"></i>
                  //     ';
                  // }
              // }

              return $actions;
          })
          ->editColumn('status', function ($row) {
              if ($row->status == "awarded") {
                  return '<span class="badge bg-label-success inlineStatus" style="cursor: pointer;">Awarded</span>';
              } elseif ($row->status == "quoted") {
                  return '<span class="badge bg-label-primary inlineStatus" style="cursor: pointer;background-color: #D8B7DD;">Quoted</span>';
              } elseif ($row->status == "lost") {
                  return '<span class="badge bg-label-danger inlineStatus" style="cursor: pointer;">Lost</span>';
              }
          })
          ->addColumn('amount', function ($row) {
              return $row->amount ?? '0';
          })
          ->editColumn('description', function ($row) {
              return Str::limit(strip_tags($row->description), 25, '...');
          })
          ->editColumn('assignment', function ($row) {
              return $row->assignment ?? '';
          })
          ->addColumn('currency', function ($row) {
              return $row->currency ?? '0';
          })
          ->editColumn('created_date', function ($row) {
              return Carbon::parse($row->created_date)->format('d M, Y');
          })
          ->editColumn('expiry_date', function ($row) {
              return '<span class="text-danger">' . Carbon::parse($row->expiry_date)->format("d M, Y") . '</span>';
          })
          ->rawColumns(['status', 'action', 'assignment', 'expiry_date', 'currency'])
          ->make(true);
  }


  public function getUserCurrency(Request $request)
  {
    // dd($request);
    $client_currency = Client::find($request->id)->currency_id;
    $Currency_data =  Currency::find($client_currency);
    return response()->json($Currency_data);
  }
  public function create()
  {
    $department = Department::all()->pluck('name', 'id');
    $currency = Currency::all()->pluck('currency', 'id');
    return view('quote.form.create',['departments' => $department, 'currencies' => $currency]);
  }

  public function store(Request $request)
  {


    if ($request->ajax()) {
      $data = $request->all();
      // Validate the input data
      $validated = $request->validate([
        'expiry_on' => 'required|date',
        'client_id' => 'required|exists:clients,id',
      ]);

      try {
        // Start database transaction
        // DB::beginTransaction();

        $user = Auth::user();
        // Insert into quotes table
        // $quoteId = DB::table('quotes')->insertGetId([
        //   'client_id' => $validated['client_id'],
        //   'reference' => $validated['reference'],
        //   'expiry_at' => $validated['expiry_on'],
        //   'status' => $validated['status'],
        //   'description' => $validated['description'],
        //   'created_by' => $user->id,
        //   'created_at' => now(),
        //   'updated_at' => now(),
        // ]);

            $quote = new Quote();
            $quote->client_id = $request['client_id'];
            $quote->reference = $request['reference'];
            $quote->expiry_at = $request['expiry_on'];
            $quote->status = $request['status'];
            $quote->description = $request['description'] ?? null;
            $quote->created_by = $user->id;
            $quote->approved_by = auth()->user()->id;
            $quote->created_at = now();
            $quote->updated_at = now();
            $quote->save();
            $quoteId =  $quote['id'];
            $quote_log_data = $quote->toArray(); // This converts the assignment model to an array
            $user_id = auth()->user()->id; // The currently logged-in user
            $created_date = $quote->created_at; // You can use the assignment's created_at field
            $updated_date = $quote->updated_at; // You can use the assignment's updated_at field

            // Log the action
            // Helpers::logAction(
            //     'Quote',                // module
            //     $request['client_id'],       // client_id
            //     'store',                     // action (store, update, delete, etc.)
            //     $quote_log_data,                       // data (the assignment data, or any relevant data you want to log)
            //     $user_id,
            //     $user_id,                    // user_id (currently logged-in user)
            //     $created_date,               // created_date (optional)
            //     $updated_date                // updated_date (optional)
            // );
            if($quote['status'] == "awarded"){
              $assignmentAdd = new Assignment();
              $assignmentAdd->assignment_type = 'regular';
              $assignmentAdd->description = $quote['description'];
              $assignmentAdd->status = 'completed';
              $assignmentAdd->client_id =  $request['client_id'];
              $assignmentAdd->quote_id =  $quoteId;
              $assignmentAdd->ledger = 0;
              $assignmentAdd->created_by = auth()->user()->id;
              $assignmentAdd->approved_by = auth()->user()->id;
              $assignmentAdd->updated_at = now();
              $assignmentAdd->created_at = now();
              $assignmentAdd->save();
              $assignment_id = $assignmentAdd['id'];
              $quote_data =  Quote::find($quoteId);
              $quote_data->assignment_id = $assignment_id;
              $quote_data->save();
              $quote_log_data = $assignmentAdd->toArray(); // This converts the assignment model to an array
              $user_id = auth()->user()->id; // The currently logged-in user
              $created_date = now(); // You can use the assignment's created_at field
              $updated_date = now(); // You can use the assignment's updated_at field

            // Log the action
            // Helpers::logAction(
            //     'Assignment',                // module
            //     $request['client_id'],       // client_id
            //     'store',                     // action (store, update, delete, etc.)
            //     $quote_log_data,                       // data (the assignment data, or any relevant data you want to log)
            //     $user_id,
            //     $user_id,                    // user_id (currently logged-in user)
            //     $created_date,               // created_date (optional)
            //     $updated_date                // updated_date (optional)
            // );

            }
        //  dd($data['amounts']);
        // Insert into quote_tasks table
        if(!is_null($data['amounts']))
        {
          foreach ($data['amounts'] as $index => $amount) {
            if (!is_null($amount) && !is_null($data['currencies'][$index]) && !is_null($data['descriptions'][$index])) {
              $quoteTaskAdd = new QuoteTask();
              $quoteTaskAdd->quote_id = $quoteId;
              $quoteTaskAdd->amount = isset($amount) ? $amount : null;
              $quoteTaskAdd->currency_id = isset($data['currencies'][$index]) ? $data['currencies'][$index] : null;
              $quoteTaskAdd->description = isset($data['descriptions'][$index]) ? $data['descriptions'][$index] : null;
              $quoteTaskAdd->department_id = isset($data['department_ids'][$index]) ? $data['department_ids'][$index] : null;
              $quoteTaskAdd->updated_at = now();
              $quoteTaskAdd->created_at = now();
              $quoteTaskAdd->save();

              //This is for add quote task log
              $quote_log_data = $quoteTaskAdd->toArray(); // This converts the assignment model to an array
              $user_id = auth()->user()->id; // The currently logged-in user
              $created_date = now(); // You can use the assignment's created_at field
              $updated_date = now(); // You can use the assignment's updated_at field

              // Log the action
              // Helpers::logAction(
              //     'Quote Task',                // module
              //     $request['client_id'],       // client_id
              //     'store',                     // action (store, update, delete, etc.)
              //     $quote_log_data,                       // data (the assignment data, or any relevant data you want to log)
              //     $user_id,
              //     $user_id,                    // user_id (currently logged-in user)
              //     $created_date,               // created_date (optional)
              //     $updated_date                // updated_date (optional)
              // );
              if($quote['status'] == "awarded"){
                $assignmentTask = new AssignmentTask();
                $assignment_id = $assignmentAdd->id ?? '';
                $assignmentTask->assignment_id = $assignment_id;
                $assignmentTask->description = isset($data['descriptions'][$index]) ? $data['descriptions'][$index] : null;
                $assignmentTask->amount =  isset($amount) ? $amount : null;
                $assignmentTask->currency_id =  isset($data['currencies'][$index]) ? $data['currencies'][$index] : null;
                $assignmentTask->received_amount = 0;
                $assignmentTask->department_id = isset($data['department_ids'][$index]) ? $data['department_ids'][$index] : null;
                // $assignmentTask->status = $request['status'] ?? '';
                $assignmentTask->created_by = auth()->user()->id;
                $assignmentTask->updated_by = auth()->user()->id;
                $assignmentTask->updated_at = now();
                $assignmentTask->created_at = now();
                // Save the updated record
                $assignmentTask->save();

                $quote_log_data = $assignmentTask->toArray(); // This converts the assignment model to an array
                $user_id = auth()->user()->id; // The currently logged-in user
                $created_date = now(); // You can use the assignment's created_at field
                $updated_date = now(); // You can use the assignment's updated_at field
                // This is for Assignment Task log
                // Log the action
                // Helpers::logAction(
                //     'Assignment Task',                // module
                //     $request['client_id'],       // client_id
                //     'store',                     // action (store, update, delete, etc.)
                //     $quote_log_data,                       // data (the assignment data, or any relevant data you want to log)
                //     $user_id,
                //     $user_id,                    // user_id (currently logged-in user)
                //     $created_date,               // created_date (optional)
                //     $updated_date                // updated_date (optional)
                // );
              }

            }
          }

        }

        // Commit the transaction
        // DB::commit();
        return response()->json(['success' => 'Quote stored successfully',   'redirect' => route('quotes.index')]);
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
    $user = auth()->user(); // Get the logged-in user
    $role_id = $user->role_id; // Get the user's role_id

    // Fetch all department ids the user belongs to
    $userDepartmentIds = $user->departments->pluck('id'); // Get an array of department IDs

    // Check if the user is an admin (role_id 2) or a normal user (role_id 1)
    $isAdmin = ($role_id == 3); // If role_id is 2, the user is an admin

    // Fetch the most recent access level for the logged-in user based on quote_id, user_id, and department_id
    $quoteUserAccess = DB::table('quote_users')
        ->where('quote_id', $id)
        ->where('user_id', $user->id)
        ->whereIn('department_id', $userDepartmentIds) // Check if any of the user's departments match
        ->orderBy('quote_users.updated_at', 'desc') // Get the most recent access level
        ->first(); // Get the latest record
    // If the user is neither an admin nor has the edit access, deny access to the edit page
    if (!$isAdmin && (!$quoteUserAccess || $quoteUserAccess->access_level != 'edit')) {
        return redirect()->route('quotes.index')->with('error', 'You do not have the required access to edit this quote.');
    }

    $department = Department::all()->pluck('name', 'id');
    // dd($department);
    $quote = Quote::findOrFail($id);
    $access_user = User::select('id', 'name', 'email')->where('role_id', 1)->get();
    $currency = Currency::all()->pluck('currency', 'id');
    $client = Client::all()->pluck('client', 'id');
    $quote_tasks = $quote->tasks;

    // Attach currency name to each task by iterating over tasks
    $quote_tasks->each(function ($task) {
        // Assuming the task has a currency_id and a relationship defined as 'currency' in the Task model
        $task->currency_name = $task->currency ? $task->currency->currency : null;
    });
    $quote_users = QuoteUser::with('user', 'department')->where('quote_id', $id)->get();
    $users_data_quote = User::all();  // To load all users
    $departments = Department::all();  // To load all departments
    $user_data = User::with('departments')->whereNull('deleted_at')->get();

    return view('quote.form.edit', ['quote' => $quote, 'quote_task' => $quote_tasks, 'departments' => $department, 'currencies' => $currency,'client'=>$client,'access_user' => $access_user,'quote_users' => $quote_users,'user_data' => $user_data,'users_data_quote'=>$users_data_quote,'departments'=>$departments]);
  }

  public function update(Request $request)
  {
    // dd($request);

    $quoteUpdatedData = [
      'expiry_at' => $request['expiry_on'],
      'reference' => $request['reference'],
      'client_id' => $request['client_id'],
      'status' =>  $request['status'],
      'description' =>  $request['description'],
    ];
    // $quoteTasksArray = array_pop($quoteUpdatedData);
    // dd($quoteUpdatedData);
      try{
        $user = Auth::user();
        $id = $request->quote_id ?? '';
        $quote = Quote::findOrFail($id);
        // dd($request);
          if ($quote) {

            $updateQuoteSuccessfully = $quote->update($quoteUpdatedData);
            if($updateQuoteSuccessfully) {
              // $quote_log_data = $quoteUpdatedData->toArray(); // This converts the assignment model to an array
              $user_id = auth()->user()->id; // The currently logged-in user
              $created_date = now(); // You can use the assignment's created_at field
              $updated_date = now(); // You can use the assignment's updated_at field
              // This is for Assignment Task log
              // Log the action
              // Helpers::logAction(
              //     'Assignment',                // module
              //     $request['client_id'],       // client_id
              //     'update',                     // action (store, update, delete, etc.)
              //     $quote_log_data,                       // data (the assignment data, or any relevant data you want to log)
              //     $user_id,
              //     $user_id,                    // user_id (currently logged-in user)
              //     $created_date,               // created_date (optional)
              //     $updated_date                // updated_date (optional)
              // );
            }
            if($quote['status'] == "awarded")
            {
              $assignmentAdd = Assignment::where('id',$quote->assignment_id)->first();

              if($assignmentAdd)
              {
                $assignmentAdd->assignment_type = 'regular';
                $assignmentAdd->description = $request['description'];
                $assignmentAdd->status = 'completed';
                $assignmentAdd->client_id = $request['client_id'];
                $assignmentAdd->quote_id = $id;
                $assignmentAdd->ledger = 0;
                $assignmentAdd->created_by = auth()->user()->id;
                $assignmentAdd->approved_by = auth()->user()->id;
                $assignmentAdd->updated_at = now();
                $assignmentAdd->created_at = now();
                $assignmentAdd->save();
                $quote_log_data = $assignmentAdd->toArray(); // This converts the assignment model to an array
                $user_id = auth()->user()->id; // The currently logged-in user
                $created_date = now(); // You can use the assignment's created_at field
                $updated_date = now(); // You can use the assignment's updated_at field
                // This is for Assignment Task log
                // Log the action
                // Helpers::logAction(
                //     'Assignment',                // module
                //     $request['client_id'],       // client_id
                //     'update',                     // action (store, update, delete, etc.)
                //     $quote_log_data,                       // data (the assignment data, or any relevant data you want to log)
                //     $user_id,
                //     $user_id,                    // user_id (currently logged-in user)
                //     $created_date,               // created_date (optional)
                //     $updated_date                // updated_date (optional)
                // );

              } else{
                $assignmentAdd = new Assignment();
                $assignmentAdd->assignment_type = 'regular';
                $assignmentAdd->description = $request['description'];
                $assignmentAdd->status = 'completed';
                $assignmentAdd->client_id = $request['client_id'];
                $assignmentAdd->quote_id = $id;
                $assignmentAdd->ledger = 0;
                $assignmentAdd->created_by = auth()->user()->id;
                $assignmentAdd->approved_by = auth()->user()->id;
                $assignmentAdd->updated_at = now();
                $assignmentAdd->created_at = now();
                $assignmentAdd->save();
                $assignment_id = $assignmentAdd['id'];
                $quote_log_data = $assignmentAdd->toArray(); // This converts the assignment model to an array
                $user_id = auth()->user()->id; // The currently logged-in user
                $created_date = now(); // You can use the assignment's created_at field
                $updated_date = now(); // You can use the assignment's updated_at field
                // This is for Assignment Task log
                // Log the action
                // Helpers::logAction(
                //     'Assignment',                // module
                //     $request['client_id'],       // client_id
                //     'store',                     // action (store, update, delete, etc.)
                //     $quote_log_data,                       // data (the assignment data, or any relevant data you want to log)
                //     $user_id,
                //     $user_id,                    // user_id (currently logged-in user)
                //     $created_date,               // created_date (optional)
                //     $updated_date                // updated_date (optional)
                // );
                if($assignment_id){
                  $quote->assignment_id = $assignment_id;
                  $quote->save();
                }
                // dd($assignment_id);
              }
            }
            $data = $request->all();
            $quatitation_remove_null = $data['task_id'] ?? ''; // Check if 'task_id' exists in $data

            if ($quatitation_remove_null) {
                // Filter out null task_ids
                $quatitation_update_data = array_filter($quatitation_remove_null, function ($task_id) {
                    return !is_null($task_id); // Keep only non-null task_ids
                });

                // Ensure the array is reindexed after filtering
                $quatitation_update_data = array_values($quatitation_update_data);
                // dd($quatitation_update_data);
                // If there are any task IDs left after filtering
                if (!empty($quatitation_update_data)) {


                    // Update QuoteTasks based on the filtered task IDs
                    QuoteTask::where('quote_id', $id)
                        ->whereNull('deleted_at')
                        ->whereNotIn('id', $quatitation_update_data) // Pass the array of task_ids to exclude
                        ->update(['deleted_at' => now()]);
                        // Mark tasks as deleted by setting deleted_at to current time
                }
            } else{
              $for_one_quatitation_remove_null = $request['one_task_id'] ?? '';
              if($for_one_quatitation_remove_null){
                $quatitation_update_data = array_filter($for_one_quatitation_remove_null, function ($task_id) {
                  return !is_null($task_id); // Keep only non-null task_ids
                });

                  // Ensure the array is reindexed after filtering
                  $quatitation_update_data = array_values($quatitation_update_data);

                  // dd($quatitation_update_data);
                  // If there are any task IDs left after filtering
                  if (!empty($quatitation_update_data)) {

                      // Update QuoteTasks based on the filtered task IDs
                      $data_one = QuoteTask::where('quote_id', $id)
                          ->whereNull('deleted_at')
                          ->whereIn('id', $quatitation_update_data) // Pass the array of task_ids to exclude
                          ->update(['deleted_at' => now()]);

                          // Mark tasks as deleted by setting deleted_at to current time
                  }
                  }

              }

         // Check if 'task_id' exists and is not empty

        // if (isset($data['task_id']) && is_array($data['task_id']) && count(array_filter($data['task_id'], function($value) { return !is_null($value); })) > 0) {
          // Task IDs are provided, perform update
          // dd('empty');  // This will now only execute if task_id is an array with at least one non-null value
          if($data['task_id']) {
            foreach ($data['task_id'] as $index => $task_id) {
              // dd($task_id);
                // Ensure task_id is not null, and the related data is also not null
                if (!is_null($task_id) && !is_null($data['currencies'][$index]) && !is_null($data['descriptions'][$index])) {
                  $quoteTaskAdd = QuoteTask::where('quote_id', $id)
                  ->where('id', $task_id) // Check for specific task_id
                  ->first();
                    if ($quoteTaskAdd) {
                        // Update existing quote task
                        $quoteTaskAdd->quote_id = $id;
                        $quoteTaskAdd->amount = isset($data['amounts'][$index]) ? $data['amounts'][$index] : null;
                        $quoteTaskAdd->currency_id = isset($data['currencies'][$index]) ? $data['currencies'][$index] : null;
                        $quoteTaskAdd->description = isset($data['descriptions'][$index]) ? $data['descriptions'][$index] : null;
                        $quoteTaskAdd->department_id = isset($data['department_ids'][$index]) ? $data['department_ids'][$index] : null;
                        $quoteTaskAdd->updated_at = now();
                        $quoteTaskAdd->created_at = now(); // Use created_at only if needed
                        $quoteTaskAdd->save();

                        $quote_log_data = $quoteTaskAdd->toArray(); // This converts the assignment model to an array
                        $user_id = auth()->user()->id; // The currently logged-in user
                        $created_date = now(); // You can use the assignment's created_at field
                        $updated_date = now(); // You can use the assignment's updated_at field
                        // This is for Assignment Task log
                        // Log the action
                        // Helpers::logAction(
                        //     'Quote Task',                // module
                        //     $request['client_id'],       // client_id
                        //     'update',                     // action (store, update, delete, etc.)
                        //     $quote_log_data,                       // data (the assignment data, or any relevant data you want to log)
                        //     $user_id,
                        //     $user_id,                    // user_id (currently logged-in user)
                        //     $created_date,               // created_date (optional)
                        //     $updated_date                // updated_date (optional)
                        // );

                    }
                    if($quote['status'] == "awarded")
                    {
                      // dd($quote);

                      $assignments = AssignmentTask::where('assignment_id', $quote->assignment_id)
                                               ->get(); // Get all assignments for this assignment_id
                  if(count($assignments) > 0){

                       foreach ($assignments as $assignmentUpdate) {
                           // Update each AssignmentTask record based on the data provided
                           $assignmentUpdate->description = isset($data['descriptions'][$index]) ? $data['descriptions'][$index] : null;
                           $assignmentUpdate->amount = isset($data['amounts'][$index]) ? $data['amounts'][$index] : null;
                           $assignmentUpdate->currency_id = isset($data['currencies'][$index]) ? $data['currencies'][$index] : null;
                           $assignmentUpdate->department_id = isset($data['department_ids'][$index]) ? $data['department_ids'][$index] : null;
                           $assignmentUpdate->received_amount = 0; // Assuming default value for received_amount
                           $assignmentUpdate->created_by =auth()->user()->id;
                           $assignmentUpdate->updated_by = auth()->user()->id;
                           $assignmentUpdate->updated_at = now();
                           $assignmentUpdate->created_at = now();
                           $assignmentUpdate->save();

                            $quote_log_data = $assignmentUpdate->toArray(); // This converts the assignment model to an array
                            $user_id = auth()->user()->id; // The currently logged-in user
                            $created_date = now(); // You can use the assignment's created_at field
                            $updated_date = now(); // You can use the assignment's updated_at field
                            // This is for Assignment Task log
                            // Log the action
                            // Helpers::logAction(
                            //     'Assignment Task',                // module
                            //     $request['client_id'],       // client_id
                            //     'update',                     // action (store, update, delete, etc.)
                            //     $quote_log_data,                       // data (the assignment data, or any relevant data you want to log)
                            //     $user_id,
                            //     $user_id,                    // user_id (currently logged-in user)
                            //     $created_date,               // created_date (optional)
                            //     $updated_date                // updated_date (optional)
                            // );
                       }
                     } else{

                          $assignmentTask = new AssignmentTask();
                          $assignment_id = $quote->assignment_id ?? $assignment_id;
                          $assignmentTask->assignment_id = $assignment_id;
                          $assignmentTask->currency_id =  isset($data['currencies'][$index]) ? $data['currencies'][$index] : null;
                          $assignmentTask->description = isset($data['descriptions'][$index]) ? $data['descriptions'][$index] : null;
                          $assignmentTask->amount = isset($data['amounts'][$index]) ? $data['amounts'][$index] : null;
                          $assignmentTask->received_amount = 0;
                          $assignmentTask->department_id = isset($data['department_ids'][$index]) ? $data['department_ids'][$index] : null;
                          $assignmentTask->created_by = auth()->user()->id;
                          $assignmentTask->updated_by = auth()->user()->id;
                          $assignmentTask->updated_at = now();
                          $assignmentTask->created_at = now();
                          // Save the updated record
                          $assignmentTask->save();

                          $quote_log_data = $assignmentTask->toArray(); // This converts the assignment model to an array
                          $user_id = auth()->user()->id; // The currently logged-in user
                          $created_date = now(); // You can use the assignment's created_at field
                          $updated_date = now(); // You can use the assignment's updated_at field
                          // This is for Assignment Task log
                          // Log the action
                          // Helpers::logAction(
                          //     'Assignment Task',                // module
                          //     $request['client_id'],       // client_id
                          //     'store',                     // action (store, update, delete, etc.)
                          //     $quote_log_data,                       // data (the assignment data, or any relevant data you want to log)
                          //     $user_id,
                          //     $user_id,                    // user_id (currently logged-in user)
                          //     $created_date,               // created_date (optional)
                          //     $updated_date                // updated_date (optional)
                          // );
                     }
                  }
                }
            }
          }
          else{
            if($data['amounts']){
              foreach ($data['amounts'] as $index => $amount) {
                    $quoteTaskAdd = new QuoteTask();
                    // Add new quote task
                    $quoteTaskAdd->quote_id = $id;
                    $quoteTaskAdd->amount =   isset($amount) ? $amount : null;
                    $quoteTaskAdd->currency_id = isset($data['currencies'][$index]) ? $data['currencies'][$index] : null;
                    $quoteTaskAdd->description = isset($data['descriptions'][$index]) ? $data['descriptions'][$index] : null;
                    $quoteTaskAdd->department_id = isset($data['department_ids'][$index]) ? $data['department_ids'][$index] : null;
                    $quoteTaskAdd->updated_at = now();
                    $quoteTaskAdd->created_at = now(); // Ensure created_at is set
                    $quoteTaskAdd->save();

                    $quote_log_data = $quoteTaskAdd->toArray(); // This converts the assignment model to an array
                    $user_id = auth()->user()->id; // The currently logged-in user
                    $created_date = now(); // You can use the assignment's created_at field
                    $updated_date = now(); // You can use the assignment's updated_at field
                    // This is for Assignment Task log
                    // Log the action
                    // Helpers::logAction(
                    //     'Quote Task',                // module
                    //     $request['client_id'],       // client_id
                    //     'store',                     // action (store, update, delete, etc.)
                    //     $quote_log_data,                       // data (the assignment data, or any relevant data you want to log)
                    //     $user_id,
                    //     $user_id,                    // user_id (currently logged-in user)
                    //     $created_date,               // created_date (optional)
                    //     $updated_date                // updated_date (optional)
                    // );
                    if($quote['status'] == "awarded")
                    {

                        $assignmentTask = new AssignmentTask();
                        $assignment_id = $quote->assignment_id ?? $assignment_id;
                        $assignmentTask->assignment_id = $assignment_id;
                        $assignmentTask->currency_id =  isset($data['currencies'][$index]) ? $data['currencies'][$index] : null;
                        $assignmentTask->description = isset($data['descriptions'][$index]) ? $data['descriptions'][$index] : null;
                        $assignmentTask->amount = isset($amount) ? $amount : null;
                        $assignmentTask->received_amount = 0;
                        $assignmentTask->department_id = isset($data['department_ids'][$index]) ? $data['department_ids'][$index] : null;
                        $assignmentTask->created_by = auth()->user()->id;
                        $assignmentTask->updated_by = auth()->user()->id;
                        $assignmentTask->updated_at = now();
                        $assignmentTask->created_at = now();
                        // Save the updated record
                        $assignmentTask->save();
                        // dd($assignmentTask);

                        $quote_log_data = $assignmentTask->toArray(); // This converts the assignment model to an array
                        $user_id = auth()->user()->id; // The currently logged-in user
                        $created_date = now(); // You can use the assignment's created_at field
                        $updated_date = now(); // You can use the assignment's updated_at field
                        // This is for Assignment Task log
                        // Log the action
                        // Helpers::logAction(
                        //     'Assignment Task',                // module
                        //     $request['client_id'],       // client_id
                        //     'store',                     // action (store, update, delete, etc.)
                        //     $quote_log_data,                       // data (the assignment data, or any relevant data you want to log)
                        //     $user_id,
                        //     $user_id,                    // user_id (currently logged-in user)
                        //     $created_date,               // created_date (optional)
                        //     $updated_date                // updated_date (optional)
                        // );
                    }
              }
            }
          }
          }
        return response()->json(['success' => 'Quote updated successfully', 'redirect' => route('quotes.index')]);
      } catch (\Exception $e) {
        // Rollback on error
        DB::rollBack();
        return response()->json(['error' => 'Failed to store quote: ' . $e->getMessage()], 500);
      }


    return redirect()->route('quotes.index')->with('message', 'Quotation is updated successfully');

    // return response()->json(['success' => 'Assignment updated successfully', 'redirect' => route('assignment.index')]);
  }

  public function getDepartment()
  {
    $department = Department::all()->pluck('name', 'id');

    return response()->json($department);
  }

  public function getCurrency()
  {
    $currency = Currency::all()->pluck('currency', 'id');
    return response()->json($currency);
  }

  public function getClient()
  {
    $client = Client::all()->pluck('client', 'id');
    return response()->json($client);
  }

  public function destroy($id)
  {
    try {
      // Find the quote or fail if not found
      $quote = Quote::findOrFail($id);

      // Start database transaction
      DB::beginTransaction();

      // Delete related quote tasks and quote users
      $quote->tasks()->delete(); // Deletes all related quote tasks
      $quote->users()->delete(); // Deletes all related quote users

      // Delete the quote itself
      $quote->delete();

      // Commit the transaction
      DB::commit();

      // Return a success response
      return response()->json([
        'success' => 'Quote and its related data deleted successfully'
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
    $quote = Quote::findOrFail($id);

    $formattedDate = Carbon::parse($quote->expiry_at)->format('d-m-Y');
    $currency = Currency::all()->pluck('currency', 'id');
    $user = User::select('users.id', 'users.name', 'users.email')
    ->join('users_departments', 'users.id', '=', 'users_departments.user_id')  // Join with the user_departments table
    ->where('users_departments.role_id', 1)  // Filter based on the role_id in user_departments
    ->distinct()  // Ensures unique users
    ->get();
    // Find the client associated with the quote
    $client = Client::find($quote->client_id);
    $quoteTasksWithCurrency = $quote->tasks->load('currency');

    // Add currency name to each task in the collection
    $quote_tasks = $quote->tasks;

    // Attach currency name to each task by iterating over tasks
    $quote_tasks->each(function ($task) {
        // Assuming the task has a currency_id and a relationship defined as 'currency' in the Task model
        $task->currency_name = $task->currency ? $task->currency->currency : null;
    });
    // $quote_users = QuoteUser::all();
    $quote_users = QuoteUser::with('user', 'department')->where('quote_id', $id)->get();
    $users_data_quote = User::all();  // To load all users
    $departments = Department::all();  // To load all departments
    $user_data = User::with('departments')->whereNull('deleted_at')->get();

    return view('quote.form.view', ['quote' => $quote, 'quote_task' => $quote_tasks, 'department' => $department, 'currencies' => $currency,'client'=>$client,'formattedDate'=>$formattedDate, 'quote_task' => $quoteTasksWithCurrency,'user' => $user,'quote_users' => $quote_users,'user_data' => $user_data,'users_data_quote'=>$users_data_quote,'departments'=>$departments]);
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
      $quoteUsers = [];
      foreach ($request->user_id as $key => $userId) {
          // Check if the record with the same user_id and quote_id already exists
          $existingQuoteUser = QuoteUser::where('quote_id', $request->quote_id)
              ->where('user_id', $userId)
              ->where('access_level', $request->access_level[$key])
              ->first();

          if (!$existingQuoteUser) {
              // If no existing record is found, create a new one
              $quoteUsers[] = QuoteUser::create([
                  'user_id' => $userId,
                  'department_id' => $request->department_id[$key], // Match department_id by index
                  'access_level' => $request->access_level[$key],
                  'quote_id' => $request->quote_id,
                  'created_at' => now(),
                  'updated_at' => now()
              ]);
          } else {
              // Optionally, you can store or return the existing records that were not inserted
              // For example, add a message or store them in an array if needed
              // $quoteUsers[] = 'Existing record for user_id ' . $userId . ' and quote_id ' . $request->quote_id . ' was skipped.';
          }
      }

      // Return a success response, including any messages or skipped records if necessary
      return response()->json([
          'success' => true,
          'message' => 'Quote Users processed successfully!',
          'data' => $quoteUsers
      ]);
  }

}
