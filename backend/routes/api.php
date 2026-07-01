<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\StreamController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/stream', [StreamController::class, 'show']);
Route::patch('/assignments/{assignment}/expire', [AssignmentController::class, 'expire']);
