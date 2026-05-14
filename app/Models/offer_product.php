<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class offer_product extends Model
{
    protected $fillable = ['offer_id', 'product_id', 'quantity','purchase_type'];
    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
