<?php

namespace App\Events;

use App\Models\FileUpload;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Http\Resources\FileUploadResource;
use Illuminate\Support\Facades\Log;

class UploadStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $upload;

    public function __construct(FileUpload $upload)
    {
        $this->upload = (new FileUploadResource($upload))->resolve();
        Log::info('UploadStatusUpdated event constructed', ['upload' => $this->upload]);
    }

    public function broadcastOn()
    {
        Log::info('Broadcasting on uploads channel');
        return new Channel('uploads');
    }

    public function broadcastWith()
    {
        Log::info('Broadcasting with data', ['upload' => $this->upload]);
        return ['upload' => $this->upload];
    }
} 