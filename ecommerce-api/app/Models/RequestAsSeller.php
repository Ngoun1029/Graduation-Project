<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestAsSeller extends Model
{
    use HasFactory;

    protected $table = 'request_as_seller';

    protected $fillable = [
        'user_id',
        'description',
        'request_date',
        'pending_status',
    ];

    protected $casts = [
        'request_date' => 'datetime',
    ];

    protected $hidden = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
