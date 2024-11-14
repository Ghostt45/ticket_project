<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;



Route::post('/order/book', [OrderController::class, 'bookOrder']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
