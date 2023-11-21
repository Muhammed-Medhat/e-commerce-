<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait UploadBese64Image
{
    public function UploadBese64Image($image64, $folder = 'images')
    {
        if ($image64) {
            $image_64 = $image64; //your base64 encoded data
            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
            $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
            // find substring fro replace here eg: data:image/png;base64,
            $image = str_replace($replace, '', $image_64);
            $image = str_replace(' ', '+', $image);
            $imageName = Str::random(5) . '.' . $extension;
            #insert image in folder
            Storage::disk($folder)->put($imageName, base64_decode($image));
            return $imageName;
        }
    }
}