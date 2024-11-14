<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});


Route::get('/orders/bookForm', [OrderController::class, 'showBookingForm'])->name('orders.bookForm');
Route::post('/orders/book', [OrderController::class, 'bookOrder'])->name('orders.book');
