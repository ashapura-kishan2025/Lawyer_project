@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Assign Role to User</h1>

        <form action="{{ route('permissions.assignRoleToUser') }}" method="POST">
            @csrf
            <div class="form-group">
                <select name="user_id" class="form-control" required>
                    <option value="">Select User</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <select name="role_id" class="form-control" required>
                    <option value="">Select Role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Assign Role</button>
        </form>
    </div>
@endsection
