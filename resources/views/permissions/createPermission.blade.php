@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create a New Permission</h1>

        <form action="{{ route('permissions.createPermission') }}" method="POST">
            @csrf
            <div class="form-group">
                <input type="text" name="name" class="form-control" placeholder="Permission Name" required>
            </div>
            <button type="submit" class="btn btn-primary">Create Permission</button>
        </form>
    </div>
@endsection
