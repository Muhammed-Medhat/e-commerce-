<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Category extends Model
{
    use HasFactory;

    protected $guarded = [];
    // protected $with = ['sub_categories'];


    protected function image(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // If $value is null, return null
                return $value ? asset('images/category/' . $value) : null;
            }
        );
    }


    /**
     * Get the sub_categories for the blog post.
     */
    public function sub_categories()
    {
        return $this->hasMany(Category::class ,'parent_category');
    }

    /**
     * Get the parent catergory for the blog post.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class ,'parent_category');
    }

}
