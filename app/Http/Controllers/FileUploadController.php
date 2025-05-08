<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FileUpload;
use App\Jobs\ProcessCsvUpload;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
    public function index()
    {
        return view('uploads', [
            'uploads' => FileUpload::latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt']);
        
        try {
            $file = $request->file('csv_file');
            $originalName = $file->getClientOriginalName();
            $fileName = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            
            // Create directory if it doesn't exist
            $directory = 'csv-uploads';
            if (!Storage::exists($directory)) {
                Storage::makeDirectory($directory);
            }
            
            // Store file with move method
            $path = $directory . '/' . $fileName;
            if ($file->move(storage_path('app/' . $directory), $fileName)) {
                Log::info('File successfully stored at: ' . $path);
            } else {
                throw new \Exception('Failed to move uploaded file');
            }
            
            // Create database record
            $upload = FileUpload::create([
                'original_name' => $originalName,
                'path' => $path,
                'status' => 'pending'
            ]);
            
            // Dispatch processing job
            ProcessCsvUpload::dispatch($upload);
            
            return redirect()->back()->with('success', 'File uploaded successfully');
            
        } catch (\Exception $e) {
            Log::error('File upload failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withErrors(['csv_file' => 'Failed to save file: ' . $e->getMessage()]);
        }
    }
}