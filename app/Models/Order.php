<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_id',
        'user_id',
        'order_number',
        'status',
        'total',
        'item_count',
        'is_paid',
        'shipping_address',
        'billing_address',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }


    public function shipping()
    {
        return $this->belongsTo(Shipping::class);
    }

    public function returns()
    {
        return $this->hasMany(ReturnModel::class);
    }
}
