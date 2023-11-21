<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Brand extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function logo(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // If $value is null, return null
                return $value ? asset('images/brand/' . $value) : null;
            }
        );
    }
}
