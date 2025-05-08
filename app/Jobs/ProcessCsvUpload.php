<?php

namespace App\Jobs;

use App\Models\FileUpload;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use App\Events\UploadStatusUpdated;

class ProcessCsvUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public FileUpload $fileUpload) {}

    public function handle()
    {
        Log::info("Starting CSV processing", ['file_id' => $this->fileUpload->id, 'file_path' => $this->fileUpload->path]);

        $this->fileUpload->update(['status' => 'processing']);
        event(new UploadStatusUpdated($this->fileUpload));

        try {
            $fullPath = storage_path('app/' . $this->fileUpload->path);
            
            if (!file_exists($fullPath)) {
                throw new \Exception("CSV file not found at path: {$fullPath}");
            }
            
            $csv = Reader::createFromPath($fullPath, 'r');
            $csv->setHeaderOffset(0);

            $rowCount = 0;

            foreach ($csv as $record) {
                $cleanRecord = array_map(fn($value) =>
                    mb_convert_encoding(trim($value), 'UTF-8', 'UTF-8'), $record);

                Product::updateOrCreate(
                    ['unique_key' => $cleanRecord['UNIQUE_KEY']],
                    [
                        'product_title' => $cleanRecord['PRODUCT_TITLE'] ?? '',
                        'product_description' => $cleanRecord['PRODUCT_DESCRIPTION'] ?? '',
                        'style' => $cleanRecord['STYLE#'] ?? '',
                        'sanmar_mainframe_color' => $cleanRecord['SANMAR_MAINFRAME_COLOR'] ?? '',
                        'size' => $cleanRecord['SIZE'] ?? '',
                        'color_name' => $cleanRecord['COLOR_NAME'] ?? '',
                        'piece_price' => $cleanRecord['PIECE_PRICE'] ?? 0,
                    ]
                );

                $rowCount++;
            }

            $this->fileUpload->update(['status' => 'completed']);
            event(new UploadStatusUpdated($this->fileUpload));

            Log::info("CSV processing completed", [
                'file_id' => $this->fileUpload->id,
                'rows_processed' => $rowCount
            ]);
        } catch (\Throwable $e) {
            $this->fileUpload->update(['status' => 'failed']);
            event(new UploadStatusUpdated($this->fileUpload));

            Log::error("CSV processing failed", [
                'file_id' => $this->fileUpload->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}