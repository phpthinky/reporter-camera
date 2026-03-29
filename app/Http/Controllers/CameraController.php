<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Native\Mobile\Facades\Camera;

class CameraController extends Controller
{
    public function index()
    {
        return view('camera.camera');
    }

    public function photo()
    {
        Camera::getPhoto();
        return response()->json(['ok' => true]);
    }

    public function gallery()
    {
        Camera::pickImages();
        return response()->json(['ok' => true]);
    }

    public function photos()
    {
        $photos = DB::table('photos')->orderByDesc('taken_at')->get();
        return view('camera.photos', compact('photos'));
    }

    public function save(Request $request)
    {
        try {
            $sourcePath = $request->query('path');

            if (!$sourcePath) {
                return response()->json(['error' => 'No path received from camera']);
            }

            if (!file_exists($sourcePath)) {
                return response()->json([
                    'error'   => 'Source file not found',
                    'path'    => $sourcePath,
                    'storage' => storage_path('app/private/photos'),
                ]);
            }

            // Prevent duplicate
            $existing = DB::table('photos')->where('source_path', $sourcePath)->first();
            if ($existing) {
                return response()->json(['url' => '/photo/' . basename($existing->path)]);
            }

            $dir = storage_path('app/private/photos');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $filename = 'photo_' . time() . '_' . uniqid() . '.jpg';
            $destPath = $dir . '/' . $filename;

            $bytes = file_get_contents($sourcePath);
            if ($bytes === false || file_put_contents($destPath, $bytes) === false) {
                return response()->json([
                    'error'  => 'Failed to write photo to storage',
                    'src'    => $sourcePath,
                    'dest'   => $destPath,
                    'dirOk'  => is_dir($dir),
                ]);
            }

            DB::table('photos')->insert([
                'source_path' => $sourcePath,
                'path'        => $destPath,
                'taken_at'    => now(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            return response()->json(['url' => '/photo/' . $filename]);

        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getFile() . ':' . $e->getLine()]);
        }
    }

    public function serve(string $filename)
    {
        try {
            $filename = basename($filename);
            $path     = storage_path('app/private/photos/' . $filename);

            if (!file_exists($path)) {
                return response()->json(['error' => 'Not found: ' . $path], 404);
            }

            $data = file_get_contents($path);
            return response($data, 200)->header('Content-Type', 'image/jpeg');

        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}