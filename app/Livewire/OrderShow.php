<?php

namespace App\Livewire;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class OrderShow extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to order.');
        }

        $this->order = $order->load('orderItems.product');
    }

    public function render(): View
    {
        return view('livewire.order-show')
            ->title('Order #' . $this->order->id);
    }
}
