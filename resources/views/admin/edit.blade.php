@extends('nav_bar')

@section('admin-title', 'Edit Account')

@section('admin-content')
<div class="container mt-5">
    <div class="card mt-3 shadow-sm p-4">
        <h4>Edit Account: {{ $user->name }}</h4>

        <form method="POST" action="{{ route('account.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Password (leave blank to keep current)</label>
                <input type="password" name="password" class="form-control" placeholder="Enter new password">
            </div>

            <div class="mb-3">
                <label>Role</label>
                <select name="role" class="form-control" required>
                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="college" {{ $user->role === 'college' ? 'selected' : '' }}>College</option>
                    <option value="student" {{ $user->role === 'student' ? 'selected' : '' }}>Student</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update Account</button>
        </form>
    </div>
</div>
@endsection
