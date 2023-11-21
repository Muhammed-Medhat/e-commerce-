<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{

    public function __construct() {
        $this->middleware(['auth:sanctum'],['only' =>
            ['logout',
            'updatePassword',]
            ]);
    }

    function login(LoginRequest $request) {
        try {
            #check email & password
            if (!Auth::attempt($request->only(['email', 'password']), $request->has('remember_me'))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }
            #get validation data requests
            $validation_data = $request->validated();
            # get admin
            $admin = User::where('email',$validation_data['email'])->first();
            #check id admin or not
            if ($admin->is_admin !== 1) {
                return response()->json(['message'=>'somthing else' , 'status'=>false]);
            }
            #send admin =>
            return response()->json([
                'data'=>$admin,
                'token_expired' => config('sanctum.expiration'). ' minute',
                'token' => $admin->createToken("API TOKEN")->plainTextToken, #create token of this admin
                'status'=>true]);

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
            #validation request
            $validateUser = Validator::make(
                $request->all(),
                [
                    'old_password' => 'required|min:8',
                    'new_password' => 'required|confirmed|min:8'
                ]
            );
            // valid error message
            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            #check if old password true or not
            if (Hash::check($request->old_password, auth()->user()->password)) {
                #Get Auth User
                $user = auth()->user();
                #get user and upodate password with hashing it
                User::find($user->id)->update(['password' => Hash::make($request->new_password)]);
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

}
