<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle()
    {
        try {
            DB::transaction(function () {
                // Update order status to processing
                $this->order->update(['status' => 'processing']);

                // Process each item in the order
                foreach ($this->order->items as $item) {
                    $orderItem = $item->pivot;
                    
                    // Check if enough stock is available
                    if ($item->stock < $orderItem->quantity) {
                        throw new \Exception("Insufficient stock for item: {$item->name}");
                    }

                    // Decrease stock
                    $item->decrement('stock', $orderItem->quantity);
                }

                // Mark order as completed
                $this->order->update(['status' => 'completed']);
            });
        } catch (\Exception $e) {
            $this->order->update(['status' => 'failed']);
            throw $e; // Re-throw to mark job as failed
        }
    }
}
