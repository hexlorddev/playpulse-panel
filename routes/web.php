<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\ServerController;
use App\Http\Controllers\Dashboard\FileManagerController;
use App\Http\Controllers\Dashboard\BackupController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Landing page
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard.index');
    }
    return view('welcome');
});

// Authentication Routes (handled by Laravel Breeze/Sanctum)
// require __DIR__.'/auth.php';

// Dashboard Routes (protected by auth middleware)
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Main Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard/realtime-data', [DashboardController::class, 'realTimeData'])->name('dashboard.realtime-data');
    
    // Server Management
    Route::prefix('dashboard/servers')->name('dashboard.servers.')->group(function () {
        Route::get('/', [ServerController::class, 'index'])->name('index');
        Route::get('/create', [ServerController::class, 'create'])->name('create');
        Route::post('/', [ServerController::class, 'store'])->name('store');
        Route::get('/{server}', [ServerController::class, 'show'])->name('show');
        Route::get('/{server}/console', [ServerController::class, 'console'])->name('console');
        Route::get('/{server}/settings', [ServerController::class, 'settings'])->name('settings');
        Route::patch('/{server}/settings', [ServerController::class, 'updateSettings'])->name('update-settings');
        Route::post('/{server}/start', [ServerController::class, 'start'])->name('start');
        Route::post('/{server}/stop', [ServerController::class, 'stop'])->name('stop');
        Route::post('/{server}/restart', [ServerController::class, 'restart'])->name('restart');
        Route::delete('/{server}', [ServerController::class, 'destroy'])->name('destroy');
    });
    
    // File Manager
    Route::prefix('dashboard/servers/{server}/files')->name('dashboard.files.')->group(function () {
        Route::get('/', [FileManagerController::class, 'index'])->name('index');
        Route::get('/browse', [FileManagerController::class, 'browse'])->name('browse');
        Route::get('/edit/{file}', [FileManagerController::class, 'edit'])->name('edit');
        Route::put('/edit/{file}', [FileManagerController::class, 'update'])->name('update');
        Route::post('/upload', [FileManagerController::class, 'upload'])->name('upload');
        Route::post('/create-folder', [FileManagerController::class, 'createFolder'])->name('create-folder');
        Route::post('/create-file', [FileManagerController::class, 'createFile'])->name('create-file');
        Route::delete('/{file}', [FileManagerController::class, 'destroy'])->name('destroy');
        Route::get('/download/{file}', [FileManagerController::class, 'download'])->name('download');
        Route::post('/compress', [FileManagerController::class, 'compress'])->name('compress');
        Route::post('/extract', [FileManagerController::class, 'extract'])->name('extract');
    });
    
    // Backup Management
    Route::prefix('dashboard/servers/{server}/backups')->name('dashboard.backups.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::post('/', [BackupController::class, 'store'])->name('store');
        Route::get('/{backup}', [BackupController::class, 'show'])->name('show');
        Route::post('/{backup}/restore', [BackupController::class, 'restore'])->name('restore');
        Route::delete('/{backup}', [BackupController::class, 'destroy'])->name('destroy');
        Route::get('/{backup}/download', [BackupController::class, 'download'])->name('download');
        Route::post('/{backup}/lock', [BackupController::class, 'lock'])->name('lock');
        Route::post('/{backup}/unlock', [BackupController::class, 'unlock'])->name('unlock');
    });
    
    // Database Management
    Route::prefix('dashboard/databases')->name('dashboard.databases.')->group(function () {
        // TODO: Database management routes
    });
    
    // Billing & Subscription
    Route::prefix('dashboard/billing')->name('dashboard.billing.')->group(function () {
        // TODO: Billing routes
    });
    
    // Account Settings
    Route::prefix('dashboard/settings')->name('dashboard.settings.')->group(function () {
        // TODO: Settings routes
    });
    
    // Support & Tickets
    Route::prefix('dashboard/support')->name('dashboard.support.')->group(function () {
        // TODO: Support ticket routes
    });
});

// Admin Routes (for admin panel)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // TODO: Admin routes for managing nodes, users, templates, etc.
});

// API Routes for external integrations
Route::prefix('api/v1')->middleware(['auth:sanctum'])->group(function () {
    // Server management API
    Route::apiResource('servers', App\Http\Controllers\API\ServerController::class);
    
    // File management API
    Route::prefix('servers/{server}')->group(function () {
        // TODO: File management API endpoints
    });
    
    // Node monitoring API (for node daemons)
    Route::prefix('nodes')->group(function () {
        // TODO: Node API endpoints
    });
});

// Webhook Routes (for payment processors, etc.)
Route::prefix('webhooks')->group(function () {
    // TODO: Webhook endpoints for Stripe, PayPal, etc.
});

// Health Check Route
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'services' => [
            'database' => 'ok',
            'redis' => 'ok',
            'storage' => 'ok',
        ]
    ]);
})->name('health-check');