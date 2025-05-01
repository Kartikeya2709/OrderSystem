<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessOrder;
use App\Models\Item;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $items = Item::all();
        $orders = Order::with('items')->latest()->get();
        return view('orders.index', compact('items', 'orders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            // Create order
            $order = new Order();
            $total = 0;

            // Calculate total and prepare items
            $orderItems = [];
            foreach ($request->items as $item) {
                $dbItem = Item::findOrFail($item['id']);
                $total += $dbItem->price * $item['quantity'];
                $orderItems[$item['id']] = [
                    'quantity' => $item['quantity'],
                    'price' => $dbItem->price
                ];
            }

            $order->total_amount = $total;
            $order->save();

            // Attach items to order
            $order->items()->attach($orderItems);

            // Dispatch job to process order
            ProcessOrder::dispatch($order);

            DB::commit();

            return redirect()->route('orders.index')
                ->with('success', 'Order created successfully and is being processed.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error creating order: ' . $e->getMessage());
        }
    }
}
