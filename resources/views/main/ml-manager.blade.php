@extends('nav_bar')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/ml-manager.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('admin-title', 'ML Feature Manager')

@section('admin-content')
    <div>
        <h2>ML Manager</h2>
        <p>List of ML Features will be displayed here.</p>
    </div>
@endsection