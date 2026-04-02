<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = ['image', 'description', 'expires_at', 'product_id'];
    
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function product()
    {
        // أضفنا أسماء الحقول صراحة لقطع الشك باليقين في SQL Server
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
