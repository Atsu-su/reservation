<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;

Route::get('/home', [ReservationController::class, 'index'])->name('home');
Route::get('/home/{id}', [ReservationController::class, 'show'])->name('home.show');