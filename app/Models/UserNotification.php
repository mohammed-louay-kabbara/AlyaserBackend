<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $fillable = [
        'user_id', 'title', 'body', 'destination', 'is_read',
    ];
}
