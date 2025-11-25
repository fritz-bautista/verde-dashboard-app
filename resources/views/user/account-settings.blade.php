@extends('nav_bar')

@section('admin-title', 'Account Settings')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/account-manager-user.css') }}">
@endsection

@section('admin-content')
    <div class="container">
        <div class="card">
            <h4>{{ Auth::user()->name }}</h4>
            <p class="text">{{ Auth::user()->email }}</p>

            <form method="POST" action="{{ route('account.update.self') }}">

                @csrf
                @method('PUT')

                <div class="input-group">
                    <label>Name</label>
                    <input type="text" name="name" value="{{ Auth::user()->name }}" class="form-control">
                </div>

                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ Auth::user()->email }}" class="form-control">
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter new password (optional)">
                </div>

                <button type="submit" class="btn btn-primary">Update Account</button>
            </form>
        </div>
    </div>
@endsection