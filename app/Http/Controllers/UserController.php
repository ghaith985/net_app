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
    public function renewToken(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        // البحث عن المستخدم الذي يمتلك الـ Refresh Token
        $user = \App\Models\User::where('refresh_token', $request->refresh_token)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Invalid refresh token.',
            ], 401);
        }

        // التحقق من صلاحية Refresh Token (اختياري)
        // يمكنك إضافة شروط إضافية هنا إذا أردت.

        // حذف التوكينات السابقة
        $user->tokens()->delete();

        // إنشاء توكين جديد
        $newAccessToken = $user->createToken('auth_token')->plainTextToken;

        // إنشاء Refresh Token جديد
        $newRefreshToken = base64_encode(Str::random(40));
        $user->refresh_token = $newRefreshToken;
        $user->save();

        return response()->json([
            'message' => 'Token renewed successfully.',
            'data' => [
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefreshToken,
            ],
        ]);
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
                'role_id' => 'required|in:1,2'
            ];
            $validation = Validator::make($data, $rules);
            if ($validation->fails()) {
                return response()->json([
                    "messages" => $validation->errors()
                ], 422);
            }
            // إنشاء المستخدم بناءً على الدور
            if ($data['role_id'] == 1) {
                // إنشاء حساب أدمن
                $data['is_admin'] = true; // إذا كان لديك عمود في الجدول يدل على أن المستخدم هو أدمن
            } else {
                // إنشاء حساب مستخدم عادي
                $data['is_admin'] = false;
            }


            $user = $this->userRepository->register($data);
            if ($user) {
                // Generate Access Token
                $accessToken = $user->createToken('auth_token')->plainTextToken;
                // Calculate Expiration Time
                $expirationMinutes = config('sanctum.expiration'); // قراءة وقت الصلاحية من الإعدادات
                $expirationTime = $expirationMinutes ? now()->addMinutes($expirationMinutes)->toDateTimeString() : null;

                $newRefreshToken = base64_encode(Str::random(40));
                $user->refresh_token = $newRefreshToken;
                $user->save();

                return response()->json([
                    'messages' => 'User has been Created',
                    'data' => [
                        'user' => $user,
                        'access_token' => $accessToken,
                        'token_expiration' => $expirationTime ,// وقت انتهاء صلاحية التوكين
                        'refresh_token' => $newRefreshToken // وقت انتهاء صلاحية التوكين
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
            $expirationMinutes = config('sanctum.expiration'); // قراءة وقت الصلاحية من الإعدادات
            $expirationTime = $expirationMinutes ? now()->addMinutes($expirationMinutes)->toDateTimeString() : null;
            $newRefreshToken = base64_encode(Str::random(40));
            $user->refresh_token = $newRefreshToken;
            $user->save();

            $datares['token'] = $token;
            $datares['token_expiration'] = $expirationTime;
            $datares['refresh_token'] = $newRefreshToken;
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
