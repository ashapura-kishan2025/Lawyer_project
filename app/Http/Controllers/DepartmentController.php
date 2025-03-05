<?php
// app/Http/Controllers/DepartmentController.php
namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Routing\Controller;
// use DataTables;


class DepartmentController extends Controller
{
  public function __construct()
  {
    // Middleware to check if the user has the relevant permissions
    $this->middleware('permission:DEPARTMENT.LIST', ['only' => ['index']]);
    $this->middleware('permission:DEPARTMENT.VIEW', ['only' => ['view']]);
    $this->middleware('permission:DEPARTMENT.CREATE', ['only' => ['create','store']]);
    $this->middleware('permission:DEPARTMENT.EDIT', ['only' => ['edit', 'update']]);
    $this->middleware('permission:DEPARTMENT.DELETE', ['only' => ['destroy']]);
  }
  public function index(Request $request)
  {

    if ($request->ajax()) {

      $data = Department::select(['id', 'name as department', 'description', 'status', 'created_at']); // Ensure column names match
      // if ($request->has('start_date') && $request->has('end_date')) {

      //   $data->whereBetween('created_at', [
      //     $request->input('start_date'),
      //     $request->input('end_date'),
      //   ]);
      // }

      return DataTables::of($data)
        ->addColumn('action', function ($row) {
          return '<i class="fa-sharp fa-solid fa-pen  edit-department-btn" style="font-size: 14px; color:#192b53;cursor: pointer;"
          data-bs-toggle="offcanvas"
          data-bs-target="#offcanvasAddUser"
          data-id="' . $row->id . '"></i>
          <i class="ti ti-trash delete-department-btn"  style="font-size: 20px;color:red;cursor: pointer;"
          data-id="' . $row->id . '"></i>';

        })
        ->editColumn('status', function ($row) {
          return $row->status == 1
            ? '<span class="badge bg-label-success inlineStatus" onClick="inlineClickStatusChange(\'' . $row->id . '\', \'' . $row->status . '\')" style="cursor: pointer;">Active</span>'
            : '<span class="badge bg-label-danger inlineStatus" onClick="inlineClickStatusChange(\'' . $row->id . '\', \'' . $row->status . '\')" style="cursor: pointer;">Inactive</span>';
        })
        ->editColumn('created_at', function ($row) {
          return $row->created_at->format('j M, Y');
          // Format the created_at date as desired
        })
        ->editColumn('description', function ($row) {
          // return Str::limit(strip_tags($row->description), 25, '...');
          // Format the created_at date as desired
        })
        ->rawColumns(['status', 'action', 'description'])
        ->make(true);
    }

    return view('departments.index'); // Blade view for the DataTable
  }

  public function store(Request $request)
  {
    if ($request->ajax()) {
      $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:500',
        'status' => 'required',
      ]);

      $response = Department::create($validatedData);
      if ($response) {
        return response()->json([
          'success' => TRUE,
          'message' => 'Department Created Successfully'
        ]);
      };
    }
  }

  public function edit($id)
  {

    $department = Department::findOrFail($id);
    return response()->json([
      'success' => true,
      'data' => $department
    ]);
  }

  public function update(Request $request, Department $department)
  {
    if ($request->ajax()) {
      $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:500',
        'status' => 'required',
        'id' => 'nullable|integer',
      ]);

      $name = $validatedData['name'];
      $desc = $validatedData['description'] ?? null;
      $status = $validatedData['status'];
      $id = $validatedData['id'] ?? null;
      $udpateData = [
        'name' => $name,
        'description' => $desc,
        'status' => $status
      ];
      $department = Department::findOrFail($id);

      if ($department) {
        $response = $department->update($udpateData);
        if ($response) {
          return response()->json([
            'success' => TRUE,
            'message' => 'Department Updated Successfully'
          ]);
        }
      }
    }
  }

  public function destroy(Department $department)
  {
    // dd($department->id);
    $department->delete();

    return response()->json([
      'success' => true,
      'message' => 'Department deleted successfully.',
      'department_id' => $department->id
    ]);
  }
  public function inlineStatusChange(Request $request, Department $department)
  {
    if ($request->ajax()) {
      $id = $request->input('id');
      $status = $request->input('status');
      $department = Department::findOrFail($id);

      if ($department) {
        $department->update(['status' => $status]);
      }
      return response()->json(['success' => true, 'message' => 'Department Status Updated Successfully']);
    } else {
      return "Something is not working";
    }
  }
}
