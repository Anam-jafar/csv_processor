<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UploadStatusController;

Route::get('/uploads', [UploadStatusController::class, 'index']);

