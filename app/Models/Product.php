<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'ameen_guid','name','retail_price' ,'wholesale_price' , 'quantity', 'category_id', 'image'
    ];
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
