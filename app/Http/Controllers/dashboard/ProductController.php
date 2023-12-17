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
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

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

    function listing(Request $request) {
        try {
                // Validation Rules
                $validator = Validator::make($request->all(), [
                    'q' => ['string'],
                    'sort_by' => [Rule::in(["rating-high-to-low","rating-low-to-high","low-to-high", "high-to-low", "a-z", "z-a", "old", "new"])],
                    'price_range' => ['json'],
                    'filter_by_brand' => ['json'],
                    'filter_by_categories' => ['json'],
                    'filter_by_date_range' => ['json'],
                ]);
    
                // valid error message
                if ($validator->fails()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'validation error',
                        'errors' => $validator->errors()
                    ], 422);
                }
                #Preparation query
                $products = Product::query();
                            //////////////////////////// start filters //////////////////////////////////////////////

                /* Filter by data range */
                if(isset($request->filter_by_date_range)){
                    $filter_by_date_range = json_decode($request->filter_by_date_range);
                    $products->whereBetween('created_at', [
                        Carbon::parse($filter_by_date_range[0])->format('Y-m-d\TH:i:s.u\Z'),
                        Carbon::parse($filter_by_date_range[1])->format('Y-m-d\TH:i:s.u\Z'),
                    ]);
                }
                /* Filter by search */
                if(isset($request->q)){
                    $query = $request->q;
                    $products
                    ->where('name', 'like', "%{$query}%");
                }
        
                /* Sort asc */
                if(isset($request->sort_by) && $request->sort_by == "a-z"){
                    $products->orderBy("id","asc");
                }
        
                /* Sort desc */
                if(isset($request->sort_by) && $request->sort_by == "z-a"){
                    $products->orderBy("id","desc");
                }
        
                /* Filter by date old */
                if(isset($request->sort_by) && $request->sort_by == "old"){
                    $products->orderBy("created_at","asc");
                }
            
                /* Filter by date new */
                if(isset($request->sort_by) && $request->sort_by == "new"){
                    $products->orderBy("created_at","desc");
                }
                // Filter by brands
                if (isset($request->filter_by_brands)) {
                    $filter_by_brands = json_decode($request->filter_by_brands);
                    $products->whereHas('brand', function ($query) use ($filter_by_brands) {
                        $query->whereIn('name', $filter_by_brands);
                    });
                }

                // Filter by categories
                if (isset($request->filter_by_categories)) {
                    $filter_by_categories = json_decode($request->filter_by_categories);
                    $products->whereHas('category', function ($query) use ($filter_by_categories) {
                        $query->whereIn('name', $filter_by_categories);
                    });
                }
                // Filter by price range
                if (isset($request->price_range)) {
                    $price_range = json_decode($request->price_range); // ex. [10, 20]
                    $products->whereBetween('price', $price_range);
                }
                // Filter by price low-to-high
                if (isset($request->sort_by) && $request->sort_by == "low-to-high") {
                    $products->orderBy('price','asc');
                
                }

                // Filter by price high-to-low
                if (isset($request->sort_by) && $request->sort_by == "high-to-low") {
                    $products->orderBy('price','desc');
                }
        
            return response()->json(['data'=>$products->paginate(), 'status'=>true]);
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
