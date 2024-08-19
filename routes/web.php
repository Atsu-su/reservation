<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;

Route::get('/home', [ReservationController::class, 'index'])->name('home');