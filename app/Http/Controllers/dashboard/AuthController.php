<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCustomerRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\User;
use App\Traits\UploadBese64Image;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class AuthController extends Controller
{
    use UploadBese64Image;

    public function __construct() {
        $this->middleware(['auth:sanctum'], ['only' => ['logout','updatePassword','createCustomer','updateCustomer']]);
    }

    function login(LoginRequest $request) {
        try {
            #check email & password =>
            if (!Auth::attempt($request->only(['email', 'password']), $request->has('remember_me'))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }
            #get validation data requests => 
            $validation_data = $request->validated();
            # get admin =>
            $admin = User::where('email',$validation_data['email'])->first();
            #check id admin or not =>
            if ($admin->is_admin !== 1) {
                return response()->json(['message'=>'somthing else' , 'status'=>false]);
            }
            #send admin =>
            return response()->json([
                'data'=>$admin,
                'token_expired' => config('sanctum.expiration'). ' minute',
                'token' => $admin->createToken("API TOKEN")->plainTextToken,
                'status'=>true])
                ;

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $accessToken = $request->bearerToken();
            // Get access token from database
            $token = PersonalAccessToken::findToken($accessToken);

            // Revoke token
            $token->delete();
            return [
                'message' => 'user logged out'
            ];
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'old_password' => 'required|min:8',
                    'new_password' => 'required|confirmed|min:8'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            if (Hash::check($request->old_password, auth()->user()->password)) {
             /*get user => */   $user = auth()->user();
             /*get user and uodate password => */   User::find($user->id)->update(['password' => Hash::make($request->new_password)]);
                return response()->json(['message' => 'password has been changed']);
            } else {
                return response()->json(['message' => 'old password wrong']);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    function createCustomer(CreateCustomerRequest $request) {
        try {
            #get validation data requests => 
            $validation_data = $request->validated();
            $validation_data['is_admin'] = 0;
            $validation_data['password'] = Hash::make($validation_data['password']);


            if (array_key_exists('image', $validation_data)) {
                    $validation_data['image'] = $this->UploadBese64Image($validation_data['image'],'users');
            } else {
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

    function updateCustomer(UpdateCustomerRequest $request, $id) {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['message'=>'user not found', 'status'=>false],404);
            }
            #get validation data requests => 
            $validation_data = $request->validated();
            $validation_data['is_admin'] = 0;

            if (array_key_exists('image', $validation_data)) {
                if ($validation_data['image'] !== null) {
                    ## delete old image ##
                    $image = $user->getRawOriginal('image');
                    if (!empty($image)) {
                        if (File::exists(public_path('images/users/' . $image))) {
                            File::delete(public_path('images/users/' . $image));
                        }
                    }

                    ## insert new image ##
                    $validation_data['image'] = $this->UploadBese64Image($validation_data['image'],'users');
                } else {
                    ## delete image ##
                    $image = $user->getRawOriginal('image');
                    if (!empty($image)) {
                        if (File::exists(public_path('images/users/' . $image))) {
                            File::delete(public_path('images/users/' . $image));
                            $validation_data['image'] = null;
                        }
                    }
                }
            }

            #create user =>
            $user = $user->update($validation_data);
            return response()->json(['data'=>$user, 'status'=>true]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    function deleteCustomer($id) {
        
        try {
            $customer = User::where('id',$id)->where('is_admin',0)->first();
            if (!$customer) {
                return response()->json(['message'=>'somthing wrong', 'status'=>false]);
            } else {
                    ## delete image ##
                    $image = $customer->getRawOriginal('image');
                    if (!empty($image)) {
                        if (File::exists(public_path('images/users/' . $image))) {
                            File::delete(public_path('images/users/' . $image));
                            $customer->image = null;
                        }
                    }
                $customer->delete();
                return response()->json(['message'=>"customer has been deleted",'status'=>true]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    function listing() {
        try {
            $customers= User::where('is_admin',0)->paginate();
            return response()->json(['data'=>$customers,'status'=>true]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    function viewCustomer($id) {
        try {
            $customer = User::getCustomerById($id);
            if (!$customer) {
                return response()->json(['message'=>'something wrong','status'=>false]);
            }
            return response()->json(['data'=>$customer,'status'=>true]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

}
