<?php

use App\Http\Controllers\AuthControler;
use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthControler::class, 'register']);
Route::post('/login', [AuthControler::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthControler::class, 'logout']);
    Route::get('/me', [AuthControler::class, 'profile']);

    Route::get('/books', [BookController::class, 'all']);
    Route::post('/books', [BookController::class, 'simpan']);
    Route::put('/books/{id}', [BookController::class, 'update']);
    Route::delete('/books/{id}', [BookController::class, 'hapus']);

    Route::put('/books/{id}/pinjam', [BookController::class, 'pinjam']);
    Route::put('/books/{id}/kembalikan', [BookController::class, 'kembalikan']);
});
