<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelatedProduct extends Model
{
    use HasFactory;
    protected $guarded = [];


    ##############################
    ####  Relationships  ####
    ##############################

    /**
     * Get the product that owns the related_product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
