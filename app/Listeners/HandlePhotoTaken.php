<?php

namespace App\Listeners;

use Native\Mobile\Events\Camera\PhotoTaken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HandlePhotoTaken
{
    public function handle(PhotoTaken $event): void
    {
        Log::info('PhotoTaken fired', ['path' => $event->path]);

        $sourcePath = $event->path;

        $dir = public_path('captures');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'photo_' . time() . '.jpg';
        $destPath = $dir . '/' . $filename;

        if (copy($sourcePath, $destPath)) {
            Log::info('Photo copied to', ['dest' => $destPath]);
            Cache::put('latest_photo', asset('captures/' . $filename), 60);
        } else {
            Log::error('Copy failed', ['src' => $sourcePath, 'dest' => $destPath]);
        }
    }
}