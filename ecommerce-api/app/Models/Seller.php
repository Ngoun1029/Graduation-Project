<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    use HasFactory;

    protected $fillable = [
       'user_id',
       'shop_name',
       'store_slug',
       'verification_status',
       'rating',
       'status',
       'balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'status' => 'integer',
        'verification_status' => 'integer',
        'rating' => 'integer',
    ];

    protected $hidden = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
