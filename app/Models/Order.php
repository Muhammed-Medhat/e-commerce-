<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $with = ['user','products_order'];

    ##############################
    ####  Relationships  ####
    ##############################

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the products_order for the order.
     */
    public function products_order()
    {
        return $this->hasMany(OrderProdect::class,'order_id', 'id');
    }
}