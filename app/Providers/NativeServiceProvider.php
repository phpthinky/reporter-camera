<?php

namespace App\Providers;

use Native\Mobile\NativeServiceProvider as BaseServiceProvider;

class NativeServiceProvider extends BaseServiceProvider
{
    public function plugins(): array
    {
        return [
            \Native\Mobile\Providers\CameraServiceProvider::class,
        ];
    }
}