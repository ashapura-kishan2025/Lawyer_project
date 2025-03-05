<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Routing\Controller;
class CountryController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function __construct()
  {
    // Middleware to check if the user has the relevant permissions
    $this->middleware('permission:COUNTRY.LIST', ['only' => ['index']]);
    $this->middleware('permission:COUNTRY.VIEW', ['only' => ['view']]);
    $this->middleware('permission:COUNTRY.CREATE', ['only' => ['create','store']]);
    $this->middleware('permission:COUNTRY.EDIT', ['only' => ['edit', 'update']]);
    $this->middleware('permission:COUNTRY.DELETE', ['only' => ['destroy']]);
  }
  public function index(Request $request)
  {

    if ($request->ajax()) {

      $data = Country::select(['id', 'country', 'code', 'status', 'created_at']); // Ensure column names match

      return DataTables::of($data)
        ->addColumn('action', function ($row) {
          return '<i class="fa-sharp fa-solid fa-pen edit-country-btn"  style="font-size: 14px; color:#192b53;cursor: pointer;"
          data-bs-toggle="offcanvas"
          data-bs-target="#offcanvasAddUser"
          data-id="' . $row->id . '"></i>
          <i class="ti ti-trash delete-country-btn" style="font-size: 20px;color:red;cursor: pointer;"
          data-id="' . $row->id . '"></i>';
        })
        ->editColumn('status', function ($row) {
          return $row->status == 1
            ? '<span class="badge bg-label-success inlineStatus" onClick="inlineClickStatusChange(\'' . $row->id . '\', \'' . $row->status . '\')" style="cursor: pointer;">Active</span>'
            : '<span class="badge bg-label-danger inlineStatus" onClick="inlineClickStatusChange(\'' . $row->id . '\', \'' . $row->status . '\')" style="cursor: pointer;">Inactive</span>';
        })
        ->editColumn('created_at', function ($row) {
          return Carbon::parse($row->created_at)->format('d M, Y');
        })
        ->rawColumns(['status', 'action', 'created_at'])
        ->make(true);
    }

    return view('country.index'); // Blade view for the DataTable
  }



  public function store(Request $request)
  {

    if ($request->ajax()) {
      $validatedData = $request->validate([
        'country' => 'required|string|max:255',
        'code' => 'nullable|string|max:500',
        'status' => 'required',
      ]);

      $response = Country::create($validatedData);
      if ($response) {
        return response()->json([
          'success' => TRUE,
          'message' => 'Country Created Successfully'
        ]);
      };
    }
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id)
  {
    $country = Country::findOrFail($id);
    return response()->json([
      'success' => true,
      'data' => $country
    ]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {

    if ($request->ajax()) {
      $validatedData = $request->validate([
        'country' => 'nullable|string|max:500',
        'code' => 'nullable|string|max:500',
        'status' => 'required',
        'id' => 'nullable|integer',
      ]);

      $country = $validatedData['country'];
      $code = $validatedData['code'];
      $status = $validatedData['status'];
      $id = $validatedData['id'] ?? null;
      $udpateData = [
        'country' => $country,
        'code' => $code,
        'status' => $status
      ];
      $country = Country::findOrFail($id);

      if ($country) {
        $response = $country->update($udpateData);
        if ($response) {
          return response()->json([
            'success' => TRUE,
            'message' => 'Country Updated Successfully'
          ]);
        }
      }
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Country $country)
  {

    $country->delete();

    return response()->json([
      'success' => true,
      'message' => 'Country details deleted successfully.',
      'country_id' => $country->id
    ]);
  }

  public function inlineStatusChange(Request $request, Country $country)
  {
    if ($request->ajax()) {
      $id = $request->input('id');
      $status = $request->input('status');
      $country = Country::findOrFail($id);

      if ($country) {
        $country->update(['status' => $status]);
      }
      return response()->json(['success' => true, 'message' => 'Country Status Updated Successfully']);
    } else {
      return "Something is not working";
    }
  }
}
