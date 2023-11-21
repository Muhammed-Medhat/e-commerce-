<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;


trait DeleteBase64Image
{
    public function DeleteBase64Image($image, $folder)
    {
                if (!empty($image)) {
                    #create a path image 
                    $path = "images/$folder/$image";
                    #check if it path exists to delete ro not
                    if (File::exists(public_path($path))) {
                        #delete image
                        File::delete(public_path($path));
                    }
                    
                }
    }
}