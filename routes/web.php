<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LinkController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard redirect to links
Route::get('/dashboard', function () {
    return redirect()->route('links');
})->name('dashboard');

// Public Link Management Routes (No middleware)
Route::prefix('links')->group(function () {
    Route::get('/', [LinkController::class, 'index'])->name('links');
    Route::post('/store', [LinkController::class, 'shortenwaccount'])->name('links.store');
    Route::post('/{id}/update', [LinkController::class, 'update'])->name('links.update');
    Route::delete('/{id}/delete', [LinkController::class, 'destroy'])->name('links.destroy');
});

// Public shorten endpoint
Route::post('/shorten', [LinkController::class, 'shorten'])->name('shorten');

// Short link redirection
Route::get('/{new_link}', [LinkController::class, 'redirect'])->name('redirect');
