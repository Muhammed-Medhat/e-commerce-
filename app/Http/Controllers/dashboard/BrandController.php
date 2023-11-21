<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Http\Request;
use App\Traits\DeleteBaser64Image;
use App\Traits\UploadBese64Image;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    //traits
    use UploadBese64Image; # store image
    use DeleteBaser64Image; # delete image

    function createBrand(CreateBrandRequest $request) {
        try {
            #get validation data requests
            $validation_data = $request->validated();
            #check if i have image key in request
            if (array_key_exists('logo', $validation_data)) {
                #store path image in foleder and save image in DB
                $validation_data['logo'] = $this->UploadBese64Image($validation_data['logo'],'brand');
            } else {
                #set image column in DB as null
                    $validation_data['logo'] = null;
                }

            #create user =>
            $user = Brand::create($validation_data);

            return response()->json(['data'=>$user, 'status'=>true]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    function updateBrand(UpdateBrandRequest $request, $id) {
        try {
            #get Brand by ID
            $brand = Brand::find($id);
            #check if brand nof found in DB
            if (!$brand) {
                return response()->json(['message'=>'brand not found', 'status'=>false],404);
            }
            #get validation data requests => 
            $validation_data = $request->validated();
            #check if i have logo key in request
            if (array_key_exists('logo', $validation_data)) {
                #check if i have a value in logo Or NOT 
                if ($validation_data['logo'] !== null) {
                    ## delete old logo ##
                    $logo = $brand->getRawOriginal('image'); // Get Original name of logo without path 
                    $this->DeleteBaser64Image($logo,'brand'); // Delete LOGO in folder brands

                    ## insert new logo in DB & insert new logo in folder Brand ##
                    $validation_data['logo'] = $this->UploadBese64Image($validation_data['logo'],'brand');

                } else { // A value of logo key is Null thats mian delete logo from brand folder and DB
                    # Get Original name of logo without path 
                    $image = $brand->getRawOriginal('logo'); 
                    ## delete image ##
                    $this->DeleteBaser64Image($image,'brand');
                }
            }
            #check if i have website_link key in request & = null
            if (array_key_exists('website_link', $validation_data) && $validation_data['website_link'] == null) {
                    $validation_data['website_link'] = null;
            }
            #check if i have description key in request & = null
            if (array_key_exists('description', $validation_data) && $validation_data['description'] == null) {
                    $validation_data['description'] = null;
            }

            #update brand
            $brand->update($validation_data);

            return response()->json(['data'=>$brand, 'status'=>true]);
            
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    function deleteBrand($id) {
        
        try {
            #get brand by ID & Make sure it's a customer
            $brand = Brand::find($id);
            #check if brand nof found in DB or NOT
            if (!$brand) {
                return response()->json(['message'=>'somthing wrong', 'status'=>false],404);
            } else {
                    ## delete logo ##
                    $logo = $brand->getRawOriginal('logo'); // Get Original name of logo without path 
                    $this->DeleteBaser64Image($logo,'brand'); // Delete LOGO in folder brand
                    $brand->delete(); // delete brand in DB
                return response()->json(['message'=>"brand has been deleted",'status'=>true]);
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
            $brands = Brand::query();
            //////////////////////////// start filters //////////////////////////////////////////////

            /* Filter by data range */
            if(isset($request->filter_by_date_range)){
                $filter_by_date_range = json_decode($request->filter_by_date_range);
                $brands->whereBetween('created_at', [
                    Carbon::parse($filter_by_date_range[0])->format('Y-m-d\TH:i:s.u\Z'),
                    Carbon::parse($filter_by_date_range[1])->format('Y-m-d\TH:i:s.u\Z'),
                ]);
            }
            /* Filter by search */
            if(isset($request->q)){
                $query = $request->q;
                $brands
                ->where('name', 'like', "%{$query}%");
            }
    
            /* Sort asc */
            if(isset($request->sort_by) && $request->sort_by == "a-z"){
                $brands->orderBy("id","asc");
            }
    
            /* Sort desc */
            if(isset($request->sort_by) && $request->sort_by == "z-a"){
                $brands->orderBy("id","desc");
            }
    
            /* Filter by date old */
            if(isset($request->sort_by) && $request->sort_by == "old"){
                $brands->orderBy("created_at","asc");
            }
        
            /* Filter by date new */
            if(isset($request->sort_by) && $request->sort_by == "new"){
                $brands->orderBy("created_at","desc");
            }
            //////////////////////////// end filters //////////////////////////////////////////////

            return response()->json(['data'=>$brands->paginate(), 'status'=>true]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    function viewBrand($id) {
        try {
            #get brand by ID
            $brand = Brand::find($id);
            #check if brand nof found in DB
            if (!$brand) {
                return response()->json(['message'=>'something wrong','status'=>false],404);
            }
            return response()->json(['data'=>$brand,'status'=>true]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
