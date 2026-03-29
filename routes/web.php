<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\CameraController;

Route::get('/', [CameraController::class, 'index']);
Route::get('/camera/photo', [CameraController::class, 'photo']);
Route::get('/camera/gallery', [CameraController::class, 'gallery']);
Route::get('/photos', [CameraController::class, 'photos']);
Route::get('/camera/save', function (Request $request) {
    $sourcePath = $request->query('path');

    if (!$sourcePath || !file_exists($sourcePath)) {
        return response()->json(['error' => 'File not found: ' . $sourcePath]);
    }

    // Prevent duplicate — check if same source path already saved
    $existing = DB::table('photos')
        ->where('source_path', $sourcePath)
        ->first();

    if ($existing) {
        return response()->json(['url' => '/photo/' . basename($existing->path)]);
    }

    $dir = storage_path('app/private/photos');
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $filename = 'photo_' . time() . '.jpg';
    $destPath = $dir . '/' . $filename;

    if (copy($sourcePath, $destPath)) {
        DB::table('photos')->insert([
            'source_path' => $sourcePath,
            'path'        => $destPath,
            'taken_at'    => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json(['url' => '/photo/' . $filename]);
    }

    return response()->json(['error' => 'Copy failed']);
});

Route::get('/photo/{filename}', function ($filename) {
    $path = storage_path('app/private/photos/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path);
})->where('filename', '[^/]+');