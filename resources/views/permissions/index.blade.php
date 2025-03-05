@php
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts.horizontalLayout')


@section('title', 'Update Permissions')

@section('content')
<div class="container mt-5">
    @if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <div class="card">
        <div class="card-header">
            <h4>Role : {{$role->name }}</h4>
        </div>
        <div class="card-body">
        <form action="{{ route('permissions.update',$role->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="role_name" class="form-label">Role Name</label>
                <input type="text" class="form-control" name="role_name" value="{{ $role->name }}"/>
            </div>
            <input type="hidden" value="{{ $selectedDepartments ?? '' }}" name="department_id" />
            @error('role_name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <div class="mb-3">

                <label for="">Permissions</label><br>
                <input type="checkbox" id="selectAll" />
                <label for="selectAll">Select All</label>

                <div class="row">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Module</th>
                                @foreach($actions as $action)
                                    <th>{{ ucfirst($action) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($modules as $module)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $module->name)) }}</td>
                                    <input type="hidden" value="{{ $module->id }}" name="id_data[]" />

                                    @foreach($actions as $action)
                                        <td>
                                            <input type="checkbox" name="permissions[]"
                                                value="{{ $module->name . '.' . $action }}"
                                                {{ in_array($module->name . '.' . $action, $rolePermissionNames) ? 'checked' : '' }}
                                                >
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            </tbody>
                    </table>
                </div>
            </div>

            <div class="mb-3">
                <a href="{{ route('role.index') }}" class="btn btn-light btn1 me-2">Cancel</a>
                <button type="submit" class="btn btn-info btn1">Update</button>
            </div>
        </form>

        </div>
    </div>
</div>
<script>
// Select/Deselect All checkboxes
$('#selectAll').change(function() {
    // If the "Select All" checkbox is checked, select all permission checkboxes
    if ($(this).prop('checked')) {
        $('input[name="permissions[]"]').prop('checked', true);
    } else {
        $('input[name="permissions[]"]').prop('checked', false);
    }
});
</script>
@endsection
