<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\exchange_rate;

class Product extends Model
{
    protected $fillable = [
        'ameen_guid','name', 'ameen_code','retail_price','wholesale_price','quantity','category_id','image'
    ];

    /**
     * ⭐ علاقات
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function offers()
    {
        return $this->belongsToMany(Offer::class, 'offer_products')
                    ->withPivot('quantity');
    }

    /**
     * ⭐ تحويل السعر إلى دولار (يستبدل القيمة الأصلية)
     */
    public function getRetailPriceAttribute($value)
    {
        $rate = exchange_rate::where('is_default', true)->value('rate') ?? 1;

        return round($value / $rate, 2);
    }

    public function getWholesalePriceAttribute($value)
    {
        $rate = exchange_rate::where('is_default', true)->value('rate') ?? 1;

        return round($value / $rate, 2);
    }
}
