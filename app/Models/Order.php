<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $with = ['created_by','user','products_order'];

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
        return $this->hasMany(OrderProdect::class);
    }

    /**
     * Get the user that owns the order. created_by
     */
    public function created_by()
    {
        return $this->belongsTo(User::class,'created_by', 'id');
    }
    
}
