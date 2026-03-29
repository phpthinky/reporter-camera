<?php

namespace App\Listeners;

use Native\Mobile\Events\Camera\PhotoTaken;
use Illuminate\Support\Facades\DB;

class HandlePhotoTaken
{
    /*
    public function handle(PhotoTaken $event): void
    {

    $filename = basename($event->path); // ← yung original filename lang!
    //$destination = public_path('photos/' . $filename);

        DB::table('photos')->insert([
            'path'       => $event->path,
            'taken_at'   => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }*/


   public function handle(PhotoTaken $event): void
    {
        $filename = basename($event->path); // ← yung original filename lang!
        $destination = public_path('photos/' . $filename);
        
        if (!is_dir(public_path('photos'))) {
            mkdir(public_path('photos'), 0755, true);
        }
        
        copy($event->path, $destination);
        
        DB::table('photos')->insert([
            'path'       => 'photos/' . $filename,
            'taken_at'   => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    }
}