<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = ['image', 'description', 'expires_at', 'price'];
    
    protected $casts = [
        'expires_at' => 'datetime',
    ];
    public function products()
    {
        // نربط المنتجات بالعرض مع جلب الكمية المحددة داخل العرض
        return $this->belongsToMany(Product::class, 'offer_product')
                    ->withPivot('quantity');
    }
    

}
