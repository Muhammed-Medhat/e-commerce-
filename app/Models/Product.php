<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $with = ['brand','category','related_products','images'];

    ##############################
    ####  Relationships  ####
    ##############################

    /**
     * Get the images for the product.
     */
    public function images()
    {
        return $this->hasMany(ImageProduct::class,'product_id', 'id');
    }

    /**
     * Get the related_products for the product.
     */
    public function related_products()
    {
        return $this->hasMany(RelatedProduct::class,'product_id', 'id');
    }

    /**
     * Get the brand that owns the products.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the category that owns the products.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the products_order for the order.
     */
    public function products_order()
    {
        return $this->hasMany(OrderProdect::class,'product_id', 'id');
    }
}
