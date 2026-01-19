<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class LowStockNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param array $products
     */
    public function __construct(
        public array $products
    ) {}

    /**
     * Execute the job.
     * Implementation will be completed in Task 12.
     */
    public function handle(): void
    {
        // Email sending logic will be implemented in Task 12
    }
}
