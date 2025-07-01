<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Events\JobCompleted;

class TemplateJob implements ShouldQueue
{
    use Queueable;

    protected string $message;
    public $userId;

    /**
     * Create a new job instance.
     */
    public function __construct($message, $userId)
    {
        $this->message = $message;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {   sleep(3);
        Log::info("Queued Job Message: " . $this->message);
        // Dispatch the event
        broadcast(new JobCompleted("Job completed for user ID {$this->userId}", $this->userId));
    }
}
