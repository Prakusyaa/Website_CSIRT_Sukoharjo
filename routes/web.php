<?php

use App\Http\Controllers\Auth\SessionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [SessionController::class, 'create'])->name('login');
    Route::post('login', [SessionController::class, 'store']);
});

Route::middleware(['auth'])->group(function () {
    Route::post('logout', [SessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/inbox', function () {
        return Inertia::render('Inbox');
    })->name('inbox');

    Route::resource('incidents', \App\Http\Controllers\IncidentController::class);
    
    // Secure Evidence Download Endpoint
    Route::get('/incidents/{incident}/attachments/{attachment}/download', [\App\Http\Controllers\AttachmentController::class, 'download'])
        ->name('incidents.attachments.download');
});
