<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Assignment;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller;
class ClientController extends Controller
{

  public function __construct()
  {
    // Middleware to check if the user has the relevant permissions
    $this->middleware('permission:CLIENTS.LIST', ['only' => ['index']]);
    $this->middleware('permission:CLIENTS.VIEW', ['only' => ['view']]);
    $this->middleware('permission:CLIENTS.CREATE', ['only' => ['create','store']]);
    $this->middleware('permission:CLIENTS.EDIT', ['only' => ['edit', 'update']]);
    $this->middleware('permission:CLIENTS.DELETE', ['only' => ['destroy']]);
  }
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    if ($request->ajax()) {

      $data = Client::select(['id', 'client', 'email', 'type', 'created_at', 'contact_person', 'currency_id', 'mobile'])->orderBy('id', 'desc'); // Ensure column names match
      if ($request->get('client_name')) {
        $data->where('clients.client', 'like', '%' . $request->get('client_name') . '%');
      }
      return DataTables::of($data)
        ->addColumn('action', function ($row) {
          
          return '<i class="fa-solid fa-eye view-client-btn me-2" style="font-size: 14px;color: #cb9a59; cursor: pointer;"
                    data-id="' . $row->id . '"></i>
          <i class="fa-sharp fa-solid fa-pen edit-client-btn me-2" style="font-size: 14px; color:#192b53;cursor: pointer;"
          data-bs-toggle="offcanvas"
          data-bs-target="#offcanvasAddUser"
          data-id="' . $row->id . '"></i>
          <i class="ti ti-trash delete-client-btn"  style="font-size: 20px;color:red;cursor: pointer;"
          data-id="' . $row->id . '"></i>';
        })
        ->editColumn('created_at', function ($row) {
          return Carbon::parse($row->created_at)->format('d M, Y');
        })
        ->editColumn('currency_id', function ($row) {
          $client = Client::find($row->id);
          $currencyName =  $client->currency->currency; // get currency name from currency
          return $currencyName;
        })
        ->make(true); // Make sure to return this;
    }
    return view('client.index');
  }

  public function store(Request $request)
  {
    if ($request->ajax()) {
      $validatedData = $request->validate([
        'type' => 'required',
        // 'client' => 'required|string|max:255|unique:clients,client',
        'client' => 'required|string|max:255',
        'email' => 'required|email|unique:clients,email',
        'currency_id' => 'required',
        'mobile' => 'required|digits:10',
        'billing_address' => 'required',
        'country' => 'required',
        'source_other' => 'required',
        'linkedin_url' => 'nullable'
      ]);
      $client = new Client();
      $client->type = $request->type;
      $client->client = $request->client;
      $client->email = $request->email;
      $client->currency_id = $request->currency_id;
      $client->mobile = $request->mobile;
      $client->billing_address = $request->billing_address;
      $client->country_id = $request->country;
      $client->source_id = $request->source_other;
      $client->source_other = $request->source_value ?? '';
      $client->contact_person = auth()->user()->name;
      $client->company_name = $request->company_name;
      $client->created_by = auth()->user()->id;
      $client->created_at = now();
      $client->updated_at = now();
      $client->save();

      if ($client) {
        // return back()->with('success', 'Client added Successfully!');
        return response()->json([
          'success' => TRUE,
          'message' => 'Client added Successfully'
        ]);
      };
    }
  }



  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id)
  {
    $client = Client::findOrFail($id);
    // dd($client);
    $currency_name = $client->currency->currency;
    $country_name = $client->country->country;

    // $source_name = $client->source->title;
    // dd($source_name);

    $client['country_id'] = $client->country_id;
    $client['source_id'] = $client->source_id;
    // $client['source_id'] = $source_name;
    $client['currency_id'] = $client->currency_id;
    return response()->json([
      'success' => true,
      'data' => $client
    ]);
  }

  // public function getSpecificCurrency($currencyId)
  // {
  //   dd($currencyId);
  // }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    if ($request->ajax()) {
      // dd($request->all());
      // dd("In update");
      $validatedData = $request->validate([
        'id' => 'required',
        'type' => 'required',
        // 'client' => 'required|string|max:255|unique:clients,client',
        'client' => 'required|string|max:255',
        'email' => 'required|email',
        'currency_id' => 'required',
        'mobile' => 'required|digits:10',
        'billing_address' => 'required',
        'country' => 'required',
        'source_other' => 'required',
        'linkedin_url' => 'nullable',
      ]);
      $user = Auth::user();
      $loggedinUserID = $user->id;
      $loggedinUserName = $user->name;
      $validatedData['created_by'] = $loggedinUserID;
      $validatedData['contact_person'] = $loggedinUserName;
      $client['company_name'] = $request->company_name;
      $client = Client::findOrFail($id);
      // dd($validatedData);
      if ($client) {
        $response = $client->update($validatedData);
        if ($response) {
          return response()->json([
            'success' => TRUE,
            'message' => 'Client Updated Successfully'
          ]);
        }
      }
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Client $client)
  {
    $client_assignment = Assignment::where('client_id',$client->id)->get();
    if(count($client_assignment) == 0){
      $client->delete();
      return response()->json([
        'success' => true,
        'message' => 'Client details deleted successfully.',
        'country_id' => $client->id
      ]);
    } else{
      return response()->json([
        'success' => false,
        'message' => 'The record cannot be deleted.Because This client has Assignment',
        'country_id' => $client->id
      ]);
    }

   
  }

  public function getCurrency()
  {
    $currency = Currency::all()->pluck('currency', 'id');
    // dd($currency); // For debugging purposes
    return response()->json($currency);
  }
  public function getCountry()
  {
    $country = Country::all()->pluck('country', 'id');
    // dd($currency); // For debugging purposes
    return response()->json($country);
  }
  public function getSource()
  {
    $sources = Source::all()->pluck('title', 'id');
    // dd($currency); // For debugging purposes
    return response()->json($sources);
  }
  public function getSourceFiled(Request $request)
  {
    $other_source = $request->source;
    return response()->json(['value' => $other_source]);
  }
  public function view($id)
  { 
    $client = Client::findOrFail($id);
    $currency_name = Currency::findOrFail($client->currency_id);
    $country_name = Country::find($client->country_id);
    $source_name = Client::find($client->source_id);
    $name = explode(' ', $client->client);
    $initials = strtoupper($name[0][0] . (isset($name[1]) ? $name[1][0] : '')); // Get first letter of both first and last name
    return view('client.view', ['client' => $client,'country_name' => $country_name,'source_name' => $source_name,'initials' =>$initials,'currency_name' => $currency_name]);
  }
  public function getQuotesforView(Request $request)
  {
    $quotes = DB::table('quotes')
          ->join('clients', 'quotes.client_id', '=', 'clients.id')
          ->join('users as created_by', 'quotes.created_by', '=', 'created_by.id')
          ->leftJoin('quote_tasks', 'quote_tasks.quote_id', '=', 'quotes.id') // Left Join to include quotes without tasks
          ->leftJoin('currencies', 'clients.currency_id', '=', 'currencies.id') // Left Join for currencies to include quotes without tasks
          ->select(
              'quotes.id', // Quote ID
              'clients.client as client_name', // Corrected: Selecting client name from the clients table
              'created_by.name as created_by',
              'quotes.created_at as created_date',
              'quotes.reference as reference',
              'quotes.expiry_at as expiry_date',
              'quotes.assignment_id as assignment',
              'currencies.code as currency',
              'quotes.description',
              'quotes.status',
              DB::raw('COALESCE(SUM(quote_tasks.amount), 0) as amount') // Sum of all task amounts for a quote
          )
          ->groupBy(
              'quotes.id',
              'clients.client',
              'created_by.name',
              'quotes.created_at',
              'quotes.expiry_at',
              'quotes.assignment_id',
              'quotes.description',
              'quotes.status',
              'currencies.code',
              'quotes.reference'
          )->where('quotes.client_id', $request->get('client_id'))
          ->whereNull('quote_tasks.deleted_at')
          ->whereNull('quotes.deleted_at');

      // Apply filters
    
      if ($request->get('start_date') && $request->get('end_date')) {
          // Parse the start and end dates using Carbon
          $startDate = Carbon::parse($request->get('start_date'))->startOfDay();
          $endDate = Carbon::parse($request->get('end_date'))->endOfDay();
          // Apply the date range filter
          $quotes->whereBetween('quotes.created_at', [$startDate, $endDate]);
      }

      // Apply the search filter on client_name, created_by, description, and status
      if ($request->get('reference')) {
          $quotes->where('clients.reference', 'like', '%' . $request->get('reference') . '%');
      }
      if ($request->get('description')) {
          $quotes->where('quotes.description', 'like', '%' . $request->get('description') . '%');
      }
      if ($request->get('created_by')) {
          $quotes->where('created_by.name', 'like', '%' . $request->get('created_by') . '%');
      }
      if ($request->get('status')) {
          $quotes->where('quotes.status', 'like', '%' . $request->get('status') . '%');
      }

      // Fetch the quotes
      $quotes = $quotes->orderBy('quotes.created_at', 'desc')->get();

      return DataTables::of($quotes)
          ->addColumn('action', function ($row) {
              return '
                  <i class="fa-solid fa-eye view-quote-btn me-2" style="font-size: 14px;color: #cb9a59; cursor: pointer;" data-id="' . $row->id . '"></i>
                  <i class="fa-sharp fa-solid fa-pen edit-quote-btn me-2" style="font-size: 14px; cursor: pointer;color:#192b53;" data-id="' . $row->id . '"></i>
                  <i class="ti ti-trash delete-quote-btn" style="font-size: 20px; color: red; cursor: pointer;" data-id="' . $row->id . '"></i>
              ';
          })
          ->editColumn('status', function ($row) {
              if ($row->status == "awarded") {
                  return '<span class="badge bg-label-success inlineStatus" style="cursor: pointer;">' . $row->status . '</span>';
              } elseif ($row->status == "quoted") {
                  return '<span class="badge bg-label-primary inlineStatus" style="cursor: pointer;">' . $row->status . '</span>';
              } elseif ($row->status == "lost") {
                  return '<span class="badge bg-label-danger inlineStatus" style="cursor: pointer;">' . $row->status . '</span>';
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
  public function getAssignmentDataforclient(Request $request)
  {
      // Start building the query using the DB Query Builder
      $quotes = DB::table('assignments')
          ->join('clients', 'assignments.client_id', '=', 'clients.id')
          ->join('users as created_by', 'assignments.created_by', '=', 'created_by.id')
          ->leftJoin('assignment_tasks', 'assignment_tasks.assignment_id', '=', 'assignments.id')
          ->leftJoin('departments', 'assignment_tasks.department_id', '=', 'departments.id')
          ->leftJoin('quotes', 'quotes.id', '=', 'assignments.quote_id')
          ->leftJoin('currencies', 'clients.currency_id', '=', 'currencies.id')
          ->select(
              'assignments.id',
              'clients.client as client_name',
              'clients.id as client_id',
              'created_by.name as created_by',
              'assignments.created_at as created_date',
              'assignments.quote_id as quote_id',
              'assignments.description',
              'assignments.status',
              'assignments.ledger',
              'currencies.code as currency',
              DB::raw('COALESCE(SUM(assignment_tasks.amount), 0) as amount'),
              DB::raw('GROUP_CONCAT(DISTINCT departments.name SEPARATOR ", ") as department_name')
          )->where('assignments.client_id', $request->get('client_id'))
          ->whereNull('assignments.deleted_at')
          ->whereNull('assignment_tasks.deleted_at')  // Ensure to ignore soft-deleted assignments
          ->groupBy(
              'assignments.id',
              'clients.client',
              'clients.id',
              'created_by.name',
              'assignments.created_at',
              'assignments.quote_id',
              'assignments.description',
              'assignments.status',
              'assignments.ledger',
              'quotes.id',
              'currencies.code'
          );
  
      // Apply filters based on request parameters
      if ($request->get('status')) {
          $quotes->where('assignments.status', $request->get('status'));
      }
  
      if ($request->get('client_id')) {
          $quotes->where('assignments.client_id', $request->get('client_id')); // Filter by client_id
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
  
      // Sorting by creation date
      $quotes->orderBy('assignments.created_at', 'desc');
  
      // Get the data and return as a DataTable response
      $data = $quotes->get();
  
      return DataTables::of($data)
          ->addColumn('action', function ($row) {
              return '
                  <i class="fa-solid fa-eye view-assignment-btn me-2"  style="font-size: 14px;color: #cb9a59; cursor: pointer;"
                      data-id="' . $row->client_id . '"></i>
                  <i class="fa-sharp fa-solid fa-pen edit-assignment-btn" style="font-size: 14px; cursor: pointer;color:#192b53;"
                      data-id="' . $row->client_id . '"></i>
                  <i class="ti ti-trash delete-assignment-btn" style="font-size: 20px; color: red; cursor: pointer;"
                      data-id="' . $row->client_id . '"></i>
              ';
          })
          ->editColumn('status', function ($row) {
              // Format status with color badges
              if ($row->status == "completed") {
                  return '<span class="badge bg-label-success inlineStatus"  style="cursor: pointer;">' . $row->status . '</span>';
              } elseif ($row->status == "processing") {
                  return '<span class="badge bg-label-primary inlineStatus"  style="cursor: pointer;">' . $row->status . '</span>';
              } elseif ($row->status == "pending") {
                  return '<span class="badge bg-label-warning inlineStatus"  style="cursor: pointer;">' . $row->status . '</span>';
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
          ->editColumn('created_date', function ($row) {
              // Format the created date appropriately
              return Carbon::parse($row->created_date)->format('d M, Y');
          })
          ->rawColumns(['status', 'action', 'department', 'currency', 'quote', 'client', 'amount'])
          ->make(true);
  }
  public function getDepartmentClientWise()
  {
    $department = Department::all()->pluck('name', 'id');
    return response()->json($department);
  }
  
}
