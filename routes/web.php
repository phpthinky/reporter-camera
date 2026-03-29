<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CameraController;
use Illuminate\Http\Request;

Route::get('/', [CameraController::class, 'index']);
Route::get('/camera/photo',   [CameraController::class, 'photo']);
Route::get('/camera/gallery', [CameraController::class, 'gallery']);


/**/
Route::get('/camera/save', function (Request $request) {
    $sourcePath = $request->query('path');

    if (!$sourcePath || !file_exists($sourcePath)) {
        return response()->json(['error' => 'File not found: ' . $sourcePath]);
    }

    $dir = public_path('captures');
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $filename = 'photo_' . time() . '.jpg';
    $destPath = $dir . '/' . $filename;

    if (copy($sourcePath, $destPath)) {
        return response()->json(['url' => asset('captures/' . $filename)]);
    }

    return response()->json(['error' => 'Copy failed']);
});

/**--/

Route::get('/camera/save', function (Request $request) {
    $sourcePath = $request->query('path');

    // If it's already a public URL, just return it directly
    if (str_starts_with($sourcePath, 'http')) {
        return response()->json(['url' => $sourcePath]);
    }

    if (!$sourcePath || !file_exists($sourcePath)) {
        return response()->json(['error' => 'File not found: ' . $sourcePath]);
    }

    $dir = public_path('captures');
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $filename = 'photo_' . time() . '.jpg';
    $destPath = $dir . '/' . $filename;

    if (copy($sourcePath, $destPath)) {
        return response()->json(['url' => asset('captures/' . $filename)]);
    }

    return response()->json(['error' => 'Copy failed']);
});

/**/