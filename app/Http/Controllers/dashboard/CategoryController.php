<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Traits\DeleteBase64Image;
use App\Traits\UploadBese64Image;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
        //traits
    use UploadBese64Image; # store image
    use DeleteBase64Image; # delete image

    function createCategory(CreateCategoryRequest $request) {
        try {
            #get validation data requests
            $validation_data = $request->validated();
            #check if i have image key in request
            if (array_key_exists('image', $validation_data)) {
                #store path image in foleder and save image in DB
                $validation_data['image'] = $this->UploadBese64Image($validation_data['image'],'category');
            } else {
                #set image column in DB as null
                    $validation_data['image'] = null;
                }

            #create slug by name
            $validation_data['slug'] = Str::slug($validation_data['name']);

            #create category
            $category = Category::create($validation_data);

            return response()->json(['data'=>$category, 'status'=>true]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    function updateCategory(UpdateCategoryRequest $request, $id) {
        try {
            #get category by ID
            $category = Category::find($id);
            #check if brand nof found in DB
            if (!$category) {
                return response()->json(['message'=>'category not found', 'status'=>false],404);
            }
            #get validation data requests => 
            $validation_data = $request->validated();
            #check if i have image key in request
            if (array_key_exists('image', $validation_data)) {
                #check if i have a value in image Or NOT 
                if ($validation_data['image'] !== null) {
                    ## delete old image ##
                    $image = $category->getRawOriginal('image'); // Get Original name of image without path 
                    $this->DeleteBase64Image($image,'category'); // Delete IMAGE in folder category

                    ## insert new image in DB & insert new image in folder Category ##
                    $validation_data['image'] = $this->UploadBese64Image($validation_data['image'],'category');

                } else { // A value of image key is Null thats mian delete image from category folder and DB //
                    # Get Original name of image without path 
                    $image = $category->getRawOriginal('image'); 
                    ## delete image ##
                    $this->DeleteBase64Image($image,'category');
                }
            }
            #check if i have parent_category key in request & = null
            if (array_key_exists('parent_category', $validation_data) && $validation_data['parent_category'] == null) {
                    $validation_data['parent_category'] = null;
            }
            #check if i have neme key in request to create slug & = null
            if (array_key_exists('name', $validation_data) && $validation_data['name'] == null) {
                    $validation_data['slug'] = Str::slug($validation_data['name']);
            }

            #update category
            $category->update($validation_data);

            return response()->json(['data'=>$category, 'status'=>true]);
            
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    function deleteCategory($id) {

        try {
            #get category by ID & Make sure it's a customer
            $category = Category::find($id);
            #check if category nof found in DB or NOT
            if (!$category) {
                return response()->json(['message'=>'somthing wrong', 'status'=>false],404);
            } else {
                    ## delete image ##
                    $image = $category->getRawOriginal('image'); // Get Original name of image without path 
                    $this->DeleteBase64Image($image,'category'); // Delete IMAGE in folder brand
                    $category->delete(); // delete brand in DB
                return response()->json(['message'=>"category has been deleted",'status'=>true]);
            }
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
                'q'=>['string'],
                'sort_by' => [Rule::in(["a-z","z-a","old","new"])],
                'with_parent' => [Rule::in([0, 1])],
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
            $categories = Category::query();
            //////////////////////////// start filters //////////////////////////////////////////////

            /* Filter by data range */
            if(isset($request->filter_by_date_range)){
                $filter_by_date_range = json_decode($request->filter_by_date_range);
                $categories->whereBetween('created_at', [
                    Carbon::parse($filter_by_date_range[0])->format('Y-m-d\TH:i:s.u\Z'),
                    Carbon::parse($filter_by_date_range[1])->format('Y-m-d\TH:i:s.u\Z'),
                ]);
            }
            /* Filter by search */
            if(isset($request->q)){
                $query = $request->q;
                $categories
                ->where('name', 'like', "%{$query}%");
            }
    
            /* Sort asc */
            if(isset($request->sort_by) && $request->sort_by == "a-z"){
                $categories->orderBy("id","asc");
            }
    
            /* Sort desc */
            if(isset($request->sort_by) && $request->sort_by == "z-a"){
                $categories->orderBy("id","desc");
            }
    
            /* Filter by date old */
            if(isset($request->sort_by) && $request->sort_by == "old"){
                $categories->orderBy("created_at","asc");
            }
        
            /* Filter by date new */
            if(isset($request->sort_by) && $request->sort_by == "new"){
                $categories->orderBy("created_at","desc");
            }
        
            /* Filter by with_parent_category */
            if(isset($request->with_parent) && $request->with_parent == 1){
                $categories->with(['parent']);
            }
            //////////////////////////// end filters //////////////////////////////////////////////

            return response()->json(['data'=>$categories->paginate(), 'status'=>true]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    function viewCategory($id) {
        try {
            #get category by ID
            $category = Category::with(['parent'])->find($id);
            #check if category not found in DB
            if (!$category) {
                return response()->json(['message'=>'something wrong','status'=>false],404);
            }
            return response()->json(['data'=>$category,'status'=>true]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
