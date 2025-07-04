<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/vtt/{path}', function ($path) {
    $file = storage_path('app/public/' . $path);
    if (!file_exists($file)) {
        abort(404);
    }
    $response = response()->make(file_get_contents($file), 200);
    $response->header('Content-Type', 'text/vtt');
    $response->header('Access-Control-Allow-Origin', '*');
    return $response;
})->where('path', '.*');
Route::options('/vtt/{path}', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept');
})->where('path', '.*');
