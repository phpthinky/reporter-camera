<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Native\Mobile\Events\Camera\PhotoTaken;
use App\Listeners\HandlePhotoTaken;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(PhotoTaken::class, HandlePhotoTaken::class);
    }
}