<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;

// routes/web.php
Route::get('/', [FileUploadController::class, 'index']);
Route::post('/upload', [FileUploadController::class, 'store'])->name('upload');



