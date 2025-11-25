@extends('nav_bar')

@section('admin-title', 'Utility Manager')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/utility-manager.css') }}">
@endsection

@section('admin-content')
<div class="utility-container">
    @if (session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    <div class="grid">
        <!-- Utility Staff CRUD -->
        <div class="card">
            <div class="card-header">
                <h3>Utility Staff List</h3>
                <button class="btn btn-primary" onclick="document.getElementById('addModal').showModal()">Add</button>
            </div>

            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($utilities as $u)
                        <tr>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email ?? 'N/A' }}</td>
                            <td>{{ $u->contact ?? 'N/A' }}</td>
                            <td>{{ ucfirst($u->status) }}</td>
                            <td class="text-center">
                                <form action="{{ route('utility.destroy', $u->id) }}" method="POST"
                                      onsubmit="return confirm('Delete this staff?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">No staff added</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Assign Utility -->
        <div class="card">
            <h3>Assign Staff to Floor</h3>
            <form action="{{ route('utility.assign') }}" method="POST">
                @csrf
                <label>Utility Staff</label>
                <select name="utility_id" required>
                    <option disabled selected>Select staff</option>
                    @foreach($utilities as $utility)
                        <option value="{{ $utility->id }}">{{ $utility->name }}</option>
                    @endforeach
                </select>

                <label>Floor</label>
                <select name="level_id" required>
                    <option disabled selected>Select floor</option>
                    @foreach($levels as $level)
                        <option value="{{ $level->id }}">{{ $level->name }}</option>
                    @endforeach
                </select>

                <button class="btn btn-indigo full-width">Assign</button>
            </form>

            <hr>

            <h3>Current Assignments</h3>
            <div class="table-scroll">
                <table class="styled-table small">
                    <thead>
                        <tr>
                            <th>Utility</th>
                            <th>Floor</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $a)
                            <tr>
                                <td>{{ $a->utility->name }}</td>
                                <td>{{ $a->level->name }}</td>
                                <td>{{ $a->assigned_date }}</td>
                                <td>
                                    <span class="{{ $a->status === 'completed' ? 'status-completed' : 'status-pending' }}">
                                        {{ ucfirst($a->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('utility.updateStatus', $a->id) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-primary btn-small">Toggle</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">No assignments yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Utility Modal -->
<dialog id="addModal" class="modal">
    <form method="POST" action="{{ route('utility.store') }}" class="modal-form">
        @csrf
        <h3>Add Utility Staff</h3>

        <label>Name</label>
        <input type="text" name="name" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Confirm Password</label>
        <input type="password" name="password_confirmation" required>

        <label>Contact</label>
        <input type="text" name="contact">

        <div class="modal-actions">
            <button type="button" onclick="document.getElementById('addModal').close()" class="btn btn-gray">Cancel</button>
            <button class="btn btn-primary">Save</button>
        </div>
    </form>
</dialog>
@endsection
