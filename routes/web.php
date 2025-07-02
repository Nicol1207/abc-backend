<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/contents/images/{filename}', function ($filename) {
    $path = storage_path('app/public/contents/images/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->download($path);
});

Route::get('/contents/videos/{filename}', function ($filename) {
    $path = storage_path('app/public/contents/videos/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->download($path);
});

Route::get('/contents/texts/{filename}', function ($filename) {
    $path = storage_path('app/public/contents/texts/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->download($path);
});
