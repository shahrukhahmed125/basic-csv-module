<?php

use App\Http\Controllers\PostCsvController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/posts/index', [PostCsvController::class, 'index'])->name('posts.index');
Route::post('/posts/import', [PostCsvController::class, 'import'])->name('posts.import');
