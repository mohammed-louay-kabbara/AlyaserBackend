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
        // أضفنا 'offer_products' كبارامتر ثاني لتحديد اسم الجدول بوضوح
        return $this->belongsToMany(Product::class, 'offer_products') 
                    ->withPivot('quantity', 'purchase_type')
                    ->withTimestamps();
    }
    
    

}
