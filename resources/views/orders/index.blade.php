@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Available Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Total Value</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td>{{ $item->name }}</td>
                                    <td>${{ number_format($item->price, 2) }}</td>
                                    <td>{{ $item->stock }}</td>
                                    <td>${{ number_format($item->price * $item->stock, 2) }}</td>
                                    <td>
                                        <form action="{{ route('items.destroy', $item) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this item?')">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Create New Order</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
                    @csrf
                    <div id="orderItems">
                        <div class="order-item mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <select name="items[0][id]" class="form-select" required>
                                        <option value="">Select Item</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }} (${{ number_format($item->price, 2) }}) - Stock: {{ $item->stock }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" name="items[0][quantity]" class="form-control" placeholder="Quantity" min="1" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-secondary mb-3" onclick="addItem()">Add Another Item</button>
                    <button type="submit" class="btn btn-primary">Create Order</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Orders</h5>
            </div>
            <div class="card-body">
                @foreach($orders as $order)
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-muted">Order #{{ $order->id }}</h6>
                            <p class="card-text">
                                Status: <span class="badge bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'failed' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </p>
                            <p class="card-text">Total: ${{ number_format($order->total_amount, 2) }}</p>
                            <div class="small">
                                @foreach($order->items as $item)
                                    <div>{{ $item->name }} x {{ $item->pivot->quantity }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
let itemCount = 1;

function addItem() {
    const template = `
        <div class="order-item mb-3">
            <div class="row">
                <div class="col-md-6">
                    <select name="items[${itemCount}][id]" class="form-select" required>
                        <option value="">Select Item</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}">{{ $item->name }} (${{ number_format($item->price, 2) }}) - Stock: {{ $item->stock }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="number" name="items[${itemCount}][quantity]" class="form-control" placeholder="Quantity" min="1" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.order-item').remove()">Remove</button>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('orderItems').insertAdjacentHTML('beforeend', template);
    itemCount++;
}
</script>
@endsection
