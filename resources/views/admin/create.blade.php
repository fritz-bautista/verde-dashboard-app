@extends('nav_bar')

@section('admin-title', 'Add New User')

@section('admin-content')
<div class="container mt-5">
    <div class="card mt-3 shadow-sm p-4">
        <h4>Add New Account</h4>
        <form method="POST" action="{{ route('account.store') }}">
            @csrf

            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" class="form-control" placeholder="Enter full name" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter email address" required>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>

            <div class="mb-3">
                <label>Role</label>
                <select name="role" class="form-control" required>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Create Account</button>
        </form>
    </div>
</div>
@endsection
