<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Native\Mobile\Facades\Camera;

class CameraController extends Controller
{
    public function index()
    {
        return view('camera');
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
}