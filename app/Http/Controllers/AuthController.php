<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Client;
use http\Client\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Events\NewAccountRegistered;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index()
    {
        return "hello";
    }


    public function register(RegisterUserRequest $request)
    {
        try {
            $input = $request->validated();
            $input['password'] = Hash::make($input['password']);
            $user = User::create($input);

            event(new NewAccountRegistered($user));

            return response()->json([
                'status' => true,
                'message' => "Account created",
                'data' => [
                    'user_id' => $user->id
                ]
            ], 201);

        }catch (\Exception $e){
            if(isset($user) && $user){
                $user->delete();
            }
            return response()->json([
                'status' => false,
                'message' => 'registration failed',
                'error' => $e->getMessage()
            ], 406);
        }
    }

    public function login(LoginUserRequest $request)
    {
        try {

            // Check if user data exist
            if (!$user = User::attempt($request->email, $request->password)){
                throw new \Exception('Invalid credentials provided', 400);
            }

            if (!$user->emailVerified()){
                throw new \Exception('Account not yet verified. Please verify your mail', 401);
            }


            $client = new Client();
            $response = $client->request('POST',
                config('service.passport.login_endpoint'),
                ["form_params" => [
                    'client_secret' => config('service.passport.client_secret'),
                    'client_id' => config('service.passport.client_id'),
                    'grant_type' => 'password',
                    'username' => $request->email,
                    'password' => $request->password
                ]
            ]);

            $response = (array) json_decode($response->getBody()->getContents());
            $response['user'] = $user;

            return response()->json([
                'status' => true,
                'message' => "Login successful",
                'data' => $response
            ], 201);
        }catch (\Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Login Failed. '.$e->getMessage(),
                'error' => $e->getMessage()
            ], $e->getCode() ? $e->getCode() : 500);
        }
    }

    public function verify(\Illuminate\Http\Request $request, $id)
    {
        try {
            $token = $request->get('token');

            if(!$user = User::find($id)){
                throw new \Exception("Bad request", 400);
            }

            if (!$user->verifyWithToken($token)){
                throw new \Exception("Bad request", 400);
            }

            return response()->json([
                'status' => true,
                'message' => "Account verified",
                'data' => []
            ], 201);

        }catch (\Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Verification failed. '.$e->getMessage(),
                'error' => $e->getMessage()
            ], $e->getCode() ? $e->getCode() : 500);
        }
    }

    public function logout()
    {
        try {
            if (!$user = auth()->user()){
                return response()->json([
                    'status' => true,
                    'message' => 'Account already logged out'
                ]);
            }

            auth()->user()->tokens()->each(function($token){
                $token->delete();
            });

            return response()->json([
                'status' => true,
                'message' => 'Account has been logged out'
            ]);
        }catch (\Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'This action has failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
