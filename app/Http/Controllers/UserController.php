<?php

namespace App\Http\Controllers;

use App\Repository\UserRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class UserController extends Controller
{
    protected $userRepository;
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    public function register(Request $request)
    {
        try {
            $data = $request->all();
            $rules = [
                'name' => 'required|regex:/^[a-zA-Z0-9_]+$/|between:3,25|unique:users|alpha_dash',
                'first_name' => 'required|regex:/^[a-zA-Z0-9_]+$/|between:3,25',
                'last_name' => 'required|regex:/^[a-zA-Z0-9_]+$/|between:3,25',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8',
                'role_id' => 'required'
            ];
            $validation = Validator::make($data, $rules);
            if ($validation->fails()) {
                return response()->json([
                    "messages" => $validation->errors()
                ], 422);
            }

            $user = $this->userRepository->register($data);
            if ($user) {
                // Generate Access Token
                $accessToken = $user->createToken('auth_token')->plainTextToken;

                // Generate Refresh Token
                $refreshToken = base64_encode(Str::random(40)); // أو أي طريقة توليد أخرى
                $user->refresh_token = $refreshToken; // احفظه في قاعدة البيانات إذا أردت
                $user->save();

                return response()->json([
                    'messages' => 'User has been Created',
                    'data' => [
                        'user' => $user,
                        'access_token' => $accessToken,
                        'refresh_token' => $refreshToken
                    ]
                ]);
            } else {
                return response()->json([
                    'messages' => 'The process has failed!',
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'messages' => 'The process has failed!',
                'data' => $e->getMessage()
            ]);
        }
    }

    public function login(Request $request)
    {
        $data = $request->all();
        $rules = [
            'name' => 'required|regex:/^[a-zA-Z0-9_]+$/|between:3,25|exists:users,name|alpha_dash',
            'password' => 'required|min:8'
        ];
        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
            return response()->json([
                "messages" => $validation->errors()
            ], 422);
//            $errors = $validation->errors();
//            return api()->validation('This Fields are Required.' ,$errors);
        }
        if ((Auth::attempt(['name' => $request->name, 'password' => $request->password]))) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;
            $datares['token'] = $token;
            return response()->json([
                'messages' => 'User has been Login',
                'data' => $datares
            ]);
//            return api()->ok( 'User has been Login', $datares);


        } else {
            return response()->json([
                'messages' => 'The password is incorrect',
            ]);
//            return api()->error('the password incorrect !');
        }
    }
    public function logout(Request $request)
    {
        try {

            $accessToken = $request->bearerToken();

            // Get access token from database
            $token = PersonalAccessToken::findToken($accessToken);
            if ($token)
            {
                $token->delete();
                return response()->json([
                    'messages'=>'User has been Logout',
                ]);
//                return api()->ok('User has been Logout');
            }
            else
            {
                return response()->json([
                    'messages'=>'Token not found',
                ]);
//                return api()->error('Token not Found!');

            }
        }
        catch (Exception $e)
        {
            return response()->json([
                'messages'=>'Error',
            ]);
//            return api()->error('Error ');
        }

    }
    public function displayAllUser()
    {
        return $this->userRepository->displayAllUser();
    }
}
