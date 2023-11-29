<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageProduct extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected function url(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // If $value is null, return null
                return $value ? asset('images/products/' . $value) : null;
            }
        );
    }

    ##############################
    ####  Relationships  ####
    ##############################

    /**
     * Get the product that owns the image.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
