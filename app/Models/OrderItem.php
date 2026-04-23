<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'purchase_type', 
        'quantity', 'unit_price', 'sub_total','offer_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function offer()
    {
        // علاقة "ينتمي إلى" لأن جدول order_items يحتوي على offer_id
        return $this->belongsTo(Offer::class);
    }
}
