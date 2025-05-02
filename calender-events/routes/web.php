<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Public routes
Route::get('/', function () {
    return view('student.index');
});
Route::get('/student', function () {
    return view('student.index');
});

// Auth-protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/student/create', function () {
        return view('student.create');
    });

    Route::post('/student/store', function () {
        return redirect('/student')->with('success', 'Event created successfully!');
    });
});

// Laravel default auth routes
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
