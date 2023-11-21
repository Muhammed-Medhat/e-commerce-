<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * 
 */
trait DeleteBaser64Image
{
    public function DeleteBaser64Image($image, $folder = 'images')
    {
                if (!empty($image)) {
                    $path = "images/$folder/$image";
                    if (File::exists(public_path($path))) {
                        File::delete(public_path($path));
                    }
                    
                }
    }
}