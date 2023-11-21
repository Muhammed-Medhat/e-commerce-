<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;


trait DeleteBaser64Image
{
    public function DeleteBaser64Image($image, $folder)
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