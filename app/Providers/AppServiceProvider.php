<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Native\Mobile\Events\Camera\PhotoTaken;
use App\Listeners\HandlePhotoTaken;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 
      //  Event::listen(PhotoTaken::class, HandlePhotoTaken::class);
    }
}
