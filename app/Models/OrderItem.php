<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'purchase_type', 
        'quantity', 'unit_price', 'sub_total'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
