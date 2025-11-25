@extends('nav_bar')

@section('content')
<div style="text-align:center; margin-top:120px;">
    <img src="{{ asset('img/offline.png') }}" alt="Offline" style="width:120px; opacity:0.8;">
    <h2 style="margin-top:20px;">⚠️ You’re Offline</h2>
    <p>Unable to load dashboard data. Please check your internet or database connection.</p>
    <button onclick="location.reload()" class="btn btn-primary mt-3">Retry</button>
</div>
@endsection
