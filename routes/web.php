<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;

// mockのルート
Route::get('/mock', [ReservationController::class, 'mock'])->name('mock');

Route::get('/home', [ReservationController::class, 'index'])->name('home');
Route::get('/home/create1', [ReservationController::class, 'create1_date_items'])->name('home.create1');
Route::get('/home/create2', function() {
    $message = '貸出物品の数量を入力してください。';
    return view('create2_amount', compact('message'));});
Route::post('/home/create2', [ReservationController::class, 'create2_amount'])->name('home.create2');
Route::get('/home/{id}', [ReservationController::class, 'show'])->name('home.show');

// [ReservationController::class, 'show'])->name('home.show');
