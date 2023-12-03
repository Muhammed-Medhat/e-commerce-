<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProdect extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $with = ['product'];

    ##############################
    ####  Relationships  ####
    ##############################

    /**
     * Get the order that owns the order_product.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product that owns the order_product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
