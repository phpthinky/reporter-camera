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
}