<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileUploadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'original_name' => $this->original_name,
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
