<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    BinController,
    DashboardController,
    MLController,
    EventController,
    ReportController,
    ProfileController,
    UserController,
    PredictionController,
    UtilityController,
    CollegeController,
    CollegeDashboardController,
    StudentController,
    NotificationController,
};
use App\Http\Controllers\Admin\CollegeRankingController;
use App\Http\Controllers\Admin\SettingsController;



// ------------------------------------------
// Public Routes
// ------------------------------------------
Route::get('/', fn() => view('auth.login'))->name('login');

Route::get(
    '/user/{user}/qr',
    fn(\App\Models\User $user) =>
    view('user.qr', ['user' => $user])
)->name('user.qr');

Route::get('/notifications/fetch', [NotificationController::class, 'fetch'])->name('notifications.fetch');


// ------------------------------------------
// Authenticated Routes
// ------------------------------------------
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/waste-data', [DashboardController::class, 'getWasteData'])->name('dashboard.wasteData');

    // Profile
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    // Predictions
    Route::controller(PredictionController::class)->group(function () {
        Route::get('/admin/predictions', [DashboardController::class, 'predict'])->name('predictions');
        Route::post('/run-prediction', 'run')->name('prediction.run');
    });

    // Bin Management
    Route::controller(BinController::class)->group(function () {
        Route::get('/bin-statistics/{id}', 'show')->name('bin.statistics');
    });

    // Event Management
    Route::controller(EventController::class)->group(function () {
        Route::get('/events', 'index')->name('events');
        Route::get('/admin/events', 'index')->name('events.index');
        Route::post('/admin/events', 'store')->name('events.store');
    });

    // Reports
    Route::controller(ReportController::class)->group(function () {
        Route::get('/reports', 'index')->name('reports');
        Route::get('/reports/download/{type}/{format}', 'download')->name('reports.download');
    });

    // Machine Learning Manager
    Route::get('/ml-manager', [MLController::class, 'index'])->name('ml.manager');

    // User Account Settings
    Route::get('/account-settings', [UserController::class, 'accountSettings'])->name('account.settings');
    Route::put('/account-settings', [UserController::class, 'updateOwnAccount'])->name('account.update.self');

    // Utility Management
    Route::prefix('utility')->name('utility.')->group(function () {
        Route::get('/', [UtilityController::class, 'index'])->name('index');
        Route::post('/store', [UtilityController::class, 'store'])->name('store');
        Route::post('/assign', [UtilityController::class, 'assign'])->name('assign');
        Route::post('/update-status/{id}', [UtilityController::class, 'updateStatus'])->name('updateStatus');
        Route::put('/{id}', [UtilityController::class, 'update'])->name('update');
        Route::delete('/{id}', [UtilityController::class, 'destroy'])->name('destroy');
    });

    // College CRUD
    Route::prefix('college')->name('college.')->group(function () {
        Route::get('/', [CollegeController::class, 'index'])->name('index'); // Unified manager page
        Route::post('/', [CollegeController::class, 'store'])->name('store');
        Route::put('/{id}', [CollegeController::class, 'update'])->name('update');
        Route::delete('/{id}', [CollegeController::class, 'destroy'])->name('destroy');
    });
});

// ------------------------------------------
// Admin Routes
// ------------------------------------------
Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {

        Route::get('/bin-manager', [BinController::class, 'index'])->name('bin.manager');

        // API routes for AJAX
        Route::get('/api/bin-status', [BinController::class, 'getBinStatus']);
        Route::post('/api/bin', [BinController::class, 'store']);
        Route::delete('/api/bin/{id}', [BinController::class, 'destroy']);


        // College Manager
        Route::get('/college-manager', [CollegeRankingController::class, 'index'])->name('college.manager');
        Route::post('/college-manager/college', [CollegeRankingController::class, 'storeCollege'])->name('college.store');
        Route::put('/college-manager/college/{id}', [CollegeRankingController::class, 'updateCollege'])->name('college.update');
        Route::delete('/college-manager/college/{id}', [CollegeRankingController::class, 'destroyCollege'])->name('college.destroy');

        Route::post('/college-manager/ranking-toggle', [CollegeRankingController::class, 'toggleRanking'])->name('ranking.toggle');

        // Ranking routes
        Route::get('/ranking/{id}/points', [CollegeRankingController::class, 'getSemesterPoints'])->name('ranking.points');
        Route::get('/ranking/{id}/pdf', [CollegeRankingController::class, 'downloadSemesterPdf'])->name('ranking.pdf');
    });

    // ------------------------------
    // System Settings (Data Cleanup)
    // ------------------------------
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::get('/load-table', [SettingsController::class, 'loadTable'])->name('loadTable');
        Route::post('/delete', [SettingsController::class, 'deleteRecords'])->name('deleteRecords');
    });

    // Admin User & Account Management
    Route::prefix('user-manager')->name('account.')->controller(UserController::class)->group(function () {
        Route::get('/', 'index')->name('manager');
        Route::post('/', 'store')->name('store');
        Route::put('/{user}', 'update')->name('update');
        Route::delete('/{user}', 'destroy')->name('destroy');
    });

});


// ------------------------------------------
// College Routes
// ------------------------------------------
Route::middleware(['auth', 'role:college'])->group(function () {
    Route::get('/college-dashboard', [CollegeDashboardController::class, 'index'])->name('college.dashboard');
});

// ------------------------------------------
// Student Routes
// ------------------------------------------
Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/student-app', [StudentController::class, 'index'])->name('student.index');
});

// Auth Routes
require __DIR__ . '/auth.php';
