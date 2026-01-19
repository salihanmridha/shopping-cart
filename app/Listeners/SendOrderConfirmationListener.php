<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Jobs\SendOrderConfirmationJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderConfirmationListener implements ShouldQueue
{
    public function __construct() {}

    public function handle(OrderCompleted $event): void
    {
        // Delay by 6 seconds to avoid email rate limiting
        SendOrderConfirmationJob::dispatch($event->order)->delay(now()->addSeconds(6));
    }
}
