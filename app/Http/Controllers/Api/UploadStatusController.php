<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FileUpload;
use App\Http\Resources\FileUploadResource;

class UploadStatusController extends Controller
{
    public function index()
    {
        return FileUploadResource::collection(
            FileUpload::latest()->take(20)->get()
        );
    }
}

