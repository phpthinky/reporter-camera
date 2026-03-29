<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CameraController;

Route::get('/',        [CameraController::class, 'index']);
Route::get('/capture', [CameraController::class, 'capture']);

Route::get('/camera/photo',   [CameraController::class, 'photo']);
Route::get('/camera/gallery', [CameraController::class, 'gallery']);
Route::get('/camera/save',    [CameraController::class, 'save']);

Route::get('/photo/{id}', [CameraController::class, 'serve'])->where('id', '[0-9]+');
