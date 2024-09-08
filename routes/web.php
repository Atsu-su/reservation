<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;
use App\Models\ReservationItem;

// mockのルート
Route::get('/mock', [ReservationController::class, 'mock'])->name('mock');
Route::get('/mock2', [ReservationController::class, 'mock2'])->name('mock2');

Route::get('/home', [ReservationController::class, 'index'])->name('home');
Route::get('/stock', [ReservationController::class, 'selectDate'])->name('select-date');
Route::post('/stock/result', [ReservationController::class, 'showStock'])->name('show-stock');
Route::get('/home/create1', [ReservationController::class, 'createDateItems'])->name('home.create1');
Route::get('/home/create2', [ReservationController::class, 'createAmount']);
Route::post('/home/create2', [ReservationController::class, 'createAmount'])->name('home.create2');
Route::get('/home/edit1/{id}', [ReservationController::class, 'editDate'])->name('home.edit-date');
Route::get('/home/edit2/{id}', [ReservationController::class, 'editAmount'])->name('home.edit-amount');
Route::post('/home/edit2/{id}', [ReservationController::class, 'editAmount'])->name('home.edit-amount');
Route::post('/home/store', [ReservationController::class, 'store'])->name('home.store');
Route::post('/home/update', [ReservationController::class, 'update'])->name('home.update');
Route::get('/home/result', [ReservationController::class, 'showReservationResult'])->name('home.show-reservation-result');
Route::get('/home/{id}', [ReservationController::class, 'showReservation'])->name('home.show-reservation');

Route::get('/proto', function () {
  $items = ReservationItem::with('item')
    ->where('reservation_id', 1)
    ->orderBy('item_id', 'asc')
    ->get();

  return view('create3_result_table', [
    'title' => '新規登録',
    'message' => '予約の登録中にエラーが発生しました。（予約失敗）',
    'items' => $items,
    'date' => '2021-08-01',
    'transaction' => false,
    'out_of_stocks' => [
      ['name' => '商品A', 'amount' => 10],
      ['name' => '商品B', 'amount' => 20],
    ],
  ]);
})->name('proto');
