<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'total_amount',
        'status'
    ];

    public function items()
    {
        return $this->belongsToMany(Item::class, 'order_items')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }
}
