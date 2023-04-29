<?php

namespace App\Jobs;

use App\Models\UuidCheck;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

/**
 * Delete all expired UUID checks, based on their `expire_at` time.
 */
class ExpireUuidChecks implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Preferred queue constant.
     */
    const QUEUE = 'low';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct() {
        // Set queue
        $this->onQueue(Self::QUEUE);
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware() {
        return [
            // Release exclusive lock after a day (failure)
            (new WithoutOverlapping())
                ->expireAfter(24 * 60 * 60)
                ->dontRelease()
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        UuidCheck::expired()->delete();
    }

    public function retryUntil() {
        // Matches interval in \App\Console\Kernel::schedule for ExpirePayments
        return now()->addHour();
    }
}
