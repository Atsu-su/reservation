<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;

// mockのルート
Route::get('/mock', [ReservationController::class, 'mock'])->name('mock');

Route::get('/home', [ReservationController::class, 'index'])->name('home');
Route::get('/home/create1', [ReservationController::class, 'create1_date_items'])->name('home.create1');
Route::get('/home/create2', [ReservationController::class, 'create2_amount']);
Route::post('/home/create2', [ReservationController::class, 'create2_amount'])->name('home.create2');
Route::post('/home/store', [ReservationController::class, 'store'])->name('home.store');
Route::get('/home/result', [ReservationController::class, 'show_reservation_result'])->name('home.show_reservation_result');
Route::get('/home/{id}', [ReservationController::class, 'show_reservation'])->name('home.show_reservation');

Route::get('/proto', function(){
  return view('create3_result_table', [
    'title' => '新規登録',
    'message' => '予約の登録中にエラーが発生しました。（予約失敗）',
  ]);
})->name('proto');