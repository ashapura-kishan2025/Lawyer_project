<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Routing\Controller;

class CurrencyController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function __construct()
  {
    // Middleware to check if the user has the relevant permissions
    $this->middleware('permission:CURRENCY.LIST', ['only' => ['index']]);
    $this->middleware('permission:CURRENCY.VIEW', ['only' => ['view']]);
    $this->middleware('permission:CURRENCY.CREATE', ['only' => ['create', 'store']]);
    $this->middleware('permission:CURRENCY.EDIT', ['only' => ['edit', 'update']]);
    $this->middleware('permission:CURRENCY.DELETE', ['only' => ['destroy']]);
  }
  public function index(Request $request)
  {

    if ($request->ajax()) {

      $data = Currency::select(['id', 'currency', 'code', 'status']); // Ensure column names match
      return DataTables::of($data)
        ->addColumn('action', function ($row) {
          return '<i class="fa-sharp fa-solid fa-pen edit-currency-btn me-2"  style="font-size: 14px;color:#192b53; cursor: pointer;"
          data-bs-toggle="offcanvas"
          data-bs-target="#offcanvasAddUser"
          data-id="' . $row->id . '"></i>
          <i class="ti ti-trash delete-currency-btn"  style="cursor: pointer;color:red;font-size: 20px;"
          data-id="' . $row->id . '"></i>';
        })
        ->editColumn('status', function ($row) {
          return $row->status == 1
            ? '<span class="badge bg-label-success inlineStatus" onClick="inlineClickStatusChange(\'' . $row->id . '\', \'' . $row->status . '\')" style="cursor: pointer;">Active</span>'
            : '<span class="badge bg-label-danger inlineStatus" onClick="inlineClickStatusChange(\'' . $row->id . '\', \'' . $row->status . '\')" style="cursor: pointer;">Inactive</span>';
        })


        ->rawColumns(['status', 'action'])
        ->make(true);
    }
    return view('currency.index'); // Blade view for the DataTable
  }


  public function store(Request $request)
  {
    if ($request->ajax()) {
      $validatedData = $request->validate([
        'currency' => 'required|string|max:255',
        'code' => 'nullable|string|max:500',
        'status' => 'required',
      ]);

      $response = Currency::create($validatedData);
      if ($response) {
        return response()->json([
          'success' => TRUE,
          'message' => 'Currency Created Successfully'
        ]);
      };
    }
  }


  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id)
  {
    $currency = Currency::findOrFail($id);
    return response()->json([
      'success' => true,
      'data' => $currency
    ]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    if ($request->ajax()) {
      $validatedData = $request->validate([
        'currency' => 'nullable|string|max:500',
        'code' => 'nullable|string|max:500',
        'status' => 'required',
        'id' => 'nullable|integer',
      ]);

      $currency = $validatedData['currency'];
      $code = $validatedData['code'];
      $status = $validatedData['status'];
      $id = $validatedData['id'] ?? null;
      $udpateData = [
        'currency' => $currency,
        'code' => $code,
        'status' => $status
      ];
      $currency = Currency::findOrFail($id);

      if ($currency) {
        $response = $currency->update($udpateData);
        if ($response) {
          return response()->json([
            'success' => TRUE,
            'message' => 'Currency Updated Successfully'
          ]);
        }
      }
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Currency $currency)
  {
    $currency->delete();

    return response()->json([
      'success' => true,
      'message' => 'Currency deleted successfully.',
      'currency_id' => $currency->id
    ]);
  }


  public function inlineStatusChange(Request $request, Currency $currency)
  {
    if ($request->ajax()) {
      $id = $request->input('id');
      $status = $request->input('status');
      $currency = Currency::findOrFail($id);

      if ($currency) {
        $currency->update(['status' => $status]);
      }
      return response()->json(['success' => true, 'message' => 'Currency Status Updated Successfully']);
    } else {
      return "Something is not working";
    }
  }
}
