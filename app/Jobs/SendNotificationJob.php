<?php

namespace App\Jobs;

use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class SendNotificationJob implements ShouldQueue
{
    use Queueable;

    use Dispatchable, Queueable;

    public function __construct(
        public int $userId,
        public string $type,
        public string $title,
        public string $body,
        public array $data = []
    ) {}

    public function handle()
    {
        NotificationService::send(
            $this->userId,
            $this->type,
            $this->title,
            $this->body,
            $this->data
        );
    }
}
