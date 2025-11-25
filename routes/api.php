<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WasteLevelController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BinController;
use App\Models\User;
use App\Http\Controllers\Auth\QRLoginController;
// Waste Level API
Route::post('/waste-levels', [WasteLevelController::class, 'store']);

// Dashboard live updates
Route::get('/dashboard-data', [DashboardController::class, 'getDashboardData']);
Route::get('/api/waste-data', [DashboardController::class, 'getWasteData']); // Chart

// Additional optional APIs
Route::get('/bin-status', [BinController::class, 'getBinStatus']);
Route::get('/api/report-analytics', [\App\Http\Controllers\ReportController::class, 'getReportData']);
Route::get('/weekly-waste', [DashboardController::class, 'getWeeklyWaste']);

// Auth APIs
Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    return response()->json([
        'message' => 'Login successful',
        'user' => $user
    ]);
});

Route::post('/qr-login', [QRLoginController::class, 'login']);
