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
                    'error' => 'Source file not found',
                    'path'  => $sourcePath,
                ]);
            }

            // Prevent duplicate
            $existing = DB::table('photos')->where('source_path', $sourcePath)->first();
            if ($existing) {
                return response()->json(['url' => '/photo/' . $existing->id]);
            }

            // Copy from volatile cache/temp to persistent storage
            $dir = storage_path('app/private/photos');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $filename = basename($sourcePath);
            $destPath = $dir . '/' . $filename;

            $bytes = file_get_contents($sourcePath);
            if ($bytes === false || file_put_contents($destPath, $bytes) === false) {
                return response()->json([
                    'error' => 'Failed to write to storage',
                    'src'   => $sourcePath,
                    'dest'  => $destPath,
                    'dirOk' => is_dir($dir),
                ]);
            }

            $id = DB::table('photos')->insertGetId([
                'source_path' => $sourcePath,
                'path'        => $destPath,
                'taken_at'    => now(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            return response()->json(['url' => '/photo/' . $id]);

        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getFile() . ':' . $e->getLine()]);
        }
    }

    public function serve(int $id)
    {
        try {
            $photo = DB::table('photos')->find($id);

            if (!$photo || !file_exists($photo->path)) {
                return response()->json(['error' => 'Photo not found', 'id' => $id], 404);
            }

            $data = file_get_contents($photo->path);
            return response($data, 200)->header('Content-Type', 'image/jpeg');

        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}