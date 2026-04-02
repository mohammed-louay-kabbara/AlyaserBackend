<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class exchange_rate extends Model
{
    protected $fillable = ['currency_name', 'rate', 'is_default'];

    protected $casts = [
        'rate' => 'decimal:4',
        'is_default' => 'boolean',
    ];
}
