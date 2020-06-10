<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use JWTAuth;
use Valitator;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;


class AuthController extends Controller
{

    protected $user;

    public function __construct()
    {
        $this->user = new User;
    }

    public function register(Request $request){
        $request->validate([
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:5'
        ]);

        $check_email = $this->user->where('email', $request->email)->count();

        if($check_email > 0){
            return response()->json([
                'success' => false,
                'message' => 'This email already exists please try another email.'
            ], 401);
        }

        $registerComplete = $this->user::create([
            'firstname' => $request->firstname,
            'lastname'  => $request->lastname,
            'email'     => $request->email,
            'password'  =>Hash::make($request->password)
        ]);

        if($registerComplete){
            return $this->login($request);
        }
    }

    function login(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:5'
        ]);

        $jwt_token = null;

        $input = $request->only('email', 'password');
        if (!$jwt_token = auth('users')->attempt($input)){
            return response()->json([
                'success' => false,
                'message' => 'invalid email or password'
            ]);
        }

        return response()->json([
            'success' => true,
            'token' => $jwt_token
        ]);
    }

}
