<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

    class Order extends Model
    {
        protected $fillable = [
            'user_id', 'total_amount', 
            'status', 'notes', 'ameen_guid', 'is_synced','problem','order_number','delivery_type'
        ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
