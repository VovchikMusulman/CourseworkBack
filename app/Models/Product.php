<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'description',
        'price',
        'sale_price',
        'quantity',
        'material',
        'color',
        'dimensions',
        'in_stock',
        'is_featured',
        'rating',
        'images',
    ];

    protected $casts = [
        'dimensions' => 'array',
        'images' => 'array',
        'shipping_id',
    ];

    public function shipping()
    {
        return $this->belongsTo(Shipping::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
