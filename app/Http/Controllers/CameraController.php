<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Native\Mobile\Facades\Camera;

class CameraController extends Controller
{
    public function index()
    {
        $photos = DB::table('photos')->orderByDesc('taken_at')->get();
        return view('home', compact('photos'));
    }

    public function capture()
    {
        return view('capture');
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


    public function save(Request $request)
    {
        try {
            $sourcePath = $request->query('path');

            if (!$sourcePath) {
                return response()->json(['error' => 'No path received from camera']);
            }

            if (!file_exists($sourcePath)) {
                // Temp file already purged by the time save() ran
                return response()->json([
                    'error'  => 'Temp file already gone — capture it again',
                    'source' => $sourcePath,
                ]);
            }

            // Prevent duplicate
            $existing = DB::table('photos')->where('source_path', $sourcePath)->first();
            if ($existing) {
                return response()->json(['url' => '/photo/' . $existing->id]);
            }

            // Copy from volatile cache/temp into persistent storage under a clean name
            $dir = storage_path('app/private/photos');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $filename = 'IMG_' . date('Ymd_His') . '_' . substr(md5($sourcePath), 0, 6) . '.jpg';
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

    public function preview(Request $request)
    {
        try {
            $path = $request->query('path');

            if (!$path || !file_exists($path)) {
                return response('', 404);
            }

            $data = file_get_contents($path);
            return response($data, 200)->header('Content-Type', 'image/jpeg');

        } catch (\Throwable $e) {
            return response('', 500);
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