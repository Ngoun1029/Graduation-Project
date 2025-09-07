<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $hidden = [];

    protected $fillable = [
        'category_id',
        'seller_id',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'sale_price',
        'status',
        'sale_count',
        'review_count',
        'image',
        'free_delivery',
        'delivery_fee',
        'in_stock',
        'badge',
        'attributes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'stock' => 'integer',
        'sale_count' => 'integer',
        'review_count' => 'integer',
        'free_delivery' => 'boolean',
        'in_stock' => 'boolean',
        'image' => 'array',
        'attributes' => 'array',
    ];


    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
