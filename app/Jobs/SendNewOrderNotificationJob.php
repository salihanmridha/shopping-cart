<?php

namespace App\Jobs;

use App\Mail\NewOrderPlaced;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendNewOrderNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    public function handle(): void
    {
        $adminEmail = config('shop.dummy_admin_email');

        // Ensure order items and user are loaded
        $this->order->load(['orderItems.product', 'user']);

        Mail::to($adminEmail)->send(new NewOrderPlaced($this->order));
    }
}
