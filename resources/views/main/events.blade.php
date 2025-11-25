@extends('nav_bar')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/events.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('admin-title', 'Events Manager')

@section('admin-content')
<div class="events-dashboard-container">
    <div class="event-container">

=        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="event-form">
            <h3>Add New Event</h3>
            <form action="{{ route('events.store') }}" method="POST">
                @csrf
                <input type="text" name="title" placeholder="Event Title" required>
                <input class="date" type="date" name="date" required>
                <input class="floor" type="number" name="floor" placeholder="Floor (e.g. 1)" max="8" min="1" required>
                <input type="number" name="attendees" placeholder="Expected Attendees" required>
                <input type="text" name="description" placeholder="Type or Description (e.g. Seminar)">
                <button type="submit">Save Event</button>
            </form>
        </div>

        <div class="event-table-container">
            <div class="table-header">
                <h2>Event Table</h2>
                <input type="text" class="search-bar-header" placeholder="Search...">
            </div>

            <div class="table-container">
                <table class="event-table">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Date</th>
                            <th>Floor</th>
                            <th>Attendees</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($events as $event)
                            <tr>
                                <td>{{ $event->title }}</td>
                                <td>{{ \Carbon\Carbon::parse($event->date)->format('F d, Y') }}</td>
                                <td>{{ $event->floor }}F</td>
                                <td>{{ $event->attendees }}</td>
                                <td>{{ ucfirst($event->description) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No events found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
