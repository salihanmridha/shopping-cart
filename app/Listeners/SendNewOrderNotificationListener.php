<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Jobs\SendNewOrderNotificationJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewOrderNotificationListener implements ShouldQueue
{
    public function __construct() {}

    /**
     * @param OrderCompleted $event
     * @return void
     */
    public function handle(OrderCompleted $event): void
    {
        // Delay by 3 seconds to avoid email rate limiting by mailtrap
        SendNewOrderNotificationJob::dispatch($event->order)->delay(now()->addSeconds(3));
    }
}
