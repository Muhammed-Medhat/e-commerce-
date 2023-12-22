<?php
/*
    ### staff validation it is the same customer but change is_admin from 0 to 1 ###
*/
namespace App\Http\Controllers\dashboard;

use App\Exports\StaffsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\User;
use App\Traits\DeleteBase64Image;
use App\Traits\UploadBese64Image;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Excel;

class StaffController extends Controller
{
    //traits
    use UploadBese64Image; # store image
    use DeleteBase64Image; # delete image
    
    
    function createStaff(CreateCustomerRequest $request) {
        try {
            #get validation data requests
            $validation_data = $request->validated();
            # set user as a staff auto
            $validation_data['is_admin'] = 1;
            # hashing password
            $validation_data['password'] = Hash::make($validation_data['password']);
            #check if i have image key in request
            if (array_key_exists('image', $validation_data)) {
                #store path image in foleder and save image in DB
                $validation_data['image'] = $this->UploadBese64Image($validation_data['image'],'users');
            } else {
                #set image column in DB as null
                    $validation_data['image'] = null;
                }

            #create user =>
            $user = User::create($validation_data);

            return response()->json(['data'=>$user, 'status'=>true]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
    function updateStaff(UpdateCustomerRequest $request, $id) {
        try {
            #get user by ID
            $user = User::find($id);
            #check if user nof found in DB
            if (!$user) {
                return response()->json(['message'=>'user not found', 'status'=>false],404);
            }
            #get validation data requests => 
            $validation_data = $request->validated();
            # set user as a staff auto
            $validation_data['is_admin'] = 1;
            #check if i have password key in request
            if (array_key_exists('password', $validation_data)) {
                # hashing password
                $validation_data['password'] = Hash::make($validation_data['password']);
            }
            #check if i have image key in request
            if (array_key_exists('image', $validation_data)) {
                #check if i have a value in image Or NOT 
                if ($validation_data['image'] !== null) {
                    ## delete old image ##
                    $image = $user->getRawOriginal('image'); // Get Original name of image without path 
                    $this->DeleteBase64Image($image,'users'); // Delete IMAGE in folder users

                    ## insert new image in DB & insert new image in folder users ##
                    $validation_data['image'] = $this->UploadBese64Image($validation_data['image'],'users');

                } else { // A value of image key is Null thats mian delete image from image folder and DB
                    # Get Original name of image without path 
                    $image = $user->getRawOriginal('image'); 
                    ## delete image ##
                    $this->DeleteBase64Image($image,'users');
                }
            }
            #update user =>
            $user->update($validation_data);

            return response()->json(['data'=>$user, 'status'=>true]);
            
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    function deleteStaff($id) {
        
        try {
            #get user by ID & Make sure it's from staff
            $staff = User::where('id',$id)->where('is_admin',1)->first();
            #check if user not found in DB or NOT
            if (!$staff) {
                return response()->json(['message'=>'somthing wrong', 'status'=>false],404);
            } else {
                    ## delete image ##
                    $image = $staff->getRawOriginal('image'); // Get Original name of image without path 
                    $this->DeleteBase64Image($image,'users'); // Delete IMAGE in folder users
                    $staff->delete(); // delete customer in DB
                return response()->json(['message'=>"staff has been deleted",'status'=>true]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //get all users customers and admins
    // function listing(Request $request) {
    //     try {

    //         // Validation Rules
    //         $validator = Validator::make($request->all(), [
    //             'q'=>['string'],
    //             'sort_by' => [Rule::in(["a-z","z-a","old","new"])],
    //             'filter_by_date_range' => ['json'],
    //             'is_admin'=>[Rule::in([0, 1])],
    //         ]);

    //         // valid error message
    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'validation error',
    //                 'errors' => $validator->errors()
    //             ], 422);
    //         }
    //         #Preparation query
    //         $staff = User::query();
    //         //////////////////////////// start filters //////////////////////////////////////////////

    //         /*admin or customer filter */
    //         if ($request->has('is_admin')) {
    //             $request->is_admin == 1 ? $staff->where('is_admin',1) : $staff->where('is_admin',0);
    //         }

    //         /* Filter by data range */
    //         if(isset($request->filter_by_date_range)){
    //             $filter_by_date_range = json_decode($request->filter_by_date_range);
    //             $staff->whereBetween('created_at', [
    //                 Carbon::parse($filter_by_date_range[0])->format('Y-m-d\TH:i:s.u\Z'),
    //                 Carbon::parse($filter_by_date_range[1])->format('Y-m-d\TH:i:s.u\Z'),
    //             ]);
    //         }
    //         /* Filter by search */
    //         if(isset($request->q)){
    //             $query = $request->q;
    //             $staff
    //             ->where('name', 'like', "%{$query}%");
    //         }
    
    //         /* Sort asc */
    //         if(isset($request->sort_by) && $request->sort_by == "a-z"){
    //             $staff->orderBy("id","asc");
    //         }
    
    //         /* Sort desc */
    //         if(isset($request->sort_by) && $request->sort_by == "z-a"){
    //             $staff->orderBy("id","desc");
    //         }
    
    //         /* Filter by date old */
    //         if(isset($request->sort_by) && $request->sort_by == "old"){
    //             $staff->orderBy("created_at","asc");
    //         }
        
    //         /* Filter by date new */
    //         if(isset($request->sort_by) && $request->sort_by == "new"){
    //             $staff->orderBy("created_at","desc");
    //         }
    //         //////////////////////////// end filters //////////////////////////////////////////////

    //         return response()->json(['data'=>$staff->paginate(), 'status'=>true]);

    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $th->getMessage()
    //         ], 500);
    //     }
    // }

    function viewStaff($id) {
        try {
            #get user by ID & Make sure it's from staff
            $staff = User::where('id',$id)->where('is_admin',1)->first();
            #check if user nof found in DB
            if (!$staff) {
                return response()->json(['message'=>'something wrong','status'=>false],404);
            }
            return response()->json(['data'=>$staff,'status'=>true]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function export_staff(Excel $excel , Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => ['array'],
                'id.*' => ['exists:users,id'],
            ]);
            /// valid error message //
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validator->errors()
                ], 401);
            }
    
            if ($request->id) {
                return $excel->download(new StaffsExport($request->id), 'staff.xlsx');
            }else {
                return $excel->download(new StaffsExport(), 'staff.xlsx');
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }

    }
}
