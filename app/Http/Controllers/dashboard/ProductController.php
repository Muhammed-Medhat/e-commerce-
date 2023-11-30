<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\ImageProduct;
use App\Models\Product;
use App\Models\RelatedProduct;
use Illuminate\Http\Request;
use App\Traits\DeleteBase64Image;
use App\Traits\UploadBese64Image;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ProductController extends Controller
{
        //traits
        use UploadBese64Image; # store image
        use DeleteBase64Image; # delete image

    function createProduct(CreateProductRequest $request) {
        try {
            #get validation data requests
            $validation_data = $request->validated();
            #create slug
            $validation_data['slug'] = Str::slug($validation_data['name']);
            #create product without images and related_products
            $product = Product::create(Arr::except($validation_data,['images', 'related_products']));
            # get images from request
            $images = Arr::get($validation_data,'images');
            
            $paths = [];
            if ($images !== null) {
                foreach ($images as $key => $image) {
                    $paths[] = $this->UploadBese64Image($image,'products');
                }
                //-- store images in DB --//
                foreach ($paths as $path) {
                    $photes = [
                        new ImageProduct(['url' => $path])
                    ];
                    $product->images()->saveMany($photes);
                }
            }
            
            # get related_products
            $related_products = Arr::get($validation_data,'related_products');
            ##########related_product############
            if ($related_products) {
                foreach ($related_products as $related_product) {
                    $products = [
                        new RelatedProduct(['related_product' => $related_product])
                    ];
                    $product->related_products()->saveMany($products);
                }
            }
            return response()->json(['message'=>'product has been created', 'status'=> true]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    function listing() {
        try {
            $products = Product::paginate();
            return response()->json(['data'=>$products, 'status'=>true]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    function updateProduct(UpdateProductRequest $request, $id) {
        try {
            $product = Product::find($id);
            if (!$product) {
                return response()->json(['message'=>"something error", 'status'=>false]);
            }
            $validation_data = $request->validated();

            #slug
            if (array_key_exists('name', $validation_data)) {
                #create slug
                $validation_data['slug'] = Str::slug($validation_data['name']);
            }

            # get images from request
            $images = Arr::get($validation_data,'images');
            if ($images == null) {
                ## delete old image ##
                $images = ImageProduct::where('product_id', $id)->get('url');
                foreach ($images as $key => $image) {
                    // Get Original name of image without path and deleted
                    $this->DeleteBase64Image($image->getRawOriginal('url'),'products');
                }
                #delete from DB
                $product->images()->delete();
            } else {
    
                ## delete old image ##
                $images_product = ImageProduct::where('product_id', $id)->get('url');
                foreach ($images_product as $key => $image) {
                    // Get Original name of image without path and deleted
                    $this->DeleteBase64Image($image->getRawOriginal('url'),'products');
                }
                #delete from DB
                $product->images()->delete();
                ### insert new images
                
                $paths = [];
                foreach ($images as $key => $image) {
                    $paths[] = $this->UploadBese64Image($image,'products');
                }
                //-- store images in DB --//
                foreach ($paths as $path) {
                    $photes = [
                        new ImageProduct(['url' => $path])
                    ];
                    $product->images()->saveMany($photes);
                }
            }

            #update product without images and related_products
            $product->update(Arr::except($validation_data,['images','related_products']));
            return response()->json(['message'=>"product has been updated", 'status'=>true]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }

    }
    function viewProduct($id) {
        try {
            $product = Product::find($id);
            if (!$product) {
                return response()->json(['message'=>'not found', 'status'=>false]);
            }
            return response()->json([$product, 'status'=>true]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    function deleteProduct($id) {
        try {
            $product = Product::find($id);
            if (!$product) {
                return response()->json(['message'=>'not found', 'status'=>false]);
            }
            #delete image
            $images_product = ImageProduct::where('product_id', $id)->get('url');
            foreach ($images_product as $key => $image) {
                // Get Original name of image without path and deleted
                $this->DeleteBase64Image($image->getRawOriginal('url'),'products');
            }
            ### delete product
            $product->delete();
            return response()->json(['messaage'=>'product has been deleted', 'status'=>true]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
