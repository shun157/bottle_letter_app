<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\StreamController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/sessions', [SessionController::class, 'store']);
Route::get('/stream', [StreamController::class, 'show']);
Route::patch('/assignments/{assignment}/expire', [AssignmentController::class, 'expire']);
Route::post('/messages/{message}/pickup', [MessageController::class, 'pickup']);
