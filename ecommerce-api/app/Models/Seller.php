<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    use HasFactory;

    protected $fillable = [
       'user_id',
       'rating',
       'status',
       'balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'status' => 'integer',
        'rating' => 'integer',
    ];

    protected $hidden = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
